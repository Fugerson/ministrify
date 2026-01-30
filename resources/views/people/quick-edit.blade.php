@extends('layouts.app')

@section('title', 'Швидке редагування')

@section('content')
<div x-data="quickEdit()" class="space-y-4">
    <!-- Minimal Header -->
    <div class="flex items-center justify-between mb-2">
        <a href="{{ route('people.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Швидке редагування
        </a>
    </div>

    <!-- FLOATING TOOLBAR - Always visible at bottom, respects sidebar -->
    <div class="fixed bottom-0 left-0 right-0 lg:left-64 z-20 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl px-4 py-3">
        <div class="max-w-screen-2xl mx-auto flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="w-full sm:w-48">
                <input type="text" x-model="searchQuery" placeholder="Пошук..."
                       class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-primary-500/20 dark:text-white">
            </div>

            <!-- Filters -->
            <select x-model="filterMinistry" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                <option value="">Команда</option>
                @foreach($ministries as $ministry)
                <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                @endforeach
            </select>
            <select x-model="filterStatus" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                <option value="">Статус</option>
                <option value="guest">Гість</option>
                <option value="newcomer">Новоприбулий</option>
                <option value="member">Член церкви</option>
                <option value="active">Активний</option>
            </select>
            <select x-model="filterGender" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm dark:text-white">
                <option value="">Стать</option>
                <option value="male">Ч</option>
                <option value="female">Ж</option>
            </select>

            <button @click="clearFilters()" x-show="hasFilters" x-cloak class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                ✕
            </button>

            <!-- Divider -->
            <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            <!-- Add new -->
            <button @click="addNewRow()" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Новий
            </button>

            <!-- Selection actions -->
            <template x-if="selectedCount > 0">
                <div class="flex items-center gap-2 ml-2 pl-3 border-l border-gray-300 dark:border-gray-600">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Вибрано: <span x-text="selectedCount" class="font-bold text-primary-600"></span>
                    </span>
                    <button @click="bulkGrantAccess()" class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Доступ
                    </button>
                    <button @click="bulkDelete()" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Видалити
                    </button>
                    <button @click="clearSelection()" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Save -->
            <span x-show="hasChanges" class="text-sm text-amber-600 dark:text-amber-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="10" r="5"/>
                </svg>
                Зміни
            </span>
            <button @click="saveAll()" :disabled="saving || !hasChanges"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white rounded-lg font-medium text-sm">
                <svg x-show="!saving" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="saving" class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="saving ? 'Зберігаю...' : 'Зберегти'"></span>
            </button>
        </div>
    </div>

    <!-- Scroll hint (mobile only) -->
    <div class="sm:hidden bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-2.5 text-sm text-blue-700 dark:text-blue-300 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
        </svg>
        Прокрутіть вправо для перегляду всіх колонок
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden relative mb-20" style="z-index: 0;">
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-180px)]" style="min-height: 400px;">
            <table class="w-full text-sm" style="min-width: 2800px;">
                <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-20">
                    <tr>
                        <!-- Checkbox -->
                        <th class="px-2 py-3 w-10 sticky left-0 bg-gray-100 dark:bg-gray-700 z-30 border-b border-gray-200 dark:border-gray-600">
                            <input type="checkbox" @change="toggleSelectAll($event)" :checked="allSelected" :indeterminate.prop="someSelected"
                                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                        </th>
                        <!-- Row Number -->
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-10 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">#</th>
                        <!-- Photo -->
                        <th class="px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-14 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">Фото</th>
                        <!-- Sortable columns -->
                        <template x-for="(col, colIndex) in columns" :key="col.key">
                            <th class="relative px-2 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase select-none bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 group"
                                :style="{ width: col.width, minWidth: '60px' }">
                                <div class="flex items-center gap-1 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200" @click="toggleSort(col.key)">
                                    <span x-text="col.label"></span>
                                    <template x-if="sortKey === col.key">
                                        <svg class="w-3 h-3" :class="{'rotate-180': sortDir === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </template>
                                </div>
                                <!-- Resize handle -->
                                <div class="absolute top-0 right-0 w-2 h-full cursor-col-resize flex items-center justify-center opacity-0 group-hover:opacity-100 hover:opacity-100 transition-opacity"
                                     @mousedown.stop="initResize($event, colIndex)">
                                    <div class="w-0.5 h-4 bg-gray-400 dark:bg-gray-500 rounded-full"></div>
                                </div>
                            </th>
                        </template>
                        <!-- Actions -->
                        <th class="px-2 py-3 w-10 sticky right-0 bg-gray-100 dark:bg-gray-700 z-30 border-b border-gray-200 dark:border-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(row, index) in filteredRows" :key="row.id || row.tempId">
                        <tr :class="{'bg-green-50 dark:bg-green-900/20': row.isNew, 'bg-amber-50 dark:bg-amber-900/20': row.isDirty && !row.isNew, 'bg-primary-50 dark:bg-primary-900/20': row.selected}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <!-- Checkbox -->
                            <td class="px-2 py-1 sticky left-0 bg-white dark:bg-gray-800 z-10" :class="{'bg-green-50 dark:bg-green-900/20': row.isNew, 'bg-amber-50 dark:bg-amber-900/20': row.isDirty && !row.isNew, 'bg-primary-50 dark:bg-primary-900/20': row.selected}">
                                <input type="checkbox" x-model="row.selected"
                                       class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            </td>
                            <!-- Row Number -->
                            <td class="px-2 py-1 text-gray-400 text-xs" x-text="index + 1"></td>

                            <!-- Photo -->
                            <td class="px-1 py-1">
                                <div class="relative group/photo" x-data="{ fileInput: null }">
                                    <template x-if="row.photo_url">
                                        <div class="relative group/avatar">
                                            <img :src="row.photo_url" class="w-10 h-10 rounded-full object-cover cursor-pointer" @click="fileInput.click()">
                                            <button @click.stop="deletePhoto(row)" type="button" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full opacity-0 group-hover/photo:opacity-100 transition-opacity flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            <div class="invisible group-hover/avatar:visible absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 pointer-events-none transition-all duration-150">
                                                <img :src="row.photo_url" class="w-32 h-32 rounded-xl object-cover shadow-xl ring-2 ring-white dark:ring-gray-800">
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!row.photo_url">
                                        <div @click="fileInput.click()" class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <input type="file" x-ref="fileInput" x-init="fileInput = $refs.fileInput" @change="uploadPhoto(row, $event)" accept="image/*" class="hidden">
                                    <div x-show="row.uploadingPhoto" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full">
                                        <svg class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </td>

                            <!-- First Name -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.first_name" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Ім'я">
                            </td>

                            <!-- Last Name -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.last_name" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Прізвище">
                            </td>

                            <!-- Phone -->
                            <td class="px-1 py-1">
                                <input type="tel" x-model="row.phone" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="+380...">
                            </td>

                            <!-- Email -->
                            <td class="px-1 py-1">
                                <input type="email" x-model="row.email" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="email@...">
                            </td>

                            <!-- Telegram -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.telegram_username" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="@username">
                            </td>

                            <!-- Birth Date -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.birth_date" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Gender -->
                            <td class="px-1 py-1">
                                <select x-model="row.gender" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    <option value="male">Ч</option>
                                    <option value="female">Ж</option>
                                </select>
                            </td>

                            <!-- Marital Status -->
                            <td class="px-1 py-1">
                                <select x-model="row.marital_status" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    <option value="single">Неодруж.</option>
                                    <option value="married">Одруж.</option>
                                    <option value="widowed">Вдівець</option>
                                    <option value="divorced">Розлуч.</option>
                                </select>
                            </td>

                            <!-- Membership Status -->
                            <td class="px-1 py-1">
                                <select x-model="row.membership_status" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    <option value="guest">Гість</option>
                                    <option value="newcomer">Новоприб.</option>
                                    <option value="member">Член</option>
                                    <option value="active">Активний</option>
                                </select>
                            </td>

                            <!-- Church Role -->
                            <td class="px-1 py-1">
                                <select x-model="row.church_role" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    <option value="member">Член церкви</option>
                                    <option value="servant">Служитель</option>
                                    <option value="deacon">Диякон</option>
                                    <option value="presbyter">Пресвітер</option>
                                    <option value="pastor">Пастор</option>
                                </select>
                            </td>

                            <!-- Ministry -->
                            <td class="px-1 py-1">
                                <select x-model="row.ministry_id" @change="markDirty(row)"
                                        class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                                    <option value="">—</option>
                                    @foreach($ministries as $ministry)
                                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- Address -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.address" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Адреса">
                            </td>

                            <!-- First Visit Date -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.first_visit_date" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Joined Date -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.joined_date" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Baptism Date -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.baptism_date" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Anniversary -->
                            <td class="px-1 py-1">
                                <input type="date" x-model="row.anniversary" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors">
                            </td>

                            <!-- Notes -->
                            <td class="px-1 py-1">
                                <input type="text" x-model="row.notes" @input="markDirty(row)"
                                       class="w-full px-2 py-1.5 bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-white dark:focus:bg-gray-700 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 focus:border-primary-500 rounded text-sm transition-colors"
                                       placeholder="Нотатки">
                            </td>

                            <!-- Actions -->
                            <td class="px-2 py-1 sticky right-0 bg-white dark:bg-gray-800 z-10" :class="{'bg-green-50 dark:bg-green-900/20': row.isNew, 'bg-amber-50 dark:bg-amber-900/20': row.isDirty && !row.isNew, 'bg-primary-50 dark:bg-primary-900/20': row.selected}">
                                <button @click="deleteRow(row)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div x-show="filteredRows.length === 0" class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Немає даних для відображення</p>
            <button @click="addNewRow()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Додати першу людину
            </button>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Всього: <span x-text="rows.length"></span> |
                Показано: <span x-text="filteredRows.length"></span> |
                Нових: <span x-text="rows.filter(r => r.isNew).length"></span> |
                Змінено: <span x-text="rows.filter(r => r.isDirty && !r.isNew).length"></span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Tab</kbd>
                    <span class="text-xs text-gray-500 dark:text-gray-400">перехід</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Shift</kbd>
                    <span class="text-xs text-gray-500 dark:text-gray-400">+ клік для діапазону</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.away="showDeleteModal = false">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Підтвердіть видалення</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Ви впевнені, що хочете видалити <span x-text="deleteCount" class="font-semibold"></span> <span x-text="deleteCount === 1 ? 'людину' : 'людей'"></span>?
                Ця дія незворотня.
            </p>
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Скасувати
                </button>
                <button @click="confirmDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Видалити
                </button>
            </div>
        </div>
    </div>

    <!-- Grant Access Modal -->
    <div x-show="showGrantAccessModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.away="showGrantAccessModal = false">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Надати доступ до системи</h3>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Рівень доступу</label>
                    <select x-model="grantAccessRoleId" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-purple-500 dark:text-white">
                        <option value="">Оберіть роль...</option>
                        @foreach($churchRoles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}@if($role->is_admin_role) (Повний доступ)@endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-xl text-sm">
                    <p class="text-gray-700 dark:text-gray-300">
                        Вибрано: <span x-text="grantAccessCount" class="font-semibold"></span> людей
                    </p>
                    <p x-show="grantAccessNoEmail > 0" class="text-amber-600 dark:text-amber-400 mt-1">
                        <span x-text="grantAccessNoEmail"></span> без email (буде пропущено)
                    </p>
                    <p x-show="grantAccessAlreadyHave > 0" class="text-gray-500 dark:text-gray-400 mt-1">
                        <span x-text="grantAccessAlreadyHave"></span> вже мають доступ (буде пропущено)
                    </p>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Користувачі отримають лист з посиланням для встановлення пароля.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button @click="showGrantAccessModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Скасувати
                </button>
                <button @click="confirmGrantAccess()" :disabled="!grantAccessRoleId || grantAccessLoading || grantAccessEligible === 0"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white rounded-lg transition-colors">
                    <span x-show="!grantAccessLoading">Надати доступ</span>
                    <span x-show="grantAccessLoading">Зачекайте...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div x-show="showToast" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50">
        <div class="bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span x-text="toastMessage"></span>
        </div>
    </div>
