@extends('layouts.system-admin')

@section('title', $ticket->subject)

@section('content')
<div class="space-y-6">
    <!-- Back link -->
    <a href="{{ route('system.support.index') }}" class="inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.sa_back_to_list') }}
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->category_color }}-500/20 text-{{ $ticket->category_color }}-400">
                        {{ $ticket->category_label }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                        {{ $ticket->status_label }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->priority_color }}-500/20 text-{{ $ticket->priority_color }}-400">
                        {{ $ticket->priority_label }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</h1>
            </div>

            <!-- Messages -->
            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($messages as $message)
                        <div class="p-6 {{ $message->is_internal ? 'bg-yellow-50 dark:bg-yellow-900/10 border-l-4 border-yellow-500' : ($message->is_from_admin ? 'bg-blue-50 dark:bg-blue-900/10' : '') }}">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $message->is_from_admin ? 'bg-primary-500/20' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    @if($message->is_from_admin)
                                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    @else
                                        <span class="text-gray-900 dark:text-white">{{ mb_substr($message->user?->name ?? $ticket->guest_name ?? __('app.guest'), 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $message->user?->name ?? $ticket->guest_name ?? __('app.guest') }}
                                        </span>
                                        @if($message->is_internal)
                                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-yellow-500/20 text-yellow-600 dark:text-yellow-400">
                                                {{ __('app.sa_internal_note') }}
                                            </span>
                                        @endif
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $message->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $message->message }}</div>
                                    @if($message->attachments)
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($message->attachments as $attachment)
                                                @if(str_starts_with($attachment['mime'], 'image/'))
                                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                                        <img src="{{ Storage::url($attachment['path']) }}" alt="{{ $attachment['name'] }}"
                                                             class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:opacity-80 transition-opacity">
                                                    </a>
                                                @else
                                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                                       class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors">
                                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $attachment['name'] }}</span>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Reply Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.sa_reply') }}</h2>
                <form action="{{ route('system.support.reply', $ticket) }}" method="POST" enctype="multipart/form-data" x-data="{ files: [] }">
                    @csrf
                    <textarea name="message" rows="4" required
                              placeholder="{{ __('app.sa_reply_placeholder') }}"
                              class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-4"></textarea>

                    <div class="mb-4">
                        <div class="flex items-center gap-2">
                            <label for="admin-attachments" class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ __('app.board_attach_file') }}
                            </label>
                            <input type="file" name="attachments[]" id="admin-attachments" multiple accept="image/*,.heic,.heif,.pdf"
                                   @change="files = Array.from($event.target.files)" class="hidden">
                            <template x-if="files.length > 0">
                                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="files.length + ' файл(ів)'"></span>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-gray-500 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox" name="is_internal" value="1" class="rounded bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm">{{ __('app.sa_internal_note') }}</span>
                            </label>
                            <select name="status" class="px-3 py-1 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('app.sa_no_status_change') }}</option>
                                <option value="in_progress">{{ __('app.sa_status_in_progress') }}</option>
                                <option value="waiting">{{ __('app.sa_status_waiting') }}</option>
                                <option value="resolved">{{ __('app.sa_status_resolved') }}</option>
                                <option value="closed">{{ __('app.sa_status_closed') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            {{ __('app.send') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $ticket->user ? __('app.sa_user_label') : __('app.sa_guest_contact') }}
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.first_name') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $ticket->user?->name ?? $ticket->guest_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.email') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $ticket->user?->email ?? $ticket->guest_email }}</p>
                    </div>
                    @if($ticket->user)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.sa_church_label') }}</p>
                            <p class="text-gray-900 dark:text-white">{{ $ticket->church?->name ?? __('app.sa_no_church') }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.created') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $ticket->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Ticket Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.sa_management') }}</h3>
                <form action="{{ route('system.support.update', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.status_label') }}</label>
                        <select name="status" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>{{ __('app.sa_status_open') }}</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>{{ __('app.sa_status_in_progress') }}</option>
                            <option value="waiting" {{ $ticket->status === 'waiting' ? 'selected' : '' }}>{{ __('app.sa_status_waiting') }}</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>{{ __('app.sa_status_resolved') }}</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>{{ __('app.sa_status_closed') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.sa_ticket_priority') }}</label>
                        <select name="priority" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>{{ __('app.sa_priority_low') }}</option>
                            <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>{{ __('app.sa_priority_normal') }}</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>{{ __('app.sa_priority_high') }}</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>{{ __('app.sa_priority_urgent') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.sa_assigned_to') }}</label>
                        <select name="assigned_to" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">{{ __('app.sa_not_assigned') }}</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg transition-colors">
                        {{ __('app.update') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
