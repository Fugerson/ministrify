@extends('layouts.app')

@section('title', $event->title)

@section('actions')
@can('manage-ministry', $event->ministry)
<div class="flex items-center space-x-2">
    <form method="POST" action="{{ route('assignments.notify-all', $event) }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            Надіслати сповіщення
        </button>
    </form>
    <a href="{{ route('events.edit', $event) }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        Редагувати
    </a>
</div>
@endcan
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <span class="text-4xl">{{ $event->ministry->icon }}</span>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
                    <p class="text-gray-500">{{ $event->ministry->name }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">{{ $event->date->format('d.m.Y') }}</p>
                <p class="text-gray-500">{{ $event->time->format('H:i') }}</p>
            </div>
        </div>

        @if($event->notes)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">{{ $event->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Positions and assignments -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Позиції</h2>
        </div>

        <div class="divide-y">
            @foreach($event->ministry->positions as $position)
                @php
                    $assignment = $event->assignments->firstWhere('position_id', $position->id);
                @endphp
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $position->name }}</p>

                            @if($assignment)
                                <div class="mt-2 flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-600 text-sm">{{ substr($assignment->person->first_name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('people.show', $assignment->person) }}"
                                           class="text-gray-900 hover:text-primary-600">
                                            {{ $assignment->person->full_name }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            @if($assignment->isConfirmed())
                                                <span class="text-green-600">&#9989; Підтверджено</span>
                                            @elseif($assignment->isPending())
                                                <span class="text-yellow-600">&#9203; Очікує підтвердження</span>
                                            @else
                                                <span class="text-red-600">&#10060; Відхилено</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="mt-2 text-sm text-yellow-600">&#9888; Не призначено</p>
                            @endif
                        </div>

                        @can('manage-ministry', $event->ministry)
                        <div>
                            @if($assignment)
                                <form method="POST" action="{{ route('assignments.destroy', $assignment) }}"
                                      onsubmit="return confirm('Видалити призначення?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                        Видалити
                                    </button>
                                </form>
                            @else
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" type="button"
                                            class="px-3 py-1 bg-primary-100 text-primary-700 text-sm rounded-lg hover:bg-primary-200">
                                        Призначити
                                    </button>

                                    <div x-show="open" x-cloak @click.away="open = false"
                                         class="absolute right-0 mt-2 w-64 bg-white border rounded-lg shadow-lg z-10">
                                        <div class="p-2 max-h-64 overflow-y-auto">
                                            @foreach($availablePeople->filter(fn($p) => $p->hasPositionInMinistry($event->ministry, $position)) as $person)
                                                <form method="POST" action="{{ route('assignments.store', $event) }}">
                                                    @csrf
                                                    <input type="hidden" name="position_id" value="{{ $position->id }}">
                                                    <input type="hidden" name="person_id" value="{{ $person->id }}">
                                                    <button type="submit"
                                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 rounded">
                                                        {{ $person->full_name }}
                                                    </button>
                                                </form>
                                            @endforeach

                                            @if($availablePeople->filter(fn($p) => $p->hasPositionInMinistry($event->ministry, $position))->isEmpty())
                                                <p class="px-3 py-2 text-sm text-gray-500">Немає доступних людей</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('schedule') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Назад до розкладу
        </a>

        @can('manage-ministry', $event->ministry)
        <form method="POST" action="{{ route('events.destroy', $event) }}"
              onsubmit="return confirm('Ви впевнені, що хочете видалити цю подію?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">
                Видалити подію
            </button>
        </form>
        @endcan
    </div>
</div>
@endsection
