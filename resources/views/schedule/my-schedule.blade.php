@extends('layouts.app')

@section('title', 'Мій розклад')

@push('head')
<script src="/js/pwa-db.js"></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto" x-data="mySchedule()" x-init="init()">
    {{-- Offline indicator --}}
    <div x-show="isOffline" x-cloak
         class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Ви офлайн</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400">Показуємо збережені дані. Зміни синхронізуються при підключенні.</p>
            </div>
        </div>
    </div>

    {{-- Pending sync indicator --}}
    <div x-show="pendingActions > 0 && !isOffline" x-cloak
         class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Синхронізація...</p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Очікує синхронізації: <span x-text="pendingActions"></span> дій</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Мій розклад</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ваші майбутні відповідальності</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Sync status --}}
                    <div x-show="syncedAt" class="text-xs text-gray-400 dark:text-gray-500">
                        <span x-text="'Оновлено: ' + formatSyncTime(syncedAt)"></span>
                    </div>
                    {{-- Refresh button --}}
                    <button @click="refresh()"
                            :disabled="loading"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50">
                        <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            {{-- Loading state --}}
            <template x-if="loading && responsibilities.length === 0">
                <div class="p-8 text-center">
                    <svg class="w-8 h-8 mx-auto mb-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Завантаження...</p>
                </div>
            </template>

            {{-- Responsibilities list --}}
            <template x-for="responsibility in responsibilities" :key="responsibility.id">
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="text-center min-w-[40px]">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="responsibility.event.date_formatted"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase" x-text="responsibility.event.month"></p>
                                </div>
                                <div>
                                    <a :href="'/events/' + responsibility.event.id"
                                       class="font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400"
                                       x-text="responsibility.event.title"></a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <span x-text="responsibility.event.time"></span> - <span x-text="responsibility.name"></span>
                                    </p>
                                    <p x-show="responsibility.event.ministry"
                                       class="text-xs text-gray-400"
                                       x-text="responsibility.event.ministry?.name"></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full"
                                  :class="getStatusClasses(responsibility.status)"
                                  x-text="responsibility.status_label"></span>

                            <template x-if="responsibility.status === 'pending'">
                                <div class="flex gap-1">
                                    <button @click="confirmResponsibility(responsibility)"
                                            :disabled="responsibility.processing"
                                            class="p-1.5 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg disabled:opacity-50"
                                            title="Підтвердити">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button @click="declineResponsibility(responsibility)"
                                            :disabled="responsibility.processing"
                                            class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg disabled:opacity-50"
                                            title="Відхилити">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <template x-if="!loading && responsibilities.length === 0">
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>У вас немає майбутніх відповідальностей</p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function mySchedule() {
    return {
        responsibilities: [],
        loading: true,
        isOffline: !navigator.onLine,
        syncedAt: null,
        pendingActions: 0,

        async init() {
            // Listen for online/offline events
            window.addEventListener('online', () => {
                this.isOffline = false;
                this.syncData();
            });
            window.addEventListener('offline', () => {
                this.isOffline = true;
            });

            // Listen for service worker sync messages
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (event.data?.type === 'SYNC_OFFLINE_ACTIONS') {
                        this.processOfflineActions();
                    }
                });
            }

            // Load data
            await this.loadData();
        },

        async loadData() {
            this.loading = true;

            try {
                // Try to load from cache first for instant display
                const cachedData = await PWA_DB.getSchedule();
                const syncMeta = await PWA_DB.getSyncMeta('my-schedule');

                if (cachedData.length > 0) {
                    this.responsibilities = cachedData;
                    this.syncedAt = syncMeta?.synced_at;
                }

                // Check pending actions
                const actions = await PWA_DB.getOfflineActions();
                this.pendingActions = actions.length;

                // If online, sync fresh data
                if (navigator.onLine) {
                    await this.syncData();
                }
            } catch (error) {
                console.error('Failed to load data:', error);
            }

            this.loading = false;
        },

        async syncData() {
            if (!navigator.onLine) return;

            try {
                // First process any pending offline actions
                await this.processOfflineActions();

                // Then fetch fresh data
                const result = await PWA_SYNC.syncSchedule();

                if (result.success) {
                    const data = await PWA_DB.getSchedule();
                    this.responsibilities = data;
                    this.syncedAt = result.synced_at || new Date().toISOString();
                }
            } catch (error) {
                console.error('Sync failed:', error);
            }
        },

        async processOfflineActions() {
            const result = await PWA_SYNC.processOfflineActions();
            this.pendingActions = 0;
            return result;
        },

        async refresh() {
            this.loading = true;
            await this.syncData();
            this.loading = false;
        },

        async confirmResponsibility(responsibility) {
            responsibility.processing = true;

            if (navigator.onLine) {
                try {
                    const response = await fetch(`/api/pwa/responsibilities/${responsibility.id}/confirm`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    if (response.ok) {
                        responsibility.status = 'confirmed';
                        responsibility.status_label = 'Підтверджено';
                        await PWA_DB.updateResponsibilityStatus(responsibility.id, 'confirmed', 'Підтверджено');
                    }
                } catch (error) {
                    console.error('Confirm failed:', error);
                    await this.queueOfflineAction(responsibility, 'confirm');
                }
            } else {
                await this.queueOfflineAction(responsibility, 'confirm');
                responsibility.status = 'confirmed';
                responsibility.status_label = 'Підтверджено (очікує синхр.)';
            }

            responsibility.processing = false;
        },

        async declineResponsibility(responsibility) {
            responsibility.processing = true;

            if (navigator.onLine) {
                try {
                    const response = await fetch(`/api/pwa/responsibilities/${responsibility.id}/decline`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    if (response.ok) {
                        responsibility.status = 'declined';
                        responsibility.status_label = 'Відхилено';
                        await PWA_DB.updateResponsibilityStatus(responsibility.id, 'declined', 'Відхилено');
                    }
                } catch (error) {
                    console.error('Decline failed:', error);
                    await this.queueOfflineAction(responsibility, 'decline');
                }
            } else {
                await this.queueOfflineAction(responsibility, 'decline');
                responsibility.status = 'declined';
                responsibility.status_label = 'Відхилено (очікує синхр.)';
            }

            responsibility.processing = false;
        },

        async queueOfflineAction(responsibility, action) {
            await PWA_DB.queueOfflineAction({
                type: 'responsibility_' + action,
                url: `/api/pwa/responsibilities/${responsibility.id}/${action}`,
                method: 'POST',
                responsibility_id: responsibility.id
            });
            this.pendingActions++;

            // Register for background sync if supported
            if ('serviceWorker' in navigator) {
                try {
                    const reg = await navigator.serviceWorker.ready;
                    if ('sync' in reg) {
                        await reg.sync.register('sync-schedule-actions');
                    }
                } catch (e) {
                    // Background sync not available
                }
            }
        },

        getStatusClasses(status) {
            const classes = {
                'confirmed': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'pending': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                'declined': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
            };
            return classes[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300';
        },

        formatSyncTime(isoString) {
            if (!isoString) return '';
            const date = new Date(isoString);
            const now = new Date();
            const diff = now - date;

            if (diff < 60000) return 'щойно';
            if (diff < 3600000) return Math.floor(diff / 60000) + ' хв тому';
            if (diff < 86400000) return Math.floor(diff / 3600000) + ' год тому';

            return date.toLocaleDateString('uk-UA', { day: 'numeric', month: 'short' });
        }
    };
}
</script>
@endsection
