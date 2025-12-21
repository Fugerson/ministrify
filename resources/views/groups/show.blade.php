@extends('layouts.app')

@section('title', $group->name)

@section('actions')
@can('update', $group)
<a href="{{ route('groups.edit', $group) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Редагувати
</a>
@endcan
@endsection

@section('content')
<div class="space-y-6">
    <!-- Group Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $group->color }}20;">
                <svg class="w-8 h-8" style="color: {{ $group->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                @if($group->description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $group->description }}</p>
                @endif

                <div class="flex flex-wrap gap-4 mt-4">
                    @if($group->leader)
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Лідер: <a href="{{ route('people.show', $group->leader) }}" class="ml-1 text-primary-600 dark:text-primary-400 hover:underline">{{ $group->leader->full_name }}</a>
                    </div>
                    @endif

                    @if($group->meeting_day)
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $group->meeting_day_name }}
                        @if($group->meeting_time)
                        о {{ $group->meeting_time->format('H:i') }}
                        @endif
                    </div>
                    @endif

                    @if($group->location)
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $group->location }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Members -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Учасники ({{ $group->members->count() }})</h3>
                    @can('update', $group)
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                        + Додати
                    </button>
                    @endcan
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($group->members as $member)
                    <div class="p-4 flex items-center justify-between">
                        <a href="{{ route('people.show', $member) }}" class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700 -m-2 p-2 rounded-xl transition-colors">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $member->full_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($member->pivot->role === 'leader')
                                    <span class="text-primary-600 dark:text-primary-400">Лідер</span>
                                    @elseif($member->pivot->role === 'co-leader')
                                    Со-лідер
                                    @else
                                    Учасник
                                    @endif
                                </p>
                            </div>
                        </a>
                        @can('update', $group)
                        <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}" onsubmit="return confirm('Видалити учасника?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Немає учасників
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Attendance & Stats -->
        <div class="space-y-6">
            <!-- Quick Attendance -->
            @can('update', $group)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність</h3>
                <form method="POST" action="{{ route('groups.attendance', $group) }}" class="space-y-3">
                    @csrf
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm dark:text-white">
                    <input type="number" name="total_count" placeholder="Кількість присутніх" min="0" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm dark:text-white">
                    <textarea name="notes" placeholder="Нотатки..." rows="2"
                              class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm dark:text-white"></textarea>
                    <button type="submit" class="w-full py-2.5 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700">
                        Зберегти
                    </button>
                </form>
            </div>
            @endcan

            <!-- Recent Attendance -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Історія зустрічей</h3>
                @if($group->attendances->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Немає записів</p>
                @else
                <div class="space-y-2">
                    @foreach($group->attendances as $attendance)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $attendance->date->format('d.m.Y') }}</p>
                            @if($attendance->notes)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($attendance->notes, 30) }}</p>
                            @endif
                        </div>
                        <span class="px-2.5 py-1 text-sm font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-lg">
                            {{ $attendance->total_count }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
@can('update', $group)
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('addMemberModal').classList.add('hidden')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Додати учасника</h3>
            <form method="POST" action="{{ route('groups.members.add', $group) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Людина</label>
                        <select name="person_id" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                            <option value="">Оберіть...</option>
                            @foreach($availablePeople as $person)
                            <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Роль</label>
                        <select name="role" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                            <option value="member">Учасник</option>
                            <option value="co-leader">Со-лідер</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                        Скасувати
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                        Додати
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
