<?php

namespace Tests\Integration;

use App\Http\Controllers\ProfileController;
use App\Models\Comment;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserClass;
use App\Services\AchievementService;
use App\Services\ProfileService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class ProfilePerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('PROFILE_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set PROFILE_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['cache.default' => 'array', 'session.driver' => 'array', 'achievements.enabled' => false]);
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.profile_test' => $connection]);
        DB::setDefaultConnection('profile_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), user_class INT DEFAULT 1, invited_by BIGINT NULL, deleted_by BIGINT NULL, deleted_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'comments' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, comment TEXT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'torrent_thanks' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, torrent_id BIGINT',
            'forum_posts' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT',
            'torrents' => 'id BIGINT PRIMARY KEY, owner BIGINT, name VARCHAR(100), imdbid VARCHAR(100) NULL, tmdbid VARCHAR(100) NULL, tmdb_type VARCHAR(20) NULL, size BIGINT, seeders INT DEFAULT 0, leechers INT DEFAULT 0, times_completed INT DEFAULT 0, deleted_at TIMESTAMP NULL',
            'peers' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, torrent_id BIGINT, seeder BOOLEAN, active BOOLEAN DEFAULT 0',
            'history' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, seedtime BIGINT NULL',
            'users_timeline' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, staff_id BIGINT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'torrent_subscriptions' => 'id BIGINT PRIMARY KEY, user_id BIGINT, source_torrent_id BIGINT NULL, imdbid VARCHAR(100) NULL, tmdbid VARCHAR(100) NULL, title VARCHAR(100) NULL, type VARCHAR(20) NULL',
            'torrent_movies' => 'id BIGINT PRIMARY KEY, tmdbid VARCHAR(100), slug VARCHAR(100), poster_path VARCHAR(100)',
            'torrent_series' => 'id BIGINT PRIMARY KEY, tmdbid VARCHAR(100), slug VARCHAR(100), poster_path VARCHAR(100)',
            'user_achievements' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, category VARCHAR(100)',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns})");
        }
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Owner', 'invited_by' => null, 'deleted_at' => null],
            ['id' => 11, 'name' => 'Member', 'invited_by' => 10, 'deleted_at' => null],
            ['id' => 12, 'name' => 'Deleted invitee', 'invited_by' => 10, 'deleted_at' => now()],
        ]);
        DB::table('torrents')->insert([
            ['id' => 1, 'owner' => 10, 'name' => 'Release one', 'imdbid' => 'tt1', 'tmdbid' => '101', 'size' => 100, 'seeders' => 2, 'deleted_at' => null],
            ['id' => 2, 'owner' => 10, 'name' => 'Deleted release', 'imdbid' => 'tt1', 'tmdbid' => '101', 'size' => 200, 'seeders' => 99, 'deleted_at' => now()],
        ]);
        DB::table('peers')->insert([
            ['user_id' => 10, 'torrent_id' => 1, 'seeder' => 1],
            ['user_id' => 10, 'torrent_id' => 2, 'seeder' => 1],
            ['user_id' => 10, 'torrent_id' => 999, 'seeder' => 1],
            ['user_id' => 11, 'torrent_id' => 1, 'seeder' => 1],
            ['user_id' => 10, 'torrent_id' => 1, 'seeder' => 0],
        ]);
        foreach ([0, null, 21600, 86400] as $seedtime) {
            DB::table('history')->insert(['user_id' => 10, 'seedtime' => $seedtime]);
        }
        DB::table('comments')->insert([['user_id' => 10], ['user_id' => 10], ['user_id' => 11]]);
        DB::table('torrent_thanks')->insert(['user_id' => 10, 'torrent_id' => 1]);
        DB::table('forum_posts')->insert([['user_id' => 10], ['user_id' => 10]]);
        auth()->setUser(User::findOrFail(10));
        DB::enableQueryLog();
    }

    protected function tearDown(): void
    {
        if (getenv('PROFILE_MYSQL_TEST') === '1') {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::disconnect('profile_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    public function test_statistics_preserve_null_seedtime_deleted_torrents_and_dangling_peers(): void
    {
        $service = new ProfileService;
        $stats = $service->statistics(10);
        self::assertSame(2, $stats['commentCount']);
        self::assertSame(1, $stats['thanksCount']);
        self::assertSame(2, $stats['forumPostCount']);
        self::assertSame(1, $stats['torrents_count']);
        self::assertSame(2, $stats['invitees_count']);
        self::assertSame(3, $stats['activeSeeds']);
        self::assertSame(300, $stats['totalSeedSize']);
        self::assertSame(108000, $stats['totalSeedTime']);
        self::assertEquals(36000, $stats['avgSeedTime']);
        self::assertEquals(50, $stats['seedingHealth']);
        DB::flushQueryLog();
        self::assertSame($stats, $service->statistics(10));
        self::assertCount(0, DB::getQueryLog());
        self::assertSame(1, $service->statistics(11)['commentCount']);
    }

    public function test_statistics_invalidate_after_commit_but_not_rollback(): void
    {
        $service = new ProfileService;
        $service->statistics(10);
        DB::beginTransaction();
        Comment::create(['user_id' => 10, 'comment' => 'Uncommitted']);
        self::assertSame(2, $service->statistics(10)['commentCount']);
        DB::rollBack();
        DB::flushQueryLog();
        self::assertSame(2, $service->statistics(10)['commentCount']);
        self::assertCount(0, DB::getQueryLog());
        DB::beginTransaction();
        Comment::create(['user_id' => 10, 'comment' => 'Committed']);
        DB::commit();
        self::assertSame(3, $service->statistics(10)['commentCount']);
    }

    public function test_achievement_progress_is_cached_and_awards_invalidate_it(): void
    {
        $achievementService = $this->createMock(AchievementService::class);
        $achievementService->expects(self::exactly(2))->method('progress')->willReturn([]);
        app()->instance(AchievementService::class, $achievementService);
        $service = new ProfileService;
        $user = User::findOrFail(10);
        $service->achievementProgress($user);
        $service->achievementProgress($user);
        UserAchievement::create(['user_id' => 10, 'category' => 'test']);
        $service->achievementProgress($user);
    }

    public function test_invitee_reassignment_invalidates_both_invitation_trees_after_commit(): void
    {
        $service = new ProfileService;
        $service->statistics(10);
        $service->statistics(11);
        DB::transaction(fn () => User::withTrashed()->findOrFail(12)->update(['invited_by' => 11]));
        self::assertSame(1, $service->statistics(10)['invitees_count']);
        self::assertSame(1, $service->statistics(11)['invitees_count']);
    }

    public function test_controller_keeps_private_timeline_and_subscriptions_out_of_other_members_data(): void
    {
        DB::table('users_timeline')->insert(['user_id' => 10, 'staff_id' => 11, 'created_at' => now()]);
        auth()->setUser(User::findOrFail(11));
        $data = (new ProfileController)->show(10, 'Owner')->getData();
        self::assertCount(0, $data['timeline']);
        self::assertCount(0, $data['subscribedTorrents']);
        self::assertTrue($data['user']->relationLoaded('timeline'));
        self::assertCount(0, $data['user']->timeline);
        auth()->setUser(User::findOrFail(10));
        self::assertCount(1, (new ProfileController)->show(10, 'Owner')->getData()['timeline']);
        auth()->setUser(User::findOrFail(11));
        auth()->user()->user_class = UserClass::MODERATOR;
        self::assertCount(1, (new ProfileController)->show(10, 'Owner')->getData()['timeline']);
    }

    public function test_canonical_redirect_and_archived_profile_skip_statistical_work(): void
    {
        $controller = new ProfileController;
        DB::flushQueryLog();
        self::assertStringContainsString('Owner', $controller->show(10, 'Wrong name')->getTargetUrl());
        self::assertCount(1, DB::getQueryLog());
        DB::flushQueryLog();
        $view = $controller->show(12, 'Deleted invitee');
        self::assertSame('profile.show', $view->name());
        self::assertArrayNotHasKey('achievementCategories', $view->getData());
        self::assertCount(1, DB::getQueryLog());
    }

    public function test_subscription_batch_preserves_sources_fallbacks_types_and_aggregate_totals(): void
    {
        DB::table('torrents')->insert([
            ['id' => 3, 'owner' => 11, 'name' => 'Another release', 'imdbid' => 'tt1', 'tmdbid' => '101', 'tmdb_type' => 'movie', 'size' => 400, 'seeders' => 5, 'leechers' => 3, 'times_completed' => 4],
            ['id' => 4, 'owner' => 11, 'name' => 'TV release', 'imdbid' => 'tt2', 'tmdbid' => '202', 'tmdb_type' => 'tv', 'size' => 500, 'seeders' => 8, 'leechers' => 1, 'times_completed' => 2],
        ]);
        DB::table('torrent_movies')->insert(['id' => 1, 'tmdbid' => '101', 'slug' => 'movie-title', 'poster_path' => 'movie.jpg']);
        DB::table('torrent_series')->insert(['id' => 1, 'tmdbid' => '202', 'slug' => 'tv-title', 'poster_path' => 'tv.jpg']);
        DB::table('torrent_subscriptions')->insert([
            ['id' => 1, 'user_id' => 10, 'source_torrent_id' => 1, 'imdbid' => 'tt1', 'tmdbid' => '101', 'title' => 'Movie title', 'type' => 'movie'],
            ['id' => 2, 'user_id' => 10, 'source_torrent_id' => null, 'imdbid' => 'tt2', 'tmdbid' => '202', 'title' => 'TV title', 'type' => 'tv'],
            ['id' => 3, 'user_id' => 10, 'source_torrent_id' => null, 'imdbid' => null, 'tmdbid' => '404', 'title' => 'Unavailable', 'type' => 'movie'],
            ['id' => 4, 'user_id' => 10, 'source_torrent_id' => 2, 'imdbid' => 'tt1', 'tmdbid' => '101', 'title' => 'Deleted source fallback', 'type' => 'movie'],
        ]);
        $service = new ProfileService;
        DB::flushQueryLog();
        $results = $service->subscribedTorrents(10);
        self::assertLessThanOrEqual(6, count(DB::getQueryLog()));
        self::assertSame([3, 4, 1], $results->pluck('id')->all());
        $movie = $results->firstWhere('id', 1);
        self::assertSame('Movie title', $movie->name);
        self::assertSame(7, $movie->seeders);
        self::assertSame(3, $movie->leechers);
        self::assertSame(4, $movie->times_completed);
        self::assertSame('movies', $movie->library_type);
        self::assertSame('https://image.tmdb.org/t/p/w92/movie.jpg', $movie->poster);
        self::assertSame('series', $results->firstWhere('id', 4)->library_type);
        self::assertSame('tv-title', $results->firstWhere('id', 4)->library_slug);
        self::assertCount(0, $service->subscribedTorrents(11));
    }
}
