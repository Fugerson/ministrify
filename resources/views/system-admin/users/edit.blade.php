@extends('layouts.system-admin')

@section('title', 'Редагування користувача')

@section('actions')
<a href="{{ route('system.users.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white font-medium rounded-lg">
    ← Назад
</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('system.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- User Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 space-y-4">
            <div class="flex items-center gap-4 border-b border-gray-200 dark:border-gray-700 pb-4">
                <div class="w-16 h-16 rounded-full {{ $user->is_super_admin ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600' }} flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Створено: {{ $user->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Ім'я *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Церква</label>
                <select name="church_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Без церкви</option>
                    @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ old('church_id', $user->church_id) == $church->id ? 'selected' : '' }}>
                        {{ $church->name }} ({{ $church->city }})
                    </option>
                    @endforeach
                </select>
                @error('church_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Роль в церкві *</label>
                <select name="role" required
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Адміністратор</option>
                    <option value="leader" {{ old('role', $user->role) === 'leader' ? 'selected' : '' }}>Лідер</option>
                    <option value="volunteer" {{ old('role', $user->role) === 'volunteer' ? 'selected' : '' }}>Служитель</option>
                </select>
                @error('role')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Новий пароль</label>
                <input type="password" name="password"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Залиште порожнім, щоб не змінювати">
                @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Super Admin -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Super Admin</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Надає повний доступ до системної адмінки та всіх церков</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_super_admin" value="1"
                           {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                           class="sr-only peer"
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>
            @if($user->id === auth()->id())
            <p class="text-amber-600 dark:text-amber-400 text-sm mt-3">Ви не можете змінити свій статус Super Admin</p>
            @endif
        </div>

        <div class="flex justify-between">
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('system.users.destroy', $user) }}"
                  onsubmit="return confirm('Ви впевнені, що хочете видалити цього користувача?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-3 bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-300 font-medium rounded-xl">
                    Видалити користувача
                </button>
            </form>
            @else
            <div></div>
            @endif

            <div class="flex gap-3">
                <a href="{{ route('system.users.index') }}"
                   class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-xl">
                    Скасувати
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">
                    Зберегти
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
