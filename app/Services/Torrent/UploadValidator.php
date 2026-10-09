<?php

namespace App\Services\Torrent;

use App\Models\User;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UploadValidator
{
    public function validate(Request $request, User $user): void
    {
        app(UploadPermission::class)->authorize($user);
        Validator::make($request->all(), [
            'torrent' => ['required', 'file', 'extensions:torrent', 'max:'.config('upload-api.torrent_max_kb')],
            'name' => ['required_without_all:tmdbid,tvdbid,imdb_url', 'nullable', 'string', 'max:255'],
            'description' => ['required_without_all:tmdbid,tvdbid,imdb_url', 'nullable', 'string', 'max:100000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'mediainfo' => ['nullable', 'string', 'max:100000'],
            'imdb_url' => ['nullable', 'string', 'max:255', 'regex:~^https?://(?:www\.)?imdb\.com/title/tt[0-9]+/?(?:[?#].*)?$~i'],
            'tmdbid' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'tmdb_type' => ['required_with:tmdbid', 'nullable', 'in:movie,tv'],
            'tvdbid' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'season' => ['required_with:episode', 'nullable', 'integer', 'min:0', 'max:999'],
            'episode' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'steamid' => ['nullable', 'integer', 'min:1'],
            'poster' => ['nullable', 'url:http,https', 'max:2048'],
            'background' => ['nullable', 'url:http,https', 'max:2048'],
            'genre' => ['nullable', 'string', 'max:1000'],
            'anon' => ['sometimes', 'boolean'],
            'free' => ['sometimes', 'boolean'],
            'double' => ['sometimes', 'boolean'],
            'sticky' => ['sometimes', 'boolean'],
            'seedbox' => ['sometimes', 'boolean'],
            'external' => ['sometimes', 'boolean'],
        ])->validate();

        foreach (['free', 'double', 'sticky', 'seedbox'] as $flag) {
            if ($request->boolean($flag) && $user->user_class < UserClass::MODERATOR) {
                throw ValidationException::withMessages([$flag => 'This setting requires moderator permission.']);
            }
        }
        // The old service used presence checks: remove explicit false API flags.
        foreach (['free', 'double', 'sticky', 'seedbox', 'external'] as $flag) {
            if ($request->has($flag) && ! $request->boolean($flag)) {
                $request->request->remove($flag);
            }
        }
    }
}
