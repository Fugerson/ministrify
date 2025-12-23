@extends('layouts.app')

@section('title', 'Нове повідомлення')

@section('content')
<div class="max-w-2xl" x-data="messageForm()">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('messages.send') }}" class="space-y-6">
            @csrf

            <!-- Recipient Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Отримувачі</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'all' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="all" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'all' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">Всі</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'tag' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="tag" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'tag' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">По тегу</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'ministry' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="ministry" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'ministry' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">Служіння</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'group' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="group" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'group' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">Група</span>
                    </label>
                </div>
            </div>

            <!-- Tag Select -->
            <div x-show="recipientType === 'tag'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Оберіть тег</label>
                <select name="tag_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">Оберіть...</option>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ministry Select -->
            <div x-show="recipientType === 'ministry'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Оберіть служіння</label>
                <select name="ministry_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">Оберіть...</option>
                    @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Group Select -->
            <div x-show="recipientType === 'group'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Оберіть групу</label>
                <select name="group_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">Оберіть...</option>
                    @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Template Select -->
            @if($templates->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Шаблон (опціонально)</label>
                <select @change="if($event.target.value) message = templates[$event.target.value]"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">Без шаблону</option>
                    @foreach($templates as $template)
                    <option value="{{ $loop->index }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст повідомлення *</label>
                <textarea name="message" rows="6" required x-model="message"
                          placeholder="Привіт! Нагадуємо про захід..."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white"></textarea>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Змінні: {first_name}, {last_name}, {full_name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="message.length + '/4000'"></p>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Повідомлення будуть надіслані через Telegram тільки тим людям, у яких є Telegram chat ID.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('messages.index') }}" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    Скасувати
                </a>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Надіслати
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function messageForm() {
    return {
        recipientType: 'all',
        message: '',
        templates: @json($templates->pluck('content'))
    }
}
</script>
@endsection
