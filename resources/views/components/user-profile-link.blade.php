{{-- User profile link for header (avatar + name + logout) --}}
<div class="flex items-center gap-1" x-data="{ profileOpen: false }">
    <a href="{{ route('my-schedule') }}" class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" title="{{ __('app.my_schedule') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </a>

    <div class="relative">
        <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
            <x-user-avatar size="sm" />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</span>
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
            {{-- Language Section --}}
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.language') }}</div>
            <div class="px-4 py-2">
                <select onchange="window.switchLocaleAccount(this.value)"
                        class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @php $localeLabels = ['uk' => '🇺🇦 Українська', 'en' => '🇬🇧 English']; @endphp
                    @foreach(config('app.available_locales', ['uk', 'en']) as $code)
                        <option value="{{ $code }}" {{ app()->getLocale() === $code ? 'selected' : '' }}>{{ $localeLabels[$code] ?? $code }}</option>
                    @endforeach
                </select>
            </div>

            <hr class="my-2 border-gray-200 dark:border-gray-700">

            {{-- Profile --}}
            <a href="{{ route('my-profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('app.my_profile') }}</a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">{{ __('app.logout') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
window.switchLocaleAccount = function(locale) {
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
    .then(() => location.reload())
    .catch(err => console.error('Locale switch error:', err));
};
</script>
