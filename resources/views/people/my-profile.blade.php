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
                        <p class="text-gray-500">{{ auth()->user()->role === 'admin' ? 'Адміністратор' : (auth()->user()->role === 'leader' ? 'Лідер' : 'Служитель') }}</p>
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
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
                 x-data="{
                     loading: false,
                     code: null,
                     botUsername: null,
                     error: null,
                     copied: false,
                     async generateCode() {
                         this.loading = true;
                         this.error = null;
                         try {
                             const response = await fetch('{{ route('my-profile.telegram.generate') }}', {
                                 method: 'POST',
                                 headers: {
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json',
                                 }
                             });
                             const data = await response.json();
                             if (data.error) {
                                 this.error = data.error;
                             } else {
                                 this.code = data.code;
                                 this.botUsername = data.bot_username;
                             }
                         } catch (e) {
                             this.error = 'Помилка з\'єднання';
                         }
                         this.loading = false;
                     },
                     copyCode() {
                         navigator.clipboard.writeText(this.code);
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2000);
                     }
                 }">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Telegram бот</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Сповіщення про служіння</p>
                    </div>
                </div>

                @if($person->telegram_chat_id)
                    <!-- Connected state -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-green-800 dark:text-green-200">Telegram підключено</p>
                                <p class="text-sm text-green-600 dark:text-green-400">Ви отримуватимете сповіщення</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <p class="mb-2"><strong>Доступні команди:</strong></p>
                        <ul class="space-y-1">
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/schedule</code> — ваш розклад</li>
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/next</code> — наступне служіння</li>
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/help</code> — допомога</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('my-profile.telegram.unlink') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Ви впевнені? Ви перестанете отримувати сповіщення.')"
                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                            Від'єднати Telegram
                        </button>
                    </form>
                @else
                    <!-- Not connected state -->
                    <div class="space-y-4">
                        <p class="text-gray-600 dark:text-gray-400">
                            Підключіть Telegram, щоб отримувати сповіщення про призначення на служіння, нагадування та важливі повідомлення.
                        </p>

                        <!-- Error message -->
                        <div x-show="error" x-cloak class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <p class="text-red-700 dark:text-red-400" x-text="error"></p>
                        </div>

                        <!-- Code display -->
                        <div x-show="code" x-cloak class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                Ваш код для підключення (дійсний 10 хвилин):
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg px-4 py-3 font-mono text-2xl font-bold text-center tracking-widest text-primary-600 dark:text-primary-400 border border-gray-200 dark:border-gray-700"
                                     x-text="code"></div>
                                <button @click="copyCode()" type="button"
                                        class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        :class="{ 'text-green-600': copied }">
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <p><strong>Інструкція:</strong></p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>
                                        Відкрийте бота
                                        <a x-show="botUsername" :href="'https://t.me/' + botUsername" target="_blank"
                                           class="text-primary-600 dark:text-primary-400 hover:underline">
                                            @<span x-text="botUsername"></span>
                                        </a>
                                    </li>
                                    <li>Натисніть <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/start</code></li>
                                    <li>Введіть код <span class="font-mono font-bold" x-text="code"></span></li>
                                </ol>
                            </div>
                        </div>

                        <!-- Generate button -->
                        <button @click="generateCode()" :disabled="loading" type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-xl transition-colors">
                            <svg x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg x-show="!loading" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                            </svg>
                            <span x-text="code ? 'Отримати новий код' : 'Підключити Telegram'"></span>
                        </button>
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
