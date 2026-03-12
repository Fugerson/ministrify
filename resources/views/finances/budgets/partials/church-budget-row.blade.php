{{-- Church budget item table row --}}
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
    <td class="px-6 py-4">
        <div class="font-medium text-gray-900 dark:text-white">{{ $cbi['name'] }}</div>
        @if($cbi['category'])
            <div class="text-xs text-gray-500">{{ $cbi['category']->name }}</div>
        @elseif($cbi['planned'] > 0)
            <div class="text-xs text-amber-600 dark:text-amber-400">{{ __('app.budget_no_category_warning') }}</div>
        @endif
    </td>
    <td class="px-6 py-4 text-right whitespace-nowrap text-gray-700 dark:text-gray-300">
        {{ $cbi['planned'] > 0 ? number_format($cbi['planned'], 0, ',', ' ') . ' ₴' : '—' }}
    </td>
    <td class="px-6 py-4 text-right whitespace-nowrap">
        @if($cbi['category_id'])
            <button x-on:click="showChurchTransactions({{ $cbi['id'] }}, {{ json_encode($cbi['name']) }})"
                    class="text-red-600 dark:text-red-400 hover:underline">
                {{ $cbi['actual'] > 0 ? number_format($cbi['actual'], 0, ',', ' ') . ' ₴' : '—' }}
            </button>
        @else
            <span class="text-gray-400">—</span>
        @endif
    </td>
    <td class="px-6 py-4 text-right whitespace-nowrap">
        @if($cbi['planned'] > 0)
            @php
                $diffBg = $cbi['difference'] >= 0
                    ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300'
                    : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300';
            @endphp
            <span class="inline-flex px-2 py-0.5 rounded-md text-sm font-medium {{ $diffBg }}">
                {{ $cbi['difference'] >= 0 ? '+' : '' }}{{ number_format($cbi['difference'], 0, ',', ' ') }} ₴
            </span>
        @else
            —
        @endif
    </td>
    <td class="px-6 py-4 text-right whitespace-nowrap text-gray-500 dark:text-gray-400">
        {{ number_format($cbi['annual_planned'], 0, ',', ' ') }} ₴
    </td>
    @if(auth()->user()->canEdit('finances'))
    <td class="px-6 py-4 text-center">
        <div class="flex items-center justify-center gap-1">
            <button x-on:click="openChurchItemModal('edit', {{ json_encode([
                'id' => $cbi['id'],
                'name' => $cbi['name'],
                'category_id' => $cbi['category_id'],
                'is_recurring' => $cbi['is_recurring'],
                'amounts' => $cbi['amounts'],
                'notes' => $cbi['notes'] ?? '',
            ]) }})"
                    class="p-1.5 text-gray-400 hover:text-primary-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="{{ __('ui.edit') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </button>
            <button x-on:click="deleteChurchItem({{ $cbi['id'] }}, {{ json_encode($cbi['name']) }})"
                    class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="{{ __('ui.delete') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    </td>
    @endif
</tr>
