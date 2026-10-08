<?php

namespace Tests\Integration;

use App\Models\Torrent;
use App\Services\TorrentSubscriptionService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class TorrentSubscriptionUploadTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('SUBSCRIPTION_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set SUBSCRIPTION_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.subscription_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::setDefaultConnection('subscription_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), deleted_at TIMESTAMP NULL',
            'tv_show_follows' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, imdbid VARCHAR(30), tmdbid VARCHAR(30), notify_upload BOOLEAN DEFAULT 0',
            'torrent_subscriptions' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, imdbid VARCHAR(30), tmdbid VARCHAR(30)',
            'conversations' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(255), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(255), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([
            ['id' => 2, 'name' => 'System', 'deleted_at' => null],
            ['id' => 10, 'name' => 'Uploader', 'deleted_at' => null],
            ['id' => 20, 'name' => 'Subscriber', 'deleted_at' => null],
            ['id' => 30, 'name' => 'Deleted member', 'deleted_at' => now()],
        ]);
    }

    protected function tearDown(): void
    {
        if (getenv('SUBSCRIPTION_MYSQL_TEST') === '1') {
            DB::purge('subscription_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    private function torrent(): Torrent
    {
        $torrent = new Torrent;
        $torrent->forceFill(['id' => 100, 'owner' => 10, 'name' => 'MobLand S01 720p', 'slug' => 'mobland-s01', 'imdbid' => 'tt100', 'tmdbid' => '200', 'size' => 1024]);
        $torrent->setRelation('category', null);

        return $torrent;
    }

    public function test_uploader_is_excluded_and_other_subscribers_receive_one_unread_message_each(): void
    {
        DB::table('torrent_subscriptions')->insert([
            ['user_id' => 10, 'imdbid' => 'tt100', 'tmdbid' => null],
            ['user_id' => 20, 'imdbid' => null, 'tmdbid' => '200'],
            ['user_id' => 20, 'imdbid' => 'tt100', 'tmdbid' => '200'],
            ['user_id' => 30, 'imdbid' => 'tt100', 'tmdbid' => null],
            ['user_id' => 999, 'imdbid' => 'tt100', 'tmdbid' => null],
        ]);
        Cache::put('user_unread_count_20', 0);
        Cache::put('user_conversations_20', []);
        self::assertSame(1, (new TorrentSubscriptionService)->notifyUpload($this->torrent()));
        self::assertSame([20], DB::table('messages')->orderBy('receiver_id')->pluck('receiver_id')->map(fn ($id) => (int) $id)->all());
        foreach (DB::table('messages')->get() as $message) {
            self::assertSame(2, (int) $message->sender_id);
            self::assertSame(0, (int) $message->is_read);
            self::assertStringContainsString('MobLand S01 720p', $message->body);
            self::assertStringContainsString(route('torrents.show', ['id' => 100, 'slug' => 'mobland-s01']), $message->body);
            self::assertNotNull($message->conversation_id);
        }
        self::assertFalse(Cache::has('user_unread_count_20'));
        self::assertFalse(Cache::has('user_conversations_20'));
    }

    public function test_a_subscribed_member_receives_a_message_when_another_member_uploads(): void
    {
        DB::table('torrent_subscriptions')->insert(['user_id' => 10, 'imdbid' => 'tt100', 'tmdbid' => null]);
        $service = new TorrentSubscriptionService;
        $torrent = $this->torrent();
        self::assertSame(0, $service->notifyUpload($torrent));
        $torrent->owner = 20;
        self::assertSame(1, $service->notifyUpload($torrent));
        self::assertSame(10, (int) DB::table('messages')->value('receiver_id'));
    }

    public function test_unrelated_subscriptions_and_missing_metadata_produce_no_messages(): void
    {
        DB::table('torrent_subscriptions')->insert(['user_id' => 10, 'imdbid' => 'ttOther', 'tmdbid' => '999']);
        self::assertSame(0, (new TorrentSubscriptionService)->notifyUpload($this->torrent()));
        $torrent = $this->torrent();
        $torrent->imdbid = null;
        $torrent->tmdbid = null;
        self::assertSame(0, (new TorrentSubscriptionService)->notifyUpload($torrent));
        self::assertSame(0, DB::table('messages')->count());
    }
}
