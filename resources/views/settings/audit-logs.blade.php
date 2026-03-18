@extends('layouts.app')

@section('title', __('app.audit_log_title'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.audit_log_title') }}</h1>
        <p class="text-gray-500 dark:text-gray-400">{{ __('app.audit_log_description') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4">
            <div class="col-span-2 sm:col-span-3 md:col-span-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('app.search_placeholder') }}"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
            </div>
            <div>
                <select name="action" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">{{ __('app.all_actions') }}</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ (new \App\Models\AuditLog(['action' => $action]))->action_label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="model" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">{{ __('app.all_types') }}</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ match($model) {
                                'Person' => __('app.model_person'),
                                'User' => __('app.model_user'),
                                'Event' => __('app.model_event'),
                                'Ministry' => __('app.model_ministry'),
                                'Group' => __('app.model_group'),
                                'Transaction' => __('app.model_transaction'),
                                'Expense' => __('app.model_expense'),
                                'Income' => __('app.model_income'),
                                'DonationCampaign' => __('app.model_donation_campaign'),
                                'OnlineDonation' => __('app.model_online_donation'),
                                'Board' => __('app.model_board'),
                                'BoardCard' => __('app.model_board_card'),
                                'BoardColumn' => __('app.model_board_column'),
                                'BoardEpic' => __('app.model_board_epic'),
                                'Assignment' => __('app.model_assignment'),
                                'Attendance' => __('app.model_attendance'),
                                'GroupAttendance' => __('app.model_group_attendance'),
                                'BlogPost' => __('app.model_blog_post'),
                                'BlogCategory' => __('app.model_blog_category'),
                                'Sermon' => __('app.model_sermon'),
                                'SermonSeries' => __('app.model_sermon_series'),
                                'Song' => __('app.model_song'),
                                'Gallery' => __('app.model_gallery'),
                                'GalleryPhoto' => __('app.model_gallery_photo'),
                                'Testimonial' => __('app.model_testimonial'),
                                'Faq' => __('app.model_faq'),
                                'StaffMember' => __('app.model_staff_member'),
                                'EventRegistration' => __('app.model_event_registration'),
                                'PrayerRequest' => __('app.model_prayer_request'),
                                'Announcement' => __('app.model_announcement'),
                                'ChurchRole' => __('app.model_church_role'),
                                'ChurchRolePermission' => __('app.model_church_role_permission'),
                                'Position' => __('app.model_position'),
                                'Tag' => __('app.model_tag'),
                                'MinistryTask' => __('app.model_ministry_task'),
                                'MinistryGoal' => __('app.model_ministry_goal'),
                                'MinistryMeeting' => __('app.model_ministry_meeting'),
                                'MinistryBudget' => __('app.model_ministry_budget'),
                                'MinistryType' => __('app.model_ministry_type'),
                                'ChecklistTemplate' => __('app.model_checklist_template'),
                                'EventTaskTemplate' => __('app.model_event_task_template'),
                                'ServicePlanTemplate' => __('app.model_service_plan_template'),
                                'MessageTemplate' => __('app.model_message_template'),
                                'TransactionCategory' => __('app.model_transaction_category'),
                                'ExpenseCategory' => __('app.model_expense_category'),
                                'IncomeCategory' => __('app.model_income_category'),
                                'Resource' => __('app.model_resource'),
                                'BlockoutDate' => __('app.model_blockout_date'),
                                'FamilyRelationship' => __('app.model_family_relationship'),
                                'Church' => __('app.model_church'),
                                'SupportTicket' => __('app.model_support_ticket'),
                                default => $model
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="user" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm">
                    <option value="">{{ __('app.all_users') }}</option>
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
                       placeholder="{{ __('app.date_from') }}">
            </div>
            <div class="flex gap-2">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 min-w-0 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm"
                       placeholder="{{ __('app.date_to') }}">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 shrink-0">
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
                        'green', 'emerald' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                        'blue', 'sky' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                        'purple', 'violet' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                        'teal', 'cyan' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
                        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
                        'lime' => 'bg-lime-100 text-lime-700 dark:bg-lime-900/50 dark:text-lime-300',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    };
                    $borderColor = match($color) {
                        'green', 'emerald' => 'border-l-green-500',
                        'blue', 'sky' => 'border-l-blue-500',
                        'red' => 'border-l-red-500',
                        'purple', 'violet' => 'border-l-purple-500',
                        'amber' => 'border-l-amber-500',
                        'teal', 'cyan' => 'border-l-teal-500',
                        'indigo' => 'border-l-indigo-500',
                        'lime' => 'border-l-lime-500',
                        default => 'border-l-gray-400',
                    };
                    $changes = $log->changes_summary;
                @endphp
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 border-l-4 {{ $borderColor }}">
                    <!-- Header Row -->
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-1.5 min-w-0">
                            @if($log->user)
                                <div class="w-5 h-5 shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-[10px] font-medium text-primary-600 dark:text-primary-400">
                                        {{ mb_substr($log->user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $log->user_name }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 dark:text-gray-500 hidden sm:inline-flex" title="{{ $log->user_agent }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->device_icon }}"/></svg>
                                {{ $log->device_label }}
                            </span>
                            @if($log->ip_address)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono hidden sm:inline">
                                {{ $log->ip_address }}
                            </span>
                            @endif
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono whitespace-nowrap">
                                {{ $log->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>
                    </div>

                    <!-- Tags row -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <!-- Action badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->action_icon }}"/>
                            </svg>
                            {{ $log->action_label }}
                        </span>

                        @if(!in_array($log->action, ['login', 'logout']))
                            <!-- Model type -->
                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                {{ $log->model_label }}
                            </span>

                            <!-- Model name -->
                            <span class="text-sm font-semibold text-gray-900 dark:text-white break-all">
                                {{ Str::limit($log->model_name, 40) }}
                            </span>
                        @endif

                        {{-- For "updated" show compact summary of changed fields --}}
                        @if($log->action === 'updated' && count($changes) > 0)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                ({{ implode(', ', array_map(fn($c) => $c['field'], array_slice($changes, 0, 3))) }}{{ count($changes) > 3 ? ' +' . (count($changes) - 3) : '' }})
                            </span>
                        @endif
                    </div>

                    <!-- Changes Details -->
                    @if($log->action === 'updated' && count($changes) > 0)
                        <div class="mt-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-3 overflow-hidden">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('app.changes_label') }}:</div>
                            <div class="space-y-2">
                                @foreach($changes as $change)
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $change['field'] }}:</span>
                                        <div class="mt-0.5 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                            <span class="text-red-600 dark:text-red-400 line-through break-all">{{ Str::limit($change['old'] ?? '—', 60) }}</span>
                                            <svg class="w-3 h-3 text-gray-400 shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                            </svg>
                                            <span class="text-green-600 dark:text-green-400 font-medium break-all">{{ Str::limit($change['new'] ?? '—', 60) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($log->action === 'created' && $log->new_values)
                        <div class="mt-3 bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                            <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase mb-2">{{ __('app.created_with_data') }}:</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @php
                                    $skip = ['id', 'church_id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'email_verified_at', 'checkin_token', 'google_event_id', 'google_calendar_id', 'google_id', 'calendar_token', 'telegram_bot_token', 'visible_sections'];
                                    $newVals = collect($log->new_values)->except($skip)->filter(fn($v) => $v !== null && $v !== '');
                                @endphp
                                @foreach($newVals->take(10) as $field => $value)
                                    <div class="text-sm min-w-0">
                                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ \App\Models\AuditLog::getFieldLabel($field) }}:</span>
                                        <span class="text-gray-900 dark:text-white ml-1 break-all">
                                            {{ Str::limit(\App\Models\AuditLog::formatValueForDisplay($value, $field) ?? '—', 50) }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($newVals->count() > 10)
                                    <div class="text-xs text-gray-500 sm:col-span-2">+{{ $newVals->count() - 10 }} {{ __('app.fields_more') }}</div>
                                @endif
                            </div>
                        </div>
                    @elseif($log->action === 'deleted' && $log->old_values)
                        <div class="mt-3 bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                            <div class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase mb-2">{{ __('app.deleted_record') }}:</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @php
                                    $skip = ['id', 'church_id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'email_verified_at', 'checkin_token', 'google_event_id', 'google_calendar_id', 'google_id', 'calendar_token', 'telegram_bot_token', 'visible_sections'];
                                    $oldVals = collect($log->old_values)->except($skip)->filter(fn($v) => $v !== null && $v !== '');
                                @endphp
                                @foreach($oldVals->take(10) as $field => $value)
                                    <div class="text-sm min-w-0">
                                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ \App\Models\AuditLog::getFieldLabel($field) }}:</span>
                                        <span class="text-red-700 dark:text-red-300 ml-1 line-through break-all">
                                            {{ Str::limit(\App\Models\AuditLog::formatValueForDisplay($value, $field) ?? '—', 50) }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($oldVals->count() > 10)
                                    <div class="text-xs text-gray-500 sm:col-span-2">+{{ $oldVals->count() - 10 }} {{ __('app.fields_more') }}</div>
                                @endif
                            </div>
                        </div>
                    @elseif($log->action === 'login')
                        <div class="mt-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ __('app.login_to_system') }}</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs {{ $log->device_type === 'mobile' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log->device_icon }}"/></svg>
                                {{ $log->device_label }} · {{ $log->browser_name }}
                            </span>
                        </div>
                    @elseif($log->action === 'logout')
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('app.logout_from_system') }}
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
                    {{ __('app.no_records') }}
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
        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('app.legend') }}:</div>
        <div class="flex flex-wrap gap-4 text-sm">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ __('app.action_created') }}</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-blue-500"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ __('app.action_updated') }}</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-red-500"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ __('app.action_deleted') }}</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-purple-500"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ __('app.action_restored') }}</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-gray-400"></span>
                <span class="text-gray-600 dark:text-gray-300">{{ __('app.action_other_login_logout') }}</span>
            </span>
        </div>
    </div>
</div>
@endsection
