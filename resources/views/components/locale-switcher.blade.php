@php
    $currentLocale = app()->getLocale();
    $otherLocale = $currentLocale === 'uk' ? 'en' : 'uk';
    $flag = $currentLocale === 'uk' ? '🇺🇦' : '🇬🇧';
    $otherFlag = $otherLocale === 'uk' ? '🇺🇦' : '🇬🇧';
@endphp

<button
    onclick="switchLocale('{{ $otherLocale }}')"
    class="flex items-center gap-1.5 px-2 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
    title="{{ $otherLocale === 'en' ? 'Switch to English' : 'Перейти на українську' }}"
>
    <span class="text-base">{{ $otherFlag }}</span>
    <span class="uppercase font-medium text-xs">{{ $otherLocale }}</span>
</button>
