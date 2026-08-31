<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    // Define fillable properties to allow mass assignment
    protected $fillable = [
        'name',
        'requested_by',
        'category_id',
        'imdb_url',
        'tmdb_url',
        'steam_url',
        'image',
        'description',
        'link'
    ];

    // Define relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Add this relationship method
    public function filledBy()
    {
        return $this->belongsTo(User::class, 'filled_by');
    }

    public function requester()
{
    return $this->belongsTo(User::class, 'requested_by');
}

}
