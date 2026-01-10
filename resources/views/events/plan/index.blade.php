@extends('layouts.app')

@section('title', 'План події - ' . $event->title)

@section('actions')
<div class="flex items-center gap-2">
    <a href="{{ route('events.plan.print', $event) }}" target="_blank"
       class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Друк
    </a>
    <a href="{{ route('events.show', $event) }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        До події
    </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="servicePlan()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}20;">
                    <svg class="w-7 h-7" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">План події</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $event->title }} &bull; {{ $event->ministry?->name ?? 'Без служіння' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->date?->translatedFormat('d F Y') ?? '-' }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $event->time?->format('H:i') ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Templates Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Шаблони
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak @click.away="open = false" x-transition
                     class="absolute left-0 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-20">
                    <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Застосувати шаблон</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Додасть типові пункти в план</p>
                    </div>
                    <div class="p-2 space-y-1">
                        <button type="button" @click="applyTemplate('sunday'); open = false"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <span class="font-medium">🙏 Воскресне служіння</span>
                            <span class="block text-xs text-gray-400">Прославлення, Проповідь, Пожертва, Оголошення</span>
                        </button>
                        <button type="button" @click="applyTemplate('prayer'); open = false"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <span class="font-medium">🙏 Молитвенне зібрання</span>
                            <span class="block text-xs text-gray-400">Прославлення, Молитва, Свідчення</span>
                        </button>
                        <button type="button" @click="applyTemplate('communion'); open = false"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <span class="font-medium">🍞 Служіння з Причастям</span>
                            <span class="block text-xs text-gray-400">Повне служіння з причастям</span>
                        </button>
                        <button type="button" @click="applyTemplate('baptism'); open = false"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <span class="font-medium">💧 Хрещення</span>
                            <span class="block text-xs text-gray-400">Служіння з хрещенням</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Add Dropdown -->
            <div x-data="{ open: false, selected: [] }" class="relative">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Bulk додати
                </button>
                <div x-show="open" x-cloak @click.away="open = false" x-transition
                     class="absolute left-0 mt-2 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-20">
                    <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Вибрати кілька типів</p>
                    </div>
                    <div class="p-3 space-y-2 max-h-64 overflow-y-auto">
                        @foreach(\App\Models\ServicePlanItem::typeLabels() as $type => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="{{ $type }}" x-model="selected"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                      style="background-color: {{ \App\Models\ServicePlanItem::typeColors()[$type] }}20; color: {{ \App\Models\ServicePlanItem::typeColors()[$type] }};">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="p-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="bulkAdd(selected); open = false; selected = []"
                                :disabled="selected.length === 0"
                                class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                            Додати вибрані (<span x-text="selected.length"></span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Text Parse Button -->
            <button type="button" @click="showTextParseModal = true"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                З тексту
            </button>

            <div class="flex-1"></div>

            <!-- Copy from Previous -->
            @if($previousServices->isNotEmpty())
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Копіювати план
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-20">
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Скопіювати з попереднього служіння</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Виберіть служіння для копіювання плану</p>
                        </div>
                        <div class="max-h-60 overflow-y-auto p-2">
                            @foreach($previousServices as $service)
                                <form method="POST" action="{{ route('events.plan.duplicate', [$event, $service]) }}">
                                    @csrf
                                    <input type="hidden" name="replace" value="1">
                                    <button type="submit"
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg flex items-center justify-between">
                                        <span>
                                            {{ $service->title }}
                                            <span class="text-gray-400 text-xs">({{ $service->date->format('d.m.Y') }})</span>
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $service->planItems->count() }} пунктів</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Plan Items -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <h2 class="font-semibold text-gray-900 dark:text-white">Пункти плану</h2>
            </div>
            <div class="flex items-center gap-4">
                @if($event->planItems->isNotEmpty())
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Загальний час:
                        @php
                            $totalMinutes = $event->planItems->sum('duration_minutes');
                            $hours = floor($totalMinutes / 60);
                            $mins = $totalMinutes % 60;
                        @endphp
                        @if($hours > 0){{ $hours }} год @endif{{ $mins }} хв
                    </span>
                @endif
                <button @click="showAddModal()" type="button"
                        class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Додати
                </button>
            </div>
        </div>

        @if($event->planItems->isNotEmpty())
            <!-- Timeline View -->
            <div id="plan-items" class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($event->planItems as $item)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                         data-item-id="{{ $item->id }}"
                         draggable="true"
                         @dragstart="handleDragStart($event, {{ $item->id }})"
                         @dragover.prevent
                         @drop="handleDrop($event, {{ $item->id }})">
                        <div class="flex items-start gap-4">
                            <!-- Drag Handle -->
                            <div class="cursor-move p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>

                            <!-- Time -->
                            <div class="w-28 flex-shrink-0 text-center">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '--:--' }}
                                </div>
                                @if($item->end_time)
                                    <div class="text-sm text-gray-400">
                                        {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}
                                    </div>
                                @endif
                                @if($item->duration_minutes)
                                    <div class="text-xs text-gray-400 mt-1">{{ $item->formatted_duration }}</div>
                                @endif
                            </div>

                            <!-- Type Icon & Color -->
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background-color: {{ $item->type_color }}20;">
                                <svg class="w-5 h-5" style="color: {{ $item->type_color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @switch($item->type)
                                        @case('worship')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                            @break
                                        @case('sermon')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            @break
                                        @case('prayer')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                            @break
                                        @case('offering')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            @break
                                        @case('announcement')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                            @break
                                        @case('testimony')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @break
                                        @case('baptism')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            @break
                                        @case('communion')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.5458C21 17.2203 19.8014 18.7292 18.1962 19.3478C17.2017 19.7317 16 20 16 20C16 20 14.5 20 13 19.5C11.5 19 10 18 10 18L7.5 16L10 13L12 14.5L14 16L16.5 14.5L19 12C19 12 20 13 20.5 14C21 15 21 15.5458 21 15.5458Z"/>
                                            @break
                                        @default
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    @endswitch
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $item->title }}</h3>
                                    @if($item->type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background-color: {{ $item->type_color }}20; color: {{ $item->type_color }};">
                                            {{ $item->type_label }}
                                        </span>
                                    @endif
                                    @if($item->status === 'confirmed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                            Підтверджено
                                        </span>
                                    @endif
                                </div>

                                @if($item->description)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                @endif

                                @if($item->responsible_display)
                                    <div class="mt-2 flex items-center gap-2">
                                        @if($item->responsible && $item->responsible->photo)
                                            <img src="{{ Storage::url($item->responsible->photo) }}" class="w-6 h-6 rounded-full object-cover">
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $item->responsible_display }}</span>
                                    </div>
                                @endif

                                @if($item->notes)
                                    <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                        <p class="text-xs text-yellow-700 dark:text-yellow-300 whitespace-pre-line">{{ $item->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editItem({{ $item->id }})"
                                        class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('events.plan.destroy', [$event, $item]) }}"
                                      onsubmit="return confirm('Видалити цей пункт?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Inline Add Row -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                <form @submit.prevent="inlineAdd()" class="flex items-center gap-3">
                    <select x-model="inlineForm.type"
                            class="w-40 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">Оберіть тип</option>
                        @foreach(\App\Models\ServicePlanItem::typeLabels() as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" x-model="inlineForm.title"
                           placeholder="або введіть назву вручну"
                           @keydown.enter.prevent="inlineAdd()"
                           class="flex-1 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit" :disabled="!inlineForm.type && !inlineForm.title"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                        Додати
                    </button>
                </form>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 mb-6">План події порожній</p>

                <!-- Inline Add for Empty State -->
                <div class="max-w-xl mx-auto bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-4">
                    <form @submit.prevent="inlineAdd()" class="flex items-center gap-3">
                        <select x-model="inlineForm.type"
                                class="w-40 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Оберіть тип</option>
                            @foreach(\App\Models\ServicePlanItem::typeLabels() as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" x-model="inlineForm.title"
                               placeholder="або введіть назву вручну"
                               @keydown.enter.prevent="inlineAdd()"
                               class="flex-1 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <button type="submit" :disabled="!inlineForm.type && !inlineForm.title"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 text-white text-sm font-medium rounded-lg transition-colors">
                            Додати
                        </button>
                    </form>
                </div>

                <p class="text-sm text-gray-400 dark:text-gray-500">або використайте шаблон чи копіювання з попередньої події</p>
            </div>
        @endif
    </div>

    <!-- Text Parse Modal -->
    <div x-show="showTextParseModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showTextParseModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full"
                 @click.away="showTextParseModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додати пункти з тексту</h3>
                    <button @click="showTextParseModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Введіть план у вільному форматі. Кожен рядок - окремий пункт. Можна вказувати час, тривалість, відповідальних.
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-4 text-xs text-gray-500 dark:text-gray-400">
                        <p class="font-medium mb-1">Приклади форматів:</p>
                        <pre class="whitespace-pre-wrap">10:00 Прославлення (30 хв) - Ненсі
10:30 Оголошення
Проповідь - Пастор Іван
Молитва</pre>
                    </div>
                    <textarea x-model="parseText" rows="10"
                              placeholder="Введіть план..."
                              class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent font-mono text-sm"></textarea>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="showTextParseModal = false"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Скасувати
                        </button>
                        <button type="button" @click="parseAndAdd()"
                                :disabled="!parseText.trim()"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 dark:disabled:bg-gray-600 text-white font-medium rounded-xl transition-colors">
                            Додати пункти
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden"
                 @click.away="closeModal()"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Редагувати пункт' : 'Новий пункт'"></h3>
                    <button @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="p-6 space-y-4 overflow-y-auto max-h-[calc(90vh-120px)]">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип</label>
                        <select name="type" x-model="formData.type"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Оберіть тип</option>
                            @foreach(\App\Models\ServicePlanItem::typeLabels() as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва (якщо інша)</label>
                        <input type="text" name="title" x-model="formData.title"
                               placeholder="Або введіть свою назву"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Відповідальний</label>
                        <input type="text" name="responsible_names" x-model="formData.responsible_names"
                               placeholder="Ім'я або імена через кому"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час початку</label>
                            <input type="time" name="start_time" x-model="formData.start_time"
                                   class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Час закінчення</label>
                            <input type="time" name="end_time" x-model="formData.end_time"
                                   class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нотатки</label>
                        <textarea name="notes" x-model="formData.notes" rows="2"
                                  placeholder="Додаткова інформація"
                                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                    </div>

                    <input type="hidden" name="status" value="planned">

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Скасувати
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                            <span x-text="editingId ? 'Зберегти' : 'Додати'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function servicePlan() {
    return {
        showModal: false,
        showTextParseModal: false,
        editingId: null,
        draggedId: null,
        parseText: '',
        formData: {
            type: '',
            title: '',
            start_time: '{{ $event->time ? $event->time->format("H:i") : "10:00" }}',
            end_time: '',
            responsible_id: '',
            responsible_names: '',
            description: '',
            notes: '',
            status: 'planned'
        },
        inlineForm: {
            type: '',
            title: '',
            start_time: '{{ $event->time ? $event->time->format("H:i") : "10:00" }}',
            responsible_names: ''
        },

        get formAction() {
            if (this.editingId) {
                return `{{ url('events/' . $event->id . '/plan') }}/${this.editingId}`;
            }
            return '{{ route('events.plan.store', $event) }}';
        },

        showAddModal() {
            this.editingId = null;
            this.resetForm();
            this.showModal = true;
        },

        editItem(id) {
            this.editingId = id;
            fetch(`{{ url('events/' . $event->id . '/plan') }}/${id}/data`)
                .then(r => r.json())
                .then(data => {
                    this.formData = data;
                    this.showModal = true;
                });
        },

        closeModal() {
            this.showModal = false;
            this.editingId = null;
            this.resetForm();
        },

        resetForm() {
            this.formData = {
                type: '',
                title: '',
                start_time: '{{ $event->time ? $event->time->format("H:i") : "10:00" }}',
                end_time: '',
                responsible_id: '',
                responsible_names: '',
                description: '',
                notes: '',
                status: 'planned'
            };
        },

        // Inline add
        inlineAdd() {
            const typeLabels = @json(\App\Models\ServicePlanItem::typeLabels());
            let title = this.inlineForm.title.trim();
            let type = this.inlineForm.type;

            // If type selected but no title, use type label as title
            if (type && !title) {
                title = typeLabels[type] || type;
            }

            if (!title && !type) return;

            fetch('{{ route('events.plan.store', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    title: title,
                    type: type || null,
                    start_time: null,
                    responsible_names: null
                })
            })
            .then(r => {
                if (!r.ok) {
                    return r.text().then(text => {
                        console.error('Server error:', r.status, text);
                        alert('Помилка: ' + r.status + ' - перевірте консоль');
                        throw new Error('Server error');
                    });
                }
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else if (data.errors) {
                    alert('Помилка валідації: ' + Object.values(data.errors).flat().join(', '));
                }
            })
            .catch(err => console.error('Fetch error:', err));
        },

        // Apply template
        applyTemplate(template) {
            fetch('{{ route('events.plan.apply-template', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ template: template })
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => { console.error(err); alert('Помилка: ' + err.message); });
        },

        // Bulk add multiple types
        bulkAdd(types) {
            if (!types || types.length === 0) return;

            fetch('{{ route('events.plan.bulk-add', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ types: types })
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => { console.error(err); alert('Помилка: ' + err.message); });
        },

        // Parse text and add items
        parseAndAdd() {
            if (!this.parseText.trim()) return;

            fetch('{{ route('events.plan.parse-text', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ text: this.parseText })
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => { console.error(err); alert('Помилка: ' + err.message); });
        },

        quickAdd(type) {
            fetch('{{ route('events.plan.quick-add', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ type: type })
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => { console.error(err); alert('Помилка: ' + err.message); });
        },

        handleDragStart(e, id) {
            this.draggedId = id;
            e.dataTransfer.effectAllowed = 'move';
        },

        handleDrop(e, targetId) {
            if (this.draggedId === targetId) return;

            const items = [...document.querySelectorAll('[data-item-id]')].map(el => ({
                id: parseInt(el.dataset.itemId),
                sort_order: 0
            }));

            const draggedIndex = items.findIndex(i => i.id === this.draggedId);
            const targetIndex = items.findIndex(i => i.id === targetId);

            const [draggedItem] = items.splice(draggedIndex, 1);
            items.splice(targetIndex, 0, draggedItem);

            items.forEach((item, index) => {
                item.sort_order = index;
            });

            fetch('{{ route('events.plan.reorder', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ items: items })
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error: ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => { console.error(err); alert('Помилка: ' + err.message); });

            this.draggedId = null;
        }
    }
}
</script>
@endpush
@endsection
