@extends('layouts.app')

@section('title', $ministry->name . ' - ' . __('app.goals_and_tasks_title'))

@section('actions')
<a href="{{ route('ministries.show', $ministry) }}"
   class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    {{ __('app.back') }}
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="goalsManager()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3">
            @if($ministry->color)
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $ministry->color }}"></div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ministry->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ __('app.goals_and_tasks_title') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_goals'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.total_goals_stat') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['active_goals'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.active_goals_stat') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed_goals'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.completed_goals_stat') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_tasks'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.total_tasks_stat') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.completed_tasks_stat') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <p class="text-2xl font-bold text-red-600">{{ $stats['overdue_tasks'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.overdue_tasks_stat') }}</p>
        </div>
    </div>

    <!-- Vision Section -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl shadow-sm p-6 border border-indigo-100 dark:border-indigo-800">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.vision_section') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.where_we_heading_goal') }}</p>
                </div>
            </div>
            @can('contribute-ministry', $ministry)
            <button @click="editingVision = !editingVision" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 text-sm">
                <span x-text="editingVision ? @js(__('app.cancel')) : @js(__('app.edit'))"></span>
            </button>
            @endcan
        </div>

        <div x-show="!editingVision">
            @if($ministry->vision)
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $ministry->vision }}</div>
            @else
                <p class="text-gray-500 dark:text-gray-400 italic">{{ __('app.vision_not_defined') }}</p>
            @endif
        </div>

        @can('contribute-ministry', $ministry)
        <form x-show="editingVision" @submit.prevent="submitVision($refs.visionForm)" x-ref="visionForm" class="space-y-3">
            <textarea name="vision" rows="4" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="{{ __('app.describe_vision_placeholder') }}">{{ $ministry->vision }}</textarea>
            <div class="flex justify-end">
                <button type="submit" :disabled="savingVision" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                    <span x-show="!savingVision">{{ __('app.save') }}</span>
                    <span x-show="savingVision">{{ __('app.saving') }}</span>
                </button>
            </div>
        </form>
        @endcan
    </div>

    <!-- Goals and Tasks -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.goals_heading') }}</h2>
            @can('contribute-ministry', $ministry)
            <div class="flex gap-2">
                <button @click="showTaskModal = true; taskForm.goal_id = ''" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.add_task_btn') }}
                </button>
                <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.add_goal_btn') }}
                </button>
            </div>
            @endcan
        </div>

        <div class="p-6">
            @if($ministry->goals->count() > 0)
                <div class="space-y-6">
                    @foreach($ministry->goals as $goal)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <!-- Goal Header -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                        @if($goal->status === 'active') bg-blue-100 dark:bg-blue-900/30
                                        @elseif($goal->status === 'completed') bg-green-100 dark:bg-green-900/30
                                        @elseif($goal->status === 'on_hold') bg-yellow-100 dark:bg-yellow-900/30
                                        @else bg-red-100 dark:bg-red-900/30 @endif">
                                        @if($goal->status === 'completed')
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-{{ $goal->status_color }}-600 dark:text-{{ $goal->status_color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $goal->title }}</h3>
                                        @if($goal->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $goal->description }}</p>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-{{ $goal->status_color }}-100 dark:bg-{{ $goal->status_color }}-900/30 text-{{ $goal->status_color }}-700 dark:text-{{ $goal->status_color }}-300">
                                                {{ $goal->status_label }}
                                            </span>
                                            @if($goal->period)
                                                <span class="text-gray-500 dark:text-gray-400">{{ $goal->period }}</span>
                                            @endif
                                            @if($goal->due_date)
                                                <span class="text-gray-500 dark:text-gray-400 @if($goal->is_overdue) text-red-600 dark:text-red-400 @endif">
                                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $goal->due_date->format('d.m.Y') }}
                                                </span>
                                            @endif
                                            @if($goal->priority)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg
                                                    @if($goal->priority === 'high') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300
                                                    @elseif($goal->priority === 'medium') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 @endif">
                                                    {{ $goal->priority_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <!-- Progress -->
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ $goal->calculated_progress }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $goal->calculated_progress }}%</span>
                                    </div>
                                    @can('contribute-ministry', $ministry)
                                    <div class="flex items-center gap-1">
                                        <button @click="editGoal({{ $goal->id }}, {{ json_encode([
                                            'title' => $goal->title,
                                            'description' => $goal->description,
                                            'period' => $goal->period,
                                            'due_date' => $goal->due_date?->format('Y-m-d'),
                                            'priority' => $goal->priority,
                                            'status' => $goal->status
                                        ]) }})" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="ajaxDelete('{{ route('ministries.goals.destroy', [$ministry, $goal]) }}', @js( __('messages.confirm_delete_goal') ), () => Livewire.navigate(window.location.href))" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @endcan
                                </div>
                            </div>

                            <!-- Tasks -->
                            @if($goal->tasks->count() > 0)
                                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($goal->tasks as $task)
                                        <div class="p-4 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                            @can('contribute-ministry', $ministry)
                                            <button type="button" @click="ajaxAction('{{ route('ministries.tasks.toggle', [$ministry, $task]) }}', 'POST').then(() => Livewire.navigate(window.location.href))" class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors
                                                @if($task->is_done) border-green-500 bg-green-500
                                                @else border-gray-300 dark:border-gray-600 hover:border-primary-500 @endif">
                                                @if($task->is_done)
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </button>
                                            @else
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center
                                                @if($task->is_done) border-green-500 bg-green-500
                                                @else border-gray-300 dark:border-gray-600 @endif">
                                                @if($task->is_done)
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            @endcan
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-white @if($task->is_done) line-through text-gray-500 dark:text-gray-400 @endif">{{ $task->title }}</p>
                                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs">
                                                    @if($task->assignee)
                                                        <span class="inline-flex items-center text-gray-500 dark:text-gray-400">
                                                            @if($task->assignee->photo)
                                                                <img src="{{ Storage::url($task->assignee->photo) }}" class="w-4 h-4 rounded-full mr-1" alt="">
                                                            @endif
                                                            {{ $task->assignee->full_name }}
                                                        </span>
                                                    @endif
                                                    @if($task->due_date)
                                                        <span class="@if($task->is_overdue) text-red-600 dark:text-red-400 @else text-gray-500 dark:text-gray-400 @endif">
                                                            {{ $task->due_date->format('d.m.Y') }}
                                                        </span>
                                                    @endif
                                                    @if($task->priority && $task->priority !== 'medium')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded
                                                            @if($task->priority === 'high') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300
                                                            @else bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 @endif">
                                                            {{ $task->priority_label }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @can('contribute-ministry', $ministry)
                                            <div class="flex items-center gap-1">
                                                <button @click="editTask({{ $task->id }}, {{ json_encode([
                                                    'title' => $task->title,
                                                    'description' => $task->description,
                                                    'goal_id' => $task->goal_id,
                                                    'assigned_to' => $task->assigned_to,
                                                    'due_date' => $task->due_date?->format('Y-m-d'),
                                                    'priority' => $task->priority,
                                                    'status' => $task->status
                                                ]) }})" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="ajaxDelete('{{ route('ministries.tasks.destroy', [$ministry, $task]) }}', @js( __('messages.confirm_delete_task') ), () => Livewire.navigate(window.location.href))" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Add task to goal -->
                            @can('contribute-ministry', $ministry)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700">
                                <button @click="showTaskModal = true; taskForm.goal_id = {{ $goal->id }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('app.add_task_btn') }}
                                </button>
                            </div>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('app.no_goals_yet') }}</p>
                    @can('contribute-ministry', $ministry)
                    <button @click="showGoalModal = true; resetGoalForm()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.create_first_goal') }}
                    </button>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <!-- Goal Modal -->
    @can('contribute-ministry', $ministry)
    <div x-show="showGoalModal" class="fixed inset-0 z-50" style="display: none;">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showGoalModal = false"></div>
        <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingGoalId ? @js(__('app.edit_goal_title')) : @js(__('app.new_goal_title'))"></h3>
                <button @click="showGoalModal = false" class="p-2 text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitGoal($refs.goalForm)" x-ref="goalForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.name_required') }}</label>
                    <input type="text" name="title" x-model="goalForm.title" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <template x-if="goalErrors.title"><p class="mt-1 text-sm text-red-500" x-text="goalErrors.title[0]"></p></template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.description') }}</label>
                    <textarea name="description" x-model="goalForm.description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.period_field') }}</label>
                        @php $y = date('Y'); @endphp
                        <select name="period" x-model="goalForm.period" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">{{ __('app.not_specified') }}</option>
                            @foreach([$y, $y + 1] as $year)
                                <option value="Q1 {{ $year }}">Q1 {{ $year }}</option>
                                <option value="Q2 {{ $year }}">Q2 {{ $year }}</option>
                                <option value="Q3 {{ $year }}">Q3 {{ $year }}</option>
                                <option value="Q4 {{ $year }}">Q4 {{ $year }}</option>
                                <option value="H1 {{ $year }}">H1 {{ $year }}</option>
                                <option value="H2 {{ $year }}">H2 {{ $year }}</option>
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.deadline_field') }}</label>
                        <input type="date" name="due_date" x-model="goalForm.due_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.priority_field') }}</label>
                        <select name="priority" x-model="goalForm.priority" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="low">{{ __('app.priority_low') }}</option>
                            <option value="medium">{{ __('app.priority_medium') }}</option>
                            <option value="high">{{ __('app.priority_high') }}</option>
                        </select>
                    </div>
                    <div x-show="editingGoalId">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.status_field') }}</label>
                        <select name="status" x-model="goalForm.status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="active">{{ __('app.goal_status_active') }}</option>
                            <option value="completed">{{ __('app.goal_status_completed') }}</option>
                            <option value="on_hold">{{ __('app.goal_on_hold') }}</option>
                            <option value="cancelled">{{ __('app.goal_cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showGoalModal = false" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="savingGoal" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                        <span x-show="!savingGoal" x-text="editingGoalId ? @js(__('app.save')) : @js(__('app.create'))"></span>
                        <span x-show="savingGoal">{{ __('app.saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <!-- Task Modal -->
    @can('contribute-ministry', $ministry)
    <div x-show="showTaskModal" class="fixed inset-0 z-50" style="display: none;">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showTaskModal = false"></div>
        <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingTaskId ? @js(__('app.edit_task_title')) : @js(__('app.new_task_title'))"></h3>
                <button @click="showTaskModal = false" class="p-2 text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submitTask($refs.taskForm)" x-ref="taskForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.name_required') }}</label>
                    <input type="text" name="title" x-model="taskForm.title" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <template x-if="taskErrors.title"><p class="mt-1 text-sm text-red-500" x-text="taskErrors.title[0]"></p></template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.description') }}</label>
                    <textarea name="description" x-model="taskForm.description" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.goal_label') }}</label>
                    <select name="goal_id" x-model="taskForm.goal_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">{{ __('app.without_goal') }}</option>
                        @foreach($ministry->goals as $goal)
                            <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.assignee_field') }}</label>
                        <select name="assigned_to" x-model="taskForm.assigned_to" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="">{{ __('app.not_assigned_option') }}</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.deadline_field') }}</label>
                        <input type="date" name="due_date" x-model="taskForm.due_date" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.priority_field') }}</label>
                        <select name="priority" x-model="taskForm.priority" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="low">{{ __('app.priority_low') }}</option>
                            <option value="medium">{{ __('app.priority_medium') }}</option>
                            <option value="high">{{ __('app.priority_high') }}</option>
                        </select>
                    </div>
                    <div x-show="editingTaskId">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.status_field') }}</label>
                        <select name="status" x-model="taskForm.status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                            <option value="todo">{{ __('app.task_status_todo') }}</option>
                            <option value="in_progress">{{ __('app.task_status_in_progress') }}</option>
                            <option value="done">{{ __('app.task_status_done') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showTaskModal = false" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" :disabled="savingTask" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                        <span x-show="!savingTask" x-text="editingTaskId ? @js(__('app.save')) : @js(__('app.create'))"></span>
                        <span x-show="savingTask">{{ __('app.saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>

@push('scripts')
<script>
function goalsManager() {
    const goalStoreUrl = '{{ route('ministries.goals.store', $ministry) }}';
    const goalUpdateUrlTemplate = '{{ route('ministries.goals.update', [$ministry, ':id']) }}';
    const taskStoreUrl = '{{ route('ministries.tasks.store', $ministry) }}';
    const taskUpdateUrlTemplate = '{{ route('ministries.tasks.update', [$ministry, ':id']) }}';
    const visionUrl = '{{ route('ministries.vision.update', $ministry) }}';
    const _t = {
        save_error: @json(__('app.save_error')),
        saved: @json(__('app.saved_msg')),
        connection_error: @json(__('app.connection_error')),
        check_form: @json(__('app.check_form_errors')),
    };

    async function ajaxSubmit(url, method, formEl) {
        const formData = new FormData(formEl);
        if (['PUT', 'PATCH', 'DELETE'].includes(method)) {
            formData.append('_method', method);
        }
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await response.json().catch(() => ({}));
        return { response, data };
    }

    return {
        editingVision: false,
        savingVision: false,
        showGoalModal: false,
        showTaskModal: false,
        editingGoalId: null,
        editingTaskId: null,
        savingGoal: false,
        savingTask: false,
        goalErrors: {},
        taskErrors: {},
        goalForm: {
            title: '',
            description: '',
            period: '',
            due_date: '',
            priority: 'medium',
            status: 'active'
        },
        taskForm: {
            title: '',
            description: '',
            goal_id: '',
            assigned_to: '',
            due_date: '',
            priority: 'medium',
            status: 'todo'
        },
        resetGoalForm() {
            this.editingGoalId = null;
            this.goalErrors = {};
            this.goalForm = {
                title: '',
                description: '',
                period: '',
                due_date: '',
                priority: 'medium',
                status: 'active'
            };
        },
        resetTaskForm() {
            this.editingTaskId = null;
            this.taskErrors = {};
            this.taskForm = {
                title: '',
                description: '',
                goal_id: '',
                assigned_to: '',
                due_date: '',
                priority: 'medium',
                status: 'todo'
            };
        },
        editGoal(id, data) {
            this.editingGoalId = id;
            this.goalErrors = {};
            this.goalForm = { ...data };
            this.showGoalModal = true;
        },
        editTask(id, data) {
            this.editingTaskId = id;
            this.taskErrors = {};
            this.taskForm = { ...data };
            this.showTaskModal = true;
        },
        async submitVision(formEl) {
            if (this.savingVision) return;
            this.savingVision = true;
            try {
                const { response, data } = await ajaxSubmit(visionUrl, 'POST', formEl);
                if (!response.ok) {
                    showToast('error', data.message || _t.save_error);
                    this.savingVision = false;
                    return;
                }
                showToast('success', data.message || _t.saved);
                this.editingVision = false;
                this.savingVision = false;
            } catch (e) {
                showToast('error', _t.connection_error);
                this.savingVision = false;
            }
        },
        async submitGoal(formEl) {
            if (this.savingGoal) return;
            this.savingGoal = true;
            this.goalErrors = {};
            const url = this.editingGoalId
                ? goalUpdateUrlTemplate.replace(':id', this.editingGoalId)
                : goalStoreUrl;
            const method = this.editingGoalId ? 'PUT' : 'POST';
            try {
                const { response, data } = await ajaxSubmit(url, method, formEl);
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.goalErrors = data.errors;
                        showToast('error', data.message || _t.check_form);
                    } else {
                        showToast('error', data.message || _t.save_error);
                    }
                    this.savingGoal = false;
                    return;
                }
                showToast('success', data.message || _t.saved);
                this.showGoalModal = false;
                setTimeout(() => Livewire.navigate(window.location.href), 200);
            } catch (e) {
                showToast('error', _t.connection_error);
                this.savingGoal = false;
            }
        },
        async submitTask(formEl) {
            if (this.savingTask) return;
            this.savingTask = true;
            this.taskErrors = {};
            const url = this.editingTaskId
                ? taskUpdateUrlTemplate.replace(':id', this.editingTaskId)
                : taskStoreUrl;
            const method = this.editingTaskId ? 'PUT' : 'POST';
            try {
                const { response, data } = await ajaxSubmit(url, method, formEl);
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.taskErrors = data.errors;
                        showToast('error', data.message || _t.check_form);
                    } else {
                        showToast('error', data.message || _t.save_error);
                    }
                    this.savingTask = false;
                    return;
                }
                showToast('success', data.message || _t.saved);
                this.showTaskModal = false;
                setTimeout(() => Livewire.navigate(window.location.href), 200);
            } catch (e) {
                showToast('error', _t.connection_error);
                this.savingTask = false;
            }
        }
    }
}
</script>
@endpush
@endsection
