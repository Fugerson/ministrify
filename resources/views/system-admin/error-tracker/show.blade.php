@extends('layouts.system-admin')

@section('title', 'Error Details')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('system.errors.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline mb-2 inline-block">&larr; Back to errors</a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white break-all">{{ $errorLog->message }}</h2>
            @if($errorLog->exception_class)
                <span class="inline-block mt-2 px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm rounded-lg font-mono">
                    {{ $errorLog->exception_class }}
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            {{-- Status buttons --}}
            @if($errorLog->status !== 'resolved')
                <form method="POST" action="{{ route('system.errors.update-status', $errorLog) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="resolved">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl">
                        Resolve
                    </button>
                </form>
            @endif
            @if($errorLog->status !== 'ignored')
                <form method="POST" action="{{ route('system.errors.update-status', $errorLog) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="ignored">
                    <button type="submit" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-xl">
                        Ignore
                    </button>
                </form>
            @endif
            @if($errorLog->status !== 'unresolved')
                <form method="POST" action="{{ route('system.errors.update-status', $errorLog) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="unresolved">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl">
                        Reopen
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('system.errors.destroy', $errorLog) }}" onsubmit="return confirm('Delete this error?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</p>
            <p class="mt-1">
                @if($errorLog->status === 'unresolved')
                    <span class="px-3 py-1 bg-red-100 dark:bg-red-600/20 text-red-700 dark:text-red-400 text-sm font-semibold rounded-lg">Unresolved</span>
                @elseif($errorLog->status === 'resolved')
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-600/20 text-green-700 dark:text-green-400 text-sm font-semibold rounded-lg">Resolved</span>
                @else
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-sm font-semibold rounded-lg">Ignored</span>
                @endif
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Occurrences</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $errorLog->occurrences }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">First seen</p>
            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $errorLog->first_seen_at->format('d.m.Y H:i:s') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $errorLog->first_seen_at->diffForHumans() }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Last seen</p>
            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $errorLog->last_seen_at->format('d.m.Y H:i:s') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $errorLog->last_seen_at->diffForHumans() }}</p>
        </div>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-4">Request Info</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">File</dt>
                    <dd class="text-sm font-mono text-gray-900 dark:text-white break-all">{{ $errorLog->short_file }}:{{ $errorLog->line }}</dd>
                </div>
                @if($errorLog->url)
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">URL</dt>
                    <dd class="text-sm font-mono text-gray-900 dark:text-white break-all">{{ $errorLog->method }} {{ $errorLog->url }}</dd>
                </div>
                @endif
                @if($errorLog->user)
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">User</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">#{{ $errorLog->user->id }} {{ $errorLog->user->name }} ({{ $errorLog->user->email }})</dd>
                </div>
                @endif
                @if($errorLog->ip)
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">IP</dt>
                    <dd class="text-sm font-mono text-gray-900 dark:text-white">{{ $errorLog->ip }}</dd>
                </div>
                @endif
                @if($errorLog->user_agent)
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">User Agent</dt>
                    <dd class="text-sm text-gray-900 dark:text-white break-all">{{ $errorLog->user_agent }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-4">Error Message</h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 overflow-x-auto">
                <pre class="text-sm font-mono text-red-600 dark:text-red-400 whitespace-pre-wrap break-all">{{ $errorLog->message }}</pre>
            </div>
        </div>
    </div>

    <!-- Stack Trace -->
    @if($errorLog->trace)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700" x-data="{ expanded: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">Stack Trace</h3>
            <button @click="expanded = !expanded" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                    x-text="expanded ? 'Collapse' : 'Expand'">
            </button>
        </div>
        <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto" :class="expanded ? '' : 'max-h-[300px] overflow-y-auto'">
            <pre class="text-xs font-mono text-gray-300 whitespace-pre-wrap break-all">{{ $errorLog->trace }}</pre>
        </div>
    </div>
    @endif
</div>
@endsection
