<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    protected $fillable = [
        'torrent_id',
        'language',
        'original_name',
        'file_path',
        'extension',
        'size',
        'uploaded_by',
    ];

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Subtitle.php
public function uploader()
{
    return $this->belongsTo(User::class, 'uploaded_by');
}

}
