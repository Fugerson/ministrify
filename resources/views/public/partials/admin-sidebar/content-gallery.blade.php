{{-- Gallery section inline editor --}}
<div class="space-y-2">
    {{-- Loading --}}
    <div x-show="cnt.gallery.loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- List view --}}
    <div x-show="!cnt.gallery.loading && cnt.gallery.editing === null">
        <button x-on:click="contentNew('gallery', { title: '', description: '', is_public: true })"
                class="w-full mb-2 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
            + Створити альбом
        </button>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
            <template x-for="item in cnt.gallery.items" :key="item.id">
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <template x-if="item.cover_photo">
                        <img :src="'/storage/' + item.cover_photo" class="w-10 h-10 rounded object-cover flex-shrink-0">
                    </template>
                    <template x-if="!item.cover_photo">
                        <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </template>
                    <div class="flex-1 min-w-0">
                        <p x-text="item.title" class="text-xs font-medium text-gray-800 truncate"></p>
                        <p class="text-[10px] text-gray-400"><span x-text="item.photos_count || 0"></span> фото</p>
                        <div class="flex gap-2 mt-0.5">
                            <button x-on:click="contentEdit('gallery', item, ['title', 'description', 'is_public'])" class="text-[10px] text-blue-500 hover:text-blue-700">Ред.</button>
                            <button x-on:click="contentDeleteItem('gallery', item.id)" class="text-[10px] text-red-500 hover:text-red-700">Вид.</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <p x-show="cnt.gallery.loaded && cnt.gallery.items.length === 0" class="text-xs text-gray-400 text-center py-2">Немає альбомів</p>
    </div>

    {{-- Edit/New form --}}
    <div x-show="cnt.gallery.editing !== null" class="space-y-2">
        <input x-model="cnt.gallery.form.title" type="text" placeholder="Назва альбому"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <textarea x-model="cnt.gallery.form.description" placeholder="Опис (необов'язково)" rows="2"
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
        <label class="flex items-center gap-2 text-xs text-gray-600">
            <input type="checkbox" x-model="cnt.gallery.form.is_public" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            Публічний
        </label>
        <div class="flex gap-2">
            <button x-on:click="contentSaveItem('gallery')" :disabled="contentSaving"
                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!contentSaving">Зберегти</span>
                <span x-show="contentSaving">...</span>
            </button>
            <button x-on:click="contentCancel('gallery')"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                Скасувати
            </button>
        </div>
    </div>

    {{-- Full editor link --}}
    <a href="{{ route('website-builder.gallery.index') }}" target="_blank" class="flex items-center justify-center gap-1 text-[10px] text-gray-500 hover:text-primary-600 mt-1">
        <span>Повний редактор (завантаження фото)</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    </a>
</div>
