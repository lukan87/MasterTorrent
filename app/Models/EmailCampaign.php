<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'batch_group_id',
        'batch_number',
        'batch_size',
        'previous_campaign_id',

        'subject',
        'body',
        'target',
        'filters',

        'total_recipients',
        'queued_count',
        'sent_count',
        'failed_count',

        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',

        'batch_number' => 'integer',
        'batch_size' => 'integer',
        'previous_campaign_id' => 'integer',

        'total_recipients' => 'integer',
        'queued_count' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',

        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Individual recipients belonging to this campaign.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(
            EmailCampaignRecipient::class,
            'email_campaign_id'
        );
    }

    /**
     * Previous campaign in this mailing series.
     */
    public function previousCampaign(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'previous_campaign_id'
        );
    }

    /**
     * Campaigns created directly after this campaign.
     */
    public function nextCampaigns(): HasMany
    {
        return $this->hasMany(
            self::class,
            'previous_campaign_id'
        );
    }

    /**
     * Number of recipients still waiting to finish.
     */
    public function getRemainingCountAttribute(): int
    {
        return max(
            0,
            $this->total_recipients
                - $this->sent_count
                - $this->failed_count
        );
    }

    /**
     * Campaign completion percentage.
     */
    public function getProgressAttribute(): int
    {
        if ($this->total_recipients <= 0) {
            return 0;
        }

        $processed =
            $this->sent_count
            + $this->failed_count;

        return min(
            100,
            (int) round(
                ($processed / $this->total_recipients) * 100
            )
        );
    }
}