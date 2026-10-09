<?php

namespace App\Http\Controllers;

use App\Models\UploadAttempt;
use App\Models\UserClass;
use App\Services\Torrent\UploadPermission;
use Illuminate\Http\Request;

class UploadHistoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(app(UploadPermission::class)->active($request->user()), 403);

        return $this->page(UploadAttempt::where('user_id', $request->user()->id), false);
    }

    public function admin(Request $request)
    {
        abort_unless(app(UploadPermission::class)->active($request->user()) && $request->user()->user_class >= UserClass::ADMIN, 403);
        $request->validate(['method' => ['nullable', 'in:website,api,seedbox'], 'status' => ['nullable', 'in:processing,completed,failed,duplicate']]);
        $query = UploadAttempt::query();
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $this->page($query, true);
    }

    private function page($query, bool $admin)
    {
        return response()->view('profile.api.history', [
            'attempts' => $query->with(['torrent:id,name,slug', 'user:id,name'])->latest()->paginate(25)->withQueryString(),
            'admin' => $admin,
        ])->header('Cache-Control', 'private, no-store');
    }
}
