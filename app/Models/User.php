<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Models\UserClass;
use App\Models\Invite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\UserSlot;
use App\Models\Announcement;



class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recovery_code',
        'profile_image',
        'last_activity',
        'acceptpm',
        'title',
        'enabled',
        'donor',
        'uploadpos',
        'downloadpos',
        'info',
        'IP',
        'passkey', 
        'seedbonus',
        'vip_until',
        'rsskey',
        'user_class',
        'warned',
        'warned_until',
        'invites',
        'invited_by',
        'invite_code',
        'slots',
        'invites',
        'last_upload',
        'failed_attempts',
        'banned_until',
        'email_sent',
        'email_sent_at',
        'cover',
        'background',
        'cookie_consent',
        'cookie_consent_at',
        'rank_rewarded',
        'is_immune',
        'is_freeleech',
    ];



    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_until' => 'datetime',
            'last_activity' => 'datetime',
            'warned_until' => 'datetime',
            'vip_until' => 'datetime',
            'email_sent'    => 'boolean',
            'email_sent_at' => 'datetime',
            'is_junk' => 'boolean',
            'subscribed' => 'boolean',
            
        ];
    }

    public function getRoleNameAttribute()
{
    return UserClass::getClasses()[$this->user_class] ?? 'Unknown';
}

protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('user_count');
            Cache::forget('online_users');
        });

        static::deleted(function () {
            Cache::forget('user_count');
            Cache::forget('online_users');
        });
    }

public function torrents()
{
    return $this->hasMany(Torrent::class, 'owner'); // assuming 'owner' refers to the uploader in torrents table
}

public function peers()
{
    return $this->hasMany(Peer::class, 'user_id'); // Assuming 'user_id' is the foreign key in the peers table
}

public function history()
{
    return $this->hasMany(History::class, 'user_id'); // Assuming 'user_id' is the foreign key in the peers table
}

public function uploadApplications()
{
    return $this->hasMany(UploadApplication::class, 'applicant_id');
}

public function reviewedApplications()
{
    return $this->hasMany(UploadApplication::class, 'reviewed_by');
}

public function class()
{
    return $this->belongsTo(UserClass::class, 'user_class');
}

public function canApplyForUploader()
{
    $accountAgeDays = $this->created_at->diffInDays(now());

    $ratio = $this->downloaded > 0
        ? $this->uploaded / $this->downloaded
        : 0;

    $minimumUpload = 300 * 1073741824; // 300GB

    if ($accountAgeDays < 30) {
        return ['allowed' => false, 'reason' => 'Account must be at least 30 days old.'];
    }

    if ($this->uploaded < $minimumUpload) {
        return ['allowed' => false, 'reason' => 'You must upload at least 300GB before applying.'];
    }

    if ($ratio < 1.05) {
        return ['allowed' => false, 'reason' => 'Your ratio must be at least 1.05.'];
    }

    return ['allowed' => true];
}

public function uploaderApplicationCooldown()
{
    $lastRejected = UploadApplication::where('applicant_id', $this->id)
        ->where('status', 'rejected')
        ->latest()
        ->first();

    if (!$lastRejected) {
        return [
            'blocked' => false,
            'days_remaining' => 0
        ];
    }

    $cooldownDays = 30;

    $nextAllowed = $lastRejected->decision_at->copy()->addDays($cooldownDays);

    if (now()->lt($nextAllowed)) {

       $daysRemaining = (int) floor(now()->diffInDays($nextAllowed));

        return [
            'blocked' => true,
            'days_remaining' => $daysRemaining
        ];
    }

    return [
        'blocked' => false,
        'days_remaining' => 0
    ];
}

/**
     * Count the number of torrents being seeded by the user.
     *
     * @return int
     */
public function seedingCount()
{
    return $this->peers()
        ->where('seeder', true)
        ->select(DB::raw('COUNT(DISTINCT torrent_id) as count'))
        ->value('count');
}

    /**
     * Count the number of torrents being leeched by the user.
     *
     * @return int
     */
