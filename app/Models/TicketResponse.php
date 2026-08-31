<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TicketResponse extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_note',
        'is_internal'
    ];

    protected $casts = [
        'is_staff_note' => 'boolean',
        'is_internal' => 'boolean'
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
        return $this->hasMany(TicketAttachment::class,'ticket_response_id');
    }

}