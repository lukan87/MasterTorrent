<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Genre extends Model
{
    use HasFactory;

    // The table associated with the model (optional if table name follows Laravel convention)
    protected $table = 'genres';

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('allGenres');
        });

        static::deleted(function () {
            Cache::forget('allGenres');
        });
    }

    // The torrents that belong to the genre
    public function torrents()
    {
        return $this->belongsToMany(Torrent::class, 'torrent_genre');
    }
}
