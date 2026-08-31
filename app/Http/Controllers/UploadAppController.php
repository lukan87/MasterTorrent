<?php

namespace App\Http\Controllers;

use App\Models\UploadApp;
use App\Models\Message;
use App\Models\UserClass;
use Illuminate\Http\Request;

class UploadAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->user_class > 5) {
            // Admins can view all applications
            $uploadApps = UploadApp::with(['applicant', 'staff'])->get();
        } else {
            // Applicants can only see their own applications
            $uploadApps = UploadApp::with(['applicant', 'staff'])
                ->where('applicant_id', auth()->id())
                ->latest()->paginate(25);
        }
    
        return view('uploadapps.index', compact('uploadApps'));
    }
    
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('uploadapps.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $existing = UploadApp::where('applicant_id', auth()->id())
    ->where('status', 'pending')
    ->exists();

if ($existing) {
    return redirect()->route('uploadapps.index')
        ->with('error', 'You already have a pending uploader application.');
}

if (auth()->user()->user_class >= 5) {
    return redirect()->route('uploadapps.index')
        ->with('error', 'You are already an uploader.');
}

        $request->validate([
            'why_promoted' => 'required|string|max:255',
            'internal_speed' => 'nullable|url',
            'external_speed' => 'nullable|url',
            'external_sites' => 'nullable|string',
            'scene_access' => 'required|boolean',
            'know_torrents' => 'required|boolean',
            'understand_seeding' => 'required|boolean',
        ]);

        UploadApp::create([
            'applicant_id' => auth()->id(), // Automatically set to the logged-in user
            'why_promoted' => $request->input('why_promoted'),
            'internal_speed' => $request->input('internal_speed'),
            'external_speed' => $request->input('external_speed'),
            'external_sites' => $request->input('external_sites'),
            'scene_access' => $request->input('scene_access'),
            'know_torrents' => $request->input('know_torrents'),
            'understand_seeding' => $request->input('understand_seeding'),
            'status' => 'pending', // Default to 'pending'
        ]);

        return redirect()->route('uploadapps.index')->with('success', 'Application submitted successfully!');

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $uploadApp = UploadApp::with(['applicant', 'staff'])->findOrFail($id);

        if (auth()->id() !== $uploadApp->applicant_id && auth()->user()->user_class < 7) {
            abort(403, 'Unauthorized access to this application.');
        }
    
        return view('uploadapps.show', compact('uploadApp'));
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UploadApp $uploadApp)
    {
        if (auth()->user()->user_class < 7) {
            abort(403);
            }
        return view('uploadapps.edit', compact('uploadApp'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UploadApp $uploadApp)
    {
        $request->validate([
            'why_promoted' => 'required|string',
            'internal_speed' => 'nullable|url',
            'external_speed' => 'nullable|url',
            'external_sites' => 'nullable|string',
            'scene_access' => 'required|boolean',
            'know_torrents' => 'required|boolean',
            'understand_seeding' => 'required|boolean',
            'status' => 'required|in:pending,accepted,rejected',
        ]);

       $uploadApp->update([
    'why_promoted' => $request->why_promoted,
    'internal_speed' => $request->internal_speed,
    'external_speed' => $request->external_speed,
    'external_sites' => $request->external_sites,
    'scene_access' => $request->scene_access,
    'know_torrents' => $request->know_torrents,
    'understand_seeding' => $request->understand_seeding,
]);
        return redirect()->route('uploadapps.index')->with('success', 'Application updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    
    {
// Check if the user has sufficient privileges to delete
if (auth()->user()->user_class <= 6) {
    // Redirect back with an error message if the user is unauthorized
    return redirect()->route('uploadapps.index')->with('error', 'You are not authorized to delete this application.');
}

        // Find the UploadApp by its ID
        $uploadApp = UploadApp::findOrFail($id);
    
        // Delete the record
        $uploadApp->delete();
    
        // Redirect to the index page with a success message
        return redirect()->route('uploadapps.index')->with('success', 'Application deleted successfully!');
    }

    public function updateStatus(Request $request, $id)
{
    // Validate the status input
    $request->validate([
        'status' => 'required|in:pending,accepted,rejected',
    ]);

    // Find the UploadApp by ID
    $uploadApp = UploadApp::findOrFail($id);

     // Check if the status is being changed to 'accepted'
     if ($request->input('status') === 'accepted') {
        // Update the applicant's user_class to 5

      
        $uploadApp->applicant->update([
            'user_class' => 5,
        ]);

       $messageContent = "Cererea ta a fost acceptată. Pentru a-ți menține statutul de uploader, trebuie să uploadezi minim un torrent la fiecare 2 zile.
                          Dacă nu vei îndeplini această condiție, vei reveni la clasa inițială.";
       $subject = "Cerere uploader";

       Message::create([
        'sender_id' => '2', 
        'receiver_id' => $uploadApp->applicant_id,
        'subject' => $subject,
        'body' => $messageContent,
    ]);
    }

    if ($request->input('status') === 'rejected') {
        

       $messageContent = "Cererea ta a fost respinsă deoarece nu îndeplinești condițiile cerute.";
       $subject = "Cerere uploader";

       Message::create([
        'sender_id' => '2', 
        'receiver_id' => $uploadApp->applicant_id,
        'subject' => $subject,
        'body' => $messageContent,
    ]);
    }

    // Update the status
    $uploadApp->status = $request->input('status');
    $uploadApp->staff_id = auth()->user()->id;
    $uploadApp->save();

    // Redirect back to the show page with a success message
    return redirect()->route('uploadapps.show', $uploadApp->id)->with('success', 'Status updated successfully!');
}

}
