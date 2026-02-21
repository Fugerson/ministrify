@extends('layouts.system-admin')

@section('title', 'Користувачі')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap gap-3 sm:gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук за іменем або email..."
                   class="sm:col-span-2 lg:flex-1 lg:min-w-64 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

            <select name="church_id"
                    class="w-full lg:w-auto px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Всі церкви</option>
                @foreach($churches as $church)
                <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                    {{ $church->name }}
                </option>
                @endforeach
            </select>

            <select name="role"
                    class="w-full lg:w-auto px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Всі ролі</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Адміністратор</option>
                <option value="leader" {{ request('role') == 'leader' ? 'selected' : '' }}>Лідер</option>
                <option value="volunteer" {{ request('role') == 'volunteer' ? 'selected' : '' }}>Служитель</option>
            </select>

            <label class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white cursor-pointer">
                <input type="checkbox" name="super_admin" value="1" {{ request()->has('super_admin') ? 'checked' : '' }}
                       class="rounded bg-gray-100 dark:bg-gray-600 border-gray-300 dark:border-gray-500 text-red-600 focus:ring-indigo-500">
                <span>Super Admin</span>
            </label>

            <select name="per_page" id="perPageSelect"
                    onchange="document.cookie='system_users_per_page='+this.value+';path=/;max-age=31536000'; this.form.submit()"
                    class="w-full lg:w-auto px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            </select>

            <div class="flex gap-3 sm:col-span-2 lg:contents">
                <button type="submit" class="flex-1 lg:flex-none px-6 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg">Фільтрувати</button>
                <a href="{{ route('system.users.index') }}" class="flex-1 lg:flex-none px-6 py-2 text-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-white rounded-lg">Скинути</a>
            </div>
        </form>

        @if($deletedCount > 0)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Видалених: {{ $deletedCount }}
            </span>
            <a href="{{ route('system.users.index', ['only_deleted' => 1]) }}"
               class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">
                Показати видалених →
            </a>
        </div>
        @endif
    </div>

    <!-- Users Table (desktop) -->
    <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Церква / Роль</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Створено</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $user->trashed() ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($user->person?->photo)
                                <img src="{{ Storage::url($user->person->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover mr-3 {{ $user->trashed() ? 'opacity-50' : '' }}">
                                @else
                                <div class="w-10 h-10 rounded-full {{ $user->trashed() ? 'bg-red-400' : ($user->is_super_admin ? 'bg-indigo-600' : 'bg-gray-400 dark:bg-gray-600') }} flex items-center justify-center mr-3">
                                    <span class="text-sm font-medium text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                                </div>
                                @endif
                                <div>
                                    <p class="font-medium {{ $user->trashed() ? 'text-red-600 dark:text-red-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                    @if($user->trashed())
                                    <p class="text-xs text-red-500 mt-1">Видалено: {{ $user->deleted_at->format('d.m.Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_super_admin)
                            <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-600/20 text-indigo-700 dark:text-indigo-400 text-xs rounded-full">Super Admin</span>
                            @endif
                            @forelse($user->churchMemberships ?? collect() as $membership)
                            @if($membership->church)
                            <div class="flex items-center gap-2 {{ !$loop->first ? 'mt-1' : '' }}">
                                @if($membership->church->id === $user->church_id)
                                <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0" title="Активна"></span>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 flex-shrink-0"></span>
                                @endif
                                <a href="{{ route('system.churches.show', $membership->church) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm truncate">
                                    {{ $membership->church->name }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                @if($membership->role)
                                <span class="px-2 py-0.5 text-xs rounded-full whitespace-nowrap
                                    {{ $membership->role->is_admin_role ? 'bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400' : 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400' }}
                                ">{{ $membership->role->name }}</span>
                                @else
                                <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-600/20 text-amber-700 dark:text-amber-400 text-xs rounded-full whitespace-nowrap">Очікує</span>
                                @endif
                            </div>
                            @endif
                            @empty
                                @if(!$user->is_super_admin)
                                <span class="text-gray-400 dark:text-gray-500 text-sm">Без церкви</span>
                                @endif
                            @endforelse
                        </td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300 text-sm">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($user->trashed())
                                    <form method="POST" action="{{ route('system.users.restore', $user->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-green-500 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg" title="Відновити">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('system.users.forceDelete', $user->id) }}"
                                          onsubmit="return confirm('{{ __('messages.confirm_delete_user_warning', ['name' => $user->name]) }}')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" title="Видалити назавжди">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('system.users.impersonate', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Увійти як {{ $user->name }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('system.users.edit', $user) }}"
                                       class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Редагувати">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('system.users.destroy', $user) }}"
                                          onsubmit="return confirm('{{ __('messages.confirm_delete_user', ['name' => $user->name]) }}')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Видалити">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Користувачів не знайдено</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Users Cards (mobile) -->
    <div class="md:hidden space-y-3">
        @forelse($users as $user)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 {{ $user->trashed() ? 'border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/10' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center min-w-0">
                    @if($user->person?->photo)
                    <img src="{{ Storage::url($user->person->photo) }}" alt="" class="w-10 h-10 shrink-0 rounded-full object-cover mr-3 {{ $user->trashed() ? 'opacity-50' : '' }}">
                    @else
                    <div class="w-10 h-10 shrink-0 rounded-full {{ $user->trashed() ? 'bg-red-400' : ($user->is_super_admin ? 'bg-indigo-600' : 'bg-gray-400 dark:bg-gray-600') }} flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-medium truncate {{ $user->trashed() ? 'text-red-600 dark:text-red-400 line-through' : 'text-gray-900 dark:text-white' }}">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @if($user->trashed())
                        <form method="POST" action="{{ route('system.users.restore', $user->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-green-500 hover:text-green-600 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg" title="Відновити">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('system.users.forceDelete', $user->id) }}"
                              onsubmit="return confirm('{{ __('messages.confirm_delete_user_warning', ['name' => $user->name]) }}')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" title="Видалити назавжди">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </button>
                        </form>
                    @else
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('system.users.impersonate', $user) }}" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg" title="Увійти як {{ $user->name }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('system.users.edit', $user) }}"
                           class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-lg" title="Редагувати">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('system.users.destroy', $user) }}"
                              onsubmit="return confirm('{{ __('messages.confirm_delete_user', ['name' => $user->name]) }}')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg" title="Видалити">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-3 text-xs space-y-1">
                @if($user->is_super_admin)
                <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-600/20 text-indigo-700 dark:text-indigo-400 rounded-full">Super Admin</span>
                @endif
                @forelse($user->churchMemberships ?? collect() as $membership)
                @if($membership->church)
                <div class="flex items-center gap-1.5">
                    @if($membership->church->id === $user->church_id)
                    <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0" title="Активна"></span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 flex-shrink-0"></span>
                    @endif
                    <a href="{{ route('system.churches.show', $membership->church) }}" class="text-blue-600 dark:text-blue-400 truncate">
                        {{ $membership->church->name }}
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">—</span>
                    @if($membership->role)
                    <span class="px-2 py-0.5 rounded-full whitespace-nowrap
                        {{ $membership->role->is_admin_role ? 'bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400' : 'bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400' }}
                    ">{{ $membership->role->name }}</span>
                    @else
                    <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-600/20 text-amber-700 dark:text-amber-400 rounded-full whitespace-nowrap">Очікує</span>
                    @endif
                </div>
                @endif
                @empty
                    @if(!$user->is_super_admin)
                    <span class="text-gray-400 dark:text-gray-500">Без церкви</span>
                    @endif
                @endforelse
                <span class="text-gray-400 dark:text-gray-500">{{ $user->created_at->format('d.m.Y') }}</span>
            </div>

            @if($user->trashed())
            <p class="text-xs text-red-500 mt-2">Видалено: {{ $user->deleted_at->format('d.m.Y H:i') }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
            Користувачів не знайдено
        </div>
        @endforelse

        @if($users->hasPages())
        <div class="px-2 py-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
