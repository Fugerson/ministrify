@extends('layouts.app')

@section('title', 'Редагувати користувача')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Редагувати користувача</h1>
    </div>

    <form action="{{ route('settings.users.update', $user) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ім'я</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
            <select name="role" id="role" required
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                <option value="volunteer" {{ old('role', $user->role) === 'volunteer' ? 'selected' : '' }}>Служитель</option>
                <option value="leader" {{ old('role', $user->role) === 'leader' ? 'selected' : '' }}>Лідер</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Адмін</option>
            </select>
            @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Прив'язати до людини</label>
            <div class="mt-1">
                <x-person-select
                    name="person_id"
                    :people="$people"
                    :selected="old('person_id', $user->person?->id)"
                    placeholder="Почніть вводити ім'я..."
                    null-text="Не прив'язувати"
                />
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Новий пароль (залиште порожнім щоб не змінювати)</label>
            <input type="password" name="password" id="password"
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
            @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Скасувати</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
