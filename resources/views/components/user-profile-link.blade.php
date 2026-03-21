{{-- User profile link for header (avatar + name + logout) --}}
<div class="flex items-center gap-1" x-data="{ profileOpen: false }">
    <div class="relative">
        <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
            <x-user-avatar size="sm" />
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ auth()->user()->person?->full_name ?? auth()->user()->name }}</span>
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="profileOpen" @click.outside="profileOpen = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
            {{-- Profile --}}
            <a href="{{ route('my-profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('app.my_profile') }}</a>

            {{-- Language switcher --}}
            <div class="px-4 py-2">
                <select onchange="switchLocaleAccount(this.value)"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5">
                    <option value="uk" {{ app()->getLocale() === 'uk' ? 'selected' : '' }}>🇺🇦 Українська</option>
                    <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                </select>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

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
    .then(() => Livewire.navigate(window.location.href))
    .catch(err => console.error('Locale switch error:', err));
};
</script>
