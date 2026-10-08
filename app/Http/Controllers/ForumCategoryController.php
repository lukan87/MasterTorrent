<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Services\ForumAccess;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForumCategoryController extends Controller
{
    public function create()
    {
        ForumAccess::authorize('create_categories');

        return view('forum.categories.create');
    }

    public function store(Request $request)
    {
        ForumAccess::authorize('create_categories');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_private' => [
                'nullable',
                'boolean',
            ],
        ]);

        $slug = Str::slug($validated['name']) ?: 'category';
        if (in_array($slug, ['search', 'preview', 'categories', 'my-topics'], true)) {
            $slug .= '-category';
        }

        if (ForumCategory::withTrashed()->where('slug', $slug)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'A category with this name already exists.',
                ]);
        }

        try {
            ForumCategory::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'position' => $validated['position'] ?? 0,
                'is_private' => $validated['is_private'] ?? false,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return back()->withInput()->withErrors(['name' => 'A category with this name already exists, including deleted categories.']);
        }

        return redirect()
            ->route('forum.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ForumCategory $category)
    {
        ForumAccess::authorize('edit_categories');

        return view('forum.categories.edit', compact('category'));
    }

    public function update(Request $request, ForumCategory $category)
    {
        ForumAccess::authorize('edit_categories');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_private' => [
                'nullable',
                'boolean',
            ],
        ]);

        $slug = Str::slug($validated['name']) ?: 'category';
        if (in_array($slug, ['search', 'preview', 'categories', 'my-topics'], true)) {
            $slug .= '-category';
        }

        if (
            ForumCategory::withTrashed()->where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'A category with this name already exists.',
                ]);
        }

        try {
            $category->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'position' => $validated['position'] ?? 0,
                'is_private' => $validated['is_private'] ?? false,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return back()->withInput()->withErrors(['name' => 'A category with this name already exists, including deleted categories.']);
        }

        return redirect()
            ->route('forum.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ForumCategory $category)
    {
        ForumAccess::authorize('delete_categories');

        $category->delete();

        return redirect()
            ->route('forum.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function restore($id)
    {
        ForumAccess::authorize('delete_categories');

        $category = ForumCategory::withTrashed()->findOrFail($id);

        $category->restore();

        return redirect()
            ->route('forum.index')
            ->with('success', 'Category restored successfully.');
    }

    public function forceDestroy($id)
    {

        ForumAccess::authorize('delete_categories');

        $category = ForumCategory::withTrashed()->findOrFail($id);

        $topicCount = $category->topics()->count();

        $category->forceDelete();

        $message = $topicCount > 0
            ? "Category permanently deleted along with {$topicCount} topic(s)."
            : 'Category permanently deleted.';

        return redirect()
            ->route('forum.index')
            ->with('success', $message);
    }
}
