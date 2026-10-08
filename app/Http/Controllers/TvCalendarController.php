<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\Torrent;
use App\Models\TvShowFollow;
use App\Services\TvCalendarService;
use App\Services\TvmazeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class TvCalendarController extends Controller
{
    public const COUNTRIES = ['US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France', 'NL' => 'Netherlands'];

    public function index(Request $request, TvmazeService $tvmaze, TvCalendarService $calendar)
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'view' => ['nullable', Rule::in(['day', 'week', 'agenda'])],
            'tab' => ['nullable', Rule::in(['all', 'my', 'popular'])],
            'country' => ['nullable', Rule::in(array_keys(self::COUNTRIES))],
            'source' => ['nullable', Rule::in(['all', 'broadcast', 'streaming'])],
            'q' => ['nullable', 'string', 'max:100'],
            'genre' => ['nullable', 'string', 'max:60'],
            'network' => ['nullable', 'string', 'max:100'],
            'hide_daily' => ['nullable', 'boolean'],
            'only_followed' => ['nullable', 'boolean'],
            'scope' => ['nullable', Rule::in(['all', 'followed', 'available', 'on-site'])],
        ]) + ['hide_daily' => true, 'only_followed' => false, 'date' => null, 'tab' => 'all', 'view' => 'week', 'country' => 'US', 'source' => 'all', 'q' => '', 'genre' => '', 'network' => '', 'scope' => 'all'];
        foreach (['tab' => 'all', 'view' => 'week', 'country' => 'US', 'source' => 'all', 'scope' => 'all'] as $key => $default) {
            $filters[$key] = $filters[$key] ?: $default;
        }
        $filters['hide_daily'] = $request->boolean('hide_daily', true);
        $filters['only_followed'] = $request->boolean('only_followed');
        $today = CarbonImmutable::now('Europe/London')->startOfDay();
        $selected = $filters['date'] ? CarbonImmutable::parse($filters['date'], 'Europe/London') : $today;
        $filters['date'] = $selected->toDateString();
        $start = $filters['view'] !== 'day' ? $selected->startOfWeek() : $selected;
        $days = $filters['view'] !== 'day' ? 7 : 1;
        $schedule = $tvmaze->schedule($start, $days, $filters['country'], $filters['source']);
        $all = collect($calendar->decorate($schedule['episodes'], (int) $request->user()->id));
        $genres = $all->pluck('genres')->flatten()->unique()->sort()->values();
        $networks = $all->pluck('network')->unique()->sort()->values();
        $eligible = $all->filter(fn ($episode) => (! $filters['hide_daily'] || ! $tvmaze->isDailyTalkOrReality($episode))
            && (! $filters['only_followed'] || $episode['followed']));
        $networkCounts = $eligible->countBy('network')->sortDesc()->take(6);
        $episodes = $eligible->filter(function ($episode) use ($filters) {
            return match ($filters['tab']) {
                'my' => $episode['followed'],
                'popular' => $episode['upload_count'] > 0,
                default => true,
            } && (! $filters['q'] || mb_stripos($episode['title'].' '.$episode['episode'], $filters['q']) !== false)
                && (! $filters['genre'] || in_array($filters['genre'], $episode['genres'], true))
                && (! $filters['network'] || $episode['network'] === $filters['network'])
                && match ($filters['scope']) {
                    'followed' => $episode['followed'], 'available' => $episode['available'],
                    'on-site' => $episode['upload_count'] > 0 || $episode['online_url'], default => true,
                };
        })->values();
        if ($filters['tab'] === 'popular') {
            $episodes = $episodes->sortByDesc('popularity')->values();
        }
        $dates = collect(range(0, $days - 1))->map(fn ($offset) => $start->addDays($offset));
        $filteredWeek = $filters['view'] !== 'day' && (
            filled($filters['q']) || filled($filters['genre']) || filled($filters['network'])
            || $filters['scope'] !== 'all' || $filters['source'] !== 'all' || $filters['tab'] !== 'all'
            || $filters['hide_daily'] || $filters['only_followed']
        );
        if ($filteredWeek) {
            $airDates = $episodes->pluck('date')->flip();
            $dates = $dates->filter(fn ($date) => $airDates->has($date->toDateString()))->values();
        }
        if ($filters['view'] === 'agenda' && $today->betweenIncluded($start, $start->addDays(6))) {
            // Lead with today and upcoming days; keep earlier days at the end.
            $dates = $dates->sortBy(fn ($date) => $date->lt($today)
                ? $date->addWeek()->timestamp : $date->timestamp)->values();
        }
        $watchlist = $calendar->watchlist((int) $request->user()->id);

        $data = [
            'filters' => $filters, 'today' => $today, 'start' => $start, 'end' => $start->addDays($days - 1),
            'dates' => $dates, 'episodes' => $episodes->groupBy('date'), 'genres' => $genres, 'networks' => $networks, 'networkCounts' => $networkCounts,
            'countries' => self::COUNTRIES, 'warnings' => $schedule['warnings'], 'watchlist' => $watchlist,
            'tabCounts' => ['all' => $all->pluck('show_id')->unique()->count(), 'my' => $watchlist->count(),
                'popular' => $all->filter(fn ($episode) => $episode['upload_count'] > 0)->pluck('show_id')->unique()->count()],
            'stats' => ['episodes' => $episodes->count(), 'shows' => $episodes->pluck('show_id')->unique()->count(),
                'followed' => $episodes->where('followed', true)->pluck('show_id')->unique()->count(),
                'available' => $episodes->where('available', true)->count()],
        ];
        $partial = $request->header('X-Calendar-Browse') === '1' && $request->expectsJson();
        $response = $partial
            ? response()->json(['html' => view('tv-calendar.results', $data)->render()])
            : response()->view('tv-calendar.index', $data);

        return $response->header('Cache-Control', 'private, no-store')
            ->header('Vary', 'Accept, X-Calendar-Browse');
    }

    public function follow(Request $request, int $show, TvmazeService $tvmaze)
    {
        $data = $request->validate(['notify_upload' => ['required', 'boolean']]);
        try {
            $info = $tvmaze->show($show);
        } catch (RuntimeException|ConnectionException $exception) {
            return $this->actionResponse($request, 'TVmaze is temporarily unavailable. Your watchlist has not changed.', 503);
        }
        $imdb = $tvmaze->imdbId($info['externals']['imdb'] ?? null);
        $tmdb = $imdb ? Series::where('imdb_id', $imdb)->value('tmdb_id') : null;
        if (! $tmdb && $imdb) {
            $tmdb = Torrent::where('imdbid', $imdb)->where('tmdb_type', 'tv')->value('tmdbid');
        }
        if ($data['notify_upload'] && ! $imdb && ! $tmdb) {
            return $this->actionResponse($request, 'This show has no IMDb or local series ID yet. You can follow it, but upload alerts are not available.', 422);
        }
        TvShowFollow::updateOrCreate(['user_id' => $request->user()->id, 'tvmaze_id' => $show], [
            'title' => mb_substr($info['name'], 0, 255), 'imdbid' => $imdb,
            'tmdbid' => $tmdb ? (string) $tmdb : null, 'notify_upload' => $data['notify_upload'],
        ]);

        return $this->actionResponse($request, $data['notify_upload'] ? 'Show followed. New matching uploads will be sent to your site inbox.' : 'Show saved to your watchlist. Calendar upload alerts are off.');
    }

    public function unfollow(Request $request, int $show)
    {
        TvShowFollow::where('user_id', $request->user()->id)->where('tvmaze_id', $show)->delete();

        return $this->actionResponse($request, 'Removed from your calendar watchlist.');
    }

    private function actionResponse(Request $request, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status)
                ->header('Cache-Control', 'private, no-store');
        }

        return back()->with($status === 200 ? 'success' : 'error', $message);
    }

}
