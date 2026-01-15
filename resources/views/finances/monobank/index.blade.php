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
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
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

            {{-- Filters --}}
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <form action="{{ route('finances.monobank.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    {{-- Search --}}
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    {{-- Date from --}}
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                    {{-- Date to --}}
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                    {{-- Amount min --}}
                    <input type="number" name="amount_min" value="{{ request('amount_min') }}" placeholder="Від ₴" step="0.01"
                           class="w-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                    {{-- Amount max --}}
                    <input type="number" name="amount_max" value="{{ request('amount_max') }}" placeholder="До ₴" step="0.01"
                           class="w-24 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                    {{-- Category filter (for imported tab) --}}
                    @if($tab === 'imported')
                        <select name="category_id" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Усі категорії</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    {{-- MCC category filter (for expenses and all tabs) --}}
                    @if(in_array($tab, ['expenses', 'all']))
                        <select name="mcc_category" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Тип витрат</option>
                            @foreach($mccCategories as $key => $label)
                                <option value="{{ $key }}" {{ request('mcc_category') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                        Фільтр
                    </button>

                    @if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max', 'category_id', 'mcc_category']))
                        <a href="{{ route('finances.monobank.index', ['tab' => $tab]) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">
                            Скинути
                        </a>
                    @endif
                </form>
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

            {{-- Transactions list --}}
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($transactions as $tx)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                         x-data="{ showImport: false }">
                        <div class="flex items-start gap-4">
                            {{-- Checkbox for bulk actions --}}
                            @if($tab === 'new')
                                <input type="checkbox" value="{{ $tx->id }}"
                                       x-model.number="selectedIds"
                                       class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            @endif

                            {{-- Amount --}}
                            <div class="flex-shrink-0 w-28 text-right">
                                <span class="text-lg font-semibold {{ $tx->is_income ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->formatted_amount }}
                                </span>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">
                                    {{ $tx->counterpart_display }}
                                </p>
                                @if($tx->comment)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $tx->comment }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $tx->mono_time->format('d.m.Y H:i') }}
                                    @if($tx->counterpart_iban)
                                        &bull; {{ Str::mask($tx->counterpart_iban, '*', 10, -4) }}
                                    @endif
                                </p>

                                {{-- Status badges --}}
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    @if($tx->mcc && !$tx->is_income)
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
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded {{ $colorClass }}">
                                            {{ $tx->mcc_category }}
                                        </span>
                                    @endif
                                    @if($tx->is_processed)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">
                                            Імпортовано
                                            @if($tx->person)
                                                &bull; {{ $tx->person->full_name }}
                                            @endif
                                        </span>
                                    @endif
                                    @if($tx->is_ignored)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded">
                                            Приховано
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex-shrink-0 flex items-center gap-2">
                                @if(!$tx->is_processed && !$tx->is_ignored && $tx->is_income)
                                    <button @click="showImport = !showImport"
                                            class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                        Імпорт
                                    </button>
                                    <form action="{{ route('finances.monobank.ignore', $tx) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-gray-600" title="Приховати">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                @if($tx->is_ignored)
                                    <form action="{{ route('finances.monobank.restore', $tx) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
                                            Відновити
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Import form (expandable) --}}
                        <div x-show="showImport" x-collapse x-cloak class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <form action="{{ route('finances.monobank.import', $tx) }}" method="POST"
                                  x-data="importForm({{ $tx->id }}, '{{ $donationCategory?->id }}')" x-init="loadSuggestions()">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                                        <select name="category_id" x-model="categoryId" required
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Людина
                                            <span x-show="previousCount > 0" class="text-xs text-green-600" x-text="'(' + previousCount + ' попередніх)'"></span>
                                        </label>
                                        <select name="person_id" x-model="personId"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">-- Не вказано --</option>
                                            @foreach($people as $person)
                                                <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                                        <input type="text" name="description" value="{{ $tx->counterpart_display }}"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>

                                @if($tx->counterpart_iban)
                                    <div class="mt-3">
                                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                            <input type="checkbox" name="save_iban" value="1" x-model="saveIban"
                                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            Зберегти IBAN для цієї людини (для автовизначення)
                                        </label>
                                    </div>
                                @endif

                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="showImport = false"
                                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                        Скасувати
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                        Імпортувати
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p>Транзакцій не знайдено</p>
                        @if($tab === 'new')
                            <p class="mt-2 text-sm">Натисніть "Синхронізувати" щоб завантажити нові транзакції</p>
                        @endif
                    </div>
                @endforelse
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
