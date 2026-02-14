@props([
    'name' => 'select',
    'items' => collect(),
    'selected' => null,
    'labelKey' => 'name',
    'valueKey' => 'id',
    'placeholder' => 'Почніть вводити...',
    'required' => false,
    'nullable' => true,
    'nullText' => 'Не вибрано',
    'searchKeys' => null,
    'icon' => null,
    'colorKey' => null,
])

@php
    $uniqueId = 'searchable-select-' . uniqid();
    $searchKeysArray = $searchKeys ?? [$labelKey];
@endphp

<div x-data="searchableSelectComponent_{{ str_replace('-', '_', $uniqueId) }}()" class="relative" {{ $attributes }}>
    <input type="hidden" name="{{ $name }}" :value="selectedValue">

    <!-- Search Input -->
    <div class="relative">
        <input type="text"
               x-model="searchQuery"
               @focus="updatePosition(); isOpen = true"
               @click.away="isOpen = false"
               @keydown.escape="isOpen = false"
               @keydown.arrow-down.prevent="highlightNext()"
               @keydown.arrow-up.prevent="highlightPrev()"
               @keydown.enter.prevent="selectHighlighted()"
               @keydown.tab="isOpen = false"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
               class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white pr-10">

        <!-- Clear button -->
        <button type="button"
                x-show="selectedValue"
                @click="clearSelection()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Dropdown arrow -->
        <div x-show="!selectedValue" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    <!-- Dropdown -->
    <div x-show="isOpen && (filteredItems.length > 0 || searchQuery.length === 0)"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :style="dropdownStyle"
         class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-auto">

        @if($nullable)
        <!-- Null option -->
        <div @click="selectItem(null)"
             :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === -1}"
             class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 border-b border-gray-200 dark:border-gray-700">
            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                </svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $nullText }}</span>
        </div>
        @endif

        <template x-for="(item, index) in filteredItems" :key="item.value">
            <div @mouseenter="highlightedIndex = index"
                 @click="selectItem(item)"
                 :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index}"
                 class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3">
                @if($colorKey)
                <div class="w-3 h-3 rounded-full flex-shrink-0" :style="'background-color: ' + (item.color || '#6b7280')"></div>
                @elseif($icon)
                <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    {!! $icon !!}
                </div>
                @endif
                <span class="text-sm font-medium text-gray-900 dark:text-white truncate flex-1" x-text="item.label"></span>
                <!-- Selected checkmark -->
                <svg x-show="selectedValue == item.value" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        </template>
    </div>

    <!-- No results -->
    <div x-show="isOpen && searchQuery.length > 0 && filteredItems.length === 0"
         :style="dropdownStyle"
         class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg p-4 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Нічого не знайдено
    </div>
</div>

@once
@push('scripts')
<script>
window.searchableSelectData = window.searchableSelectData || {};
</script>
@endpush
@endonce

@push('scripts')
<script>
window.searchableSelectData['{{ $uniqueId }}'] = [
    @foreach($items as $item)
    @php
        $itemArray = is_array($item) ? $item : (is_object($item) ? (method_exists($item, 'toArray') ? $item->toArray() : (array)$item) : ['id' => $item, 'name' => $item]);
        $value = data_get($item, $valueKey);
        $label = data_get($item, $labelKey);
        $color = $colorKey ? data_get($item, $colorKey) : null;
        $searchTexts = collect($searchKeysArray)->map(fn($key) => data_get($item, $key))->filter()->values()->toArray();
    @endphp
    {
        value: @json($value),
        label: @json($label),
        color: @json($color),
        searchTexts: @json($searchTexts)
    },
    @endforeach
];

function searchableSelectComponent_{{ str_replace('-', '_', $uniqueId) }}() {
    const items = window.searchableSelectData['{{ $uniqueId }}'];
    const selectedValue = @json($selected);
    const selectedItem = selectedValue !== null && selectedValue !== '' ? items.find(i => i.value == selectedValue) : null;

    return {
        searchQuery: selectedItem ? selectedItem.label : '',
        selectedValue: selectedValue,
        isOpen: false,
        highlightedIndex: {{ $nullable ? '-1' : '0' }},
        items: items,
        dropdownStyle: {},

        updatePosition() {
            const el = this.$el.querySelector('input[type="text"]');
            if (!el) return;
            const rect = el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const openAbove = spaceBelow < 260 && rect.top > 260;
            this.dropdownStyle = {
                left: rect.left + 'px',
                width: rect.width + 'px',
                ...(openAbove
                    ? { bottom: (window.innerHeight - rect.top + 4) + 'px', top: 'auto' }
                    : { top: (rect.bottom + 4) + 'px', bottom: 'auto' })
            };
        },

        get filteredItems() {
            if (!this.searchQuery || this.searchQuery === this.getSelectedLabel()) {
                return this.items;
            }
            const query = this.searchQuery.toLowerCase();
            return this.items.filter(item =>
                item.searchTexts.some(text => text && text.toLowerCase().includes(query))
            );
        },

        getSelectedLabel() {
            if (this.selectedValue === null || this.selectedValue === '') return '';
            const item = this.items.find(i => i.value == this.selectedValue);
            return item ? item.label : '';
        },

        selectItem(item) {
            if (item) {
                this.selectedValue = item.value;
                this.searchQuery = item.label;
                this.$dispatch('select-changed', { value: item.value, item: item });
            } else {
                this.selectedValue = '';
                this.searchQuery = '';
                this.$dispatch('select-changed', { value: null, item: null });
            }
            this.isOpen = false;
        },

        clearSelection() {
            this.selectedValue = '';
            this.searchQuery = '';
            this.highlightedIndex = {{ $nullable ? '-1' : '0' }};
            this.$dispatch('select-changed', { value: null, item: null });
        },

        highlightNext() {
            const max = this.filteredItems.length - 1;
            if (this.highlightedIndex < max) {
                this.highlightedIndex++;
            }
        },

        highlightPrev() {
            const min = {{ $nullable ? '-1' : '0' }};
            if (this.highlightedIndex > min) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex === -1) {
                this.selectItem(null);
            } else if (this.filteredItems.length > 0 && this.highlightedIndex >= 0) {
                this.selectItem(this.filteredItems[this.highlightedIndex]);
            }
        }
    }
}
</script>
@endpush
