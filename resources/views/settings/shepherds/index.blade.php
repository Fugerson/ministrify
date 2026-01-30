@extends('layouts.app')

@section('title', 'Опікуну')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="shepherdsManager()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Опікуни</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Виберіть людей, які можуть бути опікунами для членів церкви</p>
        </div>
    </div>

    <!-- Shepherds List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <template x-if="shepherds.length === 0">
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p>Немає опікунів. Додайте першого опікуна нижче.</p>
                </div>
            </template>
            <template x-for="shepherd in shepherds" :key="shepherd.id">
                <div class="flex items-center gap-4 p-4 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                     :data-shepherd-id="shepherd.id">

                    <!-- Photo -->
                    <div class="flex-shrink-0" x-data="{ hover: false, r: {} }" @mouseenter="shepherd.photo && (hover = true, r = $el.getBoundingClientRect())" @mouseleave="hover = false">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <template x-if="shepherd.photo">
                                <img :src="shepherd.photo" :alt="shepherd.full_name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!shepherd.photo">
                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-lg font-medium" x-text="shepherd.initials"></div>
                            </template>
                        </div>
                        <div x-show="hover" x-transition.opacity.duration.150ms class="fixed z-[100] pointer-events-none" :style="`left:${r.left+r.width/2}px;top:${r.top-8}px;transform:translate(-50%,-100%)`">
                            <img :src="shepherd.photo" class="w-32 h-32 rounded-xl object-cover shadow-xl ring-2 ring-white dark:ring-gray-800">
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="flex-1 min-w-0">
                        <a :href="'/people/' + shepherd.id" class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400" x-text="shepherd.full_name"></a>
                        <p x-show="shepherd.role" class="text-sm text-gray-500 dark:text-gray-400" x-text="shepherd.role"></p>
                    </div>

                    <!-- Sheep Count -->
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-text="shepherd.sheep_count + ' підопічних'"></span>
                        </span>
                    </div>

                    <!-- Remove Button -->
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="removeShepherd(shepherd)"
                                class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                title="Прибрати статус опікуна">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Add Shepherd -->
        <template x-if="availablePeople.length > 0">
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
                <div class="flex items-center gap-4">
                    <div class="flex-1 relative">
                        <input type="text"
                               x-model="searchQuery"
                               @focus="isOpen = true"
                               @click.away="isOpen = false"
                               @keydown.escape="isOpen = false"
                               @keydown.arrow-down.prevent="highlightNext()"
                               @keydown.arrow-up.prevent="highlightPrev()"
                               @keydown.enter.prevent="selectHighlighted()"
                               placeholder="Почніть вводити ім'я..."
                               class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white">

                        <!-- Dropdown -->
                        <div x-show="isOpen && filteredPeople.length > 0"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg max-h-60 overflow-auto">
                            <template x-for="(person, index) in filteredPeople" :key="person.id">
                                <div @mouseenter="highlightedIndex = index"
                                     :class="{'bg-primary-50 dark:bg-primary-900/30': highlightedIndex === index}"
                                     class="px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                        <template x-if="person.photo">
                                            <img :src="person.photo" :alt="person.full_name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!person.photo">
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs font-medium" x-text="person.initials"></div>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0 cursor-pointer" @click="selectPerson(person)">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="person.full_name"></div>
                                        <div x-show="person.role" class="text-xs text-gray-500 dark:text-gray-400" x-text="person.role"></div>
                                    </div>
                                    <a :href="'/people/' + person.id"
                                       @click.stop
                                       class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                       title="Переглянути профіль">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            </template>
                        </div>

                        <!-- No results -->
                        <div x-show="isOpen && searchQuery.length > 0 && filteredPeople.length === 0"
                             class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg p-4 text-center text-gray-500 dark:text-gray-400">
                            Нікого не знайдено
                        </div>
                    </div>
                    <button @click="addSelected()"
                            :disabled="!selectedPerson"
                            class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Додати
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium">Про опікунів:</p>
                <ul class="mt-1 list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                    <li>Опікуни - це люди, які духовно опікуються іншими членами церкви</li>
                    <li>Кожному члену церкви можна призначити одного опікуна з цього списку</li>
                    <li>Призначити опікуна можна в профілі людини або в таблиці людей</li>
                </ul>
            </div>
        </div>
    </div>

    <a href="{{ route('settings.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад до налаштувань
    </a>
</div>

@push('scripts')
<script>
function shepherdsManager() {
    return {
        shepherds: @json($shepherdsJson),
        availablePeople: @json($availablePeopleJson),
        searchQuery: '',
        selectedPerson: null,
        isOpen: false,
        highlightedIndex: 0,

        get filteredPeople() {
            if (!this.searchQuery) {
                return this.availablePeople;
            }
            const query = this.searchQuery.toLowerCase();
            return this.availablePeople.filter(p =>
                p.full_name.toLowerCase().includes(query)
            );
        },

        selectPerson(person) {
            this.selectedPerson = person;
            this.searchQuery = person.full_name;
            this.isOpen = false;
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredPeople.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrev() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.filteredPeople.length > 0) {
                this.selectPerson(this.filteredPeople[this.highlightedIndex]);
            }
        },

        async addSelected() {
            if (!this.selectedPerson) return;

            try {
                const response = await fetch('{{ route("settings.shepherds.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ person_id: this.selectedPerson.id })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.shepherd) {
                        // Add to shepherds list
                        this.shepherds.push(data.shepherd);
                        // Remove from available people
                        this.availablePeople = this.availablePeople.filter(p => p.id !== this.selectedPerson.id);
                        // Reset search
                        this.searchQuery = '';
                        this.selectedPerson = null;
                        this.highlightedIndex = 0;
                        if (window.showGlobalToast) showGlobalToast('Опікуна додано', 'success');
                    }
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при додаванні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        },

        async removeShepherd(shepherd) {
            let message = `Прибрати статус опікуна у "${shepherd.full_name}"?`;
            if (shepherd.sheep_count > 0) {
                message += `\n\nУвага: у цієї людини ${shepherd.sheep_count} підопічних. Вони будуть відв'язані від опікуна.`;
            }

            if (!confirm(message)) return;

            try {
                const response = await fetch(`/settings/shepherds/${shepherd.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    // Remove from shepherds
                    this.shepherds = this.shepherds.filter(s => s.id !== shepherd.id);
                    // Add back to available people
                    this.availablePeople.push({
                        id: shepherd.id,
                        full_name: shepherd.full_name,
                        photo: shepherd.photo,
                        role: shepherd.role,
                        initials: shepherd.initials,
                    });
                    // Sort available people by name
                    this.availablePeople.sort((a, b) => a.full_name.localeCompare(b.full_name));
                    if (window.showGlobalToast) showGlobalToast('Статус опікуна прибрано', 'success');
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при видаленні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        }
    }
}
</script>
@endpush
@endsection
