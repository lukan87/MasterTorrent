<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Models\UserClass;
use App\Models\Invite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\UserSlot;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

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



/**
     * Count the number of torrents being seeded by the user.
     *
     * @return int
     */
    public function seedingCount()
    {
        return $this->peers()
                    ->where('seeder', true)
                    ->distinct('torrent_id') // Ensure only unique torrents are counted
                    ->count('torrent_id'); // Count based on unique torrent IDs
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
                    ->distinct('torrent_id') // Add this to count unique torrents if desired
                    ->count('torrent_id'); // Count based on unique torrent IDs
    }


public function getSeedbonusPerHourAttribute()
{
    // Count the number of seeding torrents for the authenticated user
    $seedingCount = DB::table('peers')
    ->where('user_id', $this->id) // Assuming 'userid' refers to the user's ID in the peers table
    ->where('seeder', true) // Check if the user is a seeder
    ->distinct('torrent_id') // Count only distinct torrents
    ->count('torrent'); // Count based on the unique 'torrent' field

    // Define how many points per torrent per hour (example value)
    $pointsPerTorrent = 0.15; // Change this to your actual earning rate per torrent

    // Calculate the total earning rate
    return $seedingCount * $pointsPerTorrent; // Total points earned per hour
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




}
