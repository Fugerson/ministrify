@extends('layouts.app')

@section('title', 'Telegram розсилка')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Telegram розсилка</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Надішліть повідомлення в Telegram</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if(!$hasBot)
        <div class="bg-amber-100 dark:bg-amber-900/30 border border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-400 px-4 py-3 rounded-lg">
            Telegram бот не налаштований. <a href="{{ route('settings.index') }}" class="underline">Налаштувати</a>
        </div>
    @else
        <form method="POST" action="{{ route('telegram.broadcast.send') }}" class="space-y-6">
            @csrf

            <!-- Message -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Повідомлення</h2>
                </div>
                <div class="p-6">
                    <textarea name="message" rows="6" required
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="Введіть текст повідомлення...

Можна використовувати:
*жирний текст*
_курсив_
`код`">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Підтримується Markdown: *жирний*, _курсив_, `код`
                    </p>
                </div>
            </div>

            <!-- Recipients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Отримувачі</h2>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-gray-600 dark:text-gray-400">Вибрати всіх</span>
                    </label>
                </div>
                <div class="p-6">
                    @if($recipients->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($recipients as $person)
                                <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" name="recipients[]" value="{{ $person->id }}"
                                           class="recipient-checkbox w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                            <span class="text-primary-600 dark:text-primary-400 text-sm font-medium">
                                                {{ mb_substr($person->first_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $person->first_name }} {{ $person->last_name }}
                                            </p>
                                            @if($person->telegram_username)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $person->telegram_username }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('recipients')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Немає отримувачів</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Ніхто ще не підключив Telegram.<br>
                                Люди можуть підключитись через бота.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            @if($recipients->count() > 0)
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Надіслати
                    </button>
                </div>
            @endif
        </form>
    @endif
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.recipient-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>
@endsection
