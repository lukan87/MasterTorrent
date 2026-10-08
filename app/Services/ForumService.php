<?php

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumService
{
    private const REVISION_KEY = 'forum_cache_revision_v1';

    public static function invalidate(): void
    {
        Cache::forget(self::REVISION_KEY);
    }

    private function remember(string $key, Closure $callback): mixed
    {
        $revision = Cache::rememberForever(self::REVISION_KEY, fn () => (string) Str::uuid());

        return Cache::remember('forum_v1:'.$revision.':'.$key,
            (int) config('cache.forum_duration', 30), $callback);
    }

    public function categories(bool $deleted = false, bool $includePrivate = false): Collection
    {
        return $this->remember($deleted ? 'deleted_categories' : 'categories:'.(int) $includePrivate, function () use ($deleted, $includePrivate) {
            $query = $deleted
                ? ForumCategory::onlyTrashed()->orderByDesc('deleted_at')
                : ForumCategory::when(! $includePrivate, fn ($query) => $query->where('is_private', false))->with('latestTopic.lastPost.user')->orderBy('position');

            return $query->withCount('topics')->get();
        });
    }

    public function categoryTopics(ForumCategory $category, string $sort, int $page): LengthAwarePaginator
    {
        $topics = $this->remember("category:{$category->id}:{$sort}:{$page}", function () use ($category, $sort, $page) {
            $query = $category->topics()->with(['user', 'lastPost.user'])
                ->withCount('posts')->orderByDesc('is_pinned');
            match ($sort) {
                'created' => $query->latest('created_at'),
                'views' => $query->orderByDesc('views'),
                'replies' => $query->orderByDesc('posts_count'),
                default => $query->orderByDesc(ForumPost::select('created_at')->whereColumn('id', 'forum_topics.last_post_id')->limit(1)),
            };

            return $query->orderByDesc('id')->paginate(25, ['*'], 'page', $page);
        });

        // Unread badges and pagination links must never mutate shared cached models.
        $topics = clone $topics;
        $topics->setCollection($topics->getCollection()->map(fn ($topic) => clone $topic));

        return $topics->setPath(Paginator::resolveCurrentPath());
    }

    public function topicPosts(ForumTopic $topic, int $page): array
    {
        $data = $this->remember("topic:{$topic->id}:{$page}", function () use ($topic, $page) {
            $relations = ['user' => fn ($query) => $query->withCount('forumPosts'), 'likes'];
            $firstPost = $topic->posts()->with($relations)->oldest('id')->first();
            $replies = $topic->posts()->with($relations)
                ->when($firstPost, fn ($query) => $query->where('id', '!=', $firstPost->id))
                ->orderByDesc('created_at')->orderByDesc('id')->paginate(15, ['*'], 'page', $page);

            return ['author' => $topic->user()->first(), 'firstPost' => $firstPost, 'replies' => $replies];
        });
        $data['replies'] = clone $data['replies'];
        $data['replies']->setPath(Paginator::resolveCurrentPath());

        return $data;
    }

    public function search(string $query, int $page = 1, bool $includePrivate = false): LengthAwarePaginator
    {
        if (mb_strlen($query) < 2) {
            return new LengthAwarePaginator([], 0, 25, $page, ['path' => Paginator::resolveCurrentPath()]);
        }
        $results = $this->remember('search:'.hash('sha256', $query).':'.$page.':'.(int) $includePrivate, function () use ($query, $page, $includePrivate) {
            // Treat LIKE metacharacters as literal search text.
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query);
            $term = '%'.$escaped.'%';
            $topics = ForumTopic::visible($includePrivate)
                ->select('id', 'created_at')->selectRaw("'topic' AS kind")
                ->selectRaw("CASE WHEN title = ? THEN 0 WHEN title LIKE ? ESCAPE '!' THEN 1 ELSE 2 END AS relevance", [$query, $escaped.'%'])
                ->whereRaw("title LIKE ? ESCAPE '!'", [$term]);
            $posts = ForumPost::whereHas('topic', fn ($topic) => $topic->visible($includePrivate))
                ->select('id', 'created_at')->selectRaw("'post' AS kind, 3 AS relevance")
                ->whereRaw("body LIKE ? ESCAPE '!'", [$term]);
            $results = DB::query()->fromSub($topics->unionAll($posts), 'matches')
                ->orderBy('relevance')->orderByDesc('created_at')->orderByDesc('id')->paginate(25, ['*'], 'page', $page);
            $items = $results->getCollection();
            $topicModels = ForumTopic::with(['user', 'category', 'lastPost.user'])->withCount('posts')
                ->whereIn('id', $items->where('kind', 'topic')->pluck('id'))->get()->keyBy('id');
            $postModels = ForumPost::with(['user', 'topic.category'])
                ->whereIn('id', $items->where('kind', 'post')->pluck('id'))->get()->keyBy('id');
            $results->setCollection($items->map(fn ($item) => $item->kind === 'topic' ? $topicModels->get($item->id) : $postModels->get($item->id))->filter()->values());

            return $results;
        });

        return (clone $results)->setPath(Paginator::resolveCurrentPath());
    }

    public static function snippet(string $body, string $query): string
    {
        $text = trim(preg_replace('/\[(?:\/?[a-z]+)(?:=[^\]]*)?\]|\[\*\]/i', '', strip_tags($body)));
        $position = mb_stripos($text, $query);
        $start = max(0, ($position === false ? 0 : $position) - 60);
        $snippet = mb_substr($text, $start, 180);

        return ($start > 0 ? '…' : '').$snippet.(mb_strlen($text) > $start + 180 ? '…' : '');
    }

    public function participatedTopics(int $userId, int $page, bool $includePrivate = false): LengthAwarePaginator
    {
        $topics = $this->remember("member:{$userId}:{$page}:".(int) $includePrivate, fn () => ForumTopic::visible($includePrivate)
            ->whereIn('id', ForumPost::select('topic_id')->where('user_id', $userId))
            ->with(['user', 'lastPost.user', 'category'])->withCount('posts')
            ->orderByDesc(ForumPost::select('created_at')->whereColumn('id', 'forum_topics.last_post_id')->limit(1))->orderByDesc('id')->paginate(25, ['*'], 'page', $page));

        return (clone $topics)->setPath(Paginator::resolveCurrentPath());
    }
}
