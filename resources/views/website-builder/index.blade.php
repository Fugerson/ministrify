@extends('layouts.app')

@section('title', 'Конструктор сайту')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Конструктор сайту</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Налаштуйте публічний сайт вашої церкви</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('public.church', $church->slug) }}" target="_blank"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Переглянути сайт
            </a>
        </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl {{ $church->public_site_enabled ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700' }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $church->public_site_enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Статус сайту</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($church->public_site_enabled)
                            Сайт доступний за адресою: <a href="{{ route('public.church', $church->slug) }}" target="_blank" class="text-primary-600 hover:underline">{{ route('public.church', $church->slug) }}</a>
                        @else
                            Публічний сайт вимкнено
                        @endif
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $church->public_site_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                {{ $church->public_site_enabled ? 'Активний' : 'Вимкнено' }}
            </span>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Templates -->
        <a href="{{ route('website-builder.templates.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Шаблони дизайну</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Оберіть стиль сайту</p>
            <div class="mt-3 text-sm text-primary-600 dark:text-primary-400">
                Поточний: {{ $templateConfig['name'] ?? 'Сучасний' }}
            </div>
        </a>

        <!-- Sections -->
        <a href="{{ route('website-builder.sections.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Секції сторінки</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Увімкніть та впорядкуйте</p>
            <div class="mt-3 text-sm text-primary-600 dark:text-primary-400">
                Активних: {{ count($enabledSections) }}
            </div>
        </a>

        <!-- Design -->
        <a href="{{ route('website-builder.design.index') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Кольори та стиль</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Налаштуйте дизайн</p>
        </a>
    </div>

    <!-- Content Management -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Контент сайту</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
            <!-- About -->
            <a href="{{ route('website-builder.about.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Про нас</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Місія, візія</p>
                </div>
            </a>

            <!-- Team -->
            <a href="{{ route('website-builder.team.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Команда</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['staff_count'] }} осіб</p>
                </div>
            </a>

            <!-- Sermons -->
            <a href="{{ route('website-builder.sermons.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Проповіді</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['sermons_count'] }} записів</p>
                </div>
            </a>

            <!-- Gallery -->
            <a href="{{ route('website-builder.gallery.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Галерея</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['galleries_count'] }} альбомів</p>
                </div>
            </a>

            <!-- Blog -->
            <a href="{{ route('website-builder.blog.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Блог</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['blog_posts_count'] }} статей</p>
                </div>
            </a>

            <!-- FAQ -->
            <a href="{{ route('website-builder.faq.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">FAQ</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['faqs_count'] }} питань</p>
                </div>
            </a>

            <!-- Testimonials -->
            <a href="{{ route('website-builder.testimonials.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Свідчення</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['testimonials_count'] }} історій</p>
                </div>
            </a>

            <!-- Events -->
            <a href="{{ route('events.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Події</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['events_count'] }} публічних</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800 p-6">
        <div class="flex gap-4">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-800 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-primary-900 dark:text-primary-100">Як працює конструктор</h3>
                <p class="text-sm text-primary-700 dark:text-primary-300 mt-1">
                    Оберіть шаблон дизайну, налаштуйте кольори та шрифти, увімкніть потрібні секції та наповніть їх контентом.
                    Зміни одразу відображаються на публічному сайті вашої церкви.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
