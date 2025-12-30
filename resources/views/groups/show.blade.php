@extends('layouts.app')

@section('title', $group->name)

@section('actions')
<div class="flex items-center gap-2">
    @can('update', $group)
    <a href="{{ route('groups.attendance.checkin', $group) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl hover:bg-green-700 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Чек-ін
    </a>
    <a href="{{ route('groups.edit', $group) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Редагувати
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Group Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                        @if($group->status === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                        @elseif($group->status === 'paused') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                        @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300
                        @endif">
                        <span class="w-1.5 h-1.5 rounded-full
                            @if($group->status === 'active') bg-green-500
                            @elseif($group->status === 'paused') bg-yellow-500
                            @else bg-blue-500
                            @endif"></span>
                        {{ $group->status_label }}
                    </span>
                </div>
                @if($group->description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $group->description }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-600 dark:text-gray-400">
                    @if($group->leader)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Лідер: <a href="{{ route('people.show', $group->leader) }}" class="ml-1 text-primary-600 dark:text-primary-400 hover:underline">{{ $group->leader->full_name }}</a>
                    </div>
                    @endif
                    @if($group->meeting_day)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $group->meeting_day }}
                        @if($group->meeting_time)
                        о {{ $group->meeting_time->format('H:i') }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $group->members->count() }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Учасників</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['total_meetings'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Зустрічей</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['average_attendance'] }}</span>
                @if($attendanceStats['trend'] === 'up')
                <svg class="w-5 h-5 ml-1.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                @elseif($attendanceStats['trend'] === 'down')
                <svg class="w-5 h-5 ml-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                @endif
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Сер. відвідув.</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            @if($attendanceStats['last_meeting'])
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['last_meeting']->date->format('d.m') }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Остання зустріч</div>
            @else
            <div class="text-2xl font-bold text-gray-400">—</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Немає даних</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Members -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Учасники ({{ $group->members->count() }})</h3>
                @can('update', $group)
                <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати
                </button>
                @endcan
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($group->members->sortBy(fn($m) => $m->pivot->role === 'leader' ? 0 : ($m->pivot->role === 'assistant' ? 1 : 2)) as $member)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <a href="{{ route('people.show', $member) }}" class="flex items-center flex-1 min-w-0">
                        @if($member->photo)
                        <img src="{{ Storage::url($member->photo) }}" alt="{{ $member->full_name }}" class="w-10 h-10 rounded-full object-cover mr-3">
                        @else
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3 flex-shrink-0">
                            <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                        </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $member->full_name }}</p>
                            <p class="text-sm">
                                @if($member->pivot->role === 'leader')
                                <span class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Лідер
                                </span>
                                @elseif($member->pivot->role === 'assistant')
                                <span class="text-amber-600 dark:text-amber-400 font-medium">Помічник</span>
                                @else
                                <span class="text-gray-500 dark:text-gray-400">Учасник</span>
                                @endif
                            </p>
                        </div>
                    </a>
                    @can('update', $group)
                    <div class="flex items-center gap-2">
                        <!-- Role dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-10">
                                @if($member->pivot->role !== 'leader')
                                <form method="POST" action="{{ route('groups.members.role', [$group, $member]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="role" value="leader">
                                    <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Зробити лідером
                                    </button>
                                </form>
                                @endif
                                @if($member->pivot->role !== 'assistant')
                                <form method="POST" action="{{ route('groups.members.role', [$group, $member]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="role" value="assistant">
                                    <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Зробити помічником
                                    </button>
                                </form>
                                @endif
                                @if($member->pivot->role !== 'member')
                                <form method="POST" action="{{ route('groups.members.role', [$group, $member]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="role" value="member">
                                    <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Зробити учасником
                                    </button>
                                </form>
                                @endif
                                @if($member->pivot->role !== 'leader')
                                <hr class="my-1 border-gray-100 dark:border-gray-700">
                                <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}" onsubmit="return confirm('Видалити учасника з групи?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        Видалити з групи
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p>Немає учасників</p>
                    @can('update', $group)
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                            class="mt-3 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        Додати першого учасника
                    </button>
                    @endcan
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Відвідуваність</h3>
                <a href="{{ route('groups.attendance.index', $group) }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    Усі записи
                </a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($group->attendances->take(5) as $attendance)
                <a href="{{ route('groups.attendance.show', [$group, $attendance]) }}" class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors block">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $attendance->date->format('d.m.Y') }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($attendance->time)
                            {{ $attendance->time->format('H:i') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $attendance->members_present }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ $group->members->count() }}</span>
                        </div>
                        @if($attendance->guests_count > 0)
                        <p class="text-xs text-gray-500">+{{ $attendance->guests_count }} гостей</p>
                        @endif
                    </div>
                </a>
                @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <p class="text-sm">Немає записів відвідуваності</p>
                    @can('update', $group)
                    <a href="{{ route('groups.attendance.create', $group) }}" class="mt-2 inline-block text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        Записати першу зустріч
                    </a>
                    @endcan
                </div>
                @endforelse
            </div>
            @if($group->attendances->count() > 0)
            @can('update', $group)
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('groups.attendance.create', $group) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Записати зустріч
                </a>
            </div>
            @endcan
            @endif
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
                        <x-person-select name="person_id" :people="$availablePeople" :required="true" :nullable="false" placeholder="Пошук людини..." />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Роль</label>
                        <select name="role" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="member">Учасник</option>
                            <option value="assistant">Помічник</option>
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
