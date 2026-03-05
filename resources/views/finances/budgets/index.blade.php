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

        {{-- Period Selector + Copy --}}
        <div class="flex items-center gap-2">
            @if(auth()->user()->canEdit('finances'))
            <button x-on:click="showCopyModal = true"
                    title="Скопіювати всі бюджети команд на інший місяць"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Копіювати
            </button>
            @endif
            <select x-model="month" x-on:change="updatePeriod()"
                    title="Оберіть місяць для перегляду план/факт витрат"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @foreach([1 => 'Січень', 2 => 'Лютий', 3 => 'Березень', 4 => 'Квітень', 5 => 'Травень', 6 => 'Червень', 7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень', 10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень'] as $m => $name)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select x-model="year" x-on:change="updatePeriod()"
                    title="Оберіть рік бюджету"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                @for($y = now()->year + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- ═══ Sub-tabs: Огляд | Загальний бюджет | Команди ═══ --}}
    @php
        $churchPct = $churchBudgetTotals['percentage'] ?? 0;
        $ministryPct = $ministryPercentage ?? 0;
        $dotColor = fn($pct) => $pct > 100 ? 'bg-red-500' : ($pct > 80 ? 'bg-orange-400' : ($pct > 0 ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'));
    @endphp
    <div class="flex gap-1 overflow-x-auto scrollbar-hide pb-1">
        <button @click="budgetTab = 'overview'"
                :class="budgetTab === 'overview' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
            {{ __('app.budget_tab_overview') }}
        </button>
        <button @click="budgetTab = 'church'"
                :class="budgetTab === 'church' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
            {{ __('app.budget_tab_church') }}
            <span class="w-2 h-2 rounded-full {{ $dotColor($churchPct) }}"></span>
        </button>
        <button @click="budgetTab = 'teams'"
                :class="budgetTab === 'teams' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
            {{ __('app.budget_tab_teams') }}
            <span class="w-2 h-2 rounded-full {{ $dotColor($ministryPct) }}"></span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: Overview                                       --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="budgetTab === 'overview'" x-cloak>
        {{-- Info hint --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-blue-800 dark:text-blue-200">{{ __('app.budget_overview_hint') }}</p>
            </div>
        </div>

        {{-- Split Summary: Row 1 — Church Budget --}}
        <div class="space-y-4">
            <div>
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.budget_church_row') }}</h3>
                @if($churchBudgetTotals['planned'] > 0)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.planned') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($churchBudgetTotals['planned'], 0, ',', ' ') }} <span class="text-sm text-gray-500">₴</span></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.actual') }}</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($churchBudgetTotals['actual'], 0, ',', ' ') }} <span class="text-sm">₴</span></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.remaining') }}</p>
                        <p class="text-xl font-bold {{ $churchBudgetTotals['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} mt-1">{{ number_format($churchBudgetTotals['difference'], 0, ',', ' ') }} <span class="text-sm">₴</span></p>
                    </div>
                </div>
                @else
                <button @click="budgetTab = 'church'"
                        class="w-full bg-white dark:bg-gray-800 rounded-xl p-4 border border-dashed border-gray-300 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors text-left">
                    {{ __('app.budget_setup_church') }} &rarr;
                </button>
                @endif
            </div>

            {{-- Row 2 — Ministry Budgets --}}
            <div>
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.budget_ministry_row') }}</h3>
                @if($totals['budget'] > 0)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.budget') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totals['budget'], 0, ',', ' ') }} <span class="text-sm text-gray-500">₴</span></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.budget_spent') }}</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($totals['spent'], 0, ',', ' ') }} <span class="text-sm">₴</span></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.remaining') }}</p>
                        <p class="text-xl font-bold {{ $totals['remaining'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} mt-1">{{ number_format($totals['remaining'], 0, ',', ' ') }} <span class="text-sm">₴</span></p>
                    </div>
                </div>
                @else
                <button @click="budgetTab = 'teams'"
                        class="w-full bg-white dark:bg-gray-800 rounded-xl p-4 border border-dashed border-gray-300 dark:border-gray-600 text-sm text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors text-left">
                    {{ __('app.budget_setup_teams') }} &rarr;
                </button>
                @endif
            </div>
        </div>

        {{-- Grand Total Progress Bar --}}
        @if($grandTotals['planned'] > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mt-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.grand_total') }}: {{ __('app.used_percent') }}</span>
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

        {{-- Trend Chart --}}
        @php
            $maxTrend = max(1, max(array_column($trendData, 'planned') ?: [1]), max(array_column($trendData, 'actual') ?: [1]));
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">План vs Факт (6 місяців)</h3>
            <div class="flex items-end justify-between gap-2 h-32">
                @foreach($trendData as $td)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full flex gap-0.5 items-end" style="height: 96px;">
                        <div class="flex-1 bg-blue-200 dark:bg-blue-900/50 rounded-t transition-all"
                             style="height: {{ $maxTrend > 0 ? max(2, ($td['planned'] / $maxTrend) * 96) : 2 }}px;"
                             title="План: {{ number_format($td['planned'], 0, ',', ' ') }} ₴"></div>
                        @php
                            $factColor = $td['actual'] > $td['planned'] && $td['planned'] > 0
                                ? 'bg-red-400 dark:bg-red-600'
                                : 'bg-emerald-400 dark:bg-emerald-600';
                        @endphp
                        <div class="flex-1 {{ $factColor }} rounded-t transition-all"
                             style="height: {{ $maxTrend > 0 ? max(2, ($td['actual'] / $maxTrend) * 96) : 2 }}px;"
                             title="Факт: {{ number_format($td['actual'], 0, ',', ' ') }} ₴"></div>
                    </div>
                    <span class="text-[10px] text-gray-500 dark:text-gray-400 {{ $td['month'] == $month && $td['year'] == $year ? 'font-bold text-primary-600 dark:text-primary-400' : '' }}">
                        {{ $td['label'] }}
                    </span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-200 dark:bg-blue-900/50 rounded"></span> План</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-emerald-400 dark:bg-emerald-600 rounded"></span> Факт</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-400 dark:bg-red-600 rounded"></span> Перевищено</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: Church Budget (Загальний бюджет)               --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="budgetTab === 'church'" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.church_expenses') }}</h2>
                @if(!$churchBudget && auth()->user()->canEdit('finances'))
                    <button x-on:click="createChurchBudget()" x-bind:disabled="creatingBudget"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!creatingBudget" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <svg x-show="creatingBudget" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="creatingBudget ? '{{ __('app.creating') }}' : '{{ __('app.create_budget') }}'"></span>
                    </button>
                @endif
            </div>

            @if($churchBudget)
                @php
                    $recurringItems = collect($churchBudgetItems)->where('is_recurring', true)->values();
                    $onetimeItems = collect($churchBudgetItems)->where('is_recurring', false)->values();
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.budget_item_name') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.planned') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.actual') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.difference') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('app.annual_total') }}</th>
                                @if(auth()->user()->canEdit('finances'))
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Дії</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- Recurring items group --}}
                            @if($recurringItems->isNotEmpty())
                            <tr class="bg-blue-50/50 dark:bg-blue-900/10">
                                <td colspan="{{ auth()->user()->canEdit('finances') ? 6 : 5 }}" class="px-6 py-2">
                                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider">🔄 {{ __('app.budget_recurring_group') }}</span>
                                </td>
                            </tr>
                            @foreach($recurringItems as $cbi)
                                @include('finances.budgets.partials.church-budget-row', ['cbi' => $cbi])
                            @endforeach
                            @endif

                            {{-- One-time items group --}}
                            @if($onetimeItems->isNotEmpty())
                            <tr class="bg-amber-50/50 dark:bg-amber-900/10">
                                <td colspan="{{ auth()->user()->canEdit('finances') ? 6 : 5 }}" class="px-6 py-2">
                                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">☀️ {{ __('app.budget_onetime_group') }}</span>
                                </td>
                            </tr>
                            @foreach($onetimeItems as $cbi)
                                @include('finances.budgets.partials.church-budget-row', ['cbi' => $cbi])
                            @endforeach
                            @endif

                            {{-- Empty state --}}
                            @if(count($churchBudgetItems) === 0)
                            <tr>
                                <td colspan="{{ auth()->user()->canEdit('finances') ? 6 : 5 }}" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('app.add_budget_item') }}
                                </td>
                            </tr>
                            @endif

                            {{-- Totals --}}
                            @if(count($churchBudgetItems) > 0)
                            <tr class="bg-gray-100 dark:bg-gray-700/50 font-semibold">
                                <td class="px-6 py-3 text-gray-900 dark:text-white">Всього</td>
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
                {{-- Beautiful empty state --}}
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('app.budget_church_empty_title') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('app.budget_church_empty_desc') }}</p>
                    @if(auth()->user()->canEdit('finances'))
                    <button x-on:click="createChurchBudget()" x-bind:disabled="creatingBudget"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                        <svg x-show="!creatingBudget" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <svg x-show="creatingBudget" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ str_replace(':year', $year, __('app.budget_church_empty_cta')) }}
                    </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: Teams (Команди)                                --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="budgetTab === 'teams'" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.ministry_budgets_section') }}</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Команда</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Бюджет</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Витрачено</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Залишок</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">Прогрес</th>
                            @if(auth()->user()->canEdit('finances'))
                            <th class="px-3 py-3 w-16"></th>
                            @endif
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
                                    <div class="w-1 h-8 rounded-full mr-3 shrink-0" style="background-color: {{ $item['ministry']->color ?? '#6b7280' }}"></div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item['ministry']->name }}</div>
                                        @if($item['ministry']->leader)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['ministry']->leader->full_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ number_format($item['monthly_budget'], 0, ',', ' ') }} ₴
                                </span>
                                @if($item['allocated'] > 0)
                                <div class="text-[10px] text-green-600 dark:text-green-400 mt-0.5">
                                    ↳ виділено {{ number_format($item['allocated'], 0, ',', ' ') }} ₴
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="text-red-600 dark:text-red-400">{{ number_format($item['spent'], 0, ',', ' ') }} ₴</span>
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
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('app.budget_ministry_empty_hint') }}</span>
                                @endif
                            </td>
                            @if(auth()->user()->canEdit('finances'))
                            <td class="px-3 py-4 text-center" @click.stop>
                                <button @click="openBudgetModal({{ $item['ministry']->id }}, {{ json_encode($item['ministry']->name) }}, {{ $item['budget']?->monthly_budget ?? $item['ministry']->monthly_budget ?? 0 }}, {{ json_encode($item['budget']?->notes ?? '') }}, {{ $item['has_items'] ? 'true' : 'false' }})"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        title="{{ __('ui.edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </td>
                            @endif
                        </tr>

                        {{-- Expandable Budget Items Sub-table --}}
                        @if($item['has_items'])
                        <tr x-show="expandedMinistries.includes({{ $item['ministry']->id }})"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-cloak>
                            <td colspan="{{ auth()->user()->canEdit('finances') ? 6 : 5 }}" class="px-0 py-0 bg-gray-50 dark:bg-gray-800/80">
                                <div class="px-8 py-4">
                                    {{-- Action buttons in expanded details --}}
                                    @if(auth()->user()->canEdit('finances'))
                                    <div class="flex items-center gap-2 mb-3">
                                        <button @click.stop="openAllocateModal({{ $item['ministry']->id }}, {{ json_encode($item['ministry']->name) }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                                                title="{{ __('app.budget_allocate_tooltip') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ __('app.allocate_budget') }}
                                        </button>
                                        <button @click.stop="openBudgetModal({{ $item['ministry']->id }}, {{ json_encode($item['ministry']->name) }}, {{ $item['budget']?->monthly_budget ?? $item['ministry']->monthly_budget ?? 0 }}, {{ json_encode($item['budget']?->notes ?? '') }}, {{ $item['has_items'] ? 'true' : 'false' }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('ui.edit') }}
                                        </button>
                                    </div>
                                    @endif

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
                                                            <div class="text-xs text-gray-500">{{ $bi['category']->icon_emoji ?? '' }} {{ $bi['category']->name }}</div>
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
                                                        <button @click.stop="showTransactions({{ $bi['id'] }}, {{ json_encode($bi['name']) }})"
                                                                class="inline-flex items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            Деталі
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
                                                                    class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button @click.stop="deleteItem({{ $bi['id'] }}, {{ json_encode($bi['name']) }})"
                                                                    class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach

                                                {{-- Unmatched spending --}}
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

                                                {{-- Totals --}}
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
                            <td colspan="{{ auth()->user()->canEdit('finances') ? 6 : 5 }}" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Немає команд
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Ministries without budget items: action buttons at bottom --}}
            @if(auth()->user()->canEdit('finances'))
            @php $noBudgetMinistries = $ministries->filter(fn($m) => !$m['has_items'] && $m['monthly_budget'] == 0); @endphp
            @if($noBudgetMinistries->isNotEmpty())
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-2">
                @foreach($noBudgetMinistries as $item)
                <button @click="openBudgetModal({{ $item['ministry']->id }}, {{ json_encode($item['ministry']->name) }}, 0, '', false)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:text-primary-600 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-400 transition-colors">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $item['ministry']->color ?? '#6b7280' }}"></div>
                    {{ $item['ministry']->name }}: {{ __('app.budget_ministry_empty_hint') }}
                </button>
                @endforeach
            </div>
            @endif
            @endif
        </div>
    </div>

    {{-- ═══ Expenses Missing Receipts (always visible) ═══ --}}
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

    {{-- ═══ Modals (included from partials) ═══ --}}
    @include('finances.budgets.partials.modal-budget-edit')
    @include('finances.budgets.partials.modal-allocate')
    @include('finances.budgets.partials.modal-budget-item')
    @include('finances.budgets.partials.modal-church-item')
    @include('finances.budgets.partials.modal-church-transactions')
    @include('finances.budgets.partials.modal-transactions')
    @include('finances.budgets.partials.modal-copy-budgets')
</div>

<script>
function budgetsPage() {
    // Restore saved filters if page loaded without explicit params
    const _urlParams = new URLSearchParams(window.location.search);
    if (!_urlParams.has('year') && !_urlParams.has('month')) {
        const _saved = filterStorage.load('finance_budgets', { month: 0, year: 0 });
        if (_saved.month > 0 && _saved.year > 0) {
            Livewire.navigate(`{{ route('finances.budgets') }}?year=${_saved.year}&month=${_saved.month}`);
            return {};
        }
    }

    return {
        year: {{ $year }},
        month: {{ $month }},

        // Sub-tab
        budgetTab: localStorage.getItem('budgetTab') || 'overview',

        // Expanded ministries
        expandedMinistries: [],

        init() {
            // Save current filters
            filterStorage.save('finance_budgets', { month: Number(this.month), year: Number(this.year) });

            // Persist tab selection
            this.$watch('budgetTab', (val) => localStorage.setItem('budgetTab', val));
        },

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
        creatingBudget: false,
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

        // Copy budgets modal
        showCopyModal: false,
        copySaving: false,
        copyToMonth: {{ $month == 12 ? 1 : $month + 1 }},
        copyToYear: {{ $month == 12 ? $year + 1 : $year }},
        monthNames: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],

        updatePeriod() {
            filterStorage.save('finance_budgets', { month: Number(this.month), year: Number(this.year) });
            Livewire.navigate(`{{ route('finances.budgets') }}?year=${this.year}&month=${this.month}`);
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

            await this.loadMinistryMembers(budgetId);
            this.showItemModal = true;
        },

        async loadMinistryMembers(budgetId) {
            if (this.membersByBudget[budgetId]) {
                this.ministryMembers = this.membersByBudget[budgetId];
                return;
            }

            try {
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
            if (this.creatingBudget) return;
            this.creatingBudget = true;
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
            } finally {
                this.creatingBudget = false;
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
                const res = await fetch(`/finances/church-budget-items/${itemId}/transactions?month=${this.month}&year=${this.year}`, {
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

        async submitCopyBudgets() {
            this.copySaving = true;
            try {
                const res = await fetch('/finances/budgets/copy-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        from_year: this.year,
                        from_month: this.month,
                        to_year: Number(this.copyToYear),
                        to_month: Number(this.copyToMonth),
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.showCopyModal = false;
                    showToast('success', data.message);
                    setTimeout(() => {
                        Livewire.navigate(`{{ route('finances.budgets') }}?year=${this.copyToYear}&month=${this.copyToMonth}`);
                    }, 800);
                } else {
                    showToast('error', data.message || 'Помилка копіювання');
                }
            } catch (e) {
                showToast('error', 'Помилка копіювання');
            } finally {
                this.copySaving = false;
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('uk-UA', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
        }
    }
}
</script>
</div><!-- /finance-content -->

@include('finances.budgets.partials.modal-expense-edit')
@endsection
