@extends('layouts.app')

@section('title', $ministry->name . ' - Ресурси')

@section('content')
<div class="space-y-6" x-data="ministryResourcesManager()">
    <!-- Ministry Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($ministry->icon)
                    <span class="text-4xl">{{ $ministry->icon }}</span>
                    @else
                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                        <p class="text-gray-500 dark:text-gray-400">Ресурси команди</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'members']) }}"
               class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Команда
            </a>
            <a href="{{ route('ministries.show', ['ministry' => $ministry, 'tab' => 'schedule']) }}"
               class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Розклад
            </a>
            <a href="{{ route('ministries.resources', $ministry) }}"
               class="px-6 py-3 border-b-2 text-sm font-medium flex items-center gap-1 border-primary-600 dark:border-primary-400 text-primary-600 dark:text-primary-400 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                Ресурси
            </a>
        </div>
    </div>

    <!-- Resources Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('ministries.resources', $ministry) }}"
               class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                Корінь
            </a>
            @foreach($breadcrumbs as $crumb)
            <span class="text-gray-400">/</span>
            @if($loop->last)
            <span class="text-gray-900 dark:text-white font-medium">{{ $crumb->icon }} {{ $crumb->name }}</span>
            @else
            <a href="{{ route('ministries.resources.folder', [$ministry, $crumb]) }}"
               class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                {{ $crumb->icon }} {{ $crumb->name }}
            </a>
            @endif
            @endforeach
        </nav>

        @can('contribute-ministry', $ministry)
        <div class="flex items-center gap-2">
            <!-- Create folder button -->
            <button @click="showCreateFolder = true"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Нова папка
            </button>

            <!-- Upload button -->
            <label class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Завантажити
                <input type="file" class="hidden" @change="uploadFile($event)" multiple>
            </label>
        </div>
        @endcan
    </div>

    <!-- Storage warning (only show when approaching limit) -->
    @if($storagePercent > 70)
    <div class="rounded-xl p-4 border {{ $storagePercent > 90 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' }}">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 {{ $storagePercent > 90 ? 'text-red-500' : 'text-yellow-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium {{ $storagePercent > 90 ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }}">
                    Використано {{ number_format($storageUsed / 1024 / 1024, 1) }} MB
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Resources grid -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($resources->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Папка порожня</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Створіть папку або завантажте файли</p>
            @can('contribute-ministry', $ministry)
            <div class="flex items-center justify-center gap-3">
                <button @click="showCreateFolder = true"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Створити папку
                </button>
                <label class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors cursor-pointer">
                    Завантажити файл
                    <input type="file" class="hidden" @change="uploadFile($event)" multiple>
                </label>
            </div>
            @endcan
        </div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Назва</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Розмір</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Дата</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($resources as $resource)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer group"
                    @if($resource->isFolder())
                    @click="window.location.href='{{ route('ministries.resources.folder', [$ministry, $resource]) }}'"
                    @else
                    @click="showPreview({{ json_encode([
                        'id' => $resource->id,
                        'name' => $resource->name,
                        'icon' => $resource->icon,
                        'size' => $resource->formatted_size,
                        'mime' => $resource->mime_type,
                        'url' => Storage::url($resource->file_path),
                        'downloadUrl' => route('resources.download', $resource),
                        'createdAt' => $resource->created_at->format('d.m.Y H:i'),
                        'creator' => $resource->creator?->name,
                    ]) }})"
                    @endif>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($resource->isFolder())
                            <svg class="w-5 h-5 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                            </svg>
                            @elseif($resource->mime_type && str_starts_with($resource->mime_type, 'image/'))
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            @elseif($resource->mime_type && str_starts_with($resource->mime_type, 'video/'))
                            <svg class="w-5 h-5 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            @elseif($resource->mime_type && str_starts_with($resource->mime_type, 'audio/'))
                            <svg class="w-5 h-5 text-pink-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                            @elseif($resource->mime_type === 'application/pdf')
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $resource->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                        {{ $resource->isFile() ? $resource->formatted_size : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                        {{ $resource->created_at->format('d.m.Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('contribute-ministry', $ministry)
                        <button @click.stop="openMenu({{ $resource->id }}, '{{ addslashes($resource->name) }}', $event)"
                                class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    <!-- Create folder modal -->
    @can('contribute-ministry', $ministry)
    <div x-show="showCreateFolder" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateFolder = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Нова папка</h3>
                <form method="POST" action="{{ route('ministries.resources.folder.create', $ministry) }}">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $folder?->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Назва</label>
                            <input type="text" name="name" required autofocus
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                                   placeholder="Назва папки...">
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                        <button type="button" @click="showCreateFolder = false"
                                class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                            Скасувати
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                            Створити
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Context menu -->
    <div x-show="menuOpen" x-cloak
         :style="`top: ${menuY}px; left: ${menuX}px`"
         @click.away="menuOpen = false"
         class="fixed z-50 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-48">
        <button @click="showRenameModal()" class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Перейменувати
        </button>
        <button @click="deleteItem()" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Видалити
        </button>
    </div>

    <!-- Rename modal -->
    <div x-show="showRename" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showRename = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Перейменувати</h3>
                <form @submit.prevent="submitRename()">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Нова назва</label>
                        <input type="text" x-model="renameName" required x-ref="renameInput"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white"
                               placeholder="Назва...">
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                        <button type="button" @click="showRename = false"
                                class="w-full sm:w-auto px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900">
                            Скасувати
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                            Зберегти
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- File preview modal -->
    <div x-show="previewFile" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="min-h-screen px-4 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/70" @click="previewFile = null"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden">
                <!-- Preview header -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl" x-text="previewFile?.icon"></span>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white" x-text="previewFile?.name"></h3>
                            <p class="text-sm text-gray-500" x-text="previewFile?.size"></p>
                        </div>
                    </div>
                    <button @click="previewFile = null" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Preview content -->
                <div class="p-4">
                    <template x-if="previewFile?.mime?.startsWith('image/')">
                        <img :src="previewFile?.url" class="max-h-96 mx-auto rounded-lg">
                    </template>
                    <template x-if="previewFile?.mime?.startsWith('audio/')">
                        <audio :src="previewFile?.url" controls class="w-full"></audio>
                    </template>
                    <template x-if="previewFile?.mime?.startsWith('video/')">
                        <video :src="previewFile?.url" controls class="max-h-96 mx-auto rounded-lg"></video>
                    </template>
                    <template x-if="previewFile?.mime === 'application/pdf'">
                        <iframe :src="previewFile?.url" class="w-full h-96 rounded-lg"></iframe>
                    </template>
                    <template x-if="!previewFile?.mime?.startsWith('image/') && !previewFile?.mime?.startsWith('audio/') && !previewFile?.mime?.startsWith('video/') && previewFile?.mime !== 'application/pdf'">
                        <div class="text-center py-8">
                            <span class="text-6xl block mb-4" x-text="previewFile?.icon"></span>
                            <p class="text-gray-500 dark:text-gray-400">Попередній перегляд недоступний</p>
                        </div>
                    </template>
                </div>

                <!-- Preview footer -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Завантажено: <span x-text="previewFile?.createdAt"></span>
                            <span x-show="previewFile?.creator">від <span x-text="previewFile?.creator"></span></span>
                        </div>
                        <a :href="previewFile?.downloadUrl"
                           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Завантажити
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden forms for actions -->
    <form id="renameForm" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="name" id="renameInput">
    </form>

    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
function ministryResourcesManager() {
    return {
        showCreateFolder: false,
        showRename: false,
        renameName: '',
        menuOpen: false,
        menuX: 0,
        menuY: 0,
        selectedId: null,
        selectedName: '',
        previewFile: null,

        openMenu(id, name, event) {
            event.stopPropagation();
            this.selectedId = id;
            this.selectedName = name;
            this.menuX = Math.min(event.clientX, window.innerWidth - 200);
            this.menuY = Math.min(event.clientY, window.innerHeight - 100);
            this.menuOpen = true;
        },

        showRenameModal() {
            this.menuOpen = false;
            this.renameName = this.selectedName;
            this.showRename = true;
            this.$nextTick(() => this.$refs.renameInput.focus());
        },

        submitRename() {
            if (!this.renameName.trim()) return;
            const form = document.getElementById('renameForm');
            form.action = `/resources/${this.selectedId}/rename`;
            document.getElementById('renameInput').value = this.renameName;
            form.submit();
        },

        showPreview(file) {
            this.previewFile = file;
        },

        async uploadFile(event) {
            const files = event.target.files;
            if (!files.length) return;

            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parent_id', '{{ $folder?->id ?? "" }}');
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route("ministries.resources.upload", $ministry) }}', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const data = await response.json().catch(() => ({}));
                        alert(data.message || 'Помилка завантаження');
                    }
                } catch (error) {
                    alert('Помилка завантаження');
                }
            }

            event.target.value = '';
        },

        deleteItem() {
            this.menuOpen = false;
            if (!confirm('{{ __('messages.confirm_delete_item') }}')) return;

            const form = document.getElementById('deleteForm');
            form.action = `/resources/${this.selectedId}`;
            form.submit();
        }
    }
}
</script>
@endpush
@endsection
