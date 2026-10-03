<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorrentRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = ['name', 'category_id', 'imdb_url', 'tmdb_url', 'steam_url', 'image', 'description'];

    public static function inputRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'imdb_url' => 'nullable|url:http,https|max:255',
            'tmdb_url' => 'nullable|url:http,https|max:255',
            'steam_url' => 'nullable|url:http,https|max:255',
            'image' => 'nullable|url:http,https|max:255',
            'description' => 'nullable|string|max:500',
        ];
    }

    public static function canBeCreatedBy(?User $user): bool
    {
        return $user && $user->user_class >= UserClass::ELITE_USER;
    }

    public static function canBeFilledBy(?User $user): bool
    {
        return $user && ($user->user_class >= UserClass::UPLOADER || $user->uploadpos === 'yes');
    }

    public function canBeVotedBy(?User $user): bool
    {
        return $user && (int) $this->requested_by !== (int) $user->id;
    }

    public function votes()
    {
        return $this->hasMany(RequestVote::class, 'request_id')
            ->whereHas('request', fn ($query) => $query->where(function ($query) {
                $query->whereNull('requested_by')->orWhereColumn('requests.requested_by', '!=', 'request_votes.user_id');
            }));
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function canBeManagedBy(?User $user): bool
    {
        return $user && ($user->user_class >= UserClass::MODERATOR
            || ($this->requested_by !== null && (int) $this->requested_by === (int) $user->id));
    }

    public function canBeDeletedBy(?User $user): bool
    {
        return $user && $user->user_class >= UserClass::MODERATOR;
    }

    // Also protect links stored before HTTP-only validation was introduced.
    public function safeUrl(?string $url): ?string
    {
        return filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true) ? $url : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function filledBy()
    {
        return $this->belongsTo(User::class, 'filled_by');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
