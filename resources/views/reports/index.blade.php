@extends('layouts.app')

@section('title', 'Звіти та аналітика')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Звіти та аналітика</h1>
        <p class="text-gray-600 dark:text-gray-400">Огляд ключових показників вашої церкви</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_members'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Всього людей</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_members'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Активних (3 міс)</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_events'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Подій цього року</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_volunteers'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Служителів</p>
        </div>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Attendance Report -->
        @if($currentChurch->attendance_enabled)
        <a href="{{ route('reports.attendance') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-lg transition-all hover:-translate-y-1">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Відвідуваність</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Тренди відвідуваності, хто перестав ходити, топ відвідувачів</p>
            <span class="text-primary-600 dark:text-primary-400 text-sm font-medium group-hover:underline">Переглянути →</span>
        </a>
        @endif

        <!-- Finances Report -->
        <a href="{{ route('reports.finances') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-lg transition-all hover:-translate-y-1">
            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Фінанси</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Порівняння років, доходи по категоріях, витрати по командах</p>
            <span class="text-primary-600 dark:text-primary-400 text-sm font-medium group-hover:underline">Переглянути →</span>
        </a>

        <!-- Volunteers Report -->
        <a href="{{ route('reports.volunteers') }}" class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 hover:shadow-lg transition-all hover:-translate-y-1">
            <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Служителі</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Топ служителів, активність по місяцях, розподіл по командах</p>
            <span class="text-primary-600 dark:text-primary-400 text-sm font-medium group-hover:underline">Переглянути →</span>
        </a>
    </div>
</div>
@endsection
