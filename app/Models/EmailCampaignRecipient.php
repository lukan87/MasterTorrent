<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignRecipient extends Model
{
    protected $fillable = [
        'email_campaign_id',
        'user_id',

        'name',
        'email',

        'status',
        'attempts',
        'error',

        'queued_at',
        'processing_at',
        'sent_at',
        'failed_at',

        'bounce_type',
        'bounce_code',
        'bounce_message',
        'bounced_at',
        'user_deleted_at',
    ];

    protected $casts = [
        'attempts' => 'integer',

        'queued_at' => 'datetime',
        'processing_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',

        'bounced_at' => 'datetime',
        'user_deleted_at' => 'datetime',
    ];

    /**
     * Campaign this recipient belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            EmailCampaign::class,
            'email_campaign_id'
        );
    }

    /**
     * Current FileIplay user account.
     *
     * This can be null if the account is deleted later.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}