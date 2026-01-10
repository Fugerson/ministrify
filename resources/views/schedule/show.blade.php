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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- План події -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $event->is_service ? 'План події' : 'Відповідальності' }}</h2>
                        </div>
                        @php
                            $allItems = $event->is_service ? $event->planItems : collect();
                            $responsibilities = $event->responsibilities;
                            $totalItems = $allItems->count() + $responsibilities->count();
                            $confirmedResp = $responsibilities->where('status', 'confirmed')->count();
                            $totalMinutes = $allItems->sum('duration_minutes');
                        @endphp
                        <div class="flex items-center gap-3">
                            @if($totalItems > 0)
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($responsibilities->isNotEmpty())
                                        {{ $confirmedResp }}/{{ $responsibilities->count() }} підтв.
                                    @endif
                                    @if($allItems->isNotEmpty() && $totalMinutes > 0)
                                        @php $h = floor($totalMinutes/60); $m = $totalMinutes % 60; @endphp
                                        · @if($h > 0){{ $h }}год @endif{{ $m }}хв
                                    @endif
                                </span>
                            @endif
                            @if($event->is_service)
                                <a href="{{ route('events.plan.print', $event) }}" target="_blank"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Друк">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="plan-items-list">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2 text-left w-16">Час</th>
                                <th class="px-3 py-2 text-left">Пункт</th>
                                <th class="px-3 py-2 text-left w-32">Відповідальний</th>
                                <th class="px-3 py-2 text-left">Примітка</th>
                                <th class="px-3 py-2 w-20"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @if($event->is_service)
                        @foreach($event->planItems->sortBy('sort_order') as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 group" data-plan-id="{{ $item->id }}" x-data="{ editing: false }">
                                {{-- View Mode --}}
                                <template x-if="!editing">
                                    <td class="px-3 py-2 font-semibold text-primary-600 dark:text-primary-400 whitespace-nowrap">
                                        {{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white" @dblclick="editing = true" title="Подвійний клік для редагування">
                                        {{ $item->title }}
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                        @if($item->responsible_display)
                                            <div class="flex items-center gap-2">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $item->responsible_display }}
                                                </span>
                                                {{-- Status badge --}}
                                                @if($item->status === 'confirmed')
                                                    <span class="text-green-500" title="Підтверджено">✅</span>
                                                @elseif($item->status === 'declined')
                                                    <span class="text-red-500" title="Відхилено">❌</span>
                                                @endif
                                                {{-- Telegram button --}}
                                                @if($item->responsible_id)
                                                    @if($item->responsible?->telegram_chat_id)
                                                        <button type="button" onclick="sendTelegramNotify({{ $item->id }}, this)"
                                                                class="p-0.5 text-blue-400 hover:text-blue-600" title="Надіслати запит в Telegram">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <span class="text-gray-300 cursor-help" title="Telegram не підключений">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .37z"/>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">
                                        @if($item->notes)
                                            <span class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 px-2 py-0.5 rounded">{{ $item->notes }}</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button type="button" @click="editing = true" class="p-1 text-gray-400 hover:text-primary-600" title="Редагувати">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('events.plan.destroy', [$event, $item]) }}" class="inline" onsubmit="return confirm('Видалити?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-gray-400 hover:text-red-600" title="Видалити">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </template>

                                {{-- Edit Mode --}}
                                <template x-if="editing">
                                    <td colspan="5" class="px-3 py-2">
                                        <form method="POST" action="{{ route('events.plan.update', [$event, $item]) }}" class="flex flex-wrap gap-2 items-center" @submit="editing = false">
                                            @csrf
                                            @method('PUT')
                                            <input type="time" name="start_time" value="{{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '' }}"
                                                   class="w-28 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <input type="text" name="title" value="{{ $item->title }}" required placeholder="Назва"
                                                   class="flex-1 min-w-40 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                   @keydown.escape="editing = false">

                                            {{-- Person: select from list OR type manually --}}
                                            <select name="responsible_id" class="w-36 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                <option value="">-- зі списку --</option>
                                                @foreach($allPeople as $person)
                                                    <option value="{{ $person->id }}" {{ $item->responsible_id == $person->id ? 'selected' : '' }}>{{ $person->full_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-gray-400 text-xs">або</span>
                                            <input type="text" name="responsible_names" value="{{ $item->responsible_names }}" placeholder="вручну"
                                                   class="w-24 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                                            <input type="text" name="notes" value="{{ $item->notes }}" placeholder="Примітка"
                                                   class="flex-1 min-w-24 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <button type="submit" class="px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">OK</button>
                                            <button type="button" @click="editing = false" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded">✕</button>
                                        </form>
                                    </td>
                                </template>
                            </tr>
                        @endforeach
                    @endif

                    {{-- Empty state --}}
                    @if($event->is_service && $event->planItems->isEmpty())
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                                План порожній. Додайте перший пункт нижче.
                            </td>
                        </tr>
                    @endif
                        </tbody>
                    </table>
                </div>

                <!-- Quick Add Form -->
                <div class="p-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                    @if($event->is_service)
                        {{-- Service Plan form --}}
                        <form method="POST" action="{{ route('events.plan.store', $event) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="time" name="start_time" value="{{ now()->format('H:i') }}"
                                   class="w-28 px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                            <input type="text" name="title" required placeholder="Назва пункту"
                                   class="flex-1 min-w-40 px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                            {{-- Person: select OR manual --}}
                            <select name="responsible_id" class="w-40 px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                                <option value="">-- зі списку --</option>
                                @foreach($allPeople as $person)
                                    <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                @endforeach
                            </select>
                            <span class="text-gray-400 text-xs">або</span>
                            <input type="text" name="responsible_names" placeholder="вручну"
                                   class="w-24 px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                            <input type="text" name="notes" placeholder="Примітка"
                                   class="w-28 px-2 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                            <button type="submit" class="px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg font-medium">
                                Додати
                            </button>
                        </form>
                    @else
                        {{-- Simple responsibility form --}}
                        <form method="POST" action="{{ route('events.responsibilities.store', $event) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="name" required placeholder="Нова відповідальність"
                                   class="flex-1 px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">
                                Додати
                            </button>
                        </form>
                    @endif
                </div>
            </div>

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
                             x-show="!search || '{{ strtolower($person->full_name) }}'.includes(search.toLowerCase())">
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

        <!-- Sidebar -->
        <div class="space-y-6">
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
