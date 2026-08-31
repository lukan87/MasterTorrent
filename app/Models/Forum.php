<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forum extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'forum_category_id',
        'name',
        'description',
        'position',
        'min_class_required',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    /**
     * Forum belongs to a category
     */
public function category()
{
    return $this->belongsTo(ForumCategory::class, 'forum_category_id');
}


    /**
     * Forum has many topics
     */
    public function topics()
    {
        return $this->hasMany(ForumTopic::class);
    }

    /**
     * Scope: Only forums visible to a given user class
     */
   public function scopeVisibleTo($query, ?int $userClass = null)
{
    $userClass = $userClass ?? \App\Models\UserClass::USER;

    return $query->where('min_class_required', '<=', $userClass)
                 ->whereHas('category', fn($q) => $q->where('min_class_required', '<=', $userClass))
                 ->orderBy('position');
}


    /**
     * Check if user can see this forum
     */
    public function isVisibleTo(int $userClass): bool
    {
        return $userClass >= max($this->min_class_required, $this->category->min_class_required);
    }

   

public function last_topic()
{
    return $this->hasOne(ForumTopic::class, 'forum_id')
                ->latest('updated_at');
}


 //Added to get the last post in the forum

    public function posts()
{
    return $this->hasManyThrough(
        ForumPost::class,
        ForumTopic::class
    );
}

public function lastPost()
{
    return $this->hasOneThrough(
        ForumPost::class,    // the posts table
        ForumTopic::class,   // the topics table
        'forum_id',          // Foreign key on ForumTopic
        'forum_topic_id'     // Foreign key on ForumPost
    )->latest('created_at');
}



}
