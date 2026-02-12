{{-- Urgent Tasks Widget (Admin Only) --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 lg:px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Завдання
        </h2>
        <a href="{{ route('boards.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            Всі завдання
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if(count($urgentTasks) > 0)
    <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
            @foreach($urgentTasks->take(5) as $task)
            <a href="{{ route('boards.index', ['card' => $task->id]) }}"
               class="block bg-gray-50 dark:bg-gray-700/50 border-l-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 overflow-hidden
                      {{ $task->priority === 'urgent' ? 'border-l-red-500' : ($task->priority === 'high' ? 'border-l-orange-500' : 'border-l-yellow-500') }}">
                <div class="p-3">
                    <h4 class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2 mb-2">{{ $task->title }}</h4>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            {{ $task->column?->name }}
                        </span>
                        @if($task->priority === 'urgent')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400">
                            !
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        @if($task->due_date)
                        <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $task->due_date->format('d.m') }}
                        </span>
                        @else
                        <span></span>
                        @endif

                        @if($task->assignee)
                        <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center" title="{{ $task->assignee->full_name }}">
                            <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($task->assignee->first_name, 0, 1) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        @if(count($urgentTasks) > 5)
        <a href="{{ route('boards.index') }}" class="block text-center py-3 mt-3 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 border-t border-gray-200 dark:border-gray-700">
            + ще {{ count($urgentTasks) - 5 }} завдань
        </a>
        @endif
    </div>
    @else
    <div class="p-8 text-center">
        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-gray-600 dark:text-gray-400">Немає термінових завдань</p>
        <a href="{{ route('boards.index') }}" class="inline-block mt-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
            Перейти до трекера
        </a>
    </div>
    @endif
</div>
