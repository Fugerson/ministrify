{{-- Admin Sidebar Editor for Public Site --}}
@php
    $settings = $church->public_site_settings ?? [];
    $colors = $settings['colors'] ?? [];
    $fonts = $settings['fonts'] ?? [];
    $hero = $settings['hero'] ?? [];
    $navigation = $settings['navigation'] ?? [];
    $footer = $settings['footer'] ?? [];
    $sections = $settings['sections'] ?? [];
    $aboutData = $church->about_content;

    $defaultColors = [
        'primary' => '#3b82f6',
        'secondary' => '#10b981',
        'accent' => '#f59e0b',
        'background' => '#ffffff',
        'text' => '#1f2937',
        'heading' => '#111827',
    ];

    $availableFonts = [
        'Inter', 'Poppins', 'Open Sans', 'Roboto', 'Lato', 'Montserrat',
        'Playfair Display', 'Lora', 'DM Sans', 'Source Sans Pro',
        'Oswald', 'Nunito', 'Space Grotesk', 'Merriweather', 'PT Serif',
    ];

    $defaultSections = [
        ['id' => 'hero', 'enabled' => true, 'order' => 0, 'label' => 'Hero секція'],
        ['id' => 'service_times', 'enabled' => true, 'order' => 1, 'label' => 'Розклад служінь'],
        ['id' => 'about', 'enabled' => true, 'order' => 2, 'label' => 'Про нас'],
        ['id' => 'pastor_message', 'enabled' => false, 'order' => 3, 'label' => 'Слово пастора'],
        ['id' => 'leadership', 'enabled' => true, 'order' => 4, 'label' => 'Команда лідерів'],
        ['id' => 'events', 'enabled' => true, 'order' => 5, 'label' => 'Події'],
        ['id' => 'sermons', 'enabled' => false, 'order' => 6, 'label' => 'Проповіді'],
        ['id' => 'ministries', 'enabled' => true, 'order' => 7, 'label' => 'Служіння'],
        ['id' => 'groups', 'enabled' => true, 'order' => 8, 'label' => 'Малі групи'],
        ['id' => 'gallery', 'enabled' => true, 'order' => 9, 'label' => 'Галерея'],
        ['id' => 'testimonials', 'enabled' => false, 'order' => 10, 'label' => 'Свідчення'],
        ['id' => 'blog', 'enabled' => false, 'order' => 11, 'label' => 'Блог'],
        ['id' => 'faq', 'enabled' => false, 'order' => 12, 'label' => 'FAQ'],
        ['id' => 'donations', 'enabled' => false, 'order' => 13, 'label' => 'Пожертви'],
        ['id' => 'contact', 'enabled' => true, 'order' => 14, 'label' => 'Контакти'],
    ];

    $sectionLabels = collect($defaultSections)->pluck('label', 'id')->toArray();

    if (empty($sections)) {
        $sections = $defaultSections;
    } else {
        $sections = collect($sections)->map(function ($s) use ($sectionLabels) {
            $s['label'] = $sectionLabels[$s['id']] ?? $s['id'];
            return $s;
        })->sortBy('order')->values()->toArray();
    }
@endphp

<div x-data="adminSidebar()" x-cloak>
    {{-- Floating Admin Bar --}}
    <div x-show="!open" class="fixed bottom-6 left-6 z-[60] flex flex-col items-start gap-3">
        {{-- Edit Mode Toolbar (shown when editing) --}}
        <div x-show="editMode"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-md text-gray-900 dark:text-white rounded-2xl shadow-2xl px-4 py-3 flex items-center gap-3 max-w-[90vw]">
            <div class="text-sm text-gray-500 dark:text-white/70 hidden sm:block">
                Перетягуйте секції для зміни порядку
            </div>
            <button @click="saveEditMode()" :disabled="saving"
                    class="px-4 py-2 text-sm font-medium bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white rounded-xl transition-colors flex items-center gap-1.5 flex-shrink-0">
                <template x-if="saving">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <template x-if="!saving">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <span x-text="saving ? 'Збереження...' : 'Зберегти'"></span>
            </button>
            <button @click="cancelEditMode()"
                    class="px-4 py-2 text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-white rounded-xl transition-colors flex-shrink-0">
                Скасувати
            </button>
        </div>

        {{-- Main Buttons Row --}}
        <div class="flex items-center gap-2">
            <button @click="toggleEditMode()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl shadow-lg transition-all duration-200"
                    :class="editMode
                        ? 'bg-primary-600 text-white shadow-primary-500/25'
                        : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span x-text="editMode ? 'Редагування...' : 'Редагувати сайт'"></span>
            </button>
            <button x-show="!editMode" @click="open = true"
                    class="w-10 h-10 bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 rounded-xl shadow-lg flex items-center justify-center transition-all"
                    title="Налаштування сайту">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/20 z-[54]"
    ></div>

    {{-- Sidebar Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 bottom-0 w-[380px] max-w-[90vw] bg-white shadow-2xl z-[55] flex flex-col"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <h2 class="text-base font-semibold text-gray-900">Редактор сайту</h2>
            <div class="flex items-center gap-2">
                <button
                    @click="saveAll()"
                    :disabled="saving"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50 rounded-lg transition-colors flex items-center gap-1.5"
                >
                    <template x-if="saving">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <template x-if="!saving">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="saving ? 'Збереження...' : 'Зберегти'"></span>
                </button>
                <button @click="open = false" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-gray-200 flex-shrink-0">
            <button @click="activeTab = 'design'" :class="activeTab === 'design' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 text-sm font-medium border-b-2 transition-colors">
                Дизайн
            </button>
            <button @click="activeTab = 'sections'" :class="activeTab === 'sections' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 text-sm font-medium border-b-2 transition-colors">
                Секції
            </button>
            <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 text-sm font-medium border-b-2 transition-colors">
                Контент
            </button>
        </div>

        {{-- Toast --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150"
             :class="toast.type === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200'"
             class="mx-4 mt-3 px-3 py-2 text-sm rounded-lg border flex items-center gap-2">
            <template x-if="toast.type === 'success'">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
            <span x-text="toast.message"></span>
        </div>

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto">

            {{-- ========== DESIGN TAB ========== --}}
            <div x-show="activeTab === 'design'" class="p-4 space-y-3">

                {{-- Templates Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.templates = !accordion.templates" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Готові шаблони</span>
                        <svg :class="accordion.templates && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.templates" x-transition class="px-4 py-3">
                        <p class="text-xs text-gray-500 mb-3">Оберіть шаблон для миттєвого попереднього перегляду. Збережіть щоб застосувати.</p>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="tpl in templates" :key="tpl.id">
                                <button @click="applyTemplate(tpl)"
                                        class="group relative flex flex-col items-start p-3 rounded-lg border-2 transition-all text-left hover:shadow-md"
                                        :class="activeTemplate === tpl.id ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-200' : 'border-gray-200 hover:border-gray-300 bg-white'">
                                    {{-- Color swatches --}}
                                    <div class="flex gap-1 mb-2">
                                        <span class="w-5 h-5 rounded-full border border-gray-200 shadow-sm" :style="'background:' + tpl.colors.primary"></span>
                                        <span class="w-5 h-5 rounded-full border border-gray-200 shadow-sm" :style="'background:' + tpl.colors.secondary"></span>
                                        <span class="w-5 h-5 rounded-full border border-gray-200 shadow-sm" :style="'background:' + tpl.colors.accent"></span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 leading-tight" x-text="tpl.name"></span>
                                    <span class="text-[11px] text-gray-400 mt-0.5" x-text="tpl.fonts.heading + ' / ' + tpl.fonts.body"></span>
                                    {{-- Active checkmark --}}
                                    <div x-show="activeTemplate === tpl.id" class="absolute top-1.5 right-1.5">
                                        <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Colors Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.colors = !accordion.colors" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Кольори</span>
                        <svg :class="accordion.colors && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.colors" x-transition class="px-4 py-3 space-y-3">
                        @foreach(['primary' => 'Основний', 'secondary' => 'Додатковий', 'accent' => 'Акцент', 'background' => 'Фон', 'text' => 'Текст', 'heading' => 'Заголовки'] as $key => $label)
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" x-model="colorValues.{{ $key }}" @input="previewColors()" class="w-8 h-8 rounded cursor-pointer border border-gray-300">
                                <input type="text" x-model="colorValues.{{ $key }}" @input="previewColors()" class="w-20 text-xs font-mono px-2 py-1 border border-gray-200 rounded text-center">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Fonts Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.fonts = !accordion.fonts" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Шрифти</span>
                        <svg :class="accordion.fonts && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.fonts" x-transition class="px-4 py-3 space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Заголовки</label>
                            <select x-model="fontValues.heading" @change="previewFonts()" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
                                @foreach($availableFonts as $font)
                                    <option value="{{ $font }}">{{ $font }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Основний текст</label>
                            <select x-model="fontValues.body" @change="previewFonts()" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
                                @foreach($availableFonts as $font)
                                    <option value="{{ $font }}">{{ $font }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Hero Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.hero = !accordion.hero" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Hero секція</span>
                        <svg :class="accordion.hero && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.hero" x-transition class="px-4 py-3 space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Заголовок</label>
                            <input type="text" x-model="heroValues.title" @input="previewHero()" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2" placeholder="Назва церкви">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Підзаголовок</label>
                            <textarea x-model="heroValues.subtitle" @input="previewHero()" rows="2" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2" placeholder="Опис"></textarea>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Текст кнопки CTA</label>
                            <input type="text" x-model="heroValues.cta_text" @input="previewHero()" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2" placeholder="Наші події">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Посилання CTA</label>
                            <input type="text" x-model="heroValues.cta_url" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2" placeholder="https://...">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Затемнення фону: <span x-text="heroValues.overlay_opacity + '%'"></span></label>
                            <input type="range" x-model="heroValues.overlay_opacity" @input="previewHeroOverlay()" min="0" max="100" step="5" class="w-full">
                        </div>
                    </div>
                </div>

                {{-- Navigation Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.navigation = !accordion.navigation" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Навігація</span>
                        <svg :class="accordion.navigation && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.navigation" x-transition class="px-4 py-3 space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Стиль</label>
                            <select x-model="navValues.style" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
                                <option value="transparent">Прозорий</option>
                                <option value="solid">Суцільний</option>
                                <option value="minimal">Мінімальний</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600">Фіксована навігація</label>
                            <button @click="navValues.sticky = !navValues.sticky" :class="navValues.sticky ? 'bg-primary-600' : 'bg-gray-300'" class="relative w-10 h-5 rounded-full transition-colors">
                                <span :class="navValues.sticky ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 left-0 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer Accordion --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="accordion.footer = !accordion.footer" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                        <span class="text-sm font-medium text-gray-900">Футер</span>
                        <svg :class="accordion.footer && 'rotate-180'" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="accordion.footer" x-transition class="px-4 py-3 space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Стиль</label>
                            <select x-model="footerValues.style" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
                                <option value="simple">Простий</option>
                                <option value="centered">По центру</option>
                                <option value="multi-column">Багатоколонковий</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">Текст копірайту</label>
                            <input type="text" x-model="footerValues.copyright_text" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2" placeholder="© 2024 Назва церкви">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ========== SECTIONS TAB ========== --}}
            <div x-show="activeTab === 'sections'" class="p-4">
                <p class="text-xs text-gray-500 mb-3">Перетягуйте для зміни порядку. Зміни застосуються після збереження та перезавантаження.</p>
                <div x-ref="sectionsList" class="space-y-2">
                    @foreach($sections as $section)
                        <div data-id="{{ $section['id'] }}" data-enabled="{{ $section['enabled'] ? '1' : '0' }}" data-layout="{{ $section['layout'] ?? 'full' }}" class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 rounded-lg border border-gray-200 group">
                            <svg class="drag-handle w-4 h-4 text-gray-400 flex-shrink-0 cursor-grab active:cursor-grabbing" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            <span class="flex-1 text-sm text-gray-700">{{ $section['label'] }}</span>
                            @if(($section['layout'] ?? 'full') === 'half')
                                <span class="text-[10px] text-gray-400 px-1.5 py-0.5 bg-gray-200 rounded" title="Половина ширини">½</span>
                            @endif
                            <button @click="toggleSection($el)" :class="$el.parentElement.dataset.enabled === '1' ? 'bg-primary-600' : 'bg-gray-300'" class="relative w-9 h-5 rounded-full transition-colors flex-shrink-0">
                                <span :class="$el.parentElement.dataset.enabled === '1' ? 'translate-x-4' : 'translate-x-0.5'" class="absolute top-0.5 left-0 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ========== CONTENT TAB ========== --}}
            <div x-show="activeTab === 'content'" class="p-4 space-y-1.5">

                @php
                    $contentPanels = [
                        ['key' => 'about', 'label' => 'Про нас', 'partial' => 'public.partials.admin-sidebar.content-about'],
                        ['key' => 'team', 'label' => 'Команда', 'partial' => 'public.partials.admin-sidebar.content-team', 'cnt' => true],
                        ['key' => 'sermons', 'label' => 'Проповіді', 'partial' => 'public.partials.admin-sidebar.content-sermons', 'cnt' => true],
                        ['key' => 'gallery', 'label' => 'Галерея', 'partial' => 'public.partials.admin-sidebar.content-gallery', 'cnt' => true],
                        ['key' => 'faq', 'label' => 'FAQ', 'partial' => 'public.partials.admin-sidebar.content-faq', 'cnt' => true],
                        ['key' => 'testimonials', 'label' => 'Свідчення', 'partial' => 'public.partials.admin-sidebar.content-testimonials', 'cnt' => true],
                    ];
                @endphp

                @foreach($contentPanels as $panel)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button x-on:click="toggleContentPanel('{{ $panel['key'] }}')"
                            class="w-full flex items-center justify-between px-3 py-2 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">{{ $panel['label'] }}</span>
                            @if(isset($panel['cnt']) && $panel['cnt'])
                            <span x-show="cnt.{{ $panel['key'] }}.loaded" x-text="cnt.{{ $panel['key'] }}.items.length"
                                  class="text-[10px] bg-gray-200 text-gray-600 rounded-full px-1.5 py-0.5 leading-none"></span>
                            @endif
                        </div>
                        <svg :class="contentPanel === '{{ $panel['key'] }}' && 'rotate-180'" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="contentPanel === '{{ $panel['key'] }}'" x-transition class="border-t border-gray-200">
                        <div class="p-3">
                            @include($panel['partial'])
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Blog & Events as links --}}
                <div class="pt-2 space-y-1.5">
                    <a href="{{ route('website-builder.blog.index') }}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors group">
                        <span class="text-sm text-gray-700">Блог</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <a href="{{ route('events.index') }}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors group">
                        <span class="text-sm text-gray-700">Події</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style id="admin-sidebar-styles">
    /* Smooth accordion transitions */
    [x-transition] { transition-property: all; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('adminSidebar', () => ({
        open: false,
        editMode: false,
        activeTab: 'design',
        saving: false,
        sortableInstance: null,
        pageSortableInstance: null,
        originalPageOrder: null,
        toast: { show: false, message: '', type: 'success' },

        activeTemplate: null,

        templates: [
            {
                id: 'classic',
                name: 'Класичний',
                colors: { primary: '#3b82f6', secondary: '#10b981', accent: '#f59e0b', background: '#ffffff', text: '#1f2937', heading: '#111827' },
                fonts: { heading: 'Inter', body: 'Inter' },
            },
            {
                id: 'warm',
                name: 'Теплий',
                colors: { primary: '#ea580c', secondary: '#d97706', accent: '#dc2626', background: '#fffbeb', text: '#44403c', heading: '#1c1917' },
                fonts: { heading: 'Playfair Display', body: 'Lato' },
            },
            {
                id: 'nature',
                name: 'Природа',
                colors: { primary: '#059669', secondary: '#0d9488', accent: '#ca8a04', background: '#f0fdf4', text: '#1e3a2f', heading: '#064e3b' },
                fonts: { heading: 'Merriweather', body: 'Open Sans' },
            },
            {
                id: 'elegant',
                name: 'Елегантний',
                colors: { primary: '#7c3aed', secondary: '#6366f1', accent: '#ec4899', background: '#faf5ff', text: '#374151', heading: '#1e1b4b' },
                fonts: { heading: 'Playfair Display', body: 'DM Sans' },
            },
            {
                id: 'minimal',
                name: 'Мінімалізм',
                colors: { primary: '#374151', secondary: '#6b7280', accent: '#3b82f6', background: '#ffffff', text: '#4b5563', heading: '#111827' },
                fonts: { heading: 'Space Grotesk', body: 'Inter' },
            },
            {
                id: 'modern',
                name: 'Сучасний',
                colors: { primary: '#e11d48', secondary: '#0ea5e9', accent: '#f59e0b', background: '#ffffff', text: '#334155', heading: '#0f172a' },
                fonts: { heading: 'Montserrat', body: 'Nunito' },
            },
            {
                id: 'ocean',
                name: 'Океан',
                colors: { primary: '#0284c7', secondary: '#0891b2', accent: '#06b6d4', background: '#f0f9ff', text: '#1e3a5f', heading: '#0c4a6e' },
                fonts: { heading: 'Poppins', body: 'Open Sans' },
            },
            {
                id: 'sunset',
                name: 'Захід сонця',
                colors: { primary: '#db2777', secondary: '#e11d48', accent: '#f97316', background: '#fff1f2', text: '#4c1d4e', heading: '#3b0764' },
                fonts: { heading: 'Oswald', body: 'Lato' },
            },
        ],

        accordion: {
            templates: false,
            colors: true,
            fonts: false,
            hero: false,
            navigation: false,
            footer: false,
        },

        colorValues: {
            primary: @json($colors['primary'] ?? $defaultColors['primary']),
            secondary: @json($colors['secondary'] ?? $defaultColors['secondary']),
            accent: @json($colors['accent'] ?? $defaultColors['accent']),
            background: @json($colors['background'] ?? $defaultColors['background']),
            text: @json($colors['text'] ?? $defaultColors['text']),
            heading: @json($colors['heading'] ?? $defaultColors['heading']),
        },

        fontValues: {
            heading: @json($fonts['heading'] ?? 'Inter'),
            body: @json($fonts['body'] ?? 'Inter'),
        },

        heroValues: {
            title: @json($hero['title'] ?? $church->name),
            subtitle: @json($hero['subtitle'] ?? $church->public_description ?? ''),
            cta_text: @json($hero['cta_text'] ?? 'Наші події'),
            cta_url: @json($hero['cta_url'] ?? ''),
            overlay_opacity: @json($hero['overlay_opacity'] ?? 70),
        },

        navValues: {
            style: @json($navigation['style'] ?? 'transparent'),
            sticky: @json(($navigation['sticky'] ?? true) ? true : false),
        },

        footerValues: {
            style: @json($footer['style'] ?? 'simple'),
            copyright_text: @json($footer['copyright_text'] ?? ''),
        },

        // Content tab
        contentPanel: null,
        contentSaving: false,

        contentRoutes: {
            faq: @json(route('website-builder.faq.index')),
            team: @json(route('website-builder.team.index')),
            testimonials: @json(route('website-builder.testimonials.index')),
            sermons: @json(route('website-builder.sermons.index')),
            gallery: @json(route('website-builder.gallery.index')),
            about: @json(route('website-builder.about.update')),
        },

        aboutForm: {
            mission: @json($aboutData['mission'] ?? ''),
            vision: @json($aboutData['vision'] ?? ''),
            values: @json($aboutData['values'] ?? []),
            history: @json($aboutData['history'] ?? ''),
            beliefs: @json($aboutData['beliefs'] ?? ''),
        },

        cnt: {
            faq:          { loaded: false, loading: false, items: [], editing: null, form: {} },
            team:         { loaded: false, loading: false, items: [], editing: null, form: {} },
            testimonials: { loaded: false, loading: false, items: [], editing: null, form: {} },
            sermons:      { loaded: false, loading: false, items: [], editing: null, form: {} },
            gallery:      { loaded: false, loading: false, items: [], editing: null, form: {} },
        },

        init() {
            if (sessionStorage.getItem('adminSidebarOpen')) {
                this.open = true;
                this.activeTab = sessionStorage.getItem('adminSidebarTab') || 'design';
                sessionStorage.removeItem('adminSidebarOpen');
                sessionStorage.removeItem('adminSidebarTab');
            }
            // Restore content panel if was active before reload
            const savedPanel = sessionStorage.getItem('adminContentPanel');
            if (savedPanel) {
                sessionStorage.removeItem('adminContentPanel');
                if (this.activeTab === 'content') {
                    this.$nextTick(() => this.toggleContentPanel(savedPanel));
                }
            }
            // Restore edit mode if was active before reload
            if (sessionStorage.getItem('adminEditMode')) {
                sessionStorage.removeItem('adminEditMode');
                this.$nextTick(() => this.toggleEditMode());
            }
            if (this.activeTab === 'sections') {
                this.$nextTick(() => this.initSortable());
            }
            this.$watch('activeTab', (val) => {
                if (val === 'sections') {
                    this.$nextTick(() => this.initSortable());
                }
            });

            // Expose helper for global toolbar functions
            window.__adminSidebar = this;
        },

        toggleSection(btnEl) {
            const row = btnEl.closest('[data-id]');
            row.dataset.enabled = row.dataset.enabled === '1' ? '0' : '1';
        },

        toggleEditMode() {
            this.editMode = !this.editMode;
            const container = document.getElementById('sections-container');
            if (!container) return;

            if (this.editMode) {
                // Save original order + layout for cancel
                this.originalPageOrder = [...container.querySelectorAll('.section-wrapper')].map(el => ({
                    id: el.dataset.sectionId,
                    layout: el.dataset.layout || 'full',
                }));
                container.classList.add('edit-active');
                this.$nextTick(() => this.initPageSortable());
            } else {
                container.classList.remove('edit-active');
                this.destroyPageSortable();
                this.originalPageOrder = null;
            }
        },

        async saveEditMode() {
            this.saving = true;
            try {
                // Build sections data from page DOM order
                const container = document.getElementById('sections-container');
                const sidebarList = this.$refs.sectionsList;
                let sectionsData = [];

                if (container) {
                    const pageWrappers = [...container.querySelectorAll('.section-wrapper')];
                    const pageIds = pageWrappers.map(el => el.dataset.sectionId);
                    // Build a map of layout values from page wrappers
                    const layoutMap = {};
                    pageWrappers.forEach(el => { layoutMap[el.dataset.sectionId] = el.dataset.layout || 'full'; });
                    // Get all sections from sidebar (includes disabled ones)
                    if (sidebarList) {
                        const allSidebarItems = [...sidebarList.querySelectorAll('[data-id]')];
                        const enabledSet = new Set(pageIds);
                        let order = 0;
                        // First add enabled sections in page order
                        pageIds.forEach(id => {
                            sectionsData.push({ id, enabled: true, order: order++, layout: layoutMap[id] || 'full' });
                        });
                        // Then add disabled sections
                        allSidebarItems.forEach(item => {
                            if (!enabledSet.has(item.dataset.id)) {
                                sectionsData.push({ id: item.dataset.id, enabled: item.dataset.enabled === '1', order: order++, layout: item.dataset.layout || 'full' });
                            }
                        });
                    }
                }

                if (sectionsData.length > 0) {
                    await this.postData(@json(route('website-builder.sections.update')), { sections: sectionsData });
                }

                // Exit edit mode and reload
                this.editMode = false;
                const containerEl = document.getElementById('sections-container');
                if (containerEl) containerEl.classList.remove('edit-active');
                this.destroyPageSortable();

                sessionStorage.setItem('adminEditMode', '1');
                window.location.reload();
            } catch (e) {
                this.showPageToast('Помилка: ' + e.message);
            } finally {
                this.saving = false;
            }
        },

        cancelEditMode() {
            const container = document.getElementById('sections-container');
            if (container && this.originalPageOrder) {
                // Restore original DOM order + layout
                this.originalPageOrder.forEach(item => {
                    const el = container.querySelector('[data-section-id="' + item.id + '"]');
                    if (el) {
                        el.dataset.layout = item.layout;
                        el.classList.remove('md:w-1/2');
                        if (item.layout === 'half') el.classList.add('md:w-1/2');
                        container.appendChild(el);
                    }
                });
                // Sync sidebar back
                this.syncSidebarFromPage();
            }
            this.editMode = false;
            if (container) container.classList.remove('edit-active');
            this.destroyPageSortable();
            this.originalPageOrder = null;
        },

        destroyPageSortable() {
            if (this.pageSortableInstance) {
                this.pageSortableInstance.destroy();
                this.pageSortableInstance = null;
            }
        },

        initSortable() {
            if (this.sortableInstance) this.sortableInstance.destroy();
            const el = this.$refs.sectionsList;
            if (!el) return;
            this.sortableInstance = new Sortable(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-50',
                onEnd: () => {
                    this.syncPageFromSidebar();
                },
            });
        },

        // Page-level SortableJS on actual sections
        initPageSortable() {
            const container = document.getElementById('sections-container');
            if (!container) return;
            if (this.pageSortableInstance) this.pageSortableInstance.destroy();
            this.pageSortableInstance = new Sortable(container, {
                animation: 250,
                handle: '.section-drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                scroll: true,
                scrollSensitivity: 80,
                scrollSpeed: 15,
                onEnd: () => {
                    this.syncSidebarFromPage();
                    this.showPageToast('Порядок змінено. Натисніть "Зберегти" щоб застосувати.');
                },
            });
        },

        // Sync sidebar list order + layout to match page sections
        syncSidebarFromPage() {
            const container = document.getElementById('sections-container');
            const sidebarList = this.$refs.sectionsList;
            if (!container || !sidebarList) return;
            const pageWrappers = [...container.querySelectorAll('.section-wrapper')];
            pageWrappers.forEach(el => {
                const id = el.dataset.sectionId;
                const sidebarItem = sidebarList.querySelector('[data-id="' + id + '"]');
                if (sidebarItem) {
                    sidebarItem.dataset.layout = el.dataset.layout || 'full';
                    sidebarList.appendChild(sidebarItem);
                }
            });
        },

        // Sync page sections order + layout to match sidebar list
        syncPageFromSidebar() {
            const container = document.getElementById('sections-container');
            const sidebarList = this.$refs.sectionsList;
            if (!container || !sidebarList) return;
            const sidebarItems = [...sidebarList.querySelectorAll('[data-id]')];
            sidebarItems.forEach(sidebarEl => {
                const id = sidebarEl.dataset.id;
                const layout = sidebarEl.dataset.layout || 'full';
                const pageItem = container.querySelector('[data-section-id="' + id + '"]');
                if (pageItem) {
                    pageItem.dataset.layout = layout;
                    pageItem.classList.remove('md:w-1/2');
                    if (layout === 'half') pageItem.classList.add('md:w-1/2');
                    container.appendChild(pageItem);
                }
            });
        },

        // Show a floating toast on the page (not in sidebar)
        showPageToast(message) {
            let toast = document.getElementById('page-section-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'page-section-toast';
                toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;background:rgba(17,24,39,0.9);color:white;padding:10px 20px;border-radius:12px;font-size:14px;backdrop-filter:blur(8px);box-shadow:0 4px 20px rgba(0,0,0,0.3);transition:opacity 0.3s;pointer-events:none;';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.style.opacity = '1';
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 3000);
        },

        // Move section on page
        movePageSection(sectionId, direction) {
            const container = document.getElementById('sections-container');
            if (!container) return;
            const wrapper = container.querySelector('[data-section-id="' + sectionId + '"]');
            if (!wrapper) return;
            if (direction === 'up' && wrapper.previousElementSibling) {
                container.insertBefore(wrapper, wrapper.previousElementSibling);
            } else if (direction === 'down' && wrapper.nextElementSibling) {
                container.insertBefore(wrapper.nextElementSibling, wrapper);
            }
            this.syncSidebarFromPage();
            this.showPageToast('Порядок змінено. Натисніть "Зберегти" щоб застосувати.');
        },

        // Save per-section background color
        async saveSectionBgColor(sectionId, color) {
            try {
                await this.postData(@json(route('website-builder.sections.settings')), {
                    section_id: sectionId,
                    settings: { bg_color: color },
                });
                this.showPageToast('Колір фону збережено');
            } catch (e) {
                this.showPageToast('Помилка: ' + e.message);
            }
        },

        // Reset section bg color
        async resetSectionBgColor(sectionId) {
            try {
                await this.postData(@json(route('website-builder.sections.settings')), {
                    section_id: sectionId,
                    settings: { bg_color: null },
                });
                // Remove inline bg from section
                const wrapper = document.querySelector('[data-section-id="' + sectionId + '"]');
                if (wrapper) {
                    const section = wrapper.querySelector('section');
                    if (section) section.style.backgroundColor = '';
                }
                this.showPageToast('Колір фону скинуто');
            } catch (e) {
                this.showPageToast('Помилка: ' + e.message);
            }
        },

        // Open sidebar to edit a specific section
        openSectionEditor(sectionId) {
            this.open = true;
            const heroSections = ['hero'];
            const contentMap = {
                'about': 'about',
                'leadership': 'team',
                'faq': 'faq',
                'testimonials': 'testimonials',
                'sermons': 'sermons',
                'gallery': 'gallery',
            };

            if (heroSections.includes(sectionId)) {
                this.activeTab = 'design';
                this.$nextTick(() => { this.accordion.hero = true; });
            } else if (contentMap[sectionId]) {
                this.activeTab = 'content';
                this.$nextTick(() => { this.toggleContentPanel(contentMap[sectionId]); });
            } else {
                this.activeTab = 'sections';
                this.$nextTick(() => {
                    this.initSortable();
                    const item = this.$refs.sectionsList?.querySelector('[data-id="' + sectionId + '"]');
                    if (item) {
                        item.style.background = '#dbeafe';
                        item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => { item.style.background = ''; }, 2000);
                    }
                });
            }
        },

        // --- Content Tab Methods ---

        toggleContentPanel(panel) {
            if (this.contentPanel === panel) {
                this.contentPanel = null;
                return;
            }
            this.contentPanel = panel;
            if (this.cnt[panel] && !this.cnt[panel].loaded && !this.cnt[panel].loading) {
                this.loadContent(panel);
            }
        },

        async loadContent(section) {
            const s = this.cnt[section];
            if (!s) return;
            s.loading = true;
            try {
                const res = await fetch(this.contentRoutes[section], {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                s.items = json.items || [];
                s.loaded = true;
            } catch (e) {
                this.showToast('Помилка завантаження: ' + e.message, 'error');
            } finally {
                s.loading = false;
            }
        },

        contentNew(section, defaults) {
            this.cnt[section].editing = 'new';
            this.cnt[section].form = { ...defaults };
        },

        contentEdit(section, item, fields) {
            this.cnt[section].editing = item.id;
            const form = {};
            fields.forEach(f => { form[f] = item[f] ?? ''; });
            this.cnt[section].form = form;
        },

        contentCancel(section) {
            this.cnt[section].editing = null;
            this.cnt[section].form = {};
        },

        async contentSaveItem(section, hasFiles = false) {
            const s = this.cnt[section];
            const isNew = s.editing === 'new';
            const url = isNew ? this.contentRoutes[section] : this.contentRoutes[section] + '/' + s.editing;
            const method = isNew ? 'POST' : 'PUT';

            this.contentSaving = true;
            try {
                await this.cntFetch(url, method, s.form, hasFiles);
                this.showToast(isNew ? 'Додано' : 'Оновлено', 'success');
                s.editing = null;
                s.form = {};
                s.loaded = false;
                await this.loadContent(section);
            } catch (e) {
                this.showToast('Помилка: ' + e.message, 'error');
            } finally {
                this.contentSaving = false;
            }
        },

        async contentDeleteItem(section, id) {
            if (!confirm('Видалити цей елемент?')) return;
            try {
                await this.cntFetch(this.contentRoutes[section] + '/' + id, 'DELETE');
                this.showToast('Видалено', 'success');
                this.cnt[section].items = this.cnt[section].items.filter(i => i.id !== id);
            } catch (e) {
                this.showToast('Помилка: ' + e.message, 'error');
            }
        },

        async cntFetch(url, method, data = {}, hasFiles = false) {
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            };

            if (method === 'DELETE') {
                const res = await fetch(url, { method, headers });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Помилка видалення');
                }
                return res.json();
            }

            let body;
            let sendMethod = method;

            if (hasFiles) {
                body = new FormData();
                if (method !== 'POST') body.append('_method', method);
                for (const [key, val] of Object.entries(data)) {
                    if (key.startsWith('_')) continue;
                    if (val instanceof File) body.append(key, val);
                    else if (Array.isArray(val)) val.forEach((v, i) => body.append(key + '[' + i + ']', v ?? ''));
                    else if (val === true) body.append(key, '1');
                    else if (val === false) body.append(key, '0');
                    else if (val !== null && val !== undefined) body.append(key, String(val));
                }
                sendMethod = 'POST';
            } else {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(data);
            }

            const res = await fetch(url, { method: sendMethod, headers, body });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Помилка збереження');
            }
            return res.json();
        },

        async saveAbout() {
            this.contentSaving = true;
            try {
                const data = { ...this.aboutForm };
                data.values = (data.values || []).filter(v => v && v.trim());
                await this.cntFetch(this.contentRoutes.about, 'PUT', data);
                this.showToast('Розділ "Про нас" збережено', 'success');
            } catch (e) {
                this.showToast('Помилка: ' + e.message, 'error');
            } finally {
                this.contentSaving = false;
            }
        },

        addAboutValue() {
            this.aboutForm.values.push('');
        },

        removeAboutValue(index) {
            this.aboutForm.values.splice(index, 1);
        },

        getSectionsFromDom() {
            const el = this.$refs.sectionsList;
            if (!el) return [];
            return [...el.querySelectorAll('[data-id]')].map((row, i) => ({
                id: row.dataset.id,
                enabled: row.dataset.enabled === '1',
                order: i,
                layout: row.dataset.layout || 'full',
            }));
        },

        // --- Live Preview ---

        previewColors() {
            const primary = this.colorValues.primary;
            const shades = this.generateShades(primary);

            let css = ':root { --primary-color: ' + primary + '; --primary-dark: ' + shades[700] + '; }\n';
            for (const [shade, hex] of Object.entries(shades)) {
                css += '.bg-primary-' + shade + ' { background-color: ' + hex + ' !important; }\n';
                css += '.text-primary-' + shade + ' { color: ' + hex + ' !important; }\n';
                css += '.border-primary-' + shade + ' { border-color: ' + hex + ' !important; }\n';
                css += '.hover\\:bg-primary-' + shade + ':hover { background-color: ' + hex + ' !important; }\n';
                css += '.hover\\:text-primary-' + shade + ':hover { color: ' + hex + ' !important; }\n';
                css += '.from-primary-' + shade + ' { --tw-gradient-from: ' + hex + '; }\n';
                css += '.via-primary-' + shade + ' { --tw-gradient-via: ' + hex + '; }\n';
                css += '.to-primary-' + shade + ' { --tw-gradient-to: ' + hex + '; }\n';
            }

            let styleEl = document.getElementById('admin-color-overrides');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'admin-color-overrides';
                document.head.appendChild(styleEl);
            }
            styleEl.textContent = css;
        },

        generateShades(hex) {
            const hsl = this.hexToHsl(hex);
            return {
                50:  this.hslToHex(hsl.h, Math.min(100, hsl.s + 20), 97),
                100: this.hslToHex(hsl.h, Math.min(100, hsl.s + 15), 94),
                200: this.hslToHex(hsl.h, Math.min(100, hsl.s + 10), 86),
                300: this.hslToHex(hsl.h, Math.min(100, hsl.s + 5), 76),
                400: this.hslToHex(hsl.h, hsl.s, 62),
                500: this.hslToHex(hsl.h, hsl.s, hsl.l),
                600: this.hslToHex(hsl.h, hsl.s, Math.max(0, hsl.l - 8)),
                700: this.hslToHex(hsl.h, hsl.s, Math.max(0, hsl.l - 16)),
                800: this.hslToHex(hsl.h, Math.max(0, hsl.s - 5), Math.max(0, hsl.l - 24)),
                900: this.hslToHex(hsl.h, Math.max(0, hsl.s - 10), Math.max(0, hsl.l - 32)),
            };
        },

        hexToHsl(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
            const r = parseInt(hex.substr(0, 2), 16) / 255;
            const g = parseInt(hex.substr(2, 2), 16) / 255;
            const b = parseInt(hex.substr(4, 2), 16) / 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;
            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                    case g: h = ((b - r) / d + 2) / 6; break;
                    case b: h = ((r - g) / d + 4) / 6; break;
                }
            }
            return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
        },

        hslToHex(h, s, l) {
            s /= 100; l /= 100;
            const a = s * Math.min(l, 1 - l);
            const f = n => {
                const k = (n + h / 30) % 12;
                const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                return Math.round(255 * color).toString(16).padStart(2, '0');
            };
            return '#' + f(0) + f(8) + f(4);
        },

        previewFonts() {
            const headingFont = this.fontValues.heading;
            const bodyFont = this.fontValues.body;
            const fonts = [...new Set([headingFont, bodyFont])].map(f => f.replace(/ /g, '+')).join('&family=');

            let linkEl = document.getElementById('admin-font-preview');
            if (!linkEl) {
                linkEl = document.createElement('link');
                linkEl.id = 'admin-font-preview';
                linkEl.rel = 'stylesheet';
                document.head.appendChild(linkEl);
            }
            linkEl.href = 'https://fonts.bunny.net/css?family=' + fonts + ':400,500,600,700&display=swap';

            let styleEl = document.getElementById('admin-font-overrides');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'admin-font-overrides';
                document.head.appendChild(styleEl);
            }
            styleEl.textContent = 'body { font-family: "' + bodyFont + '", sans-serif !important; }\n'
                + 'h1, h2, h3, h4, h5, h6 { font-family: "' + headingFont + '", sans-serif !important; }';
        },

        previewHero() {
            const titleEl = document.querySelector('[data-editable="hero-title"]');
            const subtitleEl = document.querySelector('[data-editable="hero-subtitle"]');
            const ctaEl = document.querySelector('[data-editable="hero-cta"]');
            if (titleEl && this.heroValues.title) titleEl.textContent = this.heroValues.title;
            if (subtitleEl) subtitleEl.textContent = this.heroValues.subtitle || '';
            if (ctaEl && this.heroValues.cta_text) ctaEl.textContent = this.heroValues.cta_text;
        },

        previewHeroOverlay() {
            const overlayEl = document.querySelector('[data-editable="hero-overlay"]');
            if (overlayEl) {
                const opacity = this.heroValues.overlay_opacity / 100;
                overlayEl.style.background = 'linear-gradient(to right, rgba(17,24,39,' + opacity + '), rgba(17,24,39,' + (opacity * 0.78) + '))';
            }
        },

        // --- Templates ---

        applyTemplate(tpl) {
            this.activeTemplate = tpl.id;
            // Apply colors
            this.colorValues = { ...tpl.colors };
            this.previewColors();
            // Apply fonts
            this.fontValues = { ...tpl.fonts };
            this.previewFonts();
            this.showToast('Шаблон "' + tpl.name + '" застосовано для перегляду. Збережіть щоб зафіксувати.', 'success');
        },

        // --- Save ---

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        async postData(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(data),
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Помилка збереження');
            }
            return res.json();
        },

        async postFormData(url, data) {
            const formData = new FormData();
            for (const [key, value] of Object.entries(data)) {
                if (value === true) formData.append(key, '1');
                else if (value === false) formData.append(key, '0');
                else if (value !== null && value !== undefined) formData.append(key, value);
            }
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Помилка збереження');
            }
            return res.json();
        },

        async saveAll() {
            this.saving = true;
            let errors = [];

            try {
                await this.postFormData(@json(route('website-builder.design.colors')), this.colorValues);
            } catch (e) { errors.push('Кольори: ' + e.message); }

            try {
                await this.postFormData(@json(route('website-builder.design.fonts')), this.fontValues);
            } catch (e) { errors.push('Шрифти: ' + e.message); }

            try {
                await this.postFormData(@json(route('website-builder.design.hero')), {
                    title: this.heroValues.title,
                    subtitle: this.heroValues.subtitle,
                    cta_text: this.heroValues.cta_text,
                    cta_url: this.heroValues.cta_url,
                    overlay_opacity: this.heroValues.overlay_opacity,
                });
            } catch (e) { errors.push('Hero: ' + e.message); }

            try {
                await this.postFormData(@json(route('website-builder.design.navigation')), {
                    style: this.navValues.style,
                    sticky: this.navValues.sticky,
                });
            } catch (e) { errors.push('Навігація: ' + e.message); }

            try {
                await this.postFormData(@json(route('website-builder.design.footer')), {
                    style: this.footerValues.style,
                    copyright_text: this.footerValues.copyright_text,
                });
            } catch (e) { errors.push('Футер: ' + e.message); }

            try {
                const sectionsData = this.getSectionsFromDom();
                await this.postData(@json(route('website-builder.sections.update')), { sections: sectionsData });
            } catch (e) { errors.push('Секції: ' + e.message); }

            this.saving = false;

            if (errors.length > 0) {
                this.showToast(errors[0], 'error');
            } else {
                sessionStorage.setItem('adminSidebarOpen', '1');
                sessionStorage.setItem('adminSidebarTab', this.activeTab);
                if (this.contentPanel) sessionStorage.setItem('adminContentPanel', this.contentPanel);
                window.location.reload();
            }
        },
    }));
});

