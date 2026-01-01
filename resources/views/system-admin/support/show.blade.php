@extends('layouts.system-admin')

@section('title', $ticket->subject)

@section('content')
<div class="space-y-6">
    <!-- Back link -->
    <a href="{{ route('system.support.index') }}" class="inline-flex items-center text-gray-400 hover:text-white text-sm">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до списку
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Header -->
            <div class="bg-gray-800 rounded-xl p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->category_color }}-500/20 text-{{ $ticket->category_color }}-400">
                        {{ $ticket->category_label }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                        {{ $ticket->status_label }}
                    </span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $ticket->priority_color }}-500/20 text-{{ $ticket->priority_color }}-400">
                        {{ $ticket->priority_label }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-white">{{ $ticket->subject }}</h1>
            </div>

            <!-- Messages -->
            <div class="bg-gray-800 rounded-xl overflow-hidden">
                <div class="divide-y divide-gray-700">
                    @foreach($messages as $message)
                        <div class="p-6 {{ $message->is_internal ? 'bg-yellow-900/10 border-l-4 border-yellow-500' : ($message->is_from_admin ? 'bg-blue-900/10' : '') }}">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $message->is_from_admin ? 'bg-primary-500/20' : 'bg-gray-700' }}">
                                    @if($message->is_from_admin)
                                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    @else
                                        <span class="text-white">{{ mb_substr($message->user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-medium text-white">
                                            {{ $message->user->name }}
                                        </span>
                                        @if($message->is_internal)
                                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-yellow-500/20 text-yellow-400">
                                                Внутрішня нотатка
                                            </span>
                                        @endif
                                        <span class="text-sm text-gray-400">
                                            {{ $message->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-gray-300 whitespace-pre-wrap">{{ $message->message }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Reply Form -->
            <div class="bg-gray-800 rounded-xl p-6">
                <h2 class="font-semibold text-white mb-4">Відповісти</h2>
                <form action="{{ route('system.support.reply', $ticket) }}" method="POST">
                    @csrf
                    <textarea name="message" rows="4" required
                              placeholder="Напишіть відповідь..."
                              class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-4"></textarea>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-gray-400 cursor-pointer">
                                <input type="checkbox" name="is_internal" value="1" class="rounded bg-gray-700 border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm">Внутрішня нотатка</span>
                            </label>
                            <select name="status" class="px-3 py-1 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-primary-500">
                                <option value="">Не змінювати статус</option>
                                <option value="in_progress">В роботі</option>
                                <option value="waiting">Очікує відповіді</option>
                                <option value="resolved">Вирішено</option>
                                <option value="closed">Закрито</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Надіслати
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-gray-800 rounded-xl p-6">
                <h3 class="font-semibold text-white mb-4">Користувач</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Ім'я</p>
                        <p class="text-white">{{ $ticket->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Email</p>
                        <p class="text-white">{{ $ticket->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Церква</p>
                        <p class="text-white">{{ $ticket->church?->name ?? 'Без церкви' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Створено</p>
                        <p class="text-white">{{ $ticket->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Ticket Actions -->
            <div class="bg-gray-800 rounded-xl p-6">
                <h3 class="font-semibold text-white mb-4">Керування</h3>
                <form action="{{ route('system.support.update', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Статус</label>
                        <select name="status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Відкритий</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>В роботі</option>
                            <option value="waiting" {{ $ticket->status === 'waiting' ? 'selected' : '' }}>Очікує відповіді</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Вирішено</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Закрито</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Пріоритет</label>
                        <select name="priority" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Низький</option>
                            <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>Нормальний</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Високий</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Терміновий</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Призначено</label>
                        <select name="assigned_to" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">Не призначено</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                        Оновити
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
