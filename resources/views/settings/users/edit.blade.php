@extends('layouts.app')

@section('title', 'Редагувати користувача')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Редагувати користувача</h1>
    </div>

    @php
        $linkedPerson = $user->person;
        $initialPersonData = $linkedPerson ? [
            'id' => $linkedPerson->id,
            'full_name' => $linkedPerson->full_name,
            'email' => $linkedPerson->email,
        ] : null;
    @endphp
    <form action="{{ route('settings.users.update', $user) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-6"
          x-data="{ personSelected: {{ json_encode($initialPersonData) }} }"
          @person-selected.window="personSelected = $event.detail.person">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Прив'язати до людини</label>
            <div class="mt-1">
                <x-person-select
                    name="person_id"
                    :people="$people"
                    :selected="old('person_id', $user->person?->id)"
                    placeholder="Почніть вводити ім'я..."
                    null-text="Відв'язати"
                />
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Якщо обрати людину, дані візьмуться з її профілю</p>
        </div>

        <!-- Show selected person info -->
        <div x-show="personSelected" x-cloak class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                <span x-show="personSelected?.email" class="text-green-600 dark:text-green-400">
                    (<span x-text="personSelected?.email"></span>)
                </span>
            </p>
        </div>

        <!-- Show name/email fields only when no person selected -->
        <div x-show="!personSelected" x-cloak>
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ім'я</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('name') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" x-bind:required="!personSelected"
                        class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('email') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <label for="church_role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
            <select name="church_role_id" id="church_role_id" required
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('church_role_id') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                @foreach($churchRoles as $role)
                <option value="{{ $role->id }}" {{ old('church_role_id', $user->church_role_id) == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>
            @error('church_role_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
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
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
