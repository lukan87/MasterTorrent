<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('categories');
        });

        static::deleted(function () {
            Cache::forget('categories');
        });
    }

     // Define the relationship with Torrent
     public function torrents()
     {
         return $this->hasMany(Torrent::class);
     }

}
