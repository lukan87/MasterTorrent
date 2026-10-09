<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use App\Models\Shoutbox;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private HomeService $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->middleware('auth');
        $this->homeService = $homeService;
    }

    public function index(Request $request)
    {
        if ($request->hasHeader('X-Torrent-Featured')) {
            $card = app(\App\Services\Torrent\TorrentFeaturedService::class)
                ->card($request->header('X-Torrent-Featured'), $request);
            return \App\Services\PageBrowse::json([
                'html' => view('torrents.partials.featured-card', $card)->render(),
            ]);
        }
        if ($request->header('X-Home-Widget') && $request->expectsJson()) {
            $widget = $request->header('X-Home-Widget');
            abort_unless(in_array($widget, ['trending', 'polls', 'online'], true), 422);
            return \App\Services\PageBrowse::json(['html' => view('home-widgets.'.$widget, $this->homeService->getWidgetData($widget))->render()]);
        }

        $highlightRequested = $request->filled('shout') && !$request->user()->chatblock;
        if ($highlightRequested) {
            // Reject malformed links before doing dashboard work.
            $request->validate(['shout' => ['integer', 'min:1']]);
        }

        $data = $this->homeService->getDashboardData();
        $featured = app(\App\Services\Torrent\TorrentFeaturedService::class);
        $data['featuredCards'] = [
            $featured->card('sticky', $request), $featured->card('hot', $request),
        ];
        if ($highlightRequested) {
            $shout = Shoutbox::find($request->integer('shout'));
            if ($shout) {
                $thread = $shout;
                while ($thread && $thread->parent_id) {
                    $thread = Shoutbox::find($thread->parent_id);
                }
                if ($thread) {
                    $thread->load(['user', 'replies.user']);
                    // Add only to this response, never to the shared dashboard cache.
                    $data['messages'] = $data['messages']->reject(fn ($message) => $message->id === $thread->id)
                        ->push($thread);
                    $data['highlightShoutId'] = $shout->id;
                }
            }
            if (empty($data['highlightShoutId'])) {
                $data['shoutUnavailable'] = true;
            }
        }

        return view('home', $data);
    }
}
