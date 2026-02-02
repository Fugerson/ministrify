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
        $userHadNoRole = $user->church_role_id === null;
    @endphp
    <form action="{{ route('settings.users.update', $user) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6"
          x-data="{
              personSelected: {{ json_encode($initialPersonData) }},
              hadNoRole: {{ $userHadNoRole ? 'true' : 'false' }},
              showConfirm: false,
              selectedRoleName: '',
              submitForm() {
                  const roleSelect = document.getElementById('church_role_id');
                  const selectedRole = roleSelect.value;
                  if (this.hadNoRole && selectedRole) {
                      this.selectedRoleName = roleSelect.options[roleSelect.selectedIndex].text;
                      this.showConfirm = true;
                  } else {
                      this.$el.submit();
                  }
              }
          }"
          @person-selected.window="personSelected = $event.detail.person"
          @submit.prevent="submitForm()">
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
        <div x-show="personSelected && personSelected?.email" x-cloak class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                <span class="text-green-600 dark:text-green-400">
                    (<span x-text="personSelected?.email"></span>)
                </span>
            </p>
        </div>

        <!-- Warning when person has no email -->
        <div x-show="personSelected && !personSelected?.email" x-cloak class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
            <p class="text-sm text-amber-700 dark:text-amber-300">
                <span class="font-medium" x-text="personSelected?.full_name"></span>
                — немає email. Додайте email в профілі людини.
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
            <select name="church_role_id" id="church_role_id"
                class="mt-1 block w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-transparent rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white @error('church_role_id') border-red-500 bg-red-50 dark:bg-red-900/20 @enderror">
                <option value="" {{ old('church_role_id', $user->church_role_id) === null ? 'selected' : '' }}>
                    Очікує підтвердження (без доступу)
                </option>
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

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3">
            <a href="{{ route('settings.users.index') }}" class="w-full sm:w-auto px-4 py-2 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Скасувати</a>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700">
                Зберегти
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="showConfirm" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showConfirm = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-4"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-2">Надати доступ?</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-1">
                            Ви збираєтесь надати користувачу <strong>{{ $user->name }}</strong> роль:
                        </p>
                        <p class="text-center font-semibold text-primary-600 dark:text-primary-400 mb-4" x-text="selectedRoleName"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                            Користувач отримає email-сповіщення про надання доступу.
                        </p>
                        <div class="flex gap-3">
                            <button type="button" @click="showConfirm = false"
                                    class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">
                                Скасувати
                            </button>
                            <button type="button" @click="showConfirm = false; $el.closest('form').submit();"
                                    class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors">
                                Так, надати доступ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
