@extends('layouts.system-admin')

@section('title', 'Telegram Log')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">All messages</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['outgoing']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Outgoing</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['incoming']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Incoming</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['today']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Today</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Message text or person name..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Church</label>
                <select name="church_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All churches</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Direction</label>
                <select name="direction" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All</option>
                    <option value="outgoing" {{ request('direction') === 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                    <option value="incoming" {{ request('direction') === 'incoming' ? 'selected' : '' }}>Incoming</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'church_id', 'direction', 'from', 'to']))
                <a href="{{ route('system.telegram-log') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Messages Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Time</th>
                        <th class="px-4 py-3 text-left">Direction</th>
                        <th class="px-4 py-3 text-left">Church</th>
                        <th class="px-4 py-3 text-left">Person</th>
                        <th class="px-4 py-3 text-left">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($messages as $msg)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ $msg->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($msg->direction === 'outgoing')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    OUT
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    IN
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300">
                            {{ $msg->church?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-gray-900 dark:text-white font-medium">{{ $msg->person?->full_name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="max-w-md text-gray-700 dark:text-gray-300 whitespace-pre-line text-xs leading-relaxed" x-data="{ expanded: false }">
                                <div :class="expanded ? '' : 'line-clamp-3'">{{ $msg->message }}</div>
                                @if(mb_strlen($msg->message) > 200)
                                <button @click="expanded = !expanded" class="text-indigo-600 dark:text-indigo-400 text-xs mt-1 hover:underline" x-text="expanded ? 'Less' : 'More...'"></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No messages found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
