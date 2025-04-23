<?php

namespace App\Http\Controllers\Admin;

use App\Models\Peer;
use App\Models\User;
use App\Models\Torrent;
use App\Models\Comment;
use App\Models\Message;
use App\Models\History;
use App\Models\UserClass;
use App\Models\UserTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Afișează toți utilizatorii cu funcționalitate de căutare
    public function index(Request $request)
    {

         // Obține clasele de utilizatori pentru a le afișa în formular
         $userClasses = UserClass::getClasses();  // Aceasta va returna un array de clase de utilizatori

        //  dd($userClasses);

        // Căutare utilizatori după nume sau email
        $searchTerm = $request->input('search');
        $users = User::query()
        ->when($searchTerm, function ($query, $searchTerm) {
            return $query->where('name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('ip', 'LIKE', "%{$searchTerm}%"); // Adaugă căutare după IP
        })
        ->paginate(15);


        $now = now();

        // Numără utilizatorii înregistrați în ultimele 24 de ore
        $last24Hours = User::where('created_at', '>=', $now->subDay())->count();
        // Numără utilizatorii înregistrați în ultima săptămână
        $lastWeek = User::where('created_at', '>=', $now->copy()->subWeek())->count();
        // Numără utilizatorii înregistrați în ultima lună
        $lastMonth = User::where('created_at', '>=', $now->copy()->subMonth())->count();

        return view('admin.users.index', compact('users', 'searchTerm', 'last24Hours', 'lastWeek', 'lastMonth', 'userClasses'));
    }


    // Afișează formularul de editare pentru utilizator
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }



    // Actualizează informațiile utilizatorului
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();
        $oldInvites = $user->invites;
        $oldSlots = $user->slots;

       // Validate and update user information
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        // Add other validation rules as needed
    ]);

    // Handle invites and slots updates
    $newInvites = $request->input('invites', $user->invites);
    $newSlots = $request->input('slots', $user->slots);

    $user->update($validatedData + [
        'invites' => $newInvites,
        'slots' => $newSlots,
    ]);

    // Track changes for timeline
    $timelineMessage = [];

    // Check for changes in invites
    if ($newInvites != $oldInvites) {
        $diffInvites = $newInvites - $oldInvites;
        if ($diffInvites > 0) {
            $timelineMessage[] = "Added {$diffInvites} invites";
        } elseif ($diffInvites < 0) {
            $timelineMessage[] = "Removed " . abs($diffInvites) . " invites";
        }
    }

    // Check for changes in slots
    if ($newSlots != $oldSlots) {
        $diffSlots = $newSlots - $oldSlots;
        if ($diffSlots > 0) {
            $timelineMessage[] = "Added {$diffSlots} slots";
        } elseif ($diffSlots < 0) {
            $timelineMessage[] = "Removed " . abs($diffSlots) . " slots";
        }
    }

    // If there was any change, create a timeline entry
    if (!empty($timelineMessage)) {
        $timelineEntry = new UserTimeline();
        $timelineEntry->user_id = $user->id;
        $timelineEntry->staff_id = auth()->user()->id;
        $timelineEntry->comment = implode(' and ', $timelineMessage) . " by " . auth()->user()->name;
        $timelineEntry->save();
    }

    

        // Actualizează câmpurile de bază ale utilizatorului
        $user->name = $request->name;
        $user->email = $request->email;
        $user->info = $request->info;

        // Actualizează câmpurile booleene cu 'yes' sau 'no'
        $changer = Auth::user();

        // Track changes for multiple fields
        $changes = [];

        // Check if `enabled` has changed (yes/no)
        if ($request->has('enabled') && $user->enabled != $request->enabled) {
            $changes[] = "enabled";
            $user->enabled = $request->enabled;
        }


        if ($request->input('warned') == 1 && empty($request->input('warned_reason'))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['warned_reason' => 'Warning reason is required when issuing a warning.']);
        }
        
        
// Check if `warned_until` has changed
if ($request->has('warned_until') && $user->warned_until != $request->warned_until) {
    $changes[] = "warned_until";
    $user->warned_until = $request->warned_until;
}

// Check if `warned_reason` has changed
if ($request->has('warned_reason') && $user->warned_reason !== $request->warned_reason) {
    $changes[] = "warned_reason";
    $user->warned_reason = $request->warned_reason;
}

// Check if `warned` has changed
if ($request->has('warned') && $user->warned != $request->warned) {
    $changes[] = "warned";
    $user->warned = $request->warned;
}

