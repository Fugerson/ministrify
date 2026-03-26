@props([
    'color' => 'gray', // gray, primary, green, red, yellow, blue, purple, indigo
    'size' => 'sm', // sm, md
    'dot' => false,
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400',
        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
    ];

    $dotColors = [
        'gray' => 'bg-gray-500',
        'primary' => 'bg-primary-500',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'blue' => 'bg-blue-500',
        'purple' => 'bg-purple-500',
        'indigo' => 'bg-indigo-500',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $colorClass = $colors[$color] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-medium rounded-full {$colorClass} {$sizeClass}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$color] ?? $dotColors['gray'] }}"></span>
    @endif
    {{ $slot }}
</span>
