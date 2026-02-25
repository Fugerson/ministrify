{{-- About section inline editor --}}
<div class="space-y-2">
    <div>
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Місія</label>
        <textarea x-model="aboutForm.mission" rows="2" placeholder="Наша місія..."
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
    </div>

    <div>
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Візія</label>
        <textarea x-model="aboutForm.vision" rows="2" placeholder="Наша візія..."
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
    </div>

    <div>
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Цінності</label>
        <div class="space-y-1">
            <template x-for="(val, idx) in aboutForm.values" :key="idx">
                <div class="flex gap-1">
                    <input :value="val" x-on:input="aboutForm.values[idx] = $event.target.value" type="text" placeholder="Цінність..."
                           class="flex-1 text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    <button x-on:click="removeAboutValue(idx)" class="px-1.5 text-red-400 hover:text-red-600" title="Видалити">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button x-on:click="addAboutValue()" class="text-[10px] text-primary-600 hover:text-primary-800 font-medium">+ Додати цінність</button>
        </div>
    </div>

    <div>
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Історія</label>
        <textarea x-model="aboutForm.history" rows="3" placeholder="Наша історія..."
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
    </div>

    <div>
        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Вірування</label>
        <textarea x-model="aboutForm.beliefs" rows="3" placeholder="Наші вірування..."
                  class="w-full text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-y"></textarea>
    </div>

    <button x-on:click="saveAbout()" :disabled="contentSaving"
            class="w-full px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors">
        <span x-show="!contentSaving">Зберегти</span>
        <span x-show="contentSaving">Збереження...</span>
    </button>
</div>
