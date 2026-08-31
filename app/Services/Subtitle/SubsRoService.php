<?php

namespace App\Services\Subtitle;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class SubsRoService
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.subs.ro/v1.0';

        $this->apiKey = env('SUBSRO_API_KEY');
    }

    public function search(string $imdbId): array
    {
        try {

            if (!str_starts_with($imdbId, 'tt')) {
                $imdbId = 'tt' . $imdbId;
            }

            return Cache::remember(
                "subsro_{$imdbId}",
                now()->addDays(3),
                function () use ($imdbId) {

                    $response = Http::timeout(20)
                        ->withHeaders([
                            'X-Subs-Api-Key' => $this->apiKey,
                            'Content-Type'   => 'application/json',
                            'User-Agent'     => config('app.name'),
                        ])
                        ->get(
                            "{$this->baseUrl}/search/imdbid/{$imdbId}"
                        );

                    if (!$response->successful()) {

                        logger()->error(
                            'Subs.ro API error',
                            [
                                'status' => $response->status(),
                                'body' => $response->body(),
                            ]
                        );

                        return [];
                    }

                    $data = $response->json();

                    if (!empty($data['items'])) {

                        $data['items'] = collect($data['items'])
                            ->map(function ($item) {

                                $item['pageLink'] = $item['link'];

                                $item['downloadPage'] = str_replace(
                                    '/subtitrare/',
                                    '/subtitrare/descarca/',
                                    $item['link']
                                );

                                return $item;
                            })
                            ->toArray();
                    }

                    return $data;
                }
            );

        } catch (Throwable $e) {

            report($e);

            return [];
        }
    }
}