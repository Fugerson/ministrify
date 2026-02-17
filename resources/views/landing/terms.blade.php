@extends('layouts.landing')

@section('title', __('landing.terms_title'))
@section('description', __('landing.terms_meta'))

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('landing.terms_heading') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('landing.last_updated') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0">{{ __('landing.terms_section_1') }}</h2>
                <p>{{ __('landing.terms_general_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_2') }}</h2>
                <p>{{ __('landing.terms_service_intro') }}</p>
                <ul>
                    <li>{{ __('landing.terms_maintaining_database') }}</li>
                    <li>{{ __('landing.terms_planning_events') }}</li>
                    <li>{{ __('landing.terms_financial_accounting') }}</li>
                    <li>{{ __('landing.terms_organizing_groups') }}</li>
                    <li>{{ __('landing.terms_communication') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_3') }}</h2>
                <p>{{ __('landing.terms_registration_intro') }}</p>
                <ul>
                    <li>{{ __('landing.terms_accuracy_info') }}</li>
                    <li>{{ __('landing.terms_password_security') }}</li>
                    <li>{{ __('landing.terms_account_actions') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_4') }}</h2>
                <p>{{ __('landing.terms_rules_intro') }}</p>
                <ul>
                    <li>{{ __('landing.terms_not_violating_rights') }}</li>
                    <li>{{ __('landing.terms_not_illegal') }}</li>
                    <li>{{ __('landing.terms_not_malicious') }}</li>
                    <li>{{ __('landing.terms_comply_law') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_5') }}</h2>
                <p>{{ __('landing.terms_ip_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_6') }}</h2>
                <p>{{ __('landing.terms_limitation_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_7') }}</h2>
                <p>{{ __('landing.terms_changes_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.terms_section_8') }}</h2>
                <p>{{ __('landing.terms_contacts_text') }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
