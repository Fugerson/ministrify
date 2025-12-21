@extends('layouts.app')

@section('title', 'Додати людину')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('people.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Основна інформація</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Ім'я *</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Прізвище *</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Фото</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Контакти</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="+380 67 123 4567">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="telegram_username" class="block text-sm font-medium text-gray-700 mb-1">Telegram</label>
                    <input type="text" name="telegram_username" id="telegram_username" value="{{ old('telegram_username') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="@username">
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Адреса</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Дата народження</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="joined_date" class="block text-sm font-medium text-gray-700 mb-1">Дата приходу в церкву</label>
                    <input type="date" name="joined_date" id="joined_date" value="{{ old('joined_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Теги</h2>

            <div class="flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm" style="color: {{ $tag->color }}">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Служіння</h2>

            <div class="space-y-4">
                @foreach($ministries as $ministry)
                    <div x-data="{ open: false }" class="border rounded-lg p-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="ministries[{{ $ministry->id }}][selected]" value="1"
                                   @click="open = $event.target.checked"
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 font-medium">{{ $ministry->icon }} {{ $ministry->name }}</span>
                        </label>

                        <div x-show="open" x-cloak class="mt-3 ml-6 space-y-2">
                            <p class="text-sm text-gray-500">Позиції:</p>
                            @foreach($ministry->positions as $position)
                                <label class="flex items-center">
                                    <input type="checkbox" name="ministries[{{ $ministry->id }}][positions][]" value="{{ $position->id }}"
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm">{{ $position->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Нотатки</h2>

            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Додаткова інформація...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('people.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>
</div>
@endsection
