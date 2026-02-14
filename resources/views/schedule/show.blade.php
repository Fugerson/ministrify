@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@endsection

@php
    $ministriesData = $ministries->map(function($m) {
        return ['id' => $m->id, 'name' => $m->name, 'color' => $m->color];
    })->values();

    $availablePeopleData = $event->responsibilities->map(function($r) {
        return ['id' => $r->person_id, 'name' => $r->person?->full_name ?? 'Невідомий'];
    })->unique('id')->values();

    // Fallback for songs autocomplete
    if (!isset($songsForAutocomplete)) {
        $songsForAutocomplete = \App\Models\Song::where('church_id', $event->church_id)
            ->orderBy('title')
            ->get(['id', 'title', 'artist', 'key']);
    }
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header (Editable) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="eventEditor()">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center flex-1">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0"
                     :style="'background-color: ' + (ministryColor || '#3b82f6') + '20'">
                    <svg class="w-7 h-7" :style="'color: ' + (ministryColor || '#3b82f6')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    @if($canEdit)
                    <input type="text" x-model="title" @change="saveField('title', title)"
                           class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 w-full p-0 pb-1">
                    @else
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="title"></h1>
                    @endif
                    <div class="mt-1 relative" x-data="{ showDropdown: false }">
                        <!-- Ministry Badge -->
                        @if($canEdit)
                        <button type="button" @click="showDropdown = !showDropdown"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-all hover:opacity-80"
                                :style="'background-color: ' + (ministryColor || '#6b7280') + '20; color: ' + (ministryColor || '#6b7280') + '; border: 1px solid ' + (ministryColor || '#6b7280') + '40'">
                            <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (ministryColor || '#6b7280')"></span>
                            <span x-text="getMinistryName()"></span>
                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Dropdown -->
                        <div x-show="showDropdown" x-cloak @click.away="showDropdown = false"
                             class="absolute left-0 top-full mt-1 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[180px]">
                            <template x-for="ministry in ministries" :key="ministry.id">
                                <button type="button"
                                        @click="ministryId = ministry.id; ministryColor = ministry.color; saveMinistry(); showDropdown = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" :style="'background-color: ' + ministry.color"></span>
                                    <span x-text="ministry.name" class="text-gray-700 dark:text-gray-300"></span>
                                </button>
                            </template>
                        </div>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                              :style="'background-color: ' + (ministryColor || '#6b7280') + '20; color: ' + (ministryColor || '#6b7280') + '; border: 1px solid ' + (ministryColor || '#6b7280') + '40'">
                            <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (ministryColor || '#6b7280')"></span>
                            <span x-text="getMinistryName()"></span>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right shrink-0">
                @if($canEdit)
                <input type="date" x-model="date" @change="saveField('date', date)"
                       class="text-xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 p-0 pb-1 text-right cursor-pointer">
                <input type="time" x-model="time" @change="saveField('time', time)"
                       class="text-gray-500 dark:text-gray-400 bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 p-0 pb-1 text-right block ml-auto cursor-pointer">
                @else
                <span class="text-xl font-bold text-gray-900 dark:text-white" x-text="date"></span>
                <span class="text-gray-500 dark:text-gray-400 block text-right" x-text="time || ''"></span>
                @endif
                @if($event->google_event_id)
                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google Calendar
                    </span>
                @endif
            </div>
        </div>
        <!-- Notes -->
        <div class="mt-4">
            @if($canEdit)
            <textarea x-model="notes" @change="saveField('notes', notes)"
                      placeholder="Додати примітки..."
                      rows="2"
                      class="w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
            @elseif($event->notes)
            <p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-xl px-3 py-2" x-text="notes"></p>
            @endif
        </div>

        <!-- Toggles -->
        <div class="mt-4 flex items-center gap-6">
            @if($canEdit)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="isService" @change="saveField('is_service', isService); $store.event.isService = isService"
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Подія з планом</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="isSundayService" @change="saveField('service_type', isSundayService ? 'sunday_service' : null)"
                       class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Недільне служіння</span>
            </label>
            @if($currentChurch->attendance_enabled)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="trackAttendance" @change="saveField('track_attendance', trackAttendance); $store.event.trackAttendance = trackAttendance"
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Відвідуваність</span>
            </label>
            @endif
            @else
            @if($event->is_service)<span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Подія з планом</span>@endif
            @if($event->service_type === 'sunday_service')<span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Недільне служіння</span>@endif
            @if($event->track_attendance)<span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1.5"><svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Відвідуваність</span>@endif
            @endif
        </div>

        <!-- Quick stats -->
        <div class="mt-4 flex items-center gap-4 text-sm">
            @php
                $confirmedResp = $event->responsibilities->where('status', 'confirmed')->count();
                $totalResp = $event->responsibilities->count();
            @endphp
            @if($totalResp > 0)
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>{{ $confirmedResp }}/{{ $totalResp }} відповідальностей</span>
            </div>
            @endif
            @if($event->checklist && $event->checklist->items->count() > 0)
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>Чеклист: {{ $event->checklist->progress }}%</span>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <!-- Main content (full width) -->
        <div class="space-y-6">
            <!-- План події -->
            <div x-show="$store.event.isService" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700" x-data="planEditor()">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h2 class="font-semibold text-gray-900 dark:text-white">План події</h2>
                        </div>
                        <div class="flex items-center gap-2" x-data="planTemplatesManager()">
                            {{-- Apply Template Dropdown --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg flex items-center gap-1">
                                    📋 Шаблон
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false"
                                     class="absolute right-0 mt-1 w-48 sm:w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20">
                                    {{-- Custom templates --}}
                                    <div class="p-2">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Мої шаблони</p>
                                        <template x-if="customTemplates.length === 0">
                                            <p class="text-xs text-gray-400 dark:text-gray-500 py-1">Немає шаблонів</p>
                                        </template>
                                        <template x-for="tpl in customTemplates" :key="tpl.id">
                                            <div class="flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                                                <button type="button" @click="applyCustomTemplate(tpl.id); open = false"
                                                        class="flex-1 text-left px-2 py-1.5 text-sm text-gray-700 dark:text-gray-300">
                                                    <span x-text="tpl.name"></span>
                                                    <span class="text-xs text-gray-400" x-text="'(' + tpl.items_count + ')'"></span>
                                                </button>
                                                <button type="button" @click.stop="deleteTemplate(tpl.id)"
                                                        class="p-1 text-gray-400 hover:text-red-500" title="Видалити">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Save as Template Button --}}
                            <button type="button" @click="showSaveModal = true"
                                    class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Зберегти як шаблон">
                                💾 Зберегти
                            </button>

                            <a href="{{ route('events.plan.print', $event) }}" target="_blank"
                               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Друк">
                                🖨️ Друк
                            </a>

                            {{-- Save Template Modal --}}
                            <div x-show="showSaveModal" x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                 @keydown.escape.window="showSaveModal = false">
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-4" @click.outside="showSaveModal = false">
                                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Зберегти план як шаблон</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва шаблону</label>
                                            <input type="text" x-model="templateName" placeholder="Наприклад: Недільне богослужіння"
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" id="includeResponsible" x-model="includeResponsible"
                                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            <label for="includeResponsible" class="text-sm text-gray-700 dark:text-gray-300">
                                                Включити відповідальних
                                            </label>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                                        <button type="button" @click="showSaveModal = false"
                                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                            Скасувати
                                        </button>
                                        <button type="button" @click="saveAsTemplate()"
                                                :disabled="!templateName.trim()"
                                                class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                            Зберегти
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto" style="min-height: 300px;">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-1 py-4" style="width: 30px;"></th>
                                <th class="px-3 py-4 text-left" style="width: 70px;">Час</th>
                                <th class="px-3 py-4 text-left" style="width: 40%;">Що відбувається</th>
                                <th class="px-3 py-4 text-left" style="width: 1px;">Відповідальний</th>
                                <th class="px-3 py-4 text-left" style="width: 25%;">Коментарі</th>
                                <th class="px-2 py-4" style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($event->planItems->sortBy('sort_order') as $item)
                                <tr class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group" data-id="{{ $item->id }}">
                                    {{-- Drag handle --}}
                                    <td class="px-1 py-3 cursor-grab active:cursor-grabbing drag-handle">
                                        <svg class="w-4 h-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                                            <circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/>
                                            <circle cx="9" cy="15" r="1.5"/><circle cx="15" cy="15" r="1.5"/>
                                            <circle cx="9" cy="20" r="1.5"/><circle cx="15" cy="20" r="1.5"/>
                                        </svg>
                                    </td>
                                    {{-- Час --}}
                                    <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700">
                                        <input type="time"
                                               value="{{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}"
                                               @change="updateField({{ $item->id }}, 'start_time', $event.target.value)"
                                               class="min-w-[5.5rem] px-2 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
                                    </td>
                                    {{-- Що відбувається - єдиний підхід з інлайн посиланнями на пісні --}}
                                    @php
                                        // Конвертуємо старий формат (song_id) в новий ([song-ID])
                                        $displayTitle = $item->title ?? '';
                                        if ($item->song_id && $item->song) {
                                            // Якщо є прив'язана пісня і title НЕ містить її вже, додаємо [song-ID] на початок
                                            if (!str_contains($displayTitle, '[song-' . $item->song_id . ']')) {
                                                $displayTitle = '[song-' . $item->song_id . '] ' . $displayTitle;
                                            }
                                        }
                                        $displayTitle = trim($displayTitle);
                                    @endphp
                                    <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top">
                                        <div class="relative" x-data="titleEditor({{ $item->id }}, {{ Js::from($displayTitle) }}, {{ $item->song_id ?? 'null' }})">
                                            {{-- Display mode --}}
                                            <div x-show="!editing" @click="startEditing()" class="cursor-text min-h-[1.5rem] px-1 py-1 text-sm text-gray-900 dark:text-white break-words" x-html="renderWithSongLinks(title)"></div>
                                            {{-- Edit mode --}}
                                            <div x-show="editing" class="relative">
                                                <textarea x-ref="input" x-model="title"
                                                          @input="checkForSongTrigger($event.target.value); $el.style.height='auto'; $el.style.height=$el.scrollHeight+'px'"
                                                          @blur="setTimeout(() => { if(!showSongs) { saveTitle(); editing = false; } }, 150)"
                                                          @keydown.escape="editing = false; showSongs = false"
                                                          @keydown.arrow-down.prevent="if(showSongs) songIndex = Math.min(songIndex + 1, filteredSongs().length - 1)"
                                                          @keydown.arrow-up.prevent="if(showSongs) songIndex = Math.max(songIndex - 1, 0)"
                                                          @keydown.enter.prevent="if(showSongs && filteredSongs().length) { insertSongLink(filteredSongs()[songIndex]); } else { saveTitle(); editing = false; }"
                                                          placeholder="Текст... (введіть song- для пошуку пісні)"
                                                          rows="1"
                                                          class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-primary-300 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                                                          style="word-wrap: break-word; overflow-wrap: break-word;"></textarea>
                                                <div x-show="showSongs" x-transition
                                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                                    <template x-if="SONGS_DATA.length === 0">
                                                        <div class="px-3 py-3 text-center text-gray-500 dark:text-gray-400 text-sm">
                                                            Команда прославлення ще не обрала пісні.
                                                            @if($event->service_type === 'sunday_service')
                                                                @php
                                                                    $worshipMinistry = \App\Models\Ministry::where('church_id', $event->church_id)->where('is_worship_ministry', true)->first();
                                                                @endphp
                                                                @if($worshipMinistry)
                                                                    <a href="{{ route('ministries.show', ['ministry' => $worshipMinistry, 'tab' => 'schedule']) }}" class="text-primary-600 hover:underline">Обрати пісні</a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </template>
                                                    <template x-for="(song, index) in filteredSongs()" :key="song.id">
                                                        <button type="button" @mousedown.prevent="insertSongLink(song)"
                                                                :class="{'bg-primary-50 dark:bg-primary-900/30': songIndex === index}"
                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                                                            <span x-text="song.title"></span>
                                                            <span x-show="song.key" class="px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs rounded font-mono" x-text="song.key"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Відповідальний (multiple people with statuses) --}}
                                    @php
                                        // Parse existing responsible people with statuses
                                        $existingPeople = [];
                                        $statuses = $item->responsible_statuses ?? [];

                                        if ($item->responsible_names) {
                                            $names = array_map('trim', explode(',', $item->responsible_names));
                                            foreach ($names as $name) {
                                                $person = $allPeople->first(fn($p) => $p->full_name === $name);
                                                $personId = $person?->id;
                                                $existingPeople[] = [
                                                    'id' => $personId,
                                                    'name' => $name,
                                                    'hasTelegram' => (bool)($person?->telegram_chat_id),
                                                    'status' => $personId ? ($statuses[$personId] ?? null) : null
                                                ];
                                            }
                                        } elseif ($item->responsible_id && $item->responsible) {
                                            $existingPeople[] = [
                                                'id' => $item->responsible->id,
                                                'name' => $item->responsible->full_name,
                                                'hasTelegram' => (bool)$item->responsible->telegram_chat_id,
                                                'status' => $statuses[$item->responsible->id] ?? null
                                            ];
                                        }

                                        // Calculate stats
                                        $totalPeople = count($existingPeople);
                                        $confirmedCount = count(array_filter($existingPeople, fn($p) => ($p['status'] ?? null) === 'confirmed'));
                                        $pendingCount = count(array_filter($existingPeople, fn($p) => ($p['status'] ?? null) === 'pending'));
                                        $declinedCount = count(array_filter($existingPeople, fn($p) => ($p['status'] ?? null) === 'declined'));
                                        $notAskedCount = count(array_filter($existingPeople, fn($p) => ($p['status'] ?? null) === null && $p['hasTelegram']));
                                    @endphp
                                    <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top whitespace-nowrap"
                                        x-data="responsibleEditor({{ $item->id }}, {{ json_encode($existingPeople) }})">
                                        <div class="flex flex-col gap-1">
                                            {{-- Selected people as tags --}}
                                            <template x-for="(person, index) in people" :key="index">
                                                <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg" :class="getTagClass(person.status)">
                                                    {{-- Status icon --}}
                                                    <template x-if="person.status === 'confirmed'">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="person.status === 'declined'">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="person.status === 'pending'">
                                                        <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="4"/>
                                                        </svg>
                                                    </template>

                                                    <span x-text="person.name"></span>

                                                    {{-- Запитати button (show if not asked or declined) --}}
                                                    <button type="button"
                                                            x-show="person.hasTelegram && (!person.status || person.status === 'declined')"
                                                            @click="askPerson(person, index)"
                                                            class="text-blue-500 hover:text-blue-700"
                                                            :title="person.status === 'declined' ? 'Запитати ще раз' : 'Запитати в Telegram'">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                        </svg>
                                                    </button>

                                                    {{-- Нагадати button (show if pending) --}}
                                                    <button type="button"
                                                            x-show="person.hasTelegram && person.status === 'pending'"
                                                            @click="askPerson(person, index)"
                                                            class="text-yellow-600 hover:text-yellow-700"
                                                            title="Нагадати">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                        </svg>
                                                    </button>

                                                    {{-- No telegram indicator --}}
                                                    <span x-show="person.id && !person.hasTelegram && !person.status" class="text-gray-400" title="Немає Telegram">
                                                        <svg class="w-3 h-3 opacity-50" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                        </svg>
                                                    </span>

                                                    {{-- Remove button --}}
                                                    <button type="button" @click="removePerson(index)" class="text-gray-400 hover:text-red-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            {{-- Add person button --}}
                                            <div class="relative">
                                                <button type="button"
                                                        @click="open = !open"
                                                        class="inline-flex items-center gap-1 text-xs px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span x-show="people.length === 0">Додати</span>
                                                </button>

                                                {{-- Dropdown --}}
                                                <div x-show="open" x-cloak @click.outside="open = false"
                                                     class="absolute z-50 left-0 mt-1 w-48 sm:w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                                        <input type="text" x-model="search" placeholder="Пошук..."
                                                               class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    </div>
                                                    <div class="max-h-48 overflow-y-auto">
                                                        <template x-for="person in filteredPeople" :key="person.id">
                                                            <button type="button"
                                                                    @click="addPerson(person.id, person.name, person.hasTelegram)"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                                <template x-if="person.hasTelegram">
                                                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                                    </svg>
                                                                </template>
                                                                <template x-if="!person.hasTelegram">
                                                                    <span class="w-4 h-4"></span>
                                                                </template>
                                                                <span x-text="person.name"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Overall stats (show if multiple people) --}}
                                            <template x-if="people.length > 1 && people.some(p => p.status)">
                                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1"
                                                      x-text="getStats().confirmed + '/' + getStats().total"></span>
                                            </template>
                                        </div>
                                    </td>
                                    {{-- Коментарі --}}
                                    <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top">
                                        <textarea placeholder="Примітки..."
                                                  @change="updateField({{ $item->id }}, 'notes', $event.target.value)"
                                                  rows="1"
                                                  class="w-full px-1 py-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                                                  style="word-wrap: break-word; overflow-wrap: break-word;"
                                                  x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">{{ $item->notes }}</textarea>
                                    </td>
                                    {{-- Actions --}}
                                    <td class="px-3 py-3 text-center">
                                        <button type="button"
                                                @click="deleteItem({{ $item->id }})"
                                                class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                                                title="Видалити">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-row">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Почніть додавати пункти плану нижче
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add new row --}}
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    <form @submit.prevent="addItem()" class="flex items-center gap-2">
                        <input type="time" x-model="newItem.start_time"
                               class="min-w-[6rem] px-2 py-2 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 cursor-pointer">
                        {{-- Title with song autocomplete --}}
                        <div class="flex-1 relative">
                            <input type="text" x-model="newItem.title"
                                   @input="checkForSongTrigger($event.target.value)"
                                   @focus="if(newItem.title.match(/song-/i)) showSongs = true"
                                   @keydown.escape="showSongs = false"
                                   @keydown.arrow-down.prevent="if(showSongs) songIndex = Math.min(songIndex + 1, filteredSongsForNew().length - 1)"
                                   @keydown.arrow-up.prevent="if(showSongs) songIndex = Math.max(songIndex - 1, 0)"
                                   @keydown.enter.prevent="if(showSongs && filteredSongsForNew().length) { selectSongForNew(filteredSongsForNew()[songIndex]); } else { $el.form.requestSubmit(); }"
                                   placeholder="Що відбувається... (song- для пісні)" required
                                   class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div x-show="showSongs" x-transition @click.away="showSongs = false"
                                 class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                <template x-if="SONGS_DATA.length === 0">
                                    <div class="px-3 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        Команда прославлення ще не обрала пісні.
                                        @if($event->service_type === 'sunday_service')
                                            @php
                                                $wm = \App\Models\Ministry::where('church_id', $event->church_id)->where('is_worship_ministry', true)->first();
                                            @endphp
                                            @if($wm)
                                                <a href="{{ route('ministries.show', ['ministry' => $wm, 'tab' => 'schedule']) }}" class="text-primary-600 hover:underline">Обрати пісні</a>
                                            @endif
                                        @endif
                                    </div>
                                </template>
                                <template x-if="SONGS_DATA.length > 0 && filteredSongsForNew().length === 0">
                                    <div class="px-3 py-3 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        Нічого не знайдено
                                    </div>
                                </template>
                                <template x-for="(song, index) in filteredSongsForNew()" :key="song.id">
                                    <button type="button" @mousedown.prevent="selectSongForNew(song)"
                                            :class="{'bg-primary-50 dark:bg-primary-900/30': songIndex === index}"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                                        <div>
                                            <span class="text-gray-900 dark:text-white" x-text="song.title"></span>
                                            <span x-show="song.artist" class="text-gray-500 dark:text-gray-400" x-text="' - ' + song.artist"></span>
                                        </div>
                                        <span x-show="song.key" class="px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs rounded font-mono" x-text="song.key"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="relative w-44">
                            <div class="flex flex-wrap items-center gap-1 min-h-[2.375rem] px-2 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer"
                                 @click="newResponsible.open = !newResponsible.open">
                                <template x-for="(person, index) in newResponsible.people" :key="index">
                                    <span class="inline-flex items-center gap-0.5 text-xs px-1.5 py-0.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded">
                                        <span x-text="person.name" class="truncate max-w-[5rem]"></span>
                                        <button type="button" @click.stop="removeNewResponsible(index)" class="text-gray-400 hover:text-red-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                </template>
                                <span x-show="newResponsible.people.length === 0" class="text-sm text-gray-400">Відповідальний</span>
                            </div>
                            <div x-show="newResponsible.open" x-cloak @click.outside="newResponsible.open = false"
                                 class="absolute z-50 left-0 bottom-full mb-1 w-48 sm:w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                    <input type="text" x-model="newResponsible.search" placeholder="Пошук..."
                                           @click.stop
                                           class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <template x-for="person in newFilteredPeople" :key="person.id">
                                        <button type="button"
                                                @click.stop="addNewResponsible(person.id, person.name)"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                            <span x-text="person.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <input type="text" x-model="newItem.notes" placeholder="Коментар"
                               class="w-32 px-2 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg font-medium">
                            + Додати
                        </button>
                    </form>
                </div>

                {{-- Status message --}}
                <div x-show="message" x-cloak x-transition
                     class="fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm"
                     :class="messageType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                    <span x-text="message"></span>
                </div>
            </div>

            {{-- Simple responsibility form for non-service events --}}
            <div x-show="!$store.event.isService" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Відповідальності</h2>
                <form method="POST" action="{{ route('events.responsibilities.store', $event) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Нова відповідальність"
                           class="flex-1 px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                    <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">
                        Додати
                    </button>
                </form>
            </div>

            <!-- Attendance Section -->
            @if($currentChurch->attendance_enabled)
            <div x-show="$store.event.trackAttendance" x-cloak class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700" x-data="attendanceManager()">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Відвідуваність</h2>
                            <span x-show="saving" class="text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 animate-spin inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span x-show="saved" x-transition class="text-xs text-green-600 dark:text-green-400">Збережено</span>
                            <span x-show="error" class="text-xs text-red-600 dark:text-red-400">Помилка!</span>
                        </div>
                        @php
                            $presentCount = $event->attendance?->records->where('present', true)->count() ?? 0;
                            $totalPeople = $allPeople->count();
                        @endphp
                        <span class="text-sm text-gray-500 dark:text-gray-400" x-text="attending.length + '/' + {{ $totalPeople }}">{{ $presentCount }}/{{ $totalPeople }}</span>
                    </div>
                </div>

                <div class="p-4">
                    @php
                        $presentIds = $event->attendance?->records->where('present', true)->pluck('person_id')->toArray() ?? [];
                    @endphp

                    <!-- Search -->
                    <div class="mb-4">
                        <input type="text" x-model="search" placeholder="Пошук..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <!-- People List -->
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($allPeople as $person)
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
                             x-show="!search || '{{ mb_strtolower($person->full_name) }}'.includes(search.toLowerCase())">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-primary-600 dark:text-primary-400 text-xs font-medium">
                                        {{ substr($person->first_name, 0, 1) }}{{ substr($person->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $person->full_name }}</span>
                            </div>
                            <button type="button"
                                    @click="toggleAttendance({{ $person->id }})"
                                    :class="attending.includes({{ $person->id }}) ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300'"
                                    class="p-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>

                    <!-- Guests count -->
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Гості</label>
                            <input type="number" x-model="guestsCount" min="0"
                                   @change="saveAttendance()"
                                   class="w-20 px-2 py-1 text-sm text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Всього присутніх:</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="attending.length + parseInt(guestsCount || 0)"></span>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
            <script>
            function attendanceManager() {
                return {
                    search: '',
                    attending: @json($presentIds),
                    guestsCount: {{ $event->attendance?->guests_count ?? 0 }},
                    saving: false,
                    saved: false,
                    error: false,

                    async toggleAttendance(personId) {
                        const index = this.attending.indexOf(personId);
                        if (index > -1) {
                            this.attending.splice(index, 1);
                        } else {
                            this.attending.push(personId);
                        }
                        await this.saveAttendance();
                    },

                    async saveAttendance() {
                        this.saving = true;
                        this.saved = false;
                        this.error = false;
                        try {
                            const response = await fetch('{{ route("events.attendance.save", $event) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    present: this.attending,
                                    guests_count: this.guestsCount
                                })
                            });
                            if (response.ok) {
                                this.saved = true;
                                setTimeout(() => this.saved = false, 2000);
                            } else {
                                this.error = true;
                                console.error('Save failed:', response.status);
                            }
                        } catch (error) {
                            this.error = true;
                            console.error('Error saving attendance:', error);
                        } finally {
                            this.saving = false;
                        }
                    }
                }
            }
            </script>
            @endpush
            @endif

        </div>

        <!-- Secondary content (grid for sidebar items) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Linked Tasks from Boards -->
            <x-linked-cards entityType="event" :entityId="$event->id" :boards="$boards" />

            <!-- Checklist -->
            @if($event->ministry)
            @can('manage-ministry', $event->ministry)
                <x-event-checklist :event="$event" :templates="$checklistTemplates" />
            @endcan
            @endif

            <!-- Reminders -->
            @if($currentChurch->telegram_bot_token)
            @if($event->ministry)
            @can('manage-ministry', $event->ministry)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5"
                 x-data="reminderManager()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Нагадування
                    </h3>
                    <span x-show="saving" class="text-xs text-gray-400">Збереження...</span>
                </div>

                <div class="space-y-3">
                    <template x-for="(reminder, index) in reminders" :key="index">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-2">
                            <!-- Time settings row -->
                            <div class="flex items-center gap-2">
                                <select x-model="reminder.type" @change="updateReminder(index)"
                                        class="flex-1 px-2 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="days">За днів</option>
                                    <option value="hours">За годин</option>
                                </select>
                                <input type="number" x-model="reminder.value" min="1" max="30" @change="saveReminders()"
                                       class="w-14 px-2 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center">
                                <template x-if="reminder.type === 'days'">
                                    <input type="time" x-model="reminder.time" @change="saveReminders()"
                                           class="w-24 px-2 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </template>
                                <button type="button" @click="removeReminder(index)"
                                        class="p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Recipients row -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">Кому:</span>
                                <select x-model="reminder.recipients" @change="saveReminders()"
                                        class="flex-1 px-2 py-1 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="all">Всім призначеним</option>
                                    <option value="confirmed">Тільки підтвердженим</option>
                                    <option value="pending">Тільки очікуючим</option>
                                    <option value="custom">Вибрати людей...</option>
                                </select>
                            </div>

                            <!-- Custom people selector -->
                            <template x-if="reminder.recipients === 'custom'">
                                <div class="pt-1">
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="person in availablePeople" :key="person.id">
                                            <label class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full cursor-pointer transition-colors"
                                                   :class="isPersonSelected(index, person.id) ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'">
                                                <input type="checkbox" class="hidden"
                                                       :checked="isPersonSelected(index, person.id)"
                                                       @change="togglePerson(index, person.id)">
                                                <span x-text="person.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                    <p x-show="availablePeople.length === 0" class="text-xs text-gray-400 italic">
                                        Немає призначених людей
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="reminders.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">
                            Нагадування не налаштовані
                        </p>
                    </template>
                </div>

                <button type="button" @click="addReminder()"
                        class="mt-3 w-full flex items-center justify-center gap-1 px-3 py-2 text-sm text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Додати нагадування
                </button>
            </div>
            @endcan
            @endif
            @endif

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Швидкі дії</h3>
                <div class="space-y-2">
                    <!-- Add to Google Calendar -->
                    <a href="{{ route('events.google', $event) }}" target="_blank"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3zM8.25 18.75h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm4 8h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm4 8h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5zm0-4h-2.5v-2.5h2.5v2.5z"/>
                        </svg>
                        <span>Додати в Google Calendar</span>
                    </a>

                    <a href="{{ route('schedule') }}"
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Назад до розкладу</span>
                    </a>

                    @if($event->ministry)
                    @can('manage-ministry', $event->ministry)
                        @php
                            $hasRelatedEvents = $event->parent_event_id || $event->childEvents()->count() > 0;
                            $relatedCount = $event->parent_event_id
                                ? \App\Models\Event::where('parent_event_id', $event->parent_event_id)->count() + 1
                                : $event->childEvents()->count() + 1;
                        @endphp

                        <div x-data="{ showDeleteModal: false }" class="w-full">
                            <button type="button"
                                    @click="showDeleteModal = true"
                                    class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Видалити подію</span>
                            </button>

                            <!-- Delete Modal -->
                            <template x-teleport="body">
                                <div x-show="showDeleteModal"
                                     x-transition:enter="ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 z-50 overflow-y-auto"
                                     @keydown.escape.window="showDeleteModal = false"
                                     style="display: none;">

                                    <div class="fixed inset-0 bg-black/50 dark:bg-black/70" @click="showDeleteModal = false"></div>

                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div x-show="showDeleteModal"
                                             x-transition:enter="ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="ease-in duration-150"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.stop
                                             class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">

                                            <!-- Warning Icon -->
                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>

                                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white text-center">
                                                Видалити подію?
                                            </h3>

                                            @if($hasRelatedEvents)
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                    Ця подія є частиною серії з {{ $relatedCount }} подій. Що ви хочете видалити?
                                                </p>

                                                <div class="mt-6 space-y-3">
                                                    <!-- Delete only this event -->
                                                    <form method="POST" action="{{ route('events.destroy', $event) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="delete_series" value="0">
                                                        <button type="submit"
                                                                class="w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-left">
                                                            <div class="font-medium">Тільки цю подію</div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Інші події серії залишаться</div>
                                                        </button>
                                                    </form>

                                                    <!-- Delete all in series -->
                                                    <form method="POST" action="{{ route('events.destroy', $event) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="delete_series" value="1">
                                                        <button type="submit"
                                                                class="w-full px-4 py-3 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors text-left">
                                                            <div class="font-medium">Всю серію ({{ $relatedCount }} подій)</div>
                                                            <div class="text-xs text-red-200 mt-0.5">Видалити всі пов'язані події</div>
                                                        </button>
                                                    </form>
                                                </div>

                                                <div class="mt-4">
                                                    <button type="button" @click="showDeleteModal = false"
                                                            class="w-full px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                                        Скасувати
                                                    </button>
                                                </div>
                                            @else
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                    Ви впевнені, що хочете видалити цю подію? Усі призначення також будуть видалені.
                                                </p>

                                                <div class="mt-6 flex justify-center space-x-3">
                                                    <button type="button" @click="showDeleteModal = false"
                                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                                        Скасувати
                                                    </button>
                                                    <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                                            Видалити
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@push('scripts')
<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Alpine store for shared event state
document.addEventListener('alpine:init', () => {
    Alpine.store('event', {
        isService: {{ $event->is_service ? 'true' : 'false' }},
        trackAttendance: {{ $event->track_attendance ? 'true' : 'false' }}
    });
});

// Songs data for autocomplete
const SONGS_DATA = @json($songsForAutocomplete ?? []);

// Global data for responsible editor
const ALL_PEOPLE = @json($allPeople->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name, 'hasTelegram' => (bool)$p->telegram_chat_id])->values());
const PLAN_URL = '{{ url("events/" . $event->id . "/plan") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// Responsible person editor (shared between server-rendered and dynamic rows)
function responsibleEditor(itemId, initialPeople) {
    return {
        open: false,
        search: '',
        itemId: itemId,
        people: initialPeople || [],
        allPeopleList: ALL_PEOPLE,

        get filteredPeople() {
            if (!this.search) return this.allPeopleList;
            const s = this.search.toLowerCase();
            return this.allPeopleList.filter(p => p.name.toLowerCase().includes(s));
        },
        addPerson(id, name, hasTg) {
            if (this.people.find(p => p.name === name)) return;
            this.people.push({ id, name, hasTelegram: hasTg, status: null });
            this.save();
            this.search = '';
            this.open = false;
        },
        removePerson(index) {
            this.people.splice(index, 1);
            this.save();
        },
        async save() {
            const names = this.people.map(p => p.name).join(', ');
            const primaryId = this.people.length > 0 ? this.people[0].id : null;
            try {
                const response = await fetch(PLAN_URL + '/' + this.itemId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ responsible_names: names, responsible_id: primaryId })
                });
                if (response.ok) {
                    showGlobalToast('Збережено', 'success');
                } else {
                    showGlobalToast('Помилка збереження', 'error');
                }
            } catch (err) {
                console.error('Update error:', err);
                showGlobalToast("Помилка з'єднання", 'error');
            }
        },
        async askPerson(person, index) {
            if (!person.id || !person.hasTelegram) return;
            const result = await askInTelegram(this.itemId, person.name, person.id);
            if (result) {
                this.people[index].status = 'pending';
            }
        },
        async askAll() {
            for (let i = 0; i < this.people.length; i++) {
                const p = this.people[i];
                if (p.hasTelegram && (!p.status || p.status === 'declined')) {
                    await this.askPerson(p, i);
                }
            }
        },
        getStats() {
            return { total: this.people.length, confirmed: this.people.filter(p => p.status === 'confirmed').length };
        },
        getTagClass(status) {
            if (status === 'confirmed') return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
            if (status === 'declined') return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
            if (status === 'pending') return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
            return 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
        }
    };
}


