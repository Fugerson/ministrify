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
        [x-cloak] { display: none !important; }

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
        .card-clickable { cursor: pointer; -webkit-tap-highlight-color: transparent; transition: opacity 0.1s; }
        .card-clickable:active { opacity: 0.7; }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 600;
            padding: 3px 8px; border-radius: 6px;
            white-space: nowrap;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-declined { background: #fee2e2; color: #991b1b; }
        .badge-urgent { background: #fee2e2; color: #991b1b; }
        .badge-pin { background: #dbeafe; color: #1e40af; }
        .badge-today { background: #fef3c7; color: #92400e; }
        .badge-service { background: #ede9fe; color: #5b21b6; }
        .badge-song { background: #f0fdf4; color: #166534; font-weight: 500; }

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
            -webkit-tap-highlight-color: transparent;
        }
        .btn:active { opacity: 0.7; }
        .btn-confirm { background: #10b981; color: #fff; }
        .btn-decline { background: #ef4444; color: #fff; }
        .btn-pray { background: var(--tg-btn); color: var(--tg-btn-text); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-group { display: flex; gap: 8px; margin-top: 10px; }
        .btn-refresh {
            background: none; border: none; color: var(--tg-link);
            font-size: 13px; cursor: pointer; padding: 4px 8px;
            -webkit-tap-highlight-color: transparent;
        }

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
            gap: 2px; padding: 4px 8px; position: relative;
            font-size: 10px; color: var(--tg-hint);
            cursor: pointer; transition: color 0.15s;
            -webkit-tap-highlight-color: transparent;
        }
        .tab-bar-item.active { color: var(--tg-btn); }
        .tab-bar-item svg { width: 22px; height: 22px; }
        .tab-badge {
            position: absolute; top: 0; right: 2px;
            background: #ef4444; color: #fff;
            font-size: 9px; font-weight: 700;
            min-width: 16px; height: 16px;
            border-radius: 8px; display: flex;
            align-items: center; justify-content: center;
            padding: 0 4px;
        }

        /* Loading & Empty */
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
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Date divider */
        .date-divider {
            font-size: 14px; font-weight: 600;
            color: var(--tg-text);
            padding: 14px 4px 6px;
        }
        .date-divider:first-child { padding-top: 4px; }

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
        .profile-role { text-align: center; font-size: 13px; color: var(--tg-hint); margin-top: 4px; }
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

        /* Expandable */
        .expand-text {
            font-size: 13px; line-height: 1.5;
            color: var(--tg-hint);
            margin-top: 8px;
            padding-top: 8px;
            border-top: 0.5px solid var(--tg-secondary-bg);
        }

        /* Birthday */
        .birthday-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0;
            border-bottom: 0.5px solid var(--tg-secondary-bg);
        }
        .birthday-item:last-child { border-bottom: none; }

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
                <div class="section-header">
                    <span>Найближчі події</span>
                    <button class="btn-refresh" @click="refreshTab('events')">↻</button>
                </div>
                <template x-if="loading.events">
                    <div class="loading"><div class="spinner"></div></div>
                </template>
                <template x-if="!loading.events && events.length === 0">
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        Немає подій на найближчі 30 днів
                    </div>
                </template>
                <template x-if="!loading.events && events.length > 0">
                    <div>
                        <template x-for="group in groupedEvents" :key="group.date">
                            <div>
                                <div class="date-divider" x-text="group.label"></div>
                                <template x-for="event in group.items" :key="event.id">
                                    <div class="card card-clickable" @click="event._expanded = !event._expanded">
                                        <div class="card-header">
                                            <div style="display: flex; gap: 10px; align-items: flex-start; flex: 1; min-width: 0;">
                                                <div x-show="event.ministry" class="ministry-dot" :style="`background: ${event.ministry?.color || '#6b7280'}`"></div>
                                                <div style="min-width: 0;">
                                                    <div class="card-title" x-text="event.title"></div>
                                                    <div class="card-subtitle" x-text="event.ministry?.name || ''"></div>
                                                </div>
                                            </div>
                                            <span x-show="event.is_service" class="badge badge-service" x-text="event.service_type || 'Служіння'"></span>
                                        </div>
                                        <div class="card-meta" style="display: flex; gap: 12px; flex-wrap: wrap;">
                                            <span x-show="event.time">🕐 <span x-text="event.time + (event.end_time ? ' — ' + event.end_time : '')"></span></span>
                                            <span x-show="event.location">📍 <span x-text="event.location"></span></span>
                                        </div>
                                        <div class="expand-text" x-show="event._expanded && event.notes" x-text="event.notes" x-transition></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Assignments Tab -->
            <div class="tab-content" x-show="tab === 'assignments'" x-cloak>
                <div class="section-header">
                    <span>Мої призначення</span>
                    <button class="btn-refresh" @click="refreshTab('assignments')">↻</button>
                </div>
                <template x-if="loading.assignments">
                    <div class="loading"><div class="spinner"></div></div>
                </template>
                <template x-if="!loading.assignments && allAssignmentsEmpty">
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

                <!-- Service Plan Items -->
                <template x-if="planItemsList.length > 0">
                    <div class="section-header" style="margin-top: 4px;">План служіння</div>
                </template>
                <template x-for="item in planItemsList" :key="'p-' + item.id">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">
                                    <span x-text="item.type_icon"></span> <span x-text="item.title"></span>
                                </div>
                                <div class="card-subtitle" x-text="item.event.title"></div>
                            </div>
                            <span class="badge" :class="'badge-' + item.status" x-text="statusLabel(item.status)"></span>
                        </div>
                        <div x-show="item.song" class="badge badge-song" style="margin-top: 6px;">
                            🎵 <span x-text="item.song?.title"></span>
                            <span x-show="item.song?.key" x-text="'(' + item.song?.key + ')'"></span>
                        </div>
                        <div class="card-meta" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <span>📅 <span x-text="item.event.date_formatted"></span></span>
                            <span x-show="item.event.time">🕐 <span x-text="item.event.time"></span></span>
                        </div>
                        <div class="btn-group" x-show="item.status === 'pending'">
                            <button class="btn btn-confirm btn-sm" @click="confirmPlanItem(item.id)" :disabled="item._loading">✓ Підтверджую</button>
                            <button class="btn btn-decline btn-sm" @click="declinePlanItem(item.id)" :disabled="item._loading">✗ Не можу</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-content" x-show="tab === 'announcements'" x-cloak>
                <div class="section-header">
                    <span>Оголошення</span>
                    <button class="btn-refresh" @click="refreshTab('announcements')">↻</button>
                </div>
                <template x-if="loading.announcements">
                    <div class="loading"><div class="spinner"></div></div>
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
                <div class="section-header">
                    <span>Молитовні потреби</span>
                    <button class="btn-refresh" @click="refreshTab('prayers')">↻</button>
                </div>
                <template x-if="loading.prayers">
                    <div class="loading"><div class="spinner"></div></div>
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
                                <span x-text="p.author_name"></span> · <span x-text="p.created_at"></span>
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
                    <div class="loading"><div class="spinner"></div></div>
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
                            <div class="profile-role" x-show="profile.membership_label" x-text="profile.membership_label"></div>
                            <div class="card-meta" x-show="profile.joined_date" style="text-align: center; margin-top: 4px;">
                                Приєднався: <span x-text="profile.joined_date"></span>
                            </div>
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
                                <template x-for="g in profile.groups" :key="g.name">
                                    <div class="card">
                                        <div class="card-title" x-text="g.name"></div>
                                        <div class="card-meta" style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px;">
                                            <span x-show="g.meeting_day">📅 <span x-text="g.meeting_day"></span></span>
                                            <span x-show="g.meeting_time">🕐 <span x-text="g.meeting_time"></span></span>
                                            <span x-show="g.meeting_location">📍 <span x-text="g.meeting_location"></span></span>
                                        </div>
                                    </div>
                                </template>
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
                            <div class="profile-stat">
                                <span>Очікують відповіді</span>
                                <span class="profile-stat-value" style="color: var(--tg-destructive);" x-text="profile.stats.pending_assignments"></span>
                            </div>
                        </div>

                        <!-- Birthdays -->
                        <div class="section-header">
                            <span>🎂 Дні народження</span>
                            <button class="btn-refresh" @click="loadBirthdays()">↻</button>
                        </div>
                        <template x-if="loading.birthdays">
                            <div class="loading"><div class="spinner"></div></div>
                        </template>
                        <template x-if="!loading.birthdays && birthdays.length === 0">
                            <div class="card" style="text-align: center; color: var(--tg-hint); font-size: 13px; padding: 20px;">
                                Немає днів народження найближчим часом
                            </div>
                        </template>
                        <template x-if="!loading.birthdays && birthdays.length > 0">
                            <div class="card">
                                <template x-for="b in birthdays" :key="b.id">
                                    <div class="birthday-item">
                                        <div>
                                            <div style="font-size: 14px; font-weight: 500;" x-text="b.full_name"></div>
                                            <div style="font-size: 12px; color: var(--tg-hint);">
                                                <span x-text="b.date_formatted"></span>
                                                <span x-show="b.age"> · <span x-text="b.age + ' р.'"></span></span>
                                            </div>
                                        </div>
                                        <div>
                                            <span x-show="b.is_today" class="badge badge-today">🎉 Сьогодні!</span>
                                            <span x-show="!b.is_today" style="font-size: 12px; color: var(--tg-hint);" x-text="'через ' + b.days_until + ' дн.'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
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
                    <span x-show="pendingCount > 0" class="tab-badge" x-text="pendingCount"></span>
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
            planItemsList: [],
            announcements: [],
            prayers: [],
            profile: null,
            birthdays: [],

            loading: {
                events: false,
                assignments: false,
                announcements: false,
                prayers: false,
                profile: false,
                birthdays: false,
            },

            loaded: {
                events: false,
                assignments: false,
                announcements: false,
                prayers: false,
                profile: false,
                birthdays: false,
            },

            get initData() {
                return window.Telegram?.WebApp?.initData || '';
            },

            get authToken() {
                const urlToken = new URLSearchParams(window.location.search).get('token');
                if (urlToken) {
                    try { localStorage.setItem('tma_token', urlToken); } catch(e) {}
                    return urlToken;
                }
                try { return localStorage.getItem('tma_token') || ''; } catch(e) { return ''; }
            },

            get groupedEvents() {
                const groups = {};
                for (const e of this.events) {
                    if (!groups[e.date]) {
                        groups[e.date] = { date: e.date, label: e.date_formatted, items: [] };
                    }
                    groups[e.date].items.push(e);
                }
                return Object.values(groups);
            },

            get pendingCount() {
                const a = this.assignmentsList.filter(i => i.status === 'pending').length;
                const r = this.responsibilitiesList.filter(i => i.status === 'pending').length;
                const p = this.planItemsList.filter(i => i.status === 'pending').length;
                return a + r + p;
            },

            get allAssignmentsEmpty() {
                return this.assignmentsList.length === 0
                    && this.responsibilitiesList.length === 0
                    && this.planItemsList.length === 0;
            },

            statusLabel(status) {
                const map = { pending: 'Очікує', confirmed: 'Підтверджено', declined: 'Відхилено' };
                return map[status] || status;
            },

            async init() {
                const tg = window.Telegram?.WebApp;
                if (tg) {
                    tg.ready();
                    tg.expand();
                }

                try {
                    const res = await this.api('GET', '/api/tma/profile');
                    if (res && res.data) {
                        this.person = res.data;
                        this.profile = res.data;
                        this.loaded.profile = true;
                    }
                } catch (e) {
                    this.person = null;
                    // Clear stale token from localStorage on auth failure
                    try { localStorage.removeItem('tma_token'); } catch(ex) {}
                }

                this.loadingInit = false;

                if (this.person) {
                    this.loadTab('events');
                    this.loadTab('assignments');
                }
            },

            switchTab(name) {
                this.tab = name;
                if (!this.loaded[name]) {
                    this.loadTab(name);
                }
                if (name === 'profile' && !this.loaded.birthdays) {
                    this.loadBirthdays();
                }
            },

            refreshTab(name) {
                this.loaded[name] = false;
                this.loadTab(name);
            },

            async loadBirthdays() {
                this.loading.birthdays = true;
                try {
                    const res = await this.api('GET', '/api/tma/birthdays');
                    this.birthdays = res?.data || [];
                    this.loaded.birthdays = true;
                } catch (e) { console.error(e); }
                this.loading.birthdays = false;
            },

            async loadTab(name) {
                this.loading[name] = true;
                try {
                    switch (name) {
                        case 'events': {
                            const res = await this.api('GET', '/api/tma/events');
                            this.events = (res?.data || []).map(e => ({...e, _expanded: false}));
                            break;
                        }
                        case 'assignments': {
                            const res = await this.api('GET', '/api/tma/assignments');
                            this.assignmentsList = (res?.assignments || []).map(a => ({...a, _loading: false}));
                            this.responsibilitiesList = (res?.responsibilities || []).map(r => ({...r, _loading: false}));
                            this.planItemsList = (res?.plan_items || []).map(p => ({...p, _loading: false}));
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

            async confirmPlanItem(id) {
                const item = this.planItemsList.find(p => p.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/plan-items/${id}/confirm`);
                    if (res?.success) {
                        item.status = 'confirmed';
                        this.haptic('success');
                    }
                } catch (e) { console.error(e); }
                item._loading = false;
            },

            async declinePlanItem(id) {
                const item = this.planItemsList.find(p => p.id === id);
                if (!item) return;
                item._loading = true;
                try {
                    const res = await this.api('POST', `/api/tma/plan-items/${id}/decline`);
                    if (res?.success) {
                        item.status = 'declined';
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
