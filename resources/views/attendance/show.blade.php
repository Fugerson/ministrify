@extends('layouts.app')

@section('title', 'Відвідуваність: ' . $attendance->date->format('d.m.Y'))

@section('actions')
<a href="{{ route('attendance.edit', $attendance) }}"
   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
    Редагувати
</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $attendance->date->format('d.m.Y') }}</h1>
                @if($attendance->event)
                    <p class="text-gray-500">{{ $attendance->event->ministry->icon }} {{ $attendance->event->title }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-4xl font-bold text-primary-600">{{ $attendance->total_count }}</p>
                <p class="text-gray-500">загальна кількість</p>
            </div>
        </div>

        @if($attendance->notes)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">{{ $attendance->notes }}</p>
            </div>
        @endif
    </div>

    @if($attendance->records->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Присутні з бази ({{ $attendance->present_count }})</h2>
        </div>
        <div class="divide-y">
            @foreach($attendance->records->where('present', true) as $record)
                <div class="px-6 py-3 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 text-sm">{{ substr($record->person->first_name, 0, 1) }}</span>
                    </div>
                    <a href="{{ route('people.show', $record->person) }}" class="ml-3 text-gray-900 hover:text-primary-600">
                        {{ $record->person->full_name }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('attendance.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Назад
        </a>
        <form method="POST" action="{{ route('attendance.destroy', $attendance) }}"
              onsubmit="return confirm('Видалити цей запис?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">
                Видалити
            </button>
        </form>
    </div>
</div>
@endsection
