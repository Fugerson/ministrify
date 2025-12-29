@props([
    'name' => 'person_id',
    'people' => collect(),
    'selected' => null,
    'placeholder' => 'Почніть вводити ім\'я...',
    'required' => false,
    'nullable' => true,
    'nullText' => 'Не вибрано',
    'showPhoto' => true,
    'showRole' => true,
])

@php
    $uniqueId = 'person-select-' . uniqid();
@endphp

<div x-data="personSelectComponent_{{ str_replace('-', '_', $uniqueId) }}()" class="relative" {{ $attributes }}>
    <input type="hidden" name="{{ $name }}" :value="selectedId">

    <!-- Search Input -->
    <div class="relative">
        <input type="text"
               x-model="searchQuery"
               @focus="isOpen = true"
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
                x-show="selectedId"
                @click="clearSelection()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Dropdown arrow -->
        <div x-show="!selectedId" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    <!-- Dropdown -->
    <div x-show="isOpen && (filteredPeople.length > 0 || searchQuery.length === 0)"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-auto">

        @if($nullable)
        <!-- Null option -->
        <div @click="selectPerson(null)"
             :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === -1}"
             class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 border-b border-gray-100 dark:border-gray-700">
            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                </svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $nullText }}</span>
        </div>
        @endif

        <template x-for="(person, index) in filteredPeople" :key="person.id">
            <div @mouseenter="highlightedIndex = index"
                 @click="selectPerson(person)"
                 :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index}"
                 class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3">
                @if($showPhoto)
                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                    <template x-if="person.photo">
                        <img :src="person.photo" :alt="person.full_name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!person.photo">
                        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs font-medium" x-text="person.initials"></div>
                    </template>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="person.full_name"></div>
                    @if($showRole)
                    <div x-show="person.role" class="text-xs text-gray-500 dark:text-gray-400" x-text="person.role"></div>
                    @endif
                </div>
                <!-- Selected checkmark -->
                <svg x-show="selectedId == person.id" class="w-4 h-4 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        </template>
    </div>

    <!-- No results -->
    <div x-show="isOpen && searchQuery.length > 0 && filteredPeople.length === 0"
         class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg p-4 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Нікого не знайдено
    </div>
</div>

@once
@push('scripts')
<script>
window.personSelectData = window.personSelectData || {};
</script>
@endpush
@endonce

@push('scripts')
<script>
window.personSelectData['{{ $uniqueId }}'] = [
    @foreach($people->sortBy('last_name') as $person)
    @php
        $photoUrl = $person->photo ? \Illuminate\Support\Facades\Storage::url($person->photo) : null;
        $roleName = $person->churchRoleRelation ? $person->churchRoleRelation->name : ($person->church_role ?? null);
        $initials = mb_substr($person->first_name, 0, 1) . mb_substr($person->last_name ?? '', 0, 1);
    @endphp
    {
        id: {{ $person->id }},
        full_name: @json($person->full_name),
        photo: @json($photoUrl),
        role: @json($roleName),
        initials: @json($initials)
    },
    @endforeach
];

function personSelectComponent_{{ str_replace('-', '_', $uniqueId) }}() {
    const people = window.personSelectData['{{ $uniqueId }}'];
    const selectedValue = @json($selected);
    const selectedPerson = selectedValue ? people.find(p => p.id == selectedValue) : null;

    return {
        searchQuery: selectedPerson ? selectedPerson.full_name : '',
        selectedId: selectedValue,
        isOpen: false,
        highlightedIndex: {{ $nullable ? '-1' : '0' }},
        people: people,

        get filteredPeople() {
            if (!this.searchQuery || this.searchQuery === (this.getSelectedName())) {
                return this.people;
            }
            const query = this.searchQuery.toLowerCase();
            return this.people.filter(p =>
                p.full_name.toLowerCase().includes(query)
            );
        },

        getSelectedName() {
            if (!this.selectedId) return '';
            const person = this.people.find(p => p.id == this.selectedId);
            return person ? person.full_name : '';
        },

        selectPerson(person) {
            if (person) {
                this.selectedId = person.id;
                this.searchQuery = person.full_name;
            } else {
                this.selectedId = null;
                this.searchQuery = '';
            }
            this.isOpen = false;
        },

        clearSelection() {
            this.selectedId = null;
            this.searchQuery = '';
            this.highlightedIndex = {{ $nullable ? '-1' : '0' }};
        },

        highlightNext() {
            const max = this.filteredPeople.length - 1;
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
                this.selectPerson(null);
            } else if (this.filteredPeople.length > 0 && this.highlightedIndex >= 0) {
                this.selectPerson(this.filteredPeople[this.highlightedIndex]);
            }
        }
    }
}
</script>
@endpush
