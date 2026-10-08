<?php

namespace Tests\Integration;

use App\Http\Controllers\ShoutboxController;
use App\Models\Shoutbox;
use App\Models\User;
use App\Services\ShoutMentionService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class ShoutMentionTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('SHOUT_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set SHOUT_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        restore_error_handler();
        restore_exception_handler();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.shout_test' => $connection, 'cache.default' => 'array']);
        DB::setDefaultConnection('shout_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT DEFAULT 1, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'shoutbox' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, parent_id BIGINT NULL, message TEXT, sticky BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'notifications' => 'id CHAR(36) PRIMARY KEY, type VARCHAR(255), notifiable_type VARCHAR(255), notifiable_id BIGINT, data TEXT, read_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns})");
        }
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Author'],
            ['id' => 11, 'name' => 'Alice'],
            ['id' => 12, 'name' => 'Display Name'],
        ]);
    }

    protected function tearDown(): void
    {
        if (getenv('SHOUT_MYSQL_TEST') === '1') {
            DB::disconnect('shout_test');
        }
        parent::tearDown();
    }

    private function request(string $content): Request
    {
        $request = Request::create('/shoutbox', 'POST', ['content' => $content]);
        $user = User::findOrFail(10);
        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);

        return $request;
    }

    public function test_creation_notifies_each_mentioned_member_once_and_not_the_author(): void
    {
        (new ShoutboxController)->store($this->request('Hello @Alice @Alice @Author @"Display Name" @Missing!'));
        self::assertSame(1, Shoutbox::count());
        self::assertSame(2, DB::table('notifications')->count());
        self::assertEquals([11, 12], DB::table('notifications')->orderBy('notifiable_id')->pluck('notifiable_id')->all());
        $data = json_decode(DB::table('notifications')->first()->data, true);
        self::assertSame('shout_mention', $data['type']);
        self::assertSame('Author tagged you in a shout in chat.', $data['message']);
        self::assertSame(route('home', ['shout' => 1]).'#shout-1', $data['url']);
    }

    public function test_email_addresses_and_unknown_users_do_not_notify(): void
    {
        (new ShoutboxController)->store($this->request('Email me at hello@Alice.example or ask @Missing.'));
        self::assertSame(0, DB::table('notifications')->count());
    }

    public function test_reply_mentions_link_to_the_reply_in_its_original_thread(): void
    {
        $parent = Shoutbox::create(['user_id' => 10, 'message' => 'Old thread']);
        (new ShoutboxController)->reply($this->request('Hello @Alice'), $parent->id);
        $reply = Shoutbox::latest('id')->first();
        for ($i = 0; $i < 35; $i++) {
            Shoutbox::create(['user_id' => 10, 'message' => 'Newer shout']);
        }
        $redirect = (new ShoutboxController)->show($reply);
        self::assertSame(route('home', ['shout' => $reply->id]).'#shout-'.$reply->id, $redirect->getTargetUrl());
        $service = $this->createMock(\App\Services\HomeService::class);
        $latest = Shoutbox::whereNull('parent_id')->latest('id')->take(30)->get();
        $service->method('getDashboardData')->willReturn(['messages' => $latest]);
        $request = Request::create('/', 'GET', ['shout' => $reply->id]);
        $request->setUserResolver(fn () => User::findOrFail(10));
        $view = (new \App\Http\Controllers\HomeController($service))->index($request);
        self::assertCount(30, $latest, 'Shared dashboard data must stay unchanged.');
        self::assertSame($reply->id, $view->getData()['highlightShoutId']);
        self::assertSame($parent->id, $view->getData()['messages']->firstWhere('id', $parent->id)->id);
        self::assertSame($reply->id, $view->getData()['messages']->firstWhere('id', $parent->id)->replies->first()->id);
        $data = json_decode(DB::table('notifications')->first()->data, true);
        self::assertSame(route('home', ['shout' => $reply->id]).'#shout-'.$reply->id, $data['url']);
    }

    public function test_parser_handles_punctuation_spaces_and_duplicate_names(): void
    {
        self::assertSame(['Alice', 'Display Name', 'user-name', 'user.name'], (new ShoutMentionService)->names(
            'Hi @Alice! @Alice @"Display Name" @user-name @user.name. hello@domain.com'
        ));
    }

    public function test_notification_failure_rolls_back_the_shout(): void
    {
        DB::statement('ALTER TABLE notifications DROP COLUMN data');
        try {
            (new ShoutboxController)->store($this->request('Hello @Alice'));
            self::fail('Expected a notification storage failure.');
        } catch (\Illuminate\Database\QueryException $exception) {
            self::assertSame(0, Shoutbox::count());
        }
    }
}
