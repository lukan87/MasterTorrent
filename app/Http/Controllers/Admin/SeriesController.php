<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Series;

class SeriesController extends Controller
{
    // Display all series
    public function index()
    {
        $series = Series::all();
        return view('admin.series.index', compact('series'));
    }

    // Show the edit form for a specific series
    public function edit($id)
    {
        $series = Series::findOrFail($id);
        return view('admin.series.edit', compact('series'));
    }

    // Update the series information
    public function update(Request $request, $id)
{
    $series = Series::findOrFail($id);



    // Validate input
    $request->validate([
        'name' => 'required|string|max:255',

    ]);

    // Assign input values to the series model
    $series->name = $request->input('name');
    $series->poster_path = $request->input('poster_path');
    $series->overview = $request->input('overview');
    $series->backdrop_path = $request->input('backdrop_path');



    // Save the updated series back to the database
    $series->save();

    return redirect()->route('admin.series.index')->with('status', 'Series updated successfully!');
}


    // Delete series
    public function destroy($id)
    {
        $series = Series::findOrFail($id);
        $series->delete();

        return redirect()->route('admin.series.index')->with('status', 'Series deleted successfully!');
    }
}