// Song autocomplete for existing items
function existingItemSongAutocomplete(itemId, initialTitle) {
    return {
        itemId: itemId,
        title: initialTitle,
        showSongs: false,
        songSearch: '',
        songIndex: 0,

        checkForSongTrigger(value) {
            // Simple check: if value contains "song-" not inside brackets, show autocomplete
            const withoutBracketed = value.replace(/\[song-\d+\]/gi, '');
            const match = withoutBracketed.match(/song-(\S*)/i);
            if (match) {
                this.showSongs = true;
                this.songSearch = (match[1] || '').toLowerCase();
                this.songIndex = 0;
            } else {
                this.showSongs = false;
            }
        },

        filteredSongs() {
            if (!this.songSearch) return SONGS_DATA.slice(0, 10);
            return SONGS_DATA.filter(s =>
                s.title.toLowerCase().includes(this.songSearch)
            ).slice(0, 10);
        },

        selectSong(song) {
            if (!song) return;
            this.title = song.title;
            this.showSongs = false;
            updateFieldWithSong(this.itemId, song.id, song.title, song.key);
        },

        saveTitle() {
            if (!this.title.match(/song-/i)) {
                updateField(this.itemId, 'title', this.title);
            }
        }
    };
}

// Title editor with inline song links (like Shortcut)
function titleEditor(itemId, initialTitle, existingSongId = null) {
    return {
        itemId: itemId,
        title: initialTitle,
        _originalTitle: initialTitle, // Track original to detect changes
        existingSongId: existingSongId,
        editing: false,
        showSongs: false,
        songSearch: '',
        songIndex: 0,

        startEditing() {
            this.editing = true;
            this.$nextTick(() => {
                if (this.$refs.input) {
                    this.$refs.input.focus();
                    this.$refs.input.style.height = 'auto';
                    this.$refs.input.style.height = this.$refs.input.scrollHeight + 'px';
                    // Move cursor to end
                    this.$refs.input.selectionStart = this.$refs.input.value.length;
                }
            });
        },

        checkForSongTrigger(value) {
            // Simple check: if value contains "song-" not inside brackets, show autocomplete
            // First remove all [song-X] patterns, then check if "song-" remains
            const withoutBracketed = value.replace(/\[song-\d+\]/gi, '');
            const match = withoutBracketed.match(/song-(\S*)/i);
            if (match) {
                this.showSongs = true;
                this.songSearch = (match[1] || '').toLowerCase();
                this.songIndex = 0;
            } else {
                this.showSongs = false;
            }
        },

        filteredSongs() {
            if (!this.songSearch) return SONGS_DATA.slice(0, 10);
            return SONGS_DATA.filter(s =>
                s.title.toLowerCase().includes(this.songSearch) ||
                (s.artist && s.artist.toLowerCase().includes(this.songSearch))
            ).slice(0, 10);
        },

        insertSongLink(song) {
            if (!song) return;
            // Replace only "naked" song-xxx (not inside brackets) with [song-ID]
            // Use a function to find and replace only the unbracketed occurrence
            let replaced = false;
            this.title = this.title.replace(/(\[song-\d+\])|song-[^\s\]]*/gi, (match) => {
                // If it's already a bracketed song reference, keep it
                if (match.startsWith('[')) {
                    return match;
                }
                // Only replace the first naked occurrence
                if (!replaced) {
                    replaced = true;
                    return `[song-${song.id}]`;
                }
                return match;
            });
            this.showSongs = false;

            // Auto-fill responsible and notes from ALL songs in title (merged)
            const mergedTeam = _collectAllSongTeams(this.title);
            if (mergedTeam.length > 0) {
                _fillTeamFields(this.itemId, mergedTeam);
            }

            // Keep focus on input
            this.$nextTick(() => {
                if (this.$refs.input) {
                    this.$refs.input.focus();
                }
            });
        },

        saveTitle() {
            // Check if title actually changed
            if (this.title === this._originalTitle) {
                return; // No change, skip save
            }

            // Extract song IDs from [song-ID] patterns
            const songIds = [];
            this.title.replace(/\[song-(\d+)\]/g, (match, id) => {
                songIds.push(parseInt(id));
                return match;
            });

            // Save title
            updateField(this.itemId, 'title', this.title);
            this._originalTitle = this.title; // Update original after save

            // Update song_id if there's exactly one song (for backwards compatibility)
            const primarySongId = songIds.length > 0 ? songIds[0] : null;
            if (primarySongId !== this.existingSongId) {
                updateField(this.itemId, 'song_id', primarySongId);
                this.existingSongId = primarySongId;
            }
        },

        // Render text with song links
        renderWithSongLinks(text) {
            if (!text || text.trim() === '') {
                return '<span class="text-gray-400 italic">Клікніть щоб додати текст...</span>';
            }

            // Escape HTML first
            let html = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');

            // Replace [song-ID] with actual song links
            html = html.replace(/\[song-(\d+)\]/g, (match, songId) => {
                const song = SONGS_DATA.find(s => s.id == songId);
                if (song) {
                    const keyBadge = song.key ? `<span class="ml-1 px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs rounded font-mono">${song.key}</span>` : '';
                    return `<a href="/songs/${song.id}" class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline font-medium whitespace-nowrap" onclick="event.stopPropagation()"><span>🎵</span><span>${song.title}</span>${keyBadge}</a>`;
                }
                return `<span class="text-red-500">[пісня не знайдена]</span>`;
            });

            return html;
        }
    };
}

