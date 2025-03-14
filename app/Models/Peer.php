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
// public function history()
// {
//     return $this->belongsTo(History::class, 'torrent_id', 'torrent_id');
// }

public function history()
{
    return $this->hasOne(History::class, 'torrent_id', 'torrent_id');
}

// Automatically cast timestamps
protected $casts = [
    'client_updated_at' => 'datetime',
];


// Event to handle validation before saving the model
protected static function booted()
{
    static::saving(function ($peer) {
        // Get the IP address
        $ip = $peer->ip;

        // Check if the IP is a valid IPv4 address
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // If valid, store the IPv4 IP
            // The model is automatically ready to save the IPv4 address
        }
        // Check if the IP is a valid IPv6 address
        elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // If valid, store the IPv6 IP
            // The model is automatically ready to save the IPv6 address
        } else {
            // If invalid IP format, stop the save and throw an error
           // Log::error("Invalid IP address: {$ip}");
            //throw new \Exception("Invalid IP address format");
        }
    });
}

}
