<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    // Display all series
    public function index(Request $request)
    {
        return redirect()->route('admin.library.index', 'series');
    }

    public function edit($id)
    {
        $media = Series::findOrFail($id);
        return redirect()->route('admin.library.edit', ['series', $media->tmdb_id]);
    }

    // Update the series information
    public function update(Request $request, $id)
    {
        return $this->edit($id);
    }

    public function destroy($id)
    {
        abort_unless((auth()->user()?->user_class ?? 0) >= \App\Models\UserClass::ADMIN, 403);
        Series::findOrFail($id)->update(['online_enabled' => false]);
        return redirect()->route('admin.library.index', 'series')->with('status', 'Online playback disabled.');
    }
}
