{{-- Admin Sidebar Editor for Public Site --}}
@php
    $settings = $church->public_site_settings ?? [];
    $colors = $settings['colors'] ?? [];
    $fonts = $settings['fonts'] ?? [];
    $hero = $settings['hero'] ?? [];
    $navigation = $settings['navigation'] ?? [];
    $footer = $settings['footer'] ?? [];
    $sections = $settings['sections'] ?? [];

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
    {{-- FAB Button --}}
    <button
        x-show="!open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        @click="open = true"
        class="fixed bottom-6 left-6 z-[60] w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-xl hover:shadow-2xl flex items-center justify-center transition-all duration-200 group"
        title="Редактор сайту"
    >
        <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </button>

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
                    <template x-for="(section, index) in sectionItems" :key="section.id">
                        <div :data-id="section.id" class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 rounded-lg border border-gray-200 cursor-grab active:cursor-grabbing group">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            <span class="flex-1 text-sm text-gray-700" x-text="section.label"></span>
                            <button @click="section.enabled = !section.enabled" :class="section.enabled ? 'bg-primary-600' : 'bg-gray-300'" class="relative w-9 h-5 rounded-full transition-colors flex-shrink-0">
                                <span :class="section.enabled ? 'translate-x-4' : 'translate-x-0.5'" class="absolute top-0.5 left-0 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ========== CONTENT TAB ========== --}}
            <div x-show="activeTab === 'content'" class="p-4">
                <p class="text-xs text-gray-500 mb-3">Редагуйте контент через адмін-панель. Посилання відкриються у новій вкладці.</p>
                <div class="space-y-2">
                    @php
                        $contentLinks = [
                            ['label' => 'Про нас', 'url' => route('website-builder.about.edit')],
                            ['label' => 'Команда', 'url' => route('website-builder.team.index')],
                            ['label' => 'Проповіді', 'url' => route('website-builder.sermons.index')],
                            ['label' => 'Галерея', 'url' => route('website-builder.gallery.index')],
                            ['label' => 'Блог', 'url' => route('website-builder.blog.index')],
                            ['label' => 'FAQ', 'url' => route('website-builder.faq.index')],
                            ['label' => 'Свідчення', 'url' => route('website-builder.testimonials.index')],
                            ['label' => 'Події', 'url' => route('events.index')],
                        ];
                    @endphp
                    @foreach($contentLinks as $link)
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="flex items-center justify-between px-3 py-2.5 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors group">
                            <span class="text-sm text-gray-700">{{ $link['label'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endforeach
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
        activeTab: 'design',
        saving: false,
        sortableInstance: null,
        toast: { show: false, message: '', type: 'success' },

        accordion: {
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

        sectionItems: @json($sections),

        init() {
            if (sessionStorage.getItem('adminSidebarOpen')) {
                this.open = true;
                this.activeTab = sessionStorage.getItem('adminSidebarTab') || 'design';
                sessionStorage.removeItem('adminSidebarOpen');
                sessionStorage.removeItem('adminSidebarTab');
            }
            this.$watch('activeTab', (val) => {
                if (val === 'sections') {
                    this.$nextTick(() => this.initSortable());
                }
            });
        },

        initSortable() {
            if (this.sortableInstance) this.sortableInstance.destroy();
            const el = this.$refs.sectionsList;
            if (!el) return;
            this.sortableInstance = new Sortable(el, {
                animation: 150,
                handle: '.cursor-grab',
                ghostClass: 'opacity-50',
                onEnd: (evt) => {
                    const items = [...el.querySelectorAll('[data-id]')].map(el => el.dataset.id);
                    const reordered = [];
                    items.forEach((id, i) => {
                        const item = this.sectionItems.find(s => s.id === id);
                        if (item) {
                            reordered.push({ ...item, order: i });
                        }
                    });
                    this.sectionItems = reordered;
                },
            });
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
                const sectionsData = this.sectionItems.map((s, i) => ({
                    id: s.id,
                    enabled: s.enabled,
                    order: i,
                }));
                await this.postData(@json(route('website-builder.sections.update')), { sections: sectionsData });
            } catch (e) { errors.push('Секції: ' + e.message); }

            this.saving = false;

            if (errors.length > 0) {
                this.showToast(errors[0], 'error');
            } else {
                sessionStorage.setItem('adminSidebarOpen', '1');
                sessionStorage.setItem('adminSidebarTab', this.activeTab);
                window.location.reload();
            }
        },
    }));
});
</script>
