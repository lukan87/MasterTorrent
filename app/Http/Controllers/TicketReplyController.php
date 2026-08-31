<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\TicketAttachment;
use App\Models\Message;
use App\Models\User;
use App\Models\TicketEvent;
use App\Services\SystemMessageService;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketReplyController extends Controller
{

  public function store(Request $request, $ticketId)
{

    $request->validate([
        'message' => 'required',
        'attachment' => 'nullable',
        'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,zip,rar,txt,log|max:10240'
    ]);

    $ticket = Ticket::findOrFail($ticketId);

    $response = TicketResponse::create([
        'ticket_id' => $ticket->id,
        'user_id' => Auth::id(),
        'message' => $request->message,
        'is_staff_note' => $request->has('staff_note')
    ]);

    /*
    |--------------------------------------------------------------------------
    | Auto claim ticket when staff replies
    |--------------------------------------------------------------------------
    */

    if(Auth::user()->user_class > 5 && !$ticket->claimed_by){

        $ticket->update([
            'claimed_by' => Auth::id()
        ]);

        TicketEvent::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'event' => 'automatically claimed the ticket'
        ]);
    }

    TicketEvent::create([
        'ticket_id' => $ticket->id,
        'user_id' => Auth::id(),
        'event' => 'replied to the ticket'
    ]);

    /*
    |--------------------------------------------------------------------------
    | STAFF REPLIED → notify user
    |--------------------------------------------------------------------------
    */

    if(Auth::user()->user_class > 5){

SystemMessageService::send(
    Auth::id(),
    $ticket->user_id,
    'Staff replied to your ticket',
    'A staff member replied to your ticket:<br>
    <a href="'.route('tickets.show',[
        'id'=>$ticket->id,
        'slug'=>$ticket->slug
    ]).'">'.$ticket->title.'</a>'
);
    }

    /*
    |--------------------------------------------------------------------------
    | USER REPLIED → notify staff
    |--------------------------------------------------------------------------
    */

    if(Auth::user()->user_class <= 5){

        $staffMembers = User::where('user_class','>',5)->get();

        foreach ($staffMembers as $staff) {

SystemMessageService::send(
    Auth::id(),
    $staff->id,
    'User replied to ticket',
    'User replied to ticket:<br>
    <a href="'.route('tickets.show',[
        'id'=>$ticket->id,
        'slug'=>$ticket->slug
    ]).'">'.$ticket->title.'</a>'
);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */

    if ($request->hasFile('attachment')) {

        $files = $request->file('attachment');

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {

            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();

            $folder = 'ticket_attachments/'.$ticket->id;

            Storage::putFileAs($folder, $file, $filename);

            TicketAttachment::create([
                'ticket_response_id' => $response->id,
                'file_path' => $folder.'/'.$filename,
                'file_name' => $file->getClientOriginalName()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update ticket
    |--------------------------------------------------------------------------
    */

    $ticket->update([
        'last_replied_at' => now(),
        'last_replier_id' => Auth::id(),
        'status' => Auth::user()->user_class > 5
            ? 'Waiting User'
            : 'Waiting Staff'
    ]);

    return back();
}

/*
|--------------------------------------------------------------------------
| Conversation message helper
|--------------------------------------------------------------------------
*/

// private function sendConversationMessage($senderId,$receiverId,$subject,$body)
// {

//     $conversation = Conversation::where(function ($q) use ($senderId,$receiverId){

//         $q->where('user_one',$senderId)
//           ->where('user_two',$receiverId);

//     })->orWhere(function ($q) use ($senderId,$receiverId){

//         $q->where('user_one',$receiverId)
//           ->where('user_two',$senderId);

//     })->first();

//     if (!$conversation) {

//         $conversation = Conversation::create([
//             'user_one'=>$senderId,
//             'user_two'=>$receiverId,
//             'subject'=>'Support Notifications',
//             'last_message_at'=>now(),
//         ]);
//     }

//     Message::create([
//         'conversation_id'=>$conversation->id,
//         'receiver_id'=>$receiverId,
//         'sender_id'=>$senderId,
//         'subject'=>$subject,
//         'body'=>$body,
//         'is_read'=>0
//     ]);

//     $conversation->update([
//         'last_message_at'=>now()
//     ]);
// }

public function fetch($ticketId)
{
    $ticket = Ticket::with([
        'responses.user',
        'responses.attachments'
    ])->findOrFail($ticketId);

    return response()->json($ticket->responses);
}

public function download($id)
{
    $file = TicketAttachment::findOrFail($id);

 $path = storage_path('app/private/'.$file->file_path);


    if(!file_exists($path)){
        abort(404);
    }

    return response()->download($path, $file->file_name);
}


public function typing($ticketId)
{
   Cache::put('ticket_typing_'.$ticketId, [
    'user_id' => auth()->id(),
    'name' => auth()->user()->name
], 3);
}

public function typingStatus($ticketId)
{
    $data = Cache::get('ticket_typing_'.$ticketId);

return response()->json([
    'typing' => $data ? true : false,
    'name' => $data['name'] ?? null,
    'user_id' => $data['user_id'] ?? null
]);
}

}