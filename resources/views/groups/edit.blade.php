@extends('layouts.app')

@section('title', 'Редагувати групу')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('groups.update', $group) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва групи *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Опис</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ old('description', $group->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Лідер групи</label>
                <x-person-select
                    name="leader_id"
                    :people="$people"
                    :selected="old('leader_id', $group->leader_id)"
                    placeholder="Почніть вводити ім'я лідера..."
                    null-text="Без лідера"
                />
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Статус</label>
                <select name="status" id="status"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                    @foreach(\App\Models\Group::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $group->status) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Активна — група регулярно зустрічається. На паузі — тимчасово призупинена. У відпустці — сезонна перерва.
                </p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                @can('delete', $group)
                <button type="button"
                        onclick="if(confirm('Видалити групу? Усі дані про членство будуть втрачені.')) { document.getElementById('delete-group-form').submit(); }"
                        class="text-red-600 hover:text-red-700 text-sm font-medium">
                    Видалити групу
                </button>
                @endcan

                <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 sm:gap-3">
                    <a href="{{ route('groups.show', $group) }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                        Скасувати
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                        Зберегти
                    </button>
                </div>
            </div>
        </form>

        @can('delete', $group)
        <form id="delete-group-form" method="POST" action="{{ route('groups.destroy', $group) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endcan
    </div>
</div>
@endsection
