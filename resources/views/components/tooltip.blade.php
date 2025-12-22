@props(['text', 'position' => 'top'])

@php
$positions = [
    'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
];

$arrows = [
    'top' => 'top-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-b-transparent border-t-gray-900 dark:border-t-gray-700',
    'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-t-transparent border-b-gray-900 dark:border-b-gray-700',
    'left' => 'left-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-r-transparent border-l-gray-900 dark:border-l-gray-700',
    'right' => 'right-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-l-transparent border-r-gray-900 dark:border-r-gray-700',
];
@endphp

<div class="relative inline-flex" x-data="{ show: false }">
    <div @mouseenter="show = true" @mouseleave="show = false" class="cursor-help">
        {{ $slot }}
    </div>
    <div x-show="show"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 {{ $positions[$position] }} pointer-events-none">
        <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg py-2 px-3 max-w-xs whitespace-normal shadow-lg">
            {{ $text }}
        </div>
        <div class="absolute w-0 h-0 border-4 {{ $arrows[$position] }}"></div>
    </div>
</div>
