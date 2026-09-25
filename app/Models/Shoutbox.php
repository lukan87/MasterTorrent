<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoutbox extends Model
{
    use HasFactory;
    protected $table = 'shoutbox';  // Specify the table name
    protected $fillable = ['user_id', 'message', 'parent_id', 'sticky'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo(Shoutbox::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Shoutbox::class, 'parent_id');
    }
}
