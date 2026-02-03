@extends('layouts.app')

@section('title', 'Ролі музичного служіння')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ролі музичного служіння</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Налаштуйте інструменти та ролі для команди прославлення</p>
        </div>
    </div>

    <!-- Roles List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        @if($roles->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($roles as $role)
                    <div class="p-4 flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                                 style="background-color: {{ $role->color ?? '#6366f1' }}20; color: {{ $role->color ?? '#6366f1' }}">
                                {{ $role->icon ?? '🎵' }}
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $role->name }}</span>
                        </div>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button"
                                    onclick="editRole({{ $role->id }}, '{{ $role->name }}', '{{ $role->icon }}', '{{ $role->color }}')"
                                    class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <form action="{{ route('settings.worship-roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Видалити роль?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Немає ролей</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Додайте ролі для команди прославлення</p>
            </div>
        @endif

        <!-- Add Role Form -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <form action="{{ route('settings.worship-roles.store') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Назва ролі (напр. Вокал, Гітара)" required
                       class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <input type="text" name="icon" placeholder="Іконка" maxlength="5"
                       class="w-20 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                <input type="color" name="color" value="#6366f1"
                       class="w-12 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Додати
                </button>
            </form>
        </div>
    </div>

    <!-- Suggested Roles -->
    @if($roles->count() === 0)
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Рекомендовані ролі:</h3>
        <div class="flex flex-wrap gap-2">
            @foreach(['🎤 Ведучий вокал', '🎤 Бек-вокал', '🎸 Акустична гітара', '🎸 Електрогітара', '🎸 Бас', '🎹 Клавіші', '🥁 Барабани', '🎚 Звук', '💻 Медіа'] as $suggestion)
                <button type="button" onclick="addSuggested('{{ $suggestion }}')"
                        class="px-3 py-1.5 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 transition-colors">
                    {{ $suggestion }}
                </button>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="closeEditModal(event)">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Редагувати роль</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва</label>
                    <input type="text" name="name" id="editName" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Іконка</label>
                        <input type="text" name="icon" id="editIcon" maxlength="5"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                        <input type="color" name="color" id="editColor"
                               class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Скасувати
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Зберегти
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editRole(id, name, icon, color) {
    document.getElementById('editForm').action = '/settings/worship-roles/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editIcon').value = icon || '';
    document.getElementById('editColor').value = color || '#6366f1';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal(event) {
    if (!event || event.target.id === 'editModal') {
        document.getElementById('editModal').classList.add('hidden');
    }
}

function addSuggested(text) {
    const parts = text.split(' ');
    const icon = parts[0];
    const name = parts.slice(1).join(' ');

    const form = document.querySelector('form[action*="worship-roles"]');
    form.querySelector('input[name="name"]').value = name;
    form.querySelector('input[name="icon"]').value = icon;
    form.submit();
}
</script>
@endsection
