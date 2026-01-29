@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="max-w-4xl mx-auto flex flex-col h-[calc(100dvh-12rem)] lg:h-[calc(100vh-8rem)]" x-data="chatApp()" x-init="$dispatch('pm-read')">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-t-2xl border border-gray-200 dark:border-gray-700 border-b-0 px-6 py-4 flex items-center">
        <a href="{{ route('pm.index') }}" class="p-2 -ml-2 mr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 lg:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
            <span class="text-lg font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($user->name, 0, 1) }}</span>
        </div>
        <div class="ml-3 flex-1">
            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->role === 'admin' ? 'Адміністратор' : ($user->role === 'leader' ? 'Лідер' : 'Служитель') }}</p>
        </div>
        <a href="{{ route('pm.index') }}" class="hidden lg:flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Всі повідомлення
        </a>
    </div>

    <!-- Messages -->
    <div class="flex-1 bg-gray-50 dark:bg-gray-900 border-x border-gray-200 dark:border-gray-700 overflow-y-auto p-4 space-y-4"
         id="messagesContainer"
         x-ref="messagesContainer">

        @php $lastDate = null; @endphp
        @foreach($messages as $message)
            @if($lastDate !== $message->created_at->format('Y-m-d'))
                @php $lastDate = $message->created_at->format('Y-m-d'); @endphp
                <div class="flex items-center justify-center my-4">
                    <span class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs rounded-full">
                        {{ $message->created_at->isToday() ? 'Сьогодні' : ($message->created_at->isYesterday() ? 'Вчора' : $message->created_at->format('d.m.Y')) }}
                    </span>
                </div>
            @endif

            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}"
                 data-message-id="{{ $message->id }}">
                <div class="max-w-[75%] {{ $message->sender_id === auth()->id() ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white' }} rounded-2xl px-4 py-2 shadow-sm">
                    <p class="text-sm whitespace-pre-wrap break-words">{{ $message->message }}</p>
                    <p class="text-xs mt-1 {{ $message->sender_id === auth()->id() ? 'text-primary-200' : 'text-gray-400' }}">
                        {{ $message->created_at->format('H:i') }}
                        @if($message->sender_id === auth()->id() && $message->read_at)
                            <span class="ml-1">✓✓</span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach

        <!-- New messages will be appended here -->
        <template x-for="msg in newMessages" :key="msg.id">
            <div class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                <div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm"
                     :class="msg.is_mine ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white'">
                    <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.message"></p>
                    <p class="text-xs mt-1" :class="msg.is_mine ? 'text-primary-200' : 'text-gray-400'" x-text="msg.created_at"></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Input -->
    <div class="bg-white dark:bg-gray-800 rounded-b-2xl border border-gray-200 dark:border-gray-700 border-t-0 p-4">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <textarea x-model="message"
                          @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                          rows="1"
                          placeholder="Напишіть повідомлення..."
                          class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 resize-none"
                          x-ref="messageInput"
                          @input="autoResize($el)"></textarea>
            </div>
            <button type="button"
                    @click="sendMessage()"
                    :disabled="!message.trim() || sending"
                    class="p-3 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global lock to prevent any double sends
window._pmSending = false;

function chatApp() {
    return {
        message: '',
        sending: false,
        newMessages: [],
        lastMessageId: {{ $messages->last()?->id ?? 0 }},
        pollInterval: null,

        init() {
            this.scrollToBottom();
            this.startPolling();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                container.scrollTop = container.scrollHeight;
            });
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },

        sendMessage() {
            // Global lock - prevent any double sends
            if (window._pmSending) {
                // blocked by global lock
                return;
            }
            window._pmSending = true;

            const messageText = this.message.trim();
            if (!messageText) {
                window._pmSending = false;
                return;
            }

            this.sending = true;
            this.message = '';

            fetch('{{ route('pm.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: {{ $user->id }},
                    message: messageText,
                }),
            })
            .then(response => {
                if (response.ok) {
                    // Don't add locally - let polling fetch it to avoid duplicates
                    // Force immediate poll to show message faster
                    setTimeout(() => this.pollMessages(), 500);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                this.message = messageText;
            })
            .finally(() => {
                window._pmSending = false;
                this.sending = false;
                this.$refs.messageInput.style.height = 'auto';
            });
        },

        startPolling() {
            this.pollInterval = setInterval(() => this.pollMessages(), 3000);
        },

        async pollMessages() {
            try {
                const response = await fetch(`{{ route('pm.poll', $user) }}?last_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        // Avoid duplicates by checking real message ID
                        if (!this.newMessages.find(m => m.id === msg.id)) {
                            this.newMessages.push(msg);
                            this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                        }
                    });
                    this.scrollToBottom();
                }
            } catch (error) {
                console.error('Polling error:', error);
            }
        },

        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    };
}
</script>
@endpush
@endsection
