<?php

namespace Tests\Integration;

use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserClass;
use App\Services\SystemMessageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessagingTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('MESSAGING_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set MESSAGING_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.messaging_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::setDefaultConnection('messaging_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT, deleted_at TIMESTAMP NULL',
            'conversations' => 'id BIGINT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(100), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(100), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'mass_messages' => 'id BIGINT PRIMARY KEY, subject VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'mass_message_deliveries' => 'id BIGINT PRIMARY KEY, mass_message_id BIGINT, receiver_id BIGINT, message_id BIGINT NULL, status VARCHAR(20), was_read BOOLEAN NULL, delivered_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([['id' => 10, 'name' => 'Sender', 'user_class' => 1], ['id' => 20, 'name' => 'Recipient', 'user_class' => 1]]);
        DB::table('conversations')->insert(['id' => 1, 'user_one' => 10, 'user_two' => 20, 'last_message_at' => '2026-10-03 12:00:00']);
        DB::table('messages')->insert([
            ['id' => 1, 'conversation_id' => 1, 'sender_id' => 10, 'receiver_id' => 20, 'body' => 'First', 'is_read' => 0, 'created_at' => '2026-10-02 12:00:00'],
            ['id' => 2, 'conversation_id' => 1, 'sender_id' => 20, 'receiver_id' => 10, 'body' => 'Reply', 'is_read' => 0, 'created_at' => '2026-10-03 12:00:00'],
        ]);
        $this->actor(10);
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('messaging_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function actor(int $id, int $rank = UserClass::USER): void
    {
        $user = new User;
        $user->forceFill(['id' => $id, 'user_class' => $rank]);
        Auth::guard('web')->setUser($user);
    }

    public function test_only_recipient_opening_thread_marks_displayed_incoming_messages_read(): void
    {
        $controller = new ConversationController;
        $controller->show(Request::create('/messages/1'), '1');
        self::assertFalse(Message::find(1)->is_read);
        self::assertTrue(Message::find(2)->is_read);
        $this->actor(20);
        $controller->show(Request::create('/messages/1'), '1');
        self::assertTrue(Message::find(1)->is_read);
    }

    public function test_loading_recent_messages_does_not_mark_older_unseen_messages_read(): void
    {
        $this->actor(20);
        for ($id = 3; $id <= 202; $id++) {
            DB::table('messages')->insert(['id' => $id, 'conversation_id' => 1, 'sender_id' => 10, 'receiver_id' => 20, 'body' => 'Recent', 'created_at' => '2026-10-03 12:00:00']);
        }
        $view = (new ConversationController)->show(Request::create('/messages/1'), '1');
        self::assertTrue($view->getData()['hasOlder']);
        self::assertFalse(Message::find(1)->is_read);
        self::assertTrue(Message::find(202)->is_read);
        (new ConversationController)->show(Request::create('/messages/1', 'GET', ['before' => 3]), '1');
        self::assertTrue(Message::find(1)->is_read);
    }

    public function test_messenger_renders_receipts_and_deletion_for_admin_only(): void
    {
        $template = file_get_contents(__DIR__.'/../../resources/views/messages/index.blade.php');
        $template = str_replace(["@extends('layouts.app')", "@section('content')", '@endsection'], '', $template);
        foreach ([UserClass::USER, UserClass::ADMIN] as $rank) {
            $this->actor(10, $rank);
            $view = (new ConversationController)->show(Request::create('/messages/1'), '1');
            $html = Blade::render($template, $view->getData() + ['errors' => new ViewErrorBag]);
            self::assertStringContainsString('data-receipt="1"', $html);
            self::assertStringContainsString('messenger.css', $html);
            self::assertSame($rank >= UserClass::ADMIN, str_contains($html, 'ms-msg-action delete-msg'));
        }
    }

    public function test_receipts_are_private_and_polling_does_not_mark_messages_read(): void
    {
        $controller = new ConversationController;
        $request = Request::create('/messages/1/receipts', 'GET', ['ids' => [1, 2, 999]]);
        $response = $controller->receipts($request, Conversation::find(1));
        self::assertCount(2, $response->getData()->messages);
        self::assertFalse($response->getData()->messages[0]->is_read);
        self::assertFalse(Message::find(1)->is_read);
        $this->actor(30, UserClass::ADMIN);
        try {
            $controller->receipts($request, Conversation::find(1));
            self::fail('Nonparticipants must not see receipts');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_members_and_moderators_cannot_delete_even_their_own_messages(): void
    {
        foreach ([UserClass::USER, UserClass::MODERATOR] as $rank) {
            $this->actor(10, $rank);
            try {
                (new MessageController)->delete(1);
                self::fail('Deletion must require Admin');
            } catch (HttpException $exception) {
                self::assertSame(403, $exception->getStatusCode());
                self::assertNotNull(Message::find(1));
            }
        }
    }

    public function test_admin_and_above_can_delete_received_messages_and_refresh_inboxes(): void
    {
        foreach ([UserClass::ADMIN, UserClass::OWNER, UserClass::WEB_DEVELOPER] as $rank) {
            $this->actor(10, $rank);
            foreach ([10, 20] as $id) {
                Cache::put("user_unread_count_{$id}", 1);
                Cache::put("user_conversations_{$id}", 'stale');
            }
            self::assertTrue((new MessageController)->delete(2)->getData()->success);
            self::assertNull(Message::find(2));
            self::assertSame('2026-10-02 12:00:00', Conversation::find(1)->last_message_at);
            foreach ([10, 20] as $id) {
                self::assertNull(Cache::get("user_unread_count_{$id}"));
                self::assertNull(Cache::get("user_conversations_{$id}"));
            }
            DB::table('messages')->insert(['id' => 2, 'conversation_id' => 1, 'sender_id' => 20, 'receiver_id' => 10, 'body' => 'Reply', 'created_at' => '2026-10-03 12:00:00']);
        }
    }

    public function test_admin_panel_single_and_bulk_deletion_reject_moderators(): void
    {
        $this->actor(10, UserClass::MODERATOR);
        foreach (['destroy', 'bulk'] as $method) {
            try {
                $controller = new MessagesController;
                $method === 'destroy'
                    ? $controller->destroy(Message::find(1))
                    : $controller->bulk(Request::create('/admin/messages/bulk', 'POST', ['action' => 'delete', 'ids' => [1]]));
                self::fail('Moderator must not delete');
            } catch (HttpException $exception) {
                self::assertSame(403, $exception->getStatusCode());
                self::assertSame(2, Message::count());
            }
        }
    }

    public function test_edit_returns_formatted_content_and_rejects_other_sender(): void
    {
        $response = (new MessageController)->edit(Request::create('/messages/edit/1', 'POST', ['body' => '[b]Updated[/b]']), 1);
        self::assertSame('[b]Updated[/b]', $response->getData()->body);
        self::assertStringContainsString('Updated', $response->getData()->html);
        $this->actor(20);
        try {
            (new MessageController)->edit(Request::create('/messages/edit/1', 'POST', ['body' => 'Changed']), 1);
            self::fail('Only the sender may edit');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_deleting_the_last_message_removes_the_thread_and_clears_both_users_caches(): void
    {
        SystemMessageService::deleteMessage(Message::findOrFail(2));
        self::assertNotNull(Conversation::find(1));
        foreach ([10, 20] as $id) {
            Cache::put("user_conversations_{$id}", 'stale');
        }
        // Direct Eloquent deletions must behave the same as the admin service.
        Message::findOrFail(1)->delete();
        self::assertNull(Conversation::find(1));
        foreach ([10, 20] as $id) {
            self::assertNull(Cache::get("user_conversations_{$id}"));
        }
    }

    public function test_raw_database_deletions_do_not_create_phantom_pagination_and_pruning_removes_orphans(): void
    {
        for ($id = 2; $id <= 30; $id++) {
            DB::table('conversations')->insert(['id' => $id, 'user_one' => 10, 'user_two' => 20]);
        }
        $controller = new ConversationController;
        $list = $controller->index(Request::create('/messages'))->getData()['conversations'];
        self::assertSame(1, $list->total());
        self::assertFalse($list->hasPages());
        DB::table('messages')->delete();
        $list = $controller->index(Request::create('/messages'))->getData()['conversations'];
        self::assertSame(0, $list->total());
        self::assertSame(30, SystemMessageService::pruneEmptyConversations());
        self::assertSame(0, Conversation::count());
    }

    public function test_conversation_deletion_clears_both_participants_and_preserves_unrelated_threads(): void
    {
        DB::table('conversations')->insert(['id' => 2, 'user_one' => 20, 'user_two' => 30]);
        DB::table('messages')->insert(['id' => 3, 'conversation_id' => 2, 'sender_id' => 20, 'receiver_id' => 30, 'body' => 'Keep']);
        foreach ([10, 20] as $id) {
            Cache::put("user_conversations_{$id}", 'stale');
        }
        (new MessageController)->destroyConversation('1');
        self::assertNull(Conversation::find(1));
        self::assertNotNull(Conversation::find(2));
        self::assertSame(1, Message::count());
        foreach ([10, 20] as $id) {
            self::assertNull(Cache::get("user_conversations_{$id}"));
        }
    }
}
