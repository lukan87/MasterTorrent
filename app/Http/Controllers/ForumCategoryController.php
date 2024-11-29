<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\Topic;
use Illuminate\Http\Request;

class ForumCategoryController extends Controller
{
    // Display all categories
    public function index()
    {
        // Get all categories and their associated topics
        $categories = ForumCategory::with('topics')->get();

        return view('forum.categories.index', compact('categories'));
    }
    // Show a specific category with its topics
    public function show($id)
    {
        $category = ForumCategory::findOrFail($id);
        $topics = Topic::where('forum_category_id', $id)->get();

        return view('forum.categories.show', compact('category', 'topics'));
    }

    // Create a new forum category
    public function create()
    {
        return view('forum.categories.create');
    }

    // Store a new forum category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = new ForumCategory();
        $category->name = $request->input('name');
        $category->description = $request->input('description');
        $category->save();

        return redirect()->route('forum.index')->with('success', 'Category created successfully.');
    }

    // Edit an existing category
 // Show the form to edit a category
 public function edit(ForumCategory $category)
 {
     // You can pass the category to the view
     return view('forum.categories.edit', compact('category'));
 }

 // Update the category in the database
 public function update(Request $request, ForumCategory $category)
 {
     // Validate the updated category data
     $request->validate([
         'name' => 'required|string|max:255',
         'description' => 'required|string|max:500',
     ]);

     // Update the category
     $category->update([
         'name' => $request->name,
         'description' => $request->description,
     ]);

     // Redirect with a success message
     return redirect()->route('forum.categories.index')->with('success', 'Category updated successfully!');
 }

    // Delete a forum category
    public function destroy($id)
    {
        $category = ForumCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('forum.index')->with('success', 'Category deleted successfully.');
    }
}
