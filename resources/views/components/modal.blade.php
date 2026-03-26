@props([
    'name' => 'modal',
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl
    'closeable' => true,
])

@php
    $maxWidths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];

    $widthClass = $maxWidths[$maxWidth] ?? $maxWidths['md'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    @if($closeable) x-on:keydown.escape.window="open = false" @endif
    x-cloak
    {{ $attributes }}
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-50"
        @if($closeable) @click="open = false" @endif
    ></div>

    <!-- Modal Panel -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-trap.inert.noscroll="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @if($closeable) @click.self="open = false" @endif
    >
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full {{ $widthClass }} max-h-[90vh] overflow-y-auto">
            @if(isset($header))
                <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $header }}</h3>
                    @if($closeable)
                        <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <div class="p-4 sm:p-6">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
