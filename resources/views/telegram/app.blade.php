<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ministrify</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --tg-bg: var(--tg-theme-bg-color, #ffffff);
            --tg-text: var(--tg-theme-text-color, #000000);
            --tg-hint: var(--tg-theme-hint-color, #999999);
            --tg-link: var(--tg-theme-link-color, #2481cc);
            --tg-btn: var(--tg-theme-button-color, #2481cc);
            --tg-btn-text: var(--tg-theme-button-text-color, #ffffff);
            --tg-secondary-bg: var(--tg-theme-secondary-bg-color, #f0f0f0);
            --tg-section-bg: var(--tg-theme-section-bg-color, #ffffff);
            --tg-section-header: var(--tg-theme-section-header-text-color, #6d6d72);
            --tg-subtitle: var(--tg-theme-subtitle-text-color, #999999);
            --tg-destructive: var(--tg-theme-destructive-text-color, #ff3b30);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--tg-secondary-bg);
            color: var(--tg-text);
            min-height: 100vh;
            padding-bottom: 70px;
            -webkit-font-smoothing: antialiased;
        }

        .tab-content { padding: 12px; min-height: calc(100vh - 70px); }

        /* Cards */
        .card {
            background: var(--tg-section-bg);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 10px;
        }
        .card-header { display: flex; align-items: flex-start; justify-content: space-between; }
        .card-title { font-size: 15px; font-weight: 600; line-height: 1.3; }
        .card-subtitle { font-size: 13px; color: var(--tg-hint); margin-top: 2px; }
        .card-text { font-size: 14px; line-height: 1.5; margin-top: 8px; color: var(--tg-text); }
        .card-meta { font-size: 12px; color: var(--tg-subtitle); margin-top: 6px; }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 600;
            padding: 3px 8px; border-radius: 6px;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-declined { background: #fee2e2; color: #991b1b; }
        .badge-open { background: #e5e7eb; color: #374151; }
        .badge-urgent { background: #fee2e2; color: #991b1b; }
        .badge-pin { background: #dbeafe; color: #1e40af; }

        .ministry-dot {
            width: 8px; height: 8px;
            border-radius: 50%; display: inline-block;
            flex-shrink: 0; margin-top: 5px;
        }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            border: none; cursor: pointer;
            transition: opacity 0.15s;
        }
        .btn:active { opacity: 0.7; }
        .btn-confirm { background: #10b981; color: #fff; }
        .btn-decline { background: #ef4444; color: #fff; }
        .btn-pray { background: var(--tg-btn); color: var(--tg-btn-text); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-group { display: flex; gap: 8px; margin-top: 10px; }

        /* Tab Bar */
        .tab-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--tg-section-bg);
            display: flex; justify-content: space-around;
            padding: 6px 0 env(safe-area-inset-bottom, 8px);
            border-top: 0.5px solid var(--tg-hint);
            z-index: 100;
        }
        .tab-bar-item {
            display: flex; flex-direction: column; align-items: center;
            gap: 2px; padding: 4px 8px;
            font-size: 10px; color: var(--tg-hint);
            cursor: pointer; transition: color 0.15s;
            -webkit-tap-highlight-color: transparent;
        }
        .tab-bar-item.active { color: var(--tg-btn); }
        .tab-bar-item svg { width: 22px; height: 22px; }

        /* Loading */
        .loading {
            display: flex; align-items: center; justify-content: center;
            padding: 40px; color: var(--tg-hint); font-size: 14px;
        }
        .spinner {
            width: 20px; height: 20px;
            border: 2px solid var(--tg-hint);
            border-top-color: var(--tg-btn);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 40px 20px;
            color: var(--tg-hint); font-size: 14px;
        }
        .empty-state-icon { font-size: 40px; margin-bottom: 12px; }

        /* Section header */
        .section-header {
            font-size: 13px; font-weight: 600;
            color: var(--tg-section-header);
            text-transform: uppercase;
            padding: 16px 4px 8px;
        }

        /* Profile */
        .profile-avatar {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: var(--tg-btn);
            color: var(--tg-btn-text);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 600;
            margin: 0 auto 12px;
            overflow: hidden;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { text-align: center; font-size: 20px; font-weight: 700; }
        .profile-stat {
            display: flex; justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            border-bottom: 0.5px solid var(--tg-secondary-bg);
        }
        .profile-stat:last-child { border-bottom: none; }
        .profile-stat-value { font-weight: 600; color: var(--tg-btn); }
        .chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 12px;
            font-size: 12px; font-weight: 500;
            background: var(--tg-secondary-bg);
            margin: 2px;
        }

        /* Error banner */
        .error-banner {
            background: var(--tg-section-bg);
            text-align: center; padding: 60px 20px;
            min-height: 100vh;
        }
        .error-banner h2 { font-size: 18px; margin-bottom: 8px; }
        .error-banner p { color: var(--tg-hint); font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body x-data="tmaApp()" x-init="init()">

    <!-- Not linked state -->
    <template x-if="!person && !loadingInit">
        <div class="error-banner">
            <div style="font-size: 48px; margin-bottom: 16px;">🔗</div>
            <h2>Акаунт не прив'язано</h2>
            <p>Щоб користуватися додатком, надішліть команду <strong>/app</strong> боту та натисніть кнопку "Відкрити додаток".</p>
        </div>
    </template>

    <!-- Loading init -->
    <template x-if="loadingInit">
        <div class="loading" style="min-height: 100vh;">
            <div class="spinner"></div> Завантаження...
        </div>
    </template>

    <!-- Main app -->
    <template x-if="person && !loadingInit">
        <div>
            <!-- Events Tab -->
            <div class="tab-content" x-show="tab === 'events'" x-cloak>
                <div class="section-header">Найближчі події</div>
                <template x-if="loading.events">
                    <div class="loading"><div class="spinner"></div> Завантаження...</div>
                </template>
                <template x-if="!loading.events && events.length === 0">
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        Немає подій на найближчі 30 днів
                    </div>
                </template>
                <template x-for="event in events" :key="event.id">
                    <div class="card">
                        <div class="card-header">
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <div x-show="event.ministry" class="ministry-dot" :style="`background: ${event.ministry?.color || '#6b7280'}`"></div>
                                <div>
                                    <div class="card-title" x-text="event.title"></div>
                                    <div class="card-subtitle" x-text="event.ministry?.name || ''"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-meta" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span>📅 <span x-text="event.date_formatted"></span></span>
                            <span x-show="event.time">🕐 <span x-text="event.time"></span><template x-if="event.end_time"> — <span x-text="event.end_time"></span></template></span>
                            <span x-show="event.location">📍 <span x-text="event.location"></span></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Assignments Tab -->
            <div class="tab-content" x-show="tab === 'assignments'" x-cloak>
                <div class="section-header">Мої призначення</div>
                <template x-if="loading.assignments">
                    <div class="loading"><div class="spinner"></div> Завантаження...</div>
                </template>
                <template x-if="!loading.assignments && assignmentsList.length === 0 && responsibilitiesList.length === 0">
                    <div class="empty-state">
                        <div class="empty-state-icon">✨</div>
                        Немає активних призначень
                    </div>
                </template>

                <!-- Assignments -->
                <template x-for="item in assignmentsList" :key="'a-' + item.id">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title" x-text="item.position || item.event.title"></div>
                                <div class="card-subtitle" x-text="item.event.title"></div>
                            </div>
                            <span class="badge" :class="'badge-' + item.status" x-text="item.status_label"></span>
                        </div>
                        <div class="card-meta" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span>📅 <span x-text="item.event.date_formatted"></span></span>
                            <span x-show="item.event.time">🕐 <span x-text="item.event.time"></span></span>
                            <span x-show="item.event.ministry" x-text="item.event.ministry?.name"></span>
                        </div>
                        <div class="btn-group" x-show="item.status === 'pending'">
                            <button class="btn btn-confirm btn-sm" @click="confirmAssignment(item.id)" :disabled="item._loading">✓ Підтверджую</button>
                            <button class="btn btn-decline btn-sm" @click="declineAssignment(item.id)" :disabled="item._loading">✗ Не можу</button>
                        </div>
                    </div>
                </template>

                <!-- Responsibilities -->
                <template x-if="responsibilitiesList.length > 0">
                    <div class="section-header" style="margin-top: 4px;">Обов'язки</div>
                </template>
                <template x-for="item in responsibilitiesList" :key="'r-' + item.id">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title" x-text="item.name"></div>
                                <div class="card-subtitle" x-text="item.event.title"></div>
                            </div>
                            <span class="badge" :class="'badge-' + item.status" x-text="item.status_label"></span>
                        </div>
                        <div class="card-meta" style="display: flex; gap: 12px;">
                            <span>📅 <span x-text="item.event.date_formatted"></span></span>
                            <span x-show="item.event.time">🕐 <span x-text="item.event.time"></span></span>
                        </div>
                        <div class="btn-group" x-show="item.status === 'pending'">
                            <button class="btn btn-confirm btn-sm" @click="confirmResponsibility(item.id)" :disabled="item._loading">✓ Підтверджую</button>
                            <button class="btn btn-decline btn-sm" @click="declineResponsibility(item.id)" :disabled="item._loading">✗ Не можу</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-content" x-show="tab === 'announcements'" x-cloak>
                <div class="section-header">Оголошення</div>
                <template x-if="loading.announcements">
                    <div class="loading"><div class="spinner"></div> Завантаження...</div>
                </template>
                <template x-if="!loading.announcements && announcements.length === 0">
                    <div class="empty-state">
                        <div class="empty-state-icon">📢</div>
                        Немає оголошень
                    </div>
                </template>
                <template x-for="a in announcements" :key="a.id">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title" x-text="a.title"></div>
                            <div style="display: flex; gap: 4px;">
                                <span x-show="a.is_pinned" class="badge badge-pin">📌</span>
                            </div>
                        </div>
                        <div class="card-text" x-html="formatText(a.content)"></div>
                        <div class="card-meta" x-text="a.published_at_formatted"></div>
                    </div>
                </template>
            </div>

            <!-- Prayers Tab -->
            <div class="tab-content" x-show="tab === 'prayers'" x-cloak>
                <div class="section-header">Молитовні потреби</div>
                <template x-if="loading.prayers">
                    <div class="loading"><div class="spinner"></div> Завантаження...</div>
                </template>
                <template x-if="!loading.prayers && prayers.length === 0">
                    <div class="empty-state">
                        <div class="empty-state-icon">🙏</div>
                        Немає активних молитовних потреб
                    </div>
                </template>
                <template x-for="p in prayers" :key="p.id">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title" x-text="p.title"></div>
                            <span x-show="p.is_urgent" class="badge badge-urgent">🔥 Терміново</span>
                        </div>
                        <div class="card-text" x-text="p.description"></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <div class="card-meta">
                                <span x-text="p.author_name"></span> &middot; <span x-text="p.created_at"></span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 13px; color: var(--tg-hint);">🙏 <span x-text="p.prayer_count"></span></span>
                                <button class="btn btn-pray btn-sm" @click="pray(p)" :disabled="p._prayed">
                                    <span x-text="p._prayed ? '✓ Помолився' : '🙏 Помолитися'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Profile Tab -->
            <div class="tab-content" x-show="tab === 'profile'" x-cloak>
                <template x-if="loading.profile">
                    <div class="loading"><div class="spinner"></div> Завантаження...</div>
                </template>
                <template x-if="!loading.profile && profile">
                    <div>
                        <div class="card" style="text-align: center; padding: 24px 14px;">
                            <div class="profile-avatar">
                                <template x-if="profile.photo_url">
                                    <img :src="profile.photo_url" alt="">
                                </template>
                                <template x-if="!profile.photo_url">
                                    <span x-text="(profile.first_name?.[0] || '') + (profile.last_name?.[0] || '')"></span>
                                </template>
                            </div>
                            <div class="profile-name" x-text="profile.full_name"></div>
                        </div>

                        <template x-if="profile.ministries && profile.ministries.length > 0">
                            <div>
                                <div class="section-header">Служіння</div>
                                <div class="card" style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    <template x-for="m in profile.ministries" :key="m.name">
                                        <span class="chip">
                                            <span class="ministry-dot" :style="`background: ${m.color || '#6b7280'}`" style="margin-top: 0;"></span>
                                            <span x-text="m.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="profile.groups && profile.groups.length > 0">
                            <div>
                                <div class="section-header">Групи</div>
                                <div class="card" style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    <template x-for="g in profile.groups" :key="g.name">
                                        <span class="chip" x-text="g.name"></span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="section-header">Статистика</div>
                        <div class="card">
                            <div class="profile-stat">
                                <span>Найближчі призначення</span>
                                <span class="profile-stat-value" x-text="profile.stats.upcoming_assignments"></span>
                            </div>
                            <div class="profile-stat">
                                <span>Підтверджені</span>
                                <span class="profile-stat-value" x-text="profile.stats.confirmed_assignments"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tab Bar -->
            <nav class="tab-bar">
                <div class="tab-bar-item" :class="tab === 'events' && 'active'" @click="switchTab('events')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Події</span>
                </div>
                <div class="tab-bar-item" :class="tab === 'assignments' && 'active'" @click="switchTab('assignments')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                    <span>Служіння</span>
                </div>
                <div class="tab-bar-item" :class="tab === 'announcements' && 'active'" @click="switchTab('announcements')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <span>Оголошення</span>
                </div>
                <div class="tab-bar-item" :class="tab === 'prayers' && 'active'" @click="switchTab('prayers')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    <span>Молитви</span>
                </div>
                <div class="tab-bar-item" :class="tab === 'profile' && 'active'" @click="switchTab('profile')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Профіль</span>
                </div>
            </nav>
        </div>
    </template>

    <script>
    function tmaApp() {
        return {
            tab: 'events',
            person: null,
            loadingInit: true,

            events: [],
            assignmentsList: [],
            responsibilitiesList: [],
            announcements: [],
            prayers: [],
            profile: null,

            debugInfo: '',

            loading: {
                events: false,
                assignments: false,
                announcements: false,
                prayers: false,
                profile: false,
            },

            loaded: {
                events: false,
                assignments: false,
                announcements: false,
                prayers: false,
                profile: false,
            },

            get initData() {
                return window.Telegram?.WebApp?.initData || '';
            },

            get authToken() {
                return new URLSearchParams(window.location.search).get('token') || '';
            },

            async init() {
                const tg = window.Telegram?.WebApp;
                if (tg) {
                    tg.ready();
                    tg.expand();
                }

                // Check if person is linked by loading profile
                try {
                    const res = await this.api('GET', '/api/tma/profile');
                    if (res && res.data) {
                        this.person = res.data;
                        this.profile = res.data;
                        this.loaded.profile = true;
                    }
                } catch (e) {
                    const errBody = e._body || e.message || String(e);
                    this.debugInfo = JSON.stringify({
                        error: errBody,
                        initDataLen: this.initData?.length || 0,
                        initDataPreview: this.initData?.substring(0, 100) || '(empty)',
                        tgWebApp: !!window.Telegram?.WebApp,
                        platform: window.Telegram?.WebApp?.platform,
                        version: window.Telegram?.WebApp?.version,
                        hash: window.location.hash?.substring(0, 200) || '(empty)',
                        href: window.location.href,
                    }, null, 2);
                    this.person = null;
                }

                this.loadingInit = false;

                if (this.person) {
                    this.loadTab('events');
                }
            },

            switchTab(name) {
                this.tab = name;
                if (!this.loaded[name]) {
                    this.loadTab(name);
                }
            },

            async loadTab(name) {
                this.loading[name] = true;
                try {
                    switch (name) {
                        case 'events': {
                            const res = await this.api('GET', '/api/tma/events');
                            this.events = res?.data || [];
                            break;
                        }
                        case 'assignments': {
                            const res = await this.api('GET', '/api/tma/assignments');
                            this.assignmentsList = (res?.assignments || []).map(a => ({...a, _loading: false}));
                            this.responsibilitiesList = (res?.responsibilities || []).map(r => ({...r, _loading: false}));
                            break;
                        }
                        case 'announcements': {
                            const res = await this.api('GET', '/api/tma/announcements');
                            this.announcements = res?.data || [];
                            break;
                        }
                        case 'prayers': {
                            const res = await this.api('GET', '/api/tma/prayers');
                            this.prayers = (res?.data || []).map(p => ({...p, _prayed: false}));
                            break;
                        }
                        case 'profile': {
                            const res = await this.api('GET', '/api/tma/profile');
                            this.profile = res?.data || null;
                            break;
                        }
                    }
                    this.loaded[name] = true;
                } catch (e) {
                    console.error('Load tab error:', name, e);
                }
                this.loading[name] = false;
            },

            async confirmAssignment(id) {
                const item = this.assignmentsList.find(a => a.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/assignments/${id}/confirm`);
                    if (res?.success) {
                        item.status = 'confirmed';
                        item.status_label = 'Підтверджено';
                        this.haptic('success');
                    }
                } catch (e) { console.error(e); }
                item._loading = false;
            },

            async declineAssignment(id) {
                const item = this.assignmentsList.find(a => a.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/assignments/${id}/decline`);
                    if (res?.success) {
                        item.status = 'declined';
                        item.status_label = 'Відхилено';
                        this.haptic('warning');
                    }
                } catch (e) { console.error(e); }
                item._loading = false;
            },

            async confirmResponsibility(id) {
                const item = this.responsibilitiesList.find(r => r.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/responsibilities/${id}/confirm`);
                    if (res?.success) {
                        item.status = 'confirmed';
                        item.status_label = 'Підтверджено';
                        this.haptic('success');
                    }
                } catch (e) { console.error(e); }
                item._loading = false;
            },

            async declineResponsibility(id) {
                const item = this.responsibilitiesList.find(r => r.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/responsibilities/${id}/decline`);
                    if (res?.success) {
                        item.status = 'declined';
                        item.status_label = 'Відхилено';
                        this.haptic('warning');
                    }
                } catch (e) { console.error(e); }
                item._loading = false;
            },

            async pray(prayer) {
                if (prayer._prayed) return;
                try {
                    const res = await this.api('POST', `/api/tma/prayers/${prayer.id}/pray`);
                    if (res?.success) {
                        prayer.prayer_count = res.prayer_count;
                        prayer._prayed = true;
                        this.haptic('success');
                    }
                } catch (e) { console.error(e); }
            },

            async api(method, url) {
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                };
                if (this.initData) {
                    headers['X-Telegram-Init-Data'] = this.initData;
                }
                if (this.authToken) {
                    headers['X-TMA-Auth-Token'] = this.authToken;
                }
                const opts = { method, headers };
                const res = await fetch(url, opts);
                if (!res.ok) {
                    const body = await res.text();
                    const err = new Error(`API ${res.status}`);
                    err._body = body;
                    throw err;
                }
                return res.json();
            },

            haptic(type) {
                try {
                    window.Telegram?.WebApp?.HapticFeedback?.notificationOccurred(type);
                } catch (e) {}
            },

            formatText(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
            },
        };
    }
    </script>
</body>
</html>
