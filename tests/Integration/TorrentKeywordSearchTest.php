<?php

namespace Tests\Integration;

use App\Helpers\TorrentHelper;
use App\Models\Torrent;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TorrentKeywordSearchTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('TORRENT_SEARCH_MYSQL_TEST') !== '1') {
            $this->markTestSkipped('Set TORRENT_SEARCH_MYSQL_TEST=1 to use isolated temporary MySQL tables.');
        }
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $connection = config('database.connections.mysql');
        unset($connection['read'], $connection['write']);
        config(['database.connections.keyword_test' => $connection]);
        DB::setDefaultConnection('keyword_test');
        DB::statement('CREATE TEMPORARY TABLE torrents (id BIGINT PRIMARY KEY, name VARCHAR(255), imdb_url VARCHAR(255), category_id BIGINT, deleted_at TIMESTAMP NULL)');
        DB::table('torrents')->insert([
            ['id' => 31, 'name' => 'House.of.the.Dragon.S03.1080p.AMZN.WEB-DL.DDPA.5.1.H264-SPWEB', 'imdb_url' => 'https://www.imdb.com/title/tt11198330/', 'category_id' => 20],
            ['id' => 32, 'name' => 'House of Cards S01 1080p', 'imdb_url' => null, 'category_id' => 20],
            ['id' => 33, 'name' => 'House_of_the_Dragon_S02_1080p', 'imdb_url' => null, 'category_id' => 99],
            ['id' => 34, 'name' => '100% Real Movie', 'imdb_url' => null, 'category_id' => 20],
            ['id' => 35, 'name' => '1000 Real Movie', 'imdb_url' => null, 'category_id' => 20],
        ]);
    }

    protected function tearDown(): void
    {
        if (getenv('TORRENT_SEARCH_MYSQL_TEST') === '1') {
            DB::purge('keyword_test');
            restore_error_handler();
            restore_exception_handler();
        }
        parent::tearDown();
    }

    public static function titleQueries(): array
    {
        return array_map(fn ($query) => [$query], [
            'house of dragons', 'House of the Dragon', 'house.of.dragons',
            'house_of_the_dragon', 'dragon house', 'house dragon S03 1080p',
            'tt11198330', 'https://www.imdb.com/title/tt11198330/',
        ]);
    }

    private function search(string $keyword): array
    {
        $query = Torrent::query()->where('category_id', 20);
        $method = new \ReflectionMethod(TorrentHelper::class, 'applyKeywordFilter');
        $method->invoke(null, $query, Request::create('/torrents', 'GET', ['keyword' => $keyword]));

        return $query->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[DataProvider('titleQueries')]
    public function test_title_variants_and_imdb_search_find_the_release_without_bypassing_category_filters(string $keyword): void
    {
        self::assertSame([31], $this->search($keyword));
    }

    public function test_all_words_are_required_and_percent_characters_are_literal(): void
    {
        self::assertSame([], $this->search('house dragon nonexistent'));
        self::assertSame([34], $this->search('100% real'));
        self::assertSame([34], $this->search('%'));
        self::assertSame([], $this->search("' OR 1=1 --"));
    }
}
