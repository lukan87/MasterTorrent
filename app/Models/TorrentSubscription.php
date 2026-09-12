<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentSubscription extends Model
{
    use HasFactory;

    protected $table = 'torrent_subscriptions';

    protected $fillable = [
        'user_id',
        'imdbid',
        'tmdbid',
        'source_torrent_id',
        'title',
        'type',
    ];

    /**
     * Does the given user already subscribe to a title with these ids?
     * Null-safe: a null id column is ignored when matching.
     */
    public static function existsFor(
        int $userId,
        ?string $imdbid = null,
        ?string $tmdbid = null
    ): bool {
        return static::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($imdbid, $tmdbid) {
                if ($imdbid !== null && $imdbid !== '') {
                    $q->orWhere('imdbid', $imdbid);
                }
                if ($tmdbid !== null && $tmdbid !== '') {
                    $q->orWhere('tmdbid', $tmdbid);
                }
                // Show nothing by default if both ids are empty
                if (($imdbid === null || $imdbid === '') && ($tmdbid === null || $tmdbid === '')) {
                    $q->whereRaw('1 = 0');
                }
            })
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sourceTorrent()
    {
        return $this->belongsTo(Torrent::class, 'source_torrent_id');
    }
}