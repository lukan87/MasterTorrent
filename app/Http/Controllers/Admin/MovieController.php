<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.library.index', 'movies');
    }

    public function edit($id)
    {
        $media = Movie::findOrFail($id);
        return redirect()->route('admin.library.edit', ['movies', $media->tmdb_id]);
    }

    public function update(Request $request, $id)
    {
        return $this->edit($id);
    }

    public function destroy($id)
    {
        abort_unless((auth()->user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
        Movie::findOrFail($id)->update(['online_enabled' => false]);
        return redirect()->route('admin.library.index', 'movies')->with('status', 'Online playback disabled.');
    }
}
