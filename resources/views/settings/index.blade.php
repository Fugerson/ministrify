@extends('layouts.app')

@section('title', 'Налаштування')

@section('content')
<div class="max-w-6xl mx-auto space-y-4 md:space-y-6" x-data="{
    activeTab: new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('settings_tab') || 'general',
    setTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('settings_tab', tab);
        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.delete('tab');
        window.history.replaceState({}, '', url);
    }
}">
    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2">
        <div class="flex overflow-x-auto gap-1 sm:gap-2 no-scrollbar">
            <button @click="setTab('general')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'general' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Загальні
            </button>
            <button @click="setTab('theme')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'theme' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Тема
            </button>
            <button @click="setTab('public')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'public' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Сайт
            </button>
            <button @click="setTab('integrations')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'integrations' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Інтеграції
            </button>
            <button @click="setTab('data')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'data' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Категорії
            </button>
            <button @click="setTab('finance')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'finance' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Фінанси
            </button>
            <button @click="setTab('users')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'users' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Користувачі
            </button>
            <button @click="setTab('permissions')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'permissions' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Права доступу
            </button>
            <button @click="setTab('audit')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'audit' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                Журнал дій
            </button>
        </div>
    </div>

    <!-- General Tab -->
    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
    <!-- Church settings -->
    <form method="POST" action="{{ route('settings.church') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        @csrf
        @method('PUT')

        <div class="px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">Основна інформація</h2>
        </div>

        <div class="p-4 md:p-6 space-y-4">
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
                        <img src="{{ Storage::url($church->logo) }}" alt="{{ $church->name }} логотип" class="w-16 h-16 object-contain rounded-lg">
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

    <!-- Onboarding -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
         x-data="{ restarting: false }">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Onboarding Wizard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if(auth()->user()->onboarding_completed)
                        Завершено {{ auth()->user()->onboarding_completed_at?->diffForHumans() ?? '' }}
                    @else
                        Не завершено
                    @endif
                </p>
            </div>
            <button type="button"
                    @click="if(confirm('Ви впевнені, що хочете перезапустити Onboarding Wizard?')) { restarting = true; fetch('{{ route('onboarding.restart') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }}).then(r => r.json()).then(d => { if(d.redirect) window.location.href = d.redirect; }).catch(() => restarting = false); }"
                    :disabled="restarting"
                    class="px-4 py-2 bg-primary-100 dark:bg-primary-900/30 hover:bg-primary-200 dark:hover:bg-primary-900/50 text-primary-700 dark:text-primary-300 rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                <svg x-show="!restarting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg x-show="restarting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"></circle>
                    <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" class="opacity-75"></path>
                </svg>
                <span x-text="restarting ? 'Перезапуск...' : 'Перезапустити'"></span>
            </button>
        </div>
    </div>

    </div>

    <!-- Theme Tab -->
    <div x-show="activeTab === 'theme'" x-cloak class="space-y-6">
        @php
            $currentDesign = $church->design_theme ?? 'modern';
            $currentColor = $church->primary_color ?? '#3b82f6';

            $designThemes = [
                [
                    'id' => 'modern',
                    'name' => 'Сучасний',
                    'desc' => 'Заокруглені кути, м\'які тіні, сучасний вигляд',
                    'preview' => 'bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900',
                    'card' => 'rounded-2xl shadow-lg',
                    'btn' => 'rounded-xl'
                ],
                [
                    'id' => 'minimal',
                    'name' => 'Мінімалістичний',
                    'desc' => 'Чисті лінії, гострі кути, без тіней',
                    'preview' => 'bg-gray-50 dark:bg-gray-950',
                    'card' => 'rounded border',
                    'btn' => 'rounded-sm uppercase text-xs tracking-wider'
                ],
                [
                    'id' => 'brutalist',
                    'name' => 'Бруталізм',
                    'desc' => 'Сміливий, різкий, високий контраст',
                    'preview' => 'bg-white dark:bg-black',
                    'card' => 'border-2 border-black dark:border-white',
                    'btn' => 'border-2 border-black dark:border-white uppercase font-bold'
                ],
                [
                    'id' => 'glass',
                    'name' => 'Скло',
                    'desc' => 'Прозорість, розмиття, ефект матового скла',
                    'preview' => 'bg-gradient-to-br from-purple-500 to-pink-500',
                    'card' => 'rounded-2xl bg-white/20 backdrop-blur-xl border border-white/30',
                    'btn' => 'rounded-xl bg-white/20 backdrop-blur'
                ],
                [
                    'id' => 'neumorphism',
                    'name' => 'Неоморфізм',
                    'desc' => 'М\'який UI, випуклі форми, внутрішні тіні',
                    'preview' => 'bg-gray-200 dark:bg-gray-800',
                    'card' => 'rounded-2xl shadow-[9px_9px_16px_rgba(163,177,198,0.6),-9px_-9px_16px_rgba(255,255,255,0.8)]',
                    'btn' => 'rounded-xl'
                ],
                [
                    'id' => 'corporate',
                    'name' => 'Корпоративний',
                    'desc' => 'Професійний, стриманий, класичний',
                    'preview' => 'bg-slate-50 dark:bg-slate-900',
                    'card' => 'rounded-lg shadow-sm border',
                    'btn' => 'rounded-md font-semibold tracking-wide'
                ],
                [
                    'id' => 'playful',
                    'name' => 'Грайливий',
                    'desc' => 'Кольоровий, веселий, великі заокруглення',
                    'preview' => 'bg-gradient-to-br from-yellow-200 via-pink-200 to-purple-300',
                    'card' => 'rounded-3xl border-3 border-purple-400 shadow-xl',
                    'btn' => 'rounded-full font-bold'
                ],
            ];
        @endphp

        <!-- Design Theme Selection -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Стиль дизайну</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Виберіть загальний вигляд вашого інтерфейсу</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($designThemes as $theme)
                        <form method="POST" action="{{ route('settings.design-theme') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="design_theme" value="{{ $theme['id'] }}">
                            <button type="submit"
                                    class="w-full text-left transition-all hover:scale-[1.02] {{ $currentDesign === $theme['id'] ? 'ring-2 ring-primary-500 ring-offset-2' : '' }}">
                                <!-- Preview Card -->
                                <div class="h-40 {{ $theme['preview'] }} rounded-t-xl p-4 flex items-center justify-center">
                                    <div class="w-full max-w-[200px] space-y-3">
                                        <!-- Mini card preview -->
                                        <div class="bg-white dark:bg-gray-800 {{ $theme['card'] }} p-3 text-xs">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-6 h-6 rounded-full bg-primary-500"></div>
                                                <div class="h-2 bg-gray-300 dark:bg-gray-600 rounded w-20"></div>
                                            </div>
                                            <div class="space-y-1">
                                                <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                                                <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                            </div>
                                        </div>
                                        <!-- Mini button preview -->
                                        <div class="flex gap-2">
                                            <div class="bg-primary-500 text-white {{ $theme['btn'] }} px-3 py-1 text-[10px]">Кнопка</div>
                                            <div class="bg-gray-200 dark:bg-gray-700 {{ $theme['btn'] }} px-3 py-1 text-[10px]">Скасувати</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Theme info -->
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-b-xl p-4 border border-t-0 border-gray-200 dark:border-gray-700 {{ $currentDesign === $theme['id'] ? 'border-primary-500' : '' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $theme['name'] }}</h3>
                                        @if($currentDesign === $theme['id'])
                                            <span class="text-xs bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">Активний</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $theme['desc'] }}</p>
                                </div>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Color Presets -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Акцентний колір</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Виберіть основний колір інтерфейсу</p>
            </div>

            <div class="p-6">
                @php
                    $colorPresets = [
                        ['color' => '#3b82f6', 'name' => 'Синій'],
                        ['color' => '#8b5cf6', 'name' => 'Фіолетовий'],
                        ['color' => '#10b981', 'name' => 'Смарагдовий'],
                        ['color' => '#ef4444', 'name' => 'Червоний'],
                        ['color' => '#f59e0b', 'name' => 'Бурштиновий'],
                        ['color' => '#ec4899', 'name' => 'Рожевий'],
                        ['color' => '#6366f1', 'name' => 'Індіго'],
                        ['color' => '#14b8a6', 'name' => 'Бірюзовий'],
                        ['color' => '#84cc16', 'name' => 'Лайм'],
                        ['color' => '#f97316', 'name' => 'Помаранч'],
                        ['color' => '#06b6d4', 'name' => 'Блакитний'],
                        ['color' => '#a855f7', 'name' => 'Пурпур'],
                    ];
                @endphp
                <div class="flex flex-wrap gap-3">
                    @foreach($colorPresets as $preset)
                        <form method="POST" action="{{ route('settings.theme-color') }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="primary_color" value="{{ $preset['color'] }}">
                            <button type="submit"
                                    class="group relative w-12 h-12 rounded-xl border-2 transition-all hover:scale-110 {{ $currentColor === $preset['color'] ? 'border-gray-900 dark:border-white ring-2 ring-offset-2 ring-gray-400' : 'border-transparent hover:border-gray-300 dark:hover:border-gray-500' }}"
                                    style="background-color: {{ $preset['color'] }}"
                                    title="{{ $preset['name'] }}">
                                @if($currentColor === $preset['color'])
                                    <svg class="w-5 h-5 text-white absolute inset-0 m-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </form>
                    @endforeach
                    <!-- Custom color picker -->
                    <form method="POST" action="{{ route('settings.theme-color') }}" class="inline-flex items-center gap-2">
                        @csrf
                        @method('PUT')
                        <input type="color" name="primary_color" value="{{ $currentColor }}"
                               class="w-12 h-12 rounded-xl border-2 border-gray-200 dark:border-gray-700 cursor-pointer"
                               onchange="this.form.submit()">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Site Tab -->
    <div x-show="activeTab === 'public'" x-cloak class="space-y-6">
    <!-- Website Builder Link -->
    <a href="{{ route('website-builder.index') }}" class="block bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl shadow-sm p-5 hover:from-primary-600 hover:to-primary-700 transition-all group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Конструктор сайту</h3>
                    <p class="text-sm text-white/80">Редагуйте сторінки, меню та дизайн</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-white/70 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

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
                           >
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
                               >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Публічний телефон</label>
                        <input type="text" name="public_phone" value="{{ old('public_phone', $church->public_phone) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               >
                    </div>
                </div>
            </div>
        </div>

        <!-- Website -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Веб-сайт</h2>
            </div>

            <div class="p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL веб-сайту</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $church->website_url) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="https://...">
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

    <!-- Payment Settings for Public Site -->
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">Прийом пожертв Visa/Mastercard на публічному сайті</p>
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">Банка для збору пожертв</p>
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

    <!-- Integrations Tab -->
    <div x-show="activeTab === 'integrations'" x-cloak class="space-y-6">

    <!-- Telegram Chats Link -->
    <a href="{{ route('telegram.chat.index') }}" class="block bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-sm p-5 hover:from-blue-600 hover:to-blue-700 transition-all group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Telegram чати</h3>
                    <p class="text-sm text-white/80">Перегляд та відповіді на повідомлення</p>
                </div>
            </div>
            @if(($unreadTelegramCount ?? 0) > 0)
            <span class="bg-white text-blue-600 text-sm font-bold px-3 py-1 rounded-full">{{ $unreadTelegramCount > 99 ? '99+' : $unreadTelegramCount }}</span>
            @else
            <svg class="w-5 h-5 text-white/70 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            @endif
        </div>
    </a>

    <!-- Telegram bot instructions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Telegram бот</h2>
            </div>
        </div>

        <div class="p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Як підключити Telegram?</h3>
            <ol class="space-y-4 text-sm">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Відкрийте бота в Telegram</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-0.5">Натисніть <a href="https://t.me/ministrify_bot" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">@ministrify_bot</a> або знайдіть в пошуку Telegram</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Натисніть /start</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-0.5">Бот привітає вас і спробує знайти ваш профіль автоматично</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Автоматичне підключення</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-0.5">Якщо у вашому профілі в Ministrify вказано Telegram @username — бот підключиться автоматично</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Або введіть код вручну</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-0.5">Отримайте 6-значний код в розділі <a href="{{ route('my-profile') }}" class="text-primary-600 dark:text-primary-400 hover:underline">«Мій профіль»</a> і надішліть його боту</p>
                    </div>
                </li>
            </ol>

            <div class="mt-5 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-3">Що вміє бот:</p>

                <p class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">📬 Сповіщення:</p>
                <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 mb-3">
                    <li>• Повідомлення про нові призначення на служіння</li>
                    <li>• Нагадування за день до події</li>
                    <li>• Сповіщення про призначення відповідальностей</li>
                    <li>• Сповіщення про пункти плану служіння</li>
                    <li>• Повідомлення лідеру, якщо хтось відмовився</li>
                </ul>

                <p class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">✅ Підтвердження:</p>
                <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 mb-3">
                    <li>• Підтвердження або відмова кнопками ✅/❌</li>
                    <li>• Підтвердження відповідальностей на події</li>
                    <li>• Підтвердження участі в плані служіння</li>
                </ul>

                <p class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">📋 Команди:</p>
                <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 mb-3">
                    <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/schedule</code> — розклад на місяць</li>
                    <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/next</code> — наступне служіння</li>
                    <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/unavailable</code> — як вказати недоступність</li>
                    <li>• <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/help</code> — список команд</li>
                </ul>

                <p class="text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">🔗 Підключення:</p>
                <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
                    <li>• Автоматичне підключення за Telegram username</li>
                    <li>• Підключення за 6-значним кодом з профілю</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Notification settings -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{
             birthdayReminders: {{ ($church->settings['notifications']['birthday_reminders'] ?? true) ? 'true' : 'false' }},
             taskReminders: {{ ($church->settings['notifications']['task_reminders'] ?? true) ? 'true' : 'false' }},
             saving: false,
             saved: false,
             save() {
                 this.saving = true;
                 this.saved = false;
                 fetch('{{ route('settings.notifications') }}', {
                     method: 'PUT',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         birthday_reminders: this.birthdayReminders,
                         task_reminders: this.taskReminders
                     })
                 }).then(() => {
                     this.saving = false;
                     this.saved = true;
                     setTimeout(() => this.saved = false, 2000);
                 }).catch(() => {
                     this.saving = false;
                 });
             }
         }">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Автоматичні нагадування</h2>
            <span x-show="saved" x-transition class="text-sm text-green-600 dark:text-green-400">Збережено ✓</span>
        </div>

        <div class="p-6 space-y-6">
            <!-- Birthday reminders -->
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.5 1.5 0 003 15.546V12a9 9 0 1118 0v3.546zM12 3v1m0 11v1m-4-4h1m6 0h1"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">Дні народження</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Нагадування лідерам про дні народження членів церкви</p>
                    </div>
                </div>
                <div class="relative inline-flex">
                    <input type="checkbox" x-model="birthdayReminders" @change="save()" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </div>
            </label>

            <!-- Task reminders -->
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">Дедлайни завдань</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Нагадування виконавцям про терміни завдань</p>
                    </div>
                </div>
                <div class="relative inline-flex">
                    <input type="checkbox" x-model="taskReminders" @change="save()" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                </div>
            </label>
        </div>

        <div class="px-6 pb-6 pt-0">
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Нагадування для <strong>служінь</strong> налаштовуються індивідуально для кожної події в розкладі.
                        </p>
                        <a href="{{ route('schedule') }}" class="inline-flex items-center gap-1 mt-2 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700">
                            Перейти до розкладу
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Data Tab -->
    <div x-show="activeTab === 'data'" x-cloak class="space-y-6">
    <!-- Ministries -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Команди</h2>
        </div>

        <div class="p-6">
            <div class="space-y-2 mb-4">
                @forelse($ministries as $ministry)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <a href="{{ route('ministries.show', $ministry) }}" class="flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400">
                            @if($ministry->color)
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $ministry->color }}"></span>
                            @endif
                            <span class="text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('settings.ministries.destroy', $ministry) }}"
                              onsubmit="return confirm('Видалити команду?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                Видалити
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Команд ще немає</p>
                @endforelse
            </div>

            <a href="{{ route('ministries.create') }}" class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Додати команду
            </a>
        </div>
    </div>

    <!-- Finance Categories -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700" x-data="{ showForm: false, editId: null, formType: 'income' }">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Категорії фінансів</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Категорії для надходжень та витрат</p>
            </div>
            <button @click="showForm = !showForm; editId = null" class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-500 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Додати
            </button>
        </div>

        <!-- Add/Edit Form -->
        <div x-show="showForm" x-cloak class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <form action="{{ route('settings.transaction-categories.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <div>
                        <input type="text" name="name" placeholder="Назва" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <select name="type" x-model="formType"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                            <option value="income">Надходження</option>
                            <option value="expense">Витрата</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="icon" placeholder="Емодзі" maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <input type="color" name="color" value="#3B82F6"
                               class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg text-sm transition-colors">
                            Додати
                        </button>
                        <button type="button" @click="showForm = false" class="px-4 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6">
            <!-- Income Categories -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-green-600 dark:text-green-400 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Надходження
                </h3>
                <div class="space-y-2">
                    @foreach($transactionCategories->where('type', 'income') as $category)
                        <div x-data="{ editing: false }" class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div x-show="!editing" class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm" style="background-color: {{ $category->color }}20">
                                    {{ $category->icon ?? '💰' }}
                                </span>
                                <span class="text-gray-900 dark:text-white">{{ $category->name }}</span>
                                <span class="text-xs text-gray-500">{{ $category->transactions_count }} записів</span>
                            </div>
                            <div x-show="!editing" class="flex items-center gap-2">
                                <button @click="editing = true" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($category->transactions_count == 0)
                                    <form action="{{ route('settings.transaction-categories.destroy', $category) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Видалити категорію?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <!-- Edit form -->
                            <form x-show="editing" action="{{ route('settings.transaction-categories.update', $category) }}" method="POST" class="flex-1 flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $category->name }}" required
                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="text" name="icon" value="{{ $category->icon }}" placeholder="Емодзі" maxlength="10"
                                       class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="color" name="color" value="{{ $category->color }}"
                                       class="w-10 h-8 border border-gray-300 dark:border-gray-600 rounded cursor-pointer">
                                <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">Зберегти</button>
                                <button type="button" @click="editing = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">Скасувати</button>
                            </form>
                        </div>
                    @endforeach
                    @if($transactionCategories->where('type', 'income')->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає категорій надходжень</p>
                    @endif
                </div>
            </div>

            <!-- Expense Categories -->
            <div>
                <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Витрати
                </h3>
                <div class="space-y-2">
                    @foreach($transactionCategories->where('type', 'expense') as $category)
                        <div x-data="{ editing: false }" class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div x-show="!editing" class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm" style="background-color: {{ $category->color }}20">
                                    {{ $category->icon ?? '📦' }}
                                </span>
                                <span class="text-gray-900 dark:text-white">{{ $category->name }}</span>
                                <span class="text-xs text-gray-500">{{ $category->transactions_count }} записів</span>
                            </div>
                            <div x-show="!editing" class="flex items-center gap-2">
                                <button @click="editing = true" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($category->transactions_count == 0)
                                    <form action="{{ route('settings.transaction-categories.destroy', $category) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Видалити категорію?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <!-- Edit form -->
                            <form x-show="editing" action="{{ route('settings.transaction-categories.update', $category) }}" method="POST" class="flex-1 flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $category->name }}" required
                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="text" name="icon" value="{{ $category->icon }}" placeholder="Емодзі" maxlength="10"
                                       class="w-16 px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="color" name="color" value="{{ $category->color }}"
                                       class="w-10 h-8 border border-gray-300 dark:border-gray-600 rounded cursor-pointer">
                                <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">Зберегти</button>
                                <button type="button" @click="editing = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">Скасувати</button>
                            </form>
                        </div>
                    @endforeach
                    @if($transactionCategories->where('type', 'expense')->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Немає категорій витрат</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Church Roles -->
    <a href="{{ route('settings.church-roles.index') }}"
       class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Церковні ролі</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Налаштуйте ролі для членів церкви (пастор, диякон, пресвітер...)</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    <!-- Shepherds -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{ enabled: {{ $church->shepherds_enabled ? 'true' : 'false' }}, saving: false }">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Опікуни</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Призначайте духовних опікунів для членів церкви</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Toggle -->
                <button type="button"
                        @click="enabled = !enabled; saving = true; fetch('{{ route("settings.shepherds.toggle-feature") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ enabled: enabled })
                        }).finally(() => saving = false)"
                        :class="enabled ? 'bg-green-600' : 'bg-gray-200 dark:bg-gray-700'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                    <span class="sr-only">Увімкнути опікунів</span>
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                    </span>
                </button>
                <!-- Link to manage -->
                <a x-show="enabled" href="{{ route('settings.shepherds.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{ enabled: {{ $church->attendance_enabled ? 'true' : 'false' }}, saving: false }">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Відвідуваність богослужінь</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Відстежуйте присутність на богослужіннях та подіях</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Toggle -->
                <button type="button"
                        @click="enabled = !enabled; saving = true; fetch('{{ route("settings.attendance.toggle-feature") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ enabled: enabled })
                        }).finally(() => saving = false)"
                        :class="enabled ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-600 focus:ring-offset-2">
                    <span class="sr-only">Увімкнути відвідуваність</span>
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
                    </span>
                </button>
                <!-- Link to stats -->
                <a x-show="enabled" href="{{ route('attendance.stats') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

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

    <!-- Finance Tab -->
    <div x-show="activeTab === 'finance'" x-cloak class="space-y-6">
        <!-- Initial Balance -->
        <form method="POST" action="{{ route('settings.finance') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Початковий баланс</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Вкажіть баланс церкви на момент початку обліку</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Як це працює:</strong> Вкажіть суму, яка була на рахунку церкви на певну дату.
                        Всі подальші доходи та витрати будуть додаватись/віднімаватись від цієї суми для розрахунку поточного балансу.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="initial_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сума балансу *</label>
                        <div class="relative">
                            <input type="number" name="initial_balance" id="initial_balance" step="0.01" min="0" required
                                   value="{{ old('initial_balance', $church->initial_balance ?? 0) }}"
                                   class="w-full px-3 py-2 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 text-sm">грн</span>
                            </div>
                        </div>
                        @error('initial_balance')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="initial_balance_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата балансу *</label>
                        <input type="date" name="initial_balance_date" id="initial_balance_date" required
                               value="{{ old('initial_balance_date', $church->initial_balance_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('initial_balance_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if($church->initial_balance && $church->initial_balance_date)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Поточний стан</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Початковий</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($church->initial_balance, 0, ',', ' ') }} ₴</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Доходи</p>
                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">+{{ number_format($church->total_income, 0, ',', ' ') }} ₴</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Витрати</p>
                            <p class="text-lg font-semibold text-red-600 dark:text-red-400">-{{ number_format($church->total_expense, 0, ',', ' ') }} ₴</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Поточний</p>
                            <p class="text-lg font-semibold text-primary-600 dark:text-primary-400">{{ number_format($church->current_balance, 0, ',', ' ') }} ₴</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти баланс
                </button>
            </div>
        </form>

        <!-- Currency Settings -->
        <form method="POST" action="{{ route('settings.currencies') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Мультивалютність</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Оберіть валюти для обліку доходів та витрат</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        <strong>Курси валют:</strong> Автоматично оновлюються з НБУ щодня о 10:30.
                        Всі суми в іноземній валюті конвертуються в гривні за курсом на дату транзакції.
                    </p>
                </div>

                @php
                    $enabledCurrencies = $church->enabled_currencies ?? ['UAH'];
                    $allCurrencies = [
                        'UAH' => ['symbol' => '₴', 'name' => 'Гривня (UAH)', 'flag' => '🇺🇦'],
                        'USD' => ['symbol' => '$', 'name' => 'Долар США (USD)', 'flag' => '🇺🇸'],
                        'EUR' => ['symbol' => '€', 'name' => 'Євро (EUR)', 'flag' => '🇪🇺'],
                    ];
                @endphp

                <div class="space-y-3">
                    @foreach($allCurrencies as $code => $currency)
                    <label class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $code === 'UAH' ? 'bg-gray-50 dark:bg-gray-700/30' : '' }}">
                        <input type="checkbox" name="currencies[]" value="{{ $code }}"
                               {{ in_array($code, $enabledCurrencies) ? 'checked' : '' }}
                               {{ $code === 'UAH' ? 'checked disabled' : '' }}
                               class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        @if($code === 'UAH')
                            <input type="hidden" name="currencies[]" value="UAH">
                        @endif
                        <span class="ml-3 text-2xl">{{ $currency['flag'] }}</span>
                        <div class="ml-3 flex-1">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $currency['name'] }}</span>
                            @if($code === 'UAH')
                                <span class="text-xs text-gray-500 dark:text-gray-400">Основна валюта (обов'язкова)</span>
                            @endif
                        </div>
                        <span class="text-xl font-semibold text-gray-400 dark:text-gray-500">{{ $currency['symbol'] }}</span>
                    </label>
                    @endforeach
                </div>

                @if(count($enabledCurrencies) > 1)
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Поточні курси НБУ</h4>
                    <div class="flex flex-wrap gap-4">
                        @php
                            $rates = \App\Models\ExchangeRate::getLatestRates();
                        @endphp
                        @foreach(['USD', 'EUR'] as $code)
                            @if(in_array($code, $enabledCurrencies) && isset($rates[$code]))
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $allCurrencies[$code]['flag'] }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">1 {{ $code }} =</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($rates[$code], 2, ',', ' ') }} ₴</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти валюти
                </button>
            </div>
        </form>
    </div>

    <!-- Users Tab -->
    <div x-show="activeTab === 'users'" x-cloak class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Користувачі системи</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $users->count() }} {{ trans_choice('користувач|користувачі|користувачів', $users->count()) }}</p>
                </div>
                @admin
                <a href="{{ route('settings.users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Запросити
                </a>
                @endadmin
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ім'я</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Email</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Роль</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Статус</th>
                            <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Дії</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $user)
                        <tr>
                            <td class="px-3 md:px-6 py-3 md:py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 md:h-10 md:w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">{{ mb_substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</div>
                                        @if($user->person)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:block">{{ $user->person->full_name }}</div>
                                        @endif
                                        <div class="md:hidden text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $user->email }}</td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                @if($user->churchRole)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $user->churchRole->color }}20; color: {{ $user->churchRole->color }}">
                                    {{ $user->churchRole->name }}
                                </span>
                                @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    Без ролі
                                </span>
                                @endif
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center text-sm text-green-600 dark:text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    <span class="hidden md:inline">Активний</span>
                                </span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                                @admin
                                @if($user->id !== auth()->id())
                                <a href="{{ route('settings.users.edit', $user) }}" class="p-2 inline-flex text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 inline-flex text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="text-gray-400 dark:text-gray-500 text-xs">Це ви</span>
                                @endif
                                @endadmin
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Permissions Tab -->
    <div x-show="activeTab === 'permissions'" x-cloak class="space-y-6" x-data="permissionsManager()">
        @if($churchRoles->isEmpty())
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-medium text-yellow-800 dark:text-yellow-200">Немає церковних ролей</p>
                    <p class="text-sm text-yellow-600 dark:text-yellow-400">Спочатку створіть ролі на сторінці <a href="{{ route('settings.church-roles.index') }}" class="underline">Церковні ролі</a>.</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <!-- Role tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <nav class="flex -mb-px min-w-max">
                    @foreach($churchRoles as $role)
                    <button @click="currentRoleId = {{ $role->id }}"
                            :class="currentRoleId === {{ $role->id }} ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                            class="py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors whitespace-nowrap flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $role->color }}"></span>
                        {{ $role->name }}
                        @if($role->is_admin_role)
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @endif
                    </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">
                <!-- Admin notice -->
                <template x-if="isCurrentRoleAdmin()">
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-blue-800 dark:text-blue-200">Ця роль має повний доступ</p>
                                <p class="text-sm text-blue-600 dark:text-blue-400">Права ролі з повним доступом не можна обмежити.</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Permissions table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white">Модуль</th>
                                @foreach($permissionActions as $actionKey => $actionLabel)
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white text-center w-24">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($permissionModules as $moduleKey => $module)
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-4">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $module['label'] }}</span>
                                </td>
                                @foreach($permissionActions as $actionKey => $actionLabel)
                                <td class="py-4 text-center">
                                    @if(in_array($actionKey, $module['actions'] ?? []))
                                    <label class="inline-flex items-center justify-center">
                                        <template x-if="isCurrentRoleAdmin()">
                                            <input type="checkbox" checked disabled
                                                   class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed">
                                        </template>
                                        <template x-if="!isCurrentRoleAdmin()">
                                            <input type="checkbox"
                                                   x-model="rolePermissions[currentRoleId]['{{ $moduleKey }}']"
                                                   value="{{ $actionKey }}"
                                                   @change="markDirty()"
                                                   class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                                        </template>
                                    </label>
                                    @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <template x-if="!isCurrentRoleAdmin()">
                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button @click="resetToDefaults()"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Скинути до стандартних
                        </button>
                        <button @click="savePermissions()"
                                :disabled="!isDirty || saving"
                                class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!saving">Зберегти зміни</span>
                            <span x-show="saving" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Збереження...
                            </span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium mb-1">Як працюють права доступу</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                        <li>Ролі з <strong>повним доступом</strong> (позначені щитом) мають доступ до всіх функцій</li>
                        <li>Для інших ролей налаштуйте окремі права для кожного модуля</li>
                        <li><a href="{{ route('settings.church-roles.index') }}" class="underline">Керувати ролями</a> можна на сторінці "Церковні ролі"</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Audit Log Tab -->
    <div x-show="activeTab === 'audit'" x-cloak class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Журнал дій</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Останні 100 змін у системі</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дія</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Тип</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Об'єкт</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Зміни</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($log->user)
                                            <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                                <span class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                                    {{ mb_substr($log->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                        <span class="text-sm text-gray-900 dark:text-white">{{ $log->user_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $color = $log->action_color;
                                        $colorClasses = match($color) {
                                            'green' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                                            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                            'red' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                                            'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium {{ $colorClasses }}">
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                    {{ $log->model_label }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                    {{ Str::limit($log->model_name, 30) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300 hidden lg:table-cell">
                                    @if($log->changes_summary_text)
                                        <span class="font-mono" title="{{ $log->changes_summary_text }}">
                                            {{ Str::limit($log->changes_summary_text, 50) }}
                                        </span>
                                    @elseif($log->action === 'created')
                                        <span class="text-green-600 dark:text-green-400">Новий</span>
                                    @elseif($log->action === 'deleted')
                                        <span class="text-red-600 dark:text-red-400">Видалено</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Записів не знайдено
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($auditLogs->count() >= 100)
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 text-center">
                    <a href="{{ route('settings.audit-logs.index') }}" class="text-primary-600 dark:text-primary-400 hover:underline text-sm">
                        Показати всі записи з фільтрами →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function permissionsManager() {
    const moduleKeys = @json(array_keys($permissionModules));
    const modulesConfig = @json($permissionModules);

    // Build initial permissions object from church roles
    const initialPermissions = {};
    @foreach($churchRoles as $role)
    initialPermissions[{{ $role->id }}] = @json($role->getAllPermissions());
    @endforeach

    return {
        currentRoleId: {{ $churchRoles->first()?->id ?? 0 }},
        isDirty: false,
        saving: false,
        rolePermissions: initialPermissions,
        roles: @json($rolesJson->keyBy('id')),

        isCurrentRoleAdmin() {
            return this.roles[this.currentRoleId]?.is_admin_role ?? false;
        },

        markDirty() {
            this.isDirty = true;
        },

        async savePermissions() {
            this.saving = true;

            try {
                const response = await fetch('{{ route('settings.permissions.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role_id: this.currentRoleId,
                        permissions: this.rolePermissions[this.currentRoleId],
                    }),
                });

                if (response.ok) {
                    this.isDirty = false;
                    if (window.showGlobalToast) {
                        showGlobalToast('Права доступу збережено', 'success');
                    }
                } else {
                    throw new Error('Failed to save');
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка збереження', 'error');
                }
            }

            this.saving = false;
        },

        async resetToDefaults() {
            const roleName = this.roles[this.currentRoleId]?.name || 'цієї ролі';
            if (!confirm(`Скинути права для "${roleName}" до стандартних?`)) {
                return;
            }

            try {
                const response = await fetch('{{ route('settings.permissions.reset') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role_id: this.currentRoleId,
                    }),
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                if (window.showGlobalToast) {
                    showGlobalToast('Помилка скидання', 'error');
                }
            }
        }
    }
}
</script>
@endpush
@endsection
