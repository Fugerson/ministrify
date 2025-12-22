@extends('layouts.app')

@section('title', 'Журнал дій')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Журнал дій</h1>
            <p class="text-gray-500 dark:text-gray-400">Історія всіх змін у системі</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Пошук..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
            </div>
            <div>
                <select name="action" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі дії</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ match($action) {
                                'created' => 'Створено',
                                'updated' => 'Оновлено',
                                'deleted' => 'Видалено',
                                'restored' => 'Відновлено',
                                'login' => 'Вхід',
                                'logout' => 'Вихід',
                                default => $action
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="model" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі типи</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ match($model) {
                                'Person' => 'Члени',
                                'Event' => 'Події',
                                'Ministry' => 'Служіння',
                                'Group' => 'Групи',
                                'Expense' => 'Витрати',
                                'Income' => 'Доходи',
                                'User' => 'Користувачі',
                                default => $model
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі користувачі</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="Від">
            </div>
            <div class="flex gap-2">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="До">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Logs List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дія</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Тип</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Об'єкт</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">IP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $log->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($log->user)
                                        <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                            <span class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $log->user_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $color = $log->action_color;
                                    $colorClasses = match($color) {
                                        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                                        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                                        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium {{ $colorClasses }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->action_icon }}"/>
                                    </svg>
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->model_label }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                {{ Str::limit($log->model_name, 30) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-4 py-3">
                                @if($log->old_values || $log->new_values)
                                    <button onclick="showLogDetails({{ $log->id }})"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-700 text-sm">
                                        Деталі
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                Записів не знайдено
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div id="logModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="hideLogModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Деталі зміни</h3>
                <button onclick="hideLogModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="logModalContent" class="px-6 py-4 overflow-y-auto max-h-[60vh]">
                <div class="text-center py-8 text-gray-500">Завантаження...</div>
            </div>
        </div>
    </div>
</div>

<script>
function showLogDetails(logId) {
    document.getElementById('logModal').classList.remove('hidden');
    document.getElementById('logModalContent').innerHTML = '<div class="text-center py-8 text-gray-500">Завантаження...</div>';

    fetch(`/settings/audit-logs/${logId}`)
        .then(r => r.json())
        .then(data => {
            let html = '<div class="space-y-4">';

            if (data.changes && data.changes.length > 0) {
                html += '<table class="w-full text-sm">';
                html += '<thead><tr class="border-b dark:border-gray-700"><th class="py-2 text-left text-gray-500">Поле</th><th class="py-2 text-left text-gray-500">Було</th><th class="py-2 text-left text-gray-500">Стало</th></tr></thead>';
                html += '<tbody>';
                data.changes.forEach(change => {
                    html += `<tr class="border-b dark:border-gray-700">
                        <td class="py-2 font-medium">${change.field}</td>
                        <td class="py-2 text-red-600 dark:text-red-400">${change.old ?? '-'}</td>
                        <td class="py-2 text-green-600 dark:text-green-400">${change.new ?? '-'}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
            } else {
                html += '<p class="text-gray-500">Деталі недоступні</p>';
            }

            html += '</div>';
            document.getElementById('logModalContent').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('logModalContent').innerHTML = '<p class="text-red-500">Помилка завантаження</p>';
        });
}

function hideLogModal() {
    document.getElementById('logModal').classList.add('hidden');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') hideLogModal();
});
</script>
@endsection