public function leechingCount()
{
    return $this->peers()
        ->where('seeder', false)
        ->select(DB::raw('COUNT(DISTINCT torrent_id) as count'))
        ->value('count');
}


public function getSeedbonusPerHourAttribute()
{
    // Count the number of distinct torrents the user is seeding,
    // excluding torrents they own
    $seedingCount = DB::table('peers')
        ->join('torrents', 'peers.torrent_id', '=', 'torrents.id')
        ->where('peers.user_id', $this->id)
        ->where('peers.seeder', true)
        ->where('torrents.owner', '!=', $this->id) // Exclude owned torrents
        ->distinct('peers.torrent_id')
        ->count('peers.torrent_id');

    // Define how many points per torrent per hour
    $pointsPerTorrent = 0.15;

    // Total points earned per hour
    return $seedingCount * $pointsPerTorrent;
}

public function getSeedingTorrentCountAttribute()
{
    // Count the number of distinct torrents the user is seeding,
    // excluding torrents they own
    return DB::table('peers')
        ->join('torrents', 'peers.torrent_id', '=', 'torrents.id')
        ->where('peers.user_id', $this->id)
        ->where('peers.seeder', true)
        ->where('torrents.owner', '!=', $this->id) // Exclude owned torrents
        ->distinct('peers.torrent_id')
        ->count('peers.torrent_id');
}


public function userHasPermission($permission)
{
    $permissions = UserClass::permissions(); // Assuming this returns the permissions array

    // Get the user class for the current user
    $userClass = $this->user_class; // Assuming you have a user_class attribute

    // Check if the user class exists and if the permission is set
    return isset($permissions[$userClass]) && in_array($permission, $permissions[$userClass]);
}
 //Shoutbox//

 public function shoutbox()
 {
     return $this->hasMany(Shoutbox::class);
 }

 public function messages()
 {
     return $this->hasMany(Message::class);
 }

 public function sentMessages()
 {
     return $this->hasMany(Message::class, 'sender_id');
 }

 public function receivedMessages()
 {
     return $this->hasMany(Message::class, 'receiver_id');
 }

 public function histories()
    {
        return $this->hasMany(History::class, 'user_id');
    }

     /**
     * Check if the user is currently banned.
     */
    public function isBanned()
    {
        return $this->banned_until && Carbon::parse($this->banned_until)->isFuture();
    }

    /**
     * Reset failed login attempts and remove ban.
     */
    public function resetFailedAttempts()
    {
        $this->failed_attempts = 0;
        $this->banned_until = null;
        $this->save();
    }

    /**
     * Increment failed login attempts and apply ban if necessary.
     */
    public function incrementFailedAttempts($maxAttempts, $banDurationHours)
    {
        $this->failed_attempts++;
        if ($this->failed_attempts >= $maxAttempts) {
            $this->banned_until = Carbon::now()->addHours($banDurationHours);
        }
        $this->save();
    }


    public function timeline()
{
    return $this->hasMany(\App\Models\UserTimeline::class);
}

public function warnings()
{
    return $this->hasMany(Warning::class, 'user_id');
}

public function invites()
{
    return $this->hasMany(Invite::class, 'inviter_id');  // 'inviter_id' is the foreign key
}

// Define the relationship for the invites a user has used
public function invitesUsed()
{
    return $this->hasMany(Invite::class, 'user_id'); // assuming 'user_id' is the correct field
}

public function inviter()
{
    return $this->belongsTo(User::class, 'invited_by');
}

public function slots()
{
    return $this->hasMany(UserSlot::class);
}
public function hasAvailableSlot()
{
    return $this->slots > 0;
}
public function userSlots()
{
    return $this->hasMany(UserSlot::class);
}

public function isOnline()
{
    // Consider user online if they've been active in the last 5 minutes
    return $this->last_activity && $this->last_activity->gt(now()->subMinutes(5));
}

