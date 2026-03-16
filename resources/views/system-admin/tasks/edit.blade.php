@extends('layouts.system-admin')

@section('title', __('app.sa_edit_task'))

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('system.tasks.index') }}" class="inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back_to_list') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('app.sa_edit_task') }}</h2>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ __('app.sa_created_at') }}: {{ $task->created_at->format('d.m.Y H:i') }}</span>
                @if($task->completed_at)
                    <span class="text-green-600 dark:text-green-400">| {{ __('app.sa_completed_at') }}: {{ $task->completed_at->format('d.m.Y H:i') }}</span>
                @endif
            </div>
        </div>

        <form action="{{ route('system.tasks.update', $task) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @if($task->supportTicket)
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.sa_related_ticket') }}:</p>
                    <a href="{{ route('system.support.show', $task->supportTicket) }}" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">
                        {{ $task->supportTicket->subject }}
                    </a>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_task_title') }} *</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="{{ __('app.sa_task_placeholder') }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_description') }}</label>
                <textarea name="description" rows="4"
                          class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="{{ __('app.sa_task_desc_placeholder') }}">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_type') }} *</label>
                    <select name="type" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="bug" {{ old('type', $task->type) === 'bug' ? 'selected' : '' }}>{{ __('app.sa_bug') }}</option>
                        <option value="feature" {{ old('type', $task->type) === 'feature' ? 'selected' : '' }}>{{ __('app.sa_feature') }}</option>
                        <option value="improvement" {{ old('type', $task->type) === 'improvement' ? 'selected' : '' }}>{{ __('app.sa_improvement') }}</option>
                        <option value="other" {{ old('type', $task->type) === 'other' ? 'selected' : '' }}>{{ __('app.sa_other') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_priority') }} *</label>
                    <select name="priority" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>{{ __('app.sa_low') }}</option>
                        <option value="normal" {{ old('priority', $task->priority) === 'normal' ? 'selected' : '' }}>{{ __('app.sa_normal') }}</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>{{ __('app.sa_high') }}</option>
                        <option value="critical" {{ old('priority', $task->priority) === 'critical' ? 'selected' : '' }}>{{ __('app.sa_critical') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_status') }} *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="backlog" {{ old('status', $task->status) === 'backlog' ? 'selected' : '' }}>{{ __('app.sa_backlog') }}</option>
                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>{{ __('app.sa_todo') }}</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>{{ __('app.sa_in_progress') }}</option>
                        <option value="testing" {{ old('status', $task->status) === 'testing' ? 'selected' : '' }}>{{ __('app.sa_testing') }}</option>
                        <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>{{ __('app.sa_done') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_assignee') }}</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('app.sa_not_assigned') }}</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('assigned_to', $task->assigned_to) == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_deadline') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_related_ticket') }}</label>
                    <select name="support_ticket_id"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('app.sa_none') }}</option>
                        @foreach($supportTickets as $ticket)
                            <option value="{{ $ticket->id }}" {{ old('support_ticket_id', $task->support_ticket_id) == $ticket->id ? 'selected' : '' }}>
                                #{{ $ticket->id }}: {{ Str::limit($ticket->subject, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        {{ __('app.save') }}
                    </button>
                    <a href="{{ route('system.tasks.index') }}" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
                        {{ __('app.cancel') }}
                    </a>
                </div>

                <button type="button"
                        @click="ajaxDelete('{{ route('system.tasks.destroy', $task) }}', @js(__('messages.confirm_delete_task')), null, '{{ route('system.tasks.index') }}')"
                        class="px-4 py-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors">
                    {{ __('app.delete') }}
                </button>
            </div>
        </form>
    </div>

    @if($task->creator)
        <div class="mt-4 text-sm text-gray-400 dark:text-gray-500">
            {{ __('app.sa_created_by') }}: {{ $task->creator->name }}
        </div>
    @endif
</div>
@endsection
