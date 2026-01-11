@extends('layouts.app')

@section('title', $person->full_name)

@php
    use Illuminate\Support\Facades\Storage;
    $isAdmin = auth()->user()->isAdmin();
    $personMinistries = $person->ministries->keyBy('id');
@endphp

@section('content')
<div class="space-y-8" x-data="personProfile()" @change="autoSave()">
    @if($isAdmin)
    <!-- Auto-save status indicator -->
    <div x-show="saveStatus !== 'idle'" x-cloak
         class="fixed top-20 right-4 z-50 px-4 py-2 rounded-xl shadow-lg text-sm font-medium transition-all"
         :class="{
             'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': saveStatus === 'saving',
             'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': saveStatus === 'saved',
             'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': saveStatus === 'error'
         }">
        <span x-show="saveStatus === 'saving'" class="flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Збереження...
        </span>
        <span x-show="saveStatus === 'saved'" class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Збережено
        </span>
        <span x-show="saveStatus === 'error'" class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Помилка збереження
        </span>
    </div>
    <form id="personForm" method="POST" action="{{ route('people.update', $person) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
    @endif

    <!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-24"></div>
        <div class="px-6 pb-6 -mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <!-- Avatar -->
                <div class="flex-shrink-0 relative" x-data="avatarUpload()">
                    @if($isAdmin)
                        <template x-if="preview">
                            <img :src="preview" class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-lg">
                        </template>
                        <template x-if="!preview && existingPhoto">
                            <img src="{{ $person->photo ? Storage::url($person->photo) : '' }}" class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-lg">
                        </template>
                        <template x-if="!preview && !existingPhoto">
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-800 shadow-lg">
                                <span class="text-3xl font-bold text-gray-500 dark:text-gray-300">{{ mb_substr($person->first_name, 0, 1) }}</span>
                            </div>
                        </template>
                        <!-- Photo upload overlay -->
                        <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 hover:opacity-100 rounded-2xl cursor-pointer transition-opacity border-4 border-transparent">
                            <input type="file" name="photo" accept="image/*" class="sr-only" @change="handleFileSelect($event)">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                        <button type="button" x-show="preview || existingPhoto" @click="removePhoto()"
                                class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <input type="hidden" name="remove_photo" x-ref="removePhotoInput" value="0">
                    @else
                        @if($person->photo)
                            <img class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-800 shadow-lg"
                                 src="{{ Storage::url($person->photo) }}" alt="">
                        @else
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-800 shadow-lg">
                                <span class="text-3xl font-bold text-gray-500 dark:text-gray-300">{{ mb_substr($person->first_name, 0, 1) }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1">
                    @if($isAdmin)
                        <div class="flex items-center gap-2">
                            <input type="text" name="first_name" value="{{ old('first_name', $person->first_name) }}" required
                                   class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 px-0 py-0 w-auto"
                                   placeholder="Ім'я">
                            <input type="text" name="last_name" value="{{ old('last_name', $person->last_name) }}" required
                                   class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 focus:ring-0 px-0 py-0 w-auto"
                                   placeholder="Прізвище">
                        </div>
                    @else
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $person->full_name }}</h1>
                    @endif
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($person->tags as $tag)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                        @if($person->joined_date && $stats['membership_days'] !== null)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ floor($stats['membership_days'] / 365) > 0 ? floor($stats['membership_days'] / 365) . ' р. ' : '' }}{{ $stats['membership_days'] % 365 }} днів в церкві
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-2">
                    @if($person->phone)
                        <a href="tel:{{ $person->phone }}" class="p-3 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded-xl hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </a>
                    @endif
                    @if($person->telegram_username)
                        <a href="https://t.me/{{ ltrim($person->telegram_username, '@') }}" target="_blank" class="p-3 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-xl hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.535.223l.19-2.72 4.94-4.463c.215-.19-.047-.295-.334-.105l-6.11 3.85-2.63-.82c-.57-.18-.583-.57.12-.847l10.27-3.96c.475-.18.89.115.735.84z"/>
                            </svg>
                        </a>
                    @endif
                    @if($person->email)
                        <a href="mailto:{{ $person->email }}" class="p-3 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-xl hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                @if($isAdmin)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Телефон</p>
                        <input type="tel" name="phone" value="{{ old('phone', $person->phone) }}"
                               class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm"
                               placeholder="Не вказано">
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</p>
                        <input type="email" name="email" value="{{ old('email', $person->email) }}"
                               class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm truncate"
                               placeholder="Не вказано">
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">День народження</p>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $person->birth_date?->format('Y-m-d')) }}"
                               class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm">
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Адреса</p>
                        <input type="text" name="address" value="{{ old('address', $person->address) }}"
                               class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm truncate"
                               placeholder="Не вказано">
                    </div>
                @else
                    @if($person->phone)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Телефон</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $person->phone }}</p>
                        </div>
                    @endif
                    @if($person->email)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $person->email }}</p>
                        </div>
                    @endif
                    @if($person->birth_date)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">День народження</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $person->birth_date->format('d.m.Y') }}</p>
                        </div>
                    @endif
                    @if($person->address)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Адреса</p>
                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $person->address }}</p>
                        </div>
                    @endif
                    @if($church->shepherds_enabled && $person->shepherd)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Опікун</p>
                            <a href="{{ route('people.show', $person->shepherd) }}" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">{{ $person->shepherd->full_name }}</a>
                        </div>
                    @endif
                @endif
            </div>

            @if($isAdmin)
            <!-- Additional Admin Fields -->
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Telegram</p>
                    <div class="flex items-center">
                        <span class="text-gray-400 text-sm">@</span>
                        <input type="text" name="telegram_username" value="{{ old('telegram_username', $person->telegram_username) }}"
                               class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm"
                               placeholder="username">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Церковна роль</p>
                    <select name="church_role_id"
                            class="w-full font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border-0 p-0 focus:ring-0 text-sm cursor-pointer">
                        <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">-- Не вказано --</option>
                        @foreach($churchRoles as $role)
                            <option value="{{ $role->id }}" {{ $person->church_role_id == $role->id ? 'selected' : '' }} class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($church->shepherds_enabled)
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3" x-data="shepherdSearch()">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Опікун</p>
                        <a x-show="selectedId" :href="'/people/' + selectedId"
                           class="text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Профіль
                        </a>
                    </div>
                    <div class="relative">
                        <input type="text"
                               x-model="searchQuery"
                               @focus="isOpen = true"
                               @click.away="isOpen = false"
                               @keydown.escape="isOpen = false"
                               @keydown.arrow-down.prevent="highlightNext()"
                               @keydown.arrow-up.prevent="highlightPrev()"
                               @keydown.enter.prevent="selectHighlighted()"
                               :placeholder="selectedName || 'Пошук опікуна...'"
                               :class="{'text-gray-400': !searchQuery && selectedName}"
                               class="w-full text-sm font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 placeholder-gray-500 dark:placeholder-gray-400">

                        <!-- Dropdown -->
                        <div x-show="isOpen && filteredShepherds.length > 0"
                             x-transition
                             class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-auto">
                            <!-- Clear option -->
                            <div @click="clearShepherd()"
                                 class="px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 text-sm text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                -- Без опікуна --
                            </div>
                            <template x-for="(shepherd, index) in filteredShepherds" :key="shepherd.id">
                                <div @click="selectShepherd(shepherd)"
                                     @mouseenter="highlightedIndex = index"
                                     :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index}"
                                     class="px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                        <template x-if="shepherd.photo">
                                            <img :src="shepherd.photo" :alt="shepherd.full_name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!shepherd.photo">
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs font-medium" x-text="shepherd.initials"></div>
                                        </template>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white" x-text="shepherd.full_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <span x-show="updating" class="text-xs text-gray-400">Збереження...</span>
                </div>
                @endif
            </div>

            <!-- Personal Details -->
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4" x-data="{ maritalStatus: '{{ $person->marital_status ?? '' }}' }">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Стать</p>
                    <select name="gender"
                            class="w-full font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border-0 p-0 focus:ring-0 text-sm cursor-pointer">
                        <option value="" class="bg-white dark:bg-gray-800">-- Не вказано --</option>
                        @foreach(\App\Models\Person::GENDERS as $value => $label)
                            <option value="{{ $value }}" {{ $person->gender === $value ? 'selected' : '' }} class="bg-white dark:bg-gray-800">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Сімейний стан</p>
                    <select name="marital_status" x-model="maritalStatus"
                            class="w-full font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 border-0 p-0 focus:ring-0 text-sm cursor-pointer">
                        <option value="" class="bg-white dark:bg-gray-800">-- Не вказано --</option>
                        @foreach(\App\Models\Person::MARITAL_STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $person->marital_status === $value ? 'selected' : '' }} class="bg-white dark:bg-gray-800">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Дата хрещення</p>
                    <input type="date" name="baptism_date" value="{{ old('baptism_date', $person->baptism_date?->format('Y-m-d')) }}"
                           class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm">
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3" x-show="maritalStatus === 'married'" x-cloak>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Річниця весілля</p>
                    <input type="date" name="anniversary" value="{{ old('anniversary', $person->anniversary?->format('Y-m-d')) }}"
                           class="w-full font-medium text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 text-sm">
                </div>
            </div>
            <input type="hidden" name="joined_date" value="{{ $person->joined_date?->format('Y-m-d') }}">

            <!-- User Account & System Access -->
            <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl border border-purple-200 dark:border-purple-800"
                 x-data="userRoleManager()">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">Доступ до системи</p>
                            @if($person->user)
                                <div class="flex items-center gap-2">
                                    <template x-if="!editingEmail">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="userEmail">{{ $person->user->email }}</p>
                                            <button @click="editingEmail = true; $nextTick(() => $refs.emailInput.focus())"
                                                    class="p-1 text-gray-400 hover:text-purple-600 rounded transition-colors" title="Редагувати email">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <button @click="resetPassword()" :disabled="resettingPassword"
                                                    class="p-1 text-gray-400 hover:text-orange-600 rounded transition-colors disabled:opacity-50" title="Скинути пароль">
                                                <svg x-show="!resettingPassword" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                </svg>
                                                <svg x-show="resettingPassword" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="editingEmail">
                                        <div class="flex items-center gap-2">
                                            <input type="email" x-model="userEmail" x-ref="emailInput"
                                                   @keydown.enter="updateEmail()"
                                                   @keydown.escape="editingEmail = false; userEmail = '{{ $person->user->email }}'"
                                                   class="px-2 py-1 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 text-gray-900 dark:text-white w-48">
                                            <button @click="updateEmail()" class="p-1 text-green-600 hover:text-green-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button @click="editingEmail = false; userEmail = '{{ $person->user->email }}'" class="p-1 text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Немає доступу до системи</p>
                            @endif
                        </div>
                    </div>

                    @if($person->user)
                        <!-- System Role Selector -->
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Рівень доступу:</span>
                            <select x-model="role" @change="updateRole()"
                                    :disabled="saving || {{ $person->user->id === auth()->id() ? 'true' : 'false' }}"
                                    class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="volunteer" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Служитель</option>
                                <option value="leader" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Лідер команди</option>
                                <option value="admin" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Адміністратор</option>
                            </select>
                            <span x-show="saving" class="text-purple-600 dark:text-purple-400">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span x-show="saved" x-cloak class="text-green-600 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            @if($person->user->id === auth()->id())
                                <span class="text-xs text-gray-500 dark:text-gray-400">(це ви)</span>
                            @endif
                        </div>
                    @else
                        <!-- Create Account Button -->
                        <button type="button" @click="showCreateModal = true"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Надати доступ
                        </button>
                    @endif
                </div>

                <!-- Create Account Modal -->
                <template x-teleport="body">
                    <div x-show="showCreateModal" x-cloak
                         class="fixed inset-0 z-50 overflow-y-auto"
                         @keydown.escape.window="showCreateModal = false">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
                            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Надати доступ до системи</h3>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email для входу</label>
                                        <input type="email" x-model="newEmail"
                                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white"
                                               >
                                        <p x-show="createError" x-text="createError" class="mt-1 text-sm text-red-600"></p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Рівень доступу</label>
                                        <select x-model="newChurchRoleId"
                                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-purple-500 text-gray-900 dark:text-white">
                                            <option value="" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Оберіть роль...</option>
                                            @foreach($churchRoles as $role)
                                            <option value="{{ $role->id }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                                {{ $role->name }}@if($role->is_admin_role) (Повний доступ)@endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Користувач отримає доступ до системи. Пароль буде згенеровано автоматично.
                                    </p>
                                </div>

                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" @click="showCreateModal = false"
                                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                        Скасувати
                                    </button>
                                    <button type="button" @click="createAccount()"
                                            :disabled="creating || !newEmail || !newChurchRoleId"
                                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                                        <svg x-show="creating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="creating ? 'Створення...' : 'Створити'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Password Display Modal -->
                <template x-teleport="body">
                    <div x-show="showPasswordModal" x-cloak
                         class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen p-4">
                            <div class="fixed inset-0 bg-black/50"></div>
                            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                                <div class="text-center">
                                    <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Запрошення надіслано</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Лист з посиланням для встановлення пароля надіслано на email користувача.
                                    </p>

                                    <button type="button" @click="closePasswordModal()"
                                            class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                        Готово
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['attendance_30_days'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Відвідувань за 30 днів</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['services_this_month'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Подій цього місяця</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['attendance_rate'] ?? 0 }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Відвідуваність (3 міс.)</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['services_total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Всього подій</p>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
    <!-- Hidden tags to preserve existing values -->
    @foreach($person->tags as $tag)
        <input type="hidden" name="tags[]" value="{{ $tag->id }}">
    @endforeach

    <!-- Ministries (Admin editable) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Команди</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($ministries as $ministry)
                @php
                    $isInMinistry = $personMinistries->has($ministry->id);
                    $personPositionIds = [];
                    if ($isInMinistry) {
                        $pivot = $personMinistries->get($ministry->id)->pivot;
                        $personPositionIds = is_array($pivot->position_ids)
                            ? $pivot->position_ids
                            : json_decode($pivot->position_ids ?? '[]', true);
                    }
                @endphp
                <div x-data="{ open: {{ $isInMinistry ? 'true' : 'false' }} }"
                     class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 transition-colors"
                     :class="open ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800' : ''">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="ministries[{{ $ministry->id }}][selected]" value="1"
                               @click="open = $event.target.checked"
                               {{ $isInMinistry ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</span>
                    </label>

                    <div x-show="open" x-cloak x-collapse class="mt-3 ml-6 flex flex-wrap gap-2">
                        @foreach($ministry->positions as $position)
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="ministries[{{ $ministry->id }}][positions][]" value="{{ $position->id }}"
                                       {{ in_array($position->id, $personPositionIds ?? []) ? 'checked' : '' }}
                                       class="w-3 h-3 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="ml-1 text-xs text-gray-600 dark:text-gray-400">{{ $position->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Ministries & Groups -->
        <div class="lg:col-span-2 space-y-6">
            @if(!$isAdmin)
            <!-- Ministries (View only for non-admins) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Команди</h2>
                </div>
                @if($person->ministries->count() > 0)
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($person->ministries as $ministry)
                            <a href="{{ route('ministries.show', $ministry) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $ministry->color ?? '#3b82f6' }}20;">
                                        <svg class="w-5 h-5" style="color: {{ $ministry->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $ministry->name }}</p>
                                        @php
                                            $positionIds = is_array($ministry->pivot->position_ids)
                                                ? $ministry->pivot->position_ids
                                                : json_decode($ministry->pivot->position_ids ?? '[]', true);
                                            $positions = $ministry->positions->whereIn('id', $positionIds ?? []);
                                        @endphp
                                        @if($positions->count() > 0)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $positions->pluck('name')->implode(', ') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Не бере участь у командах
                    </div>
                @endif
            </div>
            @endif

            <!-- Groups -->
            @if($person->groups->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Групи</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($person->groups as $group)
                        <a href="{{ route('groups.show', $group) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $group->color }}20;">
                                    <svg class="w-5 h-5" style="color: {{ $group->color }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $group->name }}</p>
                                    @if($group->meeting_day)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ ['monday' => 'Понеділок', 'tuesday' => 'Вівторок', 'wednesday' => 'Середа', 'thursday' => 'Четвер', 'friday' => "П'ятниця", 'saturday' => 'Субота', 'sunday' => 'Неділя'][$group->meeting_day] ?? $group->meeting_day }}
                                            @if($group->meeting_time) {{ $group->meeting_time->format('H:i') }} @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sheep (People under their care) -->
            @if($church->shepherds_enabled && $person->is_shepherd && $person->sheep->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Підопічні ({{ $person->sheep->count() }})</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($person->sheep as $sheep)
                        <a href="{{ route('people.show', $sheep) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                @if($sheep->photo)
                                <img src="{{ Storage::url($sheep->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ mb_substr($sheep->first_name, 0, 1) }}{{ mb_substr($sheep->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $sheep->full_name }}</p>
                                    @if($sheep->churchRoleRelation)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sheep->churchRoleRelation->name }}</p>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Attendance Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність (12 тижнів)</h2>
                <div class="h-48">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Активність</h2>
                @if($stats['last_attended'])
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Останнє відвідування: {{ $stats['last_attended']->format('d.m.Y') }}
                    </span>
                @endif
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                @forelse($activities as $activity)
                    <div class="p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $activity['color'] === 'green' ? 'bg-green-100 dark:bg-green-900/50' :
                               ($activity['color'] === 'yellow' ? 'bg-yellow-100 dark:bg-yellow-900/50' : 'bg-red-100 dark:bg-red-900/50') }}">
                            <span class="text-sm">{{ $activity['icon'] }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] }}</p>
                            @if(isset($activity['subtitle']))
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['subtitle'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $activity['date']->format('d.m.Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        Немає активності за останні 3 місяці
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Assignments -->
    @if($person->assignments->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="font-semibold text-gray-900 dark:text-white">Останні призначення</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($person->assignments->take(10) as $assignment)
                <a href="{{ route('events.show', $assignment->event) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $assignment->event->ministry->color ?? '#3b82f6' }}20;">
                            <svg class="w-5 h-5" style="color: {{ $assignment->event->ministry->color ?? '#3b82f6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $assignment->event->title }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $assignment->event->date->format('d.m.Y') }} &bull; {{ $assignment->position->name }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                        {{ $assignment->status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' :
                           ($assignment->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300') }}">
                        {{ $assignment->status_label }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Family Relationships -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-6"
         @if($isAdmin) x-data="familyManager()" @endif>
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <h2 class="font-semibold text-gray-900 dark:text-white">Сім'я</h2>
            </div>
            @if($isAdmin)
            <button @click="showAddModal = true" type="button"
                    class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Додати
            </button>
            @endif
        </div>

        @if($isAdmin)
        <template x-if="familyMembers.length > 0">
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="member in familyMembers" :key="member.relationship_id">
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <a :href="'/people/' + member.person_id" class="flex items-center gap-3 flex-1 min-w-0">
                            <template x-if="member.photo">
                                <img :src="member.photo" alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            </template>
                            <template x-if="!member.photo">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-medium text-white" x-text="member.first_name.charAt(0)"></span>
                                </div>
                            </template>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate" x-text="member.full_name"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="member.relationship_label"></p>
                            </div>
                        </a>
                        <button type="button" @click="deleteRelationship(member.relationship_id)"
                                class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="familyMembers.length === 0">
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Немає зв'язаних членів сім'ї</p>
                <button @click="showAddModal = true" type="button" class="mt-2 text-primary-600 dark:text-primary-400 text-sm hover:underline">
                    Додати члена сім'ї
                </button>
            </div>
        </template>
        @else
        @php $familyMembers = $person->family_members; @endphp
        @if($familyMembers->count() > 0)
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($familyMembers as $member)
            <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <a href="{{ route('people.show', $member->person) }}" class="flex items-center gap-3 flex-1 min-w-0">
                    @if($member->person->photo)
                    <img src="{{ Storage::url($member->person->photo) }}" alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-medium text-white">{{ mb_substr($member->person->first_name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $member->person->full_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->relationship_label }}</p>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p>Немає зв'язаних членів сім'ї</p>
        </div>
        @endif
        @endif

        @if($isAdmin)
        <!-- Add Family Modal -->
        <template x-teleport="body">
            <div x-show="showAddModal" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 @keydown.escape.window="showAddModal = false">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showAddModal = false"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Додати члена сім'ї</h3>

                        <div class="space-y-4">
                            <!-- Person Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Людина</label>
                                <div class="relative" x-data="{ inputFocused: false }">
                                    <input type="text" x-model="searchQuery"
                                           @input.debounce.300ms="searchPeople()"
                                           @focus="inputFocused = true; loadInitialPeople()"
                                           @blur="setTimeout(() => inputFocused = false, 200)"
                                           placeholder="Пошук за ім'ям..."
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white">

                                    <!-- Selected person display -->
                                    <div x-show="selectedPerson" class="absolute inset-y-0 right-2 flex items-center">
                                        <button @click.prevent="selectedPerson = null; searchQuery = ''; searchResults = []" type="button" class="p-1 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Search results dropdown -->
                                    <div x-show="inputFocused && searchResults.length > 0 && !selectedPerson" x-cloak
                                         class="absolute z-[100] left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-48 overflow-auto">
                                        <template x-for="person in searchResults" :key="person.id">
                                            <div @mousedown.prevent="selectPerson(person)"
                                                 class="px-4 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-3">
                                                <template x-if="person.photo">
                                                    <img :src="person.photo" class="w-8 h-8 rounded-full object-cover">
                                                </template>
                                                <template x-if="!person.photo">
                                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                        <span class="text-xs text-gray-500" x-text="person.name.charAt(0)"></span>
                                                    </div>
                                                </template>
                                                <span class="text-gray-900 dark:text-white" x-text="person.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Relationship Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип зв'язку</label>
                                <select x-model="relationshipType"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white">
                                    <option value="">-- Оберіть --</option>
                                    <option value="spouse">Чоловік/Дружина</option>
                                    <option value="child">Дитина</option>
                                    <option value="parent">Батько/Мати</option>
                                    <option value="sibling">Брат/Сестра</option>
                                </select>
                            </div>

                            <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showAddModal = false"
                                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                Скасувати
                            </button>
                            <button type="button" @click="addFamilyMember()"
                                    :disabled="saving || !selectedPerson || !relationshipType"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="saving ? 'Збереження...' : 'Додати'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        @endif
    </div>

    <!-- Notes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-3">Нотатки</h2>
        @if($isAdmin)
            <textarea name="notes" rows="3" placeholder="Додаткова інформація про людину..."
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-sm">{{ old('notes', $person->notes) }}</textarea>
        @else
            @if($person->notes)
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $person->notes }}</p>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-sm">Немає нотаток</p>
            @endif
        @endif
    </div>

    @if($isAdmin)
    </form>
    @endif

    <!-- Actions -->
    <div class="flex items-center justify-between mt-6">
        <a href="{{ route('people.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад до списку
        </a>

        @admin
        <form method="POST" action="{{ route('people.destroy', $person) }}"
              onsubmit="return confirm('Ви впевнені, що хочете видалити цю людину?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">
                Видалити
            </button>
        </form>
        @endadmin
    </div>
</div>

@push('scripts')
<script>
function personProfile() {
    return {
        saveStatus: 'idle',
        saveTimeout: null,

        autoSave() {
            // Debounce - wait 800ms after last change
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveForm();
            }, 800);
        },

        async saveForm() {
            const form = document.getElementById('personForm');
            if (!form) return;

            this.saveStatus = 'saving';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.saveStatus = 'saved';
                    setTimeout(() => {
                        this.saveStatus = 'idle';
                    }, 2000);
                } else {
                    this.saveStatus = 'error';
                    setTimeout(() => {
                        this.saveStatus = 'idle';
                    }, 3000);
                }
            } catch (error) {
                this.saveStatus = 'error';
                setTimeout(() => {
                    this.saveStatus = 'idle';
                }, 3000);
            }
        }
    }
}

function userRoleManager() {
    return {
        role: '{{ $person->user?->role ?? "volunteer" }}',
        churchRoleId: {{ $person->user?->church_role_id ?? 'null' }},
        saving: false,
        saved: false,
        showCreateModal: false,
        showPasswordModal: false,
        generatedPassword: '',
        newEmail: '{{ $person->email ?? "" }}',
        newChurchRoleId: '',
        creating: false,
        createError: '',
        editingEmail: false,
        userEmail: '{{ $person->user?->email ?? "" }}',
        resettingPassword: false,

        copyPassword() {
            navigator.clipboard.writeText(this.generatedPassword);
            // Show brief confirmation
            const btn = document.getElementById('copyPasswordBtn');
            if (btn) {
                const originalText = btn.textContent;
                btn.textContent = 'Скопійовано!';
                setTimeout(() => btn.textContent = originalText, 1500);
            }
        },

        async resetPassword() {
            if (!confirm('Згенерувати новий пароль для цього користувача?')) return;

            this.resettingPassword = true;

            try {
                const response = await fetch('{{ route("people.reset-password", $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.generatedPassword = data.password;
                    this.showPasswordModal = true;
                } else {
                    alert(data.message || 'Помилка при скиданні пароля');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            } finally {
                this.resettingPassword = false;
            }
        },

        async updateEmail() {
            if (!this.userEmail.trim()) return;

            try {
                const response = await fetch('{{ route("people.update-email", $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: this.userEmail })
                });

                const data = await response.json();

                if (response.ok) {
                    this.editingEmail = false;
                } else {
                    alert(data.message || 'Помилка при оновленні email');
                    this.userEmail = '{{ $person->user?->email ?? "" }}';
                }
            } catch (error) {
                alert('Помилка з\'єднання');
                this.userEmail = '{{ $person->user?->email ?? "" }}';
            }
        },

        async updateRole() {
            this.saving = true;
            this.saved = false;

            try {
                const response = await fetch('{{ route("people.update-role", $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role: this.role })
                });

                if (response.ok) {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 2000);
                }
            } catch (error) {
                console.error('Error updating role:', error);
            } finally {
                this.saving = false;
            }
        },

        async createAccount() {
            this.creating = true;
            this.createError = '';

            try {
                const response = await fetch('{{ route("people.create-account", $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: this.newEmail,
                        church_role_id: this.newChurchRoleId
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showCreateModal = false;
                    this.generatedPassword = data.password;
                    this.showPasswordModal = true;
                    // Reload after showing password to update UI
                    this.userEmail = this.newEmail;
                } else {
                    this.createError = data.message || 'Помилка при створенні акаунту';
                }
            } catch (error) {
                this.createError = 'Помилка з\'єднання';
            } finally {
                this.creating = false;
            }
        },

        closePasswordModal() {
            this.showPasswordModal = false;
            this.generatedPassword = '';
            window.location.reload();
        }
    }
}

@if($church->shepherds_enabled)
function shepherdSearch() {
    @php
        $shepherdName = $person->shepherd?->full_name ?? '';
        $shepherdsList = $shepherds->sortBy('last_name')->filter(fn($s) => $s->id !== $person->id)->map(function($s) {
            return [
                'id' => $s->id,
                'full_name' => $s->full_name,
                'photo' => $s->photo ? Storage::url($s->photo) : null,
                'initials' => mb_substr($s->first_name, 0, 1) . mb_substr($s->last_name, 0, 1)
            ];
        })->values();
    @endphp
    return {
        searchQuery: '',
        selectedId: {{ $person->shepherd_id ?? 'null' }},
        selectedName: @json($shepherdName),
        isOpen: false,
        highlightedIndex: 0,
        updating: false,
        shepherds: @json($shepherdsList),
        get filteredShepherds() {
            if (!this.searchQuery) {
                return this.shepherds;
            }
            const query = this.searchQuery.toLowerCase();
            return this.shepherds.filter(s =>
                s.full_name.toLowerCase().includes(query)
            );
        },
        async selectShepherd(shepherd) {
            this.updating = true;
            try {
                const response = await fetch('{{ route('people.update-shepherd', $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ shepherd_id: shepherd.id })
                });
                const data = await response.json();
                if (data.success) {
                    this.selectedId = shepherd.id;
                    this.selectedName = shepherd.full_name;
                    this.searchQuery = '';
                    this.isOpen = false;
                } else {
                    alert(data.message || 'Помилка');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            } finally {
                this.updating = false;
            }
        },
        async clearShepherd() {
            this.updating = true;
            try {
                const response = await fetch('{{ route('people.update-shepherd', $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ shepherd_id: null })
                });
                const data = await response.json();
                if (data.success) {
                    this.selectedId = null;
                    this.selectedName = '';
                    this.searchQuery = '';
                    this.isOpen = false;
                } else {
                    alert(data.message || 'Помилка');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            } finally {
                this.updating = false;
            }
        },
        highlightNext() {
            if (this.highlightedIndex < this.filteredShepherds.length - 1) {
                this.highlightedIndex++;
            }
        },
        highlightPrev() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        selectHighlighted() {
            if (this.filteredShepherds.length > 0) {
                this.selectShepherd(this.filteredShepherds[this.highlightedIndex]);
            }
        }
    }
}
@endif

