@extends('layouts.system-admin')

@section('title', 'Журнал дій')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Вхід</option>
                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Вихід</option>
            </select>

            <input type="date" name="from" value="{{ request('from') }}"
                   class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

            <input type="date" name="to" value="{{ request('to') }}"
                   class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Фільтрувати</button>
                <a href="{{ route('system.audit-logs') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white rounded-lg">Скинути</a>
            </div>
        </form>
    </div>

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
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 border-l-4 {{ $borderColor }}">
                    <!-- Header Row -->
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <!-- Timestamp -->
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                            {{ $log->created_at->format('d.m.Y H:i:s') }}
                        </span>

                        <!-- User -->
                        <div class="flex items-center gap-1.5">
                            @if($log->user)
                                <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <span class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ mb_substr($log->user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->user_name }}</span>
                        </div>

                        <!-- Action badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                            {{ $log->action_label }}
                        </span>

                        <!-- Model type -->
                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                            {{ $log->model_label }}
                        </span>

                        <!-- Model name -->
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $log->model_name }}
                        </span>

                        <!-- Church -->
                        @if($log->church)
                        <a href="{{ route('system.churches.show', $log->church) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                            {{ $log->church->name }}
                        </a>
                        @endif

                        <!-- IP Address -->
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

                    <!-- Notes -->
                    @if($log->notes)
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                            {{ $log->notes }}
                        </div>
                    @endif
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
</div>
@endsection
