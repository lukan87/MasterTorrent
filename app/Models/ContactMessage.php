<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{

protected $fillable = [
'contact_id',
'sender_type',
'staff_id',
'message',
'ip'
];

public function contact()
{
return $this->belongsTo(Contact::class);
}

public function staff()
{
return $this->belongsTo(User::class,'staff_id');
}

}