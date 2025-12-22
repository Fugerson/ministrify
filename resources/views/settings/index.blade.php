@extends('layouts.app')

@section('title', 'Налаштування')

@section('content')
<x-page-help page="settings" />

<div class="max-w-4xl mx-auto space-y-6" x-data="{ activeTab: 'general' }">
    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2">
        <div class="flex flex-wrap gap-2">
            <button @click="activeTab = 'general'"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'general' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
                Загальні
            </button>
            <button @click="activeTab = 'public'"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'public' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
                Публічний сайт
            </button>
            <button @click="activeTab = 'integrations'"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'integrations' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
                Інтеграції
            </button>
            <button @click="activeTab = 'data'"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'data' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
                Категорії та теги
            </button>
            <button @click="activeTab = 'payments'"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'payments' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors">
                Платежі
            </button>
        </div>
    </div>

    <!-- General Tab -->
    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
    <!-- Church settings -->
    <form method="POST" action="{{ route('settings.church') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Основна інформація</h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $church->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Місто *</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $church->city) }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Адреса</label>
                <input type="text" name="address" id="address" value="{{ old('address', $church->address) }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Логотип</label>
                @if($church->logo)
                    <div class="mb-2">
                        <img src="{{ Storage::url($church->logo) }}" class="w-16 h-16 object-contain rounded-lg">
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>

    <!-- Notifications -->
    <form method="POST" action="{{ route('settings.notifications') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Сповіщення</h2>
        </div>

        <div class="p-6 space-y-4">
            @php $notifications = $church->settings['notifications'] ?? []; @endphp

            <label class="flex items-center">
                <input type="checkbox" name="reminder_day_before" value="1"
                       {{ $notifications['reminder_day_before'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Надсилати нагадування за 1 день до служіння</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="reminder_same_day" value="1"
                       {{ $notifications['reminder_same_day'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Надсилати нагадування в день служіння (за 2 години)</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="notify_leader_on_decline" value="1"
                       {{ $notifications['notify_leader_on_decline'] ?? false ? 'checked' : '' }}
                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Сповіщати лідера про відмови</span>
            </label>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти
            </button>
        </div>
    </form>

    <!-- Users management link -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Користувачі</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $users->count() }} користувачів</p>
            </div>
            <a href="{{ route('settings.users.index') }}"
               class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                Керувати
            </a>
        </div>
    </div>
    </div>

    <!-- Public Site Tab -->
    <div x-show="activeTab === 'public'" x-cloak class="space-y-6">
    <form method="POST" action="{{ route('settings.public-site') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Enable/Disable & URL -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Публічний сайт церкви</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Створіть мін-сайт для вашої громади</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Активувати публічний сайт</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Дозволити публічний доступ до сторінки церкви</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="public_site_enabled" value="1"
                               {{ $church->public_site_enabled ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL сайту *</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500 dark:text-gray-400 text-sm">
                            {{ url('/c/') }}/
                        </span>
                        <input type="text" name="slug" value="{{ old('slug', $church->slug ?? Str::slug($church->name)) }}" required
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-r-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="my-church">
                    </div>
                    @if($church->slug && $church->public_site_enabled)
                        <p class="mt-2 text-sm">
                            <a href="{{ route('public.church', $church->slug) }}" target="_blank" class="text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                Відкрити публічний сайт
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </p>
                    @endif
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис церкви</label>
                    <textarea name="public_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="Коротко про вашу церкву...">{{ old('public_description', $church->public_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фонове зображення</label>
                    @if($church->cover_image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($church->cover_image) }}" class="h-32 object-cover rounded-lg">
                        </div>
                    @endif
                    <input type="file" name="cover_image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Розклад богослужінь</label>
                    <input type="text" name="service_times" value="{{ old('service_times', $church->service_times) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="Неділя 10:00, Середа 19:00">
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Контактна інформація</h2>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Публічний Email</label>
                        <input type="email" name="public_email" value="{{ old('public_email', $church->public_email) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="info@church.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Публічний телефон</label>
                        <input type="text" name="public_phone" value="{{ old('public_phone', $church->public_phone) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="+380 XX XXX XX XX">
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Соціальні мережі</h2>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Веб-сайт</label>
                        <input type="url" name="website_url" value="{{ old('website_url', $church->website_url) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook</label>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url', $church->facebook_url) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="https://facebook.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $church->instagram_url) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="https://instagram.com/...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url', $church->youtube_url) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="https://youtube.com/...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pastor Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Слово пастора</h2>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ім'я пастора</label>
                        <input type="text" name="pastor_name" value="{{ old('pastor_name', $church->pastor_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фото пастора</label>
                        @if($church->pastor_photo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($church->pastor_photo) }}" class="w-16 h-16 object-cover rounded-lg">
                            </div>
                        @endif
                        <input type="file" name="pastor_photo" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Привітальне слово</label>
                    <textarea name="pastor_message" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="Напишіть привітальне слово для відвідувачів...">{{ old('pastor_message', $church->pastor_message) }}</textarea>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти налаштування сайту
                </button>
            </div>
        </div>
    </form>
    </div>

    <!-- Integrations Tab -->
    <div x-show="activeTab === 'integrations'" x-cloak class="space-y-6"
         x-data="{
             status: null,
             loading: true,
             async loadStatus() {
                 this.loading = true;
                 try {
                     const response = await fetch('{{ route('settings.telegram.status') }}');
                     this.status = await response.json();
                 } catch (e) {
                     this.status = { connected: false, error: 'Помилка з\'єднання' };
                 }
                 this.loading = false;
             }
         }"
         x-init="loadStatus()">

    <!-- Telegram bot -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Telegram бот</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Сповіщення волонтерам</p>
                </div>
            </div>

            <!-- Status indicator -->
            <div x-show="!loading" class="flex items-center gap-2">
                <template x-if="status?.connected">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-medium">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Підключено
                    </span>
                </template>
                <template x-if="!status?.connected">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full text-sm font-medium">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        Не налаштовано
                    </span>
                </template>
            </div>
        </div>

        <!-- Bot status details -->
        <div x-show="status?.connected && !loading" x-cloak class="px-6 py-4 bg-green-50 dark:bg-green-900/10 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Бот</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        <a :href="'https://t.me/' + status.bot_username" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                            @<span x-text="status.bot_username"></span>
                        </a>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Webhook</p>
                    <p class="font-medium" :class="status.webhook_url ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'"
                       x-text="status.webhook_url ? 'Налаштовано' : 'Не налаштовано'"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Очікує</p>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="status.pending_updates + ' оновлень'"></p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Token form -->
            <form method="POST" action="{{ route('settings.telegram') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="telegram_bot_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Токен бота</label>
                    <input type="password" name="telegram_bot_token" id="telegram_bot_token"
                           value="{{ old('telegram_bot_token', $church->telegram_bot_token) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Створіть бота через <a href="https://t.me/BotFather" target="_blank" class="text-primary-600 hover:underline">@BotFather</a> і вставте токен
                    </p>
                </div>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти токен
                </button>
            </form>

            @if($church->telegram_bot_token)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Дії</h3>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('settings.telegram.test') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Перевірити підключення
                        </button>
                    </form>

                    <form method="POST" action="{{ route('settings.telegram.webhook') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Налаштувати Webhook
                        </button>
                    </form>

                    <button @click="loadStatus()" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Оновити статус
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Notification settings -->
    @if($church->telegram_bot_token)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Налаштування сповіщень</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Коли надсилати нагадування</p>
        </div>

        <form method="POST" action="{{ route('settings.notifications') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                <input type="checkbox" name="reminder_day_before" value="1"
                       {{ ($church->settings['notifications']['reminder_day_before'] ?? false) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Нагадування за день до</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Надсилати о 18:00 напередодні події</p>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                <input type="checkbox" name="reminder_same_day" value="1"
                       {{ ($church->settings['notifications']['reminder_same_day'] ?? false) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Нагадування в день події</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Надсилати за 2 години до початку</p>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                <input type="checkbox" name="notify_leader_on_decline" value="1"
                       {{ ($church->settings['notifications']['notify_leader_on_decline'] ?? false) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Повідомлення лідеру при відмові</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Коли волонтер відхиляє призначення</p>
                </div>
            </label>

            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Зберегти налаштування
            </button>
        </form>
    </div>
    @endif
    </div>

    <!-- Data Tab -->
    <div x-show="activeTab === 'data'" x-cloak class="space-y-6">
    <!-- Expense categories -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Категорії витрат</h2>
        </div>

        <div class="p-6">
            <div class="space-y-2 mb-4">
                @foreach($expenseCategories as $category)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <span class="text-gray-900 dark:text-white">{{ $category->name }}</span>
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
                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                    Додати
                </button>
            </form>
        </div>
    </div>

    <!-- Tags -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Теги для людей</h2>
        </div>

        <div class="p-6">
            <div class="space-y-2 mb-4">
                @foreach($tags as $tag)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <span class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $tag->color }}"></span>
                            <span class="text-gray-900 dark:text-white">{{ $tag->name }}</span>
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
                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <input type="color" name="color" value="#3b82f6"
                       class="w-12 h-10 border border-gray-300 dark:border-gray-600 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                    Додати
                </button>
            </form>
        </div>
    </div>
    </div>

    <!-- Payments Tab -->
    <div x-show="activeTab === 'payments'" x-cloak class="space-y-6">
    @php
        $paymentSettings = $church->payment_settings ?? [];
    @endphp

    <form method="POST" action="{{ route('settings.payments') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- LiqPay -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">LiqPay</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Прийом платежів Visa/Mastercard</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="liqpay_enabled" value="1"
                           {{ !empty($paymentSettings['liqpay_enabled']) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        Для налаштування LiqPay, зареєструйтеся на
                        <a href="https://www.liqpay.ua/uk/adminbusiness" target="_blank" class="underline font-medium">liqpay.ua</a>
                        та отримайте ключі API в особистому кабінеті.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Public Key</label>
                    <input type="text" name="liqpay_public_key"
                           value="{{ old('liqpay_public_key', $paymentSettings['liqpay_public_key'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono"
                           placeholder="sandbox_XXXXXXXXXXXX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Private Key</label>
                    <input type="password" name="liqpay_private_key"
                           value="{{ old('liqpay_private_key', $paymentSettings['liqpay_private_key'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono"
                           placeholder="sandbox_XXXXXXXXXXXX">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Зберігається в зашифрованому вигляді</p>
                </div>
            </div>
        </div>

        <!-- Monobank -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-black flex items-center justify-center">
                        <span class="text-white font-bold text-sm">mono</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monobank</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Банка для збору коштів</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="monobank_enabled" value="1"
                           {{ !empty($paymentSettings['monobank_enabled']) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Створіть банку для збору в додатку Monobank і вставте посилання або ID банки.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID банки або посилання</label>
                    <input type="text" name="monobank_jar_id"
                           value="{{ old('monobank_jar_id', $paymentSettings['monobank_jar_id'] ?? '') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="https://send.monobank.ua/jar/XXXXXXXXX або jar/XXXXXXXXX">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Наприклад: https://send.monobank.ua/jar/ABC123def або просто ABC123def</p>
                </div>
            </div>
        </div>

        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            Зберегти налаштування платежів
        </button>
    </form>
    </div>
</div>
@endsection
