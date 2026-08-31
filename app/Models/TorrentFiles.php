<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentFiles extends Model
{
    use HasFactory;

    protected $table = 'files'; // Specify the table name if different from plural of the model name

    protected $fillable = [
        'filename',
        'size',
        'torrent_id', // Foreign key referencing the torrents table
    ];

    // Define a relationship with the Torrent model
    public function torrent()
    {
        return $this->belongsTo(Torrent::class); // Assuming you have a Torrent model
    }
}
