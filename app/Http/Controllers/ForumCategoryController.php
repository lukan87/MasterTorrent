<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumCategory;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ForumCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'create_categories')) {
            abort(403);
        }

        return view('forumcategories.create');
    }

    public function store(Request $request)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'create_categories')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'position' => 'nullable|integer',
            'min_class_required' => 'required|integer|min:1|max:9',
        ]);

        ForumCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'position' => $request->position ?? 0,
            'min_class_required' => $request->min_class_required,
        ]);

        return redirect()
            ->route('forums.index')
            ->with('success', 'Category created successfully.');
    }

    public function show($id)
    {
        $userClass = Auth::user()->user_class;

       $category = ForumCategory::withTrashed()->find($id);


        if (!$category) {
            return redirect()
                ->route('forums.index')
                ->with('error', 'The requested category does not exist.');
        }

        if ($userClass < $category->min_class_required) {
            return redirect()
                ->route('forums.index')
                ->with('error', 'You do not have permission to view this category.');
        }

        $category->load(['forums' => function ($q) use ($userClass) {
            $q->where('min_class_required', '<=', $userClass);
        }]);

        return view('forumcategories.show', compact('category'));
    }

    public function edit(ForumCategory $category)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'edit_categories')) {
            abort(403);
        }

        return view('forumcategories.edit', compact('category'));
    }

    public function update(Request $request, ForumCategory $category)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'edit_categories')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'position' => 'nullable|integer|min:0',
            'min_class_required' => 'required|integer|min:1|max:9',
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'position' => $validated['position'] ?? 0,
            'min_class_required' => $validated['min_class_required'],
        ]);

        return redirect()
            ->route('forums.category', $category->id)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ForumCategory $category)
{
    $userClass = Auth::user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'delete_categories')) {
        abort(403);
    }

    DB::transaction(function () use ($category) {

        foreach ($category->forums as $forum) {
            foreach ($forum->topics as $topic) {
                $topic->posts()->delete();   // soft delete
            }
            $forum->topics()->delete();     // soft delete
            $forum->delete();               // soft delete
        }

        $category->delete();                // soft delete
    });

    return redirect()
        ->route('forums.index')
        ->with('success', 'Category moved to trash. You can restore it later.');
}

public function restore($id)
{
    $userClass = Auth::user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'delete_categories')) {
        abort(403);
    }

    DB::transaction(function () use ($id) {

        $category = ForumCategory::withTrashed()->findOrFail($id);
        $category->restore();

        foreach ($category->forums()->withTrashed()->get() as $forum) {
            $forum->restore();

            foreach ($forum->topics()->withTrashed()->get() as $topic) {
                $topic->restore();
                $topic->posts()->withTrashed()->restore();
            }
        }
    });

    return redirect()
        ->route('forums.index')
        ->with('success', 'Category restored successfully.');
}


public function forceDelete($id)
{
    $userClass = Auth::user()->user_class;

    // Only high-level staff
    if (!UserClass::userHasPermission($userClass, 'delete_categories')) {
        abort(403);
    }

    DB::transaction(function () use ($id) {

        $category = ForumCategory::withTrashed()->findOrFail($id);

        foreach ($category->forums()->withTrashed()->get() as $forum) {
            foreach ($forum->topics()->withTrashed()->get() as $topic) {
                $topic->posts()->withTrashed()->forceDelete();
            }

            $forum->topics()->withTrashed()->forceDelete();
            $forum->forceDelete();
        }

        $category->forceDelete();
    });

    return redirect()
        ->route('forums.index')
        ->with('success', 'Category permanently deleted.');
}


}
