@extends('layouts.app')

@section('title', 'Зустріч ' . $attendance->date->format('d.m.Y'))

@section('actions')
@can('update', $group)
<div class="flex items-center gap-2">
    <a href="{{ route('groups.attendance.edit', [$group, $attendance]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Редагувати
    </a>
    <form method="POST" action="{{ route('groups.attendance.destroy', [$group, $attendance]) }}" onsubmit="return confirm('Видалити запис?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium rounded-xl hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Видалити
        </button>
    </form>
</div>
@endcan
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('groups.show', $group) }}" class="hover:text-primary-600">{{ $group->name }}</a>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('groups.attendance.index', $group) }}" class="hover:text-primary-600">Відвідуваність</a>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span>{{ $attendance->date->format('d.m.Y') }}</span>
    </div>

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $attendance->date->translatedFormat('l, d F Y') }}</h2>
                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if($attendance->time)
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $attendance->time->format('H:i') }}
                    </span>
                    @endif
                    @if($attendance->location)
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $attendance->location }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $attendance->members_present }}</div>
                <div class="text-sm text-gray-500">з {{ $group->members->count() }} ({{ $attendance->attendance_rate }}%)</div>
                @if($attendance->guests_count > 0)
                <div class="text-sm text-green-600 dark:text-green-400 mt-1">+{{ $attendance->guests_count }} гостей</div>
                @endif
            </div>
        </div>

        @if($attendance->notes)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-gray-600 dark:text-gray-400">{{ $attendance->notes }}</p>
        </div>
        @endif

        @if($attendance->recorder)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
            Записав: {{ $attendance->recorder->name }} • {{ $attendance->created_at->format('d.m.Y H:i') }}
        </div>
        @endif
    </div>

    <!-- Attendance List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Присутність</h3>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($attendance->records->sortByDesc('present') as $record)
            <div class="p-4 flex items-center justify-between {{ $record->present ? '' : 'opacity-50' }}">
                <div class="flex items-center">
                    @if($record->person->photo)
                    <img src="{{ Storage::url($record->person->photo) }}" class="w-10 h-10 rounded-full object-cover mr-3">
                    @else
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($record->person->first_name, 0, 1) }}{{ mb_substr($record->person->last_name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $record->person->full_name }}</p>
                        @if($record->checked_in_at)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Чек-ін о {{ $record->checked_in_at->format('H:i') }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    @if($record->present)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Присутній
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Відсутній
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
