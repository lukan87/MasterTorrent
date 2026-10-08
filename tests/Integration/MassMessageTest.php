<?php

namespace Tests\Integration;

use App\Http\Controllers\Admin\MassMessageController;
use App\Http\Controllers\Admin\MessagesController;
use App\Jobs\SendMassMessageJob;
use App\Models\Conversation;
use App\Models\MassMessage;
use App\Models\Message;
use App\Models\User;
use App\Models\UserClass;
use App\Services\SystemMessageService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Component;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

/** All database writes use connection-local temporary tables. */
class MassMessageTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('MASS_MESSAGE_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set MASS_MESSAGE_MYSQL_TEST=1 to use isolated MySQL temporary tables.');
        }
        Component::flushCache();
        Component::forgetFactory();
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.mass_message_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array', 'queue.default' => 'sync']);
        DB::setDefaultConnection('mass_message_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT, deleted_at TIMESTAMP NULL',
            'conversations' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(100), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(100), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'mass_messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, actor_id BIGINT, actor_name VARCHAR(255), sender_id BIGINT, sender_name VARCHAR(255), send_as_system BOOLEAN, subject VARCHAR(255), body TEXT, user_classes JSON, status VARCHAR(20), completed_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'mass_message_deliveries' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, mass_message_id BIGINT, receiver_id BIGINT, receiver_name VARCHAR(255), message_id BIGINT NULL, status VARCHAR(20), was_read BOOLEAN NULL, delivered_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(mass_message_id, receiver_id)',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([
            ['id' => 2, 'name' => 'System', 'user_class' => UserClass::ADMIN, 'deleted_at' => null],
            ['id' => 10, 'name' => 'Admin', 'user_class' => UserClass::ADMIN, 'deleted_at' => null],
            ['id' => 20, 'name' => 'Recipient', 'user_class' => UserClass::USER, 'deleted_at' => null],
            ['id' => 30, 'name' => 'Deleted', 'user_class' => UserClass::USER, 'deleted_at' => '2026-10-01 00:00:00'],
        ]);
        $this->actor();
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('mass_message_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function actor(int $rank = UserClass::ADMIN): void
    {
        Auth::guard('web')->setUser((new User)->forceFill(['id' => 10, 'name' => 'Admin', 'user_class' => $rank]));
    }

    private function send(array $changes = []): MassMessage
    {
        (new MassMessageController)->store(Request::create('/admin/users/mass-messages', 'POST', $changes + [
            'subject' => 'Community update', 'message' => 'Hello <script>alert(1)</script>', 'user_class' => [UserClass::USER],
        ]));

        return MassMessage::latest('id')->firstOrFail();
    }

    public function test_system_and_user_broadcasts_preserve_actor_and_preview_matches_delivery(): void
    {
        $preview = (new MassMessageController)->preview(Request::create('/', 'POST', ['user_class' => [UserClass::USER]]));
        self::assertSame(1, $preview->getData()->recipients);
        foreach ([['send_as_system' => '1'], [], ['send_as_system' => '0']] as $option) {
            $broadcast = $this->send($option);
            $expected = ($option['send_as_system'] ?? '0') === '1' ? 2 : 10;
            self::assertSame(10, $broadcast->actor_id);
            self::assertSame('Admin', $broadcast->actor_name);
            self::assertSame($expected, $broadcast->sender_id);
            self::assertSame('sent', $broadcast->status);
            self::assertNotNull($broadcast->completed_at);
            self::assertCount(1, $broadcast->deliveries);
            self::assertSame($expected, $broadcast->deliveries[0]->message->sender_id);
            self::assertSame(20, $broadcast->deliveries[0]->message->receiver_id);
        }
    }

    public function test_pending_cancellation_and_retried_jobs_do_not_duplicate_delivery(): void
    {
        Queue::fake();
        $broadcast = $this->send();
        Queue::assertPushed(SendMassMessageJob::class);
        DB::table('users')->where('id', 20)->update(['user_class' => UserClass::ADMIN]);
        $job = new SendMassMessageJob($broadcast->body, $broadcast->user_classes, 10, $broadcast->id);
        $job->handle();
        // Audience remains the original account even after a class change.
        self::assertSame(1, Message::count());
        $job->handle();
        self::assertSame(1, Message::count());

        DB::table('users')->where('id', 20)->update(['user_class' => UserClass::USER]);
        $pending = $this->send();
        (new MassMessageController)->destroyDelivery($pending, $pending->deliveries()->firstOrFail());
        (new SendMassMessageJob($pending->body, $pending->user_classes, 10, $pending->id))->handle();
        self::assertSame(1, Message::count());
        self::assertSame('removed', $pending->deliveries()->first()->status);
    }

    public function test_read_filters_detail_rendering_and_copy_deletion_preserve_audit(): void
    {
        $broadcast = $this->send(['send_as_system' => '1']);
        $delivery = $broadcast->deliveries()->firstOrFail();
        $controller = new MassMessageController;
        $view = $controller->show(Request::create('/'), $broadcast);
        self::assertFalse($delivery->message->fresh()->is_read);
        self::assertSame(0, $view->getData()['massMessage']->read_count);
        $delivery->message->update(['is_read' => true]);
        $view = $controller->show(Request::create('/', 'GET', ['receipt' => 'read']), $broadcast);
        self::assertSame(1, $view->getData()['massMessage']->read_count);
        self::assertSame(1, $view->getData()['deliveries']->total());
        $html = $this->render('show', $view->getData());
        self::assertStringContainsString('Admin', $html);
        self::assertStringContainsString('System', $html);
        self::assertStringContainsString('Recipient', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        $controller->destroyDelivery($broadcast, $delivery);
        self::assertSame(0, Message::count());
        self::assertSame('removed', $delivery->fresh()->status);
        self::assertTrue($delivery->fresh()->was_read);
        self::assertSame('Recipient', $delivery->fresh()->receiver_name);
        self::assertSame(1, MassMessage::withDeliveryCounts()->first()->read_count);
        self::assertSame(1, MassMessage::withDeliveryCounts()->first()->removed_count);
    }

    public function test_broadcast_delete_preserves_unrelated_messages_and_stops_queued_job(): void
    {
        $broadcast = $this->send();
        $other = $this->send(['subject' => 'Other broadcast']);
        (new MassMessageController)->destroy($broadcast);
        self::assertNull(MassMessage::find($broadcast->id));
        self::assertSame(1, Message::count());
        self::assertSame('Other broadcast', Message::first()->subject);
        (new SendMassMessageJob($broadcast->body, $broadcast->user_classes, 10, $broadcast->id))->handle();
        self::assertSame(1, Message::count());
        self::assertNotNull($other->fresh());
    }

    public function test_deletion_requires_admin_and_recipient_must_belong_to_broadcast(): void
    {
        $broadcast = $this->send();
        $other = $this->send();
        try {
            (new MassMessageController)->destroyDelivery($broadcast, $other->deliveries()->firstOrFail());
            self::fail('Cross-broadcast deletion must fail');
        } catch (HttpException $exception) {
            self::assertSame(404, $exception->getStatusCode());
        }
        $this->actor(UserClass::MODERATOR);
        try {
            (new MassMessageController)->destroy($broadcast);
            self::fail('Moderator deletion must fail');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame(2, Message::count());
    }

    public function test_empty_audience_and_invalid_sender_are_rejected_without_delivery(): void
    {
        foreach ([['user_class' => []], ['send_as_system' => 'bad'], ['user_class' => [999]], ['subject' => str_repeat('a', 101)]] as $changes) {
            try {
                $this->send($changes);
                self::fail('Invalid broadcast must fail');
            } catch (ValidationException $exception) {
                self::assertNotEmpty($exception->errors());
            }
        }
        self::assertSame(0, MassMessage::count());
        self::assertSame(0, Message::count());
    }

    public function test_deleted_recipient_is_skipped_and_failure_is_logged(): void
    {
        Queue::fake();
        $broadcast = $this->send();
        DB::table('users')->where('id', 20)->update(['deleted_at' => now()]);
        $job = new SendMassMessageJob($broadcast->body, $broadcast->user_classes, 10, $broadcast->id);
        $job->handle();
        self::assertSame('skipped', $broadcast->deliveries()->first()->status);
        self::assertSame('sent', $broadcast->fresh()->status);
        self::assertSame(0, Message::count());
        $broadcast->update(['status' => 'sending']);
        $job->failed(new \RuntimeException('Queue failed'));
        self::assertSame('failed', $broadcast->fresh()->status);
    }

    public function test_large_broadcast_uses_continuations_and_resumes_a_failed_job(): void
    {
        Queue::fake();
        $users = [];
        for ($id = 100; $id < 305; $id++) {
            $users[] = ['id' => $id, 'name' => 'Member '.$id, 'user_class' => UserClass::USER, 'deleted_at' => null];
        }
        DB::table('users')->insert($users);
        $broadcast = $this->send();
        $job = new SendMassMessageJob($broadcast->body, $broadcast->user_classes, 10, $broadcast->id);
        $job->handle();
        self::assertSame(200, Message::count());
        self::assertSame(6, $broadcast->deliveries()->where('status', 'pending')->count());
        Queue::assertPushed(SendMassMessageJob::class, 2);
        $job->failed(new \RuntimeException('Simulated failure'));
        self::assertSame('failed', $broadcast->fresh()->status);
        $job->handle();
        self::assertSame(206, Message::count());
        self::assertSame('sent', $broadcast->fresh()->status);
        $job->handle();
        self::assertSame(206, Message::count());
    }

    public function test_composer_and_history_render_with_filters_and_empty_history(): void
    {
        $controller = new MassMessageController;
        $html = $this->render('create', $controller->create()->getData());
        self::assertStringContainsString('Send as System', $html);
        self::assertStringContainsString('user_class[]', $html);
        $html = $this->render('index', $controller->index(Request::create('/'))->getData());
        self::assertStringContainsString('Your next announcement starts here', $html);
        $this->send();
        $this->send(['send_as_system' => '1']);
        $view = $controller->index(Request::create('/', 'GET', ['sender' => 'system']));
        self::assertSame(1, $view->getData()['broadcasts']->total());
        $html = $this->render('index', $view->getData());
        self::assertStringContainsString('Community update', $html);
    }

    public function test_mass_pill_counts_recipients_and_bulk_delete_keeps_normal_replies(): void
    {
        DB::table('users')->insert(['id' => 40, 'name' => 'Second recipient', 'user_class' => UserClass::USER]);
        $broadcast = $this->send();
        $message = Message::with('massDelivery.massMessage')->where('receiver_id', 20)->firstOrFail();
        self::assertSame(2, $message->massDelivery->massMessage->delivered_count);
        $html = view('messages.mass-pill', ['message' => $message, 'showMassActions' => true])->render();
        self::assertStringContainsString('Mass message · sent to 2 users', $html);
        self::assertStringContainsString('Delete all recipient copies', $html);
        $this->actor(UserClass::USER);
        $html = view('messages.mass-pill', ['message' => $message, 'showMassActions' => true])->render();
        self::assertStringNotContainsString('Delete all recipient copies', $html);
        $this->actor();
        $reply = SystemMessageService::send(20, 10, 'Reply', 'Normal reply to keep');
        (new MassMessageController)->destroy($broadcast);
        self::assertNotNull($reply->fresh());
        self::assertSame(1, Message::count());
        self::assertSame(1, Conversation::count());
    }

    public function test_direct_message_deletion_preserves_delivery_read_audit_and_removes_empty_thread(): void
    {
        $broadcast = $this->send();
        $delivery = $broadcast->deliveries()->firstOrFail();
        $message = $delivery->message;
        $message->update(['is_read' => true]);
        $threadId = $message->conversation_id;
        $message->delete();
        self::assertNull(Conversation::find($threadId));
        self::assertNull($delivery->fresh()->message_id);
        self::assertTrue($delivery->fresh()->was_read);
        self::assertSame('removed', $delivery->fresh()->status);
        self::assertSame(1, MassMessage::withDeliveryCounts()->first()->read_count);
    }

    public function test_admin_message_type_filter_uses_delivery_links_instead_of_subject_text(): void
    {
        $this->send(['subject' => 'Custom broadcast title']);
        $normal = SystemMessageService::send(10, 20, 'Mass Message', 'A normal message with a misleading subject');
        $controller = new MessagesController;
        $mass = $controller->index(Request::create('/', 'GET', ['type' => 'mass']))->getData()['messages'];
        $regular = $controller->index(Request::create('/', 'GET', ['type' => 'normal']))->getData()['messages'];
        self::assertSame(1, $mass->total());
        self::assertSame('Custom broadcast title', $mass->first()->subject);
        self::assertSame(1, $regular->total());
        self::assertSame($normal->id, $regular->first()->id);
        $detail = $controller->show($mass->first());
        $template = file_get_contents(__DIR__.'/../../resources/views/admin/messages/show.blade.php');
        $template = str_replace(["@extends('layouts.admin')", "@section('admin-content')", '@endsection'], '', $template);
        $html = Blade::render($template, $detail->getData());
        self::assertStringContainsString('Mass message · sent to 1 user', $html);
        self::assertStringContainsString('Delete all recipient copies', $html);

    }

    private function render(string $view, array $data): string
    {
        $template = file_get_contents(__DIR__.'/../../resources/views/admin/mass-messages/'.$view.'.blade.php');
        $template = str_replace(["@extends('layouts.admin')", "@section('admin-content')", '@endsection'], '', $template);

        return Blade::render($template, $data);
    }
}
