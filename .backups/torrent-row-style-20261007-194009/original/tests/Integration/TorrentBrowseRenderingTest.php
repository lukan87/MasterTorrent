<?php

namespace Tests\Integration;

use App\Models\Category;
use App\Models\Torrent;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\TestCase;

class TorrentBrowseRenderingTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        // Compile synthetic views in /tmp; no real data or browser sessions are needed.
        $cache = sys_get_temp_dir().'/torrent-browse-views-'.getmypid();
        if (! is_dir($cache)) {
            mkdir($cache, 0700, true);
        }
        config(['view.compiled' => $cache]);
        \Illuminate\View\Component::flushCache();
        \Illuminate\View\Component::forgetFactory();
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    private function actor(int $rank = UserClass::USER, array $attributes = []): void
    {
        $user = new User;
        $user->forceFill($attributes + [
            'id' => 10, 'user_class' => $rank, 'downloadpos' => 'yes',
            'hit_and_run_count' => 0, 'slots' => 0,
        ]);
        Auth::guard('web')->setUser($user);
    }

    private function torrent(): Torrent
    {
        $torrent = new Torrent;
        $torrent->forceFill([
            'id' => 55, 'owner' => 30, 'slug' => 'example-torrent',
            'name' => 'Example <script>title</script>', 'size' => 1073741824,
            'created_at' => '2026-10-04 10:00:00', 'seeders' => 12345,
            'leechers' => 8, 'times_completed' => 98765, 'sticky' => 1,
            'has_downloaded' => true, 'is_seeding' => true,
        ]);
        $category = new Category;
        $category->forceFill(['id' => 1, 'name' => 'Movies', 'icon' => 'bi bi-film']);
        $torrent->setRelation('category', $category);
        $torrent->setRelation('genres', collect());
        $torrent->setRelation('uploader', null);

        return $torrent;
    }

    private function actions($seedboxes = null): string
    {
        return view('torrents.partials.index.actions', [
            'torrent' => $this->torrent(), 'seedboxes' => $seedboxes ?? collect(),
        ])->render();
    }

    public function test_members_owners_and_staff_receive_only_their_available_actions(): void
    {
        foreach ([
            [UserClass::USER, 10, false, false],
            [UserClass::USER, 30, true, false],
            [UserClass::MODERATOR, 10, true, true],
            [UserClass::ADMIN, 10, true, true],
        ] as [$rank, $id, $edit, $delete]) {
            $this->actor($rank, ['id' => $id]);
            $html = $this->actions();
            self::assertStringContainsString('tx-action-download', $html);
            self::assertSame($edit, str_contains($html, 'tx-action-edit'));
            self::assertSame($delete, str_contains($html, 'tx-action-delete'));
            self::assertStringNotContainsString('tx-action-options', $html);
            self::assertStringNotContainsString('<script>title</script>', $html);
        }
    }

    public function test_restricted_downloads_hide_slot_and_seedbox_actions_but_keep_staff_controls(): void
    {
        foreach ([['downloadpos' => 'no'], ['hit_and_run_count' => 21]] as $attributes) {
            $this->actor(UserClass::MODERATOR, $attributes + ['slots' => 2]);
            $html = $this->actions(collect([(object) ['id' => 1, 'name' => 'My seedbox']]));
            self::assertStringContainsString('tx-action-locked', $html);
            self::assertStringNotContainsString('tx-action-download', $html);
            self::assertStringNotContainsString('tx-action-options', $html);
            self::assertStringContainsString('tx-action-edit', $html);
            self::assertStringContainsString('tx-action-delete', $html);
        }
        $this->actor(UserClass::USER, ['hit_and_run_count' => 20]);
        self::assertStringContainsString('tx-action-download', $this->actions());
        Auth::guard('web')->forgetUser();
        self::assertStringContainsString('Sign in to download', $this->actions());
    }

    public function test_download_options_match_slots_and_seedboxes(): void
    {
        $this->actor(UserClass::USER, ['slots' => 1]);
        $html = $this->actions();
        self::assertStringContainsString('tx-action-options', $html);
        self::assertStringContainsString('Free Download', $html);
        self::assertStringContainsString('Double Upload', $html);
        self::assertStringNotContainsString('Send to seedbox', $html);

        $this->actor();
        $html = $this->actions(collect([(object) ['id' => 2, 'name' => 'Home seedbox']]));
        self::assertStringContainsString('tx-action-options', $html);
        self::assertStringContainsString('Send to seedbox', $html);
        self::assertStringContainsString('Home seedbox', $html);
        self::assertStringNotContainsString('Free Download', $html);
    }

    public function test_browse_rows_have_six_aligned_cells_and_an_empty_state(): void
    {
        $this->actor();
        $template = file_get_contents(__DIR__.'/../../resources/views/torrents/index.blade.php');
        $template = str_replace([
            "@extends('layouts.app')", "@section('title', 'Browse Torrents')", "@section('content')",
            '@endsection', "@include('torrents.partials.movieoftheday')", "@include('torrents.partials.indexsearch')",
        ], '', $template);

        foreach ([[ $this->torrent() ], []] as $items) {
            $html = Blade::render($template, [
                'torrents' => new LengthAwarePaginator($items, count($items), 25),
                'seedboxes' => collect(), 'movieOfTheDay' => null,
            ]);
            $document = new \DOMDocument;
            @$document->loadHTML($html);
            $xpath = new \DOMXPath($document);
            self::assertSame(6, $xpath->query('//table[contains(@class,"tx-table")]/thead/tr/th')->length);
            if ($items) {
                self::assertSame(6, $xpath->query('//table[contains(@class,"tx-table")]/tbody/tr/td')->length);
                self::assertStringContainsString('12,345', $html);
                self::assertStringContainsString('98,765', $html);
                self::assertStringContainsString('torrent-name-link--seeding', $html);
                self::assertStringNotContainsString('<script>title</script>', $html);
            } else {
                self::assertStringContainsString('No torrents found', $html);
                self::assertStringContainsString('colspan="6"', $html);
            }
        }
    }

    public function test_upload_day_separators_skip_sticky_rows_and_label_each_day_once(): void
    {
        $this->actor();
        \Illuminate\Support\Carbon::setTestNow('2026-10-07 12:00:00');
        try {
            foreach (['index' => 'torrents', 'adult' => 'adult'] as $page => $variable) {
                $template = file_get_contents(__DIR__.'/../../resources/views/torrents/'.$page.'.blade.php');
                $template = preg_replace("/@(?:extends|section)\([^\n]+\)\n|@endsection/", '', $template);
                $template = str_replace([
                    "@include('torrents.partials.movieoftheday')", "@include('torrents.partials.indexsearch')",
                    "@include('torrents.partials.adultsearch')",
                ], '', $template);
                $items = [];
                // A pinned torrent from another day must not produce a separator.
                $pinned = $this->torrent();
                $pinned->created_at = '2026-09-01 10:00:00';
                $items[] = $pinned;
                foreach (array_merge(array_fill(0, 20, '2026-10-07 10:00:00'), ['2026-10-06 10:00:00', '2026-10-04 10:00:00']) as $date) {
                    $torrent = $this->torrent();
                    $torrent->sticky = 0;
                    $torrent->created_at = $date;
                    $items[] = $torrent;
                }
                $html = Blade::render($template, [
                    $variable => new LengthAwarePaginator($items, count($items), 25),
                    'seedboxes' => collect(),
                ]);
                $document = new \DOMDocument;
                @$document->loadHTML($html);
                $xpath = new \DOMXPath($document);
                $headings = $xpath->query('//tr[@class="tx-date-row"]//time');
                self::assertCount(3, $headings);
                self::assertSame('Today', $headings->item(0)->textContent);
                self::assertSame('Yesterday', $headings->item(1)->textContent);
                self::assertSame('4 October 2026', $headings->item(2)->textContent);
                self::assertSame('2026-10-07', $headings->item(0)->getAttribute('datetime'));
                $rows = $xpath->query('//table/tbody/tr');
                self::assertStringContainsString('torrent-sticky', $rows->item(0)->getAttribute('class'));
                self::assertSame('tx-date-row', $rows->item(1)->getAttribute('class'));
                self::assertSame('tx-date-row', $rows->item(22)->getAttribute('class'));
                self::assertSame(23, $xpath->query('//tr[contains(@class,"tx-list-row")]')->length);
                self::assertSame(0, $xpath->query('//td[@data-label="Uploaded"]')->length);
                self::assertSame(6, $xpath->query('//table/thead/tr/th')->length);
            }
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

}
