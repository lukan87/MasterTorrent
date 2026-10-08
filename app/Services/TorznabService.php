<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Torrent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TorznabService
{
    public const CATEGORIES = [2000 => 'Movies', 3000 => 'Audio', 4000 => 'PC', 5000 => 'TV', 7000 => 'Other'];

    public function key(User $user): string
    {
        return $user->id.'.'.hash_hmac('sha256', 'torznab:'.$user->id.':'.$user->passkey, config('app.key'));
    }

    public function authenticate(string $key): ?User
    {
        if (! preg_match('/^([1-9][0-9]*)\.[a-f0-9]{64}$/D', $key, $matches)) {
            return null;
        }
        $user = User::find($matches[1]);
        if (! $user || ! $user->passkey || ! hash_equals($this->key($user), $key)
            || $user->enabled === 'no' || $user->activation_pending || $user->isBanned()) {
            return null;
        }

        return $user;
    }

    public function categoryMap(): array
    {
        return Cache::remember('torznab.categories.v1', 300, fn () => Category::query()
            ->whereNotIn('id', Category::ADULT_IDS)->get(['id', 'name'])->mapWithKeys(fn ($category) => [
                $category->id => match ($category->browseGroup()) {
                    'Movies' => 2000, 'TV shows' => 5000, 'Music' => 3000,
                    'Games', 'Software' => 4000, default => 7000,
                },
            ])->all());
    }

    public function search(array $params): array
    {
        $map = $this->categoryMap();
        $query = Torrent::query()->whereIn('category_id', array_keys($map));
        $type = $params['t'];
        if (in_array($type, ['movie', 'tvsearch'], true)) {
            $parent = $type === 'movie' ? 2000 : 5000;
            $query->whereIn('category_id', array_keys(array_filter($map, fn ($value) => $value === $parent)));
        }
        if (! empty($params['cat'])) {
            $cats = explode(',', $params['cat']);
            // Standard subcategories inherit their parent; custom IDs are 100000 + site ID.
            $allowed = array_keys(array_filter($map, fn ($parent, $id) => in_array((string) (100000 + $id), $cats, true)
                || in_array($parent, array_map(fn ($cat) => intdiv((int) $cat, 1000) * 1000, $cats), true), ARRAY_FILTER_USE_BOTH));
            $query->whereIn('category_id', $allowed);
        }
        if (! empty($params['q'])) {
            $words = preg_split('/[\s._-]+/u', trim($params['q']), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                $word = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word);
                $query->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$word.'%']);
            }
        }
        if (! empty($params['imdbid'])) {
            $query->where('imdbid', 'tt'.preg_replace('/^tt/i', '', $params['imdbid']));
        }
        if (! empty($params['tmdbid'])) {
            $query->where('tmdbid', $params['tmdbid']);
        }
        if (! empty($params['tvdbid'])) {
            $query->where('tmdbid', $this->tvdbToTmdb($params['tvdbid']) ?? -1);
        }
        if (! empty($params['year'])) {
            $query->whereRaw('name REGEXP ?', ['(^|[^0-9])'.$params['year'].'([^0-9]|$)']);
        }
        if ($type === 'tvsearch') {
            $this->episodes($query, $params);
        }
        $offset = (int) ($params['offset'] ?? 0);
        $total = (clone $query)->count();
        $items = $query->select(['id', 'name', 'slug', 'size', 'category_id', 'seeders', 'leechers', 'times_completed', 'created_at', 'imdbid', 'tmdbid', 'free', 'double'])
            ->orderByDesc('created_at')->orderByDesc('id')->offset($offset)->limit((int) ($params['limit'] ?? 50))->get();

        return compact('items', 'total', 'offset', 'map');
    }

    private function tvdbToTmdb(string $id): ?int
    {
        $key = config('services.tmdb.key');
        if (! $key) {
            throw new \RuntimeException('TV identifier lookup is unavailable.');
        }
        $result = Cache::remember('torznab.tvdb.'.$id, 3600, fn () => Http::connectTimeout(5)->timeout(10)
            ->get('https://api.themoviedb.org/3/find/'.$id, ['api_key' => $key, 'external_source' => 'tvdb_id'])->throw()->json());

        return $result['tv_results'][0]['id'] ?? null;
    }

    private function episodes(Builder $query, array $params): void
    {
        $season = isset($params['season']) ? (int) $params['season'] : null;
        $episode = $params['ep'] ?? null;
        if ($episode && str_contains($episode, '/')) {
            if ($season === null || $season < 1900) {
                throw new \InvalidArgumentException('Daily searches require a year as the season.');
            }
            $query->whereRaw('name REGEXP ?', ['(^|[^0-9])'.$season.'[ ._-]'.str_replace('/', '[ ._-]', $episode).'([^0-9]|$)']);

            return;
        }
        if ($season !== null) {
            $marker = '(^|[^[:alnum:]])s0?'.$season;
            $query->where(function (Builder $q) use ($marker, $season) {
                $q->whereRaw('name REGEXP ?', [$marker.'([^0-9]|$)'])
                    ->orWhereRaw('name REGEXP ?', ['(^|[^[:alnum:]])0?'.$season.'x[0-9]+'])
                    ->orWhereRaw('name REGEXP ?', ['(^|[^[:alnum:]])season[ ._-]+0?'.$season.'([^0-9]|$)']);
            });
        }
        if ($episode !== null) {
            $ep = '0?'.(int) $episode;
            $query->where(function (Builder $q) use ($season, $ep) {
                $q->whereRaw('name REGEXP ?', ['(^|[^[:alnum:]])s'.($season !== null ? '0?'.$season : '[0-9]+').'[ ._-]*(e[0-9]+)*e'.$ep.'([^0-9]|$)'])
                    ->orWhereRaw('name REGEXP ?', ['(^|[^[:alnum:]])'.($season !== null ? '0?'.$season : '[0-9]+').'x'.$ep.'([^0-9]|$)']);
            });
        }
    }
}
