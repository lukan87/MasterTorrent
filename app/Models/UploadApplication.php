<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadApplication extends Model
{

    protected $table = 'upload_applications';
    protected $fillable = [
        'applicant_id',
        'reviewed_by',
        'internal_speed',
        'external_speed',
        'why_promoted',
        'external_sites',
        'scene_access',
        'know_torrents',
        'understand_seeding',
        'experience',
        'content_plan',
        'status',
        'votes_for',
        'votes_against',
        'decision_at'
    ];


    protected $casts = [
    'decision_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

   public function applicant()
{
    return $this->belongsTo(User::class, 'applicant_id')->withTrashed();
}

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function comments()
    {
        return $this->hasMany(UploadApplicationComment::class, 'application_id')
            ->latest();
    }

    public function votes()
    {
        return $this->hasMany(UploadApplicationVote::class, 'application_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function approvalPercentage()
    {
        $total = $this->votes_for + $this->votes_against;

        if ($total === 0) {
            return 0;
        }

        return round(($this->votes_for / $total) * 100);
    }
}