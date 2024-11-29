<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peer extends Model
{


    use HasFactory;

    protected $table = 'peers';



// Specify the fillable attributes
protected $fillable = [
    'peer_id',
    'md5_peer_id',
    'hash',
    'ip',
    'port',
    'agent',
    'uploaded',
    'downloaded',
    'left',
    'seeder',
    'torrent_id',
    'user_id',
    'active',
    'visible',
    'client_updated_at',
];

    // Define relationships if needed
// Define relationships if needed
public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

 // A peer can have many history entries
// In Peer Model
// Define the inverse of the relationship
public function history()
{
    return $this->belongsTo(History::class, 'torrent_id', 'torrent_id');
}

}
