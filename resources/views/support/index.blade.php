@extends('layouts.app')

@section('title', 'Підтримка')

@section('content')
<div x-data="supportKanban()" x-init="init()" class="h-[calc(100vh-200px)]">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Підтримка</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ваші запити до служби підтримки</p>
        </div>
        <a href="{{ route('support.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новий запит
        </a>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 h-full overflow-x-auto pb-4">
        <!-- Column: Відкриті -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-blue-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Відкриті</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="openTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 overflow-y-auto max-h-[calc(100vh-350px)]">
                <template x-for="ticket in openTickets" :key="ticket.id">
                    <a :href="ticket.show_url" class="block">
                        <div x-html="renderTicketCard(ticket)"></div>
                    </a>
                </template>
                <template x-if="openTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-sm">Немає відкритих запитів</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Очікують відповіді -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-yellow-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">В роботі</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="inProgressTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 overflow-y-auto max-h-[calc(100vh-350px)]">
                <template x-for="ticket in inProgressTickets" :key="ticket.id">
                    <a :href="ticket.show_url" class="block">
                        <div x-html="renderTicketCard(ticket)"></div>
                    </a>
                </template>
                <template x-if="inProgressTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Немає запитів в роботі</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Вирішено -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-green-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Вирішено</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="resolvedTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 overflow-y-auto max-h-[calc(100vh-350px)]">
                <template x-for="ticket in resolvedTickets" :key="ticket.id">
                    <a :href="ticket.show_url" class="block opacity-70">
                        <div x-html="renderTicketCard(ticket, true)"></div>
                    </a>
                </template>
                <template x-if="resolvedTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Вирішених запитів немає</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <template x-if="allTickets.length === 0">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center p-12">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає запитів</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Маєте питання або знайшли помилку? Напишіть нам!</p>
                <a href="{{ route('support.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Створити запит
                </a>
            </div>
        </div>
    </template>
</div>

<script>
function supportKanban() {
    return {
        allTickets: @json($ticketsData),

        get openTickets() {
            return this.allTickets.filter(t => t.status === 'open');
        },

        get inProgressTickets() {
            return this.allTickets.filter(t => ['in_progress', 'waiting'].includes(t.status));
        },

        get resolvedTickets() {
            return this.allTickets.filter(t => ['resolved', 'closed'].includes(t.status));
        },

        getCategoryClass(category) {
            const classes = {
                'bug': 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300',
                'question': 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300',
                'feature': 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300',
                'other': 'bg-gray-100 dark:bg-gray-500/20 text-gray-700 dark:text-gray-300',
            };
            return classes[category] || classes['other'];
        },

        renderTicketCard(ticket, isDone = false) {
            const categoryClass = this.getCategoryClass(ticket.category);
            const unreadBadge = ticket.unread > 0
                ? `<span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">${ticket.unread} нових</span>`
                : '';
            const statusBadge = !isDone
                ? `<span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full bg-${ticket.status === 'waiting' ? 'purple' : 'blue'}-100 dark:bg-${ticket.status === 'waiting' ? 'purple' : 'blue'}-500/20 text-${ticket.status === 'waiting' ? 'purple' : 'blue'}-700 dark:text-${ticket.status === 'waiting' ? 'purple' : 'blue'}-300">${ticket.status_label}</span>`
                : '';

            return `
                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                    <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full ${categoryClass}">${ticket.category_label}</span>
                        ${statusBadge}
                        ${unreadBadge}
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug mb-2 line-clamp-2">${this.escapeHtml(ticket.subject)}</p>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        ${ticket.time_ago}
                    </div>
                </div>
            `;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        },

        init() {
            // No drag-drop for users - they can only view their tickets
        }
    };
}
</script>

<style>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
