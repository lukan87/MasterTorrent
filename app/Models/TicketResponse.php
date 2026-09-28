<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    public function scopeVisibleTo($query, User $user)
    {
        return $query->when($user->user_class <= 5, fn ($query) => $query
            ->where('is_staff_note', false)->where('is_internal', false));
    }

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_note',
        'is_internal',
    ];

    protected $casts = [
        'is_staff_note' => 'boolean',
        'is_internal' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_response_id');
    }
}
