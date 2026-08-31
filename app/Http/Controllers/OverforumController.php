<?php

namespace App\Http\Controllers;

use App\Models\Overforum;
use Illuminate\Http\Request;

class OverforumController extends Controller
{
    // Show a list of all overforums
    public function index()
    {
        $overforums = Overforum::all();
        return view('overforums.index', compact('overforums'));
    }

    // Show a specific overforum
    public function show($id)
    {
        $overforum = Overforum::with('forums')->findOrFail($id);
        return view('overforums.show', compact('overforum'));
    }
    

    // Show the form to create a new overforum
    public function create()
    {
        return view('overforums.create');
    }

    // Store the newly created overforum
    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create the new overforum
        Overforum::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Redirect to the overforums index page with a success message
        return redirect()->route('overforums.index')->with('success', 'Overforum created successfully!');
    }

    // Show the form to edit an overforum
public function edit($id)
{
    $overforum = Overforum::findOrFail($id);
    return view('overforums.edit', compact('overforum'));
}

// Update the specified overforum in storage
public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    $overforum = Overforum::findOrFail($id);
    $overforum->update([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    return redirect()->route('overforums.index')->with('success', 'Overforum updated successfully!');
}


      // Destroy the specified overforum
      public function destroy($id)
      {
          // Find the overforum by ID
          $overforum = Overforum::findOrFail($id);
  
          // Optionally, check if any associated forums exist before deletion (you can choose how to handle this)
          // Example: You may want to delete forums related to this overforum as well
          $overforum->forums()->delete(); // Uncomment if you want to delete forums as well
  
          // Delete the overforum
          $overforum->delete();
  
          // Redirect back to the overforums index page with a success message
          return redirect()->route('overforums.index')->with('success', 'Overforum deleted successfully!');
      }
}
