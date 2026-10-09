<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedboxPublication extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'encrypted:array', 'register' => 'boolean'];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'queued' => 'Waiting to publish',
            'processing' => 'Publishing',
            'published' => 'Published',
            'registering' => 'Registering with seedbox',
            'checking' => 'Checking content',
            'seeding' => 'Seeding',
            'duplicate' => 'Already published',
            'failed' => 'Publishing failed',
            'registration_failed' => 'Published · seedbox setup failed',
            'manual_seeding' => 'Published · manual seeding required',
            default => 'Awaiting update',
        };
    }

    public function errorMessage(): ?string
    {
        return match ($this->error_code) {
            null => null,
            'duplicate_torrent' => 'This torrent already exists on FileIPlay. No second upload was created.',
            'existing_client_hash' => 'This torrent is already in your seedbox. Its state was preserved; check seeding in your client.',
            'verification_timeout' => 'Content checking has not finished. Check your seedbox before retrying.',
            'seedbox_registration_failed' => 'Your torrent is published. Seedbox setup did not finish; retry safely or configure seeding in your client.',
            'seedbox_status_failed' => 'Your torrent is published. Its seeding status could not be confirmed; check your client or retry.',
            'seedbox_publishing_failed' => 'Publication could not finish. Check that the selected content is complete and your seedbox connection is available, then retry.',
            'publishing_disabled' => 'Background publishing is unavailable. Contact staff before retrying; any published torrent remains available.',
            'worker_failed' => 'Background processing stopped. Retry safely or contact staff.',
            default => 'This request could not finish. Retry safely or contact staff.',
        };
    }

    public function torrent()
    {
        return $this->belongsTo(Torrent::class);
    }

    public function seedbox()
    {
        return $this->belongsTo(Seedbox::class);
    }
}
