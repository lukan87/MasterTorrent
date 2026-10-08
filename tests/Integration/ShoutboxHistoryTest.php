<?php

namespace Tests\Integration;

use App\Http\Controllers\ShoutboxController;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class ShoutboxHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('CHAT_HISTORY_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set CHAT_HISTORY_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        $compiled = sys_get_temp_dir().'/chat-history-views-'.getmypid();
        @mkdir($compiled, 0700, true);
        config(['database.connections.chat_history_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array', 'view.compiled' => $compiled]);
        DB::setDefaultConnection('chat_history_test');
        DB::statement('CREATE TEMPORARY TABLE users (id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT, chatblock BOOLEAN DEFAULT 0, deleted_at TIMESTAMP NULL)');
        DB::statement('CREATE TEMPORARY TABLE shoutbox (id BIGINT PRIMARY KEY, user_id BIGINT, message TEXT, parent_id BIGINT NULL, sticky BOOLEAN DEFAULT 0, created_at TIMESTAMP, updated_at TIMESTAMP)');
        DB::table('users')->insert(['id' => 10, 'name' => 'History member', 'user_class' => 1]);
        auth()->setUser(User::findOrFail(10));
        // Tied timestamps exercise the ID tie-breaker across every page.
        for ($id = 1; $id <= 25; $id++) {
            DB::table('shoutbox')->insert(['id' => $id, 'user_id' => 10, 'message' => 'Message '.$id, 'created_at' => '2026-10-04 10:00:00', 'updated_at' => '2026-10-04 10:00:00']);
        }
        DB::table('shoutbox')->insert([
            ['id' => 26, 'user_id' => 10, 'message' => 'Pinned', 'parent_id' => null, 'sticky' => 1, 'created_at' => '2026-10-04 10:00:00', 'updated_at' => '2026-10-04 10:00:00'],
            ['id' => 27, 'user_id' => 10, 'message' => 'Reply', 'parent_id' => 15, 'sticky' => 0, 'created_at' => '2026-10-04 10:00:00', 'updated_at' => '2026-10-04 10:00:00'],
        ]);
    }

    protected function tearDown(): void
    {
        if (getenv('CHAT_HISTORY_MYSQL_TEST') === '1') {
            DB::purge('chat_history_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    public function test_history_pages_have_no_gaps_or_duplicates_and_stop_at_oldest(): void
    {
        $cursor = 26;
        $seen = [];
        foreach ([10, 10, 5] as $index => $count) {
            $response = (new ShoutboxController)->older(Request::create('/shoutbox/older', 'GET', ['before' => $cursor, 'before_time' => '2026-10-04 10:00:00']));
            $data = $response->getData(true);
            preg_match_all('/id="shout-(\d+)" class="message /', $data['html'], $matches);
            $ids = array_map('intval', $matches[1]);
            $this->assertCount($count, $ids);
            $this->assertSame($index < 2, $data['has_more']);
            $this->assertSame($count, $data['count']);
            $this->assertStringNotContainsString('Pinned', $data['html']);
            if ($index === 1) $this->assertStringContainsString('Reply', $data['html']);
            $seen = array_merge($seen, $ids);
            $cursor = min($ids);
        }
        sort($seen);
        $this->assertSame(range(1, 25), $seen);
        $data = (new ShoutboxController)->older(Request::create('/shoutbox/older', 'GET', ['before' => 1, 'before_time' => '2026-10-04 10:00:00']))->getData(true);
        $this->assertSame(['html' => '', 'has_more' => false, 'count' => 0], $data);
    }

    public function test_historical_thread_refresh_includes_replies_and_handles_deleted_threads(): void
    {
        $controller = new ShoutboxController;
        $data = $controller->poll(Request::create('/shoutbox/poll', 'GET', ['thread' => 15]))->getData(true);
        $this->assertStringContainsString('id="shout-15"', $data['html']);
        $this->assertStringContainsString('Reply', $data['html']);
        DB::table('shoutbox')->where('id', 15)->delete();
        $this->assertSame('', $controller->poll(Request::create('/shoutbox/poll', 'GET', ['thread' => 15]))->getData(true)['html']);
    }
}
