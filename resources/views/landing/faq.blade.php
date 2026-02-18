@extends('layouts.landing')

@section('title', __('landing.faq_title'))
@section('description', __('landing.faq_meta'))
@section('keywords', __('landing.faq_keywords'))

@php
$faqItems = [
    ['q' => __('landing.what_is_ministrify'), 'a' => __('landing.ministrify_answer')],
    ['q' => __('landing.how_to_start'), 'a' => __('landing.how_to_start_answer')],
    ['q' => __('landing.how_to_register'), 'a' => __('landing.how_to_register_answer')],
    ['q' => __('landing.how_to_add_members'), 'a' => __('landing.how_to_add_members_answer')],
    ['q' => __('landing.can_use_phone'), 'a' => __('landing.can_use_phone_answer')],
    ['q' => __('landing.how_setup_telegram'), 'a' => __('landing.how_setup_telegram_answer')],
    ['q' => __('landing.what_roles_exist'), 'a' => __('landing.what_roles_answer')],
    ['q' => __('landing.is_data_safe'), 'a' => __('landing.is_data_safe_answer')],
    ['q' => __('landing.multiple_churches'), 'a' => __('landing.multiple_churches_answer')],
    ['q' => __('landing.how_financial_accounting'), 'a' => __('landing.how_financial_accounting_answer')],
    ['q' => __('landing.what_is_task_tracker'), 'a' => __('landing.what_is_task_tracker_answer')],
    ['q' => __('landing.how_attendance_works'), 'a' => __('landing.how_attendance_works_answer')],
    ['q' => __('landing.is_google_calendar'), 'a' => __('landing.is_google_calendar_answer')],
    ['q' => __('landing.how_to_contact_support'), 'a' => __('landing.how_to_contact_support_answer')],
];
@endphp

@section('schema')
@php
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a'],
        ],
    ], $faqItems),
];
@endphp
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">{{ __('landing.faq_section') }}</span>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('landing.faq_heading') }}</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('landing.faq_subheading') }}</p>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            @foreach($faqItems as $index => $faq)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    <button
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <span class="font-semibold text-gray-900 dark:text-white pr-4">{{ $faq['q'] }}</span>
                        <svg
                            class="w-5 h-5 text-gray-500 transition-transform duration-200 flex-shrink-0"
                            :class="{ 'rotate-180': open === {{ $index }} }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div
                        x-show="open === {{ $index }}"
                        x-collapse
                        class="px-6 pb-5"
                    >
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="mt-16 text-center bg-gradient-to-br from-primary-50 to-indigo-50 dark:from-gray-800 dark:to-gray-800 rounded-2xl p-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('landing.didnt_find_answer') }}</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ __('landing.always_happy_help') }}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/contact') }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('landing.contact_team') }}
                </a>
                <a href="{{ url('/register-church') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-xl border border-gray-200 dark:border-gray-600 transition-colors">
                    {{ __('landing.start_free') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
