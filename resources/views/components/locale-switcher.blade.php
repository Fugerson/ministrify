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
    // Set HTTP cookie immediately (1 year expiry)
    document.cookie = 'locale=' + locale + '; path=/; max-age=' + (365*24*60*60) + '; SameSite=Lax';

    // Send to server to save user preference
    fetch('/locale/' + locale, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    }).catch(err => console.error('Locale switch error:', err));

    // Dispatch event so all locale switchers update immediately
    window.dispatchEvent(new CustomEvent('locale-changed', { detail: locale }));

    // Reload page after brief delay to apply translations
    setTimeout(() => location.reload(), 150);
}
</script>
