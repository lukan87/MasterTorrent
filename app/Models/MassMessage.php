<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassMessage extends Model
{
    protected $fillable = ['sender_id', 'body', 'status', 'sent_count'];
}
