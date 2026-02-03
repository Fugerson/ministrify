{{-- Ministry Goals Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Цілі команд
        </h2>
    </div>

    @if($ministryGoals->count() > 0)
    <div class="p-4 lg:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($ministryGoals->take(8) as $goal)
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="font-medium text-sm text-gray-900 dark:text-white truncate flex-1">{{ $goal->title }}</h4>
                    @if($goal->priority === 'high' || $goal->priority === 'urgent')
                        <span class="w-2 h-2 rounded-full {{ $goal->priority === 'urgent' ? 'bg-red-500' : 'bg-orange-500' }} flex-shrink-0"></span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mb-2">
                    @if($goal->ministry)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium flex-shrink-0"
                              style="background-color: {{ $goal->ministry->color ?? '#6366f1' }}20; color: {{ $goal->ministry->color ?? '#6366f1' }};">
                            {{ $goal->ministry->name }}
                        </span>
                    @endif
                    @php
                        $statusColors = [
                            'in_progress' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400',
                            'completed' => 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400',
                            'on_hold' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
                            'not_started' => 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400',
                        ];
                        $statusLabels = [
                            'in_progress' => 'В процесі',
                            'completed' => 'Завершено',
                            'on_hold' => 'На паузі',
                            'not_started' => 'Не розпочато',
                            'cancelled' => 'Скасовано',
                        ];
                        $statusClass = $statusColors[$goal->status] ?? $statusColors['not_started'];
                        $statusLabel = $statusLabels[$goal->status] ?? $goal->status;
                    @endphp
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <div class="mb-2">
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width: {{ min(100, $goal->progress) }}%; background-color: {{ $goal->ministry->color ?? '#6366f1' }};"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $goal->progress }}%</span>
                    @if($goal->due_date)
                        <span class="flex items-center gap-1 {{ $goal->due_date->isPast() && $goal->status !== 'completed' ? 'text-red-500 font-medium' : '' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $goal->due_date->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає активних цілей</p>
    </div>
    @endif
</div>