// --- Global toolbar functions (called from section toolbars on the page) ---

window.__sectionMoveUp = function(btn) {
    const wrapper = btn.closest('.section-wrapper');
    if (!wrapper) return;
    const sidebar = window.__adminSidebar;
    if (sidebar) sidebar.movePageSection(wrapper.dataset.sectionId, 'up');
};

window.__sectionMoveDown = function(btn) {
    const wrapper = btn.closest('.section-wrapper');
    if (!wrapper) return;
    const sidebar = window.__adminSidebar;
    if (sidebar) sidebar.movePageSection(wrapper.dataset.sectionId, 'down');
};

window.__sectionChangeBg = function(input) {
    const wrapper = input.closest('.section-wrapper');
    if (!wrapper) return;
    const sectionId = wrapper.dataset.sectionId;
    const color = input.value;
    // Apply color to the section element inside the wrapper
    const section = wrapper.querySelector('section');
    if (section) section.style.backgroundColor = color;
    // Save to backend
    const sidebar = window.__adminSidebar;
    if (sidebar) sidebar.saveSectionBgColor(sectionId, color);
};

window.__sectionResetBg = function(btn) {
    const wrapper = btn.closest('.section-wrapper');
    if (!wrapper) return;
    const sectionId = wrapper.dataset.sectionId;
    const sidebar = window.__adminSidebar;
    if (sidebar) sidebar.resetSectionBgColor(sectionId);
};

window.__sectionEdit = function(sectionId) {
    const sidebar = window.__adminSidebar;
    if (sidebar) sidebar.openSectionEditor(sectionId);
};
</script>
