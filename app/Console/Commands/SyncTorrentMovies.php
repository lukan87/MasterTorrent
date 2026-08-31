<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Torrent;
use App\Models\TorrentMovie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncTorrentMovies extends Command
{
    protected $signature = 'torrent:sync-movies {--force}';
    protected $description = 'Sync TMDB data into torrent_movies table';

    public function handle()
    {
        $this->info('Starting sync...');

        $query = Torrent::whereNotNull('tmdbid')
            ->where('tmdb_type', 'movie')
            ->select('tmdbid')
            ->distinct();

        // Skip already synced unless --force
        if (!$this->option('force')) {
            $existing = TorrentMovie::pluck('tmdbid')->toArray();
            $query->whereNotIn('tmdbid', $existing);
        }

        $tmdbids = $query->pluck('tmdbid');

        $this->info("Found {$tmdbids->count()} movies to sync.");

        $bar = $this->output->createProgressBar($tmdbids->count());
        $bar->start();

        foreach ($tmdbids as $tmdbid) {

        $exists = TorrentMovie::where('tmdbid', $tmdbid)->exists();

// 🚫 Skip if exists and not forcing
if ($exists && !$this->option('force')) {
    $bar->advance();
    continue;
}

            try {
                $response = Http::get("https://api.themoviedb.org/3/movie/{$tmdbid}", [
                    'api_key' => config('services.tmdb.key'),
                ]);

                if (!$response->successful()) {
                    $this->warn("Failed: {$tmdbid}");
                    continue;
                }

                $tmdb = $response->json();

                if (!isset($tmdb['title'])) {
                    continue;
                }

                $title = $tmdb['title'];

                $year = null;

if (!empty($tmdb['release_date'])) {
    $year = (int) substr($tmdb['release_date'], 0, 4);
}

                TorrentMovie::updateOrCreate(
                    ['tmdbid' => $tmdbid],
                    [
                        'title'         => $title,
                        'slug'          => Str::slug($title),
                        'poster_path'   => $tmdb['poster_path'] ?? null,
                        'backdrop_path' => $tmdb['backdrop_path'] ?? null,
                        'rating'        => $tmdb['vote_average'] ?? null,
                        'year' => $year,
                    ]
                );

                // 💤 avoid rate limit
                usleep(200000); // 0.2 sec

            } catch (\Exception $e) {
                $this->error("Error syncing {$tmdbid}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info("\nSync completed!");
    }
}