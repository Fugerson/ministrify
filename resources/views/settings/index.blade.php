@extends('layouts.app')

@section('title', __('app.settings'))

@section('content')
<div class="max-w-6xl mx-auto space-y-4 md:space-y-6" x-data="{
    activeTab: (() => { let t = new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('settings_tab') || 'general'; return t === 'theme' ? 'general' : t; })(),
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
    <div id="settings-tabs" class="overflow-x-auto no-scrollbar bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-2">
        <div class="flex gap-1 sm:gap-2 min-w-max">
            <button @click="setTab('general')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'general' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_general') }}
            </button>
            <button @click="setTab('public')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'public' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_public') }}
            </button>
            <button @click="setTab('integrations')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'integrations' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_integrations') }}
            </button>
            <button @click="setTab('data')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'data' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_data') }}
            </button>
            <button @click="setTab('finance')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'finance' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.finances') }}
            </button>
            <button @click="setTab('users')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'users' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_users') }}
            </button>
<button @click="setTab('permissions')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'permissions' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_permissions') }}
            </button>
            <button @click="setTab('audit')"
                    :class="{ 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': activeTab === 'audit' }"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('app.tab_audit') }}
            </button>
        </div>
    </div>

    <!-- General Tab -->
    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
    <!-- Church settings -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{
             name: '{{ addslashes($church->name) }}',
             city: '{{ addslashes($church->city) }}',
             address: '{{ addslashes($church->address ?? '') }}',
             saving: false,
             saved: false,
             timeout: null,
             save() {
                 if (!this.name.trim() || !this.city.trim()) return;
                 this.saving = true;
                 this.saved = false;
                 fetch('{{ route('settings.church') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         _method: 'PUT',
                         name: this.name,
                         city: this.city,
                         address: this.address
                     })
                 }).then(r => r.json()).then(() => {
                     this.saving = false;
                     this.saved = true;
                     setTimeout(() => this.saved = false, 2000);
                 }).catch(() => {
                     this.saving = false;
                 });
             },
             debounceSave() {
                 clearTimeout(this.timeout);
                 this.timeout = setTimeout(() => this.save(), 500);
             }
         }">

        <div class="px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.basic_info') }}</h2>
            <span x-show="saved" x-transition class="text-sm text-green-600 dark:text-green-400">{{ __('app.saved_check') }}</span>
            <span x-show="saving" class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.saving') }}</span>
        </div>

        <div class="p-4 md:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.name_required') }}</label>
                    <input type="text" id="name" x-model="name" @input="debounceSave()" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.city_required') }}</label>
                    <input type="text" id="city" x-model="city" @input="debounceSave()" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.address') }}</label>
                <input type="text" id="address" x-model="address" @input="debounceSave()"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Logo upload -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{ uploading: false, saved: false }">
        <div class="px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.logo') }}</h2>
            <div>
                <span x-show="saved" x-transition class="text-sm text-green-600 dark:text-green-400">{{ __('app.saved_check') }}</span>
                <span x-show="uploading" class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.uploading') }}</span>
            </div>
        </div>

        <div class="p-4 md:p-6">
            @if($church->logo)
                <div class="mb-3">
                    <img src="{{ Storage::url($church->logo) }}" alt="{{ $church->name }} логотип" class="w-16 h-16 object-contain rounded-lg">
                </div>
            @endif
            <div x-data="{ fileName: '' }" class="relative">
                <input type="file" accept="image/*,.heic,.heif" class="sr-only" x-ref="logoInput"
                       @change="
                           if ($event.target.files.length) {
                               fileName = $event.target.files[0].name;
                               uploading = true;
                               saved = false;
                               const formData = new FormData();
                               formData.append('_method', 'PUT');
                               formData.append('name', '{{ addslashes($church->name) }}');
                               formData.append('city', '{{ addslashes($church->city) }}');
                               formData.append('address', '{{ addslashes($church->address ?? '') }}');
                               formData.append('logo', $event.target.files[0]);
                               fetch('{{ route('settings.church') }}', {
                                   method: 'POST',
                                   headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                   body: formData
                               }).then(() => {
                                   uploading = false;
                                   saved = true;
                                   setTimeout(() => location.reload(), 500);
                               }).catch(() => uploading = false);
                           }
                       ">
                <label @click="$refs.logoInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.choose_image') }}</p>
                        <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.image_formats') }}</p>
                    </div>
                    <template x-if="uploading">
                        <svg class="w-5 h-5 text-primary-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </template>
                </label>
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
                    <h3 class="text-lg font-semibold text-white">{{ __('app.website_builder') }}</h3>
                    <p class="text-sm text-white/80">{{ __('app.website_builder_desc') }}</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-white/70 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    <form @submit.prevent="submit($refs.publicSiteForm)" x-ref="publicSiteForm" class="space-y-6" autocomplete="off"
          x-data="{ ...ajaxForm({ url: '{{ route('settings.public-site') }}', method: 'PUT', stayOnPage: true }) }">

        <!-- Enable/Disable & URL -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.public_church_site') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.public_church_site_desc') }}</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ __('app.enable_public_site') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.enable_public_site_desc') }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="public_site_enabled" value="1"
                               {{ $church->public_site_enabled ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.site_url') }}</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500 dark:text-gray-400 text-sm">
                            {{ url('/c/') }}/
                        </span>
                        <input type="text" name="slug" value="{{ old('slug', $church->slug ?? Str::slug($church->name)) }}" required
                               autocomplete="off"
                               class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-r-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="my-church">
                    </div>
                    @if($church->slug && $church->public_site_enabled)
                        <div class="mt-2 flex items-center gap-2" x-data="{ copied: false }">
                            <span class="text-sm text-gray-500 truncate">{{ route('public.church', $church->slug) }}</span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ route('public.church', $church->slug) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                                    :title="copied ? '{{ __('app.copied') }}' : '{{ __('app.copy_link') }}'">
                                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <svg x-show="copied" x-cloak class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    @endif
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.church_description') }}</label>
                    <textarea name="public_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                              placeholder="{{ __('app.church_description_placeholder') }}">{{ old('public_description', $church->public_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.cover_image') }}</label>
                    @if($church->cover_image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($church->cover_image) }}" class="h-32 object-cover rounded-lg">
                        </div>
                    @endif
                    <div x-data="{ fileName: '' }" class="relative">
                        <input type="file" name="cover_image" accept="image/*,.heic,.heif" class="sr-only" x-ref="coverInput" @change="fileName = $event.target.files[0]?.name || ''">
                        <label @click="$refs.coverInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">Обрати зображення</p>
                                <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Розклад богослужінь</label>
                    <input type="text" name="service_times" value="{{ old('service_times', $church->service_times) }}"
                           autocomplete="off"
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
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Публічний телефон</label>
                        <input type="text" name="public_phone" value="{{ old('public_phone', $church->public_phone) }}"
                               autocomplete="off"
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
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фото пастора</label>
                        @if($church->pastor_photo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($church->pastor_photo) }}" class="w-16 h-16 object-cover rounded-lg">
                            </div>
                        @endif
                        <div x-data="{ fileName: '' }" class="relative">
                            <input type="file" name="pastor_photo" accept="image/*,.heic,.heif" class="sr-only" x-ref="pastorInput" @change="fileName = $event.target.files[0]?.name || ''">
                            <label @click="$refs.pastorInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">Обрати фото</p>
                                    <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP</p>
                                </div>
                            </label>
                        </div>
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
                <button type="submit" :disabled="saving" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!saving">Зберегти налаштування сайту</span>
                    <span x-show="saving" x-cloak>Збереження...</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Payment Settings for Public Site -->
    @php
        $paymentSettings = $church->payment_settings ?? [];
    @endphp

    <form @submit.prevent="submit($refs.paymentForm)" x-ref="paymentForm" class="space-y-6" autocomplete="off"
          x-data="{ ...ajaxForm({ url: '{{ route('settings.payments') }}', method: 'PUT', stayOnPage: true }) }">

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
                           autocomplete="off"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono"
                           placeholder="sandbox_XXXXXXXXXXXX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Private Key</label>
                    <input type="password" name="liqpay_private_key"
                           value="{{ old('liqpay_private_key', $paymentSettings['liqpay_private_key'] ?? '') }}"
                           autocomplete="new-password"
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
                           autocomplete="off"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                           placeholder="https://send.monobank.ua/jar/XXXXXXXXX або jar/XXXXXXXXX">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Наприклад: https://send.monobank.ua/jar/ABC123def або просто ABC123def</p>
                </div>
            </div>
        </div>

        <button type="submit" :disabled="saving" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50">
            <span x-show="!saving">Зберегти налаштування платежів</span>
            <span x-show="saving" x-cloak>Збереження...</span>
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

    <!-- Notification Settings -->
    @php
        $notifSettings = $church->settings['notifications'] ?? [];
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="{
            settings: {
                notify_on_assignment: {{ ($notifSettings['notify_on_assignment'] ?? true) ? 'true' : 'false' }},
                notify_on_responsibility: {{ ($notifSettings['notify_on_responsibility'] ?? true) ? 'true' : 'false' }},
                notify_on_plan_request: {{ ($notifSettings['notify_on_plan_request'] ?? true) ? 'true' : 'false' }},
                notify_leader_on_decline: {{ ($notifSettings['notify_leader_on_decline'] ?? true) ? 'true' : 'false' }},
                birthday_reminders: {{ ($notifSettings['birthday_reminders'] ?? true) ? 'true' : 'false' }},
                reminder_day_before: {{ ($notifSettings['reminder_day_before'] ?? true) ? 'true' : 'false' }},
                reminder_same_day: {{ ($notifSettings['reminder_same_day'] ?? true) ? 'true' : 'false' }},
                task_reminders: {{ ($notifSettings['task_reminders'] ?? true) ? 'true' : 'false' }},
            },
            saving: false,
            saved: false,
            toggle(key) {
                this.settings[key] = !this.settings[key];
                this.save();
            },
            save() {
                this.saving = true;
                this.saved = false;
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
                formData.append('_method', 'PUT');
                Object.entries(this.settings).forEach(([key, val]) => {
                    formData.append(key, val ? '1' : '0');
                });
                fetch('{{ route('settings.notifications') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                }).then(() => {
                    this.saving = false;
                    this.saved = true;
                    setTimeout(() => this.saved = false, 2000);
                }).catch(() => this.saving = false);
            }
         }">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Сповіщення</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Управління Telegram-сповіщеннями</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="saving" x-cloak class="text-xs text-gray-400">
                        <svg class="animate-spin h-4 w-4 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <span x-show="saved" x-cloak x-transition.opacity class="text-xs text-green-600 dark:text-green-400">Збережено</span>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Automatic Notifications -->
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">Автоматичні сповіщення</h3>
                <div class="space-y-3">
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('notify_on_assignment')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Сповіщення про призначення</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Повідомлення при призначенні на позицію в служінні</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.notify_on_assignment ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.notify_on_assignment ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('notify_on_responsibility')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Сповіщення про відповідальності</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Повідомлення при призначенні відповідальності на подію</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.notify_on_responsibility ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.notify_on_responsibility ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('notify_on_plan_request')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Сповіщення про план служіння</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Повідомлення про участь у service plan</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.notify_on_plan_request ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.notify_on_plan_request ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('notify_leader_on_decline')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Сповіщення лідеру при відхиленні</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Повідомлення лідеру, коли хтось відхиляє призначення</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.notify_leader_on_decline ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.notify_leader_on_decline ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('birthday_reminders')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Привітання з днем народження</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Автоматичні привітання учасникам у день народження</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.birthday_reminders ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.birthday_reminders ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Reminders -->
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">Нагадування</h3>
                <div class="space-y-3">
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('reminder_day_before')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Нагадування за день</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Нагадування за день до події учасникам служіння</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.reminder_day_before ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.reminder_day_before ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('reminder_same_day')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Нагадування в день події</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Нагадування в день події учасникам служіння</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.reminder_same_day ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.reminder_same_day ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center justify-between cursor-pointer group" @click.prevent="toggle('task_reminders')">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Нагадування про завдання</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Нагадування про невиконані завдання на дошках</p>
                        </div>
                        <div class="relative ml-4 flex-shrink-0">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="settings.task_reminders ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600'">
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="settings.task_reminders ? 'translate-x-5' : ''"></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Calendar Integration -->
    @php
        $googleCalendarSettings = auth()->user()->settings['google_calendar'] ?? null;
        $isGoogleConnected = $googleCalendarSettings && !empty($googleCalendarSettings['access_token']);
    @endphp
    @php
        $savedMappings = $googleCalendarSettings['calendars']
            ?? (!empty($googleCalendarSettings['calendar_id'])
                ? [['calendar_id' => $googleCalendarSettings['calendar_id'], 'ministry_id' => $googleCalendarSettings['ministry_id'] ?? null]]
                : [['calendar_id' => 'primary', 'ministry_id' => null]]);
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
         x-data="googleCalendarSync({{ $isGoogleConnected ? 'true' : 'false' }}, {{ json_encode($savedMappings) }})">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                            <path d="M19 4H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" stroke="#4285F4" stroke-width="1.5"/>
                            <path d="M8 2v4M16 2v4M3 10h18" stroke="#4285F4" stroke-width="1.5" stroke-linecap="round"/>
                            <rect x="7" y="13" width="3" height="3" rx="0.5" fill="#34A853"/>
                            <rect x="14" y="13" width="3" height="3" rx="0.5" fill="#FBBC05"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Google Calendar</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Синхронізація подій між Ministrify та Google</p>
                    </div>
                </div>
                @if($isGoogleConnected)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        Підключено
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6">
            @if($isGoogleConnected)
                @php
                    $lastSyncedAt = $googleCalendarSettings['last_synced_at'] ?? null;
                @endphp
                <!-- Connected State -->
                <div class="space-y-5">
                    <!-- Connection info -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Підключено {{ \Carbon\Carbon::parse($googleCalendarSettings['connected_at'] ?? now())->diffForHumans() }}
                        </span>
                        <button @click="if (confirm({{ Js::from(__('messages.confirm_disconnect_google')) }})) { ajaxAction('{{ route('settings.google-calendar.disconnect') }}', 'POST').then(() => location.reload()) }"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                            Відключити
                        </button>
                    </div>

                    <!-- Auto-sync info -->
                    <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800/50">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p><strong>Автоматична синхронізація</strong> кожні 15 хвилин.</p>
                            <p class="mt-1 text-green-600 dark:text-green-400">
                                @if($lastSyncedAt)
                                    Остання синхронізація: {{ \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() }}
                                @else
                                    Ще не синхронізовано
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Settings: Calendar Mappings -->
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Календарі для синхронізації</label>
                                <span x-show="saved" x-transition.opacity.duration.300ms
                                      class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Збережено
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Оберіть календар Google та команду, до якої потраплятимуть події</p>
                        </div>

                        <!-- Mapping rows -->
                        <div class="space-y-2">
                            <template x-for="(mapping, index) in mappings" :key="index">
                                <div class="p-3 bg-gray-50 dark:bg-gray-900/30 rounded-lg border border-gray-200 dark:border-gray-700"
                                     :class="mapping._new && 'ring-2 ring-primary-300 dark:ring-primary-700'"
                                     x-init="if (mapping._new) { setTimeout(() => { mapping._new = false }, 1500) }">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 min-w-0">
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">Календар Google</label>
                                            <select x-model="mapping.calendar_id" @change="saveMappings()"
                                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                                                <option value="primary">Основний календар</option>
                                                <template x-for="cal in calendars" :key="cal.id">
                                                    <option :value="cal.id" :disabled="!cal.can_sync" x-text="cal.summary + (cal.can_sync ? '' : ' (тільки читання)')"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">Команда</label>
                                            <select x-model="mapping.ministry_id" @change="saveMappings()"
                                                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm">
                                                <option value="">Без прив'язки до команди</option>
                                                @foreach($ministries as $ministry)
                                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button @click="removeMapping(index)" type="button"
                                                x-show="mappings.length > 1"
                                                class="flex-shrink-0 w-8 h-8 mt-5 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                                title="Видалити">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Per-calendar sync button + status -->
                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" @click="syncOne(index)"
                                                :disabled="mapping._syncing"
                                                class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white text-xs font-medium rounded-lg transition-colors">
                                            <svg x-show="!mapping._syncing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <svg x-show="mapping._syncing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span x-text="mapping._syncing ? 'Синхронізація...' : 'Синхронізувати'"></span>
                                        </button>
                                        <span x-show="mapping._message" x-transition
                                              :class="mapping._success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                              class="text-xs truncate" x-text="mapping._message"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add button -->
                        <button @click="addMapping()" type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Додати ще один календар
                        </button>
                    </div>

                    <!-- Sync All Button (when multiple calendars) -->
                    <button x-show="mappings.length > 1"
                            @click="fullSync()"
                            :disabled="loading"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors border border-gray-200 dark:border-gray-600">
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="loading ? 'Синхронізація всіх...' : 'Синхронізувати всі календарі'"></span>
                    </button>

                    <!-- Status Message -->
                    <div x-show="message" x-transition
                         :class="success ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'"
                         class="p-4 rounded-xl border text-sm">
                        <span x-text="message"></span>
                    </div>

                    <!-- Sync Log -->
                    @if(!empty($googleCalendarSettings['last_sync_result']))
                        @php
                            $syncResult = $googleCalendarSettings['last_sync_result'];
                            $toG = $syncResult['to_google'] ?? [];
                            $fromG = $syncResult['from_google'] ?? [];
                            $syncAt = $syncResult['at'] ?? null;
                            $calCount = $syncResult['calendars_count'] ?? 1;
                        @endphp
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Остання синхронізація</h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    @if($syncAt)
                                        {{ \Carbon\Carbon::parse($syncAt)->diffForHumans() }}
                                        ({{ $calCount }} {{ $calCount === 1 ? 'календар' : 'календарів' }})
                                    @endif
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ministrify → Google</p>
                                    <div class="space-y-0.5 text-gray-700 dark:text-gray-300">
                                        @if(($toG['created'] ?? 0) > 0)<p>+ {{ $toG['created'] }} створено</p>@endif
                                        @if(($toG['updated'] ?? 0) > 0)<p>~ {{ $toG['updated'] }} оновлено</p>@endif
                                        @if(($toG['deleted'] ?? 0) > 0)<p>- {{ $toG['deleted'] }} видалено</p>@endif
                                        @if(($toG['created'] ?? 0) + ($toG['updated'] ?? 0) + ($toG['deleted'] ?? 0) === 0)
                                            <p class="text-gray-400 dark:text-gray-500">Без змін</p>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Google → Ministrify</p>
                                    <div class="space-y-0.5 text-gray-700 dark:text-gray-300">
                                        @if(($fromG['created'] ?? 0) > 0)<p>+ {{ $fromG['created'] }} імпортовано</p>@endif
                                        @if(($fromG['updated'] ?? 0) > 0)<p>~ {{ $fromG['updated'] }} оновлено</p>@endif
                                        @if(($fromG['created'] ?? 0) + ($fromG['updated'] ?? 0) === 0)
                                            <p class="text-gray-400 dark:text-gray-500">Без змін</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Rollback Section -->
                    <div x-data="{ showRollback: false }">
                        <button @click="showRollback = !showRollback" type="button"
                                class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-4 h-4 transition-transform" :class="showRollback && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Керування подіями
                        </button>
                        <div x-show="showRollback" x-collapse class="mt-3 space-y-2">
                            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                <p class="text-xs text-amber-700 dark:text-amber-300 mb-3">
                                    Ці дії впливають на події, пов'язані з Google Calendar
                                </p>
                                <div class="space-y-2">
                                    <!-- Unlink from Google -->
                                    <button @click="if (confirm({{ Js::from(__('messages.confirm_unlink_google_events')) }})) { unlinkEvents() }"
                                            :disabled="rollbackLoading"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50">
                                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-700 dark:text-gray-300">Відв'язати від Google</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Події залишаться, але зв'язок з Google буде видалено</p>
                                        </div>
                                    </button>
                                    <!-- Delete imported -->
                                    <button @click="if (confirm({{ Js::from(__('messages.confirm_delete_google_events')) }})) { deleteImported() }"
                                            :disabled="rollbackLoading"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50">
                                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-red-600 dark:text-red-400">Видалити імпортовані події</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Видалить з Ministrify всі події, які прийшли з Google</p>
                                        </div>
                                    </button>
                                </div>
                                <!-- Rollback status -->
                                <div x-show="rollbackMessage" x-transition class="mt-3 text-sm"
                                     :class="rollbackSuccess ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <span x-text="rollbackMessage"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Not Connected State -->
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-50 dark:bg-gray-700 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">Підключіть Google Calendar</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                        Події будуть автоматично синхронізуватися між Ministrify та вашим Google Calendar кожні 15 хвилин
                    </p>
                    <a href="{{ route('settings.google-calendar.redirect') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-300 dark:bg-gray-700 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors shadow-sm">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Підключити
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
    function googleCalendarSync(isConnected = false, savedMappings = []) {
        return {
            isConnected: isConnected,
            loading: false,
            saving: false,
            saved: false,
            savedTimeout: null,
            message: '',
            success: false,
            rollbackLoading: false,
            rollbackMessage: '',
            rollbackSuccess: false,
            calendars: [],
            mappings: savedMappings.length ? savedMappings.map(m => ({
                calendar_id: m.calendar_id || 'primary',
                ministry_id: m.ministry_id ? String(m.ministry_id) : '',
                _syncing: false,
                _message: '',
                _success: false
            })) : [{ calendar_id: 'primary', ministry_id: '', _syncing: false, _message: '', _success: false }],

            async init() {
                if (this.isConnected) {
                    await this.loadCalendars();
                }
            },

            async loadCalendars() {
                try {
                    const res = await fetch('{{ route("settings.google-calendar.calendars") }}');
                    if (res.ok) {
                        const data = await res.json();
                        this.calendars = data.calendars || [];
                    } else if (res.status === 401) {
                        this.calendars = [];
                        console.warn('Google Calendar token expired — reconnect required');
                    }
                } catch (e) {
                    console.error('Failed to load calendars', e);
                }
            },

            addMapping() {
                this.mappings.push({ calendar_id: 'primary', ministry_id: '', _new: true, _syncing: false, _message: '', _success: false });
            },

            removeMapping(index) {
                if (this.mappings.length > 1) {
                    this.mappings.splice(index, 1);
                    this.saveMappings();
                }
            },

            showSaved() {
                this.saved = true;
                clearTimeout(this.savedTimeout);
                this.savedTimeout = setTimeout(() => { this.saved = false }, 2000);
            },

            async saveMappings() {
                this.saving = true;
                try {
                    const res = await fetch('{{ route("settings.google-calendar.calendars.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            calendars: this.mappings.map(m => ({
                                calendar_id: m.calendar_id,
                                ministry_id: m.ministry_id ? parseInt(m.ministry_id) : null
                            }))
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showSaved();
                    }
                } catch (e) {
                    console.error('Failed to save mappings', e);
                }
                this.saving = false;
            },

            async syncOne(index) {
                const mapping = this.mappings[index];
                mapping._syncing = true;
                mapping._message = '';
                try {
                    const res = await fetch('{{ route("settings.google-calendar.full-sync") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            calendar_id: mapping.calendar_id,
                            ministry_id: mapping.ministry_id ? parseInt(mapping.ministry_id) : null
                        })
                    });
                    const data = await res.json();
                    mapping._message = data.message || (data.success ? 'Готово' : 'Помилка');
                    mapping._success = data.success;
                } catch (e) {
                    mapping._message = 'Помилка з\'єднання';
                    mapping._success = false;
                }
                mapping._syncing = false;
            },

            async fullSync() {
                this.loading = true;
                this.message = '';
                try {
                    const res = await fetch('{{ route("settings.google-calendar.full-sync-all") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });
                    const data = await res.json();
                    this.message = data.message || (data.success ? 'Синхронізацію завершено' : 'Помилка синхронізації');
                    this.success = data.success;
                } catch (e) {
                    this.message = 'Помилка з\'єднання';
                    this.success = false;
                }
                this.loading = false;
            },

            async unlinkEvents() {
                this.rollbackLoading = true;
                this.rollbackMessage = '';
                try {
                    const res = await fetch('{{ route("settings.google-calendar.unlink-events") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    this.rollbackMessage = data.message || 'Готово';
                    this.rollbackSuccess = data.success;
                } catch (e) {
                    this.rollbackMessage = 'Помилка з\'єднання';
                    this.rollbackSuccess = false;
                }
                this.rollbackLoading = false;
            },

            async deleteImported() {
                this.rollbackLoading = true;
                this.rollbackMessage = '';
                try {
                    const res = await fetch('{{ route("settings.google-calendar.delete-events") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ scope: 'synced' })
                    });
                    const data = await res.json();
                    this.rollbackMessage = data.message || 'Готово';
                    this.rollbackSuccess = data.success;
                } catch (e) {
                    this.rollbackMessage = 'Помилка з\'єднання';
                    this.rollbackSuccess = false;
                }
                this.rollbackLoading = false;
            }
        }
    }
    </script>
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
                        <button @click="ajaxDelete('{{ route('settings.ministries.destroy', $ministry) }}', {{ Js::from(__('messages.confirm_delete_category')) }}, () => $el.closest('.flex.items-center.justify-between.p-3').remove())"
                                class="text-red-600 hover:text-red-800 text-sm">
                            Видалити
                        </button>
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
            <form @submit.prevent="submit($refs.catCreateForm)" x-ref="catCreateForm"
                  x-data="{ ...ajaxForm({ url: '{{ route('settings.transaction-categories.store') }}', method: 'POST', onSuccess: () => location.reload() }) }">
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
                        <button type="submit" :disabled="saving" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg text-sm transition-colors disabled:opacity-50">
                            <span x-show="!saving">Додати</span>
                            <span x-show="saving" x-cloak>...</span>
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
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></span>
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
                                    <button @click="ajaxDelete('{{ route('settings.transaction-categories.destroy', $category) }}', {{ Js::from(__('messages.confirm_delete_category')) }}, () => $el.closest('.flex.items-center.justify-between.p-3').remove())"
                                            class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <!-- Edit form -->
                            <form x-show="editing" @submit.prevent="submit($refs.editCatForm{{ $category->id }})" x-ref="editCatForm{{ $category->id }}" class="flex-1 flex items-center gap-2"
                                  x-data="{ ...ajaxForm({ url: '{{ route('settings.transaction-categories.update', $category) }}', method: 'PUT', stayOnPage: true, onSuccess: () => location.reload() }) }">
                                <input type="text" name="name" value="{{ $category->name }}" required
                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="color" name="color" value="{{ $category->color }}"
                                       class="w-10 h-8 border border-gray-300 dark:border-gray-600 rounded cursor-pointer">
                                <button type="submit" :disabled="saving" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm disabled:opacity-50">Зберегти</button>
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
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></span>
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
                                    <button @click="ajaxDelete('{{ route('settings.transaction-categories.destroy', $category) }}', {{ Js::from(__('messages.confirm_delete_category')) }}, () => $el.closest('.flex.items-center.justify-between.p-3').remove())"
                                            class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <!-- Edit form -->
                            <form x-show="editing" @submit.prevent="submit($refs.editCatForm{{ $category->id }})" x-ref="editCatForm{{ $category->id }}" class="flex-1 flex items-center gap-2"
                                  x-data="{ ...ajaxForm({ url: '{{ route('settings.transaction-categories.update', $category) }}', method: 'PUT', stayOnPage: true, onSuccess: () => location.reload() }) }">
                                <input type="text" name="name" value="{{ $category->name }}" required
                                       class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                <input type="color" name="color" value="{{ $category->color }}"
                                       class="w-10 h-8 border border-gray-300 dark:border-gray-600 rounded cursor-pointer">
                                <button type="submit" :disabled="saving" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm disabled:opacity-50">Зберегти</button>
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
                        <button @click="ajaxDelete('{{ route('tags.destroy', $tag) }}', {{ Js::from(__('messages.confirm_delete_tag')) }}, () => $el.closest('.flex.items-center.justify-between.p-3').remove())"
                                class="text-red-600 hover:text-red-800 text-sm">
                            Видалити
                        </button>
                    </div>
                @endforeach
            </div>

            <form @submit.prevent="submit($refs.tagCreateForm)" x-ref="tagCreateForm" class="flex gap-2"
                  x-data="{ ...ajaxForm({ url: '{{ route('tags.store') }}', method: 'POST', onSuccess: () => location.reload() }) }">
                <input type="text" name="name" placeholder="Новий тег" required
                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <input type="color" name="color" value="#3b82f6"
                       class="w-12 h-10 border border-gray-300 dark:border-gray-600 rounded-lg">
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg disabled:opacity-50">
                    <span x-show="!saving">Додати</span>
                    <span x-show="saving" x-cloak>...</span>
                </button>
            </form>
        </div>
    </div>
    </div>

    <!-- Finance Tab -->
    <div x-show="activeTab === 'finance'" x-cloak class="space-y-6">
        <!-- Initial Balance (Multi-currency) -->
        @php
            $enabledCurrenciesForBalance = $church->enabled_currencies ?? ['UAH'];
            $initialBalances = $church->initial_balances ?? [];
            $currencyInfo = [
                'UAH' => ['symbol' => '₴', 'name' => 'Гривня', 'flag' => '🇺🇦'],
                'USD' => ['symbol' => '$', 'name' => 'Долар', 'flag' => '🇺🇸'],
                'EUR' => ['symbol' => '€', 'name' => 'Євро', 'flag' => '🇪🇺'],
            ];
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="{
                 balances: {
                     UAH: {{ $initialBalances['UAH'] ?? $church->initial_balance ?? 0 }},
                     USD: {{ $initialBalances['USD'] ?? 0 }},
                     EUR: {{ $initialBalances['EUR'] ?? 0 }}
                 },
                 balanceDate: '{{ $church->initial_balance_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}',
                 enabledCurrencies: {{ json_encode($enabledCurrenciesForBalance) }},
                 saving: false,
                 saved: false,
                 timeout: null,
                 save() {
                     if (!this.balanceDate) return;
                     this.saving = true;
                     this.saved = false;
                     fetch('{{ route('settings.finance') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({
                             _method: 'PUT',
                             initial_balances: this.balances,
                             initial_balance_date: this.balanceDate
                         })
                     }).then(r => r.json()).then(() => {
                         this.saving = false;
                         this.saved = true;
                         setTimeout(() => this.saved = false, 2000);
                     }).catch(() => {
                         this.saving = false;
                     });
                 },
                 debounceSave() {
                     clearTimeout(this.timeout);
                     this.timeout = setTimeout(() => this.save(), 500);
                 }
             }"
             @currencies-changed.window="enabledCurrencies = $event.detail">

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
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
                    <div>
                        <span x-show="saved" x-transition class="text-sm text-green-600 dark:text-green-400">Збережено ✓</span>
                        <span x-show="saving" class="text-sm text-gray-500 dark:text-gray-400">Збереження...</span>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Як це працює:</strong> Вкажіть суму в кожній валюті, яка була на рахунках церкви на певну дату.
                        Баланс по кожній валюті ведеться окремо.
                    </p>
                </div>

                <!-- Date -->
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата балансу *</label>
                    <input type="date" required
                           x-model="balanceDate" @change="save()"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <!-- Currency balances -->
                <div class="space-y-3">
                    @foreach(['UAH', 'USD', 'EUR'] as $code)
                    <div x-show="enabledCurrencies.includes('{{ $code }}')" class="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center gap-2 w-24">
                            <span class="text-xl">{{ $currencyInfo[$code]['flag'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $code }}</span>
                        </div>
                        <div class="flex-1 relative">
                            <input type="number" step="0.01" min="0"
                                   x-model="balances.{{ $code }}" @input="debounceSave()"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">{{ $currencyInfo[$code]['symbol'] }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Currency Settings -->
        @php
            $enabledCurrencies = $church->enabled_currencies ?? ['UAH'];
            $allCurrencies = [
                'UAH' => ['symbol' => '₴', 'name' => 'Гривня (UAH)', 'flag' => '🇺🇦'],
                'USD' => ['symbol' => '$', 'name' => 'Долар США (USD)', 'flag' => '🇺🇸'],
                'EUR' => ['symbol' => '€', 'name' => 'Євро (EUR)', 'flag' => '🇪🇺'],
            ];
            $rates = app(\App\Services\NbuExchangeRateService::class)->getCurrentRates();
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="{
                 currencies: {{ json_encode($enabledCurrencies) }},
                 saving: false,
                 saved: false,
                 save() {
                     this.saving = true;
                     this.saved = false;
                     fetch('{{ route('settings.currencies') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({
                             _method: 'PUT',
                             currencies: this.currencies
                         })
                     }).then(r => r.json()).then(() => {
                         this.saving = false;
                         this.saved = true;
                         setTimeout(() => this.saved = false, 2000);
                     }).catch(() => this.saving = false);
                 },
                 toggle(code) {
                     if (code === 'UAH') return;
                     if (this.currencies.includes(code)) {
                         this.currencies = this.currencies.filter(c => c !== code);
                     } else {
                         this.currencies.push(code);
                     }
                     this.save();
                     this.$dispatch('currencies-changed', this.currencies);
                 }
             }">

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
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
                <div>
                    <span x-show="saved" x-transition class="text-sm text-green-600 dark:text-green-400">Збережено ✓</span>
                    <span x-show="saving" class="text-sm text-gray-500 dark:text-gray-400">Збереження...</span>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Мультивалютний облік:</strong> Кошти зберігаються в тій валюті, в якій надійшли.
                        Баланс відображається окремо по кожній валюті. Еквівалент в гривні — довідково.
                    </p>
                </div>

                <div class="space-y-3">
                    @foreach($allCurrencies as $code => $currency)
                    <label class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                           :class="{ 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800': currencies.includes('{{ $code }}') }"
                           @click.prevent="toggle('{{ $code }}')">
                        <input type="checkbox" value="{{ $code }}"
                               :checked="currencies.includes('{{ $code }}')"
                               {{ $code === 'UAH' ? 'disabled' : '' }}
                               class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500 pointer-events-none">
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

                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg" x-show="currencies.length > 1">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Поточні курси НБУ</h4>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['USD', 'EUR'] as $code)
                            @if(isset($rates[$code]))
                            <div class="flex items-center gap-2" x-show="currencies.includes('{{ $code }}')">
                                <span class="text-lg">{{ $allCurrencies[$code]['flag'] }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">1 {{ $code }} =</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($rates[$code], 2, ',', ' ') }} ₴</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Tab -->
    <div x-show="activeTab === 'users'" x-cloak class="space-y-6">
        <!-- Self-registration setting -->
        @if(auth()->user()->canEdit('settings'))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="{
                 enabled: {{ $church->getSetting('self_registration_enabled') !== false ? 'true' : 'false' }},
                 saving: false,
                 toggle() {
                     this.enabled = !this.enabled;
                     this.saving = true;
                     fetch('{{ route('settings.self-registration') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({
                             _method: 'PUT',
                             enabled: this.enabled
                         })
                     }).then(r => r.json()).then(() => {
                         this.saving = false;
                         showGlobalToast(this.enabled ? 'Самореєстрацію увімкнено' : 'Самореєстрацію вимкнено', 'success');
                     }).catch(() => {
                         this.enabled = !this.enabled;
                         this.saving = false;
                         showGlobalToast('Помилка збереження', 'error');
                     });
                 }
             }">
            <div class="px-4 md:px-6 py-4 flex items-center justify-between">
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Самореєстрація учасників</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Дозволити людям самостійно реєструватися у вашій церкві</p>
                </div>
                <button @click="toggle()" :disabled="saving"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        :class="enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'"
                        role="switch" :aria-checked="enabled">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                          :class="enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
            <div x-show="enabled" x-collapse class="px-4 md:px-6 pb-4 pt-0">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Нові користувачі отримають базовий доступ. Ви можете призначити їм роль пізніше.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @php
            $mapPerson = function($p) {
                return ['id' => $p->id, 'name' => $p->full_name, 'email' => $p->email, 'phone' => $p->phone];
            };
            $availablePeopleJs = $availablePeople->map($mapPerson)->values();
            $matchesJs = [];
            foreach ($potentialMatches as $uid => $matches) {
                $matchesJs[$uid] = $matches->map($mapPerson)->values();
            }
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Користувачі системи</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $users->count() }} {{ trans_choice('користувач|користувачі|користувачів', $users->count()) }}</p>
                </div>
                @if(auth()->user()->canEdit('settings'))
                <a href="{{ route('settings.users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Запросити
                </a>
                @endif
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
                            <td class="px-3 md:px-6 py-3 md:py-4"
                                x-data="{
                                    editing: false,
                                    saving: false,
                                    selectedRoleId: '{{ $user->church_role_id ?? '' }}',
                                    linkedPersonId: null,
                                    linkedPersonName: '',
                                    showPersonSearch: false,
                                    personSearch: '',
                                    suggestedMatches: @js($matchesJs[$user->id] ?? []),
                                    isPending: {{ $user->church_role_id ? 'false' : 'true' }},
                                    get filteredPeople() {
                                        if (!this.personSearch || this.personSearch.length < 2) return [];
                                        const q = this.personSearch.toLowerCase();
                                        return window._availablePeople.filter(p =>
                                            (p.name && p.name.toLowerCase().includes(q)) ||
                                            (p.email && p.email.toLowerCase().includes(q)) ||
                                            (p.phone && p.phone.includes(q))
                                        ).slice(0, 8);
                                    },
                                    selectPerson(p) {
                                        this.linkedPersonId = p.id;
                                        this.linkedPersonName = p.name + (p.email ? ' (' + p.email + ')' : '');
                                        this.showPersonSearch = false;
                                        this.personSearch = '';
                                    },
                                    clearPerson() { this.linkedPersonId = null; this.linkedPersonName = ''; },
                                    async saveRole() {
                                        this.saving = true;
                                        try {
                                            const body = {
                                                _method: 'PUT',
                                                name: @js($user->name),
                                                email: @js($user->email),
                                                church_role_id: this.selectedRoleId || null,
                                            };
                                            // link_person_id: lightweight linking without name/email override
                                            if (this.linkedPersonId) {
                                                body.link_person_id = this.linkedPersonId;
                                            }
                                            const res = await fetch('{{ route('settings.users.update', $user) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json',
                                                },
                                                body: JSON.stringify(body),
                                            });
                                            if (res.ok) {
                                                const data = await res.json().catch(() => ({}));
                                                showToast('success', data.message || 'Збережено!');
                                                const sel = this.$el.querySelector('select');
                                                const roleName = sel.options[sel.selectedIndex].text;
                                                const badge = this.$el.querySelector('[x-show="!editing"] button, [x-show="!editing"] span');
                                                if (badge) {
                                                    const svg = badge.querySelector('svg')?.outerHTML || '';
                                                    badge.innerHTML = roleName + ' ' + svg;
                                                }
                                                this.editing = false;
                                            } else {
                                                const data = await res.json();
                                                alert(data.message || 'Помилка');
                                            }
                                        } catch (e) {
                                            console.error(e);
                                            alert('Помилка збереження');
                                        } finally {
                                            this.saving = false;
                                        }
                                    }
                                }">
                                @if(auth()->user()->canEdit('settings') && $user->id !== auth()->id())
                                <div x-show="!editing">
                                    @if($user->churchRole)
                                    <button @click="editing = true" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80 transition-opacity"
                                            style="background-color: {{ $user->churchRole->color }}30; color: {{ $user->churchRole->color }}"
                                            title="Натисніть щоб змінити роль">
                                        {{ $user->churchRole->name }}
                                        <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    @else
                                    <button @click="editing = true" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 cursor-pointer hover:opacity-80 transition-opacity"
                                            title="Натисніть щоб призначити роль">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Очікує
                                        <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    @endif
                                </div>
                                <div x-show="editing" x-cloak>
                                    <div class="flex items-center gap-1">
                                        <select x-model="selectedRoleId" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1 pl-2 pr-7">
                                            <option value="">Без ролі</option>
                                            @foreach($churchRoles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <button @click="saveRole()" :disabled="saving"
                                                class="p-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 disabled:opacity-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button @click="editing = false; selectedRoleId = '{{ $user->church_role_id ?? '' }}'; clearPerson()"
                                                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    </div>

                                    {{-- Person linking for pending users --}}
                                    <template x-if="isPending">
                                        <div class="mt-2">
                                            {{-- Suggested matches --}}
                                            <template x-if="suggestedMatches.length > 0 && !linkedPersonId">
                                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded p-2 mb-1">
                                                    <p class="text-xs font-medium text-amber-800 dark:text-amber-200 mb-1">Можливий збіг:</p>
                                                    <template x-for="m in suggestedMatches" x-bind:key="m.id">
                                                        <button x-on:click="selectPerson(m)"
                                                                class="block w-full text-left px-2 py-1 text-xs bg-white dark:bg-gray-700 rounded border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 mb-1">
                                                            <span class="font-medium" x-text="m.name"></span>
                                                            <span class="text-gray-500 ml-1" x-text="m.email || ''"></span>
                                                            <span class="text-gray-500 ml-1" x-text="m.phone || ''"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Selected person --}}
                                            <template x-if="linkedPersonId">
                                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded px-2 py-1 flex items-center justify-between text-xs">
                                                    <span class="text-green-800 dark:text-green-200">
                                                        Привʼязати: <span class="font-medium" x-text="linkedPersonName"></span>
                                                    </span>
                                                    <button x-on:click="clearPerson()" class="text-red-500 hover:text-red-700 ml-1">&times;</button>
                                                </div>
                                            </template>

                                            {{-- Manual search --}}
                                            <template x-if="!linkedPersonId">
                                                <div>
                                                    <button x-on:click="showPersonSearch = !showPersonSearch"
                                                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        <span x-text="showPersonSearch ? 'Сховати' : 'Знайти в базі'"></span>
                                                    </button>
                                                    <template x-if="showPersonSearch">
                                                        <div class="mt-1 relative">
                                                            <input type="text" x-model="personSearch"
                                                                   placeholder="Імʼя, email, телефон..."
                                                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                                            <template x-if="filteredPeople.length > 0">
                                                                <div class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-lg max-h-32 overflow-y-auto">
                                                                    <template x-for="p in filteredPeople" x-bind:key="p.id">
                                                                        <button x-on:click="selectPerson(p)"
                                                                                class="block w-full text-left px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 text-xs border-b border-gray-100 dark:border-gray-600 last:border-0">
                                                                            <span class="font-medium" x-text="p.name"></span>
                                                                            <span class="text-gray-500 ml-1" x-text="p.email || ''"></span>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="personSearch.length >= 2 && filteredPeople.length === 0">
                                                                <p class="text-xs text-gray-400 mt-1">Нікого не знайдено</p>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                @else
                                @if($user->churchRole)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      style="background-color: {{ $user->churchRole->color }}30; color: {{ $user->churchRole->color }}">
                                    {{ $user->churchRole->name }}
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Очікує
                                </span>
                                @endif
                                @endif
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="inline-flex items-center text-sm text-green-600 dark:text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    <span class="hidden md:inline">Активний</span>
                                </span>
                            </td>
                            <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(auth()->user()->canEdit('settings'))
                                @if($user->id !== auth()->id())
                                <a href="{{ route('settings.users.edit', $user) }}" class="p-2 inline-flex text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button @click="ajaxDelete('{{ route('settings.users.destroy', $user) }}', {{ Js::from(__('messages.are_you_sure')) }}, () => $el.closest('tr').remove())"
                                        class="p-2 inline-flex text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @else
                                <span class="text-gray-400 dark:text-gray-500 text-xs">Це ви</span>
                                @endif
                                @endif
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
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
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

            <div class="p-4 sm:p-6">
                <!-- Admin notice -->
                <template x-if="isCurrentRoleAdmin()">
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-blue-800 dark:text-blue-200">Ця роль має повний доступ</p>
                                <p class="text-sm text-blue-600 dark:text-blue-400">Права ролі з повним доступом не можна обмежити.</p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Permissions table (desktop) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white">Модуль</th>
                                @foreach($permissionActions as $actionKey => $actionLabel)
                                <th class="pb-3 text-sm font-semibold text-gray-900 dark:text-white text-center w-24">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
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

                <!-- Permissions cards (mobile) -->
                <div class="sm:hidden space-y-3">
                    @foreach($permissionModules as $moduleKey => $module)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        <p class="font-medium text-gray-900 dark:text-white mb-2">{{ $module['label'] }}</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            @foreach($permissionActions as $actionKey => $actionLabel)
                                @if(in_array($actionKey, $module['actions'] ?? []))
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <template x-if="isCurrentRoleAdmin()">
                                        <input type="checkbox" checked disabled
                                               class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 bg-gray-100 dark:bg-gray-600 cursor-not-allowed">
                                    </template>
                                    <template x-if="!isCurrentRoleAdmin()">
                                        <input type="checkbox"
                                               x-model="rolePermissions[currentRoleId]['{{ $moduleKey }}']"
                                               value="{{ $actionKey }}"
                                               @change="markDirty()"
                                               class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                                    </template>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $actionLabel }}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <template x-if="!isCurrentRoleAdmin()">
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button @click="resetToDefaults()"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Скинути до стандартних
                        </button>
                        <button @click="savePermissions()"
                                :disabled="!isDirty || saving"
                                class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!saving">Зберегти зміни</span>
                            <span x-show="saving" class="flex items-center justify-center gap-2">
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

        <!-- Per-User Permission Overrides -->
        @php
            $nonAdminUsers = $users->filter(fn($u) => $u->church_role_id && !$u->churchRole?->is_admin_role)->values();
        @endphp
        @if($nonAdminUsers->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
             x-data="userOverridesManager()">
            <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Персональні права</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Додаткові права для конкретних користувачів понад їхню роль</p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($nonAdminUsers as $u)
                <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3"
                     x-data="{ expanded: false }">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">{{ mb_substr($u->name, 0, 1) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $u->name }}</div>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($u->churchRole)
                                <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded-full"
                                      style="background-color: {{ $u->churchRole->color }}30; color: {{ $u->churchRole->color }}">
                                    {{ $u->churchRole->name }}
                                </span>
                                @endif
                                {{-- Override badges --}}
                                @if(!empty($u->permission_overrides))
                                    @foreach($u->permission_overrides as $mod => $actions)
                                        @foreach($actions as $act)
                                        <span class="inline-flex px-1.5 py-0.5 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                            {{ $permissionModules[$mod]['label'] ?? $mod }}: {{ $permissionActions[$act] ?? $act }}
                                        </span>
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <button @click="openUserModal({{ $u->id }}, @js($u->name), @js($u->churchRole?->name ?? 'Без ролі'))"
                            class="flex-shrink-0 px-3 py-1.5 text-sm font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors">
                        Налаштувати
                    </button>
                </div>
                @endforeach
            </div>

            {{-- User Permissions Modal --}}
            <div x-show="showModal" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="flex items-start justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-xl transform transition-all w-full max-w-3xl mx-4"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         @keydown.escape.window="showModal = false">

                        <div class="p-6">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Додаткові права</h3>
                                <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="modalUserName"></span>
                                — роль: <span x-text="modalRoleName"></span>
                            </p>

                            {{-- Legend --}}
                            <div class="flex flex-wrap items-center gap-4 mb-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-4 h-4 rounded bg-green-100 dark:bg-green-900/40 border border-green-300 dark:border-green-700 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    Від ролі
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-4 h-4 rounded bg-purple-100 dark:bg-purple-900/40 border-2 border-purple-400 dark:border-purple-500"></span>
                                    Додатково
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-4 h-4 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-300 dark:text-gray-600">&mdash;</span>
                                    Недоступно
                                </span>
                            </div>

                            {{-- Permissions Table --}}
                            <div class="overflow-x-auto -mx-6 px-6">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="text-left py-2 pr-4 font-medium text-gray-600 dark:text-gray-400">Модуль</th>
                                            @foreach($permissionActions as $actKey => $actLabel)
                                            <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-20">{{ mb_substr($actLabel, 0, 6) }}.</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                        @foreach($permissionModules as $modKey => $mod)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="py-2.5 pr-4">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $mod['label'] }}</span>
                                            </td>
                                            @foreach($permissionActions as $actKey => $actLabel)
                                            <td class="text-center py-2.5 px-2">
                                                @if(in_array($actKey, $mod['actions']))
                                                {{-- Role permission (green check) --}}
                                                <template x-if="isRolePerm('{{ $modKey }}', '{{ $actKey }}')">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-green-100 dark:bg-green-900/40">
                                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    </span>
                                                </template>
                                                {{-- Override checkbox (purple) --}}
                                                <template x-if="!isRolePerm('{{ $modKey }}', '{{ $actKey }}')">
                                                    <label class="inline-flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox"
                                                               :checked="isOverride('{{ $modKey }}', '{{ $actKey }}')"
                                                               @change="toggleOverride('{{ $modKey }}', '{{ $actKey }}')"
                                                               class="w-5 h-5 rounded border-2 border-purple-300 dark:border-purple-600 text-purple-600 dark:text-purple-500 focus:ring-purple-500 dark:focus:ring-purple-600 bg-white dark:bg-gray-700 cursor-pointer">
                                                    </label>
                                                </template>
                                                @else
                                                <span class="text-gray-300 dark:text-gray-600">&mdash;</span>
                                                @endif
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-end gap-3">
                            <button type="button" @click="showModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Скасувати
                            </button>
                            <button type="button" @click="saveUserOverrides()"
                                    :disabled="savingUser"
                                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-xl hover:bg-purple-700 disabled:opacity-50 transition-colors">
                                <span x-show="!savingUser">Зберегти</span>
                                <span x-show="savingUser" x-cloak>Збереження...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

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
                        <li><strong>Персональні права</strong> — додаткові дозволи для конкретних користувачів понад їхню роль</li>
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
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Журнал дій</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Останні 100 змін у системі</p>
            </div>

            <!-- Desktop table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дата</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Дія</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Тип</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Об'єкт</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">Зміни</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
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
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
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

            <!-- Mobile cards -->
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($auditLogs as $log)
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
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($log->user)
                                    <div class="w-6 h-6 shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                        <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400">
                                            {{ mb_substr($log->user->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $log->user_name }}</span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap shrink-0">{{ $log->created_at->format('d.m H:i') }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                {{ $log->action_label }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ $log->model_label }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($log->model_name, 25) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                        Записів не знайдено
                    </div>
                @endforelse
            </div>

            @if($auditLogs->count() >= 100)
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-center">
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
window._availablePeople = @json($availablePeopleJs);

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
            if (!confirm({{ Js::from(__('messages.confirm_reset_role_permissions')) }}.replace(':role', roleName))) {
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

function userOverridesManager() {
    // Pre-embedded data for each non-admin user
    const usersData = {
        @foreach($users->filter(fn($u) => $u->church_role_id && !$u->churchRole?->is_admin_role) as $u)
        {{ $u->id }}: {
            rolePerms: @json($u->churchRole?->getAllPermissions() ?? []),
            overrides: @json($u->permission_overrides ?? (object)[]),
        },
        @endforeach
    };

    return {
        showModal: false,
        savingUser: false,
        modalUserId: null,
        modalUserName: '',
        modalRoleName: '',
        rolePerms: {},
        overrides: {},

        openUserModal(userId, userName, roleName) {
            this.modalUserId = userId;
            this.modalUserName = userName;
            this.modalRoleName = roleName;
            const d = usersData[userId] || {};
            this.rolePerms = JSON.parse(JSON.stringify(d.rolePerms || {}));
            this.overrides = JSON.parse(JSON.stringify(d.overrides || {}));
            this.showModal = true;
        },

        isRolePerm(mod, action) {
            return (this.rolePerms[mod] || []).includes(action);
        },

        isOverride(mod, action) {
            return (this.overrides[mod] || []).includes(action);
        },

        toggleOverride(mod, action) {
            if (!this.overrides[mod]) {
                this.overrides = { ...this.overrides, [mod]: [action] };
            } else {
                const idx = this.overrides[mod].indexOf(action);
                if (idx === -1) {
                    this.overrides = { ...this.overrides, [mod]: [...this.overrides[mod], action] };
                } else {
                    const newActions = this.overrides[mod].filter((_, i) => i !== idx);
                    if (newActions.length === 0) {
                        const { [mod]: _, ...rest } = this.overrides;
                        this.overrides = rest;
                    } else {
                        this.overrides = { ...this.overrides, [mod]: newActions };
                    }
                }
            }
        },

        async saveUserOverrides() {
            this.savingUser = true;
            try {
                const res = await fetch(`/settings/users/${this.modalUserId}/permissions`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ overrides: this.overrides }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.showModal = false;
                    if (window.showGlobalToast) showGlobalToast('Додаткові права збережено', 'success');
                    window.location.reload();
                } else {
                    if (window.showGlobalToast) showGlobalToast(data.message || 'Помилка збереження', 'error');
                }
            } catch (e) {
                if (window.showGlobalToast) showGlobalToast('Помилка збереження', 'error');
            } finally {
                this.savingUser = false;
            }
        },
    };
}
</script>
@endpush
@endsection