// Check if the user was just warned
if (
    $request->has('warned') && $request->input('warned') == 1 &&
    ($user->getOriginal('warned') == 0 || $user->getOriginal('warned') === null)
) {
    // Log to timeline
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => Auth::id(),
        'comment' => 'User was warned by ' . $currentUser->name .
                     ($request->warned_reason ? ' — Reason: ' . $request->warned_reason : ''),
    ]);

    // Send a message to the user
    Message::create([
        'sender_id' => 2,
        'receiver_id' => $user->id,
        'subject' => 'You have been warned',
        'body' => "You have received a warning from {$currentUser->name}." .
                  ($request->warned_reason ? "\n\nReason: " . $request->warned_reason : '') .
                  "\n\nPlease check your account details or contact staff for more information.",
        'is_read' => false,
    ]);
}



        // Check if `downloadpos` has changed (yes/no)
        if ($request->has('downloadpos') && $user->downloadpos != $request->downloadpos) {
            $changes[] = "downloadpos";
            $user->downloadpos = $request->downloadpos;
        }

        // Check if `uploadpos` has changed (yes/no)
        if ($request->has('uploadpos') && $user->uploadpos != $request->uploadpos) {
            $changes[] = "uploadpos";
            $user->uploadpos = $request->uploadpos;
        }

        // Check if `donor` has changed (yes/no)
        if ($request->has('donor') && $user->donor != $request->donor) {
            $changes[] = "donor";
            $user->donor = $request->donor;
        }

        // Check if `is_immune` has changed (1/0)
        if ($request->has('is_immune') && $user->is_immune != $request->is_immune) {
            $changes[] = "is_immune";
            $user->is_immune = $request->is_immune;
        }

        // Check if `is_freeleech` has changed (1/0)
        if ($request->has('is_freeleech') && $user->is_freeleech != $request->is_freeleech) {
            $changes[] = "is_freeleech";
            $user->is_freeleech = $request->is_freeleech;
        }

        // If there are changes, log them
        if (!empty($changes)) {
            // Build the comment based on changed fields
            $changedFields = implode(', ', $changes);
            $userChange = implode(', ', array_map(function($field) use ($request) {
                $value = $request->input($field);

                // Format the values (Yes/No for 'enabled', 'downloadpos', 'uploadpos', 'donor'; 1/0 for 'is_immune', 'is_freeleech')
                if (in_array($field, ['enabled', 'downloadpos', 'uploadpos', 'donor'])) {
                    $value = $value ? 'Yes' : 'No';
                } else {
                    $value = $value ? '1' : '0';
                }

                return "$field changed to $value";
            }, $changes));

            // Log the changes in UserTimeline
            UserTimeline::create([
                'user_id' => $user->id,
                'staff_id' => $changer->id, // ID of the user making the change
                'comment' => "Updated the following fields for user: $userChange by {$changer->name}.",
            ]);
        }

        // Actualizează imaginea de profil dacă este furnizată
        if ($request->filled('profile_image')) {
            $user->profile_image = $request->profile_image;
        }

        // Verifică și actualizează clasa utilizatorului doar pentru moderatori și utilizatori de nivel superior
        if ($currentUser->user_class >= UserClass::MODERATOR && $request->filled('user_class')) {
            // Verifică dacă clasa solicitată este diferită de clasa curentă
            if ((int) $request->user_class !== (int) $user->user_class) {
                // Previne promovările neautorizate
                if (
                    $request->user_class >= $currentUser->user_class || // Noua clasă este egală sau mai mare decât clasa utilizatorului curent
                    $id === $currentUser->id // Previne auto-promovarea
                ) {
                    return redirect()->back()->withErrors('Nu ai permisiunea de a face acest lucru.');
                }
        
                // Verifică dacă este o promovare la clasa 3
                if ((int) $request->user_class === 3 && (int) $user->user_class < 3) {
                    // Acordă privilegii speciale
                    $user->is_immune = 1;
                    $user->is_freeleech = 1;
        
                    // Resetare avertismente și hit-and-run
                    $user->warned = 0;
                    $user->warned_until = null;
                    $user->hit_and_run_count = 0;
        
                    // Șterge toate avertismentele utilizatorului
                    DB::table('warnings')->where('user_id', $user->id)->delete();
        
                    // Actualizează istoricul torentelor
                    DB::table('history')
                        ->where('user_id', $user->id)
                        ->where('hitrun', 1)
                        ->update([
                            'hitrun' => 0,
                            'seedtime' => 86400,
                        ]);
                }
        
                // Actualizează clasa utilizatorului
                $user->user_class = $request->user_class;
                $user->save();
        
                // Înregistrează schimbarea în UserTimeline
                UserTimeline::create([
                    'user_id' => $user->id,
                    'staff_id' => Auth::id(),
                    'comment' => 'Clasa schimbată în ' . UserClass::getClassName($request->user_class) . ' de ' . $currentUser->name,
                ]);
            }
        }
        

        // Verifică dacă durata VIP este selectată
        if ($request->filled('vip_until')) {
            // Obține durata selectată
            $vipDuration = $request->input('vip_until');
            $newVipUntil = null;

            // Verifică dacă utilizatorul a selectat să elimine statutul VIP
            if ($vipDuration === 'remove') {
                // Îndepărtează VIP doar dacă "Remove VIP" este selectat
                if ($user->vip_until !== null) {
                    $user->vip_until = null;
                    $user->user_class = 1;
                    $user->is_immune = 0;
                    $user->is_freeleech = 0;
                    $user->save();

                    // Înregistrează schimbarea în UserTimeline
                    UserTimeline::create([
                        'user_id' => $user->id,
                        'staff_id' => Auth::id(),
                        'comment' => 'Statutul VIP eliminat de ' . $currentUser->name,
                    ]);
                }
            } else {
                // Calculează data de expirare a VIP-ului în funcție de durata selectată
                switch ($vipDuration) {
                    case '4 weeks':
                        $newVipUntil = Carbon::now()->addWeeks(4);
                        break;
                    case '6 weeks':
                        $newVipUntil = Carbon::now()->addWeeks(6);
                        break;
                    case '8 weeks':
                        $newVipUntil = Carbon::now()->addWeeks(8);
                        break;
                    case '10 weeks':
                        $newVipUntil = Carbon::now()->addWeeks(10);
                        break;
                    case '12 weeks':
                        $newVipUntil = Carbon::now()->addWeeks(12);
                        break;
                    default:
                        $newVipUntil = null; // Dacă nu este selectat nimic, șterge VIP
                }

                // Actualizează doar dacă data de expirare a VIP-ului s-a schimbat
                if ($newVipUntil !== $user->vip_until) {
                    // Setează data de expirare a VIP-ului
                    $user->vip_until = $newVipUntil;

                    // Setează clasa utilizatorului la VIP (presupunând că clasa 3 reprezintă VIP)
                    $user->user_class = 3;
                    $user->is_immune = 1;
                    $user->is_freeleech = 1;

                    // Resetare avertisment și hit-and-run
                    $user->warned = 0;
                    $user->warned_until = null;
                    $user->hit_and_run_count = 0;
                    $user->save();

                    // Șterge toate avertismentele utilizatorului din tabela `warning`
    DB::table('warnings')->where('user_id', $user->id)->delete();

                     // Actualizează tabelul history pentru torentele utilizatorului
    DB::table('history')
    ->where('user_id', $user->id)
    ->where('hitrun', 1)
    ->update([
        'hitrun' => 0,
        'seedtime' => 86400,
    ]);

                    // Înregistrează schimbarea în UserTimeline
                    UserTimeline::create([
                        'user_id' => $user->id,
                        'staff_id' => Auth::id(),
                        'comment' => 'Statutul VIP setat până la ' . $user->vip_until->toDateString() . ' de ' . $currentUser->name,
                    ]);



                     // Trimite mesaj utilizatorului despre VIP
                     Message::create([
                        'sender_id' => 2, // ID-ul adminului care trimite mesajul
                        'receiver_id' => $user->id,
                        'subject' => 'VIP Status',
                        'body' => "Your VIP status has been set until {$user->vip_until->toDateString()} by {$currentUser->name}.",
                        'is_read' => false, // Mesajul este marcat ca necitit
                    ]);
                }
            }
        }


