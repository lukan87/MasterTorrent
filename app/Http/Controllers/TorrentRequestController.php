<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Torrent;
use App\Models\TorrentRequest;
use App\Notifications\RequestFilledNotification;
use App\Services\RequestMetadataService;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TorrentRequestController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['open', 'filled'])],
            'category_id' => 'nullable|integer|exists:categories,id',
            'mine' => 'nullable|boolean',
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'updated', 'popular'])],
        ]);
        $query = TorrentRequest::with(['category', 'requester', 'filledBy', 'votes' => fn ($query) => $query->with('user:id,name')->orderBy('id')])
            ->withCount(['votes', 'comments'])
            ->withExists(['votes as has_voted' => fn ($query) => $query->where('user_id', Auth::id())]);
        if ($search = trim($filters['q'] ?? '')) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        if (! empty($filters['status'])) {
            $query->where('filled', $filters['status'] === 'filled' ? 'yes' : 'no');
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['mine'])) {
            $query->where('requested_by', Auth::id());
        }
        match ($filters['sort'] ?? 'newest') {
            'popular' => $query->orderByDesc('votes_count')->orderByDesc('id'),
            'oldest' => $query->orderBy('id'),
            'updated' => $query->orderByDesc('updated_at')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };
        $requests = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $counts = TorrentRequest::selectRaw('filled, COUNT(*) AS aggregate')->groupBy('filled')->pluck('aggregate', 'filled');

        return view('requests.index', compact('requests', 'categories', 'filters', 'counts'));
    }

    public function create(Request $request)
    {
        abort_unless(TorrentRequest::canBeCreatedBy(Auth::user()), 403);
        $categories = Category::orderBy('name')->get();
        $defaults = array_filter($request->only(array_keys(TorrentRequest::inputRules())), fn ($value) => is_string($value));

        return view('requests.create', compact('categories', 'defaults'));
    }

    public function store(Request $request)
    {
        abort_unless(TorrentRequest::canBeCreatedBy(Auth::user()), 403);
        $validated = $request->validate(TorrentRequest::inputRules());
        $torrentRequest = new TorrentRequest($validated);
        $torrentRequest->requested_by = Auth::id();
        $torrentRequest->save();

        return redirect()->route('requests.show', $torrentRequest->id)->with('success', 'Request created successfully.');
    }

    public function show($id)
    {
        $request = TorrentRequest::with(['category', 'requester', 'filledBy', 'votes' => fn ($query) => $query->with('user:id,name')->orderBy('id')])
            ->withCount('votes')
            ->withExists(['votes as has_voted' => fn ($query) => $query->where('user_id', Auth::id())])
            ->findOrFail($id);
        $discussionComments = $request->comments()->discussion()->paginate(10, ['*'], 'comments_page')->withQueryString()->fragment('discussion');
        $metadata = app(RequestMetadataService::class)->forRequest($request);

        return view('requests.show', compact('request', 'discussionComments', 'metadata'));
    }

    public function edit($id)
    {
        $request = TorrentRequest::findOrFail($id);
        abort_unless($request->canBeManagedBy(Auth::user()), 403);
        $categories = Category::orderBy('name')->get();

        return view('requests.edit', compact('request', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $torrentRequest = TorrentRequest::findOrFail($id);
        abort_unless($torrentRequest->canBeManagedBy(Auth::user()), 403);
        $torrentRequest->update($request->validate(TorrentRequest::inputRules()));

        return redirect()->route('requests.show', $id)->with('success', 'Request updated successfully.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $request = TorrentRequest::lockForUpdate()->findOrFail($id);
            abort_unless($request->canBeDeletedBy(Auth::user()), 403);
            // Polymorphic comments do not have a foreign key to their target.
            $request->comments()->delete();
            $request->delete();
        });

        return redirect()->route('requests.index')->with('success', 'Request deleted successfully.');
    }

    public function fillRequest(Request $request, $id)
    {
        abort_unless(TorrentRequest::canBeFilledBy(Auth::user()), 403, 'Only uploaders or members with upload permission can fill requests.');
        $validated = $request->validate(['link' => 'required|url:http,https|max:255']);
        $parts = parse_url($validated['link']);
        $site = parse_url(route('requests.index'));
        $path = rtrim(dirname($site['path']), '/').'/torrents/';
        if (strtolower($parts['host'] ?? '') !== strtolower($site['host'])
            || ($parts['port'] ?? null) !== ($site['port'] ?? null)
            || isset($parts['user']) || isset($parts['pass'])
            || ! preg_match('~^'.preg_quote($path, '~').'([1-9][0-9]*)(?:/[^/]*)?/?$~', $parts['path'] ?? '', $matches)) {
            throw ValidationException::withMessages(['link' => 'Paste a torrent details link from this site.']);
        }
        $torrent = Torrent::find($matches[1]);
        if (! $torrent) {
            throw ValidationException::withMessages(['link' => 'This torrent does not exist or is no longer available.']);
        }
        $link = route('torrents.show', ['id' => $torrent->id]);
        DB::transaction(function () use ($id, $link) {
            $torrentRequest = TorrentRequest::lockForUpdate()->findOrFail($id);
            if ($torrentRequest->filled === 'yes') {
                throw ValidationException::withMessages(['link' => 'This request has already been filled.']);
            }
            $torrentRequest->filled = 'yes';
            $torrentRequest->link = $link;
            $torrentRequest->filled_by = Auth::id();
            $torrentRequest->save();
            // Database notifications commit with the fill; the request lock prevents duplicate sends.
            $torrentRequest->votes()->with('user')->chunkById(200, function ($votes) use ($torrentRequest) {
                foreach ($votes as $vote) {
                    $vote->user?->notify(new RequestFilledNotification($torrentRequest));
                }
            });
            if ($torrentRequest->requester) {
                SystemMessageService::send(
                    2, // System: keep all request updates in the member's System conversation.
                    $torrentRequest->requested_by,
                    'Torrent request filled',
                    "Your request for '{$torrentRequest->name}' has been filled: {$link}"
                );
            }
        });

        return redirect()->route('requests.show', $id)->with('success', 'Request filled successfully.');
    }

    public function vote(Request $request, $id)
    {
        $data = $request->validate(['voted' => 'required|boolean']);
        DB::transaction(function () use ($id, $data) {
            $target = TorrentRequest::lockForUpdate()->findOrFail($id);
            abort_unless($target->canBeVotedBy(Auth::user()), 403, 'You cannot vote on your own request.');
            if ($data['voted']) {
                $target->votes()->firstOrCreate(['user_id' => Auth::id()]);
            } else {
                $target->votes()->where('user_id', Auth::id())->delete();
            }
        });

        return back()->with('success', $data['voted'] ? 'Your vote has been added.' : 'Your vote has been removed.');
    }

    public function reopen($id)
    {
        DB::transaction(function () use ($id) {
            $request = TorrentRequest::lockForUpdate()->findOrFail($id);
            abort_unless($request->canBeManagedBy(Auth::user()), 403);
            abort_unless($request->filled === 'yes', 409, 'This request is already open.');
            $request->filled = 'no';
            $request->filled_by = null;
            $request->link = null;
            $request->save();
        });

        return redirect()->route('requests.show', $id)->with('success', 'Request reopened. Members can submit a new torrent link.');
    }
}
