{{-- FAQ section inline editor --}}
<div class="space-y-2">
    {{-- Loading --}}
    <div x-show="cnt.faq.loading" class="flex justify-center py-4">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- List view --}}
    <div x-show="!cnt.faq.loading && cnt.faq.editing === null">
        <button x-on:click="contentNew('faq', { question: '', answer: '', category: '' })"
                class="w-full mb-2 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
            + Додати FAQ
        </button>
        <div class="space-y-1.5 max-h-64 overflow-y-auto">
            <template x-for="item in cnt.faq.items" :key="item.id">
                <div class="p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <p x-text="item.question" class="text-xs font-medium text-gray-800 line-clamp-2"></p>
                    <p x-text="item.category" x-show="item.category" class="text-[10px] text-gray-400 mt-0.5"></p>
                    <div class="flex gap-2 mt-1">
                        <button x-on:click="contentEdit('faq', item, ['question', 'answer', 'category'])" class="text-[10px] text-blue-500 hover:text-blue-700">Ред.</button>
                        <button x-on:click="contentDeleteItem('faq', item.id)" class="text-[10px] text-red-500 hover:text-red-700">Вид.</button>
                    </div>
                </div>
            </template>
        </div>
        <p x-show="cnt.faq.loaded && cnt.faq.items.length === 0" class="text-xs text-gray-400 text-center py-2">Немає FAQ</p>
    </div>

    {{-- Edit/New form --}}
    <div x-show="cnt.faq.editing !== null" class="space-y-2">
        <input x-model="cnt.faq.form.question" type="text" placeholder="Питання"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <textarea x-model="cnt.faq.form.answer" placeholder="Відповідь" rows="3"
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
        <input x-model="cnt.faq.form.category" type="text" placeholder="Категорія (необов'язково)"
               class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
        <div class="flex gap-2">
            <button x-on:click="contentSaveItem('faq')" :disabled="contentSaving"
                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50">
                <span x-show="!contentSaving">Зберегти</span>
                <span x-show="contentSaving">...</span>
            </button>
            <button x-on:click="contentCancel('faq')"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                Скасувати
            </button>
        </div>
    </div>
</div>
