@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header (Editable) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" x-data="eventEditor()">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center flex-1">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0"
                     :style="'background-color: ' + (ministryColor || '#3b82f6') + '20'">
                    <svg class="w-7 h-7" :style="'color: ' + (ministryColor || '#3b82f6')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <input type="text" x-model="title" @change="saveField('title', title)"
                           class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 w-full p-0 pb-1">
                    <div class="mt-1 relative" x-data="{ showDropdown: false }">
                        <!-- Ministry Badge -->
                        <button type="button" @click="showDropdown = !showDropdown"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-all hover:opacity-80"
                                :style="'background-color: ' + (ministryColor || '#6b7280') + '20; color: ' + (ministryColor || '#6b7280') + '; border: 1px solid ' + (ministryColor || '#6b7280') + '40'">
                            <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (ministryColor || '#6b7280')"></span>
                            <span x-text="ministries.find(m => m.id == ministryId)?.name || 'Без команди'"></span>
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
                    </div>
                </div>
            </div>
            <div class="text-right shrink-0">
                <input type="date" x-model="date" @change="saveField('date', date)"
                       class="text-xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 p-0 pb-1 text-right cursor-pointer">
                <input type="time" x-model="time" @change="saveField('time', time)"
                       class="text-gray-500 dark:text-gray-400 bg-transparent border-0 border-b border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 p-0 pb-1 text-right block ml-auto cursor-pointer">
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-4">
            <textarea x-model="notes" @change="saveField('notes', notes)"
                      placeholder="Додати примітки..."
                      rows="2"
                      class="w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
        </div>

        <!-- Toggles -->
        <div class="mt-4 flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="isService" @change="saveField('is_service', isService)"
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Подія з планом</span>
            </label>
            @if($currentChurch->attendance_enabled)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="trackAttendance" @change="saveField('track_attendance', trackAttendance)"
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Відвідуваність</span>
            </label>
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
            @if($event->is_service)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700" x-data="planEditor()">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
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
                                     class="absolute right-0 mt-1 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20">
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
                                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
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
                                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
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
                                <th class="px-3 py-4 text-left" style="width: 70px;">Час</th>
                                <th class="px-3 py-4 text-left" style="width: 40%;">Що відбувається</th>
                                <th class="px-3 py-4 text-left whitespace-nowrap" style="width: 1px;">Відповідальний</th>
                                <th class="px-3 py-4 text-left" style="width: 25%;">Коментарі</th>
                                <th class="px-2 py-4" style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($event->planItems->sortBy('sort_order') as $item)
                                <tr class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group" data-id="{{ $item->id }}">
                                    {{-- Час --}}
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
                                        <input type="time"
                                               value="{{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}"
                                               @change="updateField({{ $item->id }}, 'start_time', $event.target.value)"
                                               class="min-w-[5.5rem] px-2 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
                                    </td>
                                    {{-- Що відбувається --}}
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                                        <textarea placeholder="Опис пункту..."
                                                  @change="updateField({{ $item->id }}, 'title', $event.target.value)"
                                                  rows="1"
                                                  class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                                                  style="word-wrap: break-word; overflow-wrap: break-word;"
                                                  x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">{{ $item->title }}</textarea>
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
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top whitespace-nowrap"
                                        x-data="{
                                            open: false,
                                            search: '',
                                            itemId: {{ $item->id }},
                                            people: {{ json_encode($existingPeople) }},

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
                                            save() {
                                                const names = this.people.map(p => p.name).join(', ');
                                                const primaryId = this.people.length > 0 ? this.people[0].id : null;
                                                updateField(this.itemId, 'responsible_names', names);
                                                updateField(this.itemId, 'responsible_id', primaryId);
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
                                                const total = this.people.length;
                                                const confirmed = this.people.filter(p => p.status === 'confirmed').length;
                                                return { total, confirmed };
                                            },
                                            getTagClass(status) {
                                                if (status === 'confirmed') return 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400';
                                                if (status === 'declined') return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
                                                if (status === 'pending') return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400';
                                                return 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
                                            }
                                        }">
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
                                                     class="absolute z-20 left-0 mt-1 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                                        <input type="text" x-model="search" placeholder="Пошук..."
                                                               class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                    </div>
                                                    <div class="max-h-48 overflow-y-auto">
                                                        @foreach($allPeople as $person)
                                                            <button type="button"
                                                                    x-show="!search || '{{ mb_strtolower($person->full_name) }}'.includes(search.toLowerCase())"
                                                                    @click="addPerson({{ $person->id }}, '{{ addslashes($person->full_name) }}', {{ $person->telegram_chat_id ? 'true' : 'false' }})"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                                @if($person->telegram_chat_id)
                                                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                                    </svg>
                                                                @else
                                                                    <span class="w-4 h-4"></span>
                                                                @endif
                                                                {{ $person->full_name }}
                                                            </button>
                                                        @endforeach
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
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
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
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Почніть додавати пункти плану нижче
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add new row --}}
                <div class="p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    <form @submit.prevent="addItem()" class="flex items-center gap-2">
                        <input type="time" x-model="newItem.start_time"
                               class="min-w-[6rem] px-2 py-2 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 cursor-pointer">
                        <input type="text" x-model="newItem.title" placeholder="Що відбувається..." required
                               class="flex-1 px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <input type="text" x-model="newItem.responsible_names" placeholder="Відповідальний"
                               class="w-36 px-2 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
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
            @else
            {{-- Simple responsibility form for non-service events --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
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
            @endif

            <!-- Attendance Section -->
            @if($event->track_attendance && $currentChurch->attendance_enabled)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700" x-data="attendanceManager()">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Відвідуваність</h2>
                        </div>
                        @php
                            $presentCount = $event->attendance?->records->where('present', true)->count() ?? 0;
                            $totalPeople = $allPeople->count();
                        @endphp
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $presentCount }}/{{ $totalPeople }}</span>
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
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
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
                        try {
                            await fetch('{{ route("events.attendance.save", $event) }}', {
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
                        } catch (error) {
                            console.error('Error saving attendance:', error);
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
            @can('manage-ministry', $event->ministry)
                <x-event-checklist :event="$event" :templates="$checklistTemplates" />
            @endcan

            <!-- Reminders -->
            @if($currentChurch->telegram_bot_token)
            @can('manage-ministry', $event->ministry)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5"
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

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@push('scripts')
<script>
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
        trackAttendance: {{ $event->track_attendance ? 'true' : 'false' }},
        ministries: @json($ministries->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'color' => $m->color])),

        async saveField(field, value) {
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
            notes: ''
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
                        notes: this.newItem.notes || null
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.item) {
                        this.insertNewRow(data.item);
                        // Clear form
                        this.newItem = { start_time: '', title: '', responsible_names: '', notes: '' };
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
            const tbody = document.querySelector('table tbody');
            // Remove empty row if exists
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) emptyRow.remove();

            const startTime = item.start_time ? item.start_time.substring(0, 5) : '';
            const row = document.createElement('tr');
            row.className = 'hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group';
            row.dataset.id = item.id;
            row.innerHTML = `
                <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
                    <input type="time"
                           value="${startTime}"
                           onchange="updateField(${item.id}, 'start_time', this.value)"
                           class="min-w-[5.5rem] px-2 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
                </td>
                <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                    <textarea placeholder="Опис пункту..."
                              onchange="updateField(${item.id}, 'title', this.value)"
                              rows="1"
                              class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                              style="word-wrap: break-word; overflow-wrap: break-word;"
                              oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">${item.title || ''}</textarea>
                </td>
                <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top whitespace-nowrap">
                    <input type="text"
                           value="${item.responsible_names || ''}"
                           placeholder="Відповідальний"
                           onchange="updateField(${item.id}, 'responsible_names', this.value)"
                           class="px-2 py-1 text-sm text-gray-900 dark:text-white bg-transparent border border-gray-200 dark:border-gray-600 rounded focus:ring-1 focus:ring-primary-500">
                </td>
                <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
                    <textarea placeholder="Примітки..."
                              onchange="updateField(${item.id}, 'notes', this.value)"
                              rows="1"
                              class="w-full px-1 py-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                              style="word-wrap: break-word; overflow-wrap: break-word;"
                              oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">${item.notes || ''}</textarea>
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
            // Auto-resize textareas
            row.querySelectorAll('textarea').forEach(ta => {
                ta.style.height = 'auto';
                ta.style.height = ta.scrollHeight + 'px';
            });
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
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
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

// Global function to insert new plan row
window.insertPlanRow = function(item) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = document.getElementById('empty-row');
    if (emptyRow) emptyRow.remove();

    const startTime = item.start_time ? item.start_time.substring(0, 5) : '';
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group';
    row.dataset.id = item.id;
    row.innerHTML = `
        <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
            <input type="time"
                   value="${startTime}"
                   onchange="updateField(${item.id}, 'start_time', this.value)"
                   class="min-w-[5.5rem] px-2 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 cursor-pointer">
        </td>
        <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
            <textarea placeholder="Опис пункту..."
                      onchange="updateField(${item.id}, 'title', this.value)"
                      rows="1"
                      class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                      style="word-wrap: break-word; overflow-wrap: break-word;"
                      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">${item.title || ''}</textarea>
        </td>
        <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top whitespace-nowrap">
            <input type="text"
                   value="${item.responsible_names || ''}"
                   placeholder="Відповідальний"
                   onchange="updateField(${item.id}, 'responsible_names', this.value)"
                   class="px-2 py-1 text-sm text-gray-900 dark:text-white bg-transparent border border-gray-200 dark:border-gray-600 rounded focus:ring-1 focus:ring-primary-500">
        </td>
        <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700 align-top">
            <textarea placeholder="Примітки..."
                      onchange="updateField(${item.id}, 'notes', this.value)"
                      rows="1"
                      class="w-full px-1 py-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded resize-none break-words"
                      style="word-wrap: break-word; overflow-wrap: break-word;"
                      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'">${item.notes || ''}</textarea>
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
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
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
            responsible_names: ''
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
                        responsible_names: this.newItem.responsible_names || null
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
                        responsible_names: ''
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
        availablePeople: @json($event->responsibilities->map(fn($r) => ['id' => $r->person_id, 'name' => $r->person?->full_name ?? 'Невідомий'])->unique('id')->values()),

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
@endsection
