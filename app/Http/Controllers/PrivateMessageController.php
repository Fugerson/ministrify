<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrivateMessageController extends Controller
{
    use RequiresChurch;

    /**
     * Display inbox - list of conversations
     */
    public function index()
    {
        $user = auth()->user();
        $church = $this->getChurchOrFail();

        // Get all conversations
        $conversations = PrivateMessage::conversationsForUser($church->id, $user->id);

        // Get unread count
        $unreadCount = PrivateMessage::unreadCount($church->id, $user->id);

        return view('private-messages.index', compact('conversations', 'unreadCount'));
    }

    /**
     * Show conversation with specific user
     */
    public function show(User $user)
    {
        $currentUser = auth()->user();
        $church = $this->getChurchOrFail();

        // Make sure target user belongs to the same church (via pivot)
        if (!$user->belongsToChurch($church->id)) {
            abort(404);
        }

        // Get conversation
        $messages = PrivateMessage::conversation($church->id, $currentUser->id, $user->id)
            ->with(['sender', 'recipient'])
            ->get();

        // Mark received messages as read
        PrivateMessage::where('church_id', $church->id)
            ->where('sender_id', $user->id)
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get all users for compose (via pivot)
        $users = $church->members()
            ->where('users.id', '!=', $currentUser->id)
            ->orderBy('name')
            ->get();

        return view('private-messages.show', compact('user', 'messages', 'users'));
    }

    /**
     * Start new conversation
     */
    public function create()
    {
        $currentUser = auth()->user();
        $church = $this->getChurchOrFail();

        $users = $church->members()
            ->where('users.id', '!=', $currentUser->id)
            ->orderBy('name')
            ->get();

        return view('private-messages.create', compact('users'));
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $church = $this->getChurchOrFail();

        // Check if broadcast to all
        if ($request->input('recipient_id') === 'all') {
            if (!$currentUser->canCreate('announcements')) {
                abort(403, 'У вас немає прав для масових повідомлень');
            }

            $request->validate([
                'message' => 'required|string|max:5000',
            ]);

            $recipients = $church->members()
                ->where('users.id', '!=', $currentUser->id)
                ->pluck('users.id');

            foreach ($recipients as $recipientId) {
                PrivateMessage::create([
                    'church_id' => $church->id,
                    'sender_id' => $currentUser->id,
                    'recipient_id' => $recipientId,
                    'message' => $request->input('message'),
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'broadcast' => true, 'count' => $recipients->count()]);
            }

            return redirect()->route('pm.index')
                ->with('success', 'Повідомлення надіслано ' . $recipients->count() . ' користувачам');
        }

        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        // Verify recipient belongs to the same church (via pivot)
        $recipient = User::findOrFail($validated['recipient_id']);
        if (!$recipient->belongsToChurch($church->id)) {
            abort(403);
        }

        PrivateMessage::create([
            'church_id' => $church->id,
            'sender_id' => $currentUser->id,
            'recipient_id' => $validated['recipient_id'],
            'message' => $validated['message'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('pm.show', $recipient)
            ->with('success', 'Повідомлення надіслано');
    }

    /**
     * Get unread count (for AJAX)
     */
    public function unreadCount()
    {
        $user = auth()->user();
        $church = $this->getChurchOrFail();
        $count = PrivateMessage::unreadCount($church->id, $user->id);

        return response()->json(['count' => $count]);
    }

    /**
     * Get new messages in conversation (for polling)
     */
    public function poll(User $user, Request $request)
    {
        $currentUser = auth()->user();
        $church = $this->getChurchOrFail();

        $lastId = $request->input('last_id', 0);

        $messages = PrivateMessage::conversation($church->id, $currentUser->id, $user->id)
            ->where('id', '>', $lastId)
            ->with(['sender', 'recipient'])
            ->get();

        // Mark as read
        PrivateMessage::where('church_id', $church->id)
            ->where('sender_id', $user->id)
            ->where('recipient_id', $currentUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(function ($msg) use ($currentUser) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender?->name ?? 'Видалений користувач',
                    'is_mine' => $msg->sender_id === $currentUser->id,
                    'created_at' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d.m.Y'),
                ];
            }),
        ]);
    }
}
