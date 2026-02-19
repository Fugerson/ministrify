/**
 * PWA IndexedDB Database Wrapper
 * Provides offline data storage for Ministrify PWA
 */

const PWA_DB = {
    DB_NAME: 'ministrify-pwa',
    DB_VERSION: 1,
    db: null,

    /**
     * Initialize the database
     */
    async init() {
        if (this.db) return this.db;

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store for my schedule (responsibilities)
                if (!db.objectStoreNames.contains('my-schedule')) {
                    const scheduleStore = db.createObjectStore('my-schedule', { keyPath: 'id' });
                    scheduleStore.createIndex('event_date', 'event.date', { unique: false });
                }

                // Store for offline actions queue
                if (!db.objectStoreNames.contains('offline-actions')) {
                    const actionsStore = db.createObjectStore('offline-actions', { keyPath: 'id', autoIncrement: true });
                    actionsStore.createIndex('created_at', 'created_at', { unique: false });
                }

                // Store for sync metadata
                if (!db.objectStoreNames.contains('sync-meta')) {
                    db.createObjectStore('sync-meta', { keyPath: 'key' });
                }
            };
        });
    },

    /**
     * Save my schedule data
     */
    async saveSchedule(responsibilities) {
        await this.init();
        const tx = this.db.transaction('my-schedule', 'readwrite');
        const store = tx.objectStore('my-schedule');

        // Set up transaction completion promise IMMEDIATELY to avoid missing auto-commit
        const txComplete = new Promise((resolve, reject) => {
            tx.oncomplete = resolve;
            tx.onerror = reject;
        });

        // Clear existing data
        await new Promise((resolve, reject) => {
            const clearRequest = store.clear();
            clearRequest.onsuccess = resolve;
            clearRequest.onerror = reject;
        });

        // Add new data
        for (const item of responsibilities) {
            store.put(item);
        }

        // Wait for transaction to complete
        await txComplete;

        // Save sync timestamp in a separate transaction (after the main one completes)
        await this.setSyncMeta('my-schedule', {
            key: 'my-schedule',
            synced_at: new Date().toISOString(),
            count: responsibilities.length
        });
    },

    /**
     * Get my schedule data
     */
    async getSchedule() {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('my-schedule', 'readonly');
            const store = tx.objectStore('my-schedule');
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    /**
     * Update a single responsibility status locally
     */
    async updateResponsibilityStatus(id, status, statusLabel) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('my-schedule', 'readwrite');
            const store = tx.objectStore('my-schedule');
            const getRequest = store.get(id);

            getRequest.onsuccess = () => {
                const data = getRequest.result;
                if (data) {
                    data.status = status;
                    data.status_label = statusLabel;
                    store.put(data);
                }
            };

            tx.oncomplete = resolve;
            tx.onerror = reject;
        });
    },

    /**
     * Queue an offline action
     */
    async queueOfflineAction(action) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline-actions', 'readwrite');
            const store = tx.objectStore('offline-actions');
            const request = store.add({
                ...action,
                created_at: new Date().toISOString()
            });
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    /**
     * Get all pending offline actions
     */
    async getOfflineActions() {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline-actions', 'readonly');
            const store = tx.objectStore('offline-actions');
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    },

    /**
     * Remove a processed offline action
     */
    async removeOfflineAction(id) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline-actions', 'readwrite');
            const store = tx.objectStore('offline-actions');
            const request = store.delete(id);
            request.onsuccess = resolve;
            request.onerror = reject;
        });
    },

    /**
     * Clear all offline actions
     */
    async clearOfflineActions() {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('offline-actions', 'readwrite');
            const store = tx.objectStore('offline-actions');
            const request = store.clear();
            request.onsuccess = resolve;
            request.onerror = reject;
        });
    },

    /**
     * Set sync metadata
     */
    async setSyncMeta(key, data) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('sync-meta', 'readwrite');
            const store = tx.objectStore('sync-meta');
            const request = store.put(data);
            request.onsuccess = resolve;
            request.onerror = reject;
        });
    },

    /**
     * Get sync metadata
     */
    async getSyncMeta(key) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('sync-meta', 'readonly');
            const store = tx.objectStore('sync-meta');
            const request = store.get(key);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
};

/**
 * PWA Sync Manager
 * Handles data synchronization between online/offline states
 */
const PWA_SYNC = {
    /**
     * Sync my schedule from server
     */
    async syncSchedule() {
        if (!navigator.onLine) {
            console.log('Offline - skipping schedule sync');
            return { success: false, offline: true };
        }

        try {
            const response = await fetch('/api/pwa/my-schedule', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            await PWA_DB.saveSchedule(data.responsibilities || []);

            return {
                success: true,
                count: data.count,
                synced_at: data.synced_at
            };
        } catch (error) {
            console.error('Schedule sync failed:', error);
            return { success: false, error: error.message };
        }
    },

    /**
     * Process pending offline actions
     */
    async processOfflineActions() {
        if (!navigator.onLine) return { processed: 0 };

        const actions = await PWA_DB.getOfflineActions();
        let processed = 0;

        for (const action of actions) {
            try {
                const response = await fetch(action.url, {
                    method: action.method,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: action.body ? JSON.stringify(action.body) : undefined
                });

                if (response.ok) {
                    await PWA_DB.removeOfflineAction(action.id);
                    processed++;
                }
            } catch (error) {
                console.error('Failed to process offline action:', action, error);
            }
        }

        // Refresh data after processing actions
        if (processed > 0) {
            await this.syncSchedule();
        }

        return { processed };
    },

    /**
     * Initialize sync on page load and online event
     */
    init() {
        // Sync when coming back online
        window.addEventListener('online', async () => {
            console.log('Back online - syncing...');
            await this.processOfflineActions();
            await this.syncSchedule();
            window.dispatchEvent(new CustomEvent('pwa-online'));
        });

        window.addEventListener('offline', () => {
            console.log('Gone offline');
            window.dispatchEvent(new CustomEvent('pwa-offline'));
        });

        // Initial sync if online
        if (navigator.onLine) {
            this.syncSchedule();
        }
    }
};

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => PWA_SYNC.init());
} else {
    PWA_SYNC.init();
}

// Export for use in modules
if (typeof window !== 'undefined') {
    window.PWA_DB = PWA_DB;
    window.PWA_SYNC = PWA_SYNC;
}
