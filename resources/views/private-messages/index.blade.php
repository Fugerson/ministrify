@extends('layouts.app')

@section('title', 'Чат')

@section('content')
<!-- Tabs -->
@php
$announcementUnreadCount = \App\Models\Announcement::unreadCount(auth()->user()->church_id, auth()->id());
$commTabs = [
    ['route' => 'announcements.index', 'label' => 'Оголошення', 'active' => 'announcements.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>', 'badge' => $announcementUnreadCount],
    ['route' => 'pm.index', 'label' => 'Чат', 'active' => 'pm.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>', 'badge' => $unreadCount, 'badgeId' => 'pm-badge'],
];
if(auth()->user()->canCreate('announcements')) {
    $commTabs[] = ['route' => 'messages.index', 'label' => 'Розсилка', 'active' => 'messages.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'];
}
@endphp
<div class="mb-4">
    <nav class="flex space-x-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-full sm:w-fit overflow-x-auto" aria-label="Tabs">
        @foreach($commTabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="relative flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all
                      {{ request()->routeIs($tab['active'] ?? $tab['route'])
                         ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm'
                         : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-700/50' }}">
                <span class="mr-2">{!! $tab['icon'] !!}</span>
                {{ $tab['label'] }}
                @if(isset($tab['badge']) && $tab['badge'] > 0)
                <span @if(isset($tab['badgeId'])) id="{{ $tab['badgeId'] }}" @endif
                      class="ml-2 px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full min-w-[1.25rem] text-center">
                    {{ $tab['badge'] > 9 ? '9+' : $tab['badge'] }}
                </span>
                @endif
            </a>
        @endforeach
    </nav>
</div>

<div class="max-w-7xl mx-auto" x-data="messengerApp()">
    <div class="flex bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden" style="height: calc(100vh - 140px - env(safe-area-inset-bottom, 0px)); min-height: 500px; max-height: calc(100dvh - 140px);">

        <!-- Left Sidebar: Conversations List -->
        <div class="w-full md:w-80 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 flex flex-col"
             :class="{ 'hidden md:flex': selectedUser }">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Повідомлення</h1>
                    <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-medium rounded-full {{ $unreadCount > 0 ? '' : 'hidden' }}">
                        <span class="header-unread-count">{{ $unreadCount }}</span>
                    </span>
                </div>

                <!-- Search / New Chat -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input="filterConversations"
                           class="w-full pl-10 pr-10 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500"
                           placeholder="Пошук або почати новий чат...">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button @click="showNewChat = true" type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Conversations -->
            <div class="flex-1 overflow-y-auto" id="conversations-list">
                @forelse($conversations as $oderId => $lastMessage)
                    @php
                        $otherUser = $lastMessage->sender_id === auth()->id() ? $lastMessage->recipient : $lastMessage->sender;
                        $isUnread = $lastMessage->recipient_id === auth()->id() && !$lastMessage->read_at;
                    @endphp
                    <button type="button"
                        data-user-id="{{ $otherUser->id }}"
                        data-unread="{{ $isUnread ? 'true' : 'false' }}"
                        @click="selectUser({{ $otherUser->id }}, '{{ addslashes($otherUser->name) }}', '{{ $otherUser->role }}', $el)"
                        class="conversation-btn w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left"
                        :class="{
                            'bg-primary-50 dark:bg-primary-900/20': selectedUser?.id === {{ $otherUser->id }}
                        }">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-sm">
                                <span class="text-lg font-semibold text-white">{{ mb_substr($otherUser->name, 0, 1) }}</span>
                            </div>
                            <span class="unread-dot absolute bottom-0 right-0 w-3.5 h-3.5 bg-primary-500 rounded-full border-2 border-white dark:border-gray-800 {{ $isUnread ? '' : 'hidden' }}"></span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 ml-3 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="conv-name font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $otherUser->name }}
                                </h3>
                                <span class="text-xs text-gray-400 ml-2 flex-shrink-0">
                                    {{ $lastMessage->created_at->isToday() ? $lastMessage->created_at->format('H:i') : $lastMessage->created_at->format('d.m') }}
                                </span>
                            </div>
                            <p class="conv-preview text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                @if($lastMessage->sender_id === auth()->id())
                                <span class="text-gray-400">Ви:</span>
                                @endif
                                {{ Str::limit($lastMessage->message, 45) }}
                            </p>
                        </div>
                    </button>
                @empty
                    <div class="px-4 py-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає чатів</p>
                        <button @click="showNewChat = true" class="mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Почати новий чат
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Chat Area -->
        <div class="flex-1 flex flex-col" :class="{ 'hidden': !selectedUser && window.innerWidth < 768 }">
            <!-- No chat selected -->
            <template x-if="!selectedUser">
                <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                    <div class="text-center px-4">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Приватні повідомлення</h2>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
                            Оберіть чат зі списку або почніть нову розмову з членом церкви
                        </p>
                        <button @click="showNewChat = true"
                                class="inline-flex items-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Новий чат
                        </button>
                    </div>
                </div>
            </template>

            <!-- Chat selected -->
            <template x-if="selectedUser">
                <div class="flex flex-col h-full">
                    <!-- Chat Header -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center bg-white dark:bg-gray-800">
                        <button @click="selectedUser = null" class="md:hidden p-2 -ml-2 mr-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                            <span class="text-lg font-semibold text-white" x-text="selectedUser.name.charAt(0)"></span>
                        </div>
                        <div class="ml-3 flex-1">
                            <h2 class="font-semibold text-gray-900 dark:text-white" x-text="selectedUser.name"></h2>
                            <p class="text-xs text-gray-500" x-text="selectedUser.role === 'admin' ? 'Адміністратор' : (selectedUser.role === 'leader' ? 'Лідер' : 'Служитель')"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-show="isTyping" class="text-xs text-primary-600 animate-pulse">Друкує...</span>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900" x-ref="messagesContainer" id="chatMessages">
                        <div class="flex justify-center">
                            <span x-show="loadingMessages" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-500 text-xs rounded-full animate-pulse">
                                Завантаження...
                            </span>
                        </div>

                        <template x-for="(msg, index) in messages" :key="msg.id">
                            <div>
                                <!-- Date separator -->
                                <template x-if="index === 0 || messages[index-1]?.date !== msg.date">
                                    <div class="flex items-center justify-center my-4">
                                        <span class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs rounded-full"
                                              x-text="msg.date === todayDate ? 'Сьогодні' : msg.date"></span>
                                    </div>
                                </template>

                                <!-- Message bubble -->
                                <div class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                                    <div class="max-w-[70%] group relative"
                                         :class="msg.is_mine ? 'order-1' : 'order-2'">
                                        <div class="px-4 py-2.5 rounded-2xl shadow-sm"
                                             :class="msg.is_mine ? 'bg-primary-600 text-white rounded-br-md' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-bl-md'">
                                            <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.message"></p>
                                            <div class="flex items-center justify-end gap-1 mt-1">
                                                <span class="text-xs opacity-70" x-text="msg.time"></span>
                                                <template x-if="msg.is_mine">
                                                    <svg class="w-4 h-4" :class="msg.read ? 'text-blue-300' : 'text-white/50'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/>
                                                    </svg>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="messages.length === 0 && !loadingMessages" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">Почніть спілкування!</p>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="sendMessage" class="flex items-end gap-3">
                            <div class="flex-1 relative">
                                <textarea x-model="newMessage"
                                          x-ref="messageInput"
                                          @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                                          @input="autoResize($el); emitTyping()"
                                          rows="1"
                                          placeholder="Написати повідомлення..."
                                          class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-2xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 resize-none max-h-32"></textarea>
                            </div>
                            <button type="submit"
                                    :disabled="!newMessage.trim() || sending"
                                    class="p-3 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100">
                                <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div x-show="showNewChat" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50" @click="showNewChat = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full max-h-[80vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="!composeMode">Нове повідомлення</span>
                        <span x-show="composeMode === 'all'">Написати всім</span>
                        <span x-show="composeMode === 'user'" x-text="'Написати: ' + (composeRecipient?.name || '')"></span>
                    </h3>
                    <button @click="showNewChat = false; composeMode = null; composeRecipient = null; composeMessage = ''" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Mode Selection -->
                <div x-show="!composeMode" class="p-4 space-y-3">
                    <!-- Search users with dropdown -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Оберіть отримувача</label>
                        <div class="relative">
                            <input type="text"
                                   x-model="userSearch"
                                   @focus="showUserDropdown = true"
                                   @click="showUserDropdown = true"
                                   placeholder="Почніть вводити ім'я..."
                                   class="w-full px-4 py-3 pl-10 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 cursor-pointer"
                                 @click="showUserDropdown = !showUserDropdown"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <!-- Dropdown with users -->
                        <div x-show="showUserDropdown"
                             @click.away="showUserDropdown = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute z-50 mt-1 w-full max-h-60 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg">

                            <!-- Write to All option -->
                            <button type="button"
                                    @click="composeMode = 'all'; showUserDropdown = false"
                                    class="w-full flex items-center px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors text-left border-b border-gray-200 dark:border-gray-700">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="font-medium text-primary-600 dark:text-primary-400">Написати всім</p>
                                    <p class="text-xs text-gray-500">Надіслати всім користувачам церкви</p>
                                </div>
                            </button>

                            <!-- Users list -->
                            @php
                                // For System Admin: show all users
                                // For regular users: show users from the same church
                                if (auth()->user()->is_super_admin) {
                                    $availableUsers = \App\Models\User::where('id', '!=', auth()->id())
                                        ->orderBy('name')
                                        ->get();
                                } else {
                                    $availableUsers = $currentChurch->members()
                                        ->where('users.id', '!=', auth()->id())
                                        ->orderBy('name')
                                        ->get();
                                }
                            @endphp
                            @if($availableUsers->isEmpty())
                                <div class="px-4 py-6 text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Немає користувачів</p>
                                </div>
                            @else
                                @foreach($availableUsers as $user)
                                    <button type="button"
                                            @click="composeMode = 'user'; composeRecipient = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', role: '{{ $user->role }}' }; showUserDropdown = false; userSearch = ''"
                                            x-show="userSearch === '' || '{{ mb_strtolower($user->name) }}'.indexOf(userSearch.toLowerCase()) !== -1"
                                            class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                            <span class="text-lg font-semibold text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->role === 'admin' ? 'Адміністратор' : ($user->role === 'leader' ? 'Лідер' : 'Служитель') }}</p>
                                            @if(auth()->user()->is_super_admin && $user->church)
                                                <p class="text-xs text-gray-400">{{ $user->church->name }}</p>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            @endif

                        </div>
                    </div>
                </div>

                <!-- Compose Message (when mode selected) -->
                <template x-if="composeMode">
                    <div class="p-4 space-y-4">
                        <button @click="composeMode = null; composeRecipient = null" class="flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Назад
                        </button>

                        <div x-show="composeMode === 'all'" class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                            <div class="flex items-center text-primary-700 dark:text-primary-400">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm">Повідомлення буде надіслано всім користувачам церкви</span>
                            </div>
                        </div>

                        <textarea x-model="composeMessage"
                                  rows="4"
                                  placeholder="Введіть повідомлення..."
                                  class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 resize-none"></textarea>

                        <div class="flex justify-end gap-3">
                            <button @click="showNewChat = false; composeMode = null; composeRecipient = null; composeMessage = ''"
                                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                                Скасувати
                            </button>
                            <button @click="sendComposeMessage()"
                                    :disabled="!composeMessage.trim() || composeSending"
                                    class="px-5 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white font-medium rounded-xl transition-colors flex items-center gap-2">
                                <svg x-show="!composeSending" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg x-show="composeSending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="composeMode === 'all' ? 'Надіслати всім' : 'Надіслати'"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function messengerApp() {
    return {
        searchQuery: '',
        showNewChat: false,
        userSearch: '',
        selectedUser: null,
        messages: [],
        newMessage: '',
        sending: false,
        loadingMessages: false,
        isTyping: false,
        pollInterval: null,
        lastMessageId: 0,
        todayDate: new Date().toLocaleDateString('uk-UA'),
        // Compose modal
        composeMode: null, // null, 'all', 'user'
        composeRecipient: null,
        composeMessage: '',
        composeSending: false,
        showUserDropdown: false,

        init() {
            // Check if URL has user parameter
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('user');
            if (userId) {
                // Load that user's chat
            }
        },

        async selectUser(id, name, role, btnEl = null) {
            this.selectedUser = { id, name, role };
            this.messages = [];
            this.loadingMessages = true;
            this.lastMessageId = 0;

            // Stop previous polling
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }

            // Remove unread indicator from clicked conversation
            if (btnEl) {
                const dot = btnEl.querySelector('.unread-dot');
                if (dot) dot.classList.add('hidden');
                btnEl.dataset.unread = 'false';
                btnEl.classList.remove('bg-blue-50', 'dark:bg-blue-900/10');
            }

            try {
                const response = await fetch(`/pm/${id}/poll?last_id=0`);
                const data = await response.json();
                this.messages = data.messages.map(m => ({
                    ...m,
                    time: m.created_at,
                    read: true
                }));
                if (this.messages.length > 0) {
                    this.lastMessageId = Math.max(...this.messages.map(m => m.id));
                }
                this.scrollToBottom();

                // Update unread counts
                this.updateUnreadBadges();
            } catch (e) {
                console.error('Error loading messages:', e);
            }

            this.loadingMessages = false;

            // Start polling
            this.pollInterval = setInterval(() => this.pollMessages(), 2000);
        },

        async pollMessages() {
            if (!this.selectedUser) return;

            try {
                const response = await fetch(`/pm/${this.selectedUser.id}/poll?last_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (!this.messages.find(m => m.id === msg.id)) {
                            this.messages.push({
                                ...msg,
                                time: msg.created_at,
                                read: true
                            });
                            this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                        }
                    });
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending || !this.selectedUser) return;

            this.sending = true;
            const messageText = this.newMessage;
            this.newMessage = '';

            try {
                const response = await fetch('/pm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        recipient_id: this.selectedUser.id,
                        message: messageText,
                    }),
                });

                if (response.ok) {
                    // Trigger immediate poll to show message
                    await this.pollMessages();
                }
            } catch (error) {
                console.error('Error sending message:', error);
                this.newMessage = messageText;
            }

            this.sending = false;
            this.$refs.messageInput.style.height = 'auto';
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        },

        emitTyping() {
            // Could be used for "typing..." indicator
        },

        filterConversations() {
            // Client-side filter handled by CSS
        },

        async sendComposeMessage() {
            if (!this.composeMessage.trim() || this.composeSending) return;

            this.composeSending = true;
            const recipientId = this.composeMode === 'all' ? 'all' : this.composeRecipient?.id;

            try {
                const response = await fetch('/pm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        recipient_id: recipientId,
                        message: this.composeMessage,
                    }),
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.broadcast) {
                        alert(`Повідомлення надіслано ${data.count} користувачам`);
                    }

                    // Close modal and reset
                    this.showNewChat = false;
                    this.composeMode = null;
                    this.composeRecipient = null;
                    this.composeMessage = '';

                    // If individual user, open their chat
                    if (!data.broadcast && this.composeRecipient) {
                        this.selectUser(this.composeRecipient.id, this.composeRecipient.name, this.composeRecipient.role);
                    } else {
                        // Reload page to see new conversations
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Помилка надсилання повідомлення');
            }

            this.composeSending = false;
        },

        async updateUnreadBadges() {
            try {
                const response = await fetch('/pm/unread-count');
                const data = await response.json();

                // Update tab badge
                const pmBadge = document.getElementById('pm-badge');
                if (pmBadge) {
                    if (data.count > 0) {
                        pmBadge.textContent = data.count > 9 ? '9+' : data.count;
                        pmBadge.classList.remove('hidden');
                    } else {
                        pmBadge.classList.add('hidden');
                    }
                }

                // Update header badge
                const headerBadge = document.querySelector('.header-unread-count');
                if (headerBadge) {
                    if (data.count > 0) {
                        headerBadge.textContent = data.count;
                        headerBadge.parentElement.classList.remove('hidden');
                    } else {
                        headerBadge.parentElement.classList.add('hidden');
                    }
                }
            } catch (e) {
                console.error('Error fetching unread count:', e);
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
@endsection
