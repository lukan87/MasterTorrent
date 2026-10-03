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
        $data = $this->homeService->getDashboardData();
        if ($request->filled('shout') && !$request->user()->chatblock) {
            $request->validate(['shout' => ['integer', 'min:1']]);
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
