<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMassMessageJob;
use App\Models\MassMessage;
use App\Models\MassMessageDelivery;
use App\Models\User;
use App\Models\UserClass;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MassMessageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:queued,sending,sent,failed,deleting',
            'sender' => 'nullable|in:system,user',
        ]);
        $query = MassMessage::query()->withDeliveryCounts()->latest('id');
        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(fn ($q) => $q->where('subject', 'like', $search)
                ->orWhere('body', 'like', $search)->orWhere('actor_name', 'like', $search)
                ->orWhere('sender_name', 'like', $search));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('sender')) {
            $query->where('send_as_system', $request->input('sender') === 'system');
        }
        $broadcasts = $query->paginate(20)->withQueryString();
        $stats = [
            'broadcasts' => MassMessage::count(),
            'delivered' => MassMessageDelivery::whereNotNull('delivered_at')->count(),
            'read' => MassMessageDelivery::read()->count(),
            'pending' => MassMessageDelivery::where('status', 'pending')->count(),
        ];

        return view('admin.mass-messages.index', compact('broadcasts', 'stats'));
    }

    public function create()
    {
        return view('admin.mass-messages.create', ['userClasses' => UserClass::getClasses()]);
    }

    private function validateAudience(Request $request): array
    {
        return $request->validate([
            'user_class' => 'required|array|min:1',
            'user_class.*' => 'required|integer|distinct|in:'.implode(',', array_keys(UserClass::getClasses())),
        ])['user_class'];
    }

    public function preview(Request $request)
    {
        $classes = $this->validateAudience($request);

        return response()->json(['recipients' => User::whereIn('user_class', $classes)->count()]);
    }

    public function store(Request $request)
    {
        $classes = $this->validateAudience($request);
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:100',
            'message' => 'required|string|max:16000',
            'send_as_system' => 'sometimes|boolean',
        ]);
        $actor = Auth::user();
        $asSystem = $request->boolean('send_as_system');
        $sender = $asSystem ? User::find(2) : $actor;
        if (! $sender) {
            throw ValidationException::withMessages(['send_as_system' => 'The System account is unavailable.']);
        }

        $broadcast = DB::transaction(function () use ($classes, $validated, $actor, $sender, $asSystem) {
            $broadcast = MassMessage::create([
                'actor_id' => $actor->id, 'actor_name' => $actor->name,
                'sender_id' => $sender->id, 'sender_name' => $sender->name,
                'send_as_system' => $asSystem, 'subject' => $validated['subject'] ?? 'Mass Message',
                'body' => $validated['message'], 'user_classes' => $classes, 'status' => 'queued',
            ]);
            // Freeze the audience so preview and logs reflect who was selected at submission.
            User::whereIn('user_class', $classes)->select(['id', 'name'])->chunkById(500, function ($users) use ($broadcast) {
                $now = now();
                MassMessageDelivery::insert($users->map(fn ($user) => [
                    'mass_message_id' => $broadcast->id, 'receiver_id' => $user->id,
                    'receiver_name' => $user->name, 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now,
                ])->all());
            });
            if (! $broadcast->deliveries()->exists()) {
                throw ValidationException::withMessages(['user_class' => 'No active accounts match these classes.']);
            }

            return $broadcast;
        });

        try {
            SendMassMessageJob::dispatch($broadcast->body, $classes, $sender->id, $broadcast->id);
        } catch (\Throwable $exception) {
            $broadcast->update(['status' => 'failed']);
            report($exception);

            return redirect()->route('admin.users.mass-messages.show', $broadcast)
                ->with('error', 'The broadcast could not be queued. No automatic resend was requested; check the delivery log.');
        }

        return redirect()->route('admin.users.mass-messages.show', $broadcast)
            ->with('success', 'Broadcast queued. Delivery and read status will appear here.');
    }

    public function show(Request $request, MassMessage $massMessage)
    {
        $request->validate(['search' => 'nullable|string|max:255', 'receipt' => 'nullable|in:read,unread,pending,removed,skipped']);
        $massMessage = MassMessage::withDeliveryCounts()->findOrFail($massMessage->id);
        $query = $massMessage->deliveries()->with(['message', 'receiver'])->orderBy('id');
        if ($request->filled('search')) {
            $query->where('receiver_name', 'like', '%'.$request->input('search').'%');
        }
        match ($request->input('receipt')) {
            'read' => $query->read(),
            'unread' => $query->whereHas('message', fn ($q) => $q->where('is_read', false)),
            'pending', 'skipped' => $query->where('status', $request->input('receipt')),
            'removed' => $query->removed(),
            default => null,
        };
        $deliveries = $query->paginate(50)->withQueryString();

        return view('admin.mass-messages.show', compact('massMessage', 'deliveries'));
    }

    public function destroyDelivery(MassMessage $massMessage, MassMessageDelivery $delivery)
    {
        abort_unless(Auth::user()?->user_class >= UserClass::ADMIN, 403);
        abort_unless($delivery->mass_message_id === $massMessage->id, 404);
        DB::transaction(function () use ($massMessage, $delivery) {
            // Use the same lock order as delivery to prevent a pending message being sent after deletion.
            MassMessage::whereKey($massMessage->id)->lockForUpdate()->firstOrFail();
            $delivery = MassMessageDelivery::whereKey($delivery->id)->lockForUpdate()->firstOrFail();
            $message = $delivery->message;
            $delivery->update(['status' => 'removed', 'was_read' => $message?->is_read, 'message_id' => null]);
            if ($message) {
                SystemMessageService::deleteMessage($message);
            }
        });

        return back()->with('success', 'Recipient copy removed. The delivery audit remains available.');
    }

    public function destroy(MassMessage $massMessage)
    {
        abort_unless(Auth::user()?->user_class >= UserClass::ADMIN, 403);
        DB::transaction(function () use ($massMessage) {
            $broadcast = MassMessage::whereKey($massMessage->id)->lockForUpdate()->firstOrFail();
            $broadcast->update(['status' => 'deleting']);
        });
        $massMessage->deliveries()->with('message')->chunkById(200, function ($deliveries) {
            foreach ($deliveries as $delivery) {
                if ($delivery->message) {
                    SystemMessageService::deleteMessage($delivery->message);
                }
            }
        });
        $massMessage->delete();

        return redirect()->route('admin.users.mass-messages.index')
            ->with('success', 'Broadcast and its recipient messages deleted.');
    }
}
