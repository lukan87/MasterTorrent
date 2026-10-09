<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class DuplicateTorrentException extends ValidationException
{
    public static function forUpload(): static
    {
        return new static(validator([], []), null, 'default')->withDuplicateMessage();
    }

    private function withDuplicateMessage(): static
    {
        $this->validator->errors()->add('torrent', 'This torrent already exists on the tracker.');

        return $this;
    }
}
