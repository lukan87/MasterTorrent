<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketEvent;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketReplyController extends Controller
{
    public function store(Request $request, $ticketId)
    {
        $ticket = $this->accessibleTicket($ticketId);
        abort_if($ticket->is_locked, 403, 'Unlock this ticket before replying.');
        $isStaff = Auth::user()->user_class > 5;
        abort_if(! $isStaff && in_array($ticket->status, ['Resolved', 'Closed'], true), 403, 'This ticket is resolved or closed. Please create a new ticket for further help.');
        abort_if(! $isStaff && $request->boolean('staff_note'), 403);
        $data = $request->validate([
            'message' => 'required|string|max:20000',
            'staff_note' => 'sometimes|boolean',
            'attachment' => 'nullable|array|max:5',
            'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,zip,rar,txt,log|max:10240',
        ]);
        $isNote = $isStaff && $request->boolean('staff_note');
        $paths = [];
        try {
            DB::transaction(function () use ($ticketId, $request, $data, $isStaff, $isNote, &$paths) {
                $ticket = Ticket::lockForUpdate()->findOrFail($ticketId);
                abort_if($ticket->is_locked, 403, 'Unlock this ticket before replying.');
                abort_if(! $isStaff && in_array($ticket->status, ['Resolved', 'Closed'], true), 403, 'This ticket is resolved or closed. Please create a new ticket for further help.');
                $response = $ticket->responses()->create([
                    'user_id' => Auth::id(), 'message' => $data['message'], 'is_staff_note' => $isNote,
                ]);
                foreach ($request->file('attachment', []) as $file) {
                    $path = $file->store('ticket_attachments/'.$ticket->id, 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to save the attachment.');
                    }
                    $paths[] = $path;
                    $response->attachments()->create(['file_path' => $path, 'file_name' => $file->getClientOriginalName()]);
                }
                if ($isNote) {
                    // Internal discussion does not notify the requester or change the public status.
                    return;
                }
                if ($isStaff && ! $ticket->claimed_by && ! $ticket->assigned_to) {
                    $ticket->claimed_by = Auth::id();
                }
                $ticket->fill([
                    'last_replied_at' => now(), 'last_replier_id' => Auth::id(),
                    'status' => in_array($ticket->status, ['Resolved', 'Closed'], true) ? $ticket->status : ($isStaff ? 'Waiting User' : 'Waiting Staff'),
                ])->save();
                TicketEvent::create(['ticket_id' => $ticket->id, 'user_id' => Auth::id(), 'event' => 'replied to the ticket']);
                $recipients = $isStaff ? collect([$ticket->user_id])
                    : (($ticket->assigned_to || $ticket->claimed_by)
                        ? collect([$ticket->assigned_to, $ticket->claimed_by])->filter()->unique()
                        : User::where('user_class', '>', 5)->pluck('id'));
                foreach ($recipients as $recipient) {
                    if ($recipient == Auth::id()) {
                        continue;
                    }
                    SystemMessageService::send(Auth::id(), $recipient, 'New reply to support ticket',
                        'There is a new reply to ticket #'.$ticket->id.': '.$ticket->notificationLink());
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        if (request()->expectsJson()) {
            return \App\Services\PageBrowse::json(['message' => $isNote ? 'Internal note added. Only staff can see it.' : 'Your reply has been sent.']);
        }

        return back()->with('success', $isNote ? 'Internal note added. Only staff can see it.' : 'Your reply has been sent.');
    }

    public function fetch($ticketId)
    {
        $ticket = $this->accessibleTicket($ticketId);
        $responses = $ticket->responses()->visibleTo(Auth::user())->with(['user', 'attachments'])->orderBy('id')->get();

        // Return only conversation fields; never serialize full user records or storage paths.
        return response()->json($responses->map(fn ($response) => [
            'id' => $response->id,
            'message' => $response->message,
            'is_staff_note' => $response->is_staff_note || $response->is_internal,
            'created_at' => $response->created_at->toIso8601String(),
            'user' => ['name' => $response->user?->name ?? 'Deleted user', 'is_staff' => ($response->user?->user_class ?? 0) > 5],
            'attachments' => $response->attachments->map(fn ($file) => ['file_name' => $file->file_name, 'url' => route('tickets.download', $file->id)]),
        ]));
    }

    public function download($id)
    {
        $file = TicketAttachment::with('response.ticket')->findOrFail($id);
        abort_unless($file->response?->ticket?->canBeViewedBy(Auth::user()), 403);
        abort_if(($file->response->is_staff_note || $file->response->is_internal) && Auth::user()->user_class <= 5, 403);
        abort_unless(Storage::disk('local')->exists($file->file_path), 404);

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    public function typing($ticketId)
    {
        $ticket = $this->accessibleTicket($ticketId);
        abort_if($ticket->is_locked || (in_array($ticket->status, ['Resolved', 'Closed'], true) && Auth::user()->user_class <= 5), 403);
        Cache::put('ticket_typing_'.$ticketId, ['user_id' => Auth::id(), 'name' => Auth::user()->name], 3);

        return response()->noContent();
    }

    public function typingStatus($ticketId)
    {
        $this->accessibleTicket($ticketId);
        $data = Cache::get('ticket_typing_'.$ticketId);

        return response()->json(['typing' => (bool) $data, 'name' => $data['name'] ?? null, 'user_id' => $data['user_id'] ?? null]);
    }

    private function accessibleTicket($id): Ticket
    {
        $ticket = Ticket::findOrFail($id);
        abort_unless($ticket->canBeViewedBy(Auth::user()), 403);

        return $ticket;
    }
}
