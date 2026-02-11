@extends('layouts.system-admin')

@section('title', 'Активність')

@section('content')
<div class="space-y-6" x-data="activityLog()">
    <!-- Tabs + Delete All -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex gap-1 bg-white dark:bg-gray-800 rounded-xl p-1 border border-gray-200 dark:border-gray-700">
            <a href="{{ route('system.activity-log', ['tab' => 'visits']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'visits' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Навігація
            </a>
            <a href="{{ route('system.activity-log', ['tab' => 'actions']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'actions' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                Журнал дій
            </a>
        </div>

        <button @click="deleteAll()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Очистити все
        </button>
    </div>

    <!-- Bulk Actions Bar -->
    <div x-show="selectedIds.length > 0" x-cloak
         class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-3 flex items-center justify-between">
        <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">
            Обрано: <span x-text="selectedIds.length" class="font-bold"></span>
        </span>
        <div class="flex gap-2">
            <button @click="selectedIds = []; selectAll = false"
                    class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Скасувати
            </button>
            <button @click="deleteSelected()"
                    class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Видалити обрані
            </button>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form x-ref="deleteForm" method="POST" action="{{ route('system.activity-log.delete') }}" class="hidden">
        @csrf
        <input type="hidden" name="type" value="{{ $tab === 'actions' ? 'actions' : 'visits' }}">
        <input type="hidden" name="action" x-ref="deleteAction">
        <input type="hidden" name="ids" x-ref="deleteIds">
    </form>

    @if($tab === 'visits')
        {{-- ===== VISITS TAB ===== --}}

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <input type="hidden" name="tab" value="visits">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук (URL, ім'я, маршрут)..."
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                <select name="church_id"
                        class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Всі церкви</option>
                    @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}
                    </option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ request('from') }}"
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                <input type="date" name="to" value="{{ request('to') }}"
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                <div class="flex gap-2 md:col-span-3 lg:col-span-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">Фільтрувати</button>
                    <a href="{{ route('system.activity-log', ['tab' => 'visits']) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg transition-colors">Скинути</a>
                </div>
            </form>
        </div>

        @if(request('user_id'))
            @php $filteredUser = \App\Models\User::find(request('user_id')); @endphp
            @if($filteredUser)
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-white text-sm font-bold">{{ mb_substr($filteredUser->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">Навігація користувача:</span>
                        <span class="text-sm font-bold text-indigo-900 dark:text-white">{{ $filteredUser->name }}</span>
                    </div>
                </div>
                <a href="{{ route('system.activity-log', ['tab' => 'visits']) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Показати всіх</a>
            </div>
            @endif
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $visits->total() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Всього переглядів</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $visits->pluck('user_id')->unique()->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Користувачів на сторінці</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $visits->pluck('route_name')->unique()->filter()->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Унікальних сторінок</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $visits->pluck('church_id')->unique()->filter()->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Церков на сторінці</p>
            </div>
        </div>

        <!-- Visits Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-3 py-3 text-left w-10">
                                <input type="checkbox" x-model="selectAll"
                                       @change="toggleSelectAll({{ json_encode($visits->pluck('id')->toArray()) }})"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Час</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Користувач</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Церква</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Сторінка</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase hidden lg:table-cell">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($visits as $visit)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                            :class="selectedIds.includes({{ $visit->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                            <td class="px-3 py-3">
                                <input type="checkbox"
                                       :checked="selectedIds.includes({{ $visit->id }})"
                                       @change="toggleId({{ $visit->id }})"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $visit->created_at->format('d.m.Y H:i:s') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('system.activity-log', ['tab' => 'visits', 'user_id' => $visit->user_id]) }}"
                                   class="flex items-center gap-2 hover:opacity-80 transition-opacity group">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ mb_substr($visit->user_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate max-w-[150px]">
                                        {{ $visit->user_name }}
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if($visit->church)
                                    <a href="{{ route('system.churches.show', $visit->church) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded truncate max-w-[150px] inline-block">
                                        {{ $visit->church->name }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $visit->routeLabel() }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-[300px]" title="{{ $visit->url }}">
                                        {{ Str::limit(parse_url($visit->url, PHP_URL_PATH) ?: $visit->url, 60) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                                    {{ $visit->ip_address }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                Записів не знайдено
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($visits->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $visits->links() }}
            </div>
            @endif
        </div>

    @else
        {{-- ===== ACTIONS TAB ===== --}}

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="hidden" name="tab" value="actions">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук..."
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                <select name="church_id"
                        class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Всі церкви</option>
                    @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}
                    </option>
                    @endforeach
                </select>

                <select name="action" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Всі дії</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Створено</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Оновлено</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Видалено</option>
                    <option value="restored" {{ request('action') == 'restored' ? 'selected' : '' }}>Відновлено</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Вхід</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Вихід</option>
                </select>

                @php
                    $modelOptions = [
                        'Person' => 'Члени', 'User' => 'Користувачі', 'Event' => 'Події',
                        'Ministry' => 'Служіння', 'Group' => 'Групи',
                        'Transaction' => 'Транзакції', 'DonationCampaign' => 'Кампанії пожертв',
                        'OnlineDonation' => 'Онлайн-пожертви',
                        'Board' => 'Дошки', 'BoardCard' => 'Картки',
                        'BlogPost' => 'Блог-пости', 'Sermon' => 'Проповіді', 'Song' => 'Пісні',
                        'Gallery' => 'Галереї', 'StaffMember' => 'Співробітники',
                        'EventRegistration' => 'Реєстрації', 'PrayerRequest' => 'Молитовні потреби',
                        'Announcement' => 'Оголошення', 'Assignment' => 'Призначення',
                        'Attendance' => 'Відвідуваність', 'ChurchRole' => 'Церковні ролі',
                        'Church' => 'Церква', 'SupportTicket' => 'Тікети підтримки',
                    ];
                @endphp
                <select name="model" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Всі типи</option>
                    @foreach($modelOptions as $key => $label)
                        <option value="{{ $key }}" {{ request('model') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ request('from') }}"
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                <input type="date" name="to" value="{{ request('to') }}"
                       class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

                <div class="flex gap-2 md:col-span-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">Фільтрувати</button>
                    <a href="{{ route('system.activity-log', ['tab' => 'actions']) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg transition-colors">Скинути</a>
                </div>
            </form>
        </div>

        @if(request('user_id'))
            @php $filteredUser = \App\Models\User::find(request('user_id')); @endphp
            @if($filteredUser)
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-white text-sm font-bold">{{ mb_substr($filteredUser->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">Дії користувача:</span>
                        <span class="text-sm font-bold text-indigo-900 dark:text-white">{{ $filteredUser->name }}</span>
                    </div>
                </div>
                <a href="{{ route('system.activity-log', ['tab' => 'actions']) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Показати всіх</a>
            </div>
            @endif
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $logs->where('action', 'created')->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Створено</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $logs->where('action', 'updated')->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Оновлено</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $logs->where('action', 'deleted')->count() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Видалено</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $logs->total() }}</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Всього</p>
            </div>
        </div>

        <!-- Logs List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <!-- Select All Header -->
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <input type="checkbox" x-model="selectAll"
                       @change="toggleSelectAll({{ json_encode($logs->pluck('id')->toArray()) }})"
                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs text-gray-500 dark:text-gray-400">Обрати все на сторінці</span>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                    @php
                        $color = $log->action_color;
                        $colorClasses = match($color) {
                            'green' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                            'red' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                            'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        };
                        $borderColor = match($color) {
                            'green' => 'border-l-green-500',
                            'blue' => 'border-l-blue-500',
                            'red' => 'border-l-red-500',
                            'purple' => 'border-l-purple-500',
                            default => 'border-l-gray-400',
                        };
                        $changes = $log->changes_summary;
                    @endphp
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 border-l-4 {{ $borderColor }}"
                         :class="selectedIds.includes({{ $log->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''">
                        <div class="flex items-start gap-3">
                            <!-- Checkbox -->
                            <input type="checkbox"
                                   :checked="selectedIds.includes({{ $log->id }})"
                                   @change="toggleId({{ $log->id }})"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 mt-1 flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <!-- Header Row -->
                                <div class="flex flex-wrap items-center gap-3 mb-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $log->created_at->format('d.m.Y H:i:s') }}
                                    </span>

                                    <a href="{{ route('system.activity-log', ['tab' => 'actions', 'user_id' => $log->user_id]) }}"
                                       class="flex items-center gap-1.5 hover:opacity-80">
                                        @if($log->user)
                                            <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                                <span class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400">
                                                    {{ mb_substr($log->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->user_name }}</span>
                                    </a>

                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                        {{ $log->action_label }}
                                    </span>

                                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ $log->model_label }}
                                    </span>

                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $log->model_name }}
                                    </span>

                                    @if($log->church)
                                    <a href="{{ route('system.churches.show', $log->church) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                                        {{ $log->church->name }}
                                    </a>
                                    @endif

                                    @if($log->ip_address)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono ml-auto">
                                        IP: {{ $log->ip_address }}
                                    </span>
                                    @endif
                                </div>

                                <!-- Changes Details -->
                                @if($log->action === 'updated' && count($changes) > 0)
                                    <div class="mt-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3">
                                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Зміни:</div>
                                        <div class="space-y-1.5">
                                            @foreach($changes as $change)
                                                <div class="flex items-start gap-2 text-sm">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 min-w-[120px]">{{ $change['field'] }}:</span>
                                                    <span class="text-red-600 dark:text-red-400 line-through">{{ $change['old'] ?? '—' }}</span>
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                    </svg>
                                                    <span class="text-green-600 dark:text-green-400 font-medium">{{ $change['new'] ?? '—' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($log->action === 'created' && $log->new_values)
                                    <div class="mt-3 bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                        <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase mb-2">Створено з даними:</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                                            @php
                                                $skip = ['id', 'church_id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'email_verified_at'];
                                                $newVals = collect($log->new_values)->except($skip)->filter(fn($v) => $v !== null && $v !== '');
                                            @endphp
                                            @foreach($newVals->take(10) as $field => $value)
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ \App\Models\AuditLog::getFieldLabel($field) }}:</span>
                                                    <span class="text-gray-900 dark:text-white ml-1">
                                                        @if(is_array($value))
                                                            {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                        @elseif(is_bool($value))
                                                            {{ $value ? 'Так' : 'Ні' }}
                                                        @else
                                                            {{ Str::limit((string)$value, 50) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                            @if($newVals->count() > 10)
                                                <div class="text-xs text-gray-500 col-span-2">+{{ $newVals->count() - 10 }} полів</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($log->action === 'deleted' && $log->old_values)
                                    <div class="mt-3 bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                                        <div class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase mb-2">Видалено запис:</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                                            @php
                                                $skip = ['id', 'church_id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'email_verified_at'];
                                                $oldVals = collect($log->old_values)->except($skip)->filter(fn($v) => $v !== null && $v !== '');
                                            @endphp
                                            @foreach($oldVals->take(10) as $field => $value)
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ \App\Models\AuditLog::getFieldLabel($field) }}:</span>
                                                    <span class="text-red-700 dark:text-red-300 ml-1 line-through">
                                                        @if(is_array($value))
                                                            {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                                        @elseif(is_bool($value))
                                                            {{ $value ? 'Так' : 'Ні' }}
                                                        @else
                                                            {{ Str::limit((string)$value, 50) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                            @if($oldVals->count() > 10)
                                                <div class="text-xs text-gray-500 col-span-2">+{{ $oldVals->count() - 10 }} полів</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($log->action === 'login')
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Вхід в систему
                                        @if($log->user_agent)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ Str::limit($log->user_agent, 60) }}</span>
                                        @endif
                                    </div>
                                @elseif($log->action === 'logout')
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Вихід з системи
                                    </div>
                                @endif

                                @if($log->notes)
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                                        {{ $log->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Записів не знайдено
                    </div>
                @endforelse
            </div>

            @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
function activityLog() {
    return {
        selectedIds: [],
        selectAll: false,

        toggleSelectAll(ids) {
            if (this.selectAll) {
                this.selectedIds = [...ids];
            } else {
                this.selectedIds = [];
            }
        },

        toggleId(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
            this.selectAll = false;
        },

        deleteSelected() {
            if (!confirm(`Видалити ${this.selectedIds.length} записів?`)) return;
            this.$refs.deleteAction.value = 'selected';
            this.$refs.deleteIds.value = JSON.stringify(this.selectedIds);
            this.$refs.deleteForm.submit();
        },

        deleteAll() {
            const tab = '{{ $tab }}';
            const label = tab === 'visits' ? 'навігації' : 'журналу дій';
            if (!confirm(`Видалити ВСІ записи ${label}? Ця дія незворотна.`)) return;
            this.$refs.deleteAction.value = 'all';
            this.$refs.deleteForm.submit();
        }
    }
}
</script>
@endpush
@endsection
