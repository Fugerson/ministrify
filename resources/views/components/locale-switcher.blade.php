@php $currentLocale = app()->getLocale(); @endphp

<div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1"
     x-data="{ locale: '{{ $currentLocale }}' }"
     @locale-changed.window="locale = $event.detail">
    <!-- Ukrainian Button -->
    <button @click="switchLocale('uk')"
        :class="locale === 'uk' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700'"
        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200"
        title="Українська">
        <span>🇺🇦</span>
        <span class="hidden sm:inline ml-1">Укр</span>
    </button>

    <!-- English Button -->
    <button @click="switchLocale('en')"
        :class="locale === 'en' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700'"
        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200"
        title="English">
        <span>🇬🇧</span>
        <span class="hidden sm:inline ml-1">Eng</span>
    </button>
</div>

<script>
function switchLocale(locale) {
    console.log('🌐 Switching locale to:', locale);

    // Set HTTP cookie immediately (1 year expiry)
    const maxAge = 365 * 24 * 60 * 60;
    document.cookie = 'locale=' + locale + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    console.log('🍪 Cookie set:', document.cookie);

    // Get CSRF token from meta tag or form
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                    document.querySelector('input[name="_token"]')?.value || '';
    console.log('🔐 CSRF token found:', csrfToken ? 'yes (' + csrfToken.substring(0,10) + '...)' : 'NO');

    // Send to server to save user preference
    const formData = new FormData();
    formData.append('_token', csrfToken);

    fetch('/locale/' + locale, {
        method: 'POST',
        body: formData
    })
    .then(r => {
        console.log('✅ Server response:', r.status);
        return r.json();
    })
    .then(data => console.log('📦 Server data:', data))
    .catch(err => console.error('❌ Fetch error:', err));

    // Dispatch event
    window.dispatchEvent(new CustomEvent('locale-changed', { detail: locale }));

    // Reload immediately
    console.log('🔄 Reloading page...');
    location.reload();
}
</script>
