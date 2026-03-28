<?php

namespace App\Livewire;

use App\Events\NewPrivateMessage;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatWindow extends Component
{
    public User $recipient;

    public string $message = '';

    public bool $sending = false;

    public function mount(User $recipient): void
    {
        $this->recipient = $recipient;
        $this->markAsRead();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|string|max:5000',
        ]);

        $currentUser = auth()->user();

        $pm = PrivateMessage::create([
            'sender_id' => $currentUser->id,
            'recipient_id' => $this->recipient->id,
            'message' => $this->message,
            'church_id' => $currentUser->church_id,
        ]);

        broadcast(new NewPrivateMessage($pm))->toOthers();

        $this->message = '';
        $this->dispatch('message-sent');
    }

    #[On('echo-private:private-messages.{recipientId},NewPrivateMessage')]
    public function onNewMessage(): void
    {
        $this->markAsRead();
        $this->dispatch('scroll-to-bottom');
    }

    public function getListeners(): array
    {
        return [
            'echo-private:private-messages.'.auth()->id().',NewPrivateMessage' => 'onNewMessage',
        ];
    }

    public function loadMessages()
    {
        $currentUserId = auth()->id();

        return PrivateMessage::where(function ($q) use ($currentUserId) {
            $q->where('sender_id', $currentUserId)
                ->where('recipient_id', $this->recipient->id);
        })->orWhere(function ($q) use ($currentUserId) {
            $q->where('sender_id', $this->recipient->id)
                ->where('recipient_id', $currentUserId);
        })
            ->orderBy('created_at')
            ->get();
    }

    public function render(): View
    {
        $messages = $this->loadMessages();
        $currentUserId = auth()->id();

        return view('livewire.chat-window', compact('messages', 'currentUserId'));
    }

    protected function markAsRead(): void
    {
        PrivateMessage::where('sender_id', $this->recipient->id)
            ->where('recipient_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getRecipientIdProperty(): int
    {
        return $this->recipient->id;
    }
}
