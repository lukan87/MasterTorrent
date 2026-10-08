<?php

namespace Tests\Integration;

use App\Models\Torrent;
use App\Models\User;
use App\Services\TorrentActivityNotifier;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class TorrentActivityNotificationTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('TORRENT_ACTIVITY_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set TORRENT_ACTIVITY_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        $cache = sys_get_temp_dir().'/torrent-activity-views-'.getmypid();
        if (! is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        config([
            'database.connections.torrent_activity_test' => $connection,
            'cache.default' => 'array', 'session.driver' => 'array',
            'view.compiled' => $cache, 'achievements.awarding_enabled' => false,
        ]);
        DB::setDefaultConnection('torrent_activity_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), deleted_at TIMESTAMP NULL',
            'history' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, torrent_id BIGINT, completed_at TIMESTAMP NULL',
            'notifications' => 'id CHAR(36) PRIMARY KEY, type VARCHAR(255), notifiable_type VARCHAR(255), notifiable_id BIGINT, data TEXT, read_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        foreach ([1 => 'Uploader', 2 => 'Actor', 3 => 'Downloader', 4 => 'Incomplete', 5 => 'Deleted', 6 => 'Other torrent'] as $id => $name) {
            DB::table('users')->insert(['id' => $id, 'name' => $name, 'deleted_at' => $id === 5 ? '2026-10-01 12:00:00' : null]);
        }
        foreach ([[1, 42, true], [2, 42, true], [3, 42, true], [3, 42, true], [4, 42, false], [5, 42, true], [6, 99, true]] as [$userId, $torrentId, $completed]) {
            DB::table('history')->insert(['user_id' => $userId, 'torrent_id' => $torrentId, 'completed_at' => $completed ? '2026-10-01 12:00:00' : null]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            DB::purge('torrent_activity_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function torrent(): Torrent
    {
        $torrent = new Torrent;
        $torrent->forceFill(['id' => 42, 'owner' => 1, 'name' => 'Example torrent', 'slug' => 'example-torrent']);

        return $torrent;
    }

    private function recipients(): array
    {
        return DB::table('notifications')->orderBy('notifiable_id')->pluck('notifiable_id')->map(fn ($id) => (int) $id)->all();
    }

    public function test_comment_notifies_owner_and_completed_downloaders_once_with_correct_context_and_link(): void
    {
        (new TorrentActivityNotifier)->send($this->torrent(), User::findOrFail(2), commentId: 77);
        self::assertSame([1, 3], $this->recipients());
        foreach (DB::table('notifications')->get() as $notification) {
            $data = json_decode($notification->data, true);
            self::assertSame('torrent_comment', $data['type']);
            self::assertSame((int) $notification->notifiable_id === 1 ? 'uploader' : 'downloader', $data['audience']);
            self::assertSame(2, $data['author_id']);
            self::assertSame(77, $data['comment_id']);
            self::assertSame(route('torrents.show', [42, 'example-torrent']).'#comment-77', $data['url']);
            self::assertNull($notification->read_at);
        }
    }

    public function test_reactions_include_the_reaction_and_link_to_the_reaction_control(): void
    {
        (new TorrentActivityNotifier)->send($this->torrent(), User::findOrFail(2), reaction: '👍');
        self::assertSame([1, 3], $this->recipients());
        $data = json_decode(DB::table('notifications')->where('notifiable_id', 3)->value('data'), true);
        self::assertSame('torrent_reaction', $data['type']);
        self::assertSame('👍', $data['reaction']);
        self::assertSame('downloader', $data['audience']);
        self::assertSame(route('torrents.show', [42, 'example-torrent']).'#reaction-tooltip-wrap', $data['url']);
    }

    public function test_uploaders_own_comments_still_notify_downloaders(): void
    {
        (new TorrentActivityNotifier)->send($this->torrent(), User::findOrFail(1), commentId: 78);
        self::assertSame([2, 3], $this->recipients());
    }

    public function test_missing_or_deleted_uploaders_do_not_suppress_downloader_notifications(): void
    {
        foreach ([true, false] as $deleted) {
            if ($deleted) {
                DB::table('users')->where('id', 1)->update(['deleted_at' => '2026-10-01 12:00:00']);
            } else {
                DB::table('users')->where('id', 1)->delete();
            }
            DB::table('notifications')->delete();
            (new TorrentActivityNotifier)->send($this->torrent(), User::findOrFail(2), commentId: 79);
            self::assertSame([3], $this->recipients());
        }
    }

    public function test_recipients_are_processed_across_multiple_chunks(): void
    {
        foreach (range(20, 230) as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Downloader '.$id]);
            DB::table('history')->insert(['user_id' => $id, 'torrent_id' => 42, 'completed_at' => '2026-10-01 12:00:00']);
        }
        (new TorrentActivityNotifier)->send($this->torrent(), User::findOrFail(2), reaction: '💖');
        self::assertCount(213, $this->recipients());
        self::assertContains(230, $this->recipients());
    }

    public function test_shared_display_supports_downloader_and_legacy_uploader_messages(): void
    {
        foreach (['torrent_comment', 'torrent_reaction'] as $type) {
            $data = ['type' => $type, 'author' => 'Actor', 'torrent_name' => '<Example>', 'reaction' => '👍', 'audience' => 'downloader'];
            $html = view('notifications.torrent-activity', compact('data'))->render();
            self::assertStringContainsString('a torrent you downloaded', $html);
            self::assertStringContainsString($type === 'torrent_comment' ? 'Check it out.' : 'Check their reaction.', $html);
            self::assertStringContainsString('&lt;Example&gt;', $html);
            unset($data['audience']);
            $html = view('notifications.torrent-activity', compact('data'))->render();
            self::assertStringContainsString('your torrent', $html);
            self::assertStringNotContainsString('a torrent you downloaded', $html);
        }
    }
}