function avatarUpload() {
    return {
        preview: null,
        isDragging: false,
        existingPhoto: {{ $person->photo ? 'true' : 'false' }},

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.showPreview(file);
            }
        },

        showPreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.preview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removePhoto() {
            this.preview = null;
            this.existingPhoto = false;
            this.$refs.removePhotoInput.value = '1';
            const input = this.$el.querySelector('input[type="file"]');
            if (input) input.value = '';
        }
    }
}

@if($isAdmin)
function familyManager() {
    return {
        showAddModal: false,
        searchQuery: '',
        searchResults: [],
        selectedPerson: null,
        relationshipType: '',
        saving: false,
        error: '',
        familyMembers: @json($person->family_members->map(fn($m) => [
            'relationship_id' => $m->relationship_id,
            'person_id' => $m->person->id,
            'full_name' => $m->person->full_name,
            'first_name' => $m->person->first_name,
            'photo' => $m->person->photo ? Storage::url($m->person->photo) : null,
            'relationship_label' => $m->relationship_label,
        ])),

        async searchPeople() {
            try {
                const query = this.searchQuery.trim();
                const response = await fetch(`{{ route('family.search', $person) }}?q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    console.error('Search failed:', response.status);
                    return;
                }
                const data = await response.json();
                this.searchResults = data;
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        async loadInitialPeople() {
            await this.searchPeople();
        },

        selectPerson(person) {
            this.selectedPerson = person;
            this.searchQuery = person.name;
            this.showResults = false;
        },

        async addFamilyMember() {
            if (!this.selectedPerson || !this.relationshipType) return;

            this.saving = true;
            this.error = '';

            try {
                const response = await fetch('{{ route('family.store', $person) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        related_person_id: this.selectedPerson.id,
                        relationship_type: this.relationshipType
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.member) {
                        this.familyMembers.push(data.member);
                        this.showAddModal = false;
                        this.selectedPerson = null;
                        this.searchQuery = '';
                        this.relationshipType = '';
                        if (window.showGlobalToast) showGlobalToast('Члена сім\'ї додано', 'success');
                    }
                } else {
                    const data = await response.json();
                    this.error = data.error || data.message || 'Помилка при збереженні';
                }
            } catch (error) {
                this.error = 'Помилка з\'єднання';
            } finally {
                this.saving = false;
            }
        },

        async deleteRelationship(relationshipId) {
            if (!confirm('Видалити цей сімейний зв\'язок?')) return;

            try {
                const response = await fetch(`/family/${relationshipId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.familyMembers = this.familyMembers.filter(m => m.relationship_id !== relationshipId);
                    if (window.showGlobalToast) showGlobalToast('Зв\'язок видалено', 'success');
                }
            } catch (error) {
                console.error('Delete error:', error);
                if (window.showGlobalToast) showGlobalToast('Помилка видалення', 'error');
            }
        }
    }
}
@endif

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#6b7280';
    const gridColor = isDark ? '#374151' : '#f3f4f6';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(collect($attendanceChartData)->pluck('week')),
            datasets: [{
                label: 'Відвідування',
                data: @json(collect($attendanceChartData)->pluck('count')),
                backgroundColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}80',
                borderColor: '{{ $currentChurch->primary_color ?? "#3b82f6" }}',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