// Update field with song_id
async function updateFieldWithSong(itemId, songId, title, songKey = null) {
    try {
        const response = await fetch(`{{ url('events/' . $event->id . '/plan') }}/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                song_id: songId,
                title: title
            })
        });

        if (response.ok) {
            showGlobalToast(songId ? 'Пісню додано' : 'Пісню видалено', 'success');
            // Update DOM without reload
            updateSongCellDOM(itemId, songId, title, songKey);
        } else {
            showGlobalToast('Помилка оновлення', 'error');
        }
    } catch (err) {
        console.error('Update error:', err);
        showGlobalToast('Помилка з\'єднання', 'error');
    }
}

// Update the song cell in DOM without page reload
function updateSongCellDOM(itemId, songId, title, songKey) {
    const row = document.querySelector(`tr[data-id="${itemId}"]`);
    if (!row) return;

    const titleCell = row.querySelectorAll('td')[1]; // Second td is title
    if (!titleCell) return;

    if (songId) {
        // Show song display with description field
        const keyBadge = songKey ? `<span class="px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs rounded font-mono">${escapeHtml(songKey)}</span>` : '';
        titleCell.innerHTML = `
            <div class="space-y-1">
                <div class="flex items-center gap-2" x-data="{ editing: false }">
                    <template x-if="!editing">
                        <div class="flex items-center gap-2 cursor-pointer" @click="editing = true" title="Клікніть для зміни пісні">
                            <span class="text-lg">🎵</span>
                            <a href="/songs/${songId}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium" @click.stop>${escapeHtml(title)}</a>
                            ${keyBadge}
                            <button type="button" @click.stop="if(confirm('Видалити пісню з цього пункту?')) { updateFieldWithSong(${itemId}, null, ''); editing = false; }" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500" title="Видалити пісню">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="editing">
                        <div class="w-full relative" x-data="existingItemSongAutocomplete(${itemId}, '${escapeHtml(title).replace(/'/g, "&#39;")}')" x-init="$nextTick(() => $refs.input.focus())">
                            <input type="text" x-ref="input" x-model="title"
                                   @input="checkForSongTrigger($event.target.value)"
                                   @blur="setTimeout(() => { if(!showSongs) editing = false; }, 200)"
                                   @keydown.escape="editing = false"
                                   @keydown.enter.prevent="if(showSongs && filteredSongs().length) { selectSong(filteredSongs()[songIndex]); } else { saveTitle(); editing = false; }"
                                   placeholder="Введіть song- для пошуку пісні"
                                   class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-primary-300 rounded focus:ring-1 focus:ring-primary-500">
                            <div x-show="showSongs" x-transition @click.away="showSongs = false"
                                 class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                <template x-for="(song, index) in filteredSongs()" :key="song.id">
                                    <button type="button" @click="selectSong(song)"
                                            :class="{'bg-primary-50 dark:bg-primary-900/30': songIndex === index}"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                                        <span x-text="song.title"></span>
                                        <span x-show="song.key" class="px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs rounded font-mono" x-text="song.key"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-data="{ desc: '' }">
                    <input type="text" x-model="desc"
                           @blur="updateField(${itemId}, 'description', desc)"
                           @keydown.enter="$el.blur()"
                           placeholder="Додатковий текст..."
                           class="w-full px-1 py-0.5 text-xs text-gray-600 dark:text-gray-400 bg-transparent border-0 border-b border-transparent hover:border-gray-300 focus:border-primary-500 focus:ring-0 placeholder-gray-400">
                </div>
            </div>
        `;
    } else {
        // Show regular text input
        titleCell.innerHTML = `
            <div class="relative" x-data="existingItemSongAutocomplete(${itemId}, '')">
                <textarea x-model="title"
                          @input="checkForSongTrigger($event.target.value); $el.style.height='auto'; $el.style.height=$el.scrollHeight+'px'"
                          @blur="if(!showSongs) saveTitle()"
                          @keydown.escape="showSongs = false"
                          @keydown.arrow-down.prevent="if(showSongs) songIndex = Math.min(songIndex + 1, filteredSongs().length - 1)"
                          @keydown.arrow-up.prevent="if(showSongs) songIndex = Math.max(songIndex - 1, 0)"
                          @keydown.enter.prevent="if(showSongs && filteredSongs().length) { selectSong(filteredSongs()[songIndex]); } else { saveTitle(); $el.blur(); }"
                          placeholder="Опис пункту... (song- для пісні)"
                          rows="1"
                          class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"></textarea>
                <div x-show="showSongs" x-transition @click.away="showSongs = false"
                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                    <template x-for="(song, index) in filteredSongs()" :key="song.id">
                        <button type="button" @click="selectSong(song)"
                                :class="{'bg-primary-50 dark:bg-primary-900/30': songIndex === index}"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span x-text="song.title"></span>
                            <span x-show="song.key" class="px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs rounded font-mono" x-text="song.key"></span>
                        </button>
                    </template>
                </div>
            </div>
        `;
    }

    // Re-initialize Alpine.js for the new content
    if (window.Alpine) {
        Alpine.initTree(titleCell);
    }
}

// Toast notification helper
function showGlobalToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

    toast.className = `${bgColor} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `
        <span class="text-lg">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Event Editor for inline editing
function eventEditor() {
    return {
        title: @json($event->title),
        date: @json($event->date?->format('Y-m-d')),
        time: @json($event->time?->format('H:i')),
        notes: @json($event->notes ?? ''),
        ministryId: @json($event->ministry_id),
        ministryColor: @json($event->ministry?->color ?? '#3b82f6'),
        isService: {{ $event->is_service ? 'true' : 'false' }},
        isSundayService: {{ $event->service_type === 'sunday_service' ? 'true' : 'false' }},
        trackAttendance: {{ $event->track_attendance ? 'true' : 'false' }},
        ministries: @json($ministriesData),

        // Store original values to detect changes
        _original: {
            title: @json($event->title),
            date: @json($event->date?->format('Y-m-d')),
            time: @json($event->time?->format('H:i')),
            notes: @json($event->notes ?? ''),
            ministryId: @json($event->ministry_id),
            isService: {{ $event->is_service ? 'true' : 'false' }},
            isSundayService: {{ $event->service_type === 'sunday_service' ? 'true' : 'false' }},
            trackAttendance: {{ $event->track_attendance ? 'true' : 'false' }}
        },

        async saveField(field, value) {
            // Check if value actually changed
            const originalKey = field === 'ministry_id' ? 'ministryId' :
                               field === 'is_service' ? 'isService' :
                               field === 'service_type' ? 'isSundayService' :
                               field === 'track_attendance' ? 'trackAttendance' : field;
            if (this._original[originalKey] === value) {
                return; // No change, skip save
            }

            try {
                const response = await fetch('{{ route("events.update", $event) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ [field]: value })
                });
                const data = await response.json();
                if (data.success) {
                    this._original[originalKey] = value; // Update original after successful save
                    showGlobalToast('Збережено', 'success');
                } else {
                    showGlobalToast(data.message || 'Помилка', 'error');
                }
            } catch (err) {
                console.error('Save error:', err);
                showGlobalToast('Помилка збереження', 'error');
            }
        },

        async saveMinistry() {
            const ministry = this.ministries.find(m => m.id == this.ministryId);
            if (ministry) {
                this.ministryColor = ministry.color;
            }
            await this.saveField('ministry_id', this.ministryId);
        },

        getMinistryName() {
            const ministry = this.ministries.find(m => m.id == this.ministryId);
            return ministry ? ministry.name : 'Без команди';
        }
    };
}

// Plan Templates Manager
function planTemplatesManager() {
    return {
        showSaveModal: false,
        templateName: '',
        includeResponsible: false,
        customTemplates: [],

        async init() {
            await this.loadTemplates();
        },

        async loadTemplates() {
            try {
                const response = await fetch('{{ route("service-plan-templates.index") }}', {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.customTemplates = data.templates || [];
                }
            } catch (err) {
                console.error('Error loading templates:', err);
            }
        },

        async saveAsTemplate() {
            if (!this.templateName.trim()) return;

            try {
                const response = await fetch('{{ route("service-plan-templates.store", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: this.templateName,
                        include_responsible: this.includeResponsible
                    })
                });

                const data = await response.json();
                if (data.success) {
                    showGlobalToast('Шаблон збережено', 'success');
                    this.showSaveModal = false;
                    this.templateName = '';
                    this.includeResponsible = false;
                    this.customTemplates.push(data.template);
                } else {
                    showGlobalToast(data.message || 'Помилка', 'error');
                }
            } catch (err) {
                console.error('Save template error:', err);
                showGlobalToast('Помилка збереження', 'error');
            }
        },

        async applyCustomTemplate(templateId) {
            if (!confirm('Застосувати шаблон? Поточний план буде замінено.')) return;

            try {
                const response = await fetch(`/service-plan-templates/apply/{{ $event->id }}/${templateId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showGlobalToast('Шаблон застосовано', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showGlobalToast(data.message || 'Помилка', 'error');
                }
            } catch (err) {
                console.error('Apply template error:', err);
                showGlobalToast('Помилка застосування', 'error');
            }
        },

        async deleteTemplate(templateId) {
            if (!confirm('Видалити цей шаблон?')) return;

            try {
                const response = await fetch(`/service-plan-templates/${templateId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showGlobalToast('Шаблон видалено', 'success');
                    this.customTemplates = this.customTemplates.filter(t => t.id !== templateId);
                } else {
                    showGlobalToast(data.message || 'Помилка', 'error');
                }
            } catch (err) {
                console.error('Delete template error:', err);
                showGlobalToast('Помилка видалення', 'error');
            }
        }
    };
}

// Global function to update a plan item field
async function updateField(itemId, field, value) {
    try {
        const response = await fetch(`{{ url('events/' . $event->id . '/plan') }}/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ [field]: value })
        });

        if (response.ok) {
            showGlobalToast('Збережено', 'success');
            return true;
        } else {
            showGlobalToast('Помилка збереження', 'error');
            return false;
        }
    } catch (err) {
        console.error('Update error:', err);
        showGlobalToast('Помилка з\'єднання', 'error');
        return false;
    }
}

// Collect merged team from ALL [song-ID] references in title
function _collectAllSongTeams(title) {
    const songIds = [];
    (title || '').replace(/\[song-(\d+)\]/g, (m, id) => { songIds.push(parseInt(id)); return m; });

    const seen = new Set();
    const merged = [];
    songIds.forEach(id => {
        const song = SONGS_DATA.find(s => s.id === id);
        if (song && song.team) {
            song.team.forEach(t => {
                const key = (t.person_name || '') + '|' + (t.role_name || '');
                if (!seen.has(key)) {
                    seen.add(key);
                    merged.push(t);
                }
            });
        }
    });
    return merged;
}

// Build responsible names + notes strings from team array
function _buildTeamStrings(team) {
    const responsibleNames = team.map(t => t.person_name).filter(Boolean);
    const uniqueNames = [...new Set(responsibleNames)];
    const byRole = {};
    team.forEach(t => {
        if (t.role_name && t.person_name) {
            if (!byRole[t.role_name]) byRole[t.role_name] = [];
            if (!byRole[t.role_name].includes(t.person_name)) {
                byRole[t.role_name].push(t.person_name);
            }
        }
    });
    const notes = Object.entries(byRole)
        .map(([role, names]) => `${role}: ${names.join(', ')}`)
        .join('; ');
    return { responsibleNames: uniqueNames.join(', '), notes };
}

// Fill responsible + notes from worship team (works for both server-rendered and dynamic rows)
function _fillTeamFields(itemId, team) {
    const { responsibleNames, notes } = _buildTeamStrings(team);

    // Save to DB
    updateField(itemId, 'responsible_names', responsibleNames);
    updateField(itemId, 'notes', notes);

    // Update DOM visually
    const row = document.querySelector(`tr[data-id="${itemId}"]`);
    if (!row) return;
    const tds = row.querySelectorAll(':scope > td');

    // Update responsible cell (td[3] = 4th cell)
    if (tds[3]) {
        // Try Alpine component (both server-rendered and dynamic rows now use responsibleEditor)
        try {
            const data = Alpine.$data(tds[3]);
            if (data && data.people !== undefined && data.allPeopleList) {
                const names = responsibleNames.split(',').map(n => n.trim()).filter(Boolean);
                data.people = names.map(name => {
                    const found = data.allPeopleList.find(p => p.name === name);
                    return {
                        id: found ? found.id : null,
                        name: name,
                        hasTelegram: found ? !!found.hasTelegram : false,
                        status: null
                    };
                });
            }
        } catch (e) { /* not an Alpine component */ }
    }

    // Update notes cell (td[4] = 5th cell)
    if (tds[4]) {
        const notesTextarea = tds[4].querySelector('textarea');
        if (notesTextarea) {
            notesTextarea.value = notes;
            notesTextarea.style.height = 'auto';
            notesTextarea.style.height = notesTextarea.scrollHeight + 'px';
        }
    }
}

// Global function to ask via Telegram
async function askInTelegram(itemId, personName, personId = null) {
    try {
        const body = personId ? JSON.stringify({ person_id: personId }) : null;
        const response = await fetch(`/events/{{ $event->id }}/plan/${itemId}/notify`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        });

        const data = await response.json();

        if (data.success) {
            showGlobalToast(`Запит надіслано: ${personName}`, 'success');
            return true;
        } else {
            showGlobalToast(data.message || 'Помилка надсилання', 'error');
            return false;
        }
    } catch (err) {
        console.error('Telegram error:', err);
        showGlobalToast('Помилка надсилання', 'error');
        return false;
    }
}

