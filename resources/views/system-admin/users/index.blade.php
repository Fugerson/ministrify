@extends('layouts.system-admin')

@section('title', 'Користувачі')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук за іменем або email..."
                   class="flex-1 min-w-64 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">

            <select name="church_id"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">Всі церкви</option>
                @foreach($churches as $church)
                <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                    {{ $church->name }}
                </option>
                @endforeach
            </select>

            <select name="role"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">Всі ролі</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Адміністратор</option>
                <option value="leader" {{ request('role') == 'leader' ? 'selected' : '' }}>Лідер</option>
                <option value="volunteer" {{ request('role') == 'volunteer' ? 'selected' : '' }}>Волонтер</option>
            </select>

            <label class="flex items-center gap-2 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white cursor-pointer">
                <input type="checkbox" name="super_admin" value="1" {{ request()->has('super_admin') ? 'checked' : '' }}
                       class="rounded bg-gray-600 border-gray-500 text-red-600 focus:ring-red-500">
                <span>Super Admin</span>
            </label>

            <button type="submit" class="px-6 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg">Фільтрувати</button>
            <a href="{{ route('system.users.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg">Скинути</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Роль</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Статус</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Створено</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full {{ $user->is_super_admin ? 'bg-red-600' : 'bg-gray-600' }} flex items-center justify-center mr-3">
                                    <span class="text-sm font-medium text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->church)
                            <a href="{{ route('system.churches.show', $user->church) }}" class="text-blue-400 hover:text-blue-300">
                                {{ $user->church->name }}
                            </a>
                            @else
                            <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $user->role === 'admin' ? 'bg-red-600/20 text-red-400' : '' }}
                                {{ $user->role === 'leader' ? 'bg-blue-600/20 text-blue-400' : '' }}
                                {{ $user->role === 'volunteer' ? 'bg-green-600/20 text-green-400' : '' }}
                            ">{{ $user->role }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_super_admin)
                            <span class="px-2 py-1 bg-red-600/20 text-red-400 text-xs rounded-full">Super Admin</span>
                            @else
                            <span class="px-2 py-1 bg-gray-600/20 text-gray-400 text-xs rounded-full">Звичайний</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-gray-300 text-sm">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('system.users.edit', $user) }}"
                                   class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg" title="Редагувати">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('system.users.destroy', $user) }}"
                                      onsubmit="return confirm('Видалити користувача {{ $user->name }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded-lg" title="Видалити">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Користувачів не знайдено</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
