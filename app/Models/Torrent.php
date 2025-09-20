<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Peer;
use Illuminate\Support\Facades\Cache;
use App\Models\UserSlot;

class Torrent extends Model
{
    use HasFactory;

    protected $table = 'torrents'; // Specify the table name

    // Fillable fields for mass assignment
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
        'announce',
        'background',
        'tmdb_type',
        'trailer',
        'bumped'
        // Add other fields as necessary
    ];

    protected static function boot()
    {
        parent::boot();

        // Clear caches when the torrent is saved or deleted
        static::saved(function () {
            Cache::forget('torrent_count');
            Cache::forget('top_last_day');
            Cache::forget('top_last_week');
            Cache::forget('top_last_month');
            Cache::forget('cached_torrents'); // Add this line for general cache clearing
        });

        static::deleted(function () {
            Cache::forget('torrent_count');
            Cache::forget('top_last_day');
            Cache::forget('top_last_week');
            Cache::forget('top_last_month');
            Cache::forget('cached_torrents');
        });

        static::created(function () {
            Cache::forget('cached_torrents'); // Add this for newly created torrents
        });

        // Single `updated` method to handle cache clearing
        static::updated(function ($torrent) {
            // Clear the main cache
            Cache::forget('cached_torrents');

            // Clear cache for specific pages based on the torrent's details
            $cacheKey = "cached_torrents_page_1_sort_name_dir_desc_keyword_{$torrent->slug}_category_{$torrent->category_id}_genre_{$torrent->genre}";

            // Add logic to forget cache keys that contain 'torrent_status' filtering
            $cacheKeyWithStatus = $cacheKey . "_torrent_status_{$torrent->status}";  // Add status to key
            Cache::forget($cacheKeyWithStatus);
        });
    }



    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'owner');
    }

    public function completions()
    {
        return $this->hasMany(History::class, 'torrent_id')->whereNotNull('completed_at');
    }

    public function histories()
    {
        return $this->hasMany(History::class, 'torrent_id');
    }

    public function getCompletionCountAttribute()
    {
        return $this->completions()->count();
    }

    public function peers()
    {
        return $this->hasMany(Peer::class, 'torrent_id');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'torrent_id');
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

// Add a method to get the count of thanks
public function thanksCount()
{
    return $this->thanks()->count();
}

public function images()
{
    return $this->hasMany(TorrentImage::class);
}

public function userSlots()
{
    return $this->hasMany(UserSlot::class);
}

}