public function seedboxes()
{
    return $this->hasMany(Seedbox::class);
}

public function topicSubscriptions()
{
    return $this->hasMany(TopicSubscription::class, 'user_id');
}

public function deletedBy()
{
    return $this->belongsTo(User::class, 'deleted_by');
}

public function announcements()
{
    return $this->belongsToMany(Announcement::class)
        ->withPivot('seen_at')
        ->withTimestamps();
}

/*
|--------------------------------------------------------------------------
| Seeder Rank System
|--------------------------------------------------------------------------
*/

public const SEEDER_RANKS = [
    0 => [
        'name' => 'New Seeder',
        'icon' => '🌱',
        'min'  => 0,
    ],
    1 => [
        'name' => 'Bronze',
        'icon' => '🥉',
        'min'  => 101,
    ],
    2 => [
        'name' => 'Silver',
        'icon' => '🥈',
        'min'  => 301,
    ],
    3 => [
        'name' => 'Gold',
        'icon' => '🥇',
        'min'  => 601,
    ],
    4 => [
        'name' => 'Elite',
        'icon' => '💎',
        'min'  => 1001,
    ],
    5 => [
        'name' => 'Legend',
        'icon' => '👑',
        'min'  => 2001,
    ],
];

/*
|--------------------------------------------------------------------------
| Calculate Seeder Rank
|--------------------------------------------------------------------------
*/

public static function calculateSeederRank($reputation)
{
    $rank = 0;

    foreach (self::SEEDER_RANKS as $level => $data) {

        if ($reputation >= $data['min']) {
            $rank = $level;
        }

    }

    return $rank;
}

/*
|--------------------------------------------------------------------------
| Seeder Icon
|--------------------------------------------------------------------------
*/

public function getSeederIconAttribute()
{
    return self::SEEDER_RANKS[$this->seeder_rank]['icon'] ?? '🌱';
}

/*
|--------------------------------------------------------------------------
| Seeder Rank Name
|--------------------------------------------------------------------------
*/

public function getSeederRankNameAttribute()
{
    return self::SEEDER_RANKS[$this->seeder_rank]['name'] ?? 'New Seeder';
}

/*
|--------------------------------------------------------------------------
| Seeder Rank Data Helper
|--------------------------------------------------------------------------
*/

public function getSeederRankData()
{
    return self::SEEDER_RANKS[$this->seeder_rank] ?? self::SEEDER_RANKS[0];
}

/*
|--------------------------------------------------------------------------
| Seeder Rank Progress
|--------------------------------------------------------------------------
*/

public function getSeederRankProgressAttribute()
{
    $currentRank = $this->seeder_rank;
    $reputation = $this->seeding_reputation;

    $currentMin = self::SEEDER_RANKS[$currentRank]['min'];

    $nextRank = $currentRank + 1;

    if (!isset(self::SEEDER_RANKS[$nextRank])) {
        return 100; // Max rank reached
    }

    $nextMin = self::SEEDER_RANKS[$nextRank]['min'];

    $range = $nextMin - $currentMin;

    if ($range <= 0) {
        return 100;
    }

    $progress = (($reputation - $currentMin) / $range) * 100;

    return max(0, min(100, round($progress)));
}

/*
|--------------------------------------------------------------------------
| Next Seeder Rank
|--------------------------------------------------------------------------
*/

public function getNextSeederRankAttribute()
{
    $nextRank = $this->seeder_rank + 1;

    if (!isset(self::SEEDER_RANKS[$nextRank])) {
        return null;
    }

    return self::SEEDER_RANKS[$nextRank];
}

//Forum Relationships

public function forumTopics()
{
    return $this->hasMany(ForumTopic::class, 'user_id');
}

public function forumPosts()
{
    return $this->hasMany(ForumPost::class, 'user_id');
}

//


public function forumPostLikes()
{
    return $this->hasMany(ForumPostLike::class, 'user_id');
}


}
