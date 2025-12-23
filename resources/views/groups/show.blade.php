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
<div class="space-y-8">
    <!-- Group Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $group->name }}</h2>
                @if($group->description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $group->description }}</p>
                @endif

                @if($group->leader)
                <div class="flex items-center mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Лідер: <a href="{{ route('people.show', $group->leader) }}" class="ml-1 text-primary-600 dark:text-primary-400 hover:underline">{{ $group->leader->full_name }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Members -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
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
            @forelse($group->members as $member)
            <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <a href="{{ route('people.show', $member) }}" class="flex items-center flex-1 min-w-0">
                    @if($member->photo)
                    <img src="{{ Storage::url($member->photo) }}" class="w-10 h-10 rounded-full object-cover mr-3">
                    @else
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mr-3 flex-shrink-0">
                        <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $member->full_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($member->pivot->role === 'leader')
                            <span class="text-primary-600 dark:text-primary-400 font-medium">Лідер</span>
                            @elseif($member->pivot->role === 'co-leader')
                            <span class="text-amber-600 dark:text-amber-400">Со-лідер</span>
                            @else
                            Учасник
                            @endif
                        </p>
                    </div>
                </a>
                @can('update', $group)
                @if($member->pivot->role !== 'leader')
                <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}" onsubmit="return confirm('Видалити учасника з групи?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
                @endif
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
                        <select name="person_id" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Оберіть...</option>
                            @foreach($availablePeople as $person)
                            <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Роль</label>
                        <select name="role" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white focus:ring-2 focus:ring-primary-500">
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
