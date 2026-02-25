{{-- Testimonials section inline editor --}}
<div class="space-y-2">
    {{-- Loading --}}
    <div x-show="cnt.testimonials.loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- List view --}}
    <div x-show="!cnt.testimonials.loading && cnt.testimonials.editing === null">
        <button x-on:click="contentNew('testimonials', { author_name: '', author_role: '', content: '', photo: null, _photoPreview: null, rating: 5 })"
                class="w-full mb-2 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
            + Додати свідчення
        </button>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
            <template x-for="item in cnt.testimonials.items" :key="item.id">
                <div class="flex items-start gap-2 p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <template x-if="item.photo">
                        <img :src="'/storage/' + item.photo" class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-0.5">
                    </template>
                    <div class="flex-1 min-w-0">
                        <p x-text="item.author_name" class="text-xs font-medium text-gray-800 truncate"></p>
                        <p x-text="item.content" class="text-[10px] text-gray-500 line-clamp-2 mt-0.5"></p>
                        <div class="flex gap-2 mt-1">
                            <button x-on:click="contentEdit('testimonials', item, ['author_name', 'author_role', 'content', 'rating']); cnt.testimonials.form._photoPreview = item.photo ? '/storage/' + item.photo : null; cnt.testimonials.form.photo = null;" class="text-[10px] text-blue-500 hover:text-blue-700">Ред.</button>
                            <button x-on:click="contentDeleteItem('testimonials', item.id)" class="text-[10px] text-red-500 hover:text-red-700">Вид.</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <p x-show="cnt.testimonials.loaded && cnt.testimonials.items.length === 0" class="text-xs text-gray-400 text-center py-2">Немає свідчень</p>
    </div>

    {{-- Edit/New form --}}
    <div x-show="cnt.testimonials.editing !== null" class="space-y-2">
        <div class="flex items-center gap-2">
            <template x-if="cnt.testimonials.form._photoPreview">
                <img :src="cnt.testimonials.form._photoPreview" class="w-10 h-10 rounded-full object-cover">
            </template>
            <label class="text-[10px] text-primary-600 hover:text-primary-800 cursor-pointer font-medium">
                Фото (необов'язково)
                <input type="file" accept="image/*" class="hidden" x-on:change="
                    const file = $event.target.files[0];
                    if (file) { cnt.testimonials.form.photo = file; cnt.testimonials.form._photoPreview = URL.createObjectURL(file); }
                ">
            </label>
        </div>
        <input x-model="cnt.testimonials.form.author_name" type="text" placeholder="Ім'я автора"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <input x-model="cnt.testimonials.form.author_role" type="text" placeholder="Роль / позиція (необов'язково)"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <textarea x-model="cnt.testimonials.form.content" placeholder="Текст свідчення" rows="3"
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
        <div>
            <label class="block text-[10px] text-gray-500 mb-0.5">Оцінка</label>
            <div class="flex gap-1">
                <template x-for="star in [1,2,3,4,5]" :key="star">
                    <button x-on:click="cnt.testimonials.form.rating = star" :class="star <= cnt.testimonials.form.rating ? 'text-yellow-400' : 'text-gray-300'" class="text-lg leading-none">&#9733;</button>
                </template>
            </div>
        </div>
        <div class="flex gap-2">
            <button x-on:click="contentSaveItem('testimonials', true)" :disabled="contentSaving"
                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!contentSaving">Зберегти</span>
                <span x-show="contentSaving">...</span>
            </button>
            <button x-on:click="contentCancel('testimonials')"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                Скасувати
            </button>
        </div>
    </div>
</div>
