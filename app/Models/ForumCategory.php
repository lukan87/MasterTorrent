<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    // Set the table name if it's different from the plural form
    protected $table = 'forum_categories';

    // Fillable properties
    protected $fillable = ['name', 'description'];

    // Relationship with topics
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
