<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class Torrent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'torrents';

    protected $fillable = [
        'info_hash',
        'name',
        'slug',
        'file_name',
        'description',
        'mediainfo',
        'size',
        'added',
        'category_id',
        'type',
        'steamid',
        'num_files',
        'comments',
        'views',
        'hits',
        'times_completed',
        'leechers',
        'seeders',
        'owner',
        'nfo',
        'poster',
        'request',
        'nuked',
        'free',
        'sticky',
        'double',
        'recommended',
        'announce',
        'imdb_url',
        'extern',
        'seedbox',
        'external',
        'genre',
        'imdbid',
        'tmdbid',
        'background',
        'tmdb_type',
        'trailer',
        'bumped',
        'bumped_at',
        'bumped_by',
        'created_at',
        'deleted_at',
        'deleted_by',
        'deletion_reason',
        'file_signature'
    ];

    protected $casts = [
        'bumped_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Boot
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCaches();
        });

        static::deleted(function () {
            self::clearCaches();
        });

        static::created(function () {
            Cache::forget('cached_torrents');
        });

        static::updated(function ($torrent) {

            Cache::forget('cached_torrents');

            $cacheKey = "cached_torrents_page_1_sort_name_dir_desc_keyword_{$torrent->slug}_category_{$torrent->category_id}_genre_{$torrent->genre}";

            $cacheKeyWithStatus = $cacheKey . "_torrent_status_{$torrent->status}";

            Cache::forget($cacheKeyWithStatus);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Helper
    |--------------------------------------------------------------------------
    */

    protected static function clearCaches()
    {
        Cache::forget('cached_torrents');

        // Clear common paginated torrent list caches
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("cached_torrents_page_{$i}");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'owner')->withTrashed();
    }

    public function peers()
    {
        return $this->hasMany(Peer::class, 'torrent_id');
    }

    public function histories()
    {
        return $this->hasMany(History::class, 'torrent_id');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'torrent_id');
    }

    public function completions()
    {
        return $this->hasMany(History::class, 'torrent_id')
            ->whereNotNull('completed_at');
    }

    public function commentsByTorrentId()
    {
        return $this->hasMany(Comment::class, 'torrent_id', 'id');
    }

    public function comments()
    {
        return $this->commentsByTorrentId();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'torrent_genre');
    }

    public function files()
    {
        return $this->hasMany(TorrentFiles::class);
    }

    public function thanks()
    {
        return $this->hasMany(TorrentThank::class, 'torrent_id');
    }

    public function images()
    {
        return $this->hasMany(TorrentImage::class);
    }

    public function userSlots()
    {
        return $this->hasMany(UserSlot::class);
    }

    public function subtitles()
    {
        return $this->hasMany(Subtitle::class);
    }

    public function bumper()
    {
        return $this->belongsTo(User::class, 'bumped_by');
    }

    public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'owner')->withTrashed();
}

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getCompletionCountAttribute()
    {
        return $this->completions()->count();
    }

    public function thanksCount()
    {
        return $this->thanks()->count();
    }

    /**
     * Parsed display resolution label derived from the torrent name.
     * Used by the library movie/series show pages to group torrents.
     */
    public function getResolutionLabelAttribute(): string
    {
        $u = strtoupper($this->name ?? '');

        if (preg_match('/\b(2160P|4K|UHD)\b/', $u)) {
            return '4K';
        }
        if (preg_match('/\b1080[PI]\b/', $u)) {
            return '1080p';
        }
        if (preg_match('/\b720[PI]\b/', $u)) {
            return '720p';
        }
        if (preg_match('/\b(480P|576P|SD)\b/', $u)) {
            return 'SD';
        }

        return 'Other';
    }

    /**
     * Sort weight for a resolution group (lower = shown first).
     */
    public function getResolutionOrderAttribute(): int
    {
        return match ($this->resolution_label) {
            '4K'     => 1,
            '1080p'  => 2,
            '720p'   => 3,
            'SD'     => 4,
            default  => 5,
        };
    }

    /**
     * Parsed display label describing what a TV torrent contains:
     * a full season, a single episode, a range of seasons, etc.
     * Only meaningful for series torrents.
     */
    public function getEpisodeLabelAttribute(): string
    {
        $u = strtoupper($this->name ?? '');

        // Complete series / all seasons pack
        if (preg_match('/\b(COMPLETE(?: SERIES)?|FULL SERIES|ALL SEASONS|COMPLETE BLURAY)\b/', $u)) {
            return 'Full Series';
        }

        // Season range pack — Scene style: S01-S04  or  S01.S02
        if (preg_match('/\bS(\d{1,2})[^A-Z0-9]S(\d{1,2})\b|\bS(\d{1,2})(?:\-|\.)S(\d{1,2})\b/', $u, $m)) {
            $a = $m[1] ?? $m[3];
            $b = $m[2] ?? $m[4];
            return 'Seasons '.intval($a).'-'.intval($b);
        }

        // Season range pack — "Season 1.2" / "Season 1-2" / "Season1-2"
        if (preg_match('/\bSEASON\s?(\d{1,2})(?:[\-\.]|\s[\-\.]?\s)(\d{1,2})\b/', $u, $m)) {
            return 'Seasons '.intval($m[1]).'-'.intval($m[2]);
        }

        // Single episode — S01E05
        if (preg_match('/\bS(\d{1,2})E(\d{1,2})\b/', $u, $m)) {
            return 'S'.str_pad($m[1], 2, '0', STR_PAD_LEFT)
                 .'E'.str_pad($m[2], 2, '0', STR_PAD_LEFT);
        }

        // Full single season — S13 / Season 1 / Season1 / S1
        if (preg_match('/\bS(\d{1,2})\b/', $u, $m)) {
            return 'Full Season · S'.str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        if (preg_match('/\bSEASON\s?(\d{1,2})\b/', $u, $m)) {
            return 'Full Season · S'.str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        return 'Episode';
    }


public function purge(): void
{
    if (!$this->trashed()) {
        throw new \Exception('Torrent must be soft deleted before purge.');
    }

    $torrentId = $this->id;
    $torrentFile = $this->file_name;

    DB::transaction(function () use ($torrentId, $torrentFile) {

        /*
        |--------------------------------------------------------------------------
        | Fast DB cleanup
        |--------------------------------------------------------------------------
        */

        DB::table('torrent_thanks')->where('torrent_id', $torrentId)->delete();
        DB::table('peers')->where('torrent_id', $torrentId)->delete();
        DB::table('history')->where('torrent_id', $torrentId)->delete();
        DB::table('comments')->where('torrent_id', $torrentId)->delete();
        DB::table('user_slots')->where('torrent_id', $torrentId)->delete();
        DB::table('warnings')->where('torrent', $torrentId)->delete();

        /*
        |--------------------------------------------------------------------------
        | Images
        |--------------------------------------------------------------------------
        */

        $images = DB::table('torrent_images')
            ->where('torrent_id', $torrentId)
            ->get();

        foreach ($images as $image) {

            if (!empty($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            if (!empty($image->fallback)) {
                Storage::disk('public')->delete($image->fallback);
            }
        }

        DB::table('torrent_images')->where('torrent_id', $torrentId)->delete();

        /*
        |--------------------------------------------------------------------------
        | Subtitles
        |--------------------------------------------------------------------------
        */

        $subtitles = DB::table('subtitles')
            ->where('torrent_id', $torrentId)
            ->get();

        foreach ($subtitles as $subtitle) {

            if (!empty($subtitle->file_path)) {
                Storage::delete($subtitle->file_path);
            }
        }

        DB::table('subtitles')->where('torrent_id', $torrentId)->delete();

        /*
        |--------------------------------------------------------------------------
        | Torrent file
        |--------------------------------------------------------------------------
        */

        if (!empty($torrentFile)) {
            Storage::disk('public')->delete('files/torrents/' . $torrentFile);
        }

        /*
        |--------------------------------------------------------------------------
        | Pivot tables
        |--------------------------------------------------------------------------
        */

        DB::table('torrent_genre')
            ->where('torrent_id', $torrentId)
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Permanent delete torrent
        |--------------------------------------------------------------------------
        */

        $this->forceDelete();
    });
}
}