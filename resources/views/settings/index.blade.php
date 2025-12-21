@extends('layouts.app')

@section('title', 'Налаштування')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Church settings -->
    <form method="POST" action="{{ route('settings.church') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Церква</h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Назва *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $church->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Місто *</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $church->city) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Адреса</label>
                <input type="text" name="address" id="address" value="{{ old('address', $church->address) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Логотип</label>
                @if($church->logo)
                    <div class="mb-2">
                        <img src="{{ Storage::url($church->logo) }}" class="w-16 h-16 object-contain">
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>

    <!-- Telegram bot -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Telegram бот</h2>
        </div>

        <form method="POST" action="{{ route('settings.telegram') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700 mb-1">Токен бота</label>
                <input type="text" name="telegram_bot_token" id="telegram_bot_token"
                       value="{{ old('telegram_bot_token', $church->telegram_bot_token) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                <p class="mt-1 text-sm text-gray-500">Створіть бота через @BotFather і вставте токен сюди</p>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти
                </button>

                @if($church->telegram_bot_token)
                    <form method="POST" action="{{ route('settings.telegram.test') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                            Перевірити підключення
                        </button>
                    </form>
                @endif
            </div>
        </form>
    </div>

    <!-- Notifications -->
    <form method="POST" action="{{ route('settings.notifications') }}" class="bg-white rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Сповіщення</h2>
        </div>

        <div class="p-6 space-y-4">
            @php $notifications = $church->settings['notifications'] ?? []; @endphp

            <label class="flex items-center">
                <input type="checkbox" name="reminder_day_before" value="1"
                       {{ $notifications['reminder_day_before'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700">Надсилати нагадування за 1 день до служіння</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="reminder_same_day" value="1"
                       {{ $notifications['reminder_same_day'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700">Надсилати нагадування в день служіння (за 2 години)</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="notify_leader_on_decline" value="1"
                       {{ $notifications['notify_leader_on_decline'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700">Сповіщати лідера про відмови</span>
            </label>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>

    <!-- Expense categories -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Категорії витрат</h2>
        </div>

        <div class="p-6">
            <div class="space-y-2 mb-4">
                @foreach($expenseCategories as $category)
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <span class="text-gray-900">{{ $category->name }}</span>
                        <form method="POST" action="{{ route('settings.expense-categories.destroy', $category) }}"
                              onsubmit="return confirm('Видалити категорію?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                Видалити
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('settings.expense-categories.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Нова категорія" required
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                    Додати
                </button>
            </form>
        </div>
    </div>

    <!-- Tags -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Теги для людей</h2>
        </div>

        <div class="p-6">
            <div class="space-y-2 mb-4">
                @foreach($tags as $tag)
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center">
                            <span class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $tag->color }}"></span>
                            <span class="text-gray-900">{{ $tag->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('tags.destroy', $tag) }}"
                              onsubmit="return confirm('Видалити тег?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                Видалити
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('tags.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Новий тег" required
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <input type="color" name="color" value="#3b82f6"
                       class="w-12 h-10 border border-gray-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                    Додати
                </button>
            </form>
        </div>
    </div>

    <!-- Users management link -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Користувачі</h2>
                <p class="text-sm text-gray-500">{{ $users->count() }} користувачів</p>
            </div>
            <a href="{{ route('settings.users.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                Керувати
            </a>
        </div>
    </div>
</div>
@endsection