// Get the current authenticated user making the change
$changer = Auth::user();

// Update uploaded value only if it has changed
if ($request->has('uploaded')) {
    $newUploaded = $request->input('uploaded') * (1024 ** 3); // Convert from GB to bytes

    if ($user->uploaded != $newUploaded) { // Only update if the value has changed
        $oldUploaded = $user->uploaded;
        $user->uploaded = $newUploaded;

        // Log the change in UserTimeline
        UserTimeline::create([
            'user_id' => $user->id,
            'staff_id' => $changer->id, // ID of the user making the change
            'comment' => "Updated uploaded amount from " . ($oldUploaded / (1024 ** 3)) . " GB to " . ($newUploaded / (1024 ** 3)) . " GB by " . $changer->name . ".",
        ]);
    }
}

// Update downloaded value only if it has changed
if ($request->has('downloaded')) {
    $newDownloaded = $request->input('downloaded') * (1024 ** 3); // Convert from GB to bytes

    if ($user->downloaded != $newDownloaded) { // Only update if the value has changed
        $oldDownloaded = $user->downloaded;
        $user->downloaded = $newDownloaded;

        // Log the change in UserTimeline
        UserTimeline::create([
            'user_id' => $user->id,
            'staff_id' => $changer->id, // ID of the user making the change
            'comment' => "Updated downloaded amount from " . ($oldDownloaded / (1024 ** 3)) . " GB to " . ($newDownloaded / (1024 ** 3)) . " GB by " . $changer->name . ".",
        ]);
    }
}

