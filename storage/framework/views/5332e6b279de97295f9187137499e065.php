<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'name' => 'person_id',
    'people' => collect(),
    'selected' => null,
    'placeholder' => 'Почніть вводити ім\'я...',
    'required' => false,
    'nullable' => true,
    'nullText' => 'Не вибрано',
    'showPhoto' => true,
    'showRole' => true,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'name' => 'person_id',
    'people' => collect(),
    'selected' => null,
    'placeholder' => 'Почніть вводити ім\'я...',
    'required' => false,
    'nullable' => true,
    'nullText' => 'Не вибрано',
    'showPhoto' => true,
    'showRole' => true,
]); ?>
<?php foreach (array_filter(([
    'name' => 'person_id',
    'people' => collect(),
    'selected' => null,
    'placeholder' => 'Почніть вводити ім\'я...',
    'required' => false,
    'nullable' => true,
    'nullText' => 'Не вибрано',
    'showPhoto' => true,
    'showRole' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $uniqueId = 'person-select-' . uniqid();
?>

<div x-data="personSelectComponent_<?php echo e(str_replace('-', '_', $uniqueId)); ?>()" class="relative" <?php echo e($attributes); ?>>
    <input type="hidden" name="<?php echo e($name); ?>" :value="selectedId">

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
               placeholder="<?php echo e($placeholder); ?>"
               <?php echo e($required ? 'required' : ''); ?>

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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nullable): ?>
        <!-- Null option -->
        <div @click="selectPerson(null)"
             :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === -1}"
             class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 border-b border-gray-100 dark:border-gray-700">
            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                </svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($nullText); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <template x-for="(person, index) in filteredPeople" :key="person.id">
            <div @mouseenter="highlightedIndex = index"
                 @click="selectPerson(person)"
                 :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index}"
                 class="px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhoto): ?>
                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                    <template x-if="person.photo">
                        <img :src="person.photo" :alt="person.full_name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!person.photo">
                        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs font-medium" x-text="person.initials"></div>
                    </template>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="person.full_name"></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showRole): ?>
                    <div x-show="person.role" class="text-xs text-gray-500 dark:text-gray-400" x-text="person.role"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

<?php if (! $__env->hasRenderedOnce('9724d897-55aa-42c6-af9d-4224358a567d')): $__env->markAsRenderedOnce('9724d897-55aa-42c6-af9d-4224358a567d'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
window.personSelectData = window.personSelectData || {};
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.personSelectData['<?php echo e($uniqueId); ?>'] = [
    <?php $__currentLoopData = $people->sortBy('last_name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $photoUrl = $person->photo ? \Illuminate\Support\Facades\Storage::url($person->photo) : null;
        $roleName = $person->churchRoleRelation ? $person->churchRoleRelation->name : ($person->church_role ?? null);
        $initials = mb_substr($person->first_name, 0, 1) . mb_substr($person->last_name ?? '', 0, 1);
    ?>
    {
        id: <?php echo e($person->id); ?>,
        full_name: <?php echo json_encode($person->full_name, 15, 512) ?>,
        photo: <?php echo json_encode($photoUrl, 15, 512) ?>,
        role: <?php echo json_encode($roleName, 15, 512) ?>,
        initials: <?php echo json_encode($initials, 15, 512) ?>
    },
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
];

function personSelectComponent_<?php echo e(str_replace('-', '_', $uniqueId)); ?>() {
    const people = window.personSelectData['<?php echo e($uniqueId); ?>'];
    const selectedValue = <?php echo json_encode($selected, 15, 512) ?>;
    const selectedPerson = selectedValue ? people.find(p => p.id == selectedValue) : null;

    return {
        searchQuery: selectedPerson ? selectedPerson.full_name : '',
        selectedId: selectedValue,
        isOpen: false,
        highlightedIndex: <?php echo e($nullable ? '-1' : '0'); ?>,
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
            this.highlightedIndex = <?php echo e($nullable ? '-1' : '0'); ?>;
        },

        highlightNext() {
            const max = this.filteredPeople.length - 1;
            if (this.highlightedIndex < max) {
                this.highlightedIndex++;
            }
        },

        highlightPrev() {
            const min = <?php echo e($nullable ? '-1' : '0'); ?>;
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
<?php $__env->stopPush(); ?>
<?php /**PATH /var/www/html/resources/views/components/person-select.blade.php ENDPATH**/ ?>