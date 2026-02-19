@extends('layouts.app')

@section('title', 'Одобрення служителів')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ __('Одобрення служителів') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('Керуйте заявками користувачів на роль служителя та інші ролі') }}
        </p>
    </div>

    @php
        $mapPerson = function($p) {
            return ['id' => $p->id, 'name' => $p->full_name, 'email' => $p->email, 'phone' => $p->phone];
        };
        $availablePeopleJs = $availablePeople->map($mapPerson)->values();
        $matchesJs = [];
        foreach ($potentialMatches as $userId => $matches) {
            $matchesJs[$userId] = $matches->map($mapPerson)->values();
        }
    @endphp

    <!-- Alerts -->
    @if ($servantPending->isEmpty() && $churchRolePending->isEmpty())
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-8">
            <p class="text-blue-800 dark:text-blue-200">
                {{ __('Всі заявки одобрені! Немає очікуючих користувачів.') }}
            </p>
        </div>
    @endif

    <!-- Servant Role Approvals -->
    @if (!$servantPending->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Заявки на роль служителя') }} ({{ $servantPending->count() }})
                </h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($servantPending as $user)
                    <div class="px-6 py-4" x-data="personLinker({{ $user->id }}, {{ json_encode($matchesJs[$user->id] ?? []) }})">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    @if ($user->person?->photo)
                                        <img src="{{ Storage::url($user->person->photo) }}"
                                             alt="{{ $user->name }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                            <span class="text-white font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                                @if ($user->person?->phone)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 ml-15">{{ $user->person->phone }}</p>
                                @endif
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    {{ __('Заявка від') }}: {{ $user->created_at->format('d.m.Y H:i') }}
                                </p>
                            </div>

                            <div class="text-right">
                                <span class="inline-block px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 text-sm rounded-full font-medium">
                                    {{ __('Очікує одобрення') }}
                                </span>
                            </div>
                        </div>

                        <!-- Person Linking Section -->
                        @include('settings.servant-approvals._person-linker', ['user' => $user])

                        <!-- Action Buttons -->
                        <div class="flex gap-3 mt-4">
                            <button x-on:click="approve(null)"
                                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                {{ __('Одобрити як волонтера') }}
                            </button>
                            <button onclick="showRejectModal({{ $user->id }})"
                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                                {{ __('Відхилити') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Church Role Approvals -->
    @if (!$churchRolePending->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Заявки на інші ролі') }} ({{ $churchRolePending->count() }})
                </h2>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($churchRolePending as $user)
                    <div class="px-6 py-4" x-data="personLinker({{ $user->id }}, {{ json_encode($matchesJs[$user->id] ?? []) }})">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    @if ($user->person?->photo)
                                        <img src="{{ Storage::url($user->person->photo) }}"
                                             alt="{{ $user->name }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                                @if ($user->requestedChurchRole)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                                        {{ __('Запитана роль') }}: <span class="font-medium">{{ $user->requestedChurchRole->name }}</span>
                                    </p>
                                @endif
                            </div>

                            <div class="text-right">
                                <span class="inline-block px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 text-sm rounded-full font-medium">
                                    {{ __('Очікує') }}
                                </span>
                            </div>
                        </div>

                        <!-- Person Linking Section -->
                        @include('settings.servant-approvals._person-linker', ['user' => $user])

                        <!-- Action Buttons -->
                        <div class="flex gap-3 mt-4">
                            <select x-ref="roleSelect" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg border border-gray-300 dark:border-gray-600">
                                <option value="">{{ __('-- Оберіть роль для одобрення --') }}</option>
                                @foreach ($churchRoles as $role)
                                    <option value="{{ $role->id }}"
                                            @if ($user->requestedChurchRole?->id === $role->id) selected @endif>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button x-on:click="approve($refs.roleSelect.value)"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                {{ __('Одобрити') }}
                            </button>
                            <button onclick="showRejectModal({{ $user->id }})"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                                {{ __('Відхилити') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('Відхилити заявку') }}
            </h3>
        </div>

        <form id="rejectForm" method="POST" class="p-6">
            @csrf
            <input type="hidden" id="rejectUserId" name="user_id">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Причина відхилення (необов\'язково)') }}
                </label>
                <textarea name="reason"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                          rows="4"
                          placeholder="{{ __('Введіть причину для користувача...') }}"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    {{ __('Скасувати') }}
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                    {{ __('Відхилити') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Available people for manual search (passed from server)
const availablePeople = @json($availablePeopleJs);

function personLinker(userId, suggestedMatches) {
    return {
        userId: userId,
        suggestedMatches: suggestedMatches,
        selectedPersonId: null,
        selectedPersonName: '',
        showManualSearch: false,
        searchQuery: '',

        get filteredPeople() {
            if (!this.searchQuery || this.searchQuery.length < 2) return [];
            const q = this.searchQuery.toLowerCase();
            return availablePeople.filter(p =>
                (p.name && p.name.toLowerCase().includes(q)) ||
                (p.email && p.email.toLowerCase().includes(q)) ||
                (p.phone && p.phone.includes(q))
            ).slice(0, 10);
        },

        selectPerson(person) {
            this.selectedPersonId = person.id;
            this.selectedPersonName = person.name + (person.email ? ' (' + person.email + ')' : '') + (person.phone ? ' ' + person.phone : '');
            this.showManualSearch = false;
            this.searchQuery = '';
        },

        clearSelection() {
            this.selectedPersonId = null;
            this.selectedPersonName = '';
        },

        approve(roleId) {
            if (roleId === '' || roleId === null) {
                // For servant approvals roleId is null — use default volunteer role
                // For church role approvals, roleId must be selected
                if (roleId === '') {
                    alert('{{ __("Виберіть роль для одобрення") }}');
                    return;
                }
            }

            const body = { church_role_id: roleId };
            if (this.selectedPersonId) {
                body.link_person_id = this.selectedPersonId;
            }

            fetch(`/settings/servant-approvals/${this.userId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('{{ __("Помилка") }}: ' + data.message);
                }
            })
            .catch(err => alert('{{ __("Помилка запиту") }}: ' + err));
        }
    };
}

function showRejectModal(userId) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectForm').action = `/settings/servant-approvals/${userId}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const userId = document.getElementById('rejectUserId').value;
    const reason = document.querySelector('textarea[name="reason"]').value;

    fetch(`/settings/servant-approvals/${userId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('{{ __("Помилка") }}: ' + data.message);
        }
    })
    .catch(err => alert('{{ __("Помилка запиту") }}: ' + err));
});
</script>
@endsection
