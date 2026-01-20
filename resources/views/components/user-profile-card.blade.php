{{-- User profile card for sidebar (avatar + name + church + logout) --}}
@props(['currentChurch' => null, 'size' => 'md', 'showLogout' => true])

<div class="flex items-center space-x-3">
    <x-user-avatar :size="$size" />
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $currentChurch->name ?? '' }}</p>
    </div>
    @if($showLogout)
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </button>
    </form>
    @endif
</div>
