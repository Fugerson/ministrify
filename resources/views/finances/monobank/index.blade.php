@extends('layouts.app')

@section('title', 'Monobank')

@section('content')
<div x-data="monobankPage()" class="space-y-6">
    @if(!$isConnected)
        {{-- Not Connected State --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Підключіть Monobank</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                Автоматично імпортуйте транзакції з вашої картки Monobank для обліку пожертв
            </p>

            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 max-w-md mx-auto mb-6 text-left">
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Як отримати токен:</h3>
                <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">1</span>
                        <span>Перейдіть на <a href="https://api.monobank.ua/" target="_blank" class="text-primary-600 hover:underline">api.monobank.ua</a></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">2</span>
                        <span>Відскануйте QR-код у застосунку Monobank</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">3</span>
                        <span>Натисніть <strong>"Активувати токен"</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-xs font-medium">4</span>
                        <span>Скопіюйте отриманий токен</span>
                    </li>
                </ol>
            </div>

            <form action="{{ route('finances.monobank.connect') }}" method="POST" class="max-w-md mx-auto">
                @csrf
                <div class="mb-4">
                    <input type="text" name="token" placeholder="Вставте токен сюди..."
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           required>
                    @error('token')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Підключити Monobank
                </button>
            </form>
        </div>
    @else
        {{-- Connected State --}}

        {{-- Header with account info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-lg">M</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $clientName ?? 'Monobank' }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($stats['last_sync'])
                                Остання синхронізація: {{ $stats['last_sync']->diffForHumans() }}
                            @else
                                Ще не синхронізовано
                            @endif
                            @if($church->monobank_auto_sync)
                                <span class="text-green-600">&bull; Автосинхронізація</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Sync buttons --}}
                    <form action="{{ route('finances.monobank.sync') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="days" value="7">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Синхронізувати
                        </button>
                    </form>

                    {{-- Settings dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                            <form action="{{ route('finances.monobank.sync') }}" method="POST">
                                @csrf
                                <input type="hidden" name="days" value="14">
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Синхронізувати 14 днів
                                </button>
                            </form>
                            <form action="{{ route('finances.monobank.sync') }}" method="POST">
                                @csrf
                                <input type="hidden" name="days" value="30">
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Синхронізувати 30 днів
                                </button>
                            </form>
                            <hr class="border-gray-200 dark:border-gray-700">
                            <form action="{{ route('finances.monobank.toggle-auto-sync') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ $church->monobank_auto_sync ? 'Вимкнути' : 'Увімкнути' }} автосинхронізацію
                                </button>
                            </form>
                            <hr class="border-gray-200 dark:border-gray-700">
                            <form action="{{ route('finances.monobank.disconnect') }}" method="POST"
                                  onsubmit="return confirm('Ви впевнені? Токен буде видалено.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    Відключити Monobank
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account selector if multiple --}}
            @if(count($accounts) > 1)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <form action="{{ route('finances.monobank.select-account') }}" method="POST" class="flex items-center gap-3">
                        @csrf
                        <label class="text-sm text-gray-600 dark:text-gray-400">Рахунок:</label>
                        <select name="account_id" onchange="this.form.submit()"
                                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach($accounts as $account)
                                <option value="{{ $account['id'] }}" {{ $church->monobank_account_id == $account['id'] ? 'selected' : '' }}>
                                    {{ $account['masked_pan'] ?? $account['iban'] }} ({{ number_format($account['balance'], 2) }} {{ $account['currency'] }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Нові</p>
                <p class="text-2xl font-bold text-primary-600">{{ $stats['unprocessed'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Цей місяць</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['this_month_amount'], 0, ',', ' ') }} ₴</p>
                <p class="text-xs text-gray-400">{{ $stats['this_month_count'] }} транзакцій</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Минулий місяць</p>
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ number_format($stats['last_month_amount'], 0, ',', ' ') }} ₴</p>
                <p class="text-xs text-gray-400">{{ $stats['last_month_count'] }} транзакцій</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Імпортовано</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['imported_income'], 0, ',', ' ') }} ₴</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px overflow-x-auto">
                    @php
                        $tabs = [
                            'new' => ['label' => 'Нові', 'count' => $stats['unprocessed']],
                            'imported' => ['label' => 'Імпортовані', 'count' => null],
                            'ignored' => ['label' => 'Приховані', 'count' => $stats['ignored']],
                            'expenses' => ['label' => 'Витрати', 'count' => null],
                            'all' => ['label' => 'Усі', 'count' => $stats['total']],
                        ];
                    @endphp
                    @foreach($tabs as $key => $tabData)
                        <a href="{{ route('finances.monobank.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
                           class="px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $tab === $key ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ $tabData['label'] }}
                            @if($tabData['count'])
                                <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $tab === $key ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $tabData['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Bulk actions --}}
            @if($tab === 'new' && $transactions->count() > 0)
                <div x-show="selectedIds.length > 0" x-cloak class="p-4 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-800">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-sm text-primary-700 dark:text-primary-300">
                            Обрано: <strong x-text="selectedIds.length"></strong>
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('finances.monobank.bulk-import') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <template x-for="id in selectedIds">
                                    <input type="hidden" name="transaction_ids[]" :value="id">
                                </template>
                                <select name="category_id" required class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $donationCategory && $donationCategory->id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">
                                    Імпортувати
                                </button>
                            </form>
                            <form action="{{ route('finances.monobank.bulk-ignore') }}" method="POST">
                                @csrf
                                <template x-for="id in selectedIds">
                                    <input type="hidden" name="transaction_ids[]" :value="id">
                                </template>
                                <button type="submit" class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-lg">
                                    Приховати
                                </button>
                            </form>
                            <button @click="selectedIds = []" class="px-3 py-1.5 text-gray-600 hover:text-gray-800 text-sm">
                                Скасувати
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Transactions Table --}}
            <div class="overflow-x-auto" x-data="tableFilters()">
                <table class="w-full">
                    {{-- Table Header with Filters --}}
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            @if($tab === 'new')
                                <th class="w-10 px-3 py-3">
                                    <input type="checkbox" @click="toggleAll($event)" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </th>
                            @endif
                            <th class="px-3 py-3 text-left">
                                <div class="space-y-2">
                                    <a href="{{ route('finances.monobank.index', array_merge(request()->except(['sort', 'dir']), ['tab' => $tab, 'sort' => 'mono_time', 'dir' => ($sortField === 'mono_time' && $sortDir === 'desc') ? 'asc' : 'desc'])) }}"
                                       class="flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase hover:text-primary-600">
                                        Дата
                                        @if($sortField === 'mono_time')
                                            <svg class="w-4 h-4 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <div class="flex gap-1">
                                        <input type="date" x-model="filters.date_from" @change="applyFilters()"
                                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <input type="date" x-model="filters.date_to" @change="applyFilters()"
                                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                            </th>
                            <th class="px-3 py-3 text-left">
                                <div class="space-y-2">
                                    <a href="{{ route('finances.monobank.index', array_merge(request()->except(['sort', 'dir']), ['tab' => $tab, 'sort' => 'counterpart_name', 'dir' => ($sortField === 'counterpart_name' && $sortDir === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase hover:text-primary-600">
                                        Опис
                                        @if($sortField === 'counterpart_name')
                                            <svg class="w-4 h-4 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <input type="text" x-model="filters.search" @input.debounce.500ms="applyFilters()" placeholder="Пошук..."
                                           class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                            </th>
                            <th class="px-3 py-3 text-left">
                                <div class="space-y-2">
                                    <a href="{{ route('finances.monobank.index', array_merge(request()->except(['sort', 'dir']), ['tab' => $tab, 'sort' => 'mcc', 'dir' => ($sortField === 'mcc' && $sortDir === 'asc') ? 'desc' : 'asc'])) }}"
                                       class="flex items-center gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase hover:text-primary-600">
                                        Категорія
                                        @if($sortField === 'mcc')
                                            <svg class="w-4 h-4 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <select x-model="filters.mcc_category" @change="applyFilters()" class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Усі</option>
                                        @foreach($mccCategories as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </th>
                            <th class="px-3 py-3 text-right">
                                <div class="space-y-2">
                                    <a href="{{ route('finances.monobank.index', array_merge(request()->except(['sort', 'dir']), ['tab' => $tab, 'sort' => 'amount', 'dir' => ($sortField === 'amount' && $sortDir === 'desc') ? 'asc' : 'desc'])) }}"
                                       class="flex items-center justify-end gap-1 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase hover:text-primary-600">
                                        Сума
                                        @if($sortField === 'amount')
                                            <svg class="w-4 h-4 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    </a>
                                    <div class="flex gap-1">
                                        <input type="number" x-model="filters.amount_min" @input.debounce.500ms="applyFilters()" placeholder="Від" step="1"
                                               class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <input type="number" x-model="filters.amount_max" @input.debounce.500ms="applyFilters()" placeholder="До" step="1"
                                               class="w-16 px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>
                            </th>
                            <th class="px-3 py-3 text-left">
                                <div class="space-y-2">
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Статус</span>
                                    @if($tab === 'imported')
                                        <select x-model="filters.category_id" @change="applyFilters()" class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Усі</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="h-6"></div>
                                    @endif
                                </div>
                            </th>
                            <th class="px-3 py-3 text-center">
                                <div class="space-y-2">
                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Дії</span>
                                    <div class="h-6 flex items-center justify-center">
                                        <button type="button" @click="clearFilters()" x-show="hasFilters()" class="px-2 py-1 text-gray-500 hover:text-gray-700 text-xs" title="Скинути фільтри">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($transactions as $tx)
                            @php
                                $mccKey = $tx->mcc_category_key;
                                $mccColors = [
                                    'utilities' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'groceries' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'restaurants' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'fuel' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'transport' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'healthcare' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'education' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                    'entertainment' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
                                    'shopping' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                    'transfers' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                                    'other' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                ];
                                $colorClass = $mccColors[$mccKey] ?? $mccColors['other'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50" x-data="{ showImport: false }">
                                @if($tab === 'new')
                                    <td class="px-3 py-3">
                                        <input type="checkbox" value="{{ $tx->id }}" x-model.number="selectedIds"
                                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                @endif
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $tx->mono_time->format('d.m.Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $tx->mono_time->format('H:i') }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs" title="{{ $tx->counterpart_display }}">
                                        {{ Str::limit($tx->counterpart_display, 40) }}
                                    </div>
                                    @if($tx->comment)
                                        <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $tx->comment }}">{{ Str::limit($tx->comment, 50) }}</div>
                                    @endif
                                    @if($tx->masked_iban)
                                        <div class="text-xs text-gray-400">{{ $tx->masked_iban }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($tx->mcc)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $colorClass }}">
                                            {{ $tx->mcc_category }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-semibold {{ $tx->is_income ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->formatted_amount }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @if($tx->is_processed)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">
                                            Імпортовано
                                        </span>
                                        @if($tx->person)
                                            <div class="text-xs text-gray-500 mt-1">{{ $tx->person->full_name }}</div>
                                        @endif
                                    @elseif($tx->is_ignored)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded">
                                            Приховано
                                        </span>
                                    @elseif($tx->is_income)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 rounded">
                                            Новий
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded">
                                            Витрата
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if(!$tx->is_processed && !$tx->is_ignored && $tx->is_income)
                                            <button @click="showImport = !showImport" class="px-2 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded">
                                                Імпорт
                                            </button>
                                            <form action="{{ route('finances.monobank.ignore', $tx) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1 text-gray-400 hover:text-gray-600" title="Приховати">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif($tx->is_ignored)
                                            <form action="{{ route('finances.monobank.restore', $tx) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs bg-gray-500 hover:bg-gray-600 text-white rounded">
                                                    Відновити
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            {{-- Import form row --}}
                            <tr x-show="showImport" x-collapse x-cloak>
                                <td colspan="{{ $tab === 'new' ? 8 : 7 }}" class="px-3 py-4 bg-gray-50 dark:bg-gray-800/50">
                                    <form action="{{ route('finances.monobank.import', $tx) }}" method="POST"
                                          x-data="importForm({{ $tx->id }}, '{{ $donationCategory?->id }}')" x-init="loadSuggestions()">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                                                <select name="category_id" x-model="categoryId" required
                                                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Людина
                                                    <span x-show="previousCount > 0" class="text-green-600" x-text="'(' + previousCount + ')'"></span>
                                                </label>
                                                <select name="person_id" x-model="personId"
                                                        class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    <option value="">-- Не вказано --</option>
                                                    @foreach($people as $person)
                                                        <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                                <input type="text" name="description" value="{{ $tx->counterpart_display }}"
                                                       class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            </div>
                                            <div class="flex items-end gap-2">
                                                @if($tx->counterpart_iban)
                                                    <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                        <input type="checkbox" name="save_iban" value="1" x-model="saveIban"
                                                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                                        Зберегти IBAN
                                                    </label>
                                                @endif
                                                <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded">
                                                    Імпортувати
                                                </button>
                                                <button type="button" @click="showImport = false" class="px-3 py-1.5 text-gray-600 hover:text-gray-800 text-sm">
                                                    Скасувати
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $tab === 'new' ? 8 : 7 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p>Транзакцій не знайдено</p>
                                    @if($tab === 'new')
                                        <p class="mt-2 text-sm">Натисніть "Синхронізувати" щоб завантажити нові транзакції</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            {{-- Pagination --}}
            @if($transactions->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
function monobankPage() {
    return {
        selectedIds: [],
        toggleAll(event) {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            if (event.target.checked) {
                this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            } else {
                this.selectedIds = [];
            }
        }
    }
}

function tableFilters() {
    const params = new URLSearchParams(window.location.search);
    return {
        filters: {
            date_from: params.get('date_from') || '',
            date_to: params.get('date_to') || '',
            search: params.get('search') || '',
            mcc_category: params.get('mcc_category') || '',
            amount_min: params.get('amount_min') || '',
            amount_max: params.get('amount_max') || '',
            category_id: params.get('category_id') || '',
        },
        applyFilters() {
            const url = new URL(window.location.href);
            // Keep tab, sort, dir
            const tab = url.searchParams.get('tab') || 'new';
            const sort = url.searchParams.get('sort') || 'mono_time';
            const dir = url.searchParams.get('dir') || 'desc';

            // Clear all params
            url.search = '';

            // Re-add tab, sort, dir
            url.searchParams.set('tab', tab);
            url.searchParams.set('sort', sort);
            url.searchParams.set('dir', dir);

            // Add filters
            Object.entries(this.filters).forEach(([key, value]) => {
                if (value && value.toString().trim() !== '') {
                    url.searchParams.set(key, value);
                }
            });

            window.location.href = url.toString();
        },
        clearFilters() {
            this.filters = {
                date_from: '',
                date_to: '',
                search: '',
                mcc_category: '',
                amount_min: '',
                amount_max: '',
                category_id: '',
            };
            this.applyFilters();
        },
        hasFilters() {
            return Object.values(this.filters).some(v => v && v.toString().trim() !== '');
        }
    }
}

function importForm(txId, defaultCategoryId) {
    return {
        categoryId: defaultCategoryId,
        personId: '',
        saveIban: false,
        previousCount: 0,

        async loadSuggestions() {
            try {
                const response = await fetch(`/finances/monobank/${txId}/suggestions`);
                const data = await response.json();

                if (data.suggested_category_id) {
                    this.categoryId = String(data.suggested_category_id);
                }
                if (data.suggested_person_id) {
                    this.personId = String(data.suggested_person_id);
                }
                this.previousCount = data.previous_transactions || 0;
            } catch (e) {
                console.error('Failed to load suggestions', e);
            }
        }
    }
}
</script>
@endpush
@endsection
