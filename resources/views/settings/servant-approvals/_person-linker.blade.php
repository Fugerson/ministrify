{{-- Person Linking Section --}}
<div class="mt-3 mb-2">
    {{-- Auto-detected matches --}}
    <template x-if="suggestedMatches.length > 0 && !selectedPersonId">
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-2">
            <p class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-2">
                {{ __('Можливий збіг у базі') }}:
            </p>
            <div class="space-y-1">
                <template x-for="match in suggestedMatches" x-bind:key="match.id">
                    <button x-on:click="selectPerson(match)"
                            class="w-full text-left px-3 py-2 bg-white dark:bg-gray-700 rounded border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition text-sm">
                        <span class="font-medium text-gray-900 dark:text-white" x-text="match.name"></span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2" x-text="match.email || ''"></span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2" x-text="match.phone || ''"></span>
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- Selected person indicator --}}
    <template x-if="selectedPersonId">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg px-3 py-2 flex items-center justify-between">
            <p class="text-sm text-green-800 dark:text-green-200">
                {{ __('Привʼязати до') }}: <span class="font-medium" x-text="selectedPersonName"></span>
            </p>
            <button x-on:click="clearSelection()" class="text-red-500 hover:text-red-700 text-sm ml-2">
                &times;
            </button>
        </div>
    </template>

    {{-- Manual search toggle --}}
    <template x-if="!selectedPersonId">
        <div>
            <button x-on:click="showManualSearch = !showManualSearch"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1">
                <span x-text="showManualSearch ? '{{ __('Сховати пошук') }}' : '{{ __('Знайти вручну в базі') }}'"></span>
            </button>

            {{-- Manual search dropdown --}}
            <template x-if="showManualSearch">
                <div class="mt-2 relative">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('Введіть імʼя, email або телефон...') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">

                    {{-- Search results dropdown --}}
                    <template x-if="filteredPeople.length > 0">
                        <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="person in filteredPeople" x-bind:key="person.id">
                                <button x-on:click="selectPerson(person)"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 text-sm border-b border-gray-100 dark:border-gray-600 last:border-0">
                                    <span class="font-medium text-gray-900 dark:text-white" x-text="person.name"></span>
                                    <template x-if="person.email">
                                        <span class="text-gray-500 dark:text-gray-400 ml-2 text-xs" x-text="person.email"></span>
                                    </template>
                                    <template x-if="person.phone">
                                        <span class="text-gray-500 dark:text-gray-400 ml-2 text-xs" x-text="person.phone"></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </template>

                    <template x-if="searchQuery.length >= 2 && filteredPeople.length === 0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Нікого не знайдено') }}</p>
                    </template>
                </div>
            </template>
        </div>
    </template>
</div>
