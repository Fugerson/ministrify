{{-- Team section inline editor --}}
<div class="space-y-2">
    {{-- Loading --}}
    <div x-show="cnt.team.loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- List view --}}
    <div x-show="!cnt.team.loading && cnt.team.editing === null">
        <button x-on:click="contentNew('team', { name: '', title: '', bio: '', photo: null, _photoPreview: null })"
                class="w-full mb-2 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
            + Додати члена команди
        </button>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
            <template x-for="item in cnt.team.items" :key="item.id">
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <template x-if="item.photo">
                        <img :src="'/storage/' + item.photo" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    </template>
                    <template x-if="!item.photo">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </template>
                    <div class="flex-1 min-w-0">
                        <p x-text="item.name" class="text-xs font-medium text-gray-800 truncate"></p>
                        <p x-text="item.title" class="text-[10px] text-gray-500 truncate"></p>
                        <div class="flex gap-2 mt-0.5">
                            <button x-on:click="contentEdit('team', item, ['name', 'title', 'bio']); cnt.team.form._photoPreview = item.photo ? '/storage/' + item.photo : null; cnt.team.form.photo = null;" class="text-[10px] text-blue-500 hover:text-blue-700">Ред.</button>
                            <button x-on:click="contentDeleteItem('team', item.id)" class="text-[10px] text-red-500 hover:text-red-700">Вид.</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <p x-show="cnt.team.loaded && cnt.team.items.length === 0" class="text-xs text-gray-400 text-center py-2">Немає членів команди</p>
    </div>

    {{-- Edit/New form --}}
    <div x-show="cnt.team.editing !== null" class="space-y-2">
        <div class="flex items-center gap-2">
            <template x-if="cnt.team.form._photoPreview">
                <img :src="cnt.team.form._photoPreview" class="w-12 h-12 rounded-full object-cover">
            </template>
            <template x-if="!cnt.team.form._photoPreview">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </template>
            <label class="text-[10px] text-primary-600 hover:text-primary-800 cursor-pointer font-medium">
                Обрати фото
                <input type="file" accept="image/*" class="hidden" x-on:change="
                    const file = $event.target.files[0];
                    if (file) { cnt.team.form.photo = file; cnt.team.form._photoPreview = URL.createObjectURL(file); }
                ">
            </label>
        </div>
        <input x-model="cnt.team.form.name" type="text" placeholder="Ім'я"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <input x-model="cnt.team.form.title" type="text" placeholder="Посада / роль"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <textarea x-model="cnt.team.form.bio" placeholder="Коротка біографія" rows="2"
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
        <div class="flex gap-2">
            <button x-on:click="contentSaveItem('team', true)" :disabled="contentSaving"
                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!contentSaving">Зберегти</span>
                <span x-show="contentSaving">...</span>
            </button>
            <button x-on:click="contentCancel('team')"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                Скасувати
            </button>
        </div>
    </div>
</div>
