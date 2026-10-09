<?php

namespace App\Services\Torrent;

use App\Models\User;
use App\Models\UserClass;

class UploadPermission
{
    public function active(User $user): bool
    {
        return ! $user->trashed() && $user->enabled !== 'no'
            && ! $user->activation_pending && $user->banned_until === null;
    }

    public function canUpload(User $user): bool
    {
        return $this->active($user) && ($user->user_class >= UserClass::UPLOADER || $user->uploadpos === 'yes');
    }

    public function authorize(User $user): void
    {
        abort_unless($this->canUpload($user) && is_string($user->passkey) && $user->passkey !== '', 403, 'Upload permission is restricted.');
    }
}
