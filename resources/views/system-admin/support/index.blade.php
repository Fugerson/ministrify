@extends('layouts.system-admin')

@section('title', 'Підтримка - Kanban')

@section('content')
<div x-data="supportKanban()" x-init="init()" class="h-[calc(100vh-160px)]">
    <!-- Header with filters -->
    <div class="mb-4 space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Підтримка</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Тікети від адміністраторів церков</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Всього: <span class="font-semibold text-gray-900 dark:text-white" x-text="allTickets.length"></span>
                </div>
                <button @click="showCreateModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Мій тікет
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 shadow-sm">
            <!-- Search -->
            <div class="relative flex-1 min-w-0 sm:min-w-[180px] sm:max-w-xs">
                <input type="text" x-model="searchQuery" placeholder="Пошук..."
                       class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>

            <!-- Filter by Category -->
            <select x-model="filters.category"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Всі категорії</option>
                <option value="bug">🐛 Помилки</option>
                <option value="question">❓ Питання</option>
                <option value="feature">💡 Пропозиції</option>
                <option value="other">📝 Інше</option>
            </select>

            <!-- Filter by Priority -->
            <select x-model="filters.priority"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-0 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500 hidden sm:block">
                <option value="">Всі пріоритети</option>
                <option value="urgent">🔴 Терміново</option>
                <option value="high">🟠 Високий</option>
                <option value="normal">🟡 Нормальний</option>
                <option value="low">⚪ Низький</option>
            </select>

            <template x-if="hasActiveFilters">
                <button @click="clearFilters()" class="px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Скинути
                </button>
            </template>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 h-full overflow-x-auto pb-4">
        <!-- Column: До роботи -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-blue-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">До роботи</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="todoTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 min-h-[80px] kanban-cards overflow-y-auto max-h-[calc(100vh-350px)]"
                 data-status="open" id="column-open">
                <template x-for="ticket in todoTickets" :key="ticket.id">
                    <div class="kanban-card" :data-id="ticket.id" @click="openTicketPanel(ticket)">
                        <div x-html="renderTicketCard(ticket)"></div>
                    </div>
                </template>
                <template x-if="todoTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-sm">Немає нових тікетів</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: В процесі -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-yellow-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">В процесі</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="inProgressTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 min-h-[80px] kanban-cards overflow-y-auto max-h-[calc(100vh-350px)]"
                 data-status="in_progress" id="column-in_progress">
                <template x-for="ticket in inProgressTickets" :key="ticket.id">
                    <div class="kanban-card" :data-id="ticket.id" @click="openTicketPanel(ticket)">
                        <div x-html="renderTicketCard(ticket)"></div>
                    </div>
                </template>
                <template x-if="inProgressTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Нічого в роботі</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Готово -->
        <div class="kanban-column flex-shrink-0 w-[calc(100vw-2rem)] sm:w-80 bg-gray-50/80 dark:bg-gray-800/50 rounded-xl flex flex-col border border-gray-200/50 dark:border-gray-700/50">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl bg-green-500"></div>
                <div class="p-3 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Готово</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-200/50 dark:bg-gray-700/50 px-2 py-0.5 rounded-full font-medium"
                              x-text="doneTickets.length"></span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-2 space-y-2 min-h-[80px] kanban-cards overflow-y-auto max-h-[calc(100vh-350px)]"
                 data-status="resolved" id="column-resolved">
                <template x-for="ticket in doneTickets" :key="ticket.id">
                    <div class="kanban-card opacity-70" :data-id="ticket.id" @click="openTicketPanel(ticket)">
                        <div x-html="renderTicketCard(ticket, true)"></div>
                    </div>
                </template>
                <template x-if="doneTickets.length === 0">
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">Виконаних немає</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Ticket Panel (Slide-over) -->
    <div x-show="ticketPanel.open" x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         @keydown.escape.window="closePanel()">
        <!-- Backdrop -->
        <div x-show="ticketPanel.open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/30"
             @click="closePanel()"></div>

        <!-- Panel -->
        <div x-show="ticketPanel.open"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 h-full w-full max-w-lg bg-white dark:bg-gray-800 shadow-xl flex flex-col">

            <!-- Panel Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                          :class="getCategoryClass(ticketPanel.data?.category)"
                          x-text="ticketPanel.data?.category_label"></span>
                    <span class="text-xs text-gray-400">#<span x-text="ticketPanel.data?.id"></span></span>
                </div>
                <button @click="closePanel()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Panel Content -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <!-- Subject -->
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="ticketPanel.data?.subject"></h2>

                <!-- Meta -->
                <div class="flex flex-wrap gap-3 text-sm">
                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-text="ticketPanel.data?.user_name"></span>
                    </div>
                    <template x-if="ticketPanel.data?.church_name">
                        <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span x-text="ticketPanel.data?.church_name"></span>
                        </div>
                    </template>
                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="ticketPanel.data?.time_ago"></span>
                    </div>
                </div>

                <!-- Status Selector -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Статус</label>
                    <select x-model="ticketPanel.data.status"
                            @change="updateTicketStatus(ticketPanel.data.id, ticketPanel.data.status)"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="open">📋 До роботи</option>
                        <option value="in_progress">🔄 В процесі</option>
                        <option value="resolved">✅ Готово</option>
                    </select>
                </div>

                <!-- Priority Selector -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Пріоритет</label>
                    <select x-model="ticketPanel.data.priority"
                            @change="updateTicketPriority(ticketPanel.data.id, ticketPanel.data.priority)"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="low">⚪ Низький</option>
                        <option value="normal">🟡 Нормальний</option>
                        <option value="high">🟠 Високий</option>
                        <option value="urgent">🔴 Терміновий</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a :href="ticketPanel.data?.show_url"
                       class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                        Відкрити повністю
                    </a>
                    <button @click="deleteTicket(ticketPanel.data?.id)"
                            class="px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium rounded-lg transition-colors">
                        Видалити
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showCreateModal = false">
        <!-- Backdrop -->
        <div x-show="showCreateModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50"
             @click="showCreateModal = false"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="showCreateModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop
                 class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-xl">

                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Новий тікет</h3>
                    <button @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тема</label>
                        <input type="text" x-model="createForm.subject"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                               placeholder="Опишіть проблему коротко">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія</label>
                            <select x-model="createForm.category"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="bug">🐛 Помилка</option>
                                <option value="feature">💡 Фіча</option>
                                <option value="question">❓ Питання</option>
                                <option value="other">📝 Інше</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пріоритет</label>
                            <select x-model="createForm.priority"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="low">⚪ Низький</option>
                                <option value="normal">🟡 Нормальний</option>
                                <option value="high">🟠 Високий</option>
                                <option value="urgent">🔴 Терміновий</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                        <textarea x-model="createForm.message" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"
                                  placeholder="Детальний опис..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="showCreateModal = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Скасувати
                    </button>
                    <button @click="createTicket()"
                            :disabled="createLoading || !createForm.subject || !createForm.message"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!createLoading">Створити</span>
                        <span x-show="createLoading">Збереження...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function supportKanban() {
    return {
        allTickets: @json($ticketsData),
        searchQuery: '',
        filters: {
            category: '',
            priority: ''
        },
        ticketPanel: {
            open: false,
            data: null
        },
        updateUrl: '{{ route("system.support.update.status") }}',
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,

        get hasActiveFilters() {
            return this.searchQuery || this.filters.category || this.filters.priority;
        },

        get filteredTickets() {
            let tickets = this.allTickets;

            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                tickets = tickets.filter(t =>
                    t.subject.toLowerCase().includes(q) ||
                    t.user_name.toLowerCase().includes(q) ||
                    (t.church_name && t.church_name.toLowerCase().includes(q))
                );
            }

            if (this.filters.category) {
                tickets = tickets.filter(t => t.category === this.filters.category);
            }

            if (this.filters.priority) {
                tickets = tickets.filter(t => t.priority === this.filters.priority);
            }

            return tickets;
        },

        get todoTickets() {
            return this.filteredTickets.filter(t => t.status === 'open');
        },

        get inProgressTickets() {
            return this.filteredTickets.filter(t => ['in_progress', 'waiting'].includes(t.status));
        },

        get doneTickets() {
            return this.filteredTickets.filter(t => ['resolved', 'closed'].includes(t.status));
        },

        clearFilters() {
            this.searchQuery = '';
            this.filters.category = '';
            this.filters.priority = '';
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
            const priorityBadge = (ticket.priority === 'urgent' || ticket.priority === 'high')
                ? `<span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300">${ticket.priority === 'urgent' ? 'Терм.' : 'Вис.'}</span>`
                : '';
            const unreadBadge = ticket.unread > 0
                ? `<span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">${ticket.unread}</span>`
                : '';
            const churchInfo = ticket.church_name
                ? `<div class="mt-1.5 text-[11px] text-gray-400 dark:text-gray-500 truncate flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    ${this.escapeHtml(ticket.church_name)}
                </div>`
                : (ticket.is_own ? `<div class="mt-1.5 text-[11px] text-primary-500 dark:text-primary-400 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Мій тікет
                </div>` : '');

            return `
                <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                    <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full ${categoryClass}">${ticket.category_label}</span>
                        ${priorityBadge}
                        ${unreadBadge}
                        <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 ml-auto">#${ticket.id}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug mb-2 line-clamp-2">${this.escapeHtml(ticket.subject)}</p>
                    <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-1 truncate">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate">${this.escapeHtml(ticket.user_name)}</span>
                        </div>
                        <span class="text-gray-400 dark:text-gray-500 flex-shrink-0">${ticket.time_ago}</span>
                    </div>
                    ${churchInfo}
                </div>
            `;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        },

        init() {
            this.initSortable();
        },

        initSortable() {
            const self = this;
            document.querySelectorAll('.kanban-cards').forEach(container => {
                new Sortable(container, {
                    group: 'tickets',
                    animation: 200,
                    ghostClass: 'opacity-40',
                    chosenClass: 'shadow-lg',
                    dragClass: 'rotate-2',
                    draggable: '.kanban-card',
                    onEnd: async (evt) => {
                        const ticketId = evt.item.dataset.id;
                        const newStatus = evt.to.dataset.status;

                        // Update local data
                        const ticket = self.allTickets.find(t => t.id == ticketId);
                        if (ticket) {
                            ticket.status = newStatus;
                        }

                        // Update server
                        await self.updateTicketStatus(ticketId, newStatus);
                    }
                });
            });
        },

        openTicketPanel(ticket) {
            this.ticketPanel.data = { ...ticket };
            this.ticketPanel.open = true;
        },

        closePanel() {
            this.ticketPanel.open = false;
        },

        async updateTicketStatus(ticketId, newStatus) {
            const ticket = this.allTickets.find(t => t.id == ticketId);
            if (ticket) {
                ticket.status = newStatus;
            }

            try {
                await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId,
                        status: newStatus
                    })
                });
            } catch (error) {
                console.error('Error updating status:', error);
            }
        },

        async updateTicketPriority(ticketId, newPriority) {
            const ticket = this.allTickets.find(t => t.id == ticketId);
            if (ticket) {
                ticket.priority = newPriority;
            }

            try {
                await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId,
                        priority: newPriority
                    })
                });
            } catch (error) {
                console.error('Error updating priority:', error);
            }
        },

        async deleteTicket(ticketId) {
            if (!confirm('Видалити тікет?')) return;

            try {
                const response = await fetch(`/system-admin/support/${ticketId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.allTickets = this.allTickets.filter(t => t.id !== ticketId);
                    this.closePanel();
                }
            } catch (error) {
                console.error('Error deleting ticket:', error);
            }
        },

        // Create modal
        showCreateModal: false,
        createForm: {
            subject: '',
            category: 'bug',
            priority: 'normal',
            message: ''
        },
        createLoading: false,

        async createTicket() {
            if (!this.createForm.subject || !this.createForm.message) return;

            this.createLoading = true;
            try {
                const response = await fetch('{{ route("system.support.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.createForm)
                });

                const data = await response.json();
                if (data.success) {
                    this.allTickets.unshift(data.ticket);
                    this.showCreateModal = false;
                    this.createForm = { subject: '', category: 'bug', priority: 'normal', message: '' };
                }
            } catch (error) {
                console.error('Error creating ticket:', error);
            }
            this.createLoading = false;
        }
    };
}
</script>

<style>
.kanban-card { position: relative; }
.kanban-card.sortable-ghost { opacity: 0.4; }
.kanban-card.sortable-chosen { transform: rotate(2deg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection
