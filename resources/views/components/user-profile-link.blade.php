{{-- User profile link for header (avatar + name + logout) --}}
<div class="flex items-center gap-1" x-data="{ profileOpen: false }">
    <a href="{{ route('my-schedule') }}" class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="{{ __('Мій розклад') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </a>

    {{-- Language Selector --}}
    <div class="relative">
        <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
            <x-user-avatar size="sm" />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</span>
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
            {{-- Language Section Header --}}
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('landing.theme') }}</div>

            {{-- Language Options --}}
            <button onclick="window.switchLocaleAccount('uk')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 {{ app()->getLocale() === 'uk' ? 'text-primary-600 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                <span>🇺🇦</span>
                <span>Українська</span>
                @if(app()->getLocale() === 'uk')
                    <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>
            <button onclick="window.switchLocaleAccount('en')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 {{ app()->getLocale() === 'en' ? 'text-primary-600 dark:text-primary-400 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                <span>🇬🇧</span>
                <span>English</span>
                @if(app()->getLocale() === 'en')
                    <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            <hr class="my-2 border-gray-200 dark:border-gray-700">

            {{-- Profile --}}
            <a href="{{ route('my-profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('landing.profile') ?? 'Профіль' }}</a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">{{ __('landing.logout') ?? 'Вийти' }}</button>
            </form>
        </div>
    </div>

    {{-- Old logout button (hidden, kept for backward compatibility) --}}
    <form method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
        <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="{{ __('Вийти') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </button>
    </form>
</div>

<script>
window.switchLocaleAccount = function(locale) {
    console.log('🌐 Switching locale to:', locale);

    const maxAge = 365 * 24 * 60 * 60;
    document.cookie = 'locale=' + locale + '; path=/; max-age=' + maxAge + '; SameSite=Lax; Secure';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const formData = new FormData();
    formData.append('_token', csrfToken);

    fetch('/locale/' + locale, {
        method: 'POST',
        credentials: 'include',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        console.log('✅ Locale changed:', data);
        location.reload();
    })
    .catch(err => console.error('❌ Error:', err));
};
</script>
