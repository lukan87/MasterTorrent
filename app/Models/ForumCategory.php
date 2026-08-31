<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'position',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function topics()
    {
        return $this->hasMany(ForumTopic::class, 'category_id');
    }
}