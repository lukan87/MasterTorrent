<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Str;

class Ticket extends Model
{

protected static function boot()
{
    parent::boot();

    static::creating(function ($ticket) {

        $ticket->slug = Str::slug($ticket->title);

    });
}
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'claimed_by',
        'last_replied_at',
        'last_replier_id',
        'is_locked'
    ];

    protected $casts = [
        'last_replied_at' => 'datetime',
        'is_locked' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Ticket creator
public function user()
{
    return $this->belongsTo(User::class)->withTrashed();
}

    // Category
    public function category()
    {
        return $this->belongsTo(TicketCategory::class);
    }

    // Responses
    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    // Assigned staff
    public function assignedStaff()
    {
        return $this->belongsTo(User::class,'assigned_to');
    }

    // Claimed staff
    public function claimedBy()
    {
        return $this->belongsTo(User::class,'claimed_by');
    }

    // Last replier
    public function lastReplier()
    {
        return $this->belongsTo(User::class,'last_replier_id');
    }

    public function events()
{
    return $this->hasMany(TicketEvent::class);
}


    //Helpers for status and priority labels

    public function isOpen()
{
    return $this->status === 'Open';
}

public function isResolved()
{
    return $this->status === 'Resolved';
}

public function isWaitingStaff()
{
    return $this->status === 'Waiting Staff';
}

public function isWaitingUser()
{
    return $this->status === 'Waiting User';
}

}