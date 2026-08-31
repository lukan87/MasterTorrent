<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use Illuminate\Http\Request;
use App\Models\ForumCategory;
use App\Models\Forum;
use App\Models\UserClass;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * Show all categories and their forums visible to the user
     */
public function index()
{
    $userClass = Auth::user()->user_class;

$query = ForumCategory::with(['forums' => function ($q) use ($userClass) {
    $q->withCount('topics')
      ->withCount('posts')
      ->with(['lastPost.author', 'lastPost.topic'])
      ->where('min_class_required', '<=', $userClass);
}]);

// Only admins can see trashed categories
if (UserClass::userHasPermission($userClass, 'delete_categories')) {
    $query->withTrashed();
}

$categories = $query
    ->orderBy('position')
    ->get();

return view('forums.index', compact('categories'));

}

    /**
     * Show a forum with its topics
     */
    public function showForum($forumId)
{
    // Try to find the forum
    $forum = Forum::find($forumId);

    if (!$forum) {
        // Redirect to forums index with error message
        return redirect()->route('forums.index')
                         ->with('error', 'Forum does not exist.');
    }

    $userClass = auth()->user()?->user_class ?? 0;

    // Only allow users with enough class to see this forum
    if ($userClass < $forum->min_class_required) {
        return redirect()->route('forums.index')
                         ->with('error', 'You do not have permission to view this forum.');
    }

    // Get topics with author, last post author, and posts count
    $topics = ForumTopic::with('author', 'lastPost.author')
                        ->withCount('posts')
                        ->where('forum_id', $forum->id)
                        ->paginate(20);

    return view('forums.show', compact('forum', 'topics'));
}


    public function showCategory(ForumCategory $category)
{
    $userClass = Auth::user()->user_class;

    if (!$category->isVisibleTo($userClass)) {
        abort(403);
    }

    $forums = $category->forums()->visibleTo($userClass)->get();

    return view('forums.category', compact('category', 'forums'));
}
// Show form to create a forum inside a category
    public function createForum(ForumCategory $category)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'create_forums')) {
            abort(403);
        }

        return view('forums.create', compact('category'));
    }

    // Store the forum in the database
    public function storeForum(Request $request, ForumCategory $category)
    {
        $userClass = Auth::user()->user_class;

        if (!UserClass::userHasPermission($userClass, 'create_forums')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_locked' => 'nullable|boolean',
        ]);

        Forum::create([
            'name' => $request->name,
            'description' => $request->description,
            'forum_category_id' => $category->id,
            'is_locked' => $request->is_locked ?? 0,
        ]);

        return redirect()->route('forums.category', $category->id)
                         ->with('success', 'Forum created successfully.');
    }

    // Show edit form
public function edit(Forum $forum)
{
    $userClass = Auth::user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'edit_forums')) {
        abort(403);
    }

    return view('forums.edit', compact('forum'));
}

// Update forum in DB
public function update(Request $request, Forum $forum)
{
    $userClass = Auth::user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'edit_forums')) {
        abort(403);
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'is_locked' => 'nullable|boolean',
    ]);

    $forum->update([
        'name' => $request->name,
        'description' => $request->description,
        'is_locked' => $request->is_locked ?? 0,
    ]);

    return redirect()->route('forums.category', $forum->forum_category_id)
                     ->with('success', 'Forum updated successfully.');
}

// Delete forum
public function destroy(Forum $forum)
{
    $userClass = Auth::user()->user_class;

    if (!UserClass::userHasPermission($userClass, 'delete_forums')) {
        abort(403);
    }

    $forum->delete();

    return redirect()->route('forums.category', $forum->forum_category_id)
                     ->with('success', 'Forum deleted successfully.');
}



}
