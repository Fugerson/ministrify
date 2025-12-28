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
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($shepherds as $shepherd)
            <div class="flex items-center gap-4 p-4 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                 data-shepherd-id="{{ $shepherd->id }}">

                <!-- Photo -->
                <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                    @if($shepherd->photo)
                        <img src="{{ Storage::url($shepherd->photo) }}" alt="{{ $shepherd->full_name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-lg font-medium">
                            {{ substr($shepherd->first_name, 0, 1) }}{{ substr($shepherd->last_name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <!-- Name -->
                <div class="flex-1 min-w-0">
                    <a href="{{ route('people.show', $shepherd) }}" class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                        {{ $shepherd->full_name }}
                    </a>
                    @if($shepherd->churchRoleRelation)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $shepherd->churchRoleRelation->name }}</p>
                    @endif
                </div>

                <!-- Sheep Count -->
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ $shepherd->sheep_count }} {{ trans_choice('підопічний|підопічних|підопічних', $shepherd->sheep_count) }}
                    </span>
                </div>

                <!-- Remove Button -->
                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="removeShepherd({{ $shepherd->id }}, '{{ $shepherd->full_name }}', {{ $shepherd->sheep_count }})"
                            class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                            title="Прибрати статус опікуна">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p>Немає опікунів. Додайте першого опікуна нижче.</p>
            </div>
            @endforelse
        </div>

        <!-- Add Shepherd -->
        @if($availablePeople->count() > 0)
        <div class="border-t border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center gap-4" x-data="personSearch()">
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
                                   class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
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
                        :disabled="!selectedId"
                        class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати
                </button>
            </div>
        </div>
        @endif
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
        async addShepherd(personId) {
            try {
                const response = await fetch('{{ route("settings.shepherds.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ person_id: personId })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при додаванні');
                }
            } catch (error) {
                alert('Помилка з\'єднання');
            }
        },

        async removeShepherd(personId, name, sheepCount) {
            let message = `Прибрати статус опікуна у "${name}"?`;
            if (sheepCount > 0) {
                message += `\n\nУвага: у цієї людини ${sheepCount} підопічних. Вони будуть відв'язані від опікуна.`;
            }

            if (!confirm(message)) return;

            try {
                const response = await fetch(`/settings/shepherds/${personId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
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

function personSearch() {
    return {
        searchQuery: '',
        selectedId: null,
        isOpen: false,
        highlightedIndex: 0,
        people: [
            @foreach($availablePeople->sortBy('last_name') as $person)
            {
                id: {{ $person->id }},
                full_name: @json($person->full_name),
                photo: @json($person->photo ? Storage::url($person->photo) : null),
                role: @json($person->churchRoleRelation?->name),
                initials: @json(substr($person->first_name, 0, 1) . substr($person->last_name, 0, 1))
            },
            @endforeach
        ],
        get filteredPeople() {
            if (!this.searchQuery) {
                return this.people;
            }
            const query = this.searchQuery.toLowerCase();
            return this.people.filter(p =>
                p.full_name.toLowerCase().includes(query)
            );
        },
        selectPerson(person) {
            this.selectedId = person.id;
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
            if (!this.selectedId) return;

            try {
                const response = await fetch('{{ route("settings.shepherds.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ person_id: this.selectedId })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Помилка при додаванні');
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
