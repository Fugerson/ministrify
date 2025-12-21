@props(['event', 'templates'])

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <h3 class="font-semibold text-gray-900 dark:text-white">Чеклист</h3>
        </div>

        @if($event->checklist)
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-24 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                             style="width: {{ $event->checklist->progress }}%"></div>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $event->checklist->progress }}%</span>
                </div>
                <form method="POST" action="{{ route('checklists.destroy', $event->checklist) }}"
                      onsubmit="return confirm('Видалити чеклист?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if($event->checklist)
        <!-- Checklist Items -->
        <div class="divide-y divide-gray-100 dark:divide-gray-700" x-data="{ editing: null }">
            @foreach($event->checklist->items as $item)
                <div class="p-4 flex items-start gap-3 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <form method="POST" action="{{ route('checklists.items.toggle', $item) }}" class="mt-0.5">
                        @csrf
                        <button type="submit" class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors
                            {{ $item->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-green-500' }}">
                            @if($item->is_completed)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </button>
                    </form>

                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white {{ $item->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                            {{ $item->title }}
                        </p>
                        @if($item->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $item->description }}</p>
                        @endif
                        @if($item->is_completed && $item->completedBy)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $item->completedBy->name }} &bull; {{ $item->completed_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('checklists.items.delete', $item) }}"
                          class="opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @endforeach

            <!-- Add Item Form -->
            <div class="p-4" x-data="{ adding: false, title: '' }">
                <template x-if="!adding">
                    <button @click="adding = true"
                            class="w-full p-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додати пункт
                    </button>
                </template>

                <template x-if="adding">
                    <form method="POST" action="{{ route('checklists.items.add', $event->checklist) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="title" x-model="title" required autofocus
                               placeholder="Назва пункту..."
                               class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Додати
                        </button>
                        <button type="button" @click="adding = false; title = ''" class="p-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </template>
            </div>
        </div>
    @else
        <!-- Create Checklist -->
        <div class="p-6" x-data="{ showTemplates: false }">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Додайте чеклист для відстеження підготовки</p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <form method="POST" action="{{ route('checklists.events.create', $event) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Порожній чеклист
                        </button>
                    </form>

                    @if($templates->isNotEmpty())
                        <div class="relative">
                            <button type="button" @click="showTemplates = !showTemplates"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                З шаблону
                            </button>

                            <div x-show="showTemplates" x-cloak @click.away="showTemplates = false"
                                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-10">
                                @foreach($templates as $template)
                                    <form method="POST" action="{{ route('checklists.events.create', $event) }}">
                                        @csrf
                                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                                        <button type="submit" class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $template->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $template->items->count() }} пунктів</p>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
