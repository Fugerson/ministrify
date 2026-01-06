@props(['tabs', 'currentRoute'])

<div class="mb-6">
    <nav class="flex space-x-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1" aria-label="Tabs">
        @foreach($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all
                      {{ request()->routeIs($tab['active'] ?? $tab['route'])
                         ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm'
                         : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-gray-700/50' }}">
                @if(isset($tab['icon']))
                    <span class="mr-2">{!! $tab['icon'] !!}</span>
                @endif
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full
                                 {{ request()->routeIs($tab['active'] ?? $tab['route'])
                                    ? 'bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400'
                                    : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }}">
                        {{ $tab['count'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
