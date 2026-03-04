@extends('layouts.app')

@section('title', __('app.budget'))

@section('content')
@include('finances.partials.tabs')

<div id="finance-content">
<div class="space-y-6" x-data="budgetsPage()">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.budget') }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('app.church_expenses') }} + {{ __('app.ministry_budgets_section') }}
            </p>
        </div>

        {{-- Period Selector --}}
        <div class="flex items-center gap-2">
            <select x-model="month" x-on:change="updatePeriod()"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @foreach([1 => 'Січень', 2 => 'Лютий', 3 => 'Березень', 4 => 'Квітень', 5 => 'Травень', 6 => 'Червень', 7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень', 10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень'] as $m => $name)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select x-model="year" x-on:change="updatePeriod()"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @for($y = now()->year + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Summary Cards (Grand Total: Church + Ministry) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.planned') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ number_format($grandTotals['planned'], 0, ',', ' ') }} <span class="text-lg text-gray-500">₴</span>
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.actual') }}</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                        {{ number_format($grandTotals['actual'], 0, ',', ' ') }} <span class="text-lg">₴</span>
                    </p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.remaining') }}</p>
                    <p class="text-2xl font-bold {{ $grandTotals['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} mt-1">
                        {{ number_format($grandTotals['difference'], 0, ',', ' ') }} <span class="text-lg">₴</span>
                    </p>
                </div>
                <div class="p-3 {{ $grandTotals['difference'] >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $grandTotals['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.used_percent') }}</p>
                    <p class="text-2xl font-bold {{ $grandTotals['percentage'] > 100 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mt-1">
                        {{ number_format($grandTotals['percentage'], 1) }}%
                    </p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($grandTotals['planned'] > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.used_percent') }}</span>
            <span class="text-sm font-semibold {{ $grandTotals['percentage'] > 100 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                {{ number_format($grandTotals['percentage'], 1) }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            @php
                $pctBar = min(100, $grandTotals['percentage']);
                $barColor = $grandTotals['percentage'] > 100 ? 'bg-red-600' : ($grandTotals['percentage'] > 80 ? 'bg-orange-500' : 'bg-green-500');
            @endphp
            <div class="{{ $barColor }} h-3 rounded-full transition-all" style="width: {{ $pctBar }}%"></div>
        </div>
    </div>
    @endif

    {{-- ═══ Church Budget Section ═══ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.church_expenses') }}</h2>
            @if(!$churchBudget && auth()->user()->canEdit('finances'))
                <button x-on:click="createChurchBudget()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    {{ __('app.create_budget') }}
                </button>
            @endif
        </div>

        @if($churchBudget)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('app.budget_item_name') }}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">
                            {{ __('app.budget_item_type') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('app.planned') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('app.actual') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('app.difference') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('app.annual_total') }}
                        </th>
                        @if(auth()->user()->canEdit('finances'))
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                            Дії
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($churchBudgetItems as $cbi)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $cbi['name'] }}</div>
                            @if($cbi['category'])
                                <div class="text-xs text-gray-500">{{ $cbi['category']->icon_emoji ?? '' }} {{ $cbi['category']->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($cbi['is_recurring'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    🔄 {{ __('app.monthly') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                    ☀️ {{ __('app.one_time') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap text-gray-700 dark:text-gray-300">
                            {{ $cbi['planned'] > 0 ? number_format($cbi['planned'], 0, ',', ' ') . ' ₴' : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($cbi['category_id'])
                                <button x-on:click="showChurchTransactions({{ $cbi['id'] }}, '{{ addslashes($cbi['name']) }}')"
                                        class="text-red-600 dark:text-red-400 hover:underline">
                                    {{ $cbi['actual'] > 0 ? number_format($cbi['actual'], 0, ',', ' ') . ' ₴' : '—' }}
                                </button>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium {{ $cbi['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            @if($cbi['planned'] > 0)
                                {{ $cbi['difference'] >= 0 ? '+' : '' }}{{ number_format($cbi['difference'], 0, ',', ' ') }} ₴
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ number_format($cbi['annual_planned'], 0, ',', ' ') }} ₴
                        </td>
                        @if(auth()->user()->canEdit('finances'))
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button x-on:click="openChurchItemModal('edit', {{ json_encode([
                                    'id' => $cbi['id'],
                                    'name' => $cbi['name'],
                                    'category_id' => $cbi['category_id'],
                                    'is_recurring' => $cbi['is_recurring'],
                                    'amounts' => $cbi['amounts'],
                                    'notes' => $cbi['notes'] ?? '',
                                ]) }})"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="{{ __('ui.edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button x-on:click="deleteChurchItem({{ $cbi['id'] }}, '{{ addslashes($cbi['name']) }}')"
                                        class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="{{ __('ui.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canEdit('finances') ? 7 : 6 }}" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                            {{ __('app.add_budget_item') }}
                        </td>
                    </tr>
                    @endforelse

                    {{-- Church budget totals row --}}
                    @if(count($churchBudgetItems) > 0)
                    <tr class="bg-gray-100 dark:bg-gray-700/50 font-semibold">
                        <td class="px-6 py-3 text-gray-900 dark:text-white" colspan="2">Всього</td>
                        <td class="px-6 py-3 text-right text-gray-900 dark:text-white">{{ number_format($churchBudgetTotals['planned'], 0, ',', ' ') }} ₴</td>
                        <td class="px-6 py-3 text-right text-red-600 dark:text-red-400">{{ number_format($churchBudgetTotals['actual'], 0, ',', ' ') }} ₴</td>
                        <td class="px-6 py-3 text-right {{ $churchBudgetTotals['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $churchBudgetTotals['difference'] >= 0 ? '+' : '' }}{{ number_format($churchBudgetTotals['difference'], 0, ',', ' ') }} ₴
                        </td>
                        <td class="px-6 py-3 text-right text-gray-500 dark:text-gray-400">{{ number_format($churchBudgetTotals['annual_planned'], 0, ',', ' ') }} ₴</td>
                        @if(auth()->user()->canEdit('finances'))
                        <td class="px-6 py-3"></td>
                        @endif
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Add church budget item button --}}
        @if(auth()->user()->canEdit('finances'))
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <button x-on:click="openChurchItemModal('create')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('app.add_budget_item') }}
            </button>
        </div>
        @endif
        @else
        <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
            {{ __('app.no_church_budget') }}
        </div>
        @endif
    </div>

    {{-- ═══ Ministry Budgets Section ═══ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.ministry_budgets_section') }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Команда
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Виділено
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Бюджет
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Витрачено
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Залишок
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                            Прогрес
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                            Дії
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($ministries as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $item['has_items'] ? 'cursor-pointer' : '' }}"
                        @if($item['has_items']) @click="toggleExpand({{ $item['ministry']->id }})" @endif>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($item['has_items'])
                                <svg class="w-4 h-4 mr-2 text-gray-400 transition-transform duration-200"
                                     :class="{ 'rotate-90': expandedMinistries.includes({{ $item['ministry']->id }}) }"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                @else
                                <div class="w-4 mr-2"></div>
                                @endif
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $item['ministry']->color ?? '#6b7280' }}"></div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $item['ministry']->name }}</div>
                                    @if($item['ministry']->leader)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['ministry']->leader->full_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            @if($item['allocated'] > 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">
                                {{ number_format($item['allocated'], 0, ',', ' ') }} ₴
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ number_format($item['monthly_budget'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="text-red-600 dark:text-red-400">
                                {{ number_format($item['spent'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <span class="{{ $item['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($item['remaining'], 0, ',', ' ') }} ₴
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item['monthly_budget'] > 0)
                                @php
                                    $pct = min(100, $item['percentage']);
                                    $barColor = $item['percentage'] > 100 ? 'bg-red-600' : ($item['percentage'] > 80 ? 'bg-orange-500' : 'bg-green-500');
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs {{ $item['percentage'] > 100 ? 'text-red-600' : 'text-gray-500' }} w-12 text-right">{{ number_format($item['percentage'], 0) }}%</span>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">Не задано</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center" @click.stop>
                            @if(auth()->user()->canEdit('finances'))
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openAllocateModal({{ $item['ministry']->id }}, '{{ addslashes($item['ministry']->name) }}')"
                                        class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="Виділити бюджет">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                <button @click="openBudgetModal({{ $item['ministry']->id }}, '{{ addslashes($item['ministry']->name) }}', {{ $item['budget']?->monthly_budget ?? $item['ministry']->monthly_budget ?? 0 }}, '{{ addslashes($item['budget']?->notes ?? '') }}', {{ $item['has_items'] ? 'true' : 'false' }})"
                                        class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="Редагувати бюджет">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>

                    {{-- Expandable Budget Items Sub-table --}}
                    @if($item['has_items'])
                    <tr x-show="expandedMinistries.includes({{ $item['ministry']->id }})"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-cloak>
                        <td colspan="7" class="px-0 py-0 bg-gray-50 dark:bg-gray-800/80">
                            <div class="px-8 py-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                                <th class="px-3 py-2 text-left">Стаття витрат</th>
                                                <th class="px-3 py-2 text-right">План</th>
                                                <th class="px-3 py-2 text-right">Факт</th>
                                                <th class="px-3 py-2 text-right">Різниця</th>
                                                <th class="px-3 py-2 text-left">Відповідальні</th>
                                                <th class="px-3 py-2 text-center">Чеки</th>
                                                <th class="px-3 py-2 text-center w-20">Дії</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($item['items'] as $bi)
                                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/30">
                                                <td class="px-3 py-2.5">
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $bi['name'] }}</div>
                                                    @if($bi['category'])
                                                        <div class="text-xs text-gray-500">{{ $bi['category']->icon ?? '' }} {{ $bi['category']->name }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 text-right whitespace-nowrap text-gray-700 dark:text-gray-300">
                                                    {{ number_format($bi['planned_amount'], 0, ',', ' ') }} ₴
                                                </td>
                                                <td class="px-3 py-2.5 text-right whitespace-nowrap text-red-600 dark:text-red-400">
                                                    {{ number_format($bi['actual'], 0, ',', ' ') }} ₴
                                                </td>
                                                <td class="px-3 py-2.5 text-right whitespace-nowrap font-medium {{ $bi['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $bi['difference'] >= 0 ? '+' : '' }}{{ number_format($bi['difference'], 0, ',', ' ') }} ₴
                                                </td>
                                                <td class="px-3 py-2.5">
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($bi['responsible'] as $person)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                            {{ $person->short_name ?? $person->first_name }}
                                                        </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2.5 text-center">
                                                    <button @click.stop="showTransactions({{ $bi['id'] }}, '{{ addslashes($bi['name']) }}')"
                                                            class="inline-flex items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <span>Деталі</span>
                                                    </button>
                                                </td>
                                                <td class="px-3 py-2.5 text-center">
                                                    @if(auth()->user()->canEdit('finances'))
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button @click.stop="openItemModal('edit', {{ json_encode([
                                                            'id' => $bi['id'],
                                                            'name' => $bi['name'],
                                                            'planned_amount' => $bi['planned_amount'],
                                                            'category_id' => $bi['category_id'],
                                                            'notes' => $bi['notes'] ?? '',
                                                            'person_ids' => $bi['responsible']->pluck('id')->toArray(),
                                                        ]) }}, {{ $item['budget']->id }})"
                                                                class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Редагувати">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click.stop="deleteItem({{ $bi['id'] }}, '{{ addslashes($bi['name']) }}')"
                                                                class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                                                title="Видалити">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach

                                            {{-- Unmatched spending row --}}
                                            @if($item['unmatched_spent'] > 0)
                                            <tr class="bg-orange-50/50 dark:bg-orange-900/10">
                                                <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 italic">Інші витрати</td>
                                                <td class="px-3 py-2.5 text-right text-gray-400">-</td>
                                                <td class="px-3 py-2.5 text-right text-red-600 dark:text-red-400">{{ number_format($item['unmatched_spent'], 0, ',', ' ') }} ₴</td>
                                                <td class="px-3 py-2.5"></td>
                                                <td class="px-3 py-2.5"></td>
                                                <td class="px-3 py-2.5"></td>
                                                <td class="px-3 py-2.5"></td>
                                            </tr>
                                            @endif

                                            {{-- Totals row --}}
                                            <tr class="bg-gray-100 dark:bg-gray-700/50 font-semibold">
                                                <td class="px-3 py-2.5 text-gray-900 dark:text-white">Всього</td>
                                                <td class="px-3 py-2.5 text-right text-gray-900 dark:text-white">{{ number_format(collect($item['items'])->sum('planned_amount'), 0, ',', ' ') }} ₴</td>
                                                <td class="px-3 py-2.5 text-right text-red-600 dark:text-red-400">{{ number_format($item['spent'], 0, ',', ' ') }} ₴</td>
                                                <td class="px-3 py-2.5 text-right {{ $item['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ $item['remaining'] >= 0 ? '+' : '' }}{{ number_format($item['remaining'], 0, ',', ' ') }} ₴
                                                </td>
                                                <td colspan="3" class="px-3 py-2.5"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Add item button --}}
                                @if(auth()->user()->canEdit('finances'))
                                <div class="mt-3">
                                    <button @click.stop="openItemModal('create', null, {{ $item['budget']->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Додати статтю
                                    </button>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif

                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            Немає команд
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Expenses Missing Receipts --}}
    @if($expensesMissingReceipts->count() > 0)
    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-orange-200 dark:border-orange-800 flex items-center">
            <svg class="w-5 h-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <h2 class="text-lg font-semibold text-orange-800 dark:text-orange-200">Витрати без чеків</h2>
        </div>

        <div class="divide-y divide-orange-200 dark:divide-orange-800">
            @foreach($expensesMissingReceipts as $expense)
            <div class="px-6 py-3 flex items-center justify-between">
                <div>
                    <div class="font-medium text-orange-900 dark:text-orange-100">{{ $expense->description }}</div>
                    <div class="text-sm text-orange-600 dark:text-orange-400">
                        {{ $expense->ministry?->name ?? 'Без команди' }} &bull; {{ $expense->date->format('d.m.Y') }}
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="font-semibold text-orange-900 dark:text-orange-100">
                        {{ number_format($expense->amount, 0, ',', ' ') }} ₴
                    </span>
                    <button type="button" onclick="window.openExpenseEdit && window.openExpenseEdit({{ $expense->id }})"
                       class="px-3 py-1 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors">
                        Додати чек
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Budget Edit Modal --}}
    <div x-show="showBudgetModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showBudgetModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Бюджет: <span x-text="budgetMinistryName"></span>
                </h3>

                <form @submit.prevent="saveBudget()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Місячний бюджет (₴)
                        </label>
                        <input type="number" name="monthly_budget" x-model="budgetAmount" min="0" step="100"
                               :disabled="budgetHasItems"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <p x-show="budgetHasItems" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            Бюджет розраховується як сума статей витрат
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Нотатки
                        </label>
                        <textarea name="notes" x-model="budgetNotes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showBudgetModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="budgetSaving || budgetHasItems"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!budgetSaving">Зберегти</span>
                            <span x-show="budgetSaving">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Allocate Budget Modal --}}
    <div x-show="showAllocateModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showAllocateModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Виділити бюджет: <span x-text="allocateMinistryName"></span>
                </h3>

                <form @submit.prevent="submitAllocation()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Сума *
                        </label>
                        <div class="flex gap-2">
                            <input type="number" x-model="allocateForm.amount" required min="0.01" step="0.01"
                                   placeholder="0.00"
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                            <select x-model="allocateForm.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                                @foreach($enabledCurrencies as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Спосіб оплати
                        </label>
                        <select x-model="allocateForm.payment_method"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                            <option value="">Не вказано</option>
                            <option value="cash">Готівка</option>
                            <option value="card">Картка</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Дата *
                        </label>
                        <input type="date" x-model="allocateForm.date" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Нотатки
                        </label>
                        <textarea x-model="allocateForm.notes" rows="2" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAllocateModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="allocateSaving"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!allocateSaving">Виділити</span>
                            <span x-show="allocateSaving">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Budget Item Create/Edit Modal --}}
    <div x-show="showItemModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showItemModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="itemModalTitle"></h3>

                <form @submit.prevent="saveItem()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                        <input type="text" x-model="itemForm.name" required maxlength="255"
                               placeholder="Наприклад: Оренда, Перекуси, Матеріали..."
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Запланована сума (₴) *</label>
                        <input type="number" x-model="itemForm.planned_amount" required min="0" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія витрат</label>
                        <select x-model="itemForm.category_id"
                                :class="{ 'hidden': itemForm.category_id === '__custom__' }"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без категорії (ручна прив'язка)</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                            <option value="__custom__">Інше (ввести вручну)...</option>
                        </select>
                        <div x-show="itemForm.category_id === '__custom__'" class="flex gap-2">
                            <input type="text" x-model="itemForm.category_name" placeholder="Назва категорії..."
                                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <button type="button" @click="itemForm.category_id = ''; itemForm.category_name = ''"
                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-lg">✕</button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Вибір категорії дозволяє автоматично збирати витрати</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відповідальні</label>
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 max-h-40 overflow-y-auto p-2 space-y-1">
                            <template x-for="person in ministryMembers" :key="person.id">
                                <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                    <input type="checkbox" :value="person.id"
                                           x-model="itemForm.person_ids"
                                           class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="person.name"></span>
                                </label>
                            </template>
                            <p x-show="ministryMembers.length === 0" class="text-xs text-gray-400 py-2 text-center">Завантаження учасників...</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                        <textarea x-model="itemForm.notes" rows="2" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showItemModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="itemSaving"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!itemSaving" x-text="itemModalMode === 'create' ? 'Додати' : 'Зберегти'"></span>
                            <span x-show="itemSaving">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Church Budget Item Create/Edit Modal --}}
    <div x-show="showChurchItemModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" x-on:click="showChurchItemModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-on:click.stop>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    x-text="churchItemMode === 'create' ? '{{ __('app.add_budget_item') }}' : '{{ __('app.edit_budget_item') }}'"></h3>

                <form x-on:submit.prevent="saveChurchItem()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.budget_item_name') }} *</label>
                        <input type="text" x-model="churchItemForm.name" required maxlength="255"
                               placeholder="{{ __('app.budget_item_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="churchItemForm.category_id"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Без категорії</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon_emoji ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Категорія — {{ __('app.actual') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.budget_item_type') }} *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="churchItemForm.is_recurring" value="1"
                                       class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">🔄 {{ __('app.monthly') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="churchItemForm.is_recurring" value="0"
                                       class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">☀️ {{ __('app.one_time') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Recurring: amount per month --}}
                    <div x-show="churchItemForm.is_recurring == '1'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.amount_per_month') }} (₴) *</label>
                        <input type="number" x-model="churchItemForm.amount" min="0" step="1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>

                    {{-- One-time: month + amount --}}
                    <div x-show="churchItemForm.is_recurring == '0'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.select_month') }} *</label>
                            <select x-model="churchItemForm.one_time_month"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                @foreach([1 => 'Січень', 2 => 'Лютий', 3 => 'Березень', 4 => 'Квітень', 5 => 'Травень', 6 => 'Червень', 7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень', 10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень'] as $m => $name)
                                    <option value="{{ $m }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума (₴) *</label>
                            <input type="number" x-model="churchItemForm.one_time_amount" min="0" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Примітки</label>
                        <textarea x-model="churchItemForm.notes" rows="2" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" x-on:click="showChurchItemModal = false"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            {{ __('ui.cancel') }}
                        </button>
                        <button type="submit" :disabled="churchItemSaving"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                            <span x-show="!churchItemSaving" x-text="churchItemMode === 'create' ? '{{ __('ui.add') }}' : '{{ __('ui.save') }}'"></span>
                            <span x-show="churchItemSaving">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Church Budget Transactions Modal --}}
    <div x-show="showChurchTransModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" x-on:click="showChurchTransModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-on:click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('app.matched_transactions') }}: <span x-text="churchTransItemName"></span>
                    </h3>
                    <button x-on:click="showChurchTransModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div x-show="churchTransLoading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>

                    <div x-show="!churchTransLoading && churchTransList.length === 0" class="text-center py-8 text-gray-500">
                        {{ __('app.no_matched_transactions') }}
                    </div>

                    <div x-show="!churchTransLoading && churchTransList.length > 0" class="space-y-3">
                        <template x-for="tx in churchTransList" :key="tx.id">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="tx.description || '{{ __('common.no_description') }}'"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="tx.date"></span>
                                            <template x-if="tx.category">
                                                <span> &bull; <span x-text="tx.category"></span></span>
                                            </template>
                                            <template x-if="tx.payment_method">
                                                <span> &bull; <span x-text="tx.payment_method"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-red-600 dark:text-red-400" x-text="formatMoney(tx.amount) + ' ₴'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Detail Modal --}}
    <div x-show="showTransactionsModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showTransactionsModal = false"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Транзакції: <span x-text="transactionsItemName"></span>
                    </h3>
                    <button @click="showTransactionsModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div x-show="transactionsLoading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>

                    <div x-show="!transactionsLoading && transactionsList.length === 0" class="text-center py-8 text-gray-500">
                        Немає транзакцій
                    </div>

                    <div x-show="!transactionsLoading && transactionsList.length > 0" class="space-y-3">
                        <template x-for="tx in transactionsList" :key="tx.id">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="tx.description || 'Без опису'"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="tx.date"></span>
                                            <template x-if="tx.category">
                                                <span> &bull; <span x-text="tx.category"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-red-600 dark:text-red-400" x-text="formatMoney(tx.amount) + ' ₴'"></div>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <template x-if="tx.attachments && tx.attachments.length > 0">
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="att in tx.attachments" :key="att.id">
                                            <a :href="att.url" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                <template x-if="att.is_image">
                                                    <img :src="att.url" class="w-8 h-8 object-cover rounded">
                                                </template>
                                                <template x-if="!att.is_image">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                </template>
                                                <span class="text-gray-600 dark:text-gray-400" x-text="att.name"></span>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function budgetsPage() {
    return {
        year: {{ $year }},
        month: {{ $month }},

        // Expanded ministries
        expandedMinistries: [],

        // Budget modal
        showBudgetModal: false,
        budgetMinistryId: null,
        budgetMinistryName: '',
        budgetAmount: 0,
        budgetNotes: '',
        budgetSaving: false,
        budgetHasItems: false,

        // Allocate modal
        showAllocateModal: false,
        allocateMinistryId: null,
        allocateMinistryName: '',
        allocateSaving: false,
        allocateForm: {
            amount: '',
            currency: 'UAH',
            payment_method: '',
            date: new Date().toISOString().split('T')[0],
            notes: '',
        },

        // Budget item modal
        showItemModal: false,
        itemModalMode: 'create',
        itemModalTitle: '',
        itemBudgetId: null,
        itemEditId: null,
        itemSaving: false,
        itemForm: {
            name: '',
            planned_amount: '',
            category_id: '',
            category_name: '',
            notes: '',
            person_ids: [],
        },
        ministryMembers: [],

        // Transactions modal
        showTransactionsModal: false,
        transactionsItemName: '',
        transactionsList: [],
        transactionsLoading: false,

        // Ministry members cache
        membersByBudget: {},

        // Church budget
        churchBudgetId: {{ $churchBudget?->id ?? 'null' }},
        showChurchItemModal: false,
        churchItemMode: 'create',
        churchItemEditId: null,
        churchItemSaving: false,
        churchItemForm: {
            name: '',
            category_id: '',
            is_recurring: '1',
            amount: '',
            one_time_month: {{ $month }},
            one_time_amount: '',
            notes: '',
        },

        // Church budget transactions modal
        showChurchTransModal: false,
        churchTransItemName: '',
        churchTransList: [],
        churchTransLoading: false,

        updatePeriod() {
            window.location.href = `{{ route('finances.budgets') }}?year=${this.year}&month=${this.month}`;
        },

        toggleExpand(ministryId) {
            const idx = this.expandedMinistries.indexOf(ministryId);
            if (idx === -1) {
                this.expandedMinistries.push(ministryId);
            } else {
                this.expandedMinistries.splice(idx, 1);
            }
        },

        openBudgetModal(ministryId, ministryName, amount, notes, hasItems) {
            this.budgetMinistryId = ministryId;
            this.budgetMinistryName = ministryName;
            this.budgetAmount = amount;
            this.budgetNotes = notes;
            this.budgetHasItems = hasItems;
            this.showBudgetModal = true;
        },

        openAllocateModal(ministryId, ministryName) {
            this.allocateMinistryId = ministryId;
            this.allocateMinistryName = ministryName;
            this.allocateForm = {
                amount: '',
                currency: 'UAH',
                payment_method: '',
                date: new Date().toISOString().split('T')[0],
                notes: '',
            };
            this.showAllocateModal = true;
        },

        async submitAllocation() {
            this.allocateSaving = true;
            try {
                const res = await fetch('/finances/budgets/' + this.allocateMinistryId + '/allocate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: this.allocateForm.amount,
                        currency: this.allocateForm.currency,
                        payment_method: this.allocateForm.payment_method || null,
                        date: this.allocateForm.date,
                        year: this.year,
                        month: this.month,
                        notes: this.allocateForm.notes || null,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showAllocateModal = false;
                    showToast('success', data.message || 'Бюджет виділено');
                    setTimeout(() => location.reload(), 600);
                } else if (res.status === 422 && data.errors) {
                    const msgs = Object.values(data.errors).flat();
                    showToast('error', msgs[0] || 'Помилка валідації');
                } else {
                    showToast('error', data.message || 'Помилка виділення бюджету');
                }
            } catch (e) {
                showToast('error', 'Помилка виділення бюджету');
            } finally {
                this.allocateSaving = false;
            }
        },

        async saveBudget() {
            if (this.budgetHasItems) return;
            this.budgetSaving = true;
            try {
                const res = await fetch('/finances/budgets/' + this.budgetMinistryId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        monthly_budget: this.budgetAmount,
                        year: this.year,
                        month: this.month,
                        notes: this.budgetNotes,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showBudgetModal = false;
                    showToast('success', data.message || 'Збережено');
                    setTimeout(() => location.reload(), 600);
                } else if (res.status === 422 && data.errors) {
                    const msgs = Object.values(data.errors).flat();
                    showToast('error', msgs[0] || 'Помилка валідації');
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка збереження');
            } finally {
                this.budgetSaving = false;
            }
        },

        async openItemModal(mode, itemData, budgetId) {
            this.itemModalMode = mode;
            this.itemBudgetId = budgetId;
            this.itemEditId = mode === 'edit' ? itemData.id : null;
            this.itemModalTitle = mode === 'create' ? 'Нова стаття бюджету' : 'Редагувати статтю';

            if (mode === 'edit' && itemData) {
                this.itemForm = {
                    name: itemData.name,
                    planned_amount: itemData.planned_amount,
                    category_id: itemData.category_id || '',
                    category_name: '',
                    notes: itemData.notes || '',
                    person_ids: (itemData.person_ids || []).map(String),
                };
            } else {
                this.itemForm = {
                    name: '',
                    planned_amount: '',
                    category_id: '',
                    category_name: '',
                    notes: '',
                    person_ids: [],
                };
            }

            // Load ministry members
            await this.loadMinistryMembers(budgetId);

            this.showItemModal = true;
        },

        async loadMinistryMembers(budgetId) {
            if (this.membersByBudget[budgetId]) {
                this.ministryMembers = this.membersByBudget[budgetId];
                return;
            }

            // Get ministry_id from the budget data in the page
            // We fetch members from the ministry's people
            try {
                // Find ministry id from budgetId using page data
                const ministryData = @json($ministries->map(fn($m) => [
                    'budget_id' => $m['budget']?->id,
                    'ministry_id' => $m['ministry']->id,
                ])->values());

                const entry = ministryData.find(m => m.budget_id == budgetId);
                if (!entry) {
                    this.ministryMembers = [];
                    return;
                }

                const res = await fetch(`/ministries/${entry.ministry_id}/members-json`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (res.ok) {
                    const data = await res.json();
                    this.ministryMembers = data.members || [];
                    this.membersByBudget[budgetId] = this.ministryMembers;
                } else {
                    this.ministryMembers = [];
                }
            } catch (e) {
                this.ministryMembers = [];
            }
        },

        async saveItem() {
            this.itemSaving = true;
            try {
                const url = this.itemModalMode === 'create'
                    ? `/finances/budgets/${this.itemBudgetId}/items`
                    : `/finances/budgets/items/${this.itemEditId}`;
                const method = this.itemModalMode === 'create' ? 'POST' : 'PUT';

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.itemForm.name,
                        planned_amount: this.itemForm.planned_amount,
                        category_id: (this.itemForm.category_id && this.itemForm.category_id !== '__custom__') ? this.itemForm.category_id : null,
                        category_name: (this.itemForm.category_id === '__custom__' && this.itemForm.category_name) ? this.itemForm.category_name : null,
                        notes: this.itemForm.notes || null,
                        person_ids: this.itemForm.person_ids.map(Number),
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showItemModal = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 600);
                } else if (res.status === 422) {
                    const msgs = data.errors ? Object.values(data.errors).flat() : [data.message];
                    showToast('error', msgs[0] || 'Помилка валідації');
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка збереження');
            } finally {
                this.itemSaving = false;
            }
        },

        async deleteItem(itemId, itemName) {
            if (!confirm(`Видалити статтю "${itemName}"?`)) return;

            try {
                const res = await fetch(`/finances/budgets/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast('error', data.message || 'Помилка видалення');
                }
            } catch (e) {
                showToast('error', 'Помилка видалення');
            }
        },

        async showTransactions(itemId, itemName) {
            this.transactionsItemName = itemName;
            this.transactionsList = [];
            this.transactionsLoading = true;
            this.showTransactionsModal = true;

            try {
                const res = await fetch(`/finances/budgets/items/${itemId}/transactions`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.transactionsList = data.transactions;
                }
            } catch (e) {
                showToast('error', 'Помилка завантаження транзакцій');
            } finally {
                this.transactionsLoading = false;
            }
        },

        // ═══ Church Budget Methods ═══

        async createChurchBudget() {
            try {
                const res = await fetch('/finances/church-budgets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ year: this.year }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast('error', data.message || 'Помилка');
                }
            } catch (e) {
                showToast('error', 'Помилка створення бюджету');
            }
        },

        openChurchItemModal(mode, itemData) {
            this.churchItemMode = mode;
            this.churchItemEditId = mode === 'edit' ? itemData.id : null;

            if (mode === 'edit' && itemData) {
                const amounts = itemData.amounts || {};
                const monthKeys = Object.keys(amounts);
                const isRecurring = itemData.is_recurring;

                this.churchItemForm = {
                    name: itemData.name,
                    category_id: itemData.category_id || '',
                    is_recurring: isRecurring ? '1' : '0',
                    amount: isRecurring && monthKeys.length > 0 ? amounts[monthKeys[0]] : '',
                    one_time_month: !isRecurring && monthKeys.length > 0 ? monthKeys[0] : this.month,
                    one_time_amount: !isRecurring && monthKeys.length > 0 ? amounts[monthKeys[0]] : '',
                    notes: itemData.notes || '',
                };
            } else {
                this.churchItemForm = {
                    name: '',
                    category_id: '',
                    is_recurring: '1',
                    amount: '',
                    one_time_month: this.month,
                    one_time_amount: '',
                    notes: '',
                };
            }

            this.showChurchItemModal = true;
        },

        async saveChurchItem() {
            this.churchItemSaving = true;
            try {
                const isRecurring = this.churchItemForm.is_recurring == '1';
                const url = this.churchItemMode === 'create'
                    ? `/finances/church-budgets/${this.churchBudgetId}/items`
                    : `/finances/church-budget-items/${this.churchItemEditId}`;
                const method = this.churchItemMode === 'create' ? 'POST' : 'PUT';

                const body = {
                    name: this.churchItemForm.name,
                    category_id: this.churchItemForm.category_id || null,
                    is_recurring: isRecurring,
                    notes: this.churchItemForm.notes || null,
                };

                if (isRecurring) {
                    body.amount = this.churchItemForm.amount;
                } else {
                    body.one_time_month = this.churchItemForm.one_time_month;
                    body.one_time_amount = this.churchItemForm.one_time_amount;
                }

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showChurchItemModal = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 600);
                } else if (res.status === 422) {
                    const msgs = data.errors ? Object.values(data.errors).flat() : [data.message];
                    showToast('error', msgs[0] || 'Помилка валідації');
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка збереження');
            } finally {
                this.churchItemSaving = false;
            }
        },

        async deleteChurchItem(itemId, itemName) {
            if (!confirm(`Видалити статтю "${itemName}"?`)) return;

            try {
                const res = await fetch(`/finances/church-budget-items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast('error', data.message || 'Помилка видалення');
                }
            } catch (e) {
                showToast('error', 'Помилка видалення');
            }
        },

        async showChurchTransactions(itemId, itemName) {
            this.churchTransItemName = itemName;
            this.churchTransList = [];
            this.churchTransLoading = true;
            this.showChurchTransModal = true;

            try {
                const res = await fetch(`/finances/church-budget-items/${itemId}/transactions?month=${this.month}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok) {
                    this.churchTransList = data.transactions || [];
                }
            } catch (e) {
                showToast('error', 'Помилка завантаження транзакцій');
            } finally {
                this.churchTransLoading = false;
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('uk-UA', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
        }
    }
}
</script>
</div><!-- /finance-content -->

<!-- Script must be defined BEFORE Alpine component that uses it -->
<script>
window.expenseEditModal = function() {
    return {
        modalOpen: false,
        loading: false,
        loadingData: false,
        editId: null,
        existingAttachments: [],
        deleteAttachments: [],
        selectedFiles: [],
        formData: {
            amount: '',
            currency: 'UAH',
            description: '',
            category_id: '',
            category_name: '',
            ministry_id: '',
            date: ''
        },
        init() {
            window.openExpenseEdit = (id) => this.openEdit(id);
        },
        async openEdit(id) {
            this.editId = id;
            this.loadingData = true;
            this.modalOpen = true;
            this.existingAttachments = [];
            this.deleteAttachments = [];
            this.selectedFiles = [];

            try {
                const response = await fetch(`/finances/expenses/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json().catch(() => ({}));
                const t = data.transaction;

                this.formData = {
                    amount: t.amount,
                    currency: t.currency || 'UAH',
                    description: t.description || '',
                    category_id: t.category_id || '',
                    category_name: '',
                    ministry_id: t.ministry_id || '',
                    date: t.date.substring(0, 10)
                };
                this.existingAttachments = t.attachments || [];
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            } catch (e) {
                showToast('error', 'Помилка завантаження');
                this.modalOpen = false;
            } finally {
                this.loadingData = false;
            }
        },
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            const maxSize = 10 * 1024 * 1024; // 10 MB
            const rejected = [];
            const accepted = [];
            for (const file of files) {
                if (accepted.length >= 10) break;
                if (file.size > maxSize) {
                    rejected.push(file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' МБ)');
                    continue;
                }
                accepted.push(file);
            }
            this.selectedFiles = accepted;
            if (rejected.length) {
                showToast('error', 'Файл занадто великий (макс. 10 МБ): ' + rejected.join(', '));
            }
        },
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
        toggleDeleteAttachment(id) {
            const idx = this.deleteAttachments.indexOf(id);
            if (idx === -1) {
                this.deleteAttachments.push(id);
            } else {
                this.deleteAttachments.splice(idx, 1);
            }
        },
        async submit() {
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                formData.append('amount', this.formData.amount);
                formData.append('currency', this.formData.currency);
                formData.append('description', this.formData.description);
                if (this.formData.category_id && this.formData.category_id !== '__custom__') formData.append('category_id', this.formData.category_id);
                if (this.formData.category_id === '__custom__' && this.formData.category_name) formData.append('category_name', this.formData.category_name);
                formData.append('ministry_id', this.formData.ministry_id || '');
                formData.append('date', this.formData.date);

                this.selectedFiles.forEach(file => {
                    formData.append('receipts[]', file);
                });

                this.deleteAttachments.forEach(id => {
                    formData.append('delete_attachments[]', id);
                });

                const response = await fetch(`/finances/expenses/${this.editId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                if (response.status === 413) {
                    showToast('error', 'Файл занадто великий для завантаження. Максимум 10 МБ на файл.');
                    return;
                }

                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    this.modalOpen = false;
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 500);
                } else if (response.status === 422) {
                    const errorMsgs = Object.values(data.errors || {}).flat();
                    showToast('error', errorMsgs.length ? errorMsgs[0] : (data.message || 'Помилка валідації'));
                } else {
                    showToast('error', data.message || 'Помилка збереження');
                }
            } catch (e) {
                showToast('error', 'Помилка збереження. Перевірте розмір файлів (макс. 10 МБ).');
            } finally {
                this.loading = false;
            }
        }
    };
};
</script>

<!-- Expense Edit Modal -->
<div x-data="expenseEditModal()" x-cloak>
    <div x-show="modalOpen"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg relative"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Редагувати витрату</h3>
                    <button @click="modalOpen = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Loading -->
                <div x-show="loadingData" class="p-8 text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <form x-show="!loadingData" @submit.prevent="submit()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума *</label>
                        <div class="flex gap-2">
                            <input type="number" x-model="formData.amount" step="0.01" min="0.01" required
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <select x-model="formData.currency"
                                    class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($enabledCurrencies ?? ['UAH'] as $curr)
                                    <option value="{{ $curr }}">{{ $curr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис *</label>
                        <input type="text" x-model="formData.description" required maxlength="255"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата *</label>
                        <input type="date" x-model="formData.date" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                        <select x-model="formData.category_id"
                                :class="{ 'hidden': formData.category_id === '__custom__' }"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без категорії</option>
                            @foreach($expenseCategories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->icon ?? '💸' }} {{ $cat->name }}</option>
                            @endforeach
                            <option value="__custom__">Інше (ввести вручну)...</option>
                        </select>
                        <div x-show="formData.category_id === '__custom__'" class="flex gap-2">
                            <input type="text" x-model="formData.category_name" placeholder="Назва категорії..."
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <button type="button" @click="formData.category_id = ''; formData.category_name = ''"
                                    class="px-3 py-2 text-gray-500 hover:text-red-500 border border-gray-300 dark:border-gray-600 rounded-xl">✕</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Команда</label>
                        <select x-model="formData.ministry_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Без команди</option>
                            @foreach($ministries as $m)
                                <option value="{{ $m['ministry']->id }}">{{ $m['ministry']->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Existing Attachments -->
                    <div x-show="existingAttachments.length > 0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Прикріплені чеки</label>
                        <div class="space-y-2">
                            <template x-for="att in existingAttachments" :key="att.id">
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                     :class="{ 'opacity-50 line-through': deleteAttachments.includes(att.id) }">
                                    <div class="flex items-center gap-2">
                                        <template x-if="att.is_image">
                                            <img :src="att.url" class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity" @click="$dispatch('open-lightbox', att.url)">
                                        </template>
                                        <template x-if="!att.is_image">
                                            <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </template>
                                        <div>
                                            <template x-if="att.is_image">
                                                <a href="#" @click.prevent="$dispatch('open-lightbox', att.url)" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                            </template>
                                            <template x-if="!att.is_image">
                                                <a :href="att.url" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline" x-text="att.original_name"></a>
                                            </template>
                                            <p class="text-xs text-gray-500" x-text="att.formatted_size"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="toggleDeleteAttachment(att.id)"
                                            class="p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                                            :class="{ 'bg-red-100 dark:bg-red-900/30': deleteAttachments.includes(att.id) }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Додати чеки</label>
                        <input type="file" x-ref="fileInput" @change="handleFileSelect" multiple accept="image/*,.heic,.heif,.pdf"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-900/30 dark:file:text-primary-300">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Максимум 10 файлів по 10 МБ (JPG, PNG, HEIC, PDF)</p>
                        <!-- Selected files preview -->
                        <div x-show="selectedFiles.length > 0" class="mt-2 space-y-1">
                            <template x-for="(file, index) in selectedFiles" :key="index">
                                <div class="flex items-center justify-between p-2 bg-green-50 dark:bg-green-900/20 rounded-lg text-sm">
                                    <span class="text-green-700 dark:text-green-300 truncate" x-text="file.name"></span>
                                    <button type="button" @click="removeFile(index)" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl">
                            Скасувати
                        </button>
                        <button type="submit" :disabled="loading"
                                class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                            <span x-show="!loading">Зберегти</span>
                            <span x-show="loading">Збереження...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
