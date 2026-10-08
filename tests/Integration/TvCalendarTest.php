<?php

namespace Tests\Integration;

use App\Http\Controllers\TvCalendarController;
use App\Models\Torrent;
use App\Models\TvShowFollow;
use App\Models\User;
use App\Services\TorrentSubscriptionService;
use App\Services\TvCalendarService;
use App\Services\TvmazeService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class TvCalendarTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        if (getenv('CALENDAR_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set CALENDAR_MYSQL_TEST=1 for isolated temporary MySQL tables.');
        }
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.calendar_test' => $connection, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::setDefaultConnection('calendar_test');
        foreach ([
            'users' => 'id BIGINT PRIMARY KEY, name VARCHAR(100), deleted_at TIMESTAMP NULL',
            'torrent_subscriptions' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, imdbid VARCHAR(30), tmdbid VARCHAR(30), type VARCHAR(20), title VARCHAR(255), source_torrent_id BIGINT',
            'tv_show_follows' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id BIGINT, tvmaze_id INT, title VARCHAR(255), imdbid VARCHAR(30), tmdbid VARCHAR(30), notify_upload BOOLEAN, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, UNIQUE(user_id,tvmaze_id)',
            'torrent_series' => 'id BIGINT PRIMARY KEY, tmdbid VARCHAR(30), title VARCHAR(255)',
            'series' => 'name VARCHAR(255), id BIGINT PRIMARY KEY, imdb_id VARCHAR(30), tmdb_id VARCHAR(30), slug VARCHAR(100)',
            'torrents' => 'id BIGINT PRIMARY KEY, name VARCHAR(255), slug VARCHAR(100), imdbid VARCHAR(30), tmdbid VARCHAR(30), tmdb_type VARCHAR(20), seeders INT, times_completed INT DEFAULT 0, deleted_at TIMESTAMP NULL',
            'conversations' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, user_one BIGINT, user_two BIGINT, subject VARCHAR(255), last_message_at TIMESTAMP NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
            'messages' => 'id BIGINT AUTO_INCREMENT PRIMARY KEY, conversation_id BIGINT, sender_id BIGINT, receiver_id BIGINT, subject VARCHAR(255), body TEXT, is_read BOOLEAN DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL',
        ] as $table => $columns) {
            DB::statement("CREATE TEMPORARY TABLE {$table} ({$columns}) ENGINE=InnoDB");
        }
        DB::table('users')->insert([
            ['id' => 2, 'name' => 'System', 'deleted_at' => null],
            ['id' => 10, 'name' => 'Uploader', 'deleted_at' => null],
            ['id' => 20, 'name' => 'Member', 'deleted_at' => null],
            ['id' => 30, 'name' => 'Deleted', 'deleted_at' => now()],
        ]);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        if (getenv('CALENDAR_MYSQL_TEST') === '1') {
            DB::purge('calendar_test');
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private function follow(int $user, bool $notify = true): void
    {
        TvShowFollow::create(['user_id' => $user, 'tvmaze_id' => 10, 'title' => 'Example', 'imdbid' => 'tt123', 'tmdbid' => '55', 'notify_upload' => $notify]);
    }

    private function episode(): array
    {
        return (new TvmazeService)->normalize(
            ['id' => 100, 'name' => 'Pilot', 'airdate' => '2026-10-06', 'airtime' => '20:00', 'season' => 1, 'number' => 2],
            ['id' => 10, 'name' => 'Example <script>alert(1)</script>', 'externals' => ['imdb' => 'tt123'], 'genres' => ['Drama']]
        );
    }

    private function request(int $user, array $data = []): Request
    {
        $request = Request::create('/tv-calendar', 'GET', $data);
        $request->setUserResolver(fn () => User::findOrFail($user));

        return $request;
    }

    public function test_calendar_and_title_followers_receive_one_message_without_notifying_uploader_or_deleted_users(): void
    {
        foreach ([10, 20, 30, 999] as $id) {
            $this->follow($id);
        }
        DB::table('torrent_subscriptions')->insert(['user_id' => 20, 'imdbid' => 'tt123', 'tmdbid' => '55', 'type' => 'tv']);
        $torrent = new Torrent;
        $torrent->forceFill(['id' => 100, 'name' => 'Example S01E02', 'slug' => 'example', 'owner' => 10, 'imdbid' => 'tt123', 'tmdbid' => '55', 'tmdb_type' => 'tv']);
        $torrent->setRelation('category', null);
        self::assertSame(1, (new TorrentSubscriptionService)->notifyUpload($torrent));
        self::assertSame([20], DB::table('messages')->pluck('receiver_id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_alerts_off_and_movie_id_collisions_do_not_send_calendar_messages(): void
    {
        $this->follow(20, false);
        $torrent = new Torrent;
        $torrent->forceFill(['id' => 100, 'owner' => 10, 'imdbid' => 'tt123', 'tmdbid' => '55', 'tmdb_type' => 'tv']);
        self::assertSame(0, (new TorrentSubscriptionService)->notifyUpload($torrent));
        TvShowFollow::query()->update(['notify_upload' => true]);
        $torrent->imdbid = null;
        $torrent->tmdb_type = 'movie';
        self::assertSame(0, (new TorrentSubscriptionService)->notifyUpload($torrent));
        $torrent->tmdb_type = null;
        self::assertSame(0, (new TorrentSubscriptionService)->notifyUpload($torrent));
        self::assertSame(0, DB::table('messages')->count());
    }

    public function test_matching_is_private_excludes_deleted_uploads_and_distinguishes_episode_from_series(): void
    {
        $this->follow(20);
        DB::table('series')->insert(['id' => 7, 'imdb_id' => 'tt123', 'tmdb_id' => '55', 'slug' => 'example']);
        DB::table('torrents')->insert([
            ['id' => 1, 'name' => 'Example S01E01', 'slug' => 'one', 'imdbid' => 'tt123', 'tmdbid' => '55', 'tmdb_type' => 'tv', 'seeders' => 5, 'deleted_at' => null],
            ['id' => 2, 'name' => 'Example S01E02', 'slug' => 'two', 'imdbid' => 'tt123', 'tmdbid' => '55', 'tmdb_type' => 'tv', 'seeders' => 8, 'deleted_at' => now()],
            ['id' => 3, 'name' => 'Movie S01E02', 'slug' => 'movie', 'imdbid' => 'tt999', 'tmdbid' => '55', 'tmdb_type' => 'movie', 'seeders' => 9, 'deleted_at' => null],
        ]);
        $service = new TvCalendarService;
        $row = $service->decorate([$this->episode()], 20)[0];
        self::assertTrue($row['followed']);
        self::assertTrue($row['notify_upload']);
        self::assertFalse($row['available']);
        self::assertSame(1, $row['upload_count']);
        self::assertFalse($service->decorate([$this->episode()], 10)[0]['followed']);
        DB::table('torrents')->where('id', 2)->update(['deleted_at' => null]);
        self::assertTrue($service->decorate([$this->episode()], 20)[0]['available']);
    }

    public function test_follow_is_idempotent_and_unfollow_only_removes_the_current_members_entry(): void
    {
        Http::fake(['*' => Http::response(['id' => 10, 'name' => 'Example', 'externals' => ['imdb' => 'tt123']])]);
        $controller = new TvCalendarController;
        $request = $this->request(20, ['notify_upload' => '1']);
        $controller->follow($request, 10, new TvmazeService);
        $controller->follow($request, 10, new TvmazeService);
        self::assertSame(1, TvShowFollow::count());
        self::assertTrue(TvShowFollow::first()->notify_upload);
        $this->follow(10);
        $controller->unfollow($request, 10);
        self::assertSame([10], TvShowFollow::pluck('user_id')->map(fn ($id) => (int) $id)->all());
    }

    public function test_show_without_external_ids_can_be_followed_but_cannot_enable_upload_alerts(): void
    {
        Http::fake(['*' => Http::response(['id' => 10, 'name' => 'New show', 'externals' => ['imdb' => null]])]);
        $controller = new TvCalendarController;
        $controller->follow($this->request(20, ['notify_upload' => '1']), 10, new TvmazeService);
        self::assertSame(0, TvShowFollow::count());
        $controller->follow($this->request(20, ['notify_upload' => '0']), 10, new TvmazeService);
        self::assertSame(1, TvShowFollow::count());
        self::assertFalse(TvShowFollow::first()->notify_upload);
    }

    public function test_calendar_routes_require_authentication_and_filter_validation_rejects_invalid_dates(): void
    {
        foreach (['index', 'follow', 'unfollow'] as $action) {
            $route = app('router')->getRoutes()->getByName('tv-calendar.'.$action);
            self::assertContains('auth', $route->gatherMiddleware());
        }
        $this->expectException(ValidationException::class);
        (new TvCalendarController)->index($this->request(20, ['date' => '2026-02-31']), new TvmazeService, new TvCalendarService);
    }

    public function test_watchlist_includes_site_subscriptions_without_scheduled_episodes_and_merges_duplicates(): void
    {
        $this->follow(20, false);
        DB::table('series')->insert(['id' => 7, 'name' => 'Example', 'imdb_id' => 'tt123', 'tmdb_id' => '55', 'slug' => 'example']);
        DB::table('torrent_subscriptions')->insert([
            ['user_id' => 20, 'imdbid' => null, 'tmdbid' => '55', 'type' => 'tv', 'title' => 'Example'],
            ['user_id' => 20, 'imdbid' => 'tt123', 'tmdbid' => '55', 'type' => 'tv', 'title' => 'Duplicate'],
            ['user_id' => 20, 'imdbid' => 'tt456', 'tmdbid' => '66', 'type' => 'tv', 'title' => 'MobLand'],
            ['user_id' => 20, 'imdbid' => 'tt789', 'tmdbid' => '55', 'type' => 'movie', 'title' => 'Movie with same TMDB ID'],
            ['user_id' => 10, 'imdbid' => 'tt999', 'tmdbid' => '77', 'type' => 'tv', 'title' => 'Another member’s show'],
        ]);
        $watchlist = (new TvCalendarService)->watchlist(20);
        self::assertSame(['Example', 'MobLand'], $watchlist->pluck('title')->all());
        self::assertTrue($watchlist->first()->legacy_notify);
        self::assertTrue($watchlist->first()->notify_upload);
        self::assertSame(10, (int) $watchlist->first()->tvmaze_id);
        self::assertNull($watchlist->last()->tvmaze_id);
        self::assertSame(route('library.series.show', ['tmdbid' => '66']), $watchlist->last()->url);
        self::assertSame(0, (new TvCalendarService)->watchlist(30)->count());

        // Removing the calendar entry leaves the pre-existing site follow intact.
        (new TvCalendarController)->unfollow($this->request(20), 10);
        self::assertSame(['Example', 'MobLand'], (new TvCalendarService)->watchlist(20)->pluck('title')->all());
    }

    public function test_older_subscriptions_resolve_tv_type_and_clean_title_from_local_metadata(): void
    {
        DB::table('torrents')->insert(['id' => 1, 'name' => 'Legacy.S01E01.1080p', 'slug' => 'legacy', 'imdbid' => 'tt888', 'tmdbid' => '88', 'tmdb_type' => 'tv', 'seeders' => 1]);
        DB::table('torrent_series')->insert(['id' => 1, 'title' => 'Legacy series', 'tmdbid' => '88']);
        DB::table('torrent_subscriptions')->insert(['user_id' => 20, 'imdbid' => 'tt888', 'tmdbid' => '88', 'type' => null, 'source_torrent_id' => 1]);
        $watchlist = (new TvCalendarService)->watchlist(20);
        self::assertCount(1, $watchlist);
        self::assertSame('Legacy series', $watchlist->first()->title);
        self::assertTrue($watchlist->first()->notify_upload);
    }

    public function test_filtered_week_page_only_renders_matching_air_dates_and_keeps_private_cache_headers(): void
    {
        $this->follow(20);
        $episode = ['id' => 100, 'name' => 'Pilot', 'airdate' => '2026-10-06', 'season' => 1, 'number' => 2,
            'show' => ['id' => 10, 'name' => 'Example <script>alert(1)</script>', 'externals' => ['imdb' => 'tt123'], 'genres' => ['Drama']]];
        Http::fake(['*' => Http::response([$episode])]);
        $dir = sys_get_temp_dir().'/tv-calendar-views-'.getmypid();
        @mkdir($dir.'/layouts', 0700, true);
        @mkdir($dir.'/compiled', 0700, true);
        file_put_contents($dir.'/layouts/app.blade.php', "@stack('styles') @yield('content')");
        config(['view.compiled' => $dir.'/compiled']);
        app('view')->getFinder()->prependLocation($dir);
        app('view')->share('errors', new ViewErrorBag);
        app('events')->forget('composing: layouts.app');
        $response = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'genre' => 'Drama', 'scope' => 'followed']), new TvmazeService, new TvCalendarService);
        self::assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $data = $response->getOriginalContent()->getData();
        self::assertCount(1, $data['dates']);
        self::assertSame('2026-10-06', $data['dates']->first()->toDateString());
        self::assertSame('2026-10-05', $data['start']->toDateString());
        self::assertSame(1, $data['stats']['episodes']);
        $data['errors'] = new ViewErrorBag;
        $html = view('tv-calendar.index', $data)->render();
        self::assertStringContainsString('Example &lt;script&gt;', $html);
        self::assertStringNotContainsString('Example <script>', $html);
        self::assertStringContainsString('Alerts on', $html);
        self::assertStringContainsString('day-2026-10-06', $html);
        self::assertStringNotContainsString('day-2026-10-11', $html);
        self::assertStringNotContainsString('No episodes to show', $html);
        self::assertStringContainsString('CC BY-SA', $html);
        self::assertSame('all', $data['filters']['tab']);
        self::assertStringContainsString('All shows (worldwide)', $html);
        self::assertStringNotContainsString('id="watchlist"', $html);

        $my = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'tab' => 'my']), new TvmazeService, new TvCalendarService);
        self::assertSame('my', $my->getOriginalContent()->getData()['filters']['tab']);
        self::assertStringContainsString('id="watchlist"', $my->getContent());
        self::assertLessThan(strpos($my->getContent(), 'id="airing-schedule"'), strpos($my->getContent(), 'id="watchlist"'));
        self::assertStringContainsString('name="tab" value="my"', $my->getContent());

        // Popularity comes from site download totals, and excludes shows without uploads.
        DB::table('torrents')->insert(['id' => 99, 'name' => 'Example S01E02', 'slug' => 'example', 'imdbid' => 'tt123', 'tmdbid' => '55', 'tmdb_type' => 'tv', 'seeders' => 5, 'times_completed' => 120]);
        $popular = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'tab' => 'popular']), new TvmazeService, new TvCalendarService);
        $popularData = $popular->getOriginalContent()->getData();
        self::assertSame(1, $popularData['stats']['shows']);
        self::assertSame(120, (int) $popularData['episodes']->flatten(1)->first()['popularity']);
        self::assertStringNotContainsString('id="watchlist"', $popular->getContent());
        self::assertStringContainsString('ranked by completed downloads', $popular->getContent());

        // A title search keeps its actual air date within the selected week.
        $searched = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-08', 'q' => 'Pilot']), new TvmazeService, new TvCalendarService);
        self::assertCount(1, $searched->getOriginalContent()->getData()['dates']);
        self::assertStringContainsString('id="day-2026-10-06"', $searched->getContent());
        self::assertStringNotContainsString('id="day-2026-10-08"', $searched->getContent());

        // No matches produces one useful empty state, without empty day headings.
        $empty = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'q' => 'Missing episode']), new TvmazeService, new TvCalendarService);
        self::assertCount(0, $empty->getOriginalContent()->getData()['dates']);
        self::assertStringContainsString('No matching episodes this week', $empty->getContent());
        self::assertStringNotContainsString('class="tvc-day"', $empty->getContent());

        // Browsing the full week continues to show the complete calendar.
        $full = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'hide_daily' => '0']), new TvmazeService, new TvCalendarService);
        self::assertCount(7, $full->getOriginalContent()->getData()['dates']);
        self::assertStringContainsString('id="day-2026-10-11"', $full->getContent());

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-07 12:00:00', 'Europe/London'));
        $agenda = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-08', 'view' => 'agenda', 'hide_daily' => '0']), new TvmazeService, new TvCalendarService);
        $agendaData = $agenda->getOriginalContent()->getData();
        self::assertCount(7, $agendaData['dates']);
        self::assertSame(['2026-10-07', '2026-10-08', '2026-10-09', '2026-10-10', '2026-10-11', '2026-10-05', '2026-10-06'], $agendaData['dates']->map->toDateString()->all());
        self::assertLessThan(strpos($agenda->getContent(), 'id="day-2026-10-05"'), strpos($agenda->getContent(), 'id="day-2026-10-07"'));
        $otherWeek = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-14', 'view' => 'agenda', 'hide_daily' => '0']), new TvmazeService, new TvCalendarService);
        self::assertSame('2026-10-12', $otherWeek->getOriginalContent()->getData()['dates']->first()->toDateString());

        self::assertSame('2026-10-05', $agendaData['start']->toDateString());
        self::assertStringContainsString('tvc-agenda-row', $agenda->getContent());
        self::assertStringContainsString('Monday, 5 October', $agenda->getContent());
        self::assertStringContainsString('id="day-2026-10-11"', $agenda->getContent());
        self::assertStringContainsString('tvc-agenda-time', $agenda->getContent());
        self::assertStringContainsString('name="view"', $agenda->getContent());

        $daily = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-08', 'view' => 'day', 'q' => 'Pilot']), new TvmazeService, new TvCalendarService);
        self::assertCount(1, $daily->getOriginalContent()->getData()['dates']);
        self::assertStringContainsString('id="day-2026-10-08"', $daily->getContent());

        // Checkboxes filter both the episode lists and totals, and can be cleared.
        Cache::flush();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        $rows = [];
        foreach (['Scripted', 'Talk Show', 'Reality', 'Scripted'] as $offset => $type) {
            $row = $episode;
            $row['id'] = 200 + $offset;
            $row['show']['id'] = 10 + $offset;
            $row['show']['type'] = $type;
            $row['show']['externals']['imdb'] = $offset === 0 ? 'tt123' : 'tt'.(1000 + $offset);
            $rows[] = $row;
        }
        Http::fake(['*' => Http::response($rows)]);
        $default = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06']), new TvmazeService, new TvCalendarService);
        self::assertTrue($default->getOriginalContent()->getData()['filters']['hide_daily']);
        self::assertSame(2, $default->getOriginalContent()->getData()['stats']['episodes']);
        $hidden = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'hide_daily' => '1']), new TvmazeService, new TvCalendarService);
        self::assertSame(2, $hidden->getOriginalContent()->getData()['stats']['episodes']);
        self::assertStringContainsString('Hide daily, talk &amp; reality', $hidden->getContent());
        self::assertStringContainsString('tvc-discovery-controls', $hidden->getContent());
        $onlyFollowed = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'hide_daily' => '1', 'only_followed' => '1']), new TvmazeService, new TvCalendarService);
        self::assertSame(1, $onlyFollowed->getOriginalContent()->getData()['stats']['episodes']);
        self::assertTrue($onlyFollowed->getOriginalContent()->getData()['filters']['only_followed']);
        self::assertStringContainsString('Only followed shows', $onlyFollowed->getContent());
        $cleared = (new TvCalendarController)->index($this->request(20, ['date' => '2026-10-06', 'hide_daily' => '0', 'only_followed' => '0']), new TvmazeService, new TvCalendarService);
        self::assertSame(4, $cleared->getOriginalContent()->getData()['stats']['episodes']);

    }
}
