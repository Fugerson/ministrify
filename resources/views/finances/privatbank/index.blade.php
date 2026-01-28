@extends('layouts.app')

@section('title', 'ПриватБанк')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ПриватБанк</h1>
            <p class="text-gray-600 dark:text-gray-400">Імпорт транзакцій з ПриватБанку</p>
        </div>

        @if($isConnected)
            <div class="flex items-center gap-3">
                <form action="{{ route('finances.privatbank.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Синхронізувати
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            @foreach($errors->all() as $error)
                <p class="text-red-800 dark:text-red-200">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(!$isConnected)
        <!-- Setup Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="max-w-lg">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Підключити ПриватБанк</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Отримуйте виписку з карти автоматично</p>
                    </div>
                </div>

                <form action="{{ route('finances.privatbank.connect') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Merchant ID</label>
                        <input type="text" name="merchant_id" value="{{ old('merchant_id') }}" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="12345">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Отримайте на api.privatbank.ua</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пароль мерчанта</label>
                        <input type="password" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Номер картки</label>
                        <input type="text" name="card_number" value="{{ old('card_number') }}" required
                               maxlength="16" pattern="[0-9]{16}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono"
                               placeholder="4149XXXXXXXXXXXX">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">16 цифр без пробілів</p>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        Підключити
                    </button>
                </form>

                <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Як отримати дані для підключення:</strong><br>
                        1. Зареєструйтесь на <a href="https://api.privatbank.ua/#p24/registration" target="_blank" class="underline">api.privatbank.ua</a><br>
                        2. Скопіюйте Merchant ID та пароль<br>
                        3. Вкажіть номер картки для отримання виписки
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Connected State -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Stats Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Картка</p>
                        <p class="font-semibold text-gray-900 dark:text-white font-mono text-sm">{{ $maskedCard }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Нових</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $stats['unprocessed'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Цей місяць</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($stats['this_month_amount'], 0, ',', ' ') }} ₴</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Синхронізація</p>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">
                            @if($stats['last_sync'])
                                {{ $stats['last_sync']->diffForHumans() }}
                            @else
                                Ніколи
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <form action="{{ route('finances.privatbank.toggle-auto-sync') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-sm {{ $church->privatbank_auto_sync ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($church->privatbank_auto_sync)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @endif
                            </svg>
                            Автосинхронізація {{ $church->privatbank_auto_sync ? 'увімкнена' : 'вимкнена' }}
                        </button>
                    </form>
                </div>

                <form action="{{ route('finances.privatbank.disconnect') }}" method="POST" onsubmit="return confirm('Відключити ПриватБанк?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                        Відключити ПриватБанк
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex gap-4 overflow-x-auto">
                @php
                    $tabs = [
                        'new' => ['label' => 'Нові', 'count' => $stats['unprocessed']],
                        'imported' => ['label' => 'Імпортовані', 'count' => null],
                        'ignored' => ['label' => 'Приховані', 'count' => $stats['ignored']],
                        'expenses' => ['label' => 'Витрати', 'count' => null],
                        'all' => ['label' => 'Всі', 'count' => $stats['total']],
                    ];
                @endphp
                @foreach($tabs as $key => $tabInfo)
                    <a href="{{ route('finances.privatbank.index', ['tab' => $key]) }}"
                       class="px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $tab === $key ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        {{ $tabInfo['label'] }}
                        @if($tabInfo['count'])
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $tab === $key ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $tabInfo['count'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            @if($transactions->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Немає транзакцій</p>
                    @if($tab === 'new')
                        <form action="{{ route('finances.privatbank.sync') }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">
                                Синхронізувати зараз
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Опис</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Сума</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($transactions as $tx)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $tx->privat_time->format('d.m.Y') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->privat_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $tx->counterpart_display }}</div>
                                        @if($tx->description && $tx->description !== $tx->counterpart_name)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($tx->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <span class="font-medium {{ $tx->is_income ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $tx->formatted_amount }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(!$tx->is_processed && !$tx->is_ignored && $tx->is_income)
                                            <!-- Import Modal Trigger -->
                                            <button type="button"
                                                    onclick="openImportModal({{ $tx->id }}, '{{ $tx->counterpart_display }}', {{ $tx->amount_uah }})"
                                                    class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm">
                                                Імпорт
                                            </button>
                                            <form action="{{ route('finances.privatbank.ignore', $tx) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm">
                                                    Приховати
                                                </button>
                                            </form>
                                        @elseif($tx->is_ignored)
                                            <form action="{{ route('finances.privatbank.restore', $tx) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                                                    Відновити
                                                </button>
                                            </form>
                                        @elseif($tx->is_processed)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Оброблено</span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" x-data="{ open: false }">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeImportModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Імпортувати транзакцію</h3>

            <form id="importForm" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відправник</label>
                    <p id="importSender" class="text-gray-900 dark:text-white font-medium"></p>
                    <p id="importAmount" class="text-sm text-green-600 dark:text-green-400"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                    <x-searchable-select
                        name="category_id"
                        :items="$categories"
                        :selected="$donationCategory?->id"
                        labelKey="name"
                        valueKey="id"
                        colorKey="color"
                        placeholder="Пошук категорії..."
                        nullText="Виберіть категорію..."
                        :nullable="false"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Людина (необов'язково)</label>
                    <x-person-select
                        name="person_id"
                        :people="$people"
                        :selected="null"
                        placeholder="Пошук людини..."
                        nullText="Не вказано"
                        nullable
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                    <input type="text" name="description" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        Скасувати
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                        Імпортувати
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openImportModal(id, sender, amount) {
    document.getElementById('importModal').classList.remove('hidden');
    document.getElementById('importForm').action = '/finances/privatbank/' + id + '/import';
    document.getElementById('importSender').textContent = sender;
    document.getElementById('importAmount').textContent = '+' + amount.toFixed(2) + ' UAH';
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}
</script>
@endsection
