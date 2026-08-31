<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\UserClass;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'position',
        'min_class_required',
    ];

    /**
     * A category has many forums
     */
public function forums()
{
    return $this->hasMany(Forum::class, 'forum_category_id');
}



    /**
     * Scope: Only categories visible to a given user class
     */
    public function scopeVisibleTo($query, ?int $userClass = null)
    {
        $userClass = $userClass ?? UserClass::USER;

        return $query->where('min_class_required', '<=', $userClass)
                     ->orderBy('position');
    }

    /**
     * Check if user can see this category
     */
    public function isVisibleTo(int $userClass): bool
    {
        return $userClass >= $this->min_class_required;
    }
}
