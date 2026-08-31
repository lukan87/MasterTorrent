<?php

namespace App\Http\Controllers\Admin;

use App\Models\Conversation;
use App\Models\Peer;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Torrent;
use App\Models\Comment;
use App\Models\Message;
use App\Models\History;
use App\Models\UserClass;
use App\Models\UserSlot;
use App\Models\UserTimeline;
use App\Models\Warning;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendMassMessageJob;
use Notification;
use Illuminate\Notifications\DatabaseNotification;

class UserController extends Controller
{
    // Doar aceste ID-uri au voie sa promoveze useri peste clasa 3
    // Cand vrei sa permiti altcuiva promovarea, adaugi ID-ul aici.
    private const PROMOTE_ALLOWED_IDS = [
        893, 1, 3, 1067, 1069421, 1076853, 1083357,
    ];

    // (Optional, dar recomandat) Doar aceste ID-uri au voie sa stearga useri
    // Daca nu vrei restrictie la delete, poti scoate acest block si verificarea din destroy().
    private const DELETE_ALLOWED_IDS = [
        893, 1, 3, 1067, 1069421, 1076853, 1083357,
    ];

    // Afiseaza toti utilizatorii cu functionalitate de cautare
public function index(Request $request)
{
    $userClasses = UserClass::getClasses();

    $users = User::withTrashed()

        // keyword search
        ->when($request->filled('keyword'), function ($query) use ($request) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('email', 'LIKE', "%{$keyword}%")
                  ->orWhere('ip', 'LIKE', "%{$keyword}%");
            });
        })

        // class filter
        ->when($request->filled('class'), function ($query) use ($request) {
            $query->where('user_class', $request->class);
        })

        // IP
        ->when($request->filled('ip'), function ($query) use ($request) {
            $query->where('ip', 'LIKE', "%{$request->ip}%");
        })

        // email
        ->when($request->filled('email'), function ($query) use ($request) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        })

        // deleted users
        ->when($request->deleted === 'only', function ($query) {
            $query->onlyTrashed();
        })

        ->when($request->deleted === 'no', function ($query) {
            $query->whereNull('deleted_at');
        })

        // warned users
        ->when($request->warned === 'yes', function ($query) {
            $query->where('warned', 1);
        })

        // disabled accounts
        ->when($request->enabled === 'no', function ($query) {
            $query->where('enabled', 'no');
        })

        // low ratio
        ->when($request->ratio === 'low', function ($query) {
            $query->whereRaw('uploaded / NULLIF(downloaded,0) < 0.5');
        })

        // inactive users
        ->when($request->filled('inactive'), function ($query) use ($request) {
            $query->where('updated_at', '<', now()->subDays($request->inactive));
        })

        // seedbonus filter
        ->when($request->filled('seedbonus'), function ($query) use ($request) {
            $query->where('seedbonus', '<', $request->seedbonus);
        })

        ->orderBy('id', 'asc')
        ->paginate(15)
        ->withQueryString();

    $now = now();

    $last24Hours = User::where('created_at', '>=', $now->copy()->subDay())->count();
    $lastWeek = User::where('created_at', '>=', $now->copy()->subWeek())->count();
    $lastMonth = User::where('created_at', '>=', $now->copy()->subMonth())->count();
    $warnedUsers = User::where('warned', 1)->count();
    $deletedUsers = User::onlyTrashed()->count();

    return view('admin.users.index', compact(
        'users',
        'last24Hours',
        'lastWeek',
        'lastMonth',
        'userClasses',
        'deletedUsers',
        'warnedUsers'
    ));
}

    // Afiseaza formularul de editare pentru utilizator
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // Actualizeaza informatiile utilizatorului
public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $staff = Auth::user();

    if (
        $user->user_class >= $staff->user_class &&
        $staff->user_class !== UserClass::WEB_DEVELOPER
    ) {
        abort(403, 'You cannot edit users of equal or higher class.');
    }

    DB::transaction(function () use ($request, $user, $staff) {

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'seedbonus'     => 'nullable|numeric|min:0',
            'uploaded'      => 'nullable|numeric|min:0|max:100000',
            'downloaded'    => 'nullable|numeric|min:0|max:100000',
            'recovery_code' => 'nullable|string|min:6',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Helper for timeline
        |--------------------------------------------------------------------------
        */

        $log = function ($message) use ($user, $staff) {
            UserTimeline::create([
                'user_id'  => $user->id,
                'staff_id' => $staff->id,
                'comment'  => $message . " by {$staff->name}",
            ]);
        };

        $notify = function ($subject, $body) use ($user) {

            $systemId = 2;

            $conversation = Conversation::where(function ($q) use ($systemId, $user) {
                $q->where('user_one', $systemId)
                  ->where('user_two', $user->id);
            })
            ->orWhere(function ($q) use ($systemId, $user) {
                $q->where('user_one', $user->id)
                  ->where('user_two', $systemId);
            })
            ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_one'        => $systemId,
                    'user_two'        => $user->id,
                    'subject'         => 'System Notifications',
                    'last_message_at' => now(),
                ]);
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $systemId,
                'receiver_id'     => $user->id,
                'subject'         => $subject,
                'body'            => $body,
                'is_read'         => false,
            ]);

            $conversation->update([
                'last_message_at' => now()
            ]);
        };

        /*
        |--------------------------------------------------------------------------
        | Basic Profile
        |--------------------------------------------------------------------------
        */

        if ($user->name !== $request->name) {
            $log("✏️ Username changed from {$user->name} to {$request->name}");
            $user->name = $request->name;
        }

        if ($user->email !== $request->email) {
            $log("📧 Email changed from {$user->email} to {$request->email}");
            $user->email = $request->email;
        }

        if ($request->has('info') && $user->info !== $request->info) {
            $oldInfo = filled($user->info) ? $user->info : '[empty]';
            $newInfo = filled($request->info) ? $request->info : '[empty]';
            $log("📝 Profile information changed from {$oldInfo} to {$newInfo}");
            $user->info = $request->info;
        }

        if ($request->has('profile_image') && $user->profile_image !== $request->profile_image) {
            $oldImage = filled($user->profile_image) ? $user->profile_image : '[empty]';
            $newImage = filled($request->profile_image) ? $request->profile_image : '[empty]';
            $log("🖼️ Profile image changed from {$oldImage} to {$newImage}");
            $user->profile_image = $request->profile_image;
        }

        if ($request->filled('recovery_code')) {
            $user->recovery_code = Hash::make($request->recovery_code);
            $log("🔐 Recovery code updated");
        }

        /*
        |--------------------------------------------------------------------------
        | Invites & Slots
        |--------------------------------------------------------------------------
        */

        if ($request->has('invites') && $request->invites != $user->invites) {
            $log("🎟️ Invites changed from {$user->invites} to {$request->invites}");
            $user->invites = $request->invites;
        }

        if ($request->has('slots') && $request->slots != $user->slots) {
            $log("🧩 Slots changed from {$user->slots} to {$request->slots}");
            $user->slots = $request->slots;
        }

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'enabled'     => ['label' => 'Account access'],
            'downloadpos' => ['label' => 'Download permission'],
            'uploadpos'   => ['label' => 'Upload permission'],
            'donor'       => ['label' => 'Donor status'],
        ];

        foreach ($permissions as $field => $meta) {

            if (!$request->has($field)) {
                continue;
            }

            if ($user->$field != $request->$field) {

                $oldValue = $user->$field == 'yes' ? 'yes' : 'no';
                $newValue = $request->$field == 'yes' ? 'yes' : 'no';

                $log("⚙️ {$meta['label']} changed from {$oldValue} to {$newValue}");

                $user->$field = $request->$field;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Moderation Controls
        |--------------------------------------------------------------------------
        */

        $moderation = [
            'chatblock' => [
                'label'   => 'Chat access',
                'disable' => 'disabled',
                'enable'  => 'enabled',
            ],
            'commentblock' => [
                'label'   => 'Comments',
                'disable' => 'disabled',
                'enable'  => 'enabled',
            ],
            'forumblock' => [
                'label'   => 'Forum posting',
                'disable' => 'disabled',
                'enable'  => 'enabled',
            ],
        ];

        foreach ($moderation as $field => $messages) {

            if (!$request->has($field)) {
                continue;
            }

            $new = (int) $request->$field;

            if ($user->$field != $new) {

                $oldState = (int) $user->$field ? $messages['disable'] : $messages['enable'];
                $newState = $new ? $messages['disable'] : $messages['enable'];

                $user->$field = $new;

                $log("🚫 {$messages['label']} changed from {$oldState} to {$newState}");

                $notify(
                    "Account restriction update",
                    "{$messages['label']} changed from {$oldState} to {$newState} by {$staff->name}."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Special Permissions
        |--------------------------------------------------------------------------
        */

        $specialPermissions = [
            'is_immune' => [
                'label'   => 'Imune',
                'enable'  => 'enabled',
                'disable' => 'disabled'
            ],
            'is_freeleech' => [
                'label'   => 'Freeleech',
                'enable'  => 'enabled',
                'disable' => 'disabled'
            ],
        ];

        foreach ($specialPermissions as $field => $messages) {

            if (!$request->has($field)) {
                continue;
            }

            $new = (int) $request->$field;

            if ($user->$field != $new) {

                $oldState = (int) $user->$field ? $messages['enable'] : $messages['disable'];
                $newState = $new ? $messages['enable'] : $messages['disable'];

                $user->$field = $new;

                $log("🛡️ {$messages['label']} changed from {$oldState} to {$newState}");

                $notify(
                    "Account privileges updated",
                    "{$messages['label']} changed from {$oldState} to {$newState} by {$staff->name}."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Warning System
        |--------------------------------------------------------------------------
        */

        if ($request->has('warned')) {

            $newWarned = (int) $request->warned;
            $oldWarned = (int) $user->warned;

            $oldReason = filled($user->warned_reason) ? $user->warned_reason : '[empty]';
            $newReason = filled($request->warned_reason) ? $request->warned_reason : '[empty]';

            $oldUntil = filled($user->warned_until) ? $user->warned_until : '[empty]';
            $newUntil = filled($request->warned_until) ? $request->warned_until : '[empty]';

            if ($oldWarned !== $newWarned) {

                if ($newWarned && empty($request->warned_reason)) {
                    throw new \Exception('Warning reason required.');
                }

                $user->warned = $request->warned;
                $user->warned_reason = $request->warned_reason;
                $user->warned_until = $request->warned_until;

                $log(
                    "⚠️ Warned changed from " .
                    ($oldWarned ? 'yes' : 'no') .
                    " to " .
                    ($newWarned ? 'yes' : 'no') .
                    "; reason: {$oldReason} to {$newReason}; until: {$oldUntil} to {$newUntil}"
                );

                if ($request->warned) {
                    $notify(
                        "You have been warned",
                        "You have received a warning from {$staff->name}.\n\nReason: {$request->warned_reason}"
                    );
                } else {
                    $notify(
                        "Warning Removed",
                        "Your warning has been removed by {$staff->name}."
                    );
                }
            } else {
                $reasonChanged = $user->warned_reason !== $request->warned_reason;
                $untilChanged = $user->warned_until != $request->warned_until;

                if ($newWarned && empty($request->warned_reason)) {
                    throw new \Exception('Warning reason required.');
                }

                if ($reasonChanged || $untilChanged) {
                    $user->warned_reason = $request->warned_reason;
                    $user->warned_until = $request->warned_until;

                    $log(
                        "⚠️ Warning details updated; reason: {$oldReason} to {$newReason}; until: {$oldUntil} to {$newUntil}"
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Uploaded / Downloaded
        |--------------------------------------------------------------------------
        */
if ($request->has('uploaded')) {

    $new = (float) $request->uploaded;

    $currentBytes = is_numeric($user->uploaded) ? (float) $user->uploaded : 0;
    $current = round($currentBytes / (1024 ** 3), 2);

    if ($new != $current) {

        $user->uploaded = (int) ($new * (1024 ** 3));

        $log("⬆️ Uploaded changed from {$current}GB to {$new}GB");
    }
}

if ($request->has('downloaded')) {

    $new = (float) $request->downloaded;

    $currentBytes = is_numeric($user->downloaded) ? (float) $user->downloaded : 0;
    $current = round($currentBytes / (1024 ** 3), 2);

    if ($new != $current) {

        $user->downloaded = (int) ($new * (1024 ** 3));

        $log("⬇️ Downloaded changed from {$current}GB to {$new}GB");
    }
}

        /*
        |--------------------------------------------------------------------------
        | Seedbonus
        |--------------------------------------------------------------------------
        */

        if ($request->has('seedbonus')) {

            $new = round((float) $request->seedbonus, 2);
            $current = round((float) $user->seedbonus, 2);

            if ($new !== $current) {

                $difference = $new - $current;

                if ($difference > 0) {
                    $message = "➕ Added {$difference} seedbonus ({$current} → {$new})";
                } else {
                    $message = "➖ Deducted " . abs($difference) . " seedbonus ({$current} → {$new})";
                }

                $user->seedbonus = $new;

                UserTimeline::create([
                    'user_id'  => $user->id,
                    'staff_id' => $staff->id,
                    'comment'  => "{$message} by {$staff->name}",
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VIP System
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vip_until')) {

            $oldVip = $user->vip_until ? $user->vip_until->toDateString() : '[none]';

            if ($request->vip_until === 'remove') {

                $user->vip_until = null;
                $log("⭐ VIP changed from {$oldVip} to removed");

                $notify(
                    "VIP Status Removed",
                    "Your VIP status has been removed by {$staff->name}."
                );
            } else {

                $weeks = (int) filter_var($request->vip_until, FILTER_SANITIZE_NUMBER_INT);
                $vipDate = now()->addWeeks($weeks);

                $user->vip_until = $vipDate;

                $log("⭐ VIP changed from {$oldVip} to {$vipDate->toDateString()}");

                $notify(
                    "VIP Status Granted",
                    "Your VIP status has been granted until {$vipDate->toDateString()} by {$staff->name}."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | User Class Promotion
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_class') && $staff->user_class >= UserClass::MODERATOR) {

            $newClass = (int) $request->user_class;
            $oldClass = (int) $user->user_class;

            if ($newClass !== $oldClass) {

                // Cannot edit users above yourself
                if ($user->user_class >= $staff->user_class && $staff->user_class !== UserClass::WEB_DEVELOPER) {
                    abort(403, 'You cannot modify users of equal or higher class.');
                }

                // Cannot promote to equal or higher class
                if ($newClass >= $staff->user_class && $staff->user_class !== UserClass::WEB_DEVELOPER) {
                    abort(403, 'You cannot promote a user to your class or higher.');
                }

                // Nobody can assign WEB_DEVELOPER except WEB_DEVELOPER
                if ($newClass === UserClass::WEB_DEVELOPER && $staff->user_class !== UserClass::WEB_DEVELOPER) {
                    abort(403, 'This class cannot be assigned.');
                }

                /*
                |------------------------------------------------------------------
                | VIP Promotion perks
                |------------------------------------------------------------------
                */

                if ($newClass === UserClass::VIP && $oldClass < UserClass::VIP) {

                    $oldImmune = (int) $user->is_immune ? 'enabled' : 'disabled';
                    $oldFreeleech = (int) $user->is_freeleech ? 'enabled' : 'disabled';
                    $oldWarnedState = (int) $user->warned ? 'yes' : 'no';
                    $oldWarnedUntil = filled($user->warned_until) ? $user->warned_until : '[empty]';
                    $oldHitAndRun = $user->hit_and_run_count;

                    $user->is_immune = 1;
                    $user->is_freeleech = 1;
                    $user->warned = 0;
                    $user->warned_until = null;
                    $user->warned_reason = null;
                    $user->hit_and_run_count = 0;

                    DB::table('warnings')
                        ->where('user_id', $user->id)
                        ->delete();

                    DB::table('history')
                        ->where('user_id', $user->id)
                        ->where('hitrun', 1)
                        ->update([
                            'hitrun'   => 0,
                            'seedtime' => 86400,
                        ]);

                    $log("⭐ VIP privileges granted");
                    $log("🛡️ Imune changed from {$oldImmune} to enabled");
                    $log("💎 Freeleech changed from {$oldFreeleech} to enabled");
                    $log("⚠️ Warned changed from {$oldWarnedState} to no; until: {$oldWarnedUntil} to [empty]");
                    $log("🏃 Hit and run count changed from {$oldHitAndRun} to 0");
                }

                $user->user_class = $newClass;

                $log(
                    "⬆️ User class changed from " .
                    UserClass::getClassName($oldClass) .
                    " to " .
                    UserClass::getClassName($newClass)
                );

                $notify(
                    "Account Class Updated",
                    "Your account class has been changed to " .
                    UserClass::getClassName($newClass) .
                    " by {$staff->name}."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $user->save();
    });

    return redirect()
        ->route('admin.users.index')
        ->with('success', 'User updated successfully.');
}
    // Afiseaza informatiile detaliate ale utilizatorului
    public function show($name)
    {
        $user = User::where('name', $name)->firstOrFail();

        $torrentsUploaded = Torrent::where('owner', $user->id)->paginate(50);
        $torrentsDownloaded = History::where('user_id', $user->id)->paginate(50);

        $seedingTorrents = Peer::where('user_id', $user->id)->where('seeder', 1)->paginate(50);
        $leechingTorrents = Peer::where('user_id', $user->id)->where('seeder', 0)->paginate(50);

        $comments = Comment::where('user_id', $user->id)->paginate(50);
        $messages = Message::where('sender_id', $user->id)->paginate(50);

        return view('admin.users.show', compact('user', 'torrentsUploaded', 'torrentsDownloaded', 'seedingTorrents', 'leechingTorrents', 'comments', 'messages'));
    }

public function sendMassMessage(Request $request)
{
    $request->validate([
        'message' => 'required|string',
        'user_class' => 'required|array',
        'user_class.*' => 'in:' . implode(',', array_keys(UserClass::getClasses())),
    ]);

    SendMassMessageJob::dispatch(
        $request->message,
        $request->user_class,
        Auth::id()
    );

    return redirect()
        ->route('admin.users.index')
        ->with('success', 'Mass message queued and will be sent in the background.');
}

public function previewMassMessage(Request $request)
{
    $request->validate([
        'user_class' => 'required|array',
        'user_class.*' => 'in:' . implode(',', array_keys(UserClass::getClasses())),
    ]);

    $count = User::whereIn('user_class', $request->user_class)
        ->whereNull('deleted_at')
        ->where('updated_at', '>=', now()->subMonths(12))
        ->count();

    return response()->json([
        'recipients' => $count
    ]);
}

    public function comments()
    {
        $comments = Comment::with(['user', 'torrent'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.comments', compact('comments'));
    }

    // Delete a user (restrictie pe whitelist)
public function destroy($id)
{
    $currentUser = Auth::user();

    $allowed = in_array((int) $currentUser->id, self::DELETE_ALLOWED_IDS, true)
        || $currentUser->user_class === UserClass::WEB_DEVELOPER;

    if (!$allowed) {
        \Log::warning('USER_DELETE_BLOCKED', [
            'actor_id' => $currentUser->id,
            'actor_name' => $currentUser->name,
            'target_user_id' => (int) $id,
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ]);

        abort(403, 'Nu ai permisiunea sa stergi utilizatori.');
    }

    $user = User::findOrFail($id);

    if (
    $user->user_class >= $currentUser->user_class &&
    $currentUser->user_class !== UserClass::WEB_DEVELOPER
) {
    abort(403, 'You cannot delete users of equal or higher class.');
}

    DB::transaction(function () use ($user, $currentUser) {

        // stop tracker activity
        Peer::where('user_id', $user->id)->delete();
        History::where('user_id', $user->id)->delete();

        // reassign torrents still seeded
        Torrent::where('owner', $user->id)
            ->where('seeders', '>', 0)
            ->update(['owner' => 2]);

        // delete torrents with no seeders
        $torrents = Torrent::where('owner', $user->id)
            ->where('seeders', 0)
            ->get();

        foreach ($torrents as $torrent) {
            app(\App\Http\Controllers\TorrentController::class)
                ->deleteTorrentCompletely($torrent);
        }

        // log in timeline
        UserTimeline::create([
    'user_id' => $user->id,
    'staff_id' => $currentUser->id,
    'comment' => "🗑️ Account soft deleted by {$currentUser->name}",
    ]);


        $user->deleted_by = $currentUser->id;
        $user->save();

        $user->delete(); // soft delete
    });

    \Log::warning('USER_SOFT_DELETE_OK', [
        'actor_id' => $currentUser->id,
        'actor_name' => $currentUser->name,
        'target_user_id' => $user->id,
        'target_user_name' => $user->name,
    ]);

     return redirect()
    ->back()
    ->with('status', 'User deleted successfully!');
}

public function restore($id)
{
    $currentUser = Auth::user();

    $allowed = in_array((int) $currentUser->id, self::DELETE_ALLOWED_IDS, true)
        || $currentUser->user_class === UserClass::WEB_DEVELOPER;

    if (!$allowed) {
        abort(403);
    }

    $user = User::withTrashed()->findOrFail($id);

    $user->restore();
    $user->deleted_by = null;
    $user->save();

    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => $currentUser->id,
        'comment' => "♻️ Account restored by {$currentUser->name}",
    ]);

   

        return redirect()
    ->back()
    ->with('success', 'User restored successfully.');
}

public function deletePermanently($id)
{
    $currentUser = Auth::user();

    $allowed = in_array((int) $currentUser->id, self::DELETE_ALLOWED_IDS, true)
        || $currentUser->user_class === UserClass::WEB_DEVELOPER;

    if (!$allowed) {
        abort(403);
    }

    $user = User::withTrashed()->findOrFail($id);

    DB::transaction(function () use ($user) {

        // Remove tracker activity
        Peer::where('user_id', $user->id)->delete();
        History::where('user_id', $user->id)->delete();

        /*
|--------------------------------------------------------------------------
| Remove messages & conversations
|--------------------------------------------------------------------------
*/

$conversationIds = Message::where('receiver_id', $user->id)
    ->orWhere('sender_id', $user->id)
    ->pluck('conversation_id')
    ->unique()
    ->filter();

Message::where('receiver_id', $user->id)
    ->orWhere('sender_id', $user->id)
    ->delete();

/*
|--------------------------------------------------------------------------
| Remove empty conversations
|--------------------------------------------------------------------------
*/

foreach ($conversationIds as $conversationId) {

    $hasMessages = Message::where('conversation_id', $conversationId)->exists();

    if (!$hasMessages) {
        Conversation::where('id', $conversationId)->delete();
    }
}

        // Remove user content
        Comment::where('user_id', $user->id)->delete();
        UserTimeline::where('user_id', $user->id)->delete();
        Warning::where('user_id', $user->id)->delete();
        UserSlot::where('user_id', $user->id)->delete();
        Ticket::where('user_id', $user->id)->delete();

        // Remove notifications
    //    DatabaseNotification::where('notifiable_id', $user->id)
    // ->where('notifiable_type', User::class)
    // ->delete();

        // Delete all torrents owned by user (including soft deleted ones)
        $torrents = Torrent::withTrashed()
            ->where('owner', $user->id)
            ->get();

        foreach ($torrents as $torrent) {
            $this->deleteTorrentCompletely($torrent);
        }

        // Remove notifications
       $user->notifications()->delete();

        // Permanently remove user
        $user->forceDelete();
    });

    return redirect()
    ->back()
    ->with('status', 'User permanently deleted.');
}

}
