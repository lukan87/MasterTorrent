<?php

namespace Tests\Integration;

use App\Services\Torrent\MovieOfTheDayService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class MovieOfTheDayTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('MOTD_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set MOTD_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.motd_test' => $connection]);
        DB::setDefaultConnection('motd_test');
        DB::statement('CREATE TEMPORARY TABLE torrents (id BIGINT PRIMARY KEY, name VARCHAR(255), slug VARCHAR(255), poster VARCHAR(255), category_id BIGINT, seeders INT, leechers INT, times_completed INT, created_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, description TEXT, background VARCHAR(255))');
        DB::statement('CREATE TEMPORARY TABLE categories (id BIGINT PRIMARY KEY, name VARCHAR(255))');
        DB::table('categories')->insert(['id' => 11, 'name' => 'Movies']);
        Carbon::setTestNow('2026-10-04 12:00:00');
        $path = sys_get_temp_dir().'/motd-test-views-'.getmypid();
        @mkdir($path, 0700, true);
        config(['view.compiled' => $path]);
    }

    protected function tearDown(): void
    {
        if (getenv('MOTD_MYSQL_TEST') === '1') {
            DB::disconnect('motd_test');
            Carbon::setTestNow();
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    private function insert(int $id, array $values = []): void
    {
        DB::table('torrents')->insert($values + [
            'id' => $id, 'name' => 'Example <script>movie</script>', 'slug' => 'example',
            'poster' => 'https://fixture.test/poster.jpg', 'background' => 'https://fixture.test/backdrop.jpg',
            'category_id' => 11, 'seeders' => 10, 'leechers' => 2, 'times_completed' => 30,
            'created_at' => now()->subHours(2), 'description' => str_repeat('Large description ', 1000),
        ]);
    }

    public function test_recent_pick_uses_only_the_top_five_eligible_movies_and_lightweight_columns(): void
    {
        foreach (range(1, 6) as $id) {
            $this->insert($id, ['seeders' => $id]);
        }
        $this->insert(7, ['category_id' => 99, 'seeders' => 9999]);
        $this->insert(8, ['deleted_at' => now(), 'seeders' => 9999]);
        $this->insert(9, ['created_at' => now()->subDays(3), 'seeders' => 9999]);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $movie = (new MovieOfTheDayService)->get();
        self::assertContains($movie->id, [2, 3, 4, 5, 6]);
        self::assertSame('Movies', $movie->category->name);
        self::assertCount(2, DB::getQueryLog());
        self::assertFalse(array_key_exists('description', $movie->getAttributes()));
        self::assertFalse(array_key_exists('background', $movie->getAttributes()));
    }

    public function test_week_fallback_and_empty_selection(): void
    {
        self::assertNull((new MovieOfTheDayService)->get());
        $this->insert(1, ['created_at' => now()->subDays(3)]);
        $this->insert(2, ['created_at' => now()->subDays(8), 'seeders' => 999]);
        self::assertSame(1, (new MovieOfTheDayService)->get()->id);
    }

    public function test_feature_escapes_titles_has_one_sized_lazy_poster_and_needs_no_more_queries(): void
    {
        $this->insert(1);
        $movie = (new MovieOfTheDayService)->get();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $html = view('torrents.partials.movieoftheday', ['movieOfTheDay' => $movie])->render();
        self::assertCount(0, DB::getQueryLog());
        self::assertSame(1, substr_count($html, '<img'));
        self::assertStringContainsString('width="96" height="144"', $html);
        self::assertStringContainsString('loading="lazy"', $html);
        self::assertStringContainsString('&lt;script&gt;movie&lt;/script&gt;', $html);
        self::assertStringNotContainsString('backdrop.jpg', $html);
        self::assertStringNotContainsString('<style', $html);
        self::assertStringContainsString('View release', $html);
    }

    public function test_missing_poster_uses_an_icon_and_empty_selection_has_no_card(): void
    {
        $this->insert(1, ['poster' => null]);
        $html = view('torrents.partials.movieoftheday', ['movieOfTheDay' => (new MovieOfTheDayService)->get()])->render();
        self::assertStringContainsString('bi-film', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertSame('', trim(view('torrents.partials.movieoftheday', ['movieOfTheDay' => null])->render()));
    }
}
