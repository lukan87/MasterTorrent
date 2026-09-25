<?php

namespace App\Services;

use App\Models\Invite;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteService
{
    public function create(User $user): Invite
    {
        return DB::transaction(function () use ($user) {
            $owner = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $minimumClass = config('app.invite_only') ? UserClass::ELITE_USER : UserClass::ADMIN;
            abort_if($owner->user_class < $minimumClass, 403, 'Your user class cannot create invitations.');

            if ($owner->invites <= 0) {
                throw ValidationException::withMessages(['invite' => 'You have no invites left.']);
            }

            $invite = Invite::create([
                'inviter_id' => $owner->id,
                'invite_code' => Str::random(32),
                'is_used' => false,
                'is_expired' => false,
            ]);
            $owner->decrement('invites');

            return $invite;
        });
    }

    public function revoke(User $user, int $id): void
    {
        DB::transaction(function () use ($user, $id) {
            $invite = Invite::where('inviter_id', $user->id)->lockForUpdate()->findOrFail($id);
            if ($invite->is_used || $invite->expired) {
                throw ValidationException::withMessages(['invite' => 'Only active, unused invitations can be revoked.']);
            }

            $invite->delete();
            User::whereKey($user->id)->increment('invites');
        });
    }

    /** Create the account, consume its code and deliver the message as one transaction. */
    public function register(?string $code, callable $createUser): User
    {
        return DB::transaction(function () use ($code, $createUser) {
            $invite = null;
            if (filled($code) || config('app.invite_only')) {
                $invite = Invite::where('invite_code', $code)->lockForUpdate()->first();
                if (! $invite || $invite->is_used || $invite->expired || ! $invite->inviter) {
                    throw ValidationException::withMessages(['invite_code' => 'Invalid, used or expired invite code.']);
                }
            }

            $user = $createUser($invite);
            if ($invite) {
                $invite->update(['is_used' => true, 'user_id' => $user->id]);
                // Escape BBCode delimiters in the member's chosen name.
                $name = str_replace(['[', ']'], ['&#91;', '&#93;'], $user->name);
                $profileUrl = route('profile.show', ['id' => $user->id, 'name' => $user->name]);
                SystemMessageService::send(2, $invite->inviter_id, 'Invite Used',
                    "Your invite code [b]{$invite->invite_code}[/b] has been used.\n\nNew member: [url={$profileUrl}]{$name}[/url]\n\nWelcome them to the site!");
            }

            return $user;
        });
    }
}
