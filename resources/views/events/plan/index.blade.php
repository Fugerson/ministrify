@extends('layouts.app')

@section('title', 'План служіння — ' . $event->title)

@section('content')
<div x-data="servicePlanEditor()" x-cloak class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('events.show', $event) }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        План служіння
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ $event->title }} &middot; {{ $event->date->format('d.m.Y') }}
                        @if($event->time)
                            о {{ $event->time->format('H:i') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span x-show="totalDuration > 0"
                      class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-sm font-medium">
                    Тривалість: <span x-text="totalDuration"></span> хв
                </span>
                <a href="{{ route('events.plan.print', $event) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Друк
                </a>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Quick Add Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    Швидке додавання:
                </span>
                <template x-for="type in Object.keys(typeLabels)" :key="type">
                    <button @click="quickAdd(type)"
                            :disabled="loading"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg text-sm transition-colors">
                        <span class="w-2 h-2 rounded-full" :style="`background-color: ${typeColors[type]}`"></span>
                        <span class="text-gray-700 dark:text-gray-200" x-text="typeLabels[type]"></span>
                    </button>
                </template>
            </div>

            <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>

            {{-- Templates Dropdown --}}
            <div x-data="{ open: false, templates: [] }" @click.away="open = false" class="relative">
                <button @click="open = !open; if (open && templates.length === 0) { templates = await loadTemplates(); }"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Шаблони
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">

                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Вбудовані шаблони
                        </p>
                    </div>

                    <button @click="applyTemplate('sunday'); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Недільне служіння
                    </button>
                    <button @click="applyTemplate('prayer'); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Молитовне служіння
                    </button>
                    <button @click="applyTemplate('communion'); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Причастя
                    </button>
                    <button @click="applyTemplate('baptism'); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Хрещення
                    </button>

                    <template x-if="templates.length > 0">
                        <div>
                            <div class="px-3 py-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Збережені шаблони
                                </p>
                            </div>
                            <template x-for="template in templates" :key="template.id">
                                <button @click="applyCustomTemplate(template.id); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        x-text="template.name">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Duplicate Dropdown --}}
            @if($previousServices->count() > 0)
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Дублювати з
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50 max-h-80 overflow-y-auto">

                    @foreach($previousServices as $service)
                    <button @click="duplicateFrom({{ $service->id }}); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="font-medium">{{ $service->title }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $service->date->format('d.m.Y') }}</div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Parse Text Button --}}
            <button @click="showParseModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/50 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Вставити текст
            </button>
        </div>
    </div>

    {{-- Plan Items List --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <template x-if="items.length === 0">
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Почніть додавати пункти плану
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Використайте швидке додавання, шаблони або форму нижче
                </p>
            </div>
        </template>

        <template x-if="items.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-4"></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Час</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Назва</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Тип</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Відповідальний</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                            <th class="w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(item, index) in items" :key="item.id">
                            <tr draggable="true"
                                @dragstart="dragStart(index, $event)"
                                @dragover.prevent="dragOver(index, $event)"
                                @drop="drop(index, $event)"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-move">

                                {{-- Drag Handle --}}
                                <td class="px-2 py-3">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </td>

                                {{-- Color Bar --}}
                                <td class="py-3">
                                    <div class="w-1 h-8 rounded-full" :style="`background-color: ${typeColors[item.type]}`"></div>
                                </td>

                                {{-- Time --}}
                                <td class="px-4 py-3">
                                    <input type="time"
                                           :value="item.start_time || ''"
                                           @change="updateField(item.id, 'start_time', $event.target.value)"
                                           class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </td>

                                {{-- Title --}}
                                <td class="px-4 py-3">
                                    <input type="text"
                                           :value="item.title"
                                           @change="updateField(item.id, 'title', $event.target.value)"
                                           class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Назва пункту">
                                </td>

                                {{-- Type --}}
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                          :style="`background-color: ${typeColors[item.type]}20; color: ${typeColors[item.type]}`">
                                        <span class="w-1.5 h-1.5 rounded-full" :style="`background-color: ${typeColors[item.type]}`"></span>
                                        <span x-text="typeLabels[item.type]"></span>
                                    </span>
                                </td>

                                {{-- Responsible --}}
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900 dark:text-white"
                                          x-text="item.responsible?.full_name || item.responsible_names || '—'">
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3">
                                    <button @click="cycleStatus(item.id, item.status)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors"
                                            :class="{
                                                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': item.status === 'planned',
                                                'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': item.status === 'confirmed',
                                                'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': item.status === 'declined',
                                                'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': item.status === 'completed'
                                            }">
                                        <span x-text="{
                                            'planned': 'Заплановано',
                                            'confirmed': 'Підтверджено',
                                            'declined': 'Відхилено',
                                            'completed': 'Виконано'
                                        }[item.status]"></span>
                                    </button>
                                </td>

                                {{-- Delete --}}
                                <td class="px-4 py-3">
                                    <button @click="deleteItem(item.id)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- Add New Item Form --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Додати новий пункт</h3>
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="time"
                   x-model="newItem.start_time"
                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <input type="text"
                   x-model="newItem.title"
                   @keydown.enter="addItem"
                   placeholder="Назва пункту"
                   class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <select x-model="newItem.type"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <template x-for="(label, type) in typeLabels" :key="type">
                    <option :value="type" x-text="label"></option>
                </template>
            </select>

            <button @click="addItem"
                    :disabled="!newItem.title || loading"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                Додати
            </button>
        </div>
    </div>

    {{-- Parse Text Modal --}}
    <div x-show="showParseModal"
         x-cloak
         @click.self="showParseModal = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-2xl w-full max-h-[90vh] overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Вставити текст плану
                </h3>
                <button @click="showParseModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Вставте текст плану служіння
                    </label>
                    <textarea x-model="parseText"
                              rows="12"
                              placeholder="Приклади форматів:&#10;&#10;10:00 Прославлення (30 хв) - Іван Петренко&#10;10:30 Оголошення - Марія&#10;10:35 Проповідь (45 хв)&#10;11:20 Молитва&#10;&#10;Або:&#10;Прославлення - 10:00&#10;Проповідь - Іван - 10:30&#10;Пожертва"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"></textarea>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Підказка:</strong> Система автоматично розпізнає час, назви, тривалість та відповідальних осіб.
                        Підтримуються різні формати запису.
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button @click="showParseModal = false"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Скасувати
                </button>
                <button @click="parseTextSubmit"
                        :disabled="!parseText.trim() || loading"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors">
                    Розпізнати
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function servicePlanEditor() {
    return {
        items: @json($event->planItems->sortBy('sort_order')->values()),
        loading: false,
        showParseModal: false,
        parseText: '',
        newItem: {
            title: '',
            type: 'other',
            start_time: ''
        },
        draggedIndex: null,

        typeLabels: {
            worship: 'Прославлення',
            sermon: 'Проповідь',
            announcement: 'Оголошення',
            prayer: 'Молитва',
            offering: 'Пожертва',
            testimony: 'Свідчення',
            baptism: 'Хрещення',
            communion: 'Причастя',
            child_blessing: 'Дитяче благословення',
            special: 'Особливий номер',
            other: 'Інше'
        },

        typeColors: {
            worship: '#8b5cf6',
            sermon: '#3b82f6',
            announcement: '#f59e0b',
            prayer: '#10b981',
            offering: '#ef4444',
            testimony: '#06b6d4',
            baptism: '#0ea5e9',
            communion: '#7c3aed',
            child_blessing: '#ec4899',
            special: '#f97316',
            other: '#6b7280'
        },

        get totalDuration() {
            let total = 0;
            this.items.forEach(item => {
                if (item.start_time && item.end_time) {
                    const [startH, startM] = item.start_time.split(':').map(Number);
                    const [endH, endM] = item.end_time.split(':').map(Number);
                    const duration = (endH * 60 + endM) - (startH * 60 + startM);
                    if (duration > 0) total += duration;
                }
            });
            return total;
        },

        async quickAdd(type) {
            if (this.loading) return;
            this.loading = true;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/quick-add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ type })
                });

                const data = await response.json();

                if (data.success && data.item) {
                    this.items.push(data.item);
                } else {
                    alert('Помилка при додаванні пункту');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при додаванні пункту');
            } finally {
                this.loading = false;
            }
        },

        async addItem() {
            if (!this.newItem.title.trim() || this.loading) return;
            this.loading = true;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.newItem)
                });

                const data = await response.json();

                if (data.success && data.item) {
                    this.items.push(data.item);
                    this.newItem = { title: '', type: 'other', start_time: '' };
                } else {
                    alert('Помилка при додаванні пункту');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при додаванні пункту');
            } finally {
                this.loading = false;
            }
        },

        async updateField(itemId, field, value) {
            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ [field]: value })
                });

                const data = await response.json();

                if (data.success && data.item) {
                    const index = this.items.findIndex(i => i.id === itemId);
                    if (index !== -1) {
                        this.items[index] = data.item;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при оновленні');
            }
        },

        async deleteItem(itemId) {
            if (!confirm('Видалити цей пункт плану?')) return;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.items = this.items.filter(i => i.id !== itemId);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при видаленні');
            }
        },

        dragStart(index, event) {
            this.draggedIndex = index;
            event.dataTransfer.effectAllowed = 'move';
        },

        dragOver(index, event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        },

        async drop(index, event) {
            event.preventDefault();

            if (this.draggedIndex === null || this.draggedIndex === index) return;

            const draggedItem = this.items[this.draggedIndex];
            this.items.splice(this.draggedIndex, 1);
            this.items.splice(index, 0, draggedItem);

            this.items.forEach((item, idx) => {
                item.sort_order = idx;
            });

            await this.reorder();
            this.draggedIndex = null;
        },

        async reorder() {
            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        items: this.items.map(item => ({
                            id: item.id,
                            sort_order: item.sort_order
                        }))
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    alert('Помилка при зміні порядку');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при зміні порядку');
            }
        },

        async applyTemplate(template) {
            if (!confirm('Застосувати шаблон? Поточні пункти будуть замінені.')) return;
            this.loading = true;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/apply-template`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ template })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Помилка при застосуванні шаблону');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при застосуванні шаблону');
            } finally {
                this.loading = false;
            }
        },

        async applyCustomTemplate(templateId) {
            if (!confirm('Застосувати збережений шаблон? Поточні пункти будуть замінені.')) return;
            this.loading = true;

            try {
                const response = await fetch(`/service-plan-templates/apply/{{ $event->id }}/${templateId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Помилка при застосуванні шаблону');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при застосуванні шаблону');
            } finally {
                this.loading = false;
            }
        },

        async duplicateFrom(sourceId) {
            if (!confirm('Дублювати план з іншого служіння? Поточні пункти будуть замінені.')) return;
            this.loading = true;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/duplicate/${sourceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ replace: true })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Помилка при дублюванні');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при дублюванні');
            } finally {
                this.loading = false;
            }
        },

        async parseTextSubmit() {
            if (!this.parseText.trim() || this.loading) return;
            this.loading = true;

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/parse-text`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ text: this.parseText })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Помилка при розпізнаванні тексту');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при розпізнаванні тексту');
            } finally {
                this.loading = false;
            }
        },

        async cycleStatus(itemId, currentStatus) {
            const statuses = ['planned', 'confirmed', 'completed'];
            const currentIndex = statuses.indexOf(currentStatus);
            const nextStatus = statuses[(currentIndex + 1) % statuses.length];

            try {
                const response = await fetch(`/events/{{ $event->id }}/plan/${itemId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: nextStatus })
                });

                const data = await response.json();

                if (data.success && data.item) {
                    const index = this.items.findIndex(i => i.id === itemId);
                    if (index !== -1) {
                        this.items[index] = data.item;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Помилка при оновленні статусу');
            }
        },

        async loadTemplates() {
            try {
                const response = await fetch('/service-plan-templates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                return data.templates || [];
            } catch (error) {
                console.error('Error loading templates:', error);
                return [];
            }
        }
    };
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
