{{-- Sermons section inline editor --}}
<div class="space-y-2">
    {{-- Loading --}}
    <div x-show="cnt.sermons.loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- List view --}}
    <div x-show="!cnt.sermons.loading && cnt.sermons.editing === null">
        <button x-on:click="contentNew('sermons', { title: '', sermon_date: '', video_url: '', speaker_name: '', description: '' })"
                class="w-full mb-2 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
            + Додати проповідь
        </button>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
            <template x-for="item in cnt.sermons.items" :key="item.id">
                <div class="p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <p x-text="item.title" class="text-xs font-medium text-gray-800 truncate"></p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span x-text="item.sermon_date" class="text-[10px] text-gray-400"></span>
                        <span x-show="item.speaker_name || item.speaker?.name" x-text="item.speaker_name || item.speaker?.name" class="text-[10px] text-gray-400"></span>
                    </div>
                    <div class="flex gap-2 mt-1">
                        <button x-on:click="contentEdit('sermons', item, ['title', 'sermon_date', 'video_url', 'speaker_name', 'description'])" class="text-[10px] text-blue-500 hover:text-blue-700">Ред.</button>
                        <button x-on:click="contentDeleteItem('sermons', item.id)" class="text-[10px] text-red-500 hover:text-red-700">Вид.</button>
                    </div>
                </div>
            </template>
        </div>
        <p x-show="cnt.sermons.loaded && cnt.sermons.items.length === 0" class="text-xs text-gray-400 text-center py-2">Немає проповідей</p>
    </div>

    {{-- Edit/New form --}}
    <div x-show="cnt.sermons.editing !== null" class="space-y-2">
        <input x-model="cnt.sermons.form.title" type="text" placeholder="Назва проповіді"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <input x-model="cnt.sermons.form.sermon_date" type="date"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <input x-model="cnt.sermons.form.speaker_name" type="text" placeholder="Спікер"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <input x-model="cnt.sermons.form.video_url" type="url" placeholder="URL відео (YouTube тощо)"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <textarea x-model="cnt.sermons.form.description" placeholder="Опис (необов'язково)" rows="2"
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
        <div class="flex gap-2">
            <button x-on:click="contentSaveItem('sermons')" :disabled="contentSaving"
                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!contentSaving">Зберегти</span>
                <span x-show="contentSaving">...</span>
            </button>
            <button x-on:click="contentCancel('sermons')"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                Скасувати
            </button>
        </div>
    </div>

    {{-- Full editor link --}}
    <a href="{{ route('website-builder.sermons.index') }}" target="_blank" class="flex items-center justify-center gap-1 text-[10px] text-gray-500 hover:text-primary-600 mt-1">
        <span>Повний редактор (аудіо, серії)</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    </a>
</div>