</div>

<script>
function quickEdit() {
    return {
        rows: @json($rows),
        searchQuery: '',
        filterMinistry: '',
        filterStatus: '',
        filterGender: '',
        saving: false,
        showToast: false,
        toastMessage: '',
        tempIdCounter: 0,
        sortKey: 'last_name',
        sortDir: 'asc',
        showDeleteModal: false,
        deleteCount: 0,
        pendingDeleteRows: [],
        showGrantAccessModal: false,
        grantAccessRoleId: '',
        grantAccessCount: 0,
        grantAccessNoEmail: 0,
        grantAccessAlreadyHave: 0,
        grantAccessEligible: 0,
        grantAccessLoading: false,
        pendingGrantAccessRows: [],
        lastSelectedIndex: null,
        resizing: false,
        resizeColIndex: null,
        resizeStartX: 0,
        resizeStartWidth: 0,

        columns: [
            { key: 'first_name', label: "Ім'я", width: '160px' },
            { key: 'last_name', label: 'Прізвище', width: '160px' },
            { key: 'phone', label: 'Телефон', width: '160px' },
            { key: 'email', label: 'Email', width: '220px' },
            { key: 'telegram_username', label: 'Telegram', width: '140px' },
            { key: 'birth_date', label: 'Народження', width: '140px' },
            { key: 'gender', label: 'Стать', width: '90px' },
            { key: 'marital_status', label: 'Сімейний стан', width: '130px' },
            { key: 'membership_status', label: 'Статус', width: '120px' },
            { key: 'church_role', label: 'Роль', width: '140px' },
            { key: 'ministry_id', label: 'Команда', width: '180px' },
            { key: 'address', label: 'Адреса', width: '220px' },
            { key: 'first_visit_date', label: 'Перший візит', width: '140px' },
            { key: 'joined_date', label: 'Приєднався', width: '140px' },
            { key: 'baptism_date', label: 'Хрещення', width: '140px' },
            { key: 'anniversary', label: 'Річниця', width: '140px' },
            { key: 'notes', label: 'Нотатки', width: '250px' },
        ],

        get filteredRows() {
            let result = this.rows.filter(row => {
                if (row.isDeleted) return false;

                // Search filter
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    const searchText = [
                        row.first_name, row.last_name, row.phone, row.email,
                        row.telegram_username, row.address, row.notes
                    ].filter(Boolean).join(' ').toLowerCase();
                    if (!searchText.includes(query)) return false;
                }

                // Ministry filter
                if (this.filterMinistry && row.ministry_id != this.filterMinistry) {
                    return false;
                }

                // Status filter
                if (this.filterStatus && row.membership_status !== this.filterStatus) {
                    return false;
                }

                // Gender filter
                if (this.filterGender && row.gender !== this.filterGender) {
                    return false;
                }

                return true;
            });

            // Sort
            if (this.sortKey) {
                result.sort((a, b) => {
                    let aVal = a[this.sortKey] || '';
                    let bVal = b[this.sortKey] || '';

                    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                    if (typeof bVal === 'string') bVal = bVal.toLowerCase();

                    if (aVal < bVal) return this.sortDir === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            return result;
        },

        get hasChanges() {
            return this.rows.some(r => r.isDirty || r.isNew || r.isDeleted);
        },

        get hasFilters() {
            return this.searchQuery || this.filterMinistry || this.filterStatus || this.filterGender;
        },

        get selectedCount() {
            return this.rows.filter(r => r.selected && !r.isDeleted).length;
        },

        get allSelected() {
            const visible = this.filteredRows;
            return visible.length > 0 && visible.every(r => r.selected);
        },

        get someSelected() {
            const visible = this.filteredRows;
            return visible.some(r => r.selected) && !this.allSelected;
        },

        toggleSort(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
        },

        clearFilters() {
            this.searchQuery = '';
            this.filterMinistry = '';
            this.filterStatus = '';
            this.filterGender = '';
        },

        toggleSelectAll(event) {
            const checked = event.target.checked;
            this.filteredRows.forEach(row => row.selected = checked);
        },

        clearSelection() {
            this.rows.forEach(row => row.selected = false);
        },

        markDirty(row) {
            row.isDirty = true;
        },

        addNewRow() {
            this.tempIdCounter++;
            this.rows.unshift({
                tempId: 'new_' + this.tempIdCounter,
                first_name: '',
                last_name: '',
                phone: '',
                email: '',
                telegram_username: '',
                birth_date: '',
                gender: '',
                marital_status: '',
                membership_status: '',
                church_role: '',
                ministry_id: '',
                address: '',
                first_visit_date: '',
                joined_date: '',
                baptism_date: '',
                anniversary: '',
                notes: '',
                photo_url: null,
                uploadingPhoto: false,
                isDirty: false,
                isNew: true,
                isDeleted: false,
                selected: false,
            });

            // Focus on first input of new row
            this.$nextTick(() => {
                const firstInput = this.$el.querySelector('tbody tr:first-child input[type="text"]');
                if (firstInput) firstInput.focus();
            });
        },

        deleteRow(row) {
            this.pendingDeleteRows = [row];
            this.deleteCount = 1;
            this.showDeleteModal = true;
        },

        bulkDelete() {
            const selected = this.rows.filter(r => r.selected && !r.isDeleted);
            if (selected.length === 0) return;

            this.pendingDeleteRows = selected;
            this.deleteCount = selected.length;
            this.showDeleteModal = true;
        },

        confirmDelete() {
            this.pendingDeleteRows.forEach(row => {
                if (row.isNew) {
                    const index = this.rows.indexOf(row);
                    if (index > -1) this.rows.splice(index, 1);
                } else {
                    row.isDeleted = true;
                    row.isDirty = true;
                    row.selected = false;
                }
            });

            this.showDeleteModal = false;
            this.pendingDeleteRows = [];
        },

        bulkGrantAccess() {
            const selected = this.rows.filter(r => r.selected && !r.isDeleted && !r.isNew);
            if (selected.length === 0) return;

            this.pendingGrantAccessRows = selected;
            this.grantAccessCount = selected.length;
            this.grantAccessNoEmail = selected.filter(r => !r.email).length;
            this.grantAccessAlreadyHave = selected.filter(r => r.user_id).length;
            this.grantAccessEligible = selected.filter(r => r.email && !r.user_id).length;
            this.grantAccessRoleId = '';
            this.showGrantAccessModal = true;
        },

        async confirmGrantAccess() {
            if (!this.grantAccessRoleId || this.grantAccessEligible === 0) return;

            this.grantAccessLoading = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Get only eligible IDs (have email, no user_id)
            const eligibleIds = this.pendingGrantAccessRows
                .filter(r => r.email && !r.user_id)
                .map(r => r.id);

            try {
                const response = await fetch('{{ route("people.bulk-action") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'grant_access',
                        ids: eligibleIds,
                        church_role_id: this.grantAccessRoleId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showGrantAccessModal = false;
                    this.clearSelection();
                    this.toastMessage = data.message;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 5000);

                    // Update user_id for granted rows
                    eligibleIds.forEach(id => {
                        const row = this.rows.find(r => r.id === id);
                        if (row) row.user_id = true; // Mark as having access
                    });
                } else {
                    alert(data.message || 'Сталася помилка');
                }
            } catch (error) {
                console.error('Grant access error:', error);
                alert('Сталася помилка при наданні доступу');
            } finally {
                this.grantAccessLoading = false;
            }
        },

        async saveAll() {
            this.saving = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Prepare data
            const toCreate = this.rows.filter(r => r.isNew && !r.isDeleted && (r.first_name || r.last_name));
            const toUpdate = this.rows.filter(r => r.isDirty && !r.isNew && !r.isDeleted && r.id);
            const toDelete = this.rows.filter(r => r.isDeleted && r.id);

            const mapRow = r => ({
                id: r.id,
                first_name: r.first_name,
                last_name: r.last_name,
                phone: r.phone,
                email: r.email,
                telegram_username: r.telegram_username || null,
                birth_date: r.birth_date || null,
                gender: r.gender || null,
                marital_status: r.marital_status || null,
                membership_status: r.membership_status || null,
                church_role: r.church_role || null,
                ministry_id: r.ministry_id || null,
                address: r.address || null,
                first_visit_date: r.first_visit_date || null,
                joined_date: r.joined_date || null,
                baptism_date: r.baptism_date || null,
                anniversary: r.anniversary || null,
                notes: r.notes || null,
            });

            try {
                const response = await fetch('{{ route("people.quick-save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        create: toCreate.map(mapRow),
                        update: toUpdate.map(mapRow),
                        delete: toDelete.map(r => r.id),
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update created rows with real IDs
                    if (data.created) {
                        data.created.forEach((created, index) => {
                            const row = toCreate[index];
                            if (row) {
                                row.id = created.id;
                                row.isNew = false;
                                row.isDirty = false;
                                delete row.tempId;
                            }
                        });
                    }

                    // Clear dirty flags for updated rows
                    toUpdate.forEach(r => r.isDirty = false);

                    // Remove deleted rows
                    this.rows = this.rows.filter(r => !r.isDeleted);

                    this.toast(`Збережено! Створено: ${data.stats.created}, Оновлено: ${data.stats.updated}, Видалено: ${data.stats.deleted}`);
                } else {
                    alert(data.message || 'Помилка збереження');
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Помилка збереження');
            } finally {
                this.saving = false;
            }
        },

        async uploadPhoto(row, event) {
            const file = event.target.files[0];
            if (!file) return;

            // Only allow for saved rows
            if (!row.id) {
                this.toast('Спочатку збережіть запис, потім додайте фото');
                event.target.value = '';
                return;
            }

            row.uploadingPhoto = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const formData = new FormData();
            formData.append('photo', file);

            try {
                const response = await fetch(`/people/${row.id}/upload-photo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    row.photo_url = data.photo_url;
                    this.toast('Фото завантажено');
                } else {
                    alert(data.message || 'Помилка завантаження фото');
                }
            } catch (error) {
                console.error('Photo upload error:', error);
                alert('Помилка завантаження фото');
            } finally {
                row.uploadingPhoto = false;
                event.target.value = '';
            }
        },

        async deletePhoto(row) {
            if (!row.id || !row.photo_url) return;

            if (!confirm('Видалити фото?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch(`/people/${row.id}/delete-photo`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    row.photo_url = null;
                    this.toast('Фото видалено');
                } else {
                    alert(data.message || 'Помилка видалення');
                }
            } catch (error) {
                console.error('Photo delete error:', error);
                alert('Помилка видалення');
            }
        },

        // Column resizing
        initResize(event, colIndex) {
            event.preventDefault();
            this.resizing = true;
            this.resizeColIndex = colIndex;
            this.resizeStartX = event.pageX;
            this.resizeStartWidth = parseInt(this.columns[colIndex].width);

            document.addEventListener('mousemove', this.doResize.bind(this));
            document.addEventListener('mouseup', this.stopResize.bind(this));
        },

        doResize(event) {
            if (!this.resizing) return;
            const diff = event.pageX - this.resizeStartX;
            const newWidth = Math.max(60, this.resizeStartWidth + diff);
            this.columns[this.resizeColIndex].width = newWidth + 'px';
        },

        stopResize() {
            this.resizing = false;
            document.removeEventListener('mousemove', this.doResize);
            document.removeEventListener('mouseup', this.stopResize);
        },

        toast(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 4000);
        }
    };
}
</script>
@endsection
