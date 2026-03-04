@extends('layouts.app')

@section('title', 'Оголошення')

@section('actions')
@if(auth()->user()->canCreate('announcements'))
<button onclick="openCreateAnnouncementModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    + Нове оголошення
</button>
@endif
@endsection

@section('content')
<x-comm-tabs />

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Оголошення церкви</h1>
            @if($unreadCount > 0)
            <p class="text-sm text-primary-600 dark:text-primary-400">{{ $unreadCount }} нових</p>
            @endif
        </div>
        @if($unreadCount > 0)
        <button @click="ajaxAction('{{ route('announcements.mark-all-read') }}', 'POST').then(() => window.location.reload())"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Прочитати все
        </button>
        @endif
    </div>

    <!-- Announcements List -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            @php
                $isUnread = !$announcement->isReadBy(auth()->user());
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden {{ $isUnread ? 'ring-2 ring-primary-500' : '' }}">
                <!-- Pinned Badge -->
                @if($announcement->is_pinned)
                <div class="bg-amber-50 dark:bg-amber-900/30 px-4 py-2 border-b border-amber-100 dark:border-amber-800 flex items-center text-amber-700 dark:text-amber-400 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1.323l3.954.99a1 1 0 01.756.97v.01a1 1 0 01-.756.97L11 8.253V17a1 1 0 11-2 0V8.253L5.046 7.263a1 1 0 010-1.94L9 4.323V3a1 1 0 011-1z"/>
                    </svg>
                    Закріплене оголошення
                </div>
                @endif

                <a href="{{ route('announcements.show', $announcement) }}" class="block p-4 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                @if($isUnread)
                                <span class="w-2 h-2 bg-primary-600 rounded-full"></span>
                                @endif
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white {{ $isUnread ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                    {{ $announcement->title }}
                                </h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 line-clamp-2">
                                {{ Str::limit(strip_tags($announcement->content), 150) }}
                            </p>
                            <div class="flex items-center gap-2 sm:gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $announcement->author?->name ?? 'Видалений' }}
                                </span>
                                <span>{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 ml-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>

                @if(auth()->user()->canEdit('announcements'))
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                    <button @click="ajaxAction('{{ route('announcements.pin', $announcement) }}', 'POST').then(() => { const t = $el.textContent.trim(); $el.textContent = t === 'Відкріпити' ? 'Закріпити' : 'Відкріпити'; })"
                            class="text-sm text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
                        {{ $announcement->is_pinned ? 'Відкріпити' : 'Закріпити' }}
                    </button>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <a href="{{ route('announcements.edit', $announcement) }}" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                        Редагувати
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <button @click="ajaxDelete('{{ route('announcements.destroy', $announcement) }}', '{{ __('messages.confirm_delete_announcement') }}', () => $el.closest('.rounded-2xl').remove())"
                            class="text-sm text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                        Видалити
                    </button>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Немає оголошень</h3>
                <p class="text-gray-500 dark:text-gray-400">Тут будуть важливі повідомлення від церкви</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
    <div class="mt-6">
        {{ $announcements->links() }}
    </div>
    @endif
</div>

{{-- Create Announcement Modal --}}
@if(auth()->user()->canCreate('announcements'))
<div id="createAnnouncementModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 sm:p-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeCreateAnnouncementModal()"></div>
        <div class="relative w-full max-w-lg mx-auto mt-8 sm:mt-16 bg-white dark:bg-gray-800 rounded-2xl shadow-xl z-10" x-data="announcementCreateForm()">
            <form x-ref="form" @submit.prevent="submitForm">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Нове оголошення</h2>
                    <button type="button" onclick="closeCreateAnnouncementModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-5 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Заголовок *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Введіть заголовок оголошення">
                        <template x-if="errors.title">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.title[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Текст оголошення *</label>
                        <textarea name="content" rows="6" required
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                                  placeholder="Напишіть текст оголошення..."></textarea>
                        <template x-if="errors.content">
                            <p class="mt-1 text-sm text-red-600" x-text="errors.content[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Актуально до (необов'язково)</label>
                        <input type="date" name="expires_at"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Після цієї дати оголошення автоматично сховається</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_pinned" value="1" id="modal_is_pinned"
                               class="w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-700">
                        <label for="modal_is_pinned" class="ml-3 text-gray-700 dark:text-gray-300">
                            Закріпити зверху
                        </label>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateAnnouncementModal()"
                            class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl">
                        Скасувати
                    </button>
                    <button type="submit" :disabled="saving"
                            class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl disabled:opacity-50">
                        <span x-show="!saving">Опублікувати</span>
                        <span x-show="saving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Збереження...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateAnnouncementModal() {
    document.getElementById('createAnnouncementModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCreateAnnouncementModal() {
    document.getElementById('createAnnouncementModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('createAnnouncementModal').classList.contains('hidden')) {
        closeCreateAnnouncementModal();
    }
});

function announcementCreateForm() {
    return {
        saving: false,
        errors: {},
        async submitForm() {
            this.saving = true;
            this.errors = {};
            const formData = new FormData(this.$refs.form);
            try {
                const response = await fetch('{{ route("announcements.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (response.status === 422 && data.errors) { this.errors = data.errors; showToast('error', 'Перевірте правильність заповнення форми.'); }
                    else { showToast('error', data.message || 'Помилка збереження.'); }
                    this.saving = false; return;
                }
                showToast('success', data.message || 'Оголошення опубліковано!');
                closeCreateAnnouncementModal();
                setTimeout(() => Livewire.navigate(window.location.href), 600);
            } catch (e) { showToast('error', "Помилка з'єднання з сервером."); this.saving = false; }
        }
    }
}
</script>
@endif
@endsection
