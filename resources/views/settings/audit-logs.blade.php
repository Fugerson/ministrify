@extends('layouts.app')

@section('title', 'Журнал дій')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Журнал дій</h1>
            <p class="text-gray-500 dark:text-gray-400">Детальна історія всіх змін у системі</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Пошук..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
            </div>
            <div>
                <select name="action" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі дії</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ match($action) {
                                'created' => 'Створено',
                                'updated' => 'Оновлено',
                                'deleted' => 'Видалено',
                                'restored' => 'Відновлено',
                                'login' => 'Вхід',
                                'logout' => 'Вихід',
                                default => $action
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="model" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі типи</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ match($model) {
                                'Person' => 'Члени',
                                'User' => 'Користувачі',
                                'Event' => 'Події',
                                'Ministry' => 'Служіння',
                                'Group' => 'Групи',
                                'Transaction' => 'Транзакції',
                                'Expense' => 'Витрати',
                                'Income' => 'Доходи',
                                'DonationCampaign' => 'Кампанії пожертв',
                                'OnlineDonation' => 'Онлайн-пожертви',
                                'Board' => 'Дошки',
                                'BoardCard' => 'Картки',
                                'BoardColumn' => 'Колонки дошки',
                                'BoardEpic' => 'Епіки',
                                'Assignment' => 'Призначення',
                                'Attendance' => 'Відвідуваність',
                                'GroupAttendance' => 'Відвідуваність груп',
                                'BlogPost' => 'Блог-пости',
                                'BlogCategory' => 'Категорії блогу',
                                'Sermon' => 'Проповіді',
                                'SermonSeries' => 'Серії проповідей',
                                'Song' => 'Пісні',
                                'Gallery' => 'Галереї',
                                'GalleryPhoto' => 'Фото галереї',
                                'Testimonial' => 'Свідчення',
                                'Faq' => 'FAQ',
                                'StaffMember' => 'Співробітники',
                                'EventRegistration' => 'Реєстрації',
                                'PrayerRequest' => 'Молитовні потреби',
                                'Announcement' => 'Оголошення',
                                'ChurchRole' => 'Церковні ролі',
                                'ChurchRolePermission' => 'Дозволи ролей',
                                'Position' => 'Позиції',
                                'Tag' => 'Теги',
                                'MinistryTask' => 'Завдання служіння',
                                'MinistryGoal' => 'Цілі служіння',
                                'MinistryMeeting' => 'Зустрічі служіння',
                                'MinistryBudget' => 'Бюджети служіння',
                                'MinistryType' => 'Типи служіння',
                                'ChecklistTemplate' => 'Шаблони чеклістів',
                                'EventTaskTemplate' => 'Шаблони завдань',
                                'ServicePlanTemplate' => 'Шаблони плану служби',
                                'MessageTemplate' => 'Шаблони повідомлень',
                                'TransactionCategory' => 'Категорії фінансів',
                                'ExpenseCategory' => 'Категорії витрат',
                                'IncomeCategory' => 'Категорії доходів',
                                'Resource' => 'Ресурси',
                                'BlockoutDate' => 'Блокування дат',
                                'FamilyRelationship' => 'Сімейні звʼязки',
                                'Church' => 'Церква',
                                'SupportTicket' => 'Тікети підтримки',
                                default => $model
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">Всі користувачі</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="Від">
            </div>
            <div class="flex gap-2">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="До">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Logs List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
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
                                <div class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400">
                                        {{ mb_substr($log->user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $log->user_name }}</span>
                        </div>

                        <!-- Action badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->action_icon }}"/>
                            </svg>
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
                <div class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Записів не знайдено
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Легенда:</div>
        <div class="flex flex-wrap gap-4 text-sm">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-300">Створено</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-blue-500"></span>
                <span class="text-gray-600 dark:text-gray-300">Оновлено</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-red-500"></span>
                <span class="text-gray-600 dark:text-gray-300">Видалено</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-purple-500"></span>
                <span class="text-gray-600 dark:text-gray-300">Відновлено</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-gray-400"></span>
                <span class="text-gray-600 dark:text-gray-300">Інше (вхід/вихід)</span>
            </span>
        </div>
    </div>
</div>
@endsection
