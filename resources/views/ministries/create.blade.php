@extends('layouts.app')

@section('title', 'Створити служіння')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('ministries.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Основна інформація</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-4 gap-4">
                    <div class="col-span-1">
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Іконка *</label>
                        <select name="icon" id="icon" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-2xl">
                            <option value="🎵">🎵</option>
                            <option value="👶">👶</option>
                            <option value="🎤">🎤</option>
                            <option value="🙏">🙏</option>
                            <option value="📖">📖</option>
                            <option value="🏠">🏠</option>
                            <option value="🎬">🎬</option>
                            <option value="🎧">🎧</option>
                            <option value="☕">☕</option>
                            <option value="🚗">🚗</option>
                            <option value="💻">💻</option>
                            <option value="🎨">🎨</option>
                        </select>
                    </div>

                    <div class="col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Назва *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Worship">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Опис</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="leader_id" class="block text-sm font-medium text-gray-700 mb-1">Лідер</label>
                    <select name="leader_id" id="leader_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Не вибрано</option>
                        @foreach($people as $person)
                            <option value="{{ $person->id }}" {{ old('leader_id') == $person->id ? 'selected' : '' }}>
                                {{ $person->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="monthly_budget" class="block text-sm font-medium text-gray-700 mb-1">Бюджет на місяць (грн)</label>
                    <input type="number" name="monthly_budget" id="monthly_budget" value="{{ old('monthly_budget') }}" min="0" step="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="5000">
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Колір</label>
                    <input type="color" name="color" id="color" value="{{ old('color', '#3b82f6') }}"
                           class="w-16 h-10 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Позиції</h2>
            <p class="text-sm text-gray-500 mb-4">Додайте позиції, на які можна призначати людей</p>

            <div class="space-y-2" id="positions-container">
                <input type="text" name="positions[]" placeholder="Наприклад: Вокал"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <input type="text" name="positions[]" placeholder="Наприклад: Гітара"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <input type="text" name="positions[]" placeholder="Наприклад: Звук"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <button type="button" onclick="addPosition()" class="mt-2 text-sm text-primary-600 hover:text-primary-500">
                + Додати позицію
            </button>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('ministries.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Створити
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function addPosition() {
    const container = document.getElementById('positions-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'positions[]';
    input.placeholder = 'Назва позиції';
    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
    container.appendChild(input);
}
</script>
@endpush
@endsection
