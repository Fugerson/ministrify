@extends('layouts.app')

@section('title', 'Мій профіль')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Мій профіль</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center space-x-4 mb-6">
                    @if($person->photo)
                    <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->full_name }}" class="w-20 h-20 rounded-full object-cover">
                    @else
                    <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-2xl text-gray-500">{{ mb_substr($person->first_name, 0, 1) }}{{ mb_substr($person->last_name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $person->full_name }}</h2>
                        <p class="text-gray-500">{{ auth()->user()->role === 'admin' ? 'Адміністратор' : (auth()->user()->role === 'leader' ? 'Лідер' : 'Волонтер') }}</p>
                    </div>
                </div>

                <form action="{{ route('my-profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Телефон</label>
                            <input type="text" name="phone" value="{{ old('phone', $person->phone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $person->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telegram</label>
                            <input type="text" name="telegram_username" value="{{ old('telegram_username', $person->telegram_username) }}" placeholder="@username"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Адреса</label>
                            <input type="text" name="address" value="{{ old('address', $person->address) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Зберегти
                        </button>
                    </div>
                </form>
            </div>

            <!-- Telegram connection -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Telegram бот</h3>
                @if($person->telegram_chat_id)
                <div class="flex items-center text-green-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Telegram підключено
                </div>
                @else
                <p class="text-gray-600 mb-4">Підключіть Telegram для отримання сповіщень про служіння.</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-2">1. Відкрийте бота в Telegram</p>
                    <p class="text-sm text-gray-600 mb-2">2. Натисніть /start</p>
                    <p class="text-sm text-gray-600">3. Введіть код підключення</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Upcoming assignments -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Мої служіння</h3>
                @if($upcomingAssignments->isEmpty())
                <p class="text-gray-500">Немає запланованих служінь</p>
                @else
                <div class="space-y-3">
                    @foreach($upcomingAssignments->take(5) as $assignment)
                    <div class="flex items-center justify-between py-2 border-b last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $assignment->event->date->format('d.m') }} - {{ $assignment->event->ministry->name }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->position->name }}</p>
                        </div>
                        <span class="text-lg">
                            @if($assignment->status === 'confirmed') ✅
                            @elseif($assignment->status === 'declined') ❌
                            @else ⏳
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Unavailable dates -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Дати недоступності</h3>

                @if($person->unavailableDates->isNotEmpty())
                <div class="space-y-2 mb-4">
                    @foreach($person->unavailableDates as $date)
                    <div class="flex items-center justify-between py-2 border-b last:border-0">
                        <div>
                            <p class="text-sm text-gray-900">{{ $date->date_from->format('d.m') }} - {{ $date->date_to->format('d.m.Y') }}</p>
                            @if($date->reason)
                            <p class="text-xs text-gray-500">{{ $date->reason }}</p>
                            @endif
                        </div>
                        <form action="{{ route('my-profile.unavailable.remove', $date) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('my-profile.unavailable.add') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600">З</label>
                            <input type="date" name="date_from" required min="{{ now()->format('Y-m-d') }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">По</label>
                            <input type="date" name="date_to" required min="{{ now()->format('Y-m-d') }}"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <input type="text" name="reason" placeholder="Причина (необов'язково)"
                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        Додати
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
