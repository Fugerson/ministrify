@extends('layouts.app')

@section('title', __('app.design_and_styles'))

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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.design_and_styles') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('app.customize_colors_fonts_elements') }}</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2">
        <div class="flex overflow-x-auto gap-1 no-scrollbar">
            <button @click="activeTab = 'colors'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'colors' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                {{ __('app.colors') }}
            </button>
            <button @click="activeTab = 'fonts'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'fonts' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                {{ __('app.fonts') }}
            </button>
            <button @click="activeTab = 'hero'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'hero' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                {{ __('app.hero_section') }}
            </button>
            <button @click="activeTab = 'navigation'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'navigation' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                {{ __('app.navigation') }}
            </button>
            <button @click="activeTab = 'css'" :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'css' }"
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap">
                {{ __('app.custom_css') }}
            </button>
        </div>
    </div>

    <!-- Colors Tab -->
    <div x-show="activeTab === 'colors'" x-cloak class="space-y-6">
        <form @submit.prevent="submit($refs.colorsForm)" x-ref="colorsForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.colors') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.color_scheme') }}</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php $colors = $church->site_colors; @endphp

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.primary_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary" value="{{ $colors['primary'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['primary'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.secondary_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary" value="{{ $colors['secondary'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['secondary'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.accent_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="accent" value="{{ $colors['accent'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['accent'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.background') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="background" value="{{ $colors['background'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['background'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.text') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="text" value="{{ $colors['text'] }}"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-0">
                            <input type="text" value="{{ $colors['text'] }}" readonly
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.headings') }}</label>
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
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_colors') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Fonts Tab -->
    <div x-show="activeTab === 'fonts'" x-cloak class="space-y-6">
        <form @submit.prevent="submit($refs.fontsForm)" x-ref="fontsForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.fonts') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.fonts') }}</h2>
            </div>

            <div class="p-6 space-y-4">
                @php $siteFonts = $church->site_fonts; @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.heading_font') }}</label>
                    <select name="heading" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @foreach($fonts as $value => $label)
                            <option value="{{ $value }}" {{ ($siteFonts['heading'] ?? 'Inter') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.body_text_font') }}</label>
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
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ __('app.preview') }}:</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" style="font-family: '{{ $siteFonts['heading'] ?? 'Inter' }}', sans-serif;">
                        {{ __('app.site_heading_preview') }}
                    </h3>
                    <p class="mt-2 text-gray-700 dark:text-gray-300" style="font-family: '{{ $siteFonts['body'] ?? 'Inter' }}', sans-serif;">
                        {{ __('app.site_body_text_preview') }}
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_fonts') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Hero Tab -->
    <div x-show="activeTab === 'hero'" x-cloak class="space-y-6">
        <form @submit.prevent="submit($refs.heroForm)" x-ref="heroForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.hero') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.hero_section') }}</h2>
            </div>

            @php $heroSettings = $church->hero_settings; @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.hero_type') }}</label>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.title') }}</label>
                        <input type="text" name="title" value="{{ $heroSettings['title'] ?? '' }}" placeholder="{{ __('app.welcome_placeholder') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.subtitle') }}</label>
                        <input type="text" name="subtitle" value="{{ $heroSettings['subtitle'] ?? '' }}" placeholder="{{ __('app.join_our_community') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.cta_button_text') }}</label>
                        <input type="text" name="cta_text" value="{{ $heroSettings['cta_text'] ?? '' }}" placeholder="{{ __('app.learn_more') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.cta_button_link') }}</label>
                        <input type="text" name="cta_url" value="{{ $heroSettings['cta_url'] ?? '' }}" placeholder="#contact"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.overlay_opacity') }}</label>
                    <input type="range" name="overlay_opacity" min="0" max="100" value="{{ $heroSettings['overlay_opacity'] ?? 70 }}"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.text_alignment') }}</label>
                    <div class="flex gap-2">
                        @foreach(['left' => __('app.align_left'), 'center' => __('app.align_center'), 'right' => __('app.align_right')] as $align => $label)
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
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_hero_settings') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>

        <!-- Hero Image Upload -->
        <form @submit.prevent="submit($refs.heroImageForm)" x-ref="heroImageForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.hero.image') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.hero_background_image') }}</h2>
            </div>

            <div class="p-6">
                @if(!empty($heroSettings['image']))
                    <div class="mb-4">
                        <img src="{{ Storage::url($heroSettings['image']) }}" alt="Hero background" class="w-full max-w-md h-48 object-cover rounded-lg">
                    </div>
                @endif

                <div x-data="{ fileName: '' }" class="relative">
                    <input type="file" name="hero_image" accept="image/*,.heic,.heif" class="sr-only" x-ref="heroInput" @change="fileName = $event.target.files[0]?.name || ''">
                    <label @click="$refs.heroInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.choose_image') }}</p>
                            <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">1920x1080px, до 5MB</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.upload_image') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.loading') }}
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Navigation Tab -->
    <div x-show="activeTab === 'navigation'" x-cloak class="space-y-6">
        <form @submit.prevent="submit($refs.navForm)" x-ref="navForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.navigation') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.navigation_style') }}</h2>
            </div>

            @php $navSettings = $church->getPublicSiteSetting('navigation', []); @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.navbar_style') }}</label>
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
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('app.sticky_navigation') }}</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="show_logo" value="1" {{ ($navSettings['show_logo'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('app.show_logo') }}</span>
                    </label>

                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_navigation') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>

        <!-- Footer Settings -->
        <form @submit.prevent="submit($refs.footerForm)" x-ref="footerForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.footer') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.footer') }}</h2>
            </div>

            @php $footerSettings = $church->getPublicSiteSetting('footer', []); @endphp

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.footer_style') }}</label>
                    <select name="style" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="simple" {{ ($footerSettings['style'] ?? 'simple') === 'simple' ? 'selected' : '' }}>{{ __('app.footer_simple') }}</option>
                        <option value="centered" {{ ($footerSettings['style'] ?? 'simple') === 'centered' ? 'selected' : '' }}>{{ __('app.footer_centered') }}</option>
                        <option value="multi-column" {{ ($footerSettings['style'] ?? 'simple') === 'multi-column' ? 'selected' : '' }}>{{ __('app.footer_multi_column') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.copyright_text') }}</label>
                    <input type="text" name="copyright_text" value="{{ $footerSettings['copyright_text'] ?? '' }}" placeholder="{{ __('app.all_rights_reserved_placeholder', ['year' => date('Y'), 'name' => $church->name]) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_contact" value="1" {{ ($footerSettings['show_contact'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('app.show_contact_info') }}</span>
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_footer') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Custom CSS Tab -->
    <div x-show="activeTab === 'css'" x-cloak class="space-y-6">
        <form @submit.prevent="submit($refs.cssForm)" x-ref="cssForm" x-data="{ ...ajaxForm({ url: '{{ route('website-builder.design.css') }}', method: 'POST', stayOnPage: true }) }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.custom_css') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.custom_css_description') }}</p>
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
                            <p class="font-medium">{{ __('app.warning') }}</p>
                            <p class="mt-1">{{ __('app.css_security_warning') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">{{ __('app.save_css') }}</span>
                    <span x-show="saving" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('app.saving') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
