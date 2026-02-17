@php
    $user = auth()->user();
    $tourCompleted = $user->preferences['tour_completed'] ?? false;
    $showReminder = false; // temporarily disabled
@endphp

@if($showReminder)
<div x-data="{ dismissed: false }" x-show="!dismissed" x-transition
     class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-primary-200 dark:border-primary-800 p-5 mb-6">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Ознайомтесь з системою') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Пройдіть інтерактивний тур, щоб дізнатися про всі можливості') }}</p>
            </div>
        </div>
        <button @click="dismissed = true"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <button onclick="startGuidedTour()"
            class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-xl font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ __('Розпочати тур') }}</span>
    </button>
</div>
@endif
