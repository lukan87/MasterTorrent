<?php

namespace App\Services;

use App\Models\ForumPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ForumReadService
{
    public function unread(int $userId): Builder
    {
        // Retain historical read watermarks; new visits record only displayed posts.
        return ForumPost::query()->where('forum_posts.user_id', '!=', $userId)
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('forum_post_reads')
                ->where('forum_post_reads.user_id', $userId)->whereColumn('forum_post_reads.post_id', 'forum_posts.id'))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('forum_topic_views')
                ->where('forum_topic_views.user_id', $userId)->whereColumn('forum_topic_views.topic_id', 'forum_posts.topic_id')
                ->whereColumn('forum_topic_views.updated_at', '>=', 'forum_posts.created_at'));
    }

    public function markDisplayed(int $userId, Collection $posts): void
    {
        $rows = $posts->filter()->map(fn ($post) => ['user_id' => $userId, 'post_id' => $post->id])->unique('post_id')->values()->all();
        if ($rows !== []) {
            DB::table('forum_post_reads')->insertOrIgnore($rows);
        }
    }
}
