@extends('layouts.app')

@section('title', 'Відвідуваність')

@section('actions')
<div class="flex items-center space-x-2">
    <a href="{{ route('attendance.stats') }}"
       class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        Статистика
    </a>
    <a href="{{ route('attendance.create') }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Check-in
    </a>
</div>
@endsection

@section('content')
<x-page-help page="attendance" />

@php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
    <!-- Month navigation -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('attendance.index', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
               class="p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $months[$month - 1] }} {{ $year }}</h2>
            <a href="{{ route('attendance.index', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
               class="p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Attendance records -->
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($attendances as $attendance)
            <a href="{{ route('attendance.show', $attendance) }}"
               class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $attendance->date->format('d.m.Y') }}
                            @if($attendance->event)
                                — {{ $attendance->event->title }}
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($attendance->event)
                                {{ $attendance->event->ministry->name }}
                            @else
                                Загальний check-in
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendance->total_count }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">осіб</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                <p>Немає записів за цей місяць</p>
                <a href="{{ route('attendance.create') }}" class="mt-2 inline-block text-primary-600 hover:text-primary-500">
                    Створити check-in
                </a>
            </div>
        @endforelse
    </div>

    @if($attendances->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $attendances->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
