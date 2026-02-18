@extends('layouts.app')

@section('title', 'Свідчення')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showModal: false, editingTestimonial: null }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('website-builder.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Свідчення</h1>
                <p class="text-gray-600 dark:text-gray-400">Історії членів церкви</p>
            </div>
        </div>
        <button @click="showModal = true; editingTestimonial = null" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Додати свідчення
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($testimonials->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Свідчень ще немає</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Додайте історії членів церкви</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($testimonials as $testimonial)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 overflow-hidden">
                            @if($testimonial->photo)
                                <img src="{{ Storage::url($testimonial->photo) }}" alt="{{ $testimonial->author_name }}" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $testimonial->author_name }}</p>
                            @if($testimonial->author_role)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $testimonial->author_role }}</p>
                            @endif
                        </div>
                    </div>

                    <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-4">"{{ $testimonial->content }}"</p>

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $testimonial->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $testimonial->is_public ? 'Публічне' : 'Приховане' }}
                        </span>
                        <div class="flex gap-1">
                            <button type="button" @click="editingTestimonial = @json($testimonial); showModal = true" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form action="{{ route('website-builder.testimonials.destroy', $testimonial) }}" method="POST" onsubmit="return confirm('Видалити це свідчення?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" x-text="editingTestimonial ? 'Редагувати свідчення' : 'Додати свідчення'"></h3>
                <form :action="editingTestimonial ? '{{ url('website-builder/testimonials') }}/' + editingTestimonial.id : '{{ route('website-builder.testimonials.store') }}'" method="POST" enctype="multipart/form-data">
                    @csrf
                    <template x-if="editingTestimonial">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ім'я *</label>
                                <input type="text" name="author_name" :value="editingTestimonial?.author_name || ''" required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Роль</label>
                                <input type="text" name="author_role" :value="editingTestimonial?.author_role || ''" placeholder="Член церкви"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Свідчення *</label>
                            <textarea name="content" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" x-text="editingTestimonial?.content || ''"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фото</label>
                            <div x-data="{ fileName: '' }" class="relative">
                                <input type="file" name="photo" accept="image/*,.heic,.heif" class="sr-only" x-ref="photoInput" @change="fileName = $event.target.files[0]?.name || ''">
                                <label @click="$refs.photoInput.click()" class="flex items-center gap-3 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-all group">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors">
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p x-show="!fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300">Обрати фото</p>
                                        <p x-show="fileName" x-text="fileName" class="text-sm font-medium text-primary-600 dark:text-primary-400 truncate"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" :checked="editingTestimonial?.is_public ?? true"
                                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Показувати на сайті</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Скасувати
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                            Зберегти
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
