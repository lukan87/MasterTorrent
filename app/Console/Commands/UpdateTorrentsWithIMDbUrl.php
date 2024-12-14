<?php

namespace App\Console\Commands;

use App\Models\Torrent;
use App\Models\Genre;
use App\Services\TMDBService;
use Illuminate\Console\Command;

class UpdateTorrentsWithIMDbUrl extends Command
{
    protected $signature = 'torrents:update-imdb {--id=}';

    protected $description = 'Update torrents with IMDb URL and related TMDB data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(TMDBService $tmdbService)
    {
        $this->info('Starting torrent updates...');

        $query = Torrent::whereNotNull('imdb_url')
        ->where(function ($q) {
            $q->whereNull('poster')
              ->orWhereNull('background');
        })
        ->where('seeders', '>', 0) // Added seeders > 0 condition
        ->whereNotIn('category_id', [26,27, 28, 30, 33, 34, 42, 43, 44, 49, 51, 60]); // Exclude specified categories

        // If the "id" option is passed, filter by a specific torrent ID
        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        $torrents = $query->get();

        foreach ($torrents as $torrent) {
            $this->processTorrent($torrent, $tmdbService);
        }

        $this->info('Torrent updates completed.');
        return 0;
    }

    private function processTorrent($torrent, TMDBService $tmdbService)
    {
        $imdbUrl = $torrent->imdb_url;
        $imdbId = $tmdbId = $tmdbType = null;

        // Validate IMDb URL
        if (preg_match('/^https?:\/\/(?:www\.)?imdb\.com\/title\/(tt\d{7,8})/', $imdbUrl, $matches)) {
            $imdbId = $matches[1];
            $tmdbDetails = $tmdbService->getTMDBIdAndTypeByIMDbId($imdbId);

            if ($tmdbDetails) {
                $tmdbId = $tmdbDetails['tmdb_id'];
                $tmdbType = $tmdbDetails['type'];
                $tmdbData = $tmdbService->fetchTMDBData($tmdbId, $tmdbType);

                if ($tmdbData) {
                    // Fetch poster and background images from TMDB
                    $poster = $tmdbData['poster_path']
                        ? 'https://image.tmdb.org/t/p/w600_and_h900_bestv2' . $tmdbData['poster_path']
                        : null;

                    $background = $tmdbData['backdrop_path']
                        ? 'https://image.tmdb.org/t/p/original' . $tmdbData['backdrop_path']
                        : null;

                    // Sync genres
                    $genres = $tmdbData['genres'] ?? [];
                    $genreIds = [];
                    foreach ($genres as $genre) {
                        $genreRecord = Genre::firstOrCreate(['name' => $genre['name']]);
                        $genreIds[] = $genreRecord->id;
                    }

                    // Update the genre-torrent relationship
                    $torrent->genres()->sync($genreIds);

                    // Update torrent details
                    $torrent->update([
                        'tmdbid' => $tmdbId,
                        'imdbid' => $imdbId,
                        'tmdb_type' => $tmdbType,
                        'poster' => $poster ?? $torrent->poster, // Update only if null
                        'background' => $background ?? $torrent->background, // Update only if null
                    ]);

                    // Display a detailed message for each updated torrent
                    $this->info("Updated Torrent: {$torrent->name}");
                    $this->info("  - IMDb ID: {$imdbId}");
                    $this->info("  - TMDB ID: {$tmdbId}");
                    $this->info("  - TMDB Type: {$tmdbType}");
                    $this->info("  - Poster: " . ($poster ? 'Updated' : 'No Change'));
                    $this->info("  - Background: " . ($background ? 'Updated' : 'No Change'));
                    $this->info("  - Genres: " . implode(', ', array_column($genres, 'name')));
                }
            }
        } else {
            $this->warn("Invalid IMDb URL for torrent: {$torrent->name}");
        }
    }
}
