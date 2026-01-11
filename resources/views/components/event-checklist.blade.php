@props(['event', 'templates'])

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
     x-data="checklistManager({{ $event->id }}, {{ $event->checklist ? 'true' : 'false' }})">
    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <h3 class="font-semibold text-gray-900 dark:text-white">Чеклист</h3>
        </div>

        <template x-if="hasChecklist">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-24 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="progress + '%'"></span>
                </div>
                <button type="button" @click="deleteChecklist()" class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <template x-if="hasChecklist">
        <div>
            <!-- Checklist Items -->
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="item in items" :key="item.id">
                    <div class="p-4 flex items-start gap-3 group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <button type="button" @click="toggleItem(item)"
                                class="mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                :class="item.is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-green-500'">
                            <svg x-show="item.is_completed" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white"
                               :class="item.is_completed ? 'line-through text-gray-400 dark:text-gray-500' : ''"
                               x-text="item.title"></p>
                            <p x-show="item.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="item.description"></p>
                            <p x-show="item.is_completed && item.completed_by_name" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                <span x-text="item.completed_by_name"></span> &bull; <span x-text="item.completed_at"></span>
                            </p>
                        </div>

                        <button type="button" @click="deleteItem(item)"
                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Add Item Form -->
                <div class="p-4">
                    <template x-if="!adding">
                        <button @click="adding = true"
                                class="w-full p-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-gray-500 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Додати пункт
                        </button>
                    </template>

                    <template x-if="adding">
                        <form @submit.prevent="addItem()" class="flex items-center gap-2">
                            <input type="text" x-model="newTitle" required x-ref="newItemInput"
                                   placeholder="Назва пункту..."
                                   class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white text-sm">
                            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Додати
                            </button>
                            <button type="button" @click="adding = false; newTitle = ''" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <template x-if="!hasChecklist">
        <!-- Create Checklist -->
        <div class="p-6">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Додайте чеклист для відстеження підготовки</p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <button type="button" @click="createChecklist()"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Порожній чеклист
                    </button>

                    @if($templates->isNotEmpty())
                        <div class="relative">
                            <button type="button" @click="showTemplates = !showTemplates"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                З шаблону
                            </button>

                            <div x-show="showTemplates" x-cloak @click.away="showTemplates = false"
                                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-10">
                                @foreach($templates as $template)
                                    <button type="button" @click="createChecklist({{ $template->id }})"
                                            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $template->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $template->items->count() }} пунктів</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
function checklistManager(eventId, hasChecklistInitial) {
    return {
        eventId: eventId,
        hasChecklist: hasChecklistInitial,
        checklistId: {{ $event->checklist?->id ?? 'null' }},
        items: <?php echo json_encode($event->checklist?->items->map(fn($i) => ['id' => $i->id, 'title' => $i->title, 'description' => $i->description, 'is_completed' => $i->is_completed, 'completed_by_name' => $i->completedByUser?->name, 'completed_at' => $i->completed_at?->format('d.m.Y H:i')]) ?? []); ?>,
        progress: {{ $event->checklist?->progress ?? 0 }},
        adding: false,
        newTitle: '',
        showTemplates: false,

        init() {
            this.$watch('adding', (val) => {
                if (val) this.$nextTick(() => this.$refs.newItemInput?.focus());
            });
        },

        updateProgress() {
            const total = this.items.length;
            const completed = this.items.filter(i => i.is_completed).length;
            this.progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        },

        async createChecklist(templateId = null) {
            try {
                const body = templateId ? { template_id: templateId } : {};
                const response = await fetch(`/checklists/events/${this.eventId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(body)
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.checklistId = data.checklist.id;
                        this.items = (data.checklist.items || []).map(i => ({
                            id: i.id,
                            title: i.title,
                            description: i.description,
                            is_completed: i.is_completed,
                            completed_by_name: null,
                            completed_at: null
                        }));
                        this.hasChecklist = true;
                        this.showTemplates = false;
                        this.updateProgress();
                        if (window.showGlobalToast) showGlobalToast('Чеклист створено', 'success');
                    }
                }
            } catch (err) {
                console.error(err);
                if (window.showGlobalToast) showGlobalToast('Помилка створення', 'error');
            }
        },

        async deleteChecklist() {
            if (!confirm('Видалити чеклист?')) return;

            try {
                const response = await fetch(`/checklists/${this.checklistId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    this.hasChecklist = false;
                    this.checklistId = null;
                    this.items = [];
                    this.progress = 0;
                    if (window.showGlobalToast) showGlobalToast('Чеклист видалено', 'success');
                }
            } catch (err) {
                console.error(err);
                if (window.showGlobalToast) showGlobalToast('Помилка видалення', 'error');
            }
        },

        async toggleItem(item) {
            try {
                const response = await fetch(`/checklists/items/${item.id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    item.is_completed = data.is_completed;
                    this.progress = data.progress;

                    if (item.is_completed) {
                        item.completed_by_name = '{{ auth()->user()->name }}';
                        item.completed_at = new Date().toLocaleString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    } else {
                        item.completed_by_name = null;
                        item.completed_at = null;
                    }
                }
            } catch (err) {
                console.error(err);
                if (window.showGlobalToast) showGlobalToast('Помилка', 'error');
            }
        },

        async addItem() {
            if (!this.newTitle.trim()) return;

            try {
                const response = await fetch(`/checklists/${this.checklistId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ title: this.newTitle })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.item) {
                        this.items.push({
                            id: data.item.id,
                            title: data.item.title,
                            description: data.item.description,
                            is_completed: false,
                            completed_by_name: null,
                            completed_at: null
                        });
                        this.newTitle = '';
                        this.adding = false;
                        this.updateProgress();
                        if (window.showGlobalToast) showGlobalToast('Пункт додано', 'success');
                    }
                }
            } catch (err) {
                console.error(err);
                if (window.showGlobalToast) showGlobalToast('Помилка додавання', 'error');
            }
        },

        async deleteItem(item) {
            try {
                const response = await fetch(`/checklists/items/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    this.items = this.items.filter(i => i.id !== item.id);
                    this.updateProgress();
                    if (window.showGlobalToast) showGlobalToast('Пункт видалено', 'success');
                }
            } catch (err) {
                console.error(err);
                if (window.showGlobalToast) showGlobalToast('Помилка видалення', 'error');
            }
        }
    };
}
</script>
@endpush
@endonce
