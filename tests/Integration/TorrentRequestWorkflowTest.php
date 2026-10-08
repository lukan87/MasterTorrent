<?php

namespace Tests\Integration;

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TorrentRequestController;
use App\Jobs\CheckUserAchievements;
use App\Models\TorrentRequest;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TorrentRequestWorkflowTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('REQUEST_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set REQUEST_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.request_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::setDefaultConnection('request_test');
        foreach ([
            'users' => "id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT, uploadpos VARCHAR(3) DEFAULT 'no', commentblock BOOLEAN DEFAULT 0, seedbonus DECIMAL(14,2) DEFAULT 0, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL",
            'request_votes' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, request_id BIGINT, user_id BIGINT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(request_id, user_id)',
            'comments' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, commentable_id BIGINT, commentable_type VARCHAR(255), torrent_id BIGINT NULL, parent_id BIGINT NULL, comment TEXT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'comment_reactions' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, comment_id BIGINT, user_id BIGINT, reaction VARCHAR(16), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(comment_id, user_id)',
            'categories' => 'id BIGINT PRIMARY KEY, name VARCHAR(100)',
            'torrents' => 'id BIGINT PRIMARY KEY, deleted_at TIMESTAMP NULL',
            'requests' => "id BIGINT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), requested_by BIGINT NULL, category_id BIGINT, imdb_url VARCHAR(255) NULL, tmdb_url VARCHAR(255) NULL, steam_url VARCHAR(255) NULL, image VARCHAR(255) NULL, description TEXT NULL, filled VARCHAR(3) DEFAULT 'no', filled_by BIGINT NULL, link VARCHAR(255) NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL",
            'notifications' => 'id CHAR(36) PRIMARY KEY, type VARCHAR(255), notifiable_type VARCHAR(255), notifiable_id BIGINT, data TEXT, read_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(100), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'conversations' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(255), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([['id' => 2, 'name' => 'System', 'user_class' => 2], ['id' => 10, 'name' => 'Requester', 'user_class' => 2], ['id' => 20, 'name' => 'Helper', 'user_class' => 2]]);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Movies']);
        DB::table('torrents')->insert(['id' => 42]);
        Bus::fake([CheckUserAchievements::class]);
        Http::preventStrayRequests();
        $this->actor(10);
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('request_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function actor(int $id, int $rank = 2, string $uploadpos = 'no'): void
    {
        $user = new User;
        $user->forceFill(['id' => $id, 'user_class' => $rank, 'uploadpos' => $uploadpos]);
        Auth::guard('web')->setUser($user);
    }

    private function createRequest(): TorrentRequest
    {
        (new TorrentRequestController)->store(Request::create('/requests', 'POST', [
            'name' => 'A requested movie', 'category_id' => 1, 'requested_by' => 20,
        ]));

        return TorrentRequest::latest('id')->firstOrFail();
    }

    public function test_creation_uses_authenticated_owner_and_rank(): void
    {
        self::assertSame(10, (int) $this->createRequest()->requested_by);
        $this->actor(20, 1);
        try {
            $this->createRequest();
            self::fail('Basic member created a request.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
            self::assertSame(1, TorrentRequest::count());
        }
    }

    public function test_other_member_cannot_edit_update_delete_or_reopen(): void
    {
        $item = $this->createRequest();
        $this->actor(20);
        foreach (['edit', 'update', 'destroy', 'reopen'] as $action) {
            try {
                $controller = new TorrentRequestController;
                $action === 'update' ? $controller->update(Request::create('/', 'PUT'), $item->id) : $controller->$action($item->id);
                self::fail('Another member could '.$action);
            } catch (HttpException $e) {
                self::assertSame(403, $e->getStatusCode());
            }
        }
        self::assertSame(1, TorrentRequest::count());
    }

    public function test_owner_updates_and_clears_optional_fields_but_only_staff_deletes(): void
    {
        $item = $this->createRequest();
        (new TorrentRequestController)->update(Request::create('/', 'PUT', ['name' => 'Updated', 'category_id' => 1, 'image' => null, 'requested_by' => 20]), $item->id);
        self::assertSame('Updated', $item->fresh()->name);
        self::assertNull($item->fresh()->image);
        self::assertSame(10, (int) $item->fresh()->requested_by);
        try {
            (new TorrentRequestController)->destroy($item->id);
            self::fail('Owner deleted request.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        $this->actor(20, 6);
        (new TorrentRequestController)->destroy($item->id);
        self::assertSame(0, TorrentRequest::count());
    }

    public function test_fill_creates_inbox_message_and_duplicate_fill_is_rejected(): void
    {
        $item = $this->createRequest();
        $this->actor(20, 5);
        $input = Request::create('/', 'POST', ['link' => route('torrents.show', ['id' => 42])]);
        (new TorrentRequestController)->fillRequest($input, $item->id);
        self::assertSame('yes', $item->fresh()->filled);
        self::assertSame(20, (int) $item->fresh()->filled_by);
        self::assertSame(1, DB::table('conversations')->count());
        $message = DB::table('messages')->first();
        self::assertSame(10, (int) $message->receiver_id);
        self::assertSame(2, (int) $message->sender_id);
        self::assertNotNull($message->conversation_id);
        try {
            (new TorrentRequestController)->fillRequest($input, $item->id);
            self::fail('Duplicate fill accepted.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('link', $e->errors());
            self::assertSame(1, DB::table('messages')->count());
        }
        $this->actor(10);
        (new TorrentRequestController)->reopen($item->id);
        self::assertSame('no', $item->fresh()->filled);
        self::assertNull($item->fresh()->link);
        self::assertNull($item->fresh()->filled_by);
    }

    public function test_messages_in_both_directions_and_different_subjects_share_one_thread(): void
    {
        $first = SystemMessageService::send(10, 20, 'First topic', 'Hello');
        $reply = SystemMessageService::send(20, 10, 'Another topic', 'Reply');
        $next = SystemMessageService::send(10, 20, 'Third topic', 'Later message');
        self::assertSame($first->conversation_id, $reply->conversation_id);
        self::assertSame($first->conversation_id, $next->conversation_id);
        self::assertSame(1, DB::table('conversations')->count());
        self::assertSame(3, DB::table('messages')->count());
    }

    public function test_history_consolidation_preserves_messages_read_flags_and_timestamps(): void
    {
        DB::table('conversations')->insert([
            ['id' => 1, 'user_one' => 20, 'user_two' => 10, 'subject' => 'Old topic'],
            ['id' => 2, 'user_one' => 10, 'user_two' => 20, 'subject' => 'New topic'],
        ]);
        foreach ([1, 2, null] as $index => $thread) {
            DB::table('messages')->insert([
                'conversation_id' => $thread, 'sender_id' => $index === 1 ? 20 : 10,
                'receiver_id' => $index === 1 ? 10 : 20, 'subject' => 'Subject '.$index,
                'body' => 'Message '.$index, 'is_read' => $index === 0 ? 1 : 0,
                'created_at' => '2026-01-0'.($index + 1).' 12:00:00', 'updated_at' => '2026-01-05 12:00:00',
            ]);
        }
        $before = DB::table('messages')->orderBy('id')->get()->map(fn ($message) => (array) $message);
        $migration = require __DIR__.'/../../database/migrations/2026_09_29_150000_unify_conversation_participants.php';
        $migration->up();
        self::assertSame(1, DB::table('conversations')->count());
        $thread = DB::table('conversations')->first();
        self::assertSame(10, (int) $thread->user_one);
        self::assertSame(20, (int) $thread->user_two);
        self::assertSame('2026-01-03 12:00:00', $thread->last_message_at);
        foreach (DB::table('messages')->orderBy('id')->get() as $index => $message) {
            self::assertSame(1, (int) $message->conversation_id);
            $old = $before[$index];
            $actual = (array) $message;
            unset($old['conversation_id'], $actual['conversation_id']);
            self::assertSame($old, $actual);
        }
        SystemMessageService::consolidateHistory();
        self::assertSame(3, DB::table('messages')->count());
        self::assertSame(1, DB::table('conversations')->count());
        try {
            DB::table('conversations')->insert(['user_one' => 10, 'user_two' => 20]);
            self::fail('Duplicate participant pair accepted.');
        } catch (QueryException $exception) {
            self::assertSame('23000', $exception->getCode());
        }
    }

    public function test_self_filled_requests_reuse_existing_system_conversation(): void
    {
        $existing = SystemMessageService::send(2, 10, 'Welcome', 'Welcome to the site.');
        $first = $this->createRequest();
        $second = $this->createRequest();
        $this->actor(10, 5);
        foreach ([$first, $second] as $item) {
            (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => url('/torrents/42')]), $item->id);
            self::assertSame(10, (int) $item->fresh()->filled_by);
        }
        self::assertSame(1, DB::table('conversations')->count());
        $messages = DB::table('messages')->orderBy('id')->get();
        self::assertCount(3, $messages);
        foreach ($messages as $message) {
            self::assertSame(2, (int) $message->sender_id);
            self::assertSame(10, (int) $message->receiver_id);
            self::assertSame((int) $existing->conversation_id, (int) $message->conversation_id);
        }
    }

    public function test_fill_notifies_current_voters_once_and_links_to_request(): void
    {
        $item = $this->createRequest();
        foreach ([30, 40, 50] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Voter'.$id, 'user_class' => 2]);
        }
        foreach ([10, 20, 30, 40, 50] as $id) {
            DB::table('request_votes')->insert(['request_id' => $item->id, 'user_id' => $id]);
        }
        DB::table('users')->where('id', 40)->update(['deleted_at' => now()]);
        $this->actor(50);
        (new TorrentRequestController)->vote(Request::create('/', 'POST', ['voted' => 0]), $item->id);
        $this->actor(20, 5);
        $input = Request::create('/', 'POST', ['link' => url('/torrents/42')]);
        (new TorrentRequestController)->fillRequest($input, $item->id);
        self::assertSame([20, 30], DB::table('notifications')->orderBy('notifiable_id')->pluck('notifiable_id')->map(fn ($id) => (int) $id)->all());
        foreach (DB::table('notifications')->get() as $notification) {
            $data = json_decode($notification->data, true);
            self::assertSame('request_filled', $data['type']);
            self::assertSame($item->name, $data['request_name']);
            self::assertSame(route('requests.show', $item->id), $data['url']);
            self::assertStringContainsString('has been filled', $data['message']);
            self::assertNull($notification->read_at);
        }
        try {
            (new TorrentRequestController)->fillRequest($input, $item->id);
            self::fail('Duplicate fill accepted.');
        } catch (ValidationException $e) {
            self::assertSame(2, DB::table('notifications')->count());
        }
    }

    public function test_voter_notification_failure_rolls_back_request_fill(): void
    {
        $item = $this->createRequest();
        DB::table('request_votes')->insert(['request_id' => $item->id, 'user_id' => 20]);
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT reject_notice CHECK (notifiable_id = -1)');
        $this->actor(20, 5);
        try {
            (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => url('/torrents/42')]), $item->id);
            self::fail('Expected notification write to fail.');
        } catch (QueryException $e) {
            self::assertSame('no', $item->fresh()->filled);
            self::assertNull($item->fresh()->link);
            self::assertSame(0, DB::table('notifications')->count());
            self::assertSame(0, DB::table('messages')->count());
        }
    }

    public function test_invalid_external_missing_and_deleted_torrents_are_rejected(): void
    {
        $this->actor(10, 5);
        $item = $this->createRequest();
        DB::table('torrents')->insert(['id' => 43, 'deleted_at' => now()]);
        foreach (['javascript:alert(1)', 'https://example.invalid/torrents/42', url('/requests/42'), url('/torrents/999'), url('/torrents/43')] as $link) {
            try {
                (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => $link]), $item->id);
                self::fail('Invalid link accepted: '.$link);
            } catch (ValidationException $e) {
                self::assertArrayHasKey('link', $e->errors());
                self::assertSame('no', $item->fresh()->filled);
                self::assertSame(0, DB::table('messages')->count());
            }
        }
    }

    public function test_notification_failure_rolls_back_fill(): void
    {
        $this->actor(10, 5);
        $item = $this->createRequest();
        DB::statement('ALTER TABLE messages ADD CONSTRAINT reject_message CHECK (sender_id = -1)');
        try {
            (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => url('/torrents/42')]), $item->id);
            self::fail('Expected message write to fail.');
        } catch (QueryException $e) {
            self::assertSame('no', $item->fresh()->filled);
            self::assertNull($item->fresh()->link);
            self::assertSame(0, DB::table('conversations')->count());
        }
    }

    public function test_filters_and_pagination_preserve_query(): void
    {
        $item = $this->createRequest();
        DB::table('requests')->insert(['name' => 'Other release', 'requested_by' => 20, 'category_id' => 1, 'filled' => 'yes']);
        $view = (new TorrentRequestController)->index(Request::create('/requests?q=movie&status=open&mine=1&sort=oldest'));
        self::assertSame(1, $view->getData()['requests']->total());
        self::assertSame($item->id, $view->getData()['requests']->first()->id);
        self::assertSame(2, (int) $view->getData()['counts']->sum());
    }

    public function test_fill_permission_requires_uploader_rank_or_explicit_upload_permission(): void
    {
        foreach ([[1, 'no', false], [2, 'no', false], [4, 'no', false], [5, 'no', true], [6, 'no', true], [1, 'yes', true], [2, 'yes', true], [2, '1', false]] as [$rank, $permission, $allowed]) {
            $this->actor(10);
            $item = $this->createRequest();
            $this->actor(20, $rank, $permission);
            self::assertSame($allowed, TorrentRequest::canBeFilledBy(Auth::user()));
            try {
                (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => url('/torrents/42')]), $item->id);
                self::assertTrue($allowed);
                self::assertSame('yes', $item->fresh()->filled);
            } catch (HttpException $e) {
                self::assertFalse($allowed);
                self::assertSame(403, $e->getStatusCode());
                self::assertSame('no', $item->fresh()->filled);
            }
        }
        self::assertFalse(TorrentRequest::canBeFilledBy(null));
    }

    public function test_votes_are_unique_idempotent_removable_and_sortable(): void
    {
        $popular = $this->createRequest();
        $newer = $this->createRequest();
        $controller = new TorrentRequestController;
        DB::table('users')->insert(['id' => 30, 'name' => 'Another voter', 'user_class' => 2]);
        foreach ([30, 30, 20] as $actor) {
            $this->actor($actor);
            $controller->vote(Request::create('/', 'POST', ['voted' => 1, 'user_id' => 999]), $popular->id);
        }
        self::assertSame(2, $popular->votes()->count());
        $view = $controller->index(Request::create('/requests?sort=popular'));
        self::assertSame($popular->id, $view->getData()['requests']->first()->id);
        self::assertSame(1, (int) $view->getData()['requests']->first()->has_voted);
        foreach ([1, 2] as $attempt) {
            $controller->vote(Request::create('/', 'POST', ['voted' => 0]), $popular->id);
        }
        self::assertSame(1, $popular->votes()->count());
        self::assertSame(0, $newer->votes()->count());
    }

    public function test_requester_cannot_vote_and_legacy_self_votes_are_excluded(): void
    {
        $item = $this->createRequest();
        DB::table('request_votes')->insert(['request_id' => $item->id, 'user_id' => 10]);
        foreach ([2, 6] as $rank) {
            $this->actor(10, $rank);
            self::assertFalse($item->canBeVotedBy(Auth::user()));
            try {
                (new TorrentRequestController)->vote(Request::create('/', 'POST', ['voted' => 1]), $item->id);
                self::fail('Requester was allowed to vote.');
            } catch (HttpException $e) {
                self::assertSame(403, $e->getStatusCode());
            }
        }
        $list = (new TorrentRequestController)->index(Request::create('/requests'))->getData()['requests']->first();
        self::assertSame(0, (int) $list->votes_count);
        self::assertSame(0, (int) $list->has_voted);
        self::assertCount(0, $list->votes);
        $detail = (new TorrentRequestController)->show($item->id)->getData()['request'];
        self::assertSame(0, (int) $detail->votes_count);
        self::assertCount(0, $detail->votes);
        self::assertFalse($item->canBeVotedBy(null));
    }

    public function test_request_comments_replies_and_reactions_use_shared_workflow(): void
    {
        $item = $this->createRequest();
        $controller = new CommentController;
        $payload = ['commentable_type' => TorrentRequest::class, 'commentable_id' => $item->id, 'comment' => 'Please include English subtitles.'];
        $controller->store(Request::create('/', 'POST', $payload));
        $comment = $item->comments()->firstOrFail();
        self::assertSame(10, (int) $comment->user_id);
        self::assertSame(1.0, (float) DB::table('users')->where('id', 10)->value('seedbonus'));
        Bus::assertDispatched(CheckUserAchievements::class);
        $this->actor(20);
        $controller->store(Request::create('/', 'POST', $payload + ['parent_id' => $comment->id]));
        self::assertSame(1, $comment->replies()->count());
        Bus::fake([CheckUserAchievements::class]);
        $controller->react(Request::create('/', 'POST', ['reaction' => 'thanks']), $comment->id);
        self::assertSame(1, $comment->reactions()->count());
        Bus::assertDispatched(CheckUserAchievements::class);
        $controller->react(Request::create('/', 'POST', ['reaction' => 'love']), $comment->id);
        self::assertSame(1, $comment->reactions()->count());
        self::assertSame('love', $comment->reactions()->first()->reaction);
        $controller->react(Request::create('/', 'POST', ['reaction' => 'love']), $comment->id);
        self::assertSame(0, $comment->reactions()->count());
        $this->actor(10);
        try {
            $controller->react(Request::create('/', 'POST', ['reaction' => 'love']), $comment->id);
            self::fail('Self reaction accepted.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        $this->actor(20, 6);
        (new TorrentRequestController)->destroy($item->id);
        self::assertSame(0, $item->comments()->count());
    }

    public function test_all_request_pages_render_with_missing_relations_and_validation_feedback(): void
    {
        $item = $this->createRequest();
        $item->requested_by = null;
        $item->save();
        $compiler = app('blade.compiler');
        $cache = sys_get_temp_dir().'/request-blade-'.getmypid();
        if (! is_dir($cache)) {
            mkdir($cache);
        }
        config(['view.compiled' => $cache]);
        (new \ReflectionProperty($compiler, 'cachePath'))->setValue($compiler, $cache);
        app('view')->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['name' => 'Please enter a title.'])));
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['id' => 42, 'title' => 'Movie preview', 'overview' => '<script>bad()</script>', 'poster_path' => '/poster.jpg']),
            'store.steampowered.com/*' => Http::response(['123' => ['success' => true, 'data' => ['name' => 'Game preview']]]),
        ]);
        $item->update(['tmdb_url' => 'https://themoviedb.org/movie/42', 'steam_url' => 'https://store.steampowered.com/app/123']);
        for ($id = 21; $id <= 28; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Voter'.$id, 'user_class' => 2]);
            DB::table('request_votes')->insert(['request_id' => $item->id, 'user_id' => $id]);
        }
        // Insert the current user's vote last to verify that 'You' moves to the front.
        DB::table('request_votes')->insert(['request_id' => $item->id, 'user_id' => 20]);
        $this->actor(20);
        $controller = new TorrentRequestController;
        $views = [
            $controller->index(Request::create('/requests')),
            $controller->create(Request::create('/requests/create')),
            $controller->show($item->id),
        ];
        $this->actor(20, 6);
        $views[] = $controller->edit($item->id);
        try {
            foreach ($views as $view) {
                $source = str_replace("@extends('layouts.app')", '', file_get_contents($view->getPath()));
                $html = Blade::render($source."\n@yield('content')", $view->getData());
                self::assertStringContainsString('Please enter a title.', $html);
                self::assertStringContainsString('class="rq"', $html);
                if (in_array($view->name(), ['requests.show', 'requests.index'], true)) {
                    self::assertStringContainsString('You, Voter21, Voter22 and 6 others voted', $html);
                    self::assertStringContainsString('data-bs-html="false" data-bs-title="Helper, Voter21, Voter22, Voter23, Voter24, Voter25, Voter26, Voter27, Voter28"', $html);
                    $this->actor(10);
                    $otherViewer = Blade::render($source."\n@yield('content')", $view->getData());
                    self::assertStringContainsString('Voter21, Voter22, Voter23 and 6 others voted', $otherViewer);
                    $this->actor(20, 6);
                }
                if ($view->name() === 'requests.show') {
                    self::assertStringContainsString('Movie preview', $html);
                    self::assertStringContainsString('Game preview', $html);
                    self::assertStringContainsString('Mark as filled', $html);
                    self::assertStringContainsString('id="discussion"', $html);
                    self::assertStringNotContainsString('<script>bad()</script>', $html);
                    $this->actor(20, 2);
                    $restricted = Blade::render($source."\n@yield('content')", $view->getData());
                    self::assertStringNotContainsString('Mark as filled', $restricted);
                    self::assertStringContainsString('Waiting for an uploader', $restricted);
                    $this->actor(20, 6);
                }
            }
        } finally {
            foreach (glob($cache.'/*') as $file) {
                unlink($file);
            }
            rmdir($cache);
        }
    }

    public function test_legacy_unsafe_links_and_missing_requester_are_handled(): void
    {
        $this->actor(10, 5);
        $item = $this->createRequest();
        self::assertNull($item->safeUrl('javascript:alert(1)'));
        self::assertNull($item->safeUrl('data:text/html,hello'));
        self::assertFalse($item->canBeManagedBy(null));
        $item->requested_by = null;
        $item->save();
        self::assertFalse($item->canBeManagedBy(Auth::user()));
        (new TorrentRequestController)->fillRequest(Request::create('/', 'POST', ['link' => url('/torrents/42')]), $item->id);
        self::assertSame('yes', $item->fresh()->filled);
        self::assertSame(0, DB::table('messages')->count());
    }
}
