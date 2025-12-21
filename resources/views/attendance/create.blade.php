@extends('layouts.app')

@section('title', 'Check-in')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('attendance.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                @if($event)
                    Check-in: {{ $event->title }}
                @else
                    Новий Check-in
                @endif
            </h2>

            @if($event)
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-900">{{ $event->ministry->icon }} {{ $event->ministry->name }}</p>
                    <p class="text-sm text-gray-500">{{ $event->date->format('d.m.Y') }} о {{ $event->time->format('H:i') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Дата *</label>
                    <input type="date" name="date" id="date"
                           value="{{ old('date', $date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="total_count" class="block text-sm font-medium text-gray-700 mb-1">Загальна кількість *</label>
                    <input type="number" name="total_count" id="total_count"
                           value="{{ old('total_count', 0) }}" required min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Нотатки</label>
                <textarea name="notes" id="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Відмітити присутність</h3>

            <div class="mb-4">
                <input type="text" id="search" placeholder="Пошук..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto" id="people-list">
                @foreach($people as $person)
                    <label class="flex items-center p-2 hover:bg-gray-50 rounded-lg person-item">
                        <input type="checkbox" name="present[]" value="{{ $person->id }}"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-3 text-gray-900">{{ $person->full_name }}</span>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t flex items-center justify-between text-sm text-gray-500">
                <span id="selected-count">0 обрано</span>
                <button type="button" onclick="selectAll()" class="text-primary-600 hover:text-primary-500">
                    Обрати всіх
                </button>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('attendance.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('search').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.person-item').forEach(function(item) {
        const name = item.textContent.toLowerCase();
        item.style.display = name.includes(search) ? '' : 'none';
    });
});

document.querySelectorAll('input[name="present[]"]').forEach(function(checkbox) {
    checkbox.addEventListener('change', updateCount);
});

function updateCount() {
    const count = document.querySelectorAll('input[name="present[]"]:checked').length;
    document.getElementById('selected-count').textContent = count + ' обрано';
}

function selectAll() {
    document.querySelectorAll('.person-item:not([style*="display: none"]) input[name="present[]"]').forEach(function(checkbox) {
        checkbox.checked = true;
    });
    updateCount();
}
</script>
@endpush
@endsection
