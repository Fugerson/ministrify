@extends('layouts.system-admin')

@section('title', __('app.sa_new_task'))

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('system.tasks.index') }}" class="inline-flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('app.back_to_list') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ __('app.sa_new_task') }}</h2>

        <form action="{{ route('system.tasks.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($supportTicket)
                <input type="hidden" name="support_ticket_id" value="{{ $supportTicket->id }}">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.sa_related_ticket') }}:</p>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $supportTicket->subject }}</p>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_task_title') }} *</label>
                <input type="text" name="title" value="{{ old('title', $supportTicket?->subject) }}" required
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
                          placeholder="{{ __('app.sa_task_desc_placeholder') }}">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_type') }} *</label>
                    <select name="type" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="bug" {{ old('type', $supportTicket?->category === 'bug' ? 'bug' : '') === 'bug' ? 'selected' : '' }}>{{ __('app.sa_bug') }}</option>
                        <option value="feature" {{ old('type', $supportTicket?->category === 'feature' ? 'feature' : '') === 'feature' ? 'selected' : '' }}>{{ __('app.sa_feature') }}</option>
                        <option value="improvement" {{ old('type') === 'improvement' ? 'selected' : '' }}>{{ __('app.sa_improvement') }}</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>{{ __('app.sa_other') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_priority') }} *</label>
                    <select name="priority" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('app.sa_low') }}</option>
                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>{{ __('app.sa_normal') }}</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('app.sa_high') }}</option>
                        <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>{{ __('app.sa_critical') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_status') }} *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="backlog" {{ old('status', 'backlog') === 'backlog' ? 'selected' : '' }}>{{ __('app.sa_backlog') }}</option>
                        <option value="todo" {{ old('status') === 'todo' ? 'selected' : '' }}>{{ __('app.sa_todo') }}</option>
                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>{{ __('app.sa_in_progress') }}</option>
                        <option value="testing" {{ old('status') === 'testing' ? 'selected' : '' }}>{{ __('app.sa_testing') }}</option>
                        <option value="done" {{ old('status') === 'done' ? 'selected' : '' }}>{{ __('app.sa_done') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_assignee') }}</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">{{ __('app.sa_not_assigned') }}</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('assigned_to') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('app.sa_deadline') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    {{ __('app.sa_create_task') }}
                </button>
                <a href="{{ route('system.tasks.index') }}" class="px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
