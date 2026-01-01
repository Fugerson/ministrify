@extends('layouts.system-admin')

@section('title', 'Підтримка')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Відкриті</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['open'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Очікують відповіді</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['waiting'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Вирішено</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['resolved'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4">
        <form action="{{ route('system.support.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Пошук..."
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <select name="status" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Відкриті</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Всі</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Очікують відповіді</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Вирішено</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Закриті</option>
            </select>
            <select name="category" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Всі категорії</option>
                <option value="bug" {{ request('category') === 'bug' ? 'selected' : '' }}>Помилки</option>
                <option value="question" {{ request('category') === 'question' ? 'selected' : '' }}>Питання</option>
                <option value="feature" {{ request('category') === 'feature' ? 'selected' : '' }}>Пропозиції</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Інше</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                Фільтрувати
            </button>
        </form>
    </div>

    <!-- Tickets List -->
    <div class="bg-gray-800 rounded-xl overflow-hidden">
        @if($tickets->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-400">Немає тікетів</p>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Тема</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Користувач</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Категорія</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Оновлено</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($tickets as $ticket)
                        <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location='{{ route('system.support.show', $ticket) }}'">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if($ticket->unreadMessagesForAdmin() > 0)
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    @endif
                                    <span class="text-white font-medium">{{ Str::limit($ticket->subject, 50) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-white">{{ $ticket->user->name }}</p>
                                    <p class="text-sm text-gray-400">{{ $ticket->church?->name ?? 'Без церкви' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->category_color }}-500/20 text-{{ $ticket->category_color }}-400">
                                    {{ $ticket->category_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $ticket->last_reply_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div>
        {{ $tickets->links() }}
    </div>
</div>
@endsection
