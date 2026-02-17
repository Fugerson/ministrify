{{-- User profile link for header (avatar + name + logout) --}}
<div class="flex items-center gap-1">
    <a href="{{ route('my-schedule') }}" class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="{{ __('Мій розклад') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </a>
    <a href="{{ route('my-profile') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors" title="{{ __('Мій профіль') }}">
        <x-user-avatar size="sm" />
        <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="{{ __('Вийти') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </button>
    </form>
</div>
