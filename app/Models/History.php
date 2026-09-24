<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    // Define the table associated with the model (optional)
    protected $table = 'history';

    // Specify the fillable attributes
    protected $fillable = [
        'user_id',
        'action',
        'torrent_id',
        'info_hash',
        'completed_at',
        'active',
        'seeder',
        'immune',
        'hitrun',
        'hitrun_removed_at',
        'hitrun_warned_at',
        'prewarn',
        'prewarned_at',
        'ip',
        'last_awarded',
        'uploaded',
        'actual_uploaded',
        'seedtime',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'prewarned_at' => 'datetime', 
        'hitrun' => 'boolean',
        'prewarn' => 'boolean',
        'last_awarded' => 'datetime', 
        'hitrun_removed_at' => 'datetime',
        'hitrun_warned_at' => 'datetime',
        'last_event_at' => 'datetime',
    ];

    // Define relationships
public function torrent()
{
    return $this->belongsTo(Torrent::class, 'torrent_id')->withTrashed();
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship to peers (if a History record corresponds to one Peer)
    public function peer()
    {
        return $this->belongsTo(Peer::class, 'torrent_id');
    }

    // Define the existing relationship to peers
    public function peers()
    {
        return $this->hasMany(Peer::class, 'torrent_id');
    }

    public function warning()
{
    return $this->belongsTo(Warning::class, 'torrent_id', 'torrent_id');
}

    /**
     * The download basis used for Hit & Run ratio calculations.
     *
     * Uses the credited `downloaded` where possible, but falls back to
     * `actual_downloaded` when nothing was credited (e.g. freeleech) so a 1:1
     * ratio comparison is still meaningful.
     */
    public function effectiveDownload()
    {
        return $this->downloaded > 0 ? $this->downloaded : $this->actual_downloaded;
    }

    /**
     * Ratio as credited upload over the effective download basis.
     */
    public function effectiveRatio(): float
    {
        $download = $this->effectiveDownload();

        if ($download <= 0) {
            return 0.0;
        }

        return $this->uploaded / $download;
    }

    /**
     * Percentage of the torrent actually downloaded (0-100).
     *
     * Uses effectiveDownload() so freeleech torrents (credited download = 0)
     * are still measured against the real bytes transferred. Returns 0 when
     * the torrent size is unknown so the download-threshold skip applies.
     */
    public function downloadPercent(): float
    {
        if (!$this->torrent || $this->torrent->size <= 0) {
            return 0.0;
        }

        return $this->effectiveDownload() / $this->torrent->size * 100;
    }

}
