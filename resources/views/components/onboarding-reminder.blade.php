@php
    $user = auth()->user();
    $church = $user->church;

    // Don't show for non-admin users or if no church
    if (!$church || !$user->hasRole('administrator')) {
        $showWelcome = false;
    } else {
        // Check onboarding steps completion
        $peopleCount = \App\Models\Person::where('church_id', $church->id)->count();
        $ministriesCount = \App\Models\Ministry::where('church_id', $church->id)->count();
        $eventsCount = \App\Models\Event::where('church_id', $church->id)->count();
        $groupsCount = \App\Models\Group::where('church_id', $church->id)->count();
        $hasLogo = !empty($church->logo);

        $steps = [
            'profile' => [
                'done' => $hasLogo && !empty($church->city),
                'label' => __('app.onboarding_step_profile'),
                'desc' => __('app.onboarding_step_profile_desc'),
                'url' => route('settings.index'),
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            ],
            'people' => [
                'done' => $peopleCount > 1,
                'label' => __('app.onboarding_step_people'),
                'desc' => __('app.onboarding_step_people_desc'),
                'url' => route('people.create'),
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            ],
            'ministry' => [
                'done' => $ministriesCount > 0,
                'label' => __('app.onboarding_step_ministry'),
                'desc' => __('app.onboarding_step_ministry_desc'),
                'url' => route('ministries.create'),
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            ],
            'event' => [
                'done' => $eventsCount > 0,
                'label' => __('app.onboarding_step_event'),
                'desc' => __('app.onboarding_step_event_desc'),
                'url' => route('events.create'),
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
            'group' => [
                'done' => $groupsCount > 0,
                'label' => __('app.onboarding_step_group'),
                'desc' => __('app.onboarding_step_group_desc'),
                'url' => route('groups.create'),
                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            ],
        ];

        $completedCount = collect($steps)->where('done', true)->count();
        $totalSteps = count($steps);
        $percentage = round(($completedCount / $totalSteps) * 100);

        // Hide if user dismissed the banner
        $dismissed = $user->preferences['onboarding_banner_dismissed'] ?? false;
        $showWelcome = !$dismissed;
    }
@endphp

@if($showWelcome)
<div
    x-data="{
        open: {{ $completedCount < $totalSteps ? 'true' : 'false' }},
        dismissing: false,
        dismiss() {
            this.dismissing = true;
            fetch('{{ route('preferences.update') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ onboarding_banner_dismissed: true })
            }).then(() => this.$el.remove());
        }
    }"
    class="bg-gradient-to-r from-primary-50 via-blue-50 to-indigo-50 dark:from-primary-900/20 dark:via-blue-900/20 dark:to-indigo-900/20 rounded-2xl shadow-sm border border-primary-200 dark:border-primary-800 overflow-hidden mb-6"
>
    {{-- Header --}}
    <div class="p-5 pb-0">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/50 rounded-xl flex items-center justify-center">
                    @if($completedCount >= $totalSteps)
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    @if($completedCount >= $totalSteps)
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">{{ __('app.onboarding_all_done') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.onboarding_all_done_desc') }}</p>
                    @else
                        <h3 class="font-bold text-gray-900 dark:text-white text-lg">{{ __('app.onboarding_welcome', ['name' => explode(' ', $user->name)[0]]) }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.onboarding_welcome_desc') }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($completedCount < $totalSteps)
                <button x-on:click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                    <svg class="w-5 h-5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                @endif
                <button x-on:click="dismiss()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mt-4 mb-4">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('app.onboarding_progress') }}</span>
                <span class="text-primary-600 dark:text-primary-400 font-bold">{{ $completedCount }}/{{ $totalSteps }}</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $completedCount >= $totalSteps ? 'bg-green-500' : 'bg-primary-500' }}" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>

    {{-- Steps Checklist --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($steps as $key => $step)
            <a href="{{ $step['url'] }}"
               class="group flex items-center gap-3 p-3 rounded-xl transition-all {{ $step['done'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md' }}">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ $step['done'] ? 'bg-green-100 dark:bg-green-900/40' : 'bg-gray-100 dark:bg-gray-700 group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30' }}">
                    @if($step['done'])
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium {{ $step['done'] ? 'text-green-700 dark:text-green-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $step['label'] }}</p>
                    <p class="text-xs {{ $step['done'] ? 'text-green-600 dark:text-green-500' : 'text-gray-500 dark:text-gray-400' }} truncate">{{ $step['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif
