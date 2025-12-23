@extends('layouts.app')

@section('title', 'Нова група')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('groups.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва групи *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="Молодіжна група, Хор, Служіння дітям..."
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Коротко про групу та її діяльність..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="leader_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Лідер групи</label>
                <select name="leader_id" id="leader_id"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    <option value="">Обрати лідера...</option>
                    @foreach($people as $person)
                    <option value="{{ $person->id }}" {{ old('leader_id') == $person->id ? 'selected' : '' }}>
                        {{ $person->full_name }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Лідер автоматично стане учасником групи</p>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('groups.index') }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Скасувати
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Створити групу
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
