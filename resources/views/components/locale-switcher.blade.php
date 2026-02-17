@php $currentLocale = app()->getLocale(); @endphp

<div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
    <!-- Ukrainian Button -->
    <button onclick="window.switchLocale('uk')"
        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 @if($currentLocale === 'uk') bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm @else text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 @endif"
        title="Українська">
        <span>🇺🇦</span>
        <span class="hidden sm:inline ml-1">Укр</span>
    </button>

    <!-- English Button -->
    <button onclick="window.switchLocale('en')"
        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 @if($currentLocale === 'en') bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm @else text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 @endif"
        title="English">
        <span>🇬🇧</span>
        <span class="hidden sm:inline ml-1">Eng</span>
    </button>
</div>
