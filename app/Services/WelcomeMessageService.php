<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;

class WelcomeMessageService
{
    public const SUBJECT = 'Welcome to FileIPlay!';

    public static function sendTo(User $user): Message
    {
        $body = <<<'BBCODE'
[b]Welcome to FileIPlay! Make yourself at home.[/b]

We're glad you're here. Every member helps make FileIPlay a place worth coming back to, and supporting the community starts with two simple things: [b]logging in and seeding.[/b]

[b]Make us part of your daily routine[/b]
Drop by each day, even if you aren't downloading anything. Take a quick look at new uploads, catch up on site news, join a poll, or see what everyone is talking about in the forums and shoutbox. You might discover your next favourite movie or series, or just enjoy a friendly conversation.

[b]Keep the sharing going[/b]
After downloading, leave your torrents seeding whenever you can and follow the site's seeding rules. Your seeds help other members finish their downloads and keep the library available for everyone. Even a quiet seeder makes a difference!

Your visits bring the community to life. Your seeds keep it moving. Together, those small habits help FileIPlay thrive.

[b]Visit often. Seed generously. Enjoy your stay![/b]

Happy sharing,
[b]The FileIPlay Team[/b]
BBCODE;

        return SystemMessageService::send(
            (int) config('hitrun.system_user_id', 2),
            $user->id,
            self::SUBJECT,
            $body
        );
    }
}
