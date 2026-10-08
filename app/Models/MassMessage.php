<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassMessage extends Model
{
    protected $fillable = [
        'actor_id', 'actor_name', 'sender_id', 'sender_name', 'send_as_system',
        'subject', 'body', 'user_classes', 'status', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['send_as_system' => 'boolean', 'user_classes' => 'array', 'completed_at' => 'datetime'];
    }

    public function deliveries()
    {
        return $this->hasMany(MassMessageDelivery::class);
    }

    public function scopeWithDeliveryCounts($query)
    {
        return $query->withCount([
            'deliveries',
            'deliveries as delivered_count' => fn ($q) => $q->whereNotNull('delivered_at'),
            'deliveries as read_count' => fn ($q) => $q->read(),
            'deliveries as pending_count' => fn ($q) => $q->where('status', 'pending'),
            'deliveries as skipped_count' => fn ($q) => $q->where('status', 'skipped'),
            'deliveries as removed_count' => fn ($q) => $q->removed(),
        ]);
    }
}
