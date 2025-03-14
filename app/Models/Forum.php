<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'overforum_id','description'];

    public function overforum()
    {
        return $this->belongsTo(Overforum::class);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