$user->save();


        return redirect()->route('admin.users.index')->with('success', 'Utilizatorul a fost actualizat cu succes!');
    }


    // Afișează informațiile detaliate ale utilizatorului
    public function show($name)
    {
        // Găsește utilizatorul după nume (sau folosește ID-ul)
        $user = User::where('name', $name)->firstOrFail();

        // Obține lista de torente încărcate de utilizator
        $torrentsUploaded = Torrent::where('owner', $user->id)->paginate(50);

        // Obține lista de torente descărcate de utilizator
        $torrentsDownloaded = History::where('user_id', $user->id)->paginate(50);

        // Obține lista de torente pe care utilizatorul le semnalează
        $seedingTorrents = Peer::where('user_id', $user->id)->where('seeder', 1)->paginate(50);

        // Obține lista de torente pe care utilizatorul le descarcă
        $leechingTorrents = Peer::where('user_id', $user->id)->where('seeder', 0)->paginate(50);

        // Obține lista de comentarii făcute de utilizator
        $comments = Comment::where('user_id', $user->id)->paginate(50);

        // Obține lista de mesaje trimise de utilizator
        $messages = Message::where('sender_id', $user->id)->paginate(50);

        return view('admin.users.show', compact('user', 'torrentsUploaded', 'torrentsDownloaded', 'seedingTorrents', 'leechingTorrents', 'comments', 'messages'));
    }





    public function sendMassMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id', // Ensure users exist
            'user_class' => 'nullable|array', // Allow an array of user classes
            'user_class.*' => 'in:' . implode(',', array_keys(UserClass::getClasses())), // Validate each user_class is a valid class
        ]);
    
        // Verifică dacă user_class este gol (neselectat)
        if (!$request->filled('user_class')) {
            return redirect()->back()->with('error', 'Please select at least one user class to send the message.');
        }
    
        // Obține utilizatorii cărora să le trimitem mesajul, filtrat opțional de clasele de utilizatori sau de ID-urile specifice ale utilizatorilor
        $query = User::query();
    
        if ($request->filled('user_class')) {
            $query->whereIn('user_class', $request->user_class);
        }
    
        // Use chunk to process users in smaller batches
        $sentCount = 0;
        $query->chunk(100, function ($users) use (&$sentCount, $request) {
            foreach ($users as $user) {
                Message::create([
                    'sender_id' => 2, // Assuming the admin is sending the message
                    'receiver_id' => $user->id,
                    'subject' => 'Mass Message',
                    'body' => $request->message,
                    'is_read' => false, // You can customize the status as needed
                ]);
                
                // Increment the sent count
                $sentCount++;
            }
        });
    
        return redirect()->route('admin.users.index')->with('success', "$sentCount messages sent successfully!");
    }
    



 // Method to show all comments, paginated by 50, in descending order
 public function comments()
{
    // Retrieve all comments, eager load 'user' and 'torrent' relationships
    $comments = Comment::with(['user', 'torrent']) // Eager load both 'user' and 'torrent'
                        ->orderBy('created_at', 'desc')
                        ->paginate(50);

    // Return the view with the comments data
    return view('admin.users.comments', compact('comments'));
}


// Delete a user
public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();
    return redirect()->route('admin.users.index')->with('status', 'User deleted successfully!');
}



}

