@extends('layouts.app')

@section('title', 'Редактор сайту')

@section('content')
<div x-data="siteEditor()" x-cloak class="fixed inset-0 flex flex-col bg-gray-100 dark:bg-gray-900" style="z-index: 50;">
    {{-- Top bar --}}
    <div class="flex items-center justify-between h-14 px-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('website-builder.index') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-sm font-medium">Назад</span>
            </a>
            <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>
            <h1 class="text-sm font-semibold text-gray-900 dark:text-white">Конструктор сайту</h1>
        </div>

        <div class="flex items-center gap-3">
            {{-- Viewport switcher --}}
            <div class="hidden sm:flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button @click="viewport = 'desktop'" :class="viewport === 'desktop' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'" class="p-1.5 rounded-md transition-all" title="Desktop">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </button>
                <button @click="viewport = 'tablet'" :class="viewport === 'tablet' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'" class="p-1.5 rounded-md transition-all" title="Tablet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </button>
                <button @click="viewport = 'mobile'" :class="viewport === 'mobile' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'" class="p-1.5 rounded-md transition-all" title="Mobile">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </button>
            </div>

            {{-- Preview link --}}
            <a href="{{ route('public.church', $church->slug) }}" target="_blank" class="hidden sm:inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>

            {{-- Save button --}}
            <button @click="save()" :disabled="saving || !hasChanges" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="hasChanges ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span x-text="saving ? 'Збереження...' : 'Зберегти'"></span>
            </button>
        </div>
    </div>

    {{-- Main content --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Sidebar --}}
        <div class="w-80 lg:w-96 flex-shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden">
            {{-- Sidebar tabs --}}
            <div class="flex border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button @click="activeTab = 'sections'" :class="activeTab === 'sections' ? 'text-primary-600 border-primary-600' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'" class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                    Секції
                </button>
                <button @click="activeTab = 'design'" :class="activeTab === 'design' ? 'text-primary-600 border-primary-600' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'" class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                    Дизайн
                </button>
                <button @click="activeTab = 'content'" :class="activeTab === 'content' ? 'text-primary-600 border-primary-600' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'" class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                    Контент
                </button>
            </div>

            {{-- Sidebar content --}}
            <div class="flex-1 overflow-y-auto">
                {{-- Sections tab --}}
                <div x-show="activeTab === 'sections'" class="p-4 space-y-1" x-ref="sectionList">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Перетягніть для зміни порядку. Натисніть для налаштувань.</p>

                    <template x-for="(section, index) in sections" :key="section.id">
                        <div class="group" :data-section-id="section.id">
                            {{-- Section row --}}
                            <div class="flex items-center gap-2 p-3 rounded-lg cursor-pointer transition-colors"
                                 :class="expandedSection === section.id ? 'bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 border border-transparent'"
                                 @click="toggleExpanded(section.id)">
                                {{-- Drag handle --}}
                                <div class="drag-handle cursor-grab text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300" @click.stop>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm0 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm-2 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm8-14a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm-2 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm2 4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
                                </div>

                                {{-- Section name --}}
                                <span class="flex-1 text-sm font-medium text-gray-900 dark:text-white" x-text="section.name"></span>

                                {{-- Toggle --}}
                                <button @click.stop="toggleSection(section.id)" class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="section.enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'">
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                          :class="section.enabled ? 'translate-x-4' : 'translate-x-0'"></span>
                                </button>

                                {{-- Expand arrow --}}
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expandedSection === section.id ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            {{-- Expanded settings --}}
                            <div x-show="expandedSection === section.id" x-collapse class="px-3 pb-3" x-init="ensureSettings(section.id)">
                                <div class="ml-6 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg space-y-3">
                                    {{-- Title & subtitle for most sections --}}
                                    <template x-if="!['service_times', 'contact'].includes(section.id)">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Заголовок секції</label>
                                                <input type="text" x-model="sectionSettings[section.id].title" @input="markChanged()" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500" :placeholder="section.name">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Підзаголовок</label>
                                                <input type="text" x-model="sectionSettings[section.id].subtitle" @input="markChanged()" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                        </div>
                                    </template>

                                    {{-- About: link to content editor --}}
                                    <template x-if="section.id === 'about'">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('website-builder.about.edit') }}" class="text-primary-600 hover:underline">Редагувати контент (місія, візія) &rarr;</a>
                                        </p>
                                    </template>

                                    {{-- Service times hint --}}
                                    <template x-if="section.id === 'service_times'">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Розклад служінь налаштовується в загальних налаштуваннях церкви.</p>
                                        </div>
                                    </template>

                                    {{-- Contact hint --}}
                                    <template x-if="section.id === 'contact'">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Контактна інформація береться з налаштувань церкви.</p>
                                        </div>
                                    </template>

                                    {{-- Content management links --}}
                                    <template x-if="contentLinks[section.id]">
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <a :href="contentLinks[section.id]" class="text-xs text-primary-600 hover:underline">Керувати контентом &rarr;</a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Design tab --}}
                <div x-show="activeTab === 'design'" class="p-4 space-y-4">
                    <a href="{{ route('website-builder.design.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Кольори та стиль</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Налаштувати дизайн</p>
                        </div>
                    </a>

                    <a href="{{ route('website-builder.templates.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Шаблони</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Обрати стиль сайту</p>
                        </div>
                    </a>
                </div>

                {{-- Content tab --}}
                <div x-show="activeTab === 'content'" class="p-4 space-y-2">
                    <a href="{{ route('website-builder.about.edit') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Про нас</span>
                    </a>

                    <a href="{{ route('website-builder.team.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Команда</span>
                    </a>

                    <a href="{{ route('website-builder.sermons.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Проповіді</span>
                    </a>

                    <a href="{{ route('website-builder.gallery.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Галерея</span>
                    </a>

                    <a href="{{ route('website-builder.blog.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Блог</span>
                    </a>

                    <a href="{{ route('website-builder.faq.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">FAQ</span>
                    </a>

                    <a href="{{ route('website-builder.testimonials.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Свідчення</span>
                    </a>

                    <a href="{{ route('events.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm text-gray-900 dark:text-white">Події</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Preview area --}}
        <div class="flex-1 flex items-center justify-center bg-gray-200 dark:bg-gray-900 p-4 overflow-hidden">
            <div class="relative bg-white rounded-lg shadow-2xl overflow-hidden transition-all duration-300"
                 :style="viewportStyle()">
                <iframe id="preview-iframe" :src="previewUrl" class="w-full h-full border-0" style="min-height: 100%;"></iframe>
            </div>
        </div>
    </div>

    {{-- Save notification --}}
    <div x-show="showNotification" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
         class="fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50"
         :class="notificationType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
         x-text="notificationMessage"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function siteEditor() {
    return {
        sections: @json($sections),
        sectionSettings: @json($sectionSettings),
        previewUrl: @json($previewUrl),
        activeTab: 'sections',
        expandedSection: null,
        viewport: 'desktop',
        saving: false,
        hasChanges: false,
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        sortableInstance: null,

        contentLinks: {
            leadership: '{{ route('website-builder.team.index') }}',
            events: '{{ route('events.index') }}',
            sermons: '{{ route('website-builder.sermons.index') }}',
            ministries: '{{ route('ministries.index') }}',
            groups: '{{ route('groups.index') }}',
            gallery: '{{ route('website-builder.gallery.index') }}',
            testimonials: '{{ route('website-builder.testimonials.index') }}',
            blog: '{{ route('website-builder.blog.index') }}',
            faq: '{{ route('website-builder.faq.index') }}',
        },

        init() {
            this.$nextTick(() => {
                this.initSortable();
            });
        },

        initSortable() {
            const el = this.$refs.sectionList;
            if (!el) return;

            this.sortableInstance = Sortable.create(el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-30',
                onEnd: (evt) => {
                    const items = [...el.querySelectorAll('[data-section-id]')];
                    const newOrder = items.map(item => item.dataset.sectionId);

                    this.sections = newOrder.map((id, index) => {
                        const section = this.sections.find(s => s.id === id);
                        return { ...section, order: index };
                    });

                    this.hasChanges = true;
                }
            });
        },

        toggleSection(id) {
            const section = this.sections.find(s => s.id === id);
            if (section) {
                section.enabled = !section.enabled;
                this.hasChanges = true;
            }
        },

        toggleExpanded(id) {
            this.expandedSection = this.expandedSection === id ? null : id;
        },

        ensureSettings(sectionId) {
            if (!this.sectionSettings[sectionId]) {
                this.sectionSettings[sectionId] = { title: '', subtitle: '' };
            }
            if (!this.sectionSettings[sectionId].title) {
                this.sectionSettings[sectionId].title = '';
            }
            if (!this.sectionSettings[sectionId].subtitle) {
                this.sectionSettings[sectionId].subtitle = '';
            }
        },

        markChanged() {
            this.hasChanges = true;
        },

        viewportStyle() {
            switch (this.viewport) {
                case 'tablet': return 'width: 768px; height: 100%;';
                case 'mobile': return 'width: 375px; height: 100%;';
                default: return 'width: 100%; height: 100%;';
            }
        },

        async save() {
            if (this.saving) return;
            this.saving = true;

            try {
                // Save sections order + enabled state
                const sectionsData = this.sections.map((s, i) => ({
                    id: s.id,
                    enabled: s.enabled,
                    order: i,
                }));

                const sectionsResponse = await fetch('{{ route('website-builder.sections.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ sections: sectionsData }),
                });

                if (!sectionsResponse.ok) throw new Error('Failed to save sections');

                // Save section settings
                for (const [sectionId, settings] of Object.entries(this.sectionSettings)) {
                    if (Object.keys(settings).length > 0) {
                        const settingsResponse = await fetch('{{ route('website-builder.sections.settings') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ section_id: sectionId, settings }),
                        });

                        if (!settingsResponse.ok) throw new Error('Failed to save section settings');
                    }
                }

                this.hasChanges = false;
                this.refreshPreview();
                this.notify('Зміни збережено!', 'success');
            } catch (error) {
                this.notify('Помилка збереження: ' + error.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        refreshPreview() {
            const iframe = document.getElementById('preview-iframe');
            if (iframe) {
                iframe.src = this.previewUrl + '&_t=' + Date.now();
            }
        },

        notify(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            setTimeout(() => { this.showNotification = false; }, 3000);
        },
    };
}
</script>

<style>
    /* Hide main app layout header when in editor */
    [x-cloak] { display: none !important; }
</style>
@endsection
