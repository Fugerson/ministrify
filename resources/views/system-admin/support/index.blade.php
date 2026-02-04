@extends('layouts.system-admin')

@section('title', 'Підтримка - Kanban')

@section('content')
<div x-data="supportKanban()" class="h-[calc(100vh-180px)]">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Підтримка</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Тікети від адміністраторів церков</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Всього: <span class="font-semibold text-gray-900 dark:text-white" x-text="allTickets.length"></span>
            </span>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 h-full overflow-x-auto pb-4">
        <!-- Column: До роботи -->
        <div class="flex-1 min-w-[320px] max-w-[400px] bg-gray-100 dark:bg-gray-800/50 rounded-xl flex flex-col">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">До роботи</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded-full" x-text="todoTickets.length"></span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3"
                 @dragover.prevent="dragOver($event)"
                 @drop="dropTicket($event, 'open')">
                <template x-for="ticket in todoTickets" :key="ticket.id">
                    <div x-html="ticketCard(ticket)"></div>
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
        <div class="flex-1 min-w-[320px] max-w-[400px] bg-gray-100 dark:bg-gray-800/50 rounded-xl flex flex-col">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">В процесі</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 rounded-full" x-text="inProgressTickets.length"></span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3"
                 @dragover.prevent="dragOver($event)"
                 @drop="dropTicket($event, 'in_progress')">
                <template x-for="ticket in inProgressTickets" :key="ticket.id">
                    <div x-html="ticketCard(ticket)"></div>
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
        <div class="flex-1 min-w-[320px] max-w-[400px] bg-gray-100 dark:bg-gray-800/50 rounded-xl flex flex-col">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Готово</h3>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300 rounded-full" x-text="doneTickets.length"></span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-3"
                 @dragover.prevent="dragOver($event)"
                 @drop="dropTicket($event, 'resolved')">
                <template x-for="ticket in doneTickets" :key="ticket.id">
                    <div x-html="ticketCard(ticket, true)"></div>
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
</div>

<!-- Ticket Card Template (hidden) -->
<template id="ticket-card-template">
    <div class="ticket-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 cursor-pointer hover:shadow-md transition-all"
         draggable="true">
        <div class="flex items-center gap-2 mb-2">
            <span class="category-badge px-2 py-0.5 text-xs font-medium rounded-full"></span>
            <span class="priority-badge px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 hidden"></span>
            <span class="unread-badge ml-auto px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full hidden"></span>
        </div>
        <h4 class="subject font-medium text-gray-900 dark:text-white text-sm mb-2 line-clamp-2"></h4>
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-1 truncate">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="user-name truncate"></span>
            </div>
            <span class="time-ago text-gray-400 dark:text-gray-500"></span>
        </div>
        <div class="church-info mt-1 text-xs text-gray-400 dark:text-gray-500 truncate flex items-center gap-1 hidden">
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="church-name truncate"></span>
        </div>
    </div>
</template>

<script>
function supportKanban() {
    return {
        allTickets: @json($ticketsData),
        draggedTicket: null,
        updateUrl: '{{ route("system.support.update.status") }}',

        get todoTickets() {
            return this.allTickets.filter(t => t.status === 'open');
        },

        get inProgressTickets() {
            return this.allTickets.filter(t => ['in_progress', 'waiting'].includes(t.status));
        },

        get doneTickets() {
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

        ticketCard(ticket, isDone = false) {
            const categoryClass = this.getCategoryClass(ticket.category);
            const priorityBadge = (ticket.priority === 'urgent' || ticket.priority === 'high')
                ? `<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300">${ticket.priority_label}</span>`
                : '';
            const unreadBadge = ticket.unread > 0
                ? `<span class="ml-auto px-1.5 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">${ticket.unread}</span>`
                : '';
            const churchInfo = ticket.church_name
                ? `<div class="mt-1 text-xs text-gray-400 dark:text-gray-500 truncate flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="truncate">${ticket.church_name}</span>
                </div>`
                : '';

            return `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 cursor-pointer hover:shadow-md transition-all ${isDone ? 'opacity-70' : ''}"
                     draggable="true"
                     ondragstart="supportKanbanDragStart(event, ${ticket.id})"
                     ondragend="supportKanbanDragEnd(event)"
                     onclick="window.location.href='${ticket.show_url}'">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full ${categoryClass}">${ticket.category_label}</span>
                        ${priorityBadge}
                        ${unreadBadge}
                    </div>
                    <h4 class="font-medium text-gray-900 dark:text-white text-sm mb-2 line-clamp-2">${ticket.subject}</h4>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-1 truncate">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate">${ticket.user_name}</span>
                        </div>
                        <span class="text-gray-400 dark:text-gray-500">${ticket.time_ago}</span>
                    </div>
                    ${churchInfo}
                </div>
            `;
        },

        dragOver(event) {
            event.preventDefault();
        },

        async dropTicket(event, newStatus) {
            event.preventDefault();
            const ticketId = event.dataTransfer.getData('text/plain');
            if (!ticketId) return;

            const ticket = this.allTickets.find(t => t.id == ticketId);
            if (!ticket) return;

            const oldStatus = ticket.status;

            // Optimistically update UI
            ticket.status = newStatus;

            try {
                const response = await fetch(this.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId,
                        status: newStatus
                    })
                });

                if (!response.ok) {
                    ticket.status = oldStatus;
                }
            } catch (error) {
                ticket.status = oldStatus;
                console.error('Error updating ticket:', error);
            }
        }
    };
}

// Global functions for drag-drop (needed because of dynamic HTML)
function supportKanbanDragStart(event, ticketId) {
    event.dataTransfer.setData('text/plain', ticketId);
    event.dataTransfer.effectAllowed = 'move';
    event.target.classList.add('opacity-50', 'rotate-1', 'scale-105');
}

function supportKanbanDragEnd(event) {
    event.target.classList.remove('opacity-50', 'rotate-1', 'scale-105');
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
