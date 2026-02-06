@php
    $currentRoute = Route::currentRouteName();
    $tabs = [
        ['id' => 'analytics', 'route' => 'finances.index', 'label' => 'Аналітика'],
        ['id' => 'journal', 'route' => 'finances.journal', 'label' => 'Журнал'],
        ['id' => 'incomes', 'route' => 'finances.incomes', 'label' => 'Надходження'],
        ['id' => 'expenses', 'route' => 'finances.expenses.index', 'label' => 'Витрати'],
        ['id' => 'budgets', 'route' => 'finances.budgets', 'label' => 'Бюджети'],
        ['id' => 'cards', 'route' => 'finances.cards', 'label' => 'Моя карта'],
    ];
    $activeTab = collect($tabs)->first(fn($t) => $t['route'] === $currentRoute)['id'] ?? 'analytics';
    $showFilters = !in_array($activeTab, ['cards']);
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm mb-4">
    <nav class="flex overflow-x-auto items-center" aria-label="Tabs">
        @foreach($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex-shrink-0
                   {{ $activeTab === $tab['id']
                       ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                       : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach

        <!-- Settings link -->
        @if(auth()->user()->canView('settings'))
        <a href="{{ route('settings.index') }}?tab=data"
           onclick="localStorage.setItem('settings_tab', 'data')"
           class="ml-auto mr-4 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
           title="Налаштування категорій">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>
        @endif
    </nav>
</div>

{{-- Universal filters (shown on all tabs except "Моя карта") --}}
@include('finances.partials.filters')
