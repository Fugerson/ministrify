@extends('layouts.app')

@section('title', $meeting->title)

@section('actions')
<div class="flex items-center gap-2">
    <a href="{{ route('meetings.copy', [$ministry, $meeting]) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        Копіювати
    </a>
    <a href="{{ route('meetings.edit', [$ministry, $meeting]) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Редагувати
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'agenda', showAddAgenda: false, showAddMaterial: false, showAddAttendee: false }">
    <!-- Back link -->
    <a href="{{ route('meetings.index', $ministry) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Зустрічі {{ $ministry->name }}
    </a>

    <!-- Meeting Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="flex items-start gap-4">
                <!-- Date badge -->
                <div class="w-16 h-16 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex flex-col items-center justify-center flex-shrink-0">
                    <span class="text-xs text-primary-600 dark:text-primary-400 uppercase font-medium">{{ $meeting->date->translatedFormat('M') }}</span>
                    <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ $meeting->date->format('d') }}</span>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $meeting->title }}</h1>
                        @php
                            $statusColors = [
                                'planned' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'in_progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$meeting->status] }}">
                            {{ $meeting->status_label }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $meeting->date->translatedFormat('l, d F Y') }}
                        </span>
                        @if($meeting->start_time)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $meeting->start_time->format('H:i') }}
                            @if($meeting->end_time) - {{ $meeting->end_time->format('H:i') }}@endif
                        </span>
                        @endif
                        @if($meeting->location)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $meeting->location }}
                        </span>
                        @endif
                    </div>

                    @if($meeting->theme)
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            {{ $meeting->theme }}
                        </span>
                    </div>
                    @endif

                    @if($meeting->description)
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $meeting->description }}</p>
                    @endif

                    @if($meeting->copiedFrom)
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                        Скопійовано з: {{ $meeting->copiedFrom->date->format('d.m.Y') }} - {{ $meeting->copiedFrom->title }}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="flex gap-4">
                <div class="text-center px-4 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $meeting->agendaItems->count() }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Пунктів плану</p>
                </div>
                <div class="text-center px-4 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $meeting->materials->count() }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Матеріалів</p>
                </div>
                <div class="text-center px-4 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $meeting->attendees->count() }}</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Учасників</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px overflow-x-auto no-scrollbar">
                <button @click="activeTab = 'agenda'" :class="activeTab === 'agenda' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                    План зустрічі
                    <span class="ml-1.5 px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded-full">{{ $meeting->agendaItems->count() }}</span>
                </button>
                <button @click="activeTab = 'materials'" :class="activeTab === 'materials' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                    Матеріали
                    <span class="ml-1.5 px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded-full">{{ $meeting->materials->count() }}</span>
                </button>
                <button @click="activeTab = 'attendees'" :class="activeTab === 'attendees' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                    Учасники
                    <span class="ml-1.5 px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded-full">{{ $meeting->attendees->count() }}</span>
                </button>
                <button @click="activeTab = 'notes'" :class="activeTab === 'notes' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors">
                    Нотатки
                </button>
            </nav>
        </div>

        <!-- Agenda Tab -->
        <div x-show="activeTab === 'agenda'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">План зустрічі</h3>
                <button @click="showAddAgenda = true" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати пункт
                </button>
            </div>

            @if($meeting->agendaItems->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <p>Немає пунктів плану</p>
                <button @click="showAddAgenda = true" class="mt-2 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                    Додати перший пункт
                </button>
            </div>
            @else
            <div class="space-y-2">
                @foreach($meeting->agendaItems as $item)
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl group">
                    <form method="POST" action="{{ route('meetings.agenda.toggle', $item) }}">
                        @csrf
                        <button type="submit" class="mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center {{ $item->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-500 hover:border-primary-500' }}">
                            @if($item->is_completed)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            @endif
                        </button>
                    </form>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white {{ $item->is_completed ? 'line-through text-gray-400' : '' }}">
                                    {{ $item->title }}
                                </p>
                                @if($item->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $item->description }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('meetings.agenda.destroy', $item) }}" onsubmit="return confirm('Видалити пункт?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                            @if($item->duration_minutes)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $item->duration_minutes }} хв
                            </span>
                            @endif
                            @if($item->responsible)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $item->responsible->full_name }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($meeting->agendaItems->whereNotNull('duration_minutes')->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Загальна тривалість: <strong>{{ $meeting->agendaItems->sum('duration_minutes') }} хв</strong>
                </span>
            </div>
            @endif
            @endif

            <!-- Add Agenda Form -->
            <div x-show="showAddAgenda" x-cloak class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                <form method="POST" action="{{ route('meetings.agenda.store', [$ministry, $meeting]) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" name="title" required placeholder="Назва пункту *"
                                   class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <textarea name="description" rows="2" placeholder="Опис (необов'язково)"
                                      class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"></textarea>
                        </div>
                        <div>
                            <input type="number" name="duration_minutes" min="1" placeholder="Тривалість (хв)"
                                   class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                        <div>
                            <select name="responsible_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                                <option value="">Відповідальний</option>
                                @foreach($ministry->members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showAddAgenda = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Скасувати</button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">Додати</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Materials Tab -->
        <div x-show="activeTab === 'materials'" x-cloak class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Матеріали</h3>
                <button @click="showAddMaterial = true" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати
                </button>
            </div>

            @if($meeting->materials->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p>Немає матеріалів</p>
            </div>
            @else
            <div class="grid gap-3">
                @foreach($meeting->materials as $material)
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl group">
                    <span class="text-2xl">{{ $material->type_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $material->title }}</p>
                        @if($material->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $material->description }}</p>
                        @endif
                        @if($material->type === 'link')
                        <a href="{{ $material->content }}" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                            {{ Str::limit($material->content, 50) }}
                        </a>
                        @elseif($material->type === 'note')
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $material->content }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('meetings.materials.destroy', $material) }}" onsubmit="return confirm('Видалити матеріал?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Add Material Form -->
            <div x-show="showAddMaterial" x-cloak class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                <form method="POST" action="{{ route('meetings.materials.store', [$ministry, $meeting]) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <input type="text" name="title" required placeholder="Назва матеріалу *"
                                   class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                        <div>
                            <select name="type" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                                <option value="link">Посилання</option>
                                <option value="note">Нотатка</option>
                                <option value="video">Відео</option>
                                <option value="audio">Аудіо</option>
                                <option value="document">Документ</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <textarea name="content" rows="3" required placeholder="URL посилання або текст нотатки *"
                                      class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <input type="text" name="description" placeholder="Опис (необов'язково)"
                                   class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showAddMaterial = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Скасувати</button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">Додати</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendees Tab -->
        <div x-show="activeTab === 'attendees'" x-cloak class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Учасники</h3>
                <div class="flex items-center gap-2">
                    @if($meeting->attendees->isNotEmpty())
                    <form method="POST" action="{{ route('meetings.attendees.mark-all', [$ministry, $meeting]) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors">
                            Всі присутні
                        </button>
                    </form>
                    @endif
                    @if($availableMembers->isNotEmpty())
                    <button @click="showAddAttendee = true" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Додати
                    </button>
                    @endif
                </div>
            </div>

            @if($meeting->attendees->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>Учасники будуть додані автоматично при створенні зустрічі</p>
            </div>
            @else
            <div class="grid gap-2">
                @foreach($meeting->attendees as $attendee)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        @if($attendee->person->photo)
                        <img src="{{ Storage::url($attendee->person->photo) }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ mb_substr($attendee->person->first_name, 0, 1) }}{{ mb_substr($attendee->person->last_name, 0, 1) }}</span>
                        </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $attendee->person->full_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('meetings.attendees.update', $attendee) }}" class="flex gap-1">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()"
                                    class="text-xs px-2 py-1 rounded-lg border-0 bg-transparent
                                    {{ $attendee->status === 'attended' ? 'text-green-600 dark:text-green-400' : '' }}
                                    {{ $attendee->status === 'absent' ? 'text-red-600 dark:text-red-400' : '' }}
                                    {{ $attendee->status === 'confirmed' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                    {{ $attendee->status === 'invited' ? 'text-gray-600 dark:text-gray-400' : '' }}">
                                <option value="invited" {{ $attendee->status === 'invited' ? 'selected' : '' }}>Запрошено</option>
                                <option value="confirmed" {{ $attendee->status === 'confirmed' ? 'selected' : '' }}>Підтверджено</option>
                                <option value="attended" {{ $attendee->status === 'attended' ? 'selected' : '' }}>Був присутній</option>
                                <option value="absent" {{ $attendee->status === 'absent' ? 'selected' : '' }}>Був відсутній</option>
                            </select>
                        </form>
                        <form method="POST" action="{{ route('meetings.attendees.destroy', $attendee) }}" onsubmit="return confirm('Видалити учасника?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Add Attendee Form -->
            <div x-show="showAddAttendee" x-cloak class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                <form method="POST" action="{{ route('meetings.attendees.store', [$ministry, $meeting]) }}">
                    @csrf
                    <select name="person_id" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">
                        <option value="">Оберіть учасника...</option>
                        @foreach($availableMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showAddAttendee = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Скасувати</button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">Додати</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notes Tab -->
        <div x-show="activeTab === 'notes'" x-cloak class="p-4">
            <form method="POST" action="{{ route('meetings.update', [$ministry, $meeting]) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="title" value="{{ $meeting->title }}">
                <input type="hidden" name="date" value="{{ $meeting->date->format('Y-m-d') }}">
                <input type="hidden" name="status" value="{{ $meeting->status }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Нотатки під час зустрічі</label>
                    <textarea name="notes" rows="5" placeholder="Записуйте важливі моменти..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ $meeting->notes }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Підсумок зустрічі</label>
                    <textarea name="summary" rows="5" placeholder="Короткий підсумок після завершення..."
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">{{ $meeting->summary }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                        Зберегти
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
