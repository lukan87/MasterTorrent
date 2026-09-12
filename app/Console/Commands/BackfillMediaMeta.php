<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BackfillMediaMeta extends Command
{
    protected $signature = 'media:backfill {--type=all : movie|series|all} {--limit=0 : max rows to process (0 = all)}';

    protected $description = 'Backfill TMDB metadata (ratings, genres, dates) for existing movies & series so cards/lists look complete';

    private array $pulls = [];

    public function handle(): int
    {
        $apiKey = config('services.tmdb.key') ?: config('app.tmdb_api_key');

        if (in_array($this->option('type'), ['movie', 'all'], true)) {
            $this->backfill('movie', $apiKey);
        }

        if (in_array($this->option('type'), ['series', 'all'], true)) {
            $this->backfill('tv', $apiKey);
        }

        $this->info('Backfill complete. Updated: ' . json_encode($this->pulls));

        return 0;
    }

    private function backfill(string $type, string $apiKey): void
    {
        $model  = $type === 'movie' ? new Movie() : new Series();
        $increments = 0;

        $query = $model->newQuery()->whereNull('vote_average');

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $rows = $query->get();

        $this->info("Backfilling {$rows->count()} {$type} rows...");

        foreach ($rows as $row) {
            try {
                $data = Http::timeout(12)->get(
                    "https://api.themoviedb.org/3/".($type === 'movie' ? 'movie' : 'tv')."/{$row->tmdb_id}",
                    ['api_key' => $apiKey, 'language' => 'en-US', 'append_to_response' => 'external_ids']
                );

                if (!$data->successful() || empty($data['id'])) {
                    $this->warn("  skipped #{$row->id} ({$row->name}): no TMDB data");
                    continue;
                }

                $json = $data->json();
                $genres = array_values(array_filter(array_map(fn($g) => $g['name'] ?? '', $json['genres'] ?? [])));

                $payload = [
                    'vote_average' => (float) ($json['vote_average'] ?? 0) ?: null,
                    'vote_count'   => $json['vote_count'] ?? 0,
                    'genres'       => $genres,
                    'status'       => $json['status'] ?? null,
                    'tagline'      => $json['tagline'] ?? null,
                ];

                if ($type === 'movie') {
                    $payload['release_date'] = !empty($json['release_date']) ? \Carbon\Carbon::parse($json['release_date'])->format('Y-m-d') : null;
                    $payload['runtime']      = $json['runtime'] ?? null;
                } else {
                    $payload['first_air_date']     = !empty($json['first_air_date']) ? \Carbon\Carbon::parse($json['first_air_date'])->format('Y-m-d') : null;
                    $payload['number_of_seasons']  = $json['number_of_seasons'] ?? null;
                    $payload['number_of_episodes'] = $json['number_of_episodes'] ?? null;
                }

                $row->update($payload);
                $increments++;
            } catch (\Exception $e) {
                $this->warn("  error #{$row->id} ({$row->name}): {$e->getMessage()}");
            }

            // Be polite to the TMDB rate limit.
            usleep(250000);
        }

        $this->pulls[$type] = $increments;
    }
}