@extends('layouts.app')

@section('title', 'Редагувати користувача')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Редагувати користувача</h1>
    </div>

    <form action="{{ route('settings.users.update', $user) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Ім'я</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Роль</label>
            <select name="role" id="role" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="volunteer" {{ old('role', $user->role) === 'volunteer' ? 'selected' : '' }}>Волонтер</option>
                <option value="leader" {{ old('role', $user->role) === 'leader' ? 'selected' : '' }}>Лідер</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Адмін</option>
            </select>
            @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="person_id" class="block text-sm font-medium text-gray-700">Прив'язати до людини</label>
            <select name="person_id" id="person_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Не прив'язувати --</option>
                @foreach($people as $person)
                <option value="{{ $person->id }}" {{ old('person_id', $user->person?->id) == $person->id ? 'selected' : '' }}>
                    {{ $person->full_name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Новий пароль (залиште порожнім щоб не змінювати)</label>
            <input type="password" name="password" id="password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
