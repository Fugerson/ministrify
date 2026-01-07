@extends('layouts.app')

@section('title', 'Дизайн та стилі')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ activeTab: 'colors' }">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Дизайн та стилі</h1>
            <p class="text-gray-600 dark:text-gray-400">Налаштуйте кольори, шрифти та елементи сайту</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2">
        <div class="flex overflow-x-auto gap-1 no-scrollbar">
            <button @click="activeTab = 'colors'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'colors' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                Кольори
            </button>
            <button @click="activeTab = 'fonts'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'fonts' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                Шрифти
            </button>
            <button @click="activeTab = 'hero'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'hero' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                Hero секція
            </button>
            <button @click="activeTab = 'navigation'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'navigation' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                Навігація
            </button>
            <button @click="activeTab = 'css'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'css' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                Custom CSS
            </button>
        </div>
    </div>

    <!-- Colors Tab -->
    <div x-show="activeTab === 'colors'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('website-builder.design.colors') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Кольорова схема</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php $colors = $church->site_colors; @endphp

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Основний колір</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary" value="{{ $colors['primary'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['primary'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Вторинний колір</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary" value="{{ $colors['secondary'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['secondary'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Акцентний колір</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="accent" value="{{ $colors['accent'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['accent'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Фон</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="background" value="{{ $colors['background'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['background'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="text" value="{{ $colors['text'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['text'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Заголовки</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="heading" value="{{ $colors['heading'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['heading'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти кольори
                </button>
            </div>
        </form>
    </div>

    <!-- Fonts Tab -->
    <div x-show="activeTab === 'fonts'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('website-builder.design.fonts') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Шрифти</h2>
            </div>

            <div class="p-6 space-y-4">
                @php $siteFonts = $church->site_fonts; @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Шрифт заголовків</label>
                    <select name="heading" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @foreach($fonts as $value => $label)
                            <option value="{{ $value }}" {{ ($siteFonts['heading'] ?? 'Inter') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Шрифт основного тексту</label>
                    <select name="body" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @foreach($fonts as $value => $label)
                            <option value="{{ $value }}" {{ ($siteFonts['body'] ?? 'Inter') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Preview -->
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Попередній перегляд:</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" style="font-family: '{{ $siteFonts['heading'] ?? 'Inter' }}', sans-serif;">
                        Заголовок вашого сайту
                    </h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300" style="font-family: '{{ $siteFonts['body'] ?? 'Inter' }}', sans-serif;">
                        Це приклад основного тексту на вашому сайті. Шрифти завантажуються з Google Fonts через Bunny CDN для кращої продуктивності.
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти шрифти
                </button>
            </div>
        </form>
    </div>

    <!-- Hero Tab -->
    <div x-show="activeTab === 'hero'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('website-builder.design.hero') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Hero секція</h2>
            </div>

            @php $heroSettings = $church->hero_settings; @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тип Hero</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($heroTypes as $type => $info)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ ($heroSettings['type'] ?? 'image') === $type ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="type" value="{{ $type }}" {{ ($heroSettings['type'] ?? 'image') === $type ? 'checked' : '' }} class="sr-only">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $info['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $info['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Заголовок</label>
                        <input type="text" name="title" value="{{ $heroSettings['title'] ?? '' }}" placeholder="Ласкаво просимо!"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Підзаголовок</label>
                        <input type="text" name="subtitle" value="{{ $heroSettings['subtitle'] ?? '' }}" placeholder="Приєднуйтесь до нашої громади"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст кнопки CTA</label>
                        <input type="text" name="cta_text" value="{{ $heroSettings['cta_text'] ?? '' }}" placeholder="Дізнатися більше"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Посилання кнопки CTA</label>
                        <input type="text" name="cta_url" value="{{ $heroSettings['cta_url'] ?? '' }}" placeholder="#contact"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Затемнення фону (%)</label>
                    <input type="range" name="overlay_opacity" min="0" max="100" value="{{ $heroSettings['overlay_opacity'] ?? 70 }}"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Вирівнювання тексту</label>
                    <div class="flex gap-2">
                        @foreach(['left' => 'Ліворуч', 'center' => 'По центру', 'right' => 'Праворуч'] as $align => $label)
                            <label class="flex-1">
                                <input type="radio" name="text_alignment" value="{{ $align }}" {{ ($heroSettings['text_alignment'] ?? 'left') === $align ? 'checked' : '' }} class="sr-only peer">
                                <div class="p-2 text-center border rounded-lg cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти налаштування Hero
                </button>
            </div>
        </form>

        <!-- Hero Image Upload -->
        <form method="POST" action="{{ route('website-builder.design.hero.image') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Фонове зображення Hero</h2>
            </div>

            <div class="p-6">
                @if(!empty($heroSettings['image']))
                    <div class="mb-4">
                        <img src="{{ Storage::url($heroSettings['image']) }}" alt="Hero background" class="w-full max-w-md h-48 object-cover rounded-lg">
                    </div>
                @endif

                <input type="file" name="hero_image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Рекомендований розмір: 1920x1080px, до 5MB</p>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Завантажити зображення
                </button>
            </div>
        </form>
    </div>

    <!-- Navigation Tab -->
    <div x-show="activeTab === 'navigation'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('website-builder.design.navigation') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Стиль навігації</h2>
            </div>

            @php $navSettings = $church->getPublicSiteSetting('navigation', []); @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Стиль навігаційної панелі</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($navigationStyles as $style => $label)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ ($navSettings['style'] ?? 'solid') === $style ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="style" value="{{ $style }}" {{ ($navSettings['style'] ?? 'solid') === $style ? 'checked' : '' }} class="sr-only">
                                <span class="text-gray-900 dark:text-white">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="sticky" value="1" {{ ($navSettings['sticky'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Фіксована навігація при прокрутці</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="show_logo" value="1" {{ ($navSettings['show_logo'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Показувати логотип</span>
                    </label>

                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти навігацію
                </button>
            </div>
        </form>

        <!-- Footer Settings -->
        <form method="POST" action="{{ route('website-builder.design.footer') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Футер</h2>
            </div>

            @php $footerSettings = $church->getPublicSiteSetting('footer', []); @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Стиль футера</label>
                    <select name="style" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="simple" {{ ($footerSettings['style'] ?? 'simple') === 'simple' ? 'selected' : '' }}>Простий</option>
                        <option value="centered" {{ ($footerSettings['style'] ?? 'simple') === 'centered' ? 'selected' : '' }}>По центру</option>
                        <option value="multi-column" {{ ($footerSettings['style'] ?? 'simple') === 'multi-column' ? 'selected' : '' }}>Багатоколонковий</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст копірайту</label>
                    <input type="text" name="copyright_text" value="{{ $footerSettings['copyright_text'] ?? '' }}" placeholder="© {{ date('Y') }} {{ $church->name }}. Всі права захищено."
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_contact" value="1" {{ ($footerSettings['show_contact'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Показувати контактну інформацію</span>
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти футер
                </button>
            </div>
        </form>
    </div>

    <!-- Custom CSS Tab -->
    <div x-show="activeTab === 'css'" x-cloak class="space-y-6">
        <form method="POST" action="{{ route('website-builder.design.css') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @csrf
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Кастомний CSS</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Додайте власні стилі для публічного сайту</p>
            </div>

            <div class="p-6">
                <textarea name="custom_css" rows="15" placeholder="/* Ваш CSS код */"
                          class="w-full px-3 py-2 font-mono text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">{{ $church->custom_css }}</textarea>

                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-sm text-yellow-700 dark:text-yellow-300">
                            <p class="font-medium">Увага</p>
                            <p class="mt-1">@import та javascript: заборонені з міркувань безпеки. Неправильний CSS може порушити відображення сайту.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                    Зберегти CSS
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
