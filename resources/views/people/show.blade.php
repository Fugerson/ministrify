@extends('layouts.app')

@section('title', $person->full_name)

@section('actions')
<a href="{{ route('people.edit', $person) }}"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    Редагувати
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start space-x-6">
            <div class="flex-shrink-0">
                @if($person->photo)
                    <img class="w-24 h-24 rounded-full object-cover" src="{{ Storage::url($person->photo) }}" alt="">
                @else
                    <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-3xl text-gray-500 font-medium">{{ substr($person->first_name, 0, 1) }}</span>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $person->full_name }}</h1>

                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($person->tags as $tag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    @if($person->phone)
                        <div>
                            <span class="text-gray-500">Телефон:</span>
                            <a href="tel:{{ $person->phone }}" class="block text-primary-600 hover:text-primary-500">{{ $person->phone }}</a>
                        </div>
                    @endif
                    @if($person->email)
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <a href="mailto:{{ $person->email }}" class="block text-primary-600 hover:text-primary-500">{{ $person->email }}</a>
                        </div>
                    @endif
                    @if($person->telegram_username)
                        <div>
                            <span class="text-gray-500">Telegram:</span>
                            <span class="block text-gray-900">{{ $person->telegram_username }}</span>
                        </div>
                    @endif
                    @if($person->address)
                        <div>
                            <span class="text-gray-500">Адреса:</span>
                            <span class="block text-gray-900">{{ $person->address }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Статистика</h2>

            <div class="space-y-4">
                @if($person->joined_date)
                    <div>
                        <span class="text-sm text-gray-500">В церкві з:</span>
                        <span class="block text-lg font-medium text-gray-900">{{ $person->joined_date->format('d.m.Y') }}</span>
                    </div>
                @endif

                <div>
                    <span class="text-sm text-gray-500">Служінь цього місяця:</span>
                    <span class="block text-lg font-medium text-gray-900">{{ $stats['services_this_month'] }}</span>
                </div>

                <div>
                    <span class="text-sm text-gray-500">Відвідувань за 30 днів:</span>
                    <span class="block text-lg font-medium text-gray-900">{{ $stats['attendance_30_days'] }}</span>
                </div>

                @if($person->birth_date)
                    <div>
                        <span class="text-sm text-gray-500">День народження:</span>
                        <span class="block text-lg font-medium text-gray-900">{{ $person->birth_date->format('d.m') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ministries -->
        <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Служіння</h2>

            @if($person->ministries->count() > 0)
                <div class="space-y-3">
                    @foreach($person->ministries as $ministry)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-medium">{{ $ministry->icon }} {{ $ministry->name }}</span>
                                @php
                                    $positionIds = is_array($ministry->pivot->position_ids)
                                        ? $ministry->pivot->position_ids
                                        : json_decode($ministry->pivot->position_ids ?? '[]', true);
                                    $positions = $ministry->positions->whereIn('id', $positionIds ?? []);
                                @endphp
                                @if($positions->count() > 0)
                                    <p class="text-sm text-gray-500">{{ $positions->pluck('name')->implode(', ') }}</p>
                                @endif
                            </div>
                            <a href="{{ route('ministries.show', $ministry) }}" class="text-sm text-primary-600 hover:text-primary-500">
                                Переглянути
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Не бере участь у служіннях</p>
            @endif
        </div>
    </div>

    <!-- Recent assignments -->
    @if($person->assignments->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Останні призначення</h2>
        </div>
        <div class="divide-y">
            @foreach($person->assignments->take(10) as $assignment)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $assignment->event->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $assignment->event->date->format('d.m.Y') }} &bull;
                            {{ $assignment->event->ministry->name }} &bull;
                            {{ $assignment->position->name }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $assignment->status === 'confirmed' ? 'bg-green-100 text-green-800' :
                           ($assignment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $assignment->status_label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($person->notes)
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Нотатки</h2>
        <p class="text-gray-700 whitespace-pre-wrap">{{ $person->notes }}</p>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('people.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Назад до списку
        </a>

        <form method="POST" action="{{ route('people.destroy', $person) }}"
              onsubmit="return confirm('Ви впевнені, що хочете видалити цю людину?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">
                Видалити
            </button>
        </form>
    </div>
</div>
@endsection
