<?php

namespace App\Http\Controllers;

use App\Models\UploadApplication;
use App\Services\UploadApplicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UploadApplicationController extends Controller
{
    public function __construct(private UploadApplicationService $applications)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(UploadApplication::STATUSES)]]);
        $user = $request->user();
        $canReview = $this->applications->canReview($user);
        $query = UploadApplication::query()->when(! $canReview, fn ($query) => $query->where('applicant_id', $user->id));
        $statusCounts = (clone $query)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $applications = $query->with('applicant')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')->paginate(25)->withQueryString();
        $blocker = $this->applications->applicationBlocker($user);

        return view('uploadapps.index', compact('applications', 'statusCounts', 'canReview', 'blocker'));
    }

    public function create(Request $request)
    {
        if ($reason = $this->applications->applicationBlocker($request->user())) {
            return redirect()->route('uploadapps.index')->with('error', $reason);
        }

        return view('uploadapps.create');
    }

    public function store(Request $request)
    {
        $answers = $request->validate([
            'why_promoted' => 'required|string|max:10000',
            'experience' => 'required|string|max:10000',
            'content_plan' => 'required|string|max:10000',
            'internal_speed' => 'nullable|url:http,https|max:255',
            'external_speed' => 'nullable|url:http,https|max:255',
            'external_sites' => 'nullable|string|max:5000',
            'scene_access' => 'required|boolean',
            'know_torrents' => 'required|boolean',
            'understand_seeding' => 'required|boolean',
        ]);
        $application = $this->applications->submit($request->user(), $answers);

        return redirect()->route('uploadapps.show', $application->id)
            ->with('success', 'Application submitted. You will receive a private message when a decision is made.');
    }

    public function show(Request $request, int $id)
    {
        $application = UploadApplication::with(['applicant', 'reviewer'])->findOrFail($id);
        $canReview = $this->applications->canReview($request->user());
        abort_unless($canReview || (int) $request->user()->id === (int) $application->applicant_id, 403);
        $canAct = $canReview && (int) $request->user()->id !== (int) $application->applicant_id && $application->isActive() && ! $application->applicant->trashed();
        // Staff discussion stays internal, including on an applicant's own page.
        $comments = $canReview
            ? $application->comments()->with('user')->paginate(20, ['*'], 'comments_page')
            : null;
        if ($canReview) {
            $application->load('votes.user');
        }

        return view('uploadapps.show', compact('application', 'canReview', 'canAct', 'comments'));
    }

    public function accept(Request $request, int $id)
    {
        $this->applications->authorizeReviewer($request->user());
        $data = $request->validate(['reason' => 'nullable|string|max:2000']);
        $this->applications->decide($request->user(), $id, 'accepted', $data['reason'] ?? null);

        return back()->with('success', 'Application approved. A confirmation was sent to the applicant’s inbox.');
    }

    public function reject(Request $request, int $id)
    {
        $this->applications->authorizeReviewer($request->user());
        $data = $request->validate(['reason' => 'required|string|max:2000']);
        $this->applications->decide($request->user(), $id, 'rejected', $data['reason']);

        return back()->with('success', 'Application rejected. Your feedback was sent to the applicant’s inbox.');
    }
}
