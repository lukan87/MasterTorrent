<?php

namespace Tests\Integration;

use App\Http\Controllers\ForumCategoryController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumPostLikeController;
use App\Http\Controllers\TopicSubscriptionController;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumPostLike;
use App\Models\ForumTopic;
use App\Models\User;
use App\Models\UserClass;
use App\Notifications\ForumMentionNotification;
use App\Notifications\ForumReplyNotification;
use App\Services\ForumReadService;
use App\Services\ForumService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('FORUM_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set FORUM_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['cache.default' => 'array', 'session.driver' => 'array', 'achievements.enabled' => false]);
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.forum_test' => $connection]);
        DB::setDefaultConnection('forum_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT DEFAULT 1, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'forum_categories' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), slug VARCHAR(255) UNIQUE, description TEXT NULL, icon VARCHAR(100) NULL, is_private BOOLEAN DEFAULT 0, position INT DEFAULT 0, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'forum_topics' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, category_id BIGINT, user_id BIGINT, title VARCHAR(255), slug VARCHAR(100), last_post_id BIGINT NULL, is_pinned BOOLEAN DEFAULT 0, is_locked BOOLEAN DEFAULT 0, views INT DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'forum_posts' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, topic_id BIGINT, user_id BIGINT, body TEXT, edited_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, INDEX topic_chronology (topic_id, created_at)',
            'forum_post_likes' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, post_id BIGINT, user_id BIGINT, reaction VARCHAR(30), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'forum_post_reads' => 'user_id BIGINT, post_id BIGINT, PRIMARY KEY (user_id, post_id)',
            'forum_topic_views' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, topic_id BIGINT, user_id BIGINT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'notifications' => 'id VARCHAR(36) PRIMARY KEY, type VARCHAR(255), notifiable_type VARCHAR(255), notifiable_id BIGINT, data TEXT, read_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'topic_subscriptions' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, topic_id BIGINT, user_id BIGINT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns})");
        }
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Member A', 'created_at' => now()],
            ['id' => 11, 'name' => 'Member B', 'created_at' => now()],
        ]);
        DB::table('forum_categories')->insert(['id' => 1, 'name' => 'Public', 'slug' => 'public']);
        DB::table('forum_topics')->insert(['id' => 1, 'category_id' => 1, 'user_id' => 10, 'title' => 'Discussion', 'slug' => 'discussion', 'last_post_id' => 2, 'created_at' => now()->subDay(), 'updated_at' => now()]);
        DB::table('forum_posts')->insert([
            ['id' => 1, 'topic_id' => 1, 'user_id' => 10, 'body' => 'Opening post', 'created_at' => now()->subDay()],
            ['id' => 2, 'topic_id' => 1, 'user_id' => 11, 'body' => 'A reply', 'created_at' => now()->subHour()],
        ]);
        auth()->setUser(User::findOrFail(10));
        DB::enableQueryLog();
    }

    protected function tearDown(): void
    {
        if (getenv('FORUM_MYSQL_TEST') === '1') {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::disconnect('forum_test');
            Carbon::setTestNow();
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    public function test_shared_pages_reuse_queries_and_cached_models_remain_unmodified(): void
    {
        $service = new ForumService;
        $category = ForumCategory::findOrFail(1);
        $topic = ForumTopic::findOrFail(1);
        $service->categories();
        $service->categoryTopics($category, 'latest', 1);
        $service->topicPosts($topic, 1);
        DB::flushQueryLog();
        self::assertCount(1, $service->categories());
        $topics = $service->categoryTopics($category, 'latest', 1);
        $topics->first()->new_replies_count = 99;
        $topics->appends(['member' => 'private']);
        self::assertNull($service->categoryTopics($category, 'latest', 1)->first()->new_replies_count);
        self::assertStringNotContainsString('member=private', $service->categoryTopics($category, 'latest', 1)->url(1));
        self::assertSame(2, $service->topicPosts($topic, 1)['replies']->first()->id);
        self::assertCount(0, DB::getQueryLog());
    }

    public function test_replies_edits_and_reactions_invalidate_cached_topic_content(): void
    {
        $service = new ForumService;
        $topic = ForumTopic::findOrFail(1);
        self::assertSame(1, $service->topicPosts($topic, 1)['replies']->total());
        $reply = ForumPost::create(['topic_id' => 1, 'user_id' => 11, 'body' => 'New reply']);
        self::assertSame(2, $service->topicPosts($topic, 1)['replies']->total());
        $reply->update(['body' => 'Edited reply']);
        self::assertSame('Edited reply', $service->topicPosts($topic, 1)['replies']->first()->body);
        $like = ForumPostLike::create(['post_id' => $reply->id, 'user_id' => 10, 'reaction' => 'like']);
        self::assertCount(1, $service->topicPosts($topic, 1)['replies']->first()->likes);
        $like->delete();
        self::assertCount(0, $service->topicPosts($topic, 1)['replies']->first()->likes);
        $reply->delete();
        self::assertSame(1, $service->topicPosts($topic, 1)['replies']->total());
    }

    public function test_invalidation_waits_for_commit_and_rollbacks_leave_cache_valid(): void
    {
        $service = new ForumService;
        $topic = ForumTopic::findOrFail(1);
        $service->topicPosts($topic, 1);
        DB::beginTransaction();
        ForumPost::create(['topic_id' => 1, 'user_id' => 11, 'body' => 'Uncommitted']);
        self::assertSame(1, $service->topicPosts($topic, 1)['replies']->total());
        DB::rollBack();
        DB::flushQueryLog();
        self::assertSame(1, $service->topicPosts($topic, 1)['replies']->total());
        self::assertCount(0, DB::getQueryLog());
        DB::beginTransaction();
        ForumPost::create(['topic_id' => 1, 'user_id' => 11, 'body' => 'Committed']);
        DB::commit();
        self::assertSame(2, $service->topicPosts($topic, 1)['replies']->total());
    }

    public function test_category_privacy_deletion_and_restore_refresh_index(): void
    {
        $service = new ForumService;
        $category = ForumCategory::findOrFail(1);
        self::assertCount(1, $service->categories());
        $category->update(['is_private' => true]);
        self::assertCount(0, $service->categories());
        $category->update(['is_private' => false]);
        $category->delete();
        self::assertCount(0, $service->categories());
        self::assertCount(1, $service->categories(deleted: true));
        $category->restore();
        self::assertCount(1, $service->categories());
        self::assertCount(0, $service->categories(deleted: true));
        $category->delete();
        $service->categories(deleted: true);
        $category->forceDelete();
        self::assertCount(0, $service->categories(deleted: true));
    }

    public function test_unread_badges_and_participation_are_isolated_between_members(): void
    {
        $service = new ForumService;
        $category = ForumCategory::findOrFail(1);
        DB::table('forum_topic_views')->insert(['topic_id' => 1, 'user_id' => 10, 'updated_at' => now()->subHours(2)]);
        $controller = new ForumController($service);
        $request = Request::create('/forum/public');
        self::assertSame(1, $controller->category($request, $category)->getData()['topics']->first()->new_replies_count);
        auth()->setUser(User::findOrFail(11));
        self::assertSame(1, $controller->category($request, $category)->getData()['topics']->first()->new_replies_count);
        self::assertNull($service->categoryTopics($category, 'latest', 1)->first()->new_replies_count);
        DB::table('forum_topics')->insert(['id' => 2, 'category_id' => 1, 'user_id' => 10, 'title' => 'Another discussion', 'slug' => 'another']);
        DB::table('forum_posts')->insert(['topic_id' => 2, 'user_id' => 10, 'body' => 'Member A only']);
        ForumService::invalidate();
        self::assertSame(2, $service->participatedTopics(10, 1)->total());
        self::assertSame(1, $service->participatedTopics(11, 1)->total());
    }

    public function test_search_refreshes_after_changes_and_view_counters_keep_cache_warm(): void
    {
        $service = new ForumService;
        $topic = ForumTopic::findOrFail(1);
        self::assertCount(1, $service->search('Discussion'));
        $topic->update(['title' => 'Renamed thread']);
        self::assertCount(0, $service->search('Discussion'));
        self::assertCount(1, $service->search('Renamed'));
        $service->topicPosts($topic, 1);
        ForumTopic::withoutTimestamps(fn () => $topic->increment('views'));
        DB::flushQueryLog();
        $service->topicPosts($topic, 1);
        self::assertCount(0, DB::getQueryLog());
    }

    public function test_post_permalink_resolves_page_without_loading_full_post_content(): void
    {
        for ($id = 3; $id <= 20; $id++) {
            DB::table('forum_posts')->insert(['id' => $id, 'topic_id' => 1, 'user_id' => 11, 'body' => 'Newer reply', 'created_at' => now()]);
        }
        $service = $this->createMock(ForumService::class);
        $service->expects(self::never())->method('topicPosts');
        $controller = new ForumController($service);
        $category = ForumCategory::findOrFail(1);
        $topic = ForumTopic::findOrFail(1);
        session(['forum_topic_view_1' => now()->timestamp]);
        $response = $controller->topic(Request::create('/forum/public/discussion', 'GET', ['post' => 2]), $category, $topic);
        self::assertStringContainsString('page=2', $response->getTargetUrl());
        self::assertStringEndsWith('#post-2', $response->getTargetUrl());
    }

    public function test_sort_and_pagination_have_separate_cache_entries(): void
    {
        $category = ForumCategory::findOrFail(1);
        $topic = ForumTopic::findOrFail(1);
        for ($id = 3; $id <= 30; $id++) {
            DB::table('forum_topics')->insert(['id' => $id, 'category_id' => 1, 'user_id' => 10, 'title' => 'Topic '.$id, 'slug' => 'topic-'.$id, 'views' => $id, 'created_at' => now()->addSeconds($id), 'updated_at' => now()->addSeconds($id)]);
            DB::table('forum_posts')->insert(['id' => $id, 'topic_id' => 1, 'user_id' => 11, 'body' => 'Reply '.$id, 'created_at' => now()->addSeconds($id)]);
        }
        $service = new ForumService;
        self::assertSame(30, $service->categoryTopics($category, 'views', 1)->first()->id);
        self::assertSame(5, $service->categoryTopics($category, 'views', 2)->first()->id);
        $firstPage = $service->topicPosts($topic, 1)['replies'];
        $secondPage = $service->topicPosts($topic, 2)['replies'];
        self::assertCount(15, $firstPage);
        self::assertNotSame($firstPage->first()->id, $secondPage->first()->id);
        self::assertSame(29, $firstPage->total());
    }

    public function test_displayed_posts_are_read_without_consuming_other_pages(): void
    {
        for ($id = 3; $id <= 20; $id++) {
            DB::table('forum_posts')->insert(['id' => $id, 'topic_id' => 1, 'user_id' => 11, 'body' => 'Reply '.$id, 'created_at' => now()->addSeconds($id)]);
        }
        $service = new ForumService;
        $topic = ForumTopic::findOrFail(1);
        $page = $service->topicPosts($topic, 1);
        $reads = new ForumReadService;
        self::assertSame(19, $reads->unread(10)->where('topic_id', 1)->count());
        $reads->markDisplayed(10, collect([$page['firstPost']])->concat($page['replies']->getCollection()));
        self::assertSame(4, $reads->unread(10)->where('topic_id', 1)->count());
        self::assertSame(2, $reads->unread(10)->where('topic_id', 1)->oldest('created_at')->oldest('id')->value('id'));
        // Recording the same page twice must be harmless.
        $reads->markDisplayed(10, $page['replies']->getCollection());
        self::assertSame(4, $reads->unread(10)->where('topic_id', 1)->count());
        $reads->markDisplayed(10, $service->topicPosts($topic, 2)['replies']->getCollection());
        self::assertSame(0, $reads->unread(10)->where('topic_id', 1)->count());
    }

    public function test_topic_creation_rolls_back_when_the_opening_post_fails(): void
    {
        auth()->user()->user_class = UserClass::ELITE_USER;
        ForumPost::creating(function ($post) {
            if ($post->body === 'Simulated failure') {
                throw new \RuntimeException('Opening post failed');
            }
        });
        try {
            (new ForumController(new ForumService))->store(Request::create('/forum/public', 'POST', [
                'title' => 'Atomic topic', 'body' => 'Simulated failure',
            ]), ForumCategory::findOrFail(1));
            self::fail('The simulated failure must abort creation.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Opening post failed', $exception->getMessage());
        } finally {
            app('events')->forget('eloquent.creating: '.ForumPost::class);
        }
        self::assertSame(1, ForumTopic::count());
        self::assertSame(2, ForumPost::count());
    }

    public function test_successful_posting_allocates_slugs_and_preserves_last_post(): void
    {
        Notification::fake();
        auth()->user()->user_class = UserClass::ELITE_USER;
        $controller = new ForumController(new ForumService);
        $category = ForumCategory::findOrFail(1);
        for ($number = 0; $number < 2; $number++) {
            $response = $controller->store(Request::create('/forum/public', 'POST', ['title' => 'Discussion', 'body' => 'Opening content']), $category);
            self::assertStringContainsString('discussion-'.($number + 2), $response->getTargetUrl());
        }
        $topic = ForumTopic::findOrFail(1);
        $reply = $controller->reply(Request::create('/forum/public/discussion/reply', 'POST', ['body' => 'Another reply']), $category, $topic);
        self::assertSame(ForumPost::max('id'), $topic->fresh()->last_post_id);
        self::assertStringContainsString('post='.$topic->fresh()->last_post_id, $reply->getTargetUrl());
        $topic->update(['is_locked' => true]);
        $before = ForumPost::count();
        try {
            $controller->reply(Request::create('/', 'POST', ['body' => 'Locked reply']), $category, $topic);
            self::fail('Locked topics cannot accept replies.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($before, ForumPost::count());
    }

    public function test_mentioned_followers_receive_one_notification_with_a_page_resolving_link(): void
    {
        Notification::fake();
        DB::table('topic_subscriptions')->insert(['user_id' => 11, 'topic_id' => 1]);
        DB::table('forum_topics')->where('id', 1)->update(['user_id' => 11]);
        Notification::fake();
        (new ForumController(new ForumService))->reply(Request::create('/', 'POST', ['body' => 'Hello @"Member B"']), ForumCategory::findOrFail(1), ForumTopic::findOrFail(1));
        $recipient = User::findOrFail(11);
        Notification::assertSentToTimes($recipient, ForumMentionNotification::class, 1);
        Notification::assertCount(1);
        Notification::assertSentTo($recipient, ForumMentionNotification::class, function ($notification) use ($recipient) {
            self::assertStringContainsString('post='.$notification->post->id, $notification->toDatabase($recipient)['url']);

            return true;
        });
    }

    public function test_reactions_and_subscriptions_are_idempotent_and_return_updated_reactors(): void
    {
        Notification::fake();
        $category = ForumCategory::findOrFail(1);
        $topic = ForumTopic::findOrFail(1);
        $post = ForumPost::findOrFail(2);
        $controller = new ForumPostLikeController;
        foreach (['love', 'laugh', 'laugh'] as $reaction) {
            $response = $controller->toggle(Request::create('/', 'POST', ['reaction' => $reaction], [], [], ['HTTP_ACCEPT' => 'application/json']), $category, $topic, $post);
            self::assertStringContainsString('data-reactions="2"', $response->getData(true)['html']);
            self::assertSame($reaction === 'love' ? 'love' : ($response->getData(true)['action'] === 'removed' ? null : 'laugh'), $response->getData(true)['user_reaction']);
        }
        self::assertSame(0, ForumPostLike::count());
        $subscriptions = new TopicSubscriptionController;
        $subscriptions->store($category, $topic);
        $subscriptions->store($category, $topic);
        self::assertSame(1, DB::table('topic_subscriptions')->count());
    }

    public function test_search_is_paginated_ranked_literal_and_keeps_private_data_separate(): void
    {
        DB::table('forum_categories')->insert(['id' => 2, 'name' => 'Staff', 'slug' => 'staff', 'is_private' => true]);
        DB::table('forum_topics')->insert(['category_id' => 2, 'user_id' => 10, 'title' => 'Needle private', 'slug' => 'private', 'created_at' => now()]);
        for ($id = 3; $id <= 37; $id++) {
            DB::table('forum_topics')->insert(['id' => $id, 'category_id' => 1, 'user_id' => 10, 'title' => $id === 3 ? 'Needle' : 'Needle '.$id, 'slug' => 'needle-'.$id, 'created_at' => now()->addSeconds($id)]);
        }
        $service = new ForumService;
        self::assertSame(35, $service->search('Needle')->total());
        self::assertSame(3, $service->search('Needle')->first()->id);
        self::assertSame(10, $service->search('Needle', 2)->count());
        self::assertSame(36, $service->search('Needle', 1, true)->total());
        self::assertSame(0, $service->search('Needle%')->total());
        self::assertCount(1, $service->categories());
        self::assertCount(2, $service->categories(includePrivate: true));
    }

    public function test_moderation_does_not_replace_last_post_activity_order(): void
    {
        DB::table('forum_topics')->insert(['id' => 2, 'category_id' => 1, 'user_id' => 10, 'title' => 'Older', 'slug' => 'older', 'last_post_id' => 3, 'created_at' => now()->subDays(2), 'updated_at' => now()->addDay()]);
        DB::table('forum_posts')->insert(['id' => 3, 'topic_id' => 2, 'user_id' => 10, 'body' => 'Older content', 'created_at' => now()->subDay()]);
        self::assertSame(1, (new ForumService)->categoryTopics(ForumCategory::findOrFail(1), 'latest', 1)->first()->id);
    }

    public function test_topic_renders_shared_components_and_marks_only_its_displayed_page(): void
    {
        $directory = sys_get_temp_dir().'/forum-render-test-'.getmypid();
        if (! is_dir($directory.'/layouts')) {
            mkdir($directory.'/layouts', 0700, true);
        }
        file_put_contents($directory.'/layouts/app.blade.php', '<!doctype html><head>@stack("styles")</head><body>@yield("content")@stack("scripts")</body>');
        config(['view.compiled' => $directory]);
        app('view')->getFinder()->prependLocation($directory);
        app('view')->share('errors', new ViewErrorBag);
        app('events')->forget('composing: layouts.app');
        try {
            for ($id = 3; $id <= 20; $id++) {
                DB::table('forum_posts')->insert(['id' => $id, 'topic_id' => 1, 'user_id' => 11, 'body' => 'Reply '.$id, 'created_at' => now()->addSeconds($id)]);
            }
            $response = (new ForumController(new ForumService))->topic(Request::create('/forum/public/discussion'), ForumCategory::findOrFail(1), ForumTopic::findOrFail(1));
            self::assertSame(200, $response->getStatusCode());
            self::assertStringContainsString('Newest replies first', $response->getContent());
            self::assertStringContainsString('forum.js', $response->getContent());
            self::assertSame(1, substr_count($response->getContent(), 'id="post-1"'));
            self::assertSame(4, (new ForumReadService)->unread(10)->where('topic_id', 1)->count());
            self::assertSame(19, (new ForumService)->topicPosts(ForumTopic::findOrFail(1), 1)['replies']->total());
        } finally {
            (new Filesystem)->deleteDirectory($directory);
        }
    }

    public function test_historical_notifications_resolve_posts_and_recheck_private_access(): void
    {
        DB::table('notifications')->insert([
            'id' => 'fixture-notification', 'type' => ForumReplyNotification::class,
            'notifiable_type' => User::class, 'notifiable_id' => 10,
            'data' => json_encode(['type' => 'forum_reply', 'topic_id' => 1, 'post_id' => 2, 'url' => '/forum/public/discussion#post-2']),
        ]);
        $action = app('router')->getRoutes()->getByName('notifications.read')->getAction('uses');
        self::assertStringContainsString('post=2', $action('fixture-notification')->getTargetUrl());
        ForumCategory::findOrFail(1)->update(['is_private' => true]);
        self::assertSame(route('notifications.index'), $action('fixture-notification')->getTargetUrl());
        auth()->user()->user_class = UserClass::MODERATOR;
        self::assertStringContainsString('post=2', $action('fixture-notification')->getTargetUrl());
    }

    public function test_notification_delivery_rechecks_current_private_category_access(): void
    {
        $category = ForumCategory::findOrFail(1);
        $category->update(['is_private' => true]);
        $notification = new ForumReplyNotification(ForumPost::findOrFail(2));
        $recipient = User::findOrFail(10);
        self::assertFalse($notification->shouldSend($recipient, 'database'));
        $recipient->user_class = UserClass::MODERATOR;
        self::assertTrue($notification->shouldSend($recipient, 'database'));
        $category->delete();
        self::assertFalse($notification->shouldSend($recipient, 'database'));
    }

    public function test_category_slug_conflicts_include_deleted_categories_and_reserved_names(): void
    {
        auth()->user()->user_class = UserClass::ADMIN;
        $controller = new ForumCategoryController;
        $category = ForumCategory::findOrFail(1);
        $category->delete();
        $response = $controller->store(Request::create('/forum/categories', 'POST', ['name' => 'Public']));
        self::assertSame(302, $response->getStatusCode());
        self::assertSame(1, ForumCategory::withTrashed()->count());
        $controller->store(Request::create('/forum/categories', 'POST', ['name' => 'Search']));
        self::assertSame('search-category', ForumCategory::where('name', 'Search')->value('slug'));
    }
}
