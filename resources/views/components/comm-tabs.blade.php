@php
$pmUnreadCount = \App\Models\PrivateMessage::unreadCount(auth()->user()->church_id, auth()->id());
$announcementUnreadCount = \App\Models\Announcement::unreadCount(auth()->user()->church_id, auth()->id());

$commTabs = [
    ['route' => 'announcements.index', 'label' => 'Оголошення', 'active' => 'announcements.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>', 'badge' => $announcementUnreadCount],
    ['route' => 'pm.index', 'label' => 'Чат', 'active' => 'pm.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>', 'badge' => $pmUnreadCount, 'badgeId' => 'pm-badge'],
];
if(auth()->user()->canCreate('announcements')) {
    $commTabs[] = ['route' => 'messages.index', 'label' => 'Розсилка', 'active' => 'messages.*', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'];
}
@endphp
<div class="{{ $attributes->get('class', 'mb-6') }}">
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
