<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);
        $movies = Movie::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->input('search').'%'))
            ->orderBy('name')->orderBy('id')->paginate(50)->withQueryString();

        return view('admin.movies.index', compact('movies'));
    }

    public function edit($id)
    {
        $movie = Movie::findOrFail($id);

        return view('admin.movies.edit', compact('movie'));
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'overview' => 'nullable|string|max:50000',
            // Add other fields for validation as needed
        ]);

        // Update movie details
        $movie->name = $request->name;
        // Set other movie properties here
        $movie->overview = $request->overview;

        $movie->save();

        return redirect()->route('admin.movies.index')->with('status', 'Movie updated successfully!');
    }

    public function destroy($id)
    {
        Movie::findOrFail($id)->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully');
    }
}
