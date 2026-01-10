@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@if($event->ministry)
@can('manage-ministry', $event->ministry)
<a href="{{ route('events.edit', $event) }}"
   class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
    Редагувати
</a>
@endcan
@endif
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     style="background-color: {{ $event->ministry?->color ?? '#3b82f6' }}20;">
                    <svg class="w-7 h-7" style="color: {{ $event->ministry?->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $event->ministry?->name ?? 'Без служіння' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $event->date?->format('d.m.Y') ?? '-' }}</p>
                <p class="text-gray-500 dark:text-gray-400">{{ $event->time?->format('H:i') ?? '-' }}</p>
            </div>
        </div>

        @if($event->notes)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $event->notes }}</p>
            </div>
        @endif

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
            @if($event->checklist)
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
                        <div class="flex items-center gap-2">
                            <a href="{{ route('events.plan.print', $event) }}" target="_blank"
                               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg" title="Друк">
                                🖨️ Друк
                            </a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto" style="min-height: 300px;">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-4 text-left w-20">Час</th>
                                <th class="px-3 py-4 text-left">Що відбувається</th>
                                <th class="px-3 py-4 text-left whitespace-nowrap">Відповідальний</th>
                                <th class="px-3 py-4 text-left w-32">Коментарі</th>
                                <th class="px-2 py-4 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($event->planItems->sortBy('sort_order') as $item)
                                <tr class="hover:bg-blue-50/50 dark:hover:bg-gray-700/50 group" data-id="{{ $item->id }}">
                                    {{-- Час --}}
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
                                        <input type="text"
                                               value="{{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}"
                                               placeholder="--:--"
                                               @change="updateField({{ $item->id }}, 'start_time', $event.target.value)"
                                               class="w-full px-1 py-1 text-sm font-semibold text-primary-600 dark:text-primary-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded">
                                    </td>
                                    {{-- Що відбувається --}}
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
                                        <input type="text"
                                               value="{{ $item->title }}"
                                               placeholder="Опис пункту..."
                                               @change="updateField({{ $item->id }}, 'title', $event.target.value)"
                                               class="w-full px-1 py-1 text-sm text-gray-900 dark:text-white bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded">
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
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700"
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
                                        <div class="flex flex-wrap items-center gap-1.5">
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
                                                    <button type="button" @click="removePerson(index)" class="opacity-50 hover:opacity-100 hover:text-red-500">
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
                                    <td class="px-3 py-3 border-r border-gray-100 dark:border-gray-700">
                                        <input type="text"
                                               value="{{ $item->notes }}"
                                               placeholder="Примітки..."
                                               @change="updateField({{ $item->id }}, 'notes', $event.target.value)"
                                               class="w-full px-1 py-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 focus:ring-1 focus:ring-primary-500 rounded">
                                    </td>
                                    {{-- Actions --}}
                                    <td class="px-3 py-3 text-center">
                                        <button type="button"
                                                @click="deleteItem({{ $item->id }})"
                                                class="p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
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
                        <input type="text" x-model="newItem.start_time" placeholder="Час"
                               class="w-20 px-2 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
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
                        <a href="{{ route('events.edit', $event) }}"
                           class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Редагувати подію</span>
                        </a>

                        <x-delete-confirm
                            :action="route('events.destroy', $event)"
                            title="Видалити подію?"
                            message="Ви впевнені, що хочете видалити цю подію? Усі призначення також будуть видалені."
                            button-text="Видалити подію"
                            button-class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-600 dark:text-red-400"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Видалити подію</span>
                        </x-delete-confirm>
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
                    window.location.reload();
                } else {
                    this.showMessage('Помилка додавання', 'error');
                }
            } catch (err) {
                console.error('Add error:', err);
                this.showMessage('Помилка з\'єднання', 'error');
            }
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
                    window.location.reload();
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

// Service Plan Manager
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
                    alert('Помилка: ' + response.status);
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (err) {
                console.error('Fetch error:', err);
                alert('Помилка з\'єднання');
            }
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
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
            }
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
</script>
@endpush
@endsection
