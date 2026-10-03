<?php

namespace App\Services;

use App\Models\Shoutbox;
use App\Models\User;
use App\Notifications\ShoutMentionNotification;

class ShoutMentionService
{
    public function names(string $message): array
    {
        // Ignore email addresses. Quoted mentions support spaces and punctuation.
        preg_match_all('/(?<![\pL\pN_@])@(?:"([^"\r\n]{1,100})"|([\pL\pN_][\pL\pN_.-]{0,99}))/u', $message, $matches, PREG_SET_ORDER);

        $names = [];
        foreach ($matches as $match) {
            $name = $match[1] !== '' ? $match[1] : rtrim($match[2], '.');
            $names[mb_strtolower($name)] = $name;
        }

        return array_values($names);
    }

    public function notify(Shoutbox $shout): void
    {
        $names = $this->names($shout->message);
        if ($names === []) {
            return;
        }

        $users = User::query()
            ->whereIn('name', $names)
            ->where('id', '!=', $shout->user_id)
            ->get();

        $author = $shout->user;
        foreach ($users as $user) {
            $user->notify(new ShoutMentionNotification($shout, $author->name));
        }
    }
}
