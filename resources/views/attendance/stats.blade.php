@extends('layouts.app')

@section('title', 'Статистика відвідуваності')

@section('content')
<div class="space-y-6">
    <!-- Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Середня відвідуваність</h2>

        <div class="text-center">
            <p class="text-5xl font-bold text-primary-600">{{ round($monthlyAttendance ?? 0) }}</p>
            <p class="text-gray-500 dark:text-gray-400">осіб в середньому</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Динаміка за останні 12 тижнів</h2>

        <div class="h-64 flex items-end justify-between space-x-2">
            @php $maxCount = max(array_column($chartData, 'count')) ?: 1; @endphp
            @foreach($chartData as $data)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-primary-100 dark:bg-primary-900 rounded-t"
                         style="height: {{ ($data['count'] / $maxCount) * 100 }}%">
                        <div class="w-full h-full bg-primary-500 rounded-t hover:bg-primary-600 transition-colors"
                             title="{{ $data['count'] }} осіб"></div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $data['date'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- People needing attention -->
    @if(count($needAttention) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">&#9888; Потребують уваги</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Не відвідували 3+ тижні</p>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($needAttention as $person)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-600 dark:text-gray-400">{{ substr($person->first_name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <a href="{{ route('people.show', $person) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-600">
                                {{ $person->full_name }}
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($person->phone)
                            <a href="tel:{{ $person->phone }}" class="text-primary-600 hover:text-primary-500 text-sm">
                                Зателефонувати
                            </a>
                        @endif
                        @if($person->telegram_username)
                            <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $person->telegram_username }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <a href="{{ route('attendance.index') }}" class="inline-block text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        &larr; Назад до списку
    </a>
</div>
@endsection
