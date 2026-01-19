@php
    $currentRoute = Route::currentRouteName();
    $tabs = [
        ['id' => 'journal', 'route' => 'finances.journal', 'label' => 'Журнал'],
        ['id' => 'analytics', 'route' => 'finances.index', 'label' => 'Аналітика'],
        ['id' => 'incomes', 'route' => 'finances.incomes', 'label' => 'Надходження'],
        ['id' => 'expenses', 'route' => 'finances.expenses.index', 'label' => 'Витрати'],
        ['id' => 'donations', 'route' => 'donations.index', 'label' => 'Пожертви'],
        ['id' => 'budgets', 'route' => 'finances.budgets', 'label' => 'Бюджети'],
        ['id' => 'cards', 'route' => 'finances.cards', 'label' => 'Моя карта'],
    ];
    $activeTab = collect($tabs)->first(fn($t) => $t['route'] === $currentRoute)['id'] ?? 'analytics';
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm mb-4" x-data="{ activeTab: '{{ $activeTab }}', loading: false }">
    <nav class="flex overflow-x-auto items-center" aria-label="Tabs">
        @foreach($tabs as $tab)
            <button type="button"
                    @click="
                        if (activeTab === '{{ $tab['id'] }}') return;
                        loading = true;
                        activeTab = '{{ $tab['id'] }}';
                        fetch('{{ route($tab['route']) }}', {
                            headers: { 'X-Tab-Request': '1' }
                        })
                        .then(r => r.text())
                        .then(html => {
                            const container = document.getElementById('finance-content');
                            container.innerHTML = html;
                            history.pushState({}, '', '{{ route($tab['route']) }}');

                            // Execute scripts
                            container.querySelectorAll('script').forEach(oldScript => {
                                const newScript = document.createElement('script');
                                if (oldScript.src) {
                                    newScript.src = oldScript.src;
                                } else {
                                    newScript.textContent = oldScript.textContent;
                                }
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });

                            loading = false;

                            // Reinit Alpine after scripts loaded
                            setTimeout(() => {
                                document.querySelectorAll('#finance-content [x-data]').forEach(el => {
                                    if (!el._x_dataStack) Alpine.initTree(el);
                                });
                            }, 50);
                        })
                        .catch(() => { window.location.href = '{{ route($tab['route']) }}'; });
                    "
                    :class="activeTab === '{{ $tab['id'] }}'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors flex-shrink-0">
                {{ $tab['label'] }}
            </button>
        @endforeach

        <!-- Settings link for admins -->
        @admin
        <a href="{{ route('settings.index') }}?tab=data"
           onclick="localStorage.setItem('settings_tab', 'data')"
           class="ml-auto mr-4 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
           title="Налаштування категорій">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>
        @endadmin
    </nav>

    <!-- Loading overlay -->
    <div x-show="loading" class="fixed inset-0 bg-white/50 dark:bg-gray-900/50 z-50 flex items-center justify-center">
        <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>
