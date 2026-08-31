<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_response_id',
        'file_path',
        'file_name'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function response()
    {
        return $this->belongsTo(TicketResponse::class,'ticket_response_id');
    }

}