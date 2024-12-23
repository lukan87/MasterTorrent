<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TorrentRequest;
use App\Models\Category;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;


class TorrentRequestController extends Controller
{
    // Show all torrent requests
    public function index()
    {
        $requests = TorrentRequest::with('category')
            ->orderBy('id', 'desc') // Order by 'id' in descending order
            ->paginate(20);

        return view('requests.index', compact('requests'));
    }


    // Show the form to create a new request
    public function create()
    {
        $categories = Category::all();
        return view('requests.create', compact('categories'));
    }

    // Store a new torrent request
    public function store(Request $request)
    {

        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'requested_by' => 'nullable', // Set the requester ID
            'category_id' => 'required|exists:categories,id',
            'imdb_url' => 'nullable|url',
            'tmdb_url' => 'nullable|url',
            'steam_url' => 'nullable|url',
            'image' => 'nullable|url',  // Validate the image as a URL
            'description' => 'nullable|string|max:500'
        ]);

        // Handle image URL
        if ($request->filled('image')) {
            $validated['image'] = $request->image; // Store the provided URL
        }

        TorrentRequest::create($validated);

        return redirect()->route('requests.index')->with('success', 'Request created successfully!');
    }

    // Show a specific torrent request
    public function show($id)
    {
        $request = TorrentRequest::with('category')->findOrFail($id);
        return view('requests.show', compact('request'));
    }

    // Show the form to edit an existing request
    public function edit($id)
    {
        $request = TorrentRequest::findOrFail($id);
        $categories = Category::all();
        return view('requests.edit', compact('request', 'categories'));
    }

    // Update an existing torrent request
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'imdb_url' => 'nullable|url',
            'tmdb_url' => 'nullable|url',
            'steam_url' => 'nullable|url',
            'image' => 'nullable|url',  // Validate the image as a URL
            'description' => 'nullable|string|max:500'
        ]);

        $torrentRequest = TorrentRequest::findOrFail($id);

        // If an image URL is provided, update the image field
        if ($request->filled('image')) {
            $validated['image'] = $request->image; // Store the new image URL
        }

        $torrentRequest->update($validated);

        return redirect()->route('requests.index')->with('success', 'Request updated successfully!');
    }

    // Delete a torrent request
    public function destroy($id)
    {
        $request = TorrentRequest::findOrFail($id);
        $request->delete();

        return redirect()->route('requests.index')->with('success', 'Request deleted successfully!');
    }


    public function fillRequest(Request $request, $id)
    {
        $user = Auth::user();
        // Validate the incoming request
        $validated = $request->validate([
            'link' => 'required|string', // Ensure 'link' is provided and is a string
        ]);

        // Find the request by ID
        $torrentRequest = TorrentRequest::findOrFail($id);

        // Update the 'filled' column to 'yes' and store the link
        $torrentRequest->filled = 'yes'; // Set 'filled' to 'yes'
        $torrentRequest->link = $validated['link']; // Store the validated link
        $torrentRequest->filled_by = $user->id; // Set the current authenticated user

        // Save the changes
        $torrentRequest->save();

         // Send a message to the requester
       $messageContent = "Your torrent request for '{$torrentRequest->name}' has been filled. You can download it here: {$validated['link']}.";
       $subject = "Torrent Request";

       Message::create([
        'sender_id' => '2', // System
        'receiver_id' => $torrentRequest->requested_by, // Requester ID
        'subject' => $subject,
        'body' => $messageContent,
    ]);


        // Redirect back with success message
        return redirect()->route('requests.show', $id)->with('success', 'Request has been filled successfully.');
    }

}
