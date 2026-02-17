@extends('layouts.landing')

@section('title', __('landing.privacy_title'))
@section('description', __('landing.privacy_meta'))

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('landing.privacy_heading') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('landing.last_updated') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0">{{ __('landing.privacy_section_1') }}</h2>
                <p>Ministrify поважає вашу приватність та захищає ваші персональні дані. Ця політика пояснює, як ми збираємо, використовуємо та захищаємо вашу інформацію.</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_2') }}</h2>
                <p>Ми можемо збирати наступну інформацію:</p>
                <ul>
                    <li><strong>Реєстраційні дані:</strong> ім'я, email, назва церкви</li>
                    <li><strong>Дані членів церкви:</strong> імена, контакти, дати народження (вводяться вами)</li>
                    <li><strong>Технічні дані:</strong> IP-адреса, тип браузера, час відвідування</li>
                    <li><strong>Дані використання:</strong> які функції ви використовуєте</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_3') }}</h2>
                <p>{{ __('landing.privacy_use_intro') }}</p>
                <ul>
                    <li>{{ __('landing.privacy_providing_services') }}</li>
                    <li>{{ __('landing.privacy_improving_functionality') }}</li>
                    <li>{{ __('landing.privacy_technical_support') }}</li>
                    <li>{{ __('landing.privacy_important_notifications') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_4') }}</h2>
                <p>{{ __('landing.privacy_protection_intro') }}</p>
                <ul>
                    <li>{{ __('landing.privacy_ssl_encryption') }}</li>
                    <li>{{ __('landing.privacy_password_hashing') }}</li>
                    <li>{{ __('landing.privacy_regular_backups') }}</li>
                    <li>{{ __('landing.privacy_limited_access') }}</li>
                    <li>{{ __('landing.privacy_security_monitoring') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_5') }}</h2>
                <p>{{ __('landing.privacy_third_parties_intro') }}</p>
                <ul>
                    <li>{{ __('landing.privacy_necessary_services') }}</li>
                    <li>{{ __('landing.privacy_legal_requirement') }}</li>
                    <li>{{ __('landing.privacy_explicit_consent') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_6') }}</h2>
                <p>{{ __('landing.privacy_cookies_intro') }}</p>
                <ul>
                    <li>{{ __('landing.privacy_session_maintenance') }}</li>
                    <li>{{ __('landing.privacy_saving_settings') }}</li>
                    <li>{{ __('landing.privacy_usage_analytics') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_7') }}</h2>
                <p>{{ __('landing.privacy_rights_intro') }}</p>
                <ul>
                    <li>{{ __('landing.privacy_get_copy') }}</li>
                    <li>{{ __('landing.privacy_correct_data') }}</li>
                    <li>{{ __('landing.privacy_delete_data') }}</li>
                    <li>{{ __('landing.privacy_restrict_processing') }}</li>
                    <li>{{ __('landing.privacy_withdraw_consent') }}</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_8') }}</h2>
                <p>{{ __('landing.privacy_storage_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_9') }}</h2>
                <p>{{ __('landing.privacy_changes_text') }}</p>

                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.privacy_section_10') }}</h2>
                <p>{{ __('landing.privacy_contacts_text') }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