// Plan Editor - Spreadsheet-like inline editing
function planEditor() {
    return {
        message: '',
        messageType: 'success',
        newItem: {
            start_time: '',
            title: '',
            responsible_names: '',
            notes: '',
            song_id: null
        },
        // Responsible person selector for new items
        newResponsible: {
            open: false,
            search: '',
            people: [],
        },
        get newFilteredPeople() {
            if (!this.newResponsible.search) return ALL_PEOPLE;
            const s = this.newResponsible.search.toLowerCase();
            return ALL_PEOPLE.filter(p => p.name.toLowerCase().includes(s));
        },
        addNewResponsible(id, name) {
            if (this.newResponsible.people.find(p => p.name === name)) return;
            this.newResponsible.people.push({ id, name });
            this.newItem.responsible_names = this.newResponsible.people.map(p => p.name).join(', ');
            this.newResponsible.search = '';
            this.newResponsible.open = false;
        },
        removeNewResponsible(index) {
            this.newResponsible.people.splice(index, 1);
            this.newItem.responsible_names = this.newResponsible.people.map(p => p.name).join(', ');
        },
        // Song autocomplete for new items
        showSongs: false,
        songSearch: '',
        songIndex: 0,

        checkForSongTrigger(value) {
            // Simple check: if value contains "song-" not inside brackets, show autocomplete
            const withoutBracketed = value.replace(/\[song-\d+\]/gi, '');
            const match = withoutBracketed.match(/song-(\S*)/i);
            if (match) {
                this.showSongs = true;
                this.songSearch = (match[1] || '').toLowerCase();
                this.songIndex = 0;
            } else {
                this.showSongs = false;
            }
        },

        filteredSongsForNew() {
            if (!this.songSearch) return SONGS_DATA.slice(0, 10);
            return SONGS_DATA.filter(s =>
                s.title.toLowerCase().includes(this.songSearch) ||
                (s.artist && s.artist.toLowerCase().includes(this.songSearch))
            ).slice(0, 10);
        },

        selectSongForNew(song) {
            if (!song) return;
            // Replace only "naked" song-xxx (not inside brackets) with [song-ID]
            let replaced = false;
            this.newItem.title = this.newItem.title.replace(/(\[song-\d+\])|song-[^\s\]]*/gi, (match) => {
                if (match.startsWith('[')) return match;
                if (!replaced) {
                    replaced = true;
                    return `[song-${song.id}]`;
                }
                return match;
            });
            this.newItem.song_id = song.id;
            this.showSongs = false;

            // Auto-fill responsible and notes from ALL songs in title (merged)
            const mergedTeam = _collectAllSongTeams(this.newItem.title);
            if (mergedTeam.length > 0) {
                const { responsibleNames, notes } = _buildTeamStrings(mergedTeam);
                this.newItem.responsible_names = responsibleNames;
                this.newItem.notes = notes;
                // Sync newResponsible.people from names
                this.newResponsible.people = responsibleNames.split(',').map(n => n.trim()).filter(Boolean).map(name => {
                    const found = ALL_PEOPLE.find(p => p.name === name);
                    return { id: found ? found.id : null, name };
                });
            }
        },

        async addItem() {
            if (!this.newItem.title.trim()) return;

            try {
                const response = await fetch('{{ route("events.plan.store", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        title: this.newItem.title,
                        start_time: this.newItem.start_time || null,
                        responsible_names: this.newItem.responsible_names || null,
                        notes: this.newItem.notes || null,
                        song_id: this.newItem.song_id || null
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.item) {
                        this.insertNewRow(data.item);
                        // Clear form
                        this.newItem = { start_time: '', title: '', responsible_names: '', notes: '', song_id: null };
                        this.newResponsible = { open: false, search: '', people: [] };
                        showGlobalToast('Пункт додано', 'success');
                    }
                } else {
                    this.showMessage('Помилка додавання', 'error');
                }
            } catch (err) {
                console.error('Add error:', err);
                this.showMessage('Помилка з\'єднання', 'error');
            }
        },

        insertNewRow(item) {
            // Use global function that handles songs (it appends the row and resizes textareas)
            window.insertPlanRow(item);
        },

        async deleteItem(id) {
            if (!confirm('Видалити цей пункт?')) return;

            try {
                const response = await fetch(`{{ url('events/' . $event->id . '/plan') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    // Remove row from DOM
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
                        showGlobalToast('Пункт видалено', 'success');
                    }
                    // Check if table is empty
                    const tbody = document.querySelector('table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `<tr id="empty-row">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                Почніть додавати пункти плану нижче
                            </td>
                        </tr>`;
                    }
                } else {
                    this.showMessage('Помилка видалення', 'error');
                }
            } catch (err) {
                console.error('Delete error:', err);
                this.showMessage('Помилка з\'єднання', 'error');
            }
        },


        showMessage(msg, type) {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
            }, 3000);
        }
    };
}

// Helper: escape string for safe use inside HTML attribute
function _escHtmlAttr(str) {
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// Global function to insert new plan row
window.insertPlanRow = function(item) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = document.getElementById('empty-row');
    if (emptyRow) emptyRow.remove();

    const startTime = item.start_time ? item.start_time.substring(0, 5) : '';
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group';
    row.dataset.id = item.id;

    // Build display title with [song-ID] prefix if needed
    let displayTitle = item.title || '';
    const songId = item.song_id || (item.song ? item.song.id : null);
    if (songId && !displayTitle.includes(`[song-${songId}]`)) {
        displayTitle = `[song-${songId}] ${displayTitle}`.trim();
    }

    // Build x-data attribute (HTML-escaped JS expression)
    const xDataExpr = `titleEditor(${item.id}, ${JSON.stringify(displayTitle)}, ${songId || 'null'})`;
    const xDataAttr = _escHtmlAttr(xDataExpr);

    // Build initial people array for responsible editor
    const initialPeople = [];
    if (item.responsible_names) {
        item.responsible_names.split(',').map(n => n.trim()).filter(Boolean).forEach(name => {
            const found = ALL_PEOPLE.find(p => p.name === name);
            initialPeople.push({
                id: found ? found.id : null,
                name: name,
                hasTelegram: found ? !!found.hasTelegram : false,
                status: null
            });
        });
    }
    const respXData = _escHtmlAttr(`responsibleEditor(${item.id}, ${JSON.stringify(initialPeople)})`);

    const escNotes = _escHtmlAttr(item.notes || '');

    row.innerHTML = `
        <td class="px-1 py-3 cursor-grab active:cursor-grabbing drag-handle">
            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                <circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/>
                <circle cx="9" cy="15" r="1.5"/><circle cx="15" cy="15" r="1.5"/>
                <circle cx="9" cy="20" r="1.5"/><circle cx="15" cy="20" r="1.5"/>
            </svg>
        </td>
        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700">
            <input type="time"
                   value="${startTime}"
                   onchange="updateField(${item.id}, 'start_time', this.value)"
                   class="min-w-[5.5rem] px-2 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
        </td>
        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top">
            <div class="relative" x-data="${xDataAttr}">
                <div x-show="!editing" @click="startEditing()" class="cursor-text min-h-[1.5rem] px-1 py-1 text-sm text-gray-900 dark:text-white break-words" x-html="renderWithSongLinks(title)"></div>
                <div x-show="editing" class="relative">
                    <textarea x-ref="input" x-model="title"
                              @input="checkForSongTrigger($event.target.value); $el.style.height='auto'; $el.style.height=$el.scrollHeight+'px'"
                              @blur="setTimeout(() => { if(!showSongs) { saveTitle(); editing = false; } }, 150)"
                              @keydown.escape="editing = false; showSongs = false"
                              @keydown.arrow-down.prevent="if(showSongs) songIndex = Math.min(songIndex + 1, filteredSongs().length - 1)"
                              @keydown.arrow-up.prevent="if(showSongs) songIndex = Math.max(songIndex - 1, 0)"
                              @keydown.enter.prevent="if(showSongs && filteredSongs().length) { insertSongLink(filteredSongs()[songIndex]); } else { saveTitle(); editing = false; }"
                              placeholder="Текст... (введіть song- для пошуку пісні)"
                              rows="1"
                              class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 border border-primary-300 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                              style="word-wrap: break-word; overflow-wrap: break-word;"></textarea>
                    <div x-show="showSongs" x-transition
                         class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                        <template x-if="SONGS_DATA.length === 0">
                            <div class="px-3 py-3 text-center text-gray-500 dark:text-gray-400 text-sm">
                                Команда прославлення ще не обрала пісні.
                            </div>
                        </template>
                        <template x-for="(song, index) in filteredSongs()" :key="song.id">
                            <button type="button" @mousedown.prevent="insertSongLink(song)"
                                    :class="{'bg-primary-50 dark:bg-primary-900/30': songIndex === index}"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                                <span x-text="song.title"></span>
                                <span x-show="song.key" class="px-1.5 py-0.5 bg-primary-100 text-primary-700 text-xs rounded font-mono" x-text="song.key"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </td>
        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top whitespace-nowrap"
            x-data="${respXData}">
            <div class="flex flex-col gap-1">
                <template x-for="(person, index) in people" :key="index">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg" :class="getTagClass(person.status)">
                        <span x-text="person.name"></span>
                        <button type="button" @click="removePerson(index)" class="text-gray-400 hover:text-red-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                </template>
                <div class="relative">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1 text-xs px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-dashed border-gray-300 dark:border-gray-600">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span x-show="people.length === 0">Додати</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute z-50 left-0 mt-1 w-48 sm:w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                        <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                            <input type="text" x-model="search" placeholder="Пошук..."
                                   class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            <template x-for="person in filteredPeople" :key="person.id">
                                <button type="button"
                                        @click="addPerson(person.id, person.name, person.hasTelegram)"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                    <span x-text="person.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 align-top">
            <textarea placeholder="Примітки..."
                      onchange="updateField(${item.id}, 'notes', this.value)"
                      rows="1"
                      class="w-full px-1 py-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                      style="word-wrap: break-word; overflow-wrap: break-word;"
                      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">${escNotes}</textarea>
        </td>
        <td class="px-3 py-3 text-center">
            <button type="button"
                    onclick="window.planEditorDeleteItem(${item.id})"
                    class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                    title="Видалити">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    tbody.appendChild(row);

    // Initialize Alpine.js on the new row for titleEditor to work
    Alpine.initTree(row);

    row.querySelectorAll('textarea').forEach(ta => {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    });
};

// Global delete function for dynamically added rows
window.planEditorDeleteItem = async function(id) {
    if (!confirm('Видалити цей пункт?')) return;

    try {
        const response = await fetch(`{{ url('events/' . $event->id . '/plan') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
                showGlobalToast('Пункт видалено', 'success');
            }
            const tbody = document.querySelector('table tbody');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = `<tr id="empty-row">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Почніть додавати пункти плану нижче
                    </td>
                </tr>`;
            }
        } else {
            showGlobalToast('Помилка видалення', 'error');
        }
    } catch (err) {
        console.error('Delete error:', err);
        showGlobalToast('Помилка з\'єднання', 'error');
    }
};

// Service Plan Manager (legacy, kept for compatibility)
function servicePlanManager() {
    return {
        showTextModal: false,
        parseText: '',
        newItem: {
            start_time: '{{ $event->time ? $event->time->format("H:i") : "10:00" }}',
            type: '',
            title: '',
            responsible_names: '',
            song_id: null
        },

        async addItem() {
            if (!this.newItem.title.trim()) return;

            try {
                const response = await fetch('{{ route("events.plan.store", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        title: this.newItem.title,
                        type: this.newItem.type || null,
                        start_time: this.newItem.start_time || null,
                        responsible_names: this.newItem.responsible_names || null,
                        song_id: this.newItem.song_id || null
                    })
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error:', response.status, text);
                    showGlobalToast('Помилка: ' + response.status, 'error');
                    return;
                }

                const data = await response.json();
                if (data.success && data.item) {
                    // Use global insertNewRow function
                    window.insertPlanRow(data.item);
                    this.newItem = {
                        start_time: '{{ $event->time ? $event->time->format("H:i") : "10:00" }}',
                        type: '',
                        title: '',
                        responsible_names: '',
                        song_id: null
                    };
                    showGlobalToast('Пункт додано', 'success');
                }
            } catch (err) {
                console.error('Fetch error:', err);
                showGlobalToast('Помилка з\'єднання', 'error');
            }
        },

        async deleteItem(id) {
            window.planEditorDeleteItem(id);
        },

        async applyTemplate(template) {
            try {
                const response = await fetch('{{ route("events.plan.apply-template", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ template })
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                alert('Помилка');
            }
        },

        async parseAndAdd() {
            if (!this.parseText.trim()) return;

            try {
                const response = await fetch('{{ route("events.plan.parse-text", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ text: this.parseText })
                });

                if (!response.ok) {
                    alert('Помилка: ' + response.status);
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.showTextModal = false;
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
                alert('Помилка парсингу');
            }
        }
    };
}

// Send Telegram notification for plan item
async function sendTelegramNotify(itemId, button) {
    const originalContent = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    button.disabled = true;

    try {
        const response = await fetch(`/events/{{ $event->id }}/plan/${itemId}/notify`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            button.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            setTimeout(() => { button.innerHTML = originalContent; button.disabled = false; }, 2000);
        } else {
            alert(data.message || 'Помилка');
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    } catch (err) {
        console.error(err);
        alert('Помилка надсилання');
        button.innerHTML = originalContent;
        button.disabled = false;
    }
}

(function() {
    const pollUrl = "{{ route('events.responsibilities.poll', $event) }}";
    let lastCheck = new Date().toISOString();
    let pollInterval = null;

    // Status badge classes
    const statusClasses = {
        'confirmed': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'pending': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'declined': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'open': 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300'
    };

    const statusIcons = {
        'confirmed': '✅',
        'pending': '⏳',
        'declined': '❌',
        'open': '📋'
    };

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');

        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

        toast.className = `${bgColor} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transform translate-x-full transition-transform duration-300`;
        toast.innerHTML = `
            <span class="text-lg">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function updateStatusBadge(row, status, statusLabel) {
        const badge = row.querySelector('.status-badge');
        if (!badge) return;

        // Remove all status classes
        Object.values(statusClasses).forEach(cls => {
            cls.split(' ').forEach(c => badge.classList.remove(c));
        });

        // Add new status classes
        statusClasses[status]?.split(' ').forEach(c => badge.classList.add(c));

        // Update text
        badge.dataset.status = status;
        const labelSpan = badge.querySelector('.status-label');
        if (labelSpan) {
            labelSpan.textContent = statusLabel;
        }
        badge.innerHTML = `${statusIcons[status] || ''} <span class="status-label">${statusLabel}</span>`;

        // Flash animation
        row.classList.add('ring-2', 'ring-primary-500');
        setTimeout(() => row.classList.remove('ring-2', 'ring-primary-500'), 1000);
    }

    async function pollResponsibilities() {
        try {
            const response = await fetch(`${pollUrl}?since=${encodeURIComponent(lastCheck)}`);
            if (!response.ok) return;

            const data = await response.json();
            lastCheck = data.server_time;

            // Update each responsibility row
            data.responsibilities.forEach(resp => {
                const row = document.querySelector(`.responsibility-row[data-id="${resp.id}"]`);
                if (!row) return;

                const currentStatus = row.querySelector('.status-badge')?.dataset.status;
                if (currentStatus && currentStatus !== resp.status) {
                    updateStatusBadge(row, resp.status, resp.status_label);
                }
            });

            // Show toast for new responses
            if (data.new_responses && data.new_responses.length > 0) {
                data.new_responses.forEach(resp => {
                    const emoji = resp.status === 'confirmed' ? '✅' : '❌';
                    const action = resp.status === 'confirmed' ? 'підтвердив' : 'відхилив';
                    showToast(`${emoji} ${resp.person_name} ${action}: ${resp.name}`, resp.status === 'confirmed' ? 'success' : 'error');
                });

                // Play notification sound (optional)
                try {
                    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleR8EMLra4IpgFgk7nODeeWAfEjig4NlrSxcXO5ve2V9EHUA+neDWWz0W');
                    audio.volume = 0.3;
                    audio.play().catch(() => {});
                } catch(e) {}
            }

        } catch (error) {
            console.error('Poll error:', error);
        }
    }

    // Start polling
    function startPolling() {
        if (pollInterval) return;
        pollInterval = setInterval(pollResponsibilities, 5000);
    }

    // Stop polling when page is hidden
    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    // Handle visibility change
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });

    // Start polling on page load
    startPolling();
})();

// Reminder Manager
function reminderManager() {
    return {
        reminders: @json($event->reminder_settings ?? []).map(r => ({
            ...r,
            recipients: r.recipients || 'all',
            person_ids: r.person_ids || []
        })),
        saving: false,
        availablePeople: @json($availablePeopleData),

        addReminder() {
            this.reminders.push({
                type: 'days',
                value: 1,
                time: '18:00',
                recipients: 'all',
                person_ids: []
            });
            this.saveReminders();
        },

        removeReminder(index) {
            this.reminders.splice(index, 1);
            this.saveReminders();
        },

        updateReminder(index) {
            if (this.reminders[index].type === 'hours') {
                this.reminders[index].time = null;
            } else {
                this.reminders[index].time = '18:00';
            }
            this.saveReminders();
        },

        isPersonSelected(reminderIndex, personId) {
            return this.reminders[reminderIndex].person_ids?.includes(personId) ?? false;
        },

        togglePerson(reminderIndex, personId) {
            const reminder = this.reminders[reminderIndex];
            if (!reminder.person_ids) reminder.person_ids = [];

            const idx = reminder.person_ids.indexOf(personId);
            if (idx === -1) {
                reminder.person_ids.push(personId);
            } else {
                reminder.person_ids.splice(idx, 1);
            }
            this.saveReminders();
        },

        async saveReminders() {
            this.saving = true;
            try {
                const params = [['_method', 'PUT']];

                this.reminders.forEach((r, i) => {
                    params.push([`reminders[${i}][type]`, r.type]);
                    params.push([`reminders[${i}][value]`, r.value]);
                    params.push([`reminders[${i}][time]`, r.time || '']);
                    params.push([`reminders[${i}][recipients]`, r.recipients || 'all']);
                    if (r.person_ids && r.person_ids.length > 0) {
                        r.person_ids.forEach(pid => {
                            params.push([`reminders[${i}][person_ids][]`, pid]);
                        });
                    }
                });

                const response = await fetch('{{ route("events.update", $event) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams(params)
                });

                const data = await response.json();
                if (data.success) {
                    showGlobalToast('Нагадування збережено', 'success');
                } else {
                    showGlobalToast(data.message || 'Помилка', 'error');
                }
            } catch (err) {
                console.error('Save reminders error:', err);
                showGlobalToast('Помилка збереження', 'error');
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initPlanSortable();
});

function initPlanSortable() {
    const tbody = document.querySelector('table tbody');
    if (!tbody || typeof Sortable === 'undefined') return;

    new Sortable(tbody, {
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'shadow-lg',
        onEnd: async function(evt) {
            const rows = tbody.querySelectorAll('tr[data-id]');
            const items = [...rows].map(function(row, index) {
                return {
                    id: parseInt(row.dataset.id),
                    sort_order: index
                };
            });

            try {
                const response = await fetch('/events/{{ $event->id }}/plan/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ items: items })
                });

                const data = await response.json();
                if (data.success || response.ok) {
                    showGlobalToast('Порядок оновлено', 'success');
                } else {
                    showGlobalToast(data.message || 'Помилка збереження порядку', 'error');
                }
            } catch (err) {
                console.error('Reorder error:', err);
                showGlobalToast('Помилка збереження порядку', 'error');
            }
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    tr.sortable-ghost { opacity: 0.4; }
    tr.sortable-chosen { background-color: rgb(239 246 255 / 0.5); }
    .dark tr.sortable-chosen { background-color: rgb(55 65 81 / 0.5); }
</style>
@endpush
@endsection
