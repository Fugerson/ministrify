@extends('layouts.app')

@section('title', $gallery->title)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('website-builder.gallery.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $gallery->title }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $photos->count() }} {{ trans_choice('фото|фото|фото', $photos->count()) }}
                    @if($gallery->event_date)
                        &middot; {{ $gallery->event_date->format('d.m.Y') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('website-builder.gallery.edit', $gallery) }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редагувати
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($gallery->description)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-gray-700 dark:text-gray-300">{{ $gallery->description }}</p>
        </div>
    @endif

    <!-- Upload photos -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
         x-data="{ uploading: false }">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Завантажити фото</h3>
        <form action="{{ route('website-builder.gallery.photos.upload', $gallery) }}" method="POST" enctype="multipart/form-data"
              @submit="uploading = true">
            @csrf
            <div class="flex items-center gap-4">
                <label class="flex-1 flex items-center justify-center px-4 py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                    <div class="text-center">
                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Натисніть для вибору фото (до 50 файлів)</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 block mt-1">JPG, PNG, GIF, WebP, HEIC &middot; до 5MB кожне</span>
                    </div>
                    <input type="file" name="photos[]" multiple accept="image/*,.heic,.heif" class="hidden">
                </label>
            </div>
            @error('photos')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
            @error('photos.*')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50"
                        :disabled="uploading">
                    <span x-show="!uploading">Завантажити</span>
                    <span x-show="uploading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Завантаження...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Photos grid -->
    @if($photos->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Фото ще немає</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Завантажте перші фото в альбом</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($photos as $photo)
                <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="aspect-square">
                        <img src="{{ Storage::url($photo->file_path) }}"
                             alt="{{ $photo->alt_text ?? $gallery->title }}"
                             class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                             @click="$dispatch('open-lightbox', '{{ Storage::url($photo->file_path) }}')"
                             loading="lazy">
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                        <form action="{{ route('website-builder.gallery.photos.delete', $photo) }}" method="POST"
                              onsubmit="return confirm('{{ __('messages.confirm_delete_photo') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 bg-red-500/80 hover:bg-red-600 text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @if($photo->caption)
                        <div class="p-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $photo->caption }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
