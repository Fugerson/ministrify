<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tickets = SupportTicket::where('user_id', $user->id)
            ->with(['latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        // Transform for Kanban view
        $ticketsData = $tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'category_label' => $ticket->category_label,
                'priority' => $ticket->priority,
                'priority_label' => $ticket->priority_label,
                'status' => $ticket->status,
                'status_label' => $ticket->status_label,
                'time_ago' => $ticket->updated_at->diffForHumans(),
                'unread' => $ticket->unreadMessagesForUser(),
                'show_url' => route('support.show', $ticket),
            ];
        });

        return view('support.index', compact('ticketsData'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:bug,question,feature,other',
            'message' => 'required|string|max:10000',
        ]);

        $user = auth()->user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'church_id' => $user->church_id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => 'normal',
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_from_admin' => false,
        ]);

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Запит створено! Ми відповімо найближчим часом.');
    }

    public function show(SupportTicket $ticket)
    {
        // Ensure user owns this ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(404);
        }

        // Mark admin messages as read
        $ticket->messages()
            ->where('is_from_admin', true)
            ->where('is_internal', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $ticket->messages()
            ->where('is_internal', false)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('support.show', compact('ticket', 'messages'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_from_admin' => false,
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status' => $ticket->status === 'waiting' ? 'open' : $ticket->status,
        ]);

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Повідомлення надіслано!');
    }

    public function close(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(404);
        }

        $ticket->update(['status' => 'closed']);

        return redirect()->route('support.index')
            ->with('success', 'Запит закрито.');
    }
}
