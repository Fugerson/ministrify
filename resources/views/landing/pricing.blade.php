@extends('layouts.landing')

@section('title', __('landing.pricing_title'))
@section('description', __('landing.pricing_meta'))

@section('content')
{{-- Hero --}}
<section class="pt-32 pb-16 bg-gradient-to-b from-primary-50 to-white dark:from-gray-900 dark:to-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium mb-4">{{ __('landing.pricing') }}</span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('landing.pricing_heading') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('landing.pricing_subheading') }}
            </p>
        </div>
    </div>
</section>

{{-- Plans --}}
<section class="py-20 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">

            {{-- Free --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 flex flex-col">
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Free</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">$0</span>
                        <span class="text-gray-500 dark:text-gray-400">/{{ __('landing.month') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('landing.plan_free_desc') }}</p>
                </div>

                <ul class="space-y-3 mb-8 flex-1">
                    @foreach([
                        [true, __('landing.plan_people_limit', ['count' => 50])],
                        [true, __('landing.plan_users_limit', ['count' => 3])],
                        [true, __('landing.plan_ministries_limit', ['count' => 3])],
                        [true, __('landing.plan_groups_limit', ['count' => 2])],
                        [true, __('landing.plan_events_limit', ['count' => 20])],
                        [true, __('landing.plan_attendance')],
                        [false, __('landing.plan_finances')],
                        [false, __('landing.plan_reports')],
                        [false, __('landing.plan_website')],
                    ] as [$included, $label])
                    <li class="flex items-start gap-3">
                        @if($included)
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</span>
                        @else
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ $label }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>

                <a href="{{ route('landing.register') }}" class="block w-full text-center px-6 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    {{ __('landing.start_free') }}
                </a>
            </div>

            {{-- Standard (recommended) --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl border-2 border-primary-500 p-8 flex flex-col shadow-xl shadow-primary-500/10">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <span class="inline-block px-4 py-1 rounded-full bg-primary-500 text-white text-sm font-semibold">{{ __('landing.recommended') }}</span>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Standard</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">$9</span>
                        <span class="text-gray-500 dark:text-gray-400">/{{ __('landing.month') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('landing.plan_standard_desc') }}</p>
                </div>

                <ul class="space-y-3 mb-8 flex-1">
                    @foreach([
                        [true, __('landing.plan_people_limit', ['count' => 300])],
                        [true, __('landing.plan_users_limit', ['count' => 15])],
                        [true, __('landing.plan_ministries_limit', ['count' => 15])],
                        [true, __('landing.plan_groups_limit', ['count' => 10])],
                        [true, __('landing.plan_events_limit', ['count' => 100])],
                        [true, __('landing.plan_attendance')],
                        [true, __('landing.plan_finances')],
                        [true, __('landing.plan_reports')],
                        [true, __('landing.plan_boards')],
                        [false, __('landing.plan_website')],
                        [false, __('landing.plan_google_calendar')],
                    ] as [$included, $label])
                    <li class="flex items-start gap-3">
                        @if($included)
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</span>
                        @else
                        <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ $label }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>

                <a href="{{ route('landing.register') }}" class="block w-full text-center px-6 py-3 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-colors shadow-lg shadow-primary-500/25">
                    {{ __('landing.choose_standard') }}
                </a>
            </div>

            {{-- Pro --}}
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 flex flex-col">
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pro</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">$19</span>
                        <span class="text-gray-500 dark:text-gray-400">/{{ __('landing.month') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('landing.plan_pro_desc') }}</p>
                </div>

                <ul class="space-y-3 mb-8 flex-1">
                    @foreach([
                        [true, __('landing.plan_people_unlimited')],
                        [true, __('landing.plan_users_unlimited')],
                        [true, __('landing.plan_all_modules')],
                        [true, __('landing.plan_website')],
                        [true, __('landing.plan_google_calendar')],
                        [true, __('landing.plan_telegram_bot')],
                        [true, __('landing.plan_custom_roles')],
                        [true, __('landing.plan_priority_support')],
                    ] as [$included, $label])
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</span>
                    </li>
                    @endforeach
                </ul>

                <a href="{{ route('landing.register') }}" class="block w-full text-center px-6 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:border-primary-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    {{ __('landing.choose_pro') }}
                </a>
            </div>

        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">{{ __('landing.pricing_faq_title') }}</h2>

        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['landing.pricing_faq_q1', 'landing.pricing_faq_a1'],
                ['landing.pricing_faq_q2', 'landing.pricing_faq_a2'],
                ['landing.pricing_faq_q3', 'landing.pricing_faq_a3'],
                ['landing.pricing_faq_q4', 'landing.pricing_faq_a4'],
            ] as $i => $faq)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between px-6 py-4 text-left">
                    <span class="font-medium text-gray-900 dark:text-white">{{ __($faq[0]) }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === {{ $i }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-gray-400">{{ __($faq[1]) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-gradient-to-r from-primary-600 to-primary-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">{{ __('landing.pricing_cta_title') }}</h2>
        <p class="text-lg text-primary-100 mb-8">{{ __('landing.pricing_cta_desc') }}</p>
        <a href="{{ route('landing.register') }}" class="inline-block px-8 py-4 bg-white text-primary-600 font-semibold rounded-xl hover:bg-primary-50 transition-colors shadow-lg">
            {{ __('landing.start_free') }}
        </a>
    </div>
</section>
@endsection
