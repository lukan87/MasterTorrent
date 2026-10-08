<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassMessageDelivery extends Model
{
    protected $fillable = ['mass_message_id', 'receiver_id', 'receiver_name', 'message_id', 'status', 'was_read', 'delivered_at'];

    protected function casts(): array
    {
        return ['delivered_at' => 'datetime', 'was_read' => 'boolean'];
    }

    public function massMessage()
    {
        return $this->belongsTo(MassMessage::class)->withCount([
            'deliveries as delivered_count' => fn ($query) => $query->whereNotNull('delivered_at'),
        ]);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id')->withTrashed();
    }

    public function scopeRead($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('message', fn ($m) => $m->where('is_read', true))
                ->orWhere(fn ($m) => $m->whereNull('message_id')->where('was_read', true));
        });
    }

    public function scopeRemoved($query)
    {
        return $query->where(fn ($q) => $q->where('status', 'removed')
            ->orWhere(fn ($q) => $q->whereNotNull('delivered_at')->whereNull('message_id')));
    }
}
