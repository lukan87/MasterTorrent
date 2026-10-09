<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadAttempt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function errorMessage(): ?string
    {
        return match ($this->error_code) {
            'duplicate_torrent' => 'This torrent already exists on the tracker.',
            'validation_failed' => 'Torrent or metadata validation failed. Check the submitted fields.',
            'forbidden' => 'The account did not have permission to upload.',
            'upload_failed' => 'The upload could not be completed. Retry or contact staff.',
            default => null,
        };
    }
}
