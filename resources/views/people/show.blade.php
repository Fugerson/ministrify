@extends('layouts.app')

@section('title', $person->full_name)

@section('actions')
<a href="{{ route('people.edit', $person) }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Редагувати
</a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-24"></div>
        <div class="px-6 pb-6 -mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($person->photo)
                        <img class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-lg"
                             src="{{ Storage::url($person->photo) }}" alt="">
                    @else
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-800 shadow-lg">
                            <span class="text-3xl font-bold text-gray-500 dark:text-gray-300">{{ mb_substr($person->first_name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $person->full_name }}</h1>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($person->tags as $tag)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                        @if($person->joined_date && $stats['membership_days'] !== null)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ floor($stats['membership_days'] / 365) > 0 ? floor($stats['membership_days'] / 365) . ' р. ' : '' }}{{ $stats['membership_days'] % 365 }} днів в церкві
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-2">
                    @if($person->phone)
                        <a href="tel:{{ $person->phone }}" class="p-3 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded-xl hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </a>
                    @endif
                    @if($person->telegram_username)
                        <a href="https://t.me/{{ ltrim($person->telegram_username, '@') }}" target="_blank" class="p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-xl hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.535.223l.19-2.72 4.94-4.463c.215-.19-.047-.295-.334-.105l-6.11 3.85-2.63-.82c-.57-.18-.583-.57.12-.847l10.27-3.96c.475-.18.89.115.735.84z"/>
                            </svg>
                        </a>
                    @endif
                    @if($person->email)
                        <a href="mailto:{{ $person->email }}" class="p-3 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-xl hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                @if($person->phone)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Телефон</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $person->phone }}</p>
                    </div>
                @endif
                @if($person->email)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $person->email }}</p>
                    </div>
                @endif
                @if($person->birth_date)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">День народження</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $person->birth_date->format('d.m.Y') }}</p>
                    </div>
                @endif
                @if($person->address)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Адреса</p>
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $person->address }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['attendance_30_days'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Відвідувань за 30 днів</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['services_this_month'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Служінь цього місяця</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['attendance_rate'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Відвідуваність (3 міс.)</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['services_total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Всього служінь</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ministries & Groups -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ministries -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Служіння</h2>
                </div>
                @if($person->ministries->count() > 0)
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($person->ministries as $ministry)
                            <a href="{{ route('ministries.show', $ministry) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $ministry->color ?? '#3b82f6' }}20;">
                                        <span class="text-xl">{{ $ministry->icon }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</p>
                                        @php
                                            $positionIds = is_array($ministry->pivot->position_ids)
                                                ? $ministry->pivot->position_ids
                                                : json_decode($ministry->pivot->position_ids ?? '[]', true);
                                            $positions = $ministry->positions->whereIn('id', $positionIds ?? []);
                                        @endphp
                                        @if($positions->count() > 0)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $positions->pluck('name')->implode(', ') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Не бере участь у служіннях
                    </div>
                @endif
            </div>

            <!-- Groups -->
            @if($person->groups->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Групи</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($person->groups as $group)
                        <a href="{{ route('groups.show', $group) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $group->color }}20;">
                                    <svg class="w-5 h-5" style="color: {{ $group->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $group->name }}</p>
                                    @if($group->meeting_day)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ ['monday' => 'Понеділок', 'tuesday' => 'Вівторок', 'wednesday' => 'Середа', 'thursday' => 'Четвер', 'friday' => "П'ятниця", 'saturday' => 'Субота', 'sunday' => 'Неділя'][$group->meeting_day] ?? $group->meeting_day }}
                                            @if($group->meeting_time) {{ $group->meeting_time->format('H:i') }} @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Attendance Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність (12 тижнів)</h2>
                <div class="h-48">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Активність</h2>
                @if($stats['last_attended'])
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Останнє відвідування: {{ $stats['last_attended']->format('d.m.Y') }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                @forelse($activities as $activity)
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $activity['color'] === 'green' ? 'bg-green-100 dark:bg-green-900/50' :
                               ($activity['color'] === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/50' : 'bg-red-100 dark:bg-red-900/50') }}">
                            <span class="text-sm">{{ $activity['icon'] }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] }}</p>
                            @if(isset($activity['subtitle']))
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['subtitle'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $activity['date']->format('d.m.Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Немає активності за останні 3 місяці
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Assignments -->
    @if($person->assignments->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Останні призначення</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($person->assignments->take(10) as $assignment)
                <a href="{{ route('events.show', $assignment->event) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-xl">{{ $assignment->event->ministry->icon }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $assignment->event->title }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $assignment->event->date->format('d.m.Y') }} &bull; {{ $assignment->position->name }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                        {{ $assignment->status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' :
                           ($assignment->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300') }}">
                        {{ $assignment->status_label }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($person->notes)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Нотатки</h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $person->notes }}</p>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('people.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад до списку
        </a>

        <form method="POST" action="{{ route('people.destroy', $person) }}"
              onsubmit="return confirm('Ви впевнені, що хочете видалити цю людину?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">
                Видалити
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? '#374151' : '#f3f4f6';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(collect($attendanceChartData)->pluck('week')),
            datasets: [{
                label: 'Відвідування',
                data: @json(collect($attendanceChartData)->pluck('count')),
                backgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}80',
                borderColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
