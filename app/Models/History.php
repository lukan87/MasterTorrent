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
        'prewarn',
        'prewarned_at',
        'ip',
        'last_awarded', 
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'prewarned_at' => 'datetime', 
        'hitrun' => 'boolean',
        'prewarn' => 'boolean',
        'last_awarded' => 'datetime', 
    ];

    // Define relationships
    public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent_id');
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

}
