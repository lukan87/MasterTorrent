<?php

namespace App\Services\Torrent;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TorrentAnonymity
{
    public function resolve(Request $request, User $user): bool
    {
        Validator::make($request->all(), ['anon' => ['sometimes', 'boolean']])->validate();

        return $request->has('anon') ? $request->boolean('anon') : (bool) $user->anonymous;
    }
}
