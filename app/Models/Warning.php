<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warning extends Model
{
    use HasFactory, SoftDeletes;

    // Table associated with the model (optional if the table name matches the model's plural form)
    protected $table = 'warnings';

    // Mass assignable attributes (add any fields you want to mass-assign)
    protected $fillable = [
        'user_id',
        'warned_by',
        'torrent',
        'reason',
        'expires_on',
        'active',
        'deleted_by',
    ];

    // Cast attributes to proper data types (if necessary)
    protected $casts = [
        'expires_on' => 'datetime',
        'active' => 'boolean',
    ];

    // Relationship with User (who the warning belongs to)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with User (who issued the warning)
    public function warnedBy()
    {
        return $this->belongsTo(User::class, 'warned_by');
    }

    // Relationship with Torrent (related torrent)
    public function torrent()
    {
        return $this->belongsTo(Torrent::class, 'torrent');
    }

    public function torrenttitle()
    {
        return $this->belongsTo(Torrent::class, 'torrent');
    }

    public function warneduser()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}

}
