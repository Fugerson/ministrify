@props(['entityType', 'entityId', 'boards' => []])

<div x-data="linkedCards()" x-init="loadCards()" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Пов'язані завдання
        </h3>
        @if(count($boards) > 0)
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Створити завдання
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                 class="absolute right-0 mt-2 w-48 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-700 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-600">
                <div class="py-1">
                    @foreach($boards as $board)
                    <form action="{{ route('boards.create-from-entity') }}" method="POST">
                        @csrf
                        <input type="hidden" name="entity_type" value="{{ $entityType }}">
                        <input type="hidden" name="entity_id" value="{{ $entityId }}">
                        <input type="hidden" name="board_id" value="{{ $board->id }}">
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center">
                            <span class="w-3 h-3 rounded mr-2" style="background-color: {{ $board->color }}"></span>
                            {{ $board->name }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div x-show="loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div x-show="!loading && cards.length === 0" class="text-center py-4">
        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає пов'язаних завдань</p>
    </div>

    <div x-show="!loading && cards.length > 0" class="space-y-2">
        <template x-for="card in cards" :key="card.id">
            <a :href="'/boards/cards/' + card.id"
               class="block p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full"
                              :class="{
                                  'bg-red-500': card.priority === 'urgent',
                                  'bg-orange-500': card.priority === 'high',
                                  'bg-yellow-500': card.priority === 'medium',
                                  'bg-green-500': card.priority === 'low',
                                  'bg-gray-400': !card.priority
                              }"></span>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="card.title"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span x-show="card.is_completed" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Виконано
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="card.column?.board?.name"></span>
                    </div>
                </div>
                <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400">
                    <span x-show="card.column" x-text="card.column?.name"></span>
                    <span x-show="card.due_date">
                        <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="formatDate(card.due_date)"></span>
                    </span>
                    <span x-show="card.assignee">
                        <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-text="card.assignee?.first_name + ' ' + card.assignee?.last_name"></span>
                    </span>
                </div>
            </a>
        </template>
    </div>
</div>

<script>
function linkedCards() {
    return {
        cards: [],
        loading: true,
        async loadCards() {
            try {
                const response = await fetch(`/boards/linked-cards?entity_type={{ $entityType }}&entity_id={{ $entityId }}`);
                const data = await response.json();
                this.cards = data;
            } catch (error) {
                console.error('Failed to load linked cards:', error);
            } finally {
                this.loading = false;
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('uk-UA', { day: 'numeric', month: 'short' });
        }
    };
}
</script>
