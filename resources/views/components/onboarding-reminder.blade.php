@php
    $user = auth()->user();
    $showReminder = $user && $user->isAdmin() && !$user->isSuperAdmin() && !$user->onboarding_completed && $user->onboarding_state;
    $progress = $showReminder ? $user->getOnboardingProgress() : null;
    $stepsState = $showReminder ? ($user->onboarding_state['steps'] ?? []) : [];
@endphp

@if($showReminder)
<div x-data="{ dismissed: false }" x-show="!dismissed" x-transition
     class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Завершіть налаштування</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $progress['percentage'] }}% виконано</p>
            </div>
        </div>
        <button @click="dismissed = true"
                class="text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Progress Bar -->
    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
        <div class="h-full bg-primary-500 rounded-full transition-all duration-500"
             style="width: {{ $progress['percentage'] }}%"></div>
    </div>

    <!-- Steps Checklist -->
    <div class="space-y-2 mb-4">
        @foreach(\App\Models\User::ONBOARDING_STEPS as $stepKey => $stepConfig)
            @php
                $stepState = $stepsState[$stepKey] ?? [];
                $isCompleted = $stepState['completed'] ?? false;
                $isSkipped = $stepState['skipped'] ?? false;
            @endphp
            <div class="flex items-center gap-2 text-sm">
                @if($isCompleted)
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 line-through">{{ $stepConfig['title'] }}</span>
                @elseif($isSkipped)
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400">{{ $stepConfig['title'] }} (пропущено)</span>
                @else
                    <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded-full"></div>
                    <span class="text-gray-700 dark:text-gray-300">{{ $stepConfig['title'] }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Action Button -->
    <a href="{{ route('onboarding.show') }}"
       class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-xl font-medium transition-colors">
        <span>Продовжити налаштування</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>
@endif
