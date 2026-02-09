<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServicePlanController;
use App\Http\Controllers\ServicePlanTemplateController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SystemAdminController;
use App\Http\Controllers\PrivateMessageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\TelegramBroadcastController;
use App\Http\Controllers\WorshipTeamController;
use App\Http\Controllers\TelegramChatController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\QrCheckinController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\MonobankSyncController;
use App\Http\Controllers\PrivatbankSyncController;
use Illuminate\Support\Facades\Route;

// Health check endpoint (for monitoring)
Route::get('health', function () {
    $status = ['status' => 'ok', 'timestamp' => now()->toIso8601String()];

    // Check database
    try {
        \DB::connection()->getPdo();
        $status['database'] = 'ok';
    } catch (\Exception $e) {
        $status['database'] = 'error';
        $status['status'] = 'degraded';
    }

    // Check Redis
    try {
        \Illuminate\Support\Facades\Redis::ping();
        $status['redis'] = 'ok';
    } catch (\Exception $e) {
        $status['redis'] = 'error';
        $status['status'] = 'degraded';
    }

    return response()->json($status, $status['status'] === 'ok' ? 200 : 503);
})->middleware('throttle:30,1')->name('health');

// UptimeRobot webhook for Telegram alerts
Route::match(['get', 'post'], 'webhook/uptime/{secret}', function ($secret, \Illuminate\Http\Request $request) {
    // Verify secret from config (set UPTIME_WEBHOOK_SECRET in .env)
    $configSecret = config('services.uptime.webhook_secret');

    if (!$configSecret) {
        abort(403);
    }

    if (!hash_equals($configSecret, $secret)) {
        abort(403);
    }

    $botToken = config('services.telegram.alert_bot_token');
    $chatId = config('services.telegram.alert_chat_id');

    if (!$botToken || !$chatId) {
        return response()->json(['error' => 'Telegram not configured'], 500);
    }

    // Parse UptimeRobot data
    $monitorName = $request->input('monitorFriendlyName', 'Unknown');
    $alertType = $request->input('alertType', '1'); // 1 = down, 2 = up
    $alertDetails = $request->input('alertDetails', '');

    $emoji = $alertType == '1' ? '🔴' : '🟢';
    $status = $alertType == '1' ? 'DOWN' : 'UP';

    $message = "{$emoji} *Ministrify {$status}*\n\n";
    $message .= "Monitor: {$monitorName}\n";
    if ($alertDetails) {
        $message .= "Details: {$alertDetails}\n";
    }
    $message .= "Time: " . now()->format('Y-m-d H:i:s');

    // Send to Telegram
    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
    ]);

    return response()->json(['ok' => true]);
})->middleware('throttle:10,1')->withoutMiddleware(['web'])->name('uptime.webhook');

// QR Check-in (public with optional auth)
Route::get('checkin/{token}', [QrCheckinController::class, 'show'])->middleware('throttle:30,1')->name('checkin.show');

// Monobank webhook (public, no CSRF)
Route::match(['get', 'post'], 'monobank/webhook/{secret}', [MonobankSyncController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->name('monobank.webhook')
    ->withoutMiddleware(['web']);

// Landing pages (public)
Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('features', [LandingController::class, 'features'])->name('landing.features');
Route::get('contact', [LandingController::class, 'contact'])->name('landing.contact');
Route::post('contact', [LandingController::class, 'sendContact'])->name('landing.contact.send')->middleware('throttle:5,1');
Route::get('register-church', [LandingController::class, 'register'])->name('landing.register');
Route::post('register-church', [LandingController::class, 'processRegistration'])->name('landing.register.process')->middleware('throttle:3,1');
Route::get('docs', [LandingController::class, 'docs'])->name('landing.docs');
Route::get('faq', [LandingController::class, 'faq'])->name('landing.faq');
Route::get('terms', [LandingController::class, 'terms'])->name('landing.terms');
Route::get('privacy', [LandingController::class, 'privacy'])->name('landing.privacy');

// Public church mini-sites with rate limiting for forms
Route::prefix('c/{slug}')->name('public.')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [PublicSiteController::class, 'church'])->name('church');
    Route::get('/events', [PublicSiteController::class, 'events'])->name('events');
    Route::get('/events/{event}', [PublicSiteController::class, 'event'])->name('event');
    Route::post('/events/{event}/register', [PublicSiteController::class, 'registerForEvent'])->name('event.register')->middleware('throttle:10,1');
    Route::get('/ministry/{ministrySlug}', [PublicSiteController::class, 'ministry'])->name('ministry');
    Route::post('/ministry/{ministrySlug}/join', [PublicSiteController::class, 'joinMinistry'])->name('ministry.join')->middleware('throttle:10,1');
    Route::get('/group/{groupSlug}', [PublicSiteController::class, 'group'])->name('group');
    Route::post('/group/{groupSlug}/join', [PublicSiteController::class, 'joinGroup'])->name('group.join')->middleware('throttle:10,1');
    Route::get('/donate', [PublicSiteController::class, 'donate'])->name('donate');
    Route::post('/donate', [PublicSiteController::class, 'processDonation'])->name('donate.process')->middleware('throttle:5,1');
    Route::get('/donate/success', [PublicSiteController::class, 'donateSuccess'])->name('donate.success');
    Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
});

// Authentication routes with rate limiting
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle.login');
    Route::get('register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
    Route::get('join', [RegisterController::class, 'showJoin'])->name('join');
    Route::post('join', [RegisterController::class, 'join'])->name('join.store')->middleware('throttle:5,1');
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');

    // Google OAuth
    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('auth/google/register', [SocialAuthController::class, 'showRegisterOptions'])->name('auth.google.register');
    Route::post('auth/google/register', [SocialAuthController::class, 'completeRegistration'])->name('auth.google.complete');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::post('stop-impersonating', [SystemAdminController::class, 'stopImpersonating'])->name('stop-impersonating')->middleware('auth');

// Email Verification
Route::middleware('auth')->group(function () {
    Route::get('email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Verification link can be clicked without being logged in
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Two-Factor Authentication (throttled to prevent brute force)
Route::get('two-factor/challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
Route::post('two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->middleware('throttle:5,1')->name('two-factor.verify');

Route::middleware(['auth', 'church'])->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('show');
    Route::get('enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('enable');
    Route::post('confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('confirm');
    Route::delete('disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
    Route::post('regenerate', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate');
});

// System Admin Panel (Super Admin only)
Route::middleware(['auth', 'super_admin'])->prefix('system-admin')->name('system.')->group(function () {
    Route::get('/', [SystemAdminController::class, 'index'])->name('index');

    // Churches
    Route::get('churches', [SystemAdminController::class, 'churches'])->name('churches.index');
    Route::get('churches/create', [SystemAdminController::class, 'createChurch'])->name('churches.create');
    Route::post('churches', [SystemAdminController::class, 'storeChurch'])->name('churches.store');
    Route::get('churches/{church}', [SystemAdminController::class, 'showChurch'])->name('churches.show');
    Route::match(['get', 'post'], 'churches/{church}/switch', [SystemAdminController::class, 'switchToChurch'])->name('churches.switch');
    Route::delete('churches/{church}', [SystemAdminController::class, 'destroyChurch'])->name('churches.destroy');

    // Users
    Route::get('users', [SystemAdminController::class, 'users'])->name('users.index');
    Route::get('users/{user}/edit', [SystemAdminController::class, 'editUser'])->name('users.edit');
    Route::put('users/{user}', [SystemAdminController::class, 'updateUser'])->name('users.update');
    Route::delete('users/{user}', [SystemAdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('users/{user}/impersonate', [SystemAdminController::class, 'impersonateUser'])->name('users.impersonate');
    Route::post('users/{id}/restore', [SystemAdminController::class, 'restoreUser'])->name('users.restore');
    Route::delete('users/{id}/force-delete', [SystemAdminController::class, 'forceDeleteUser'])->name('users.forceDelete');

    // Audit Logs
    Route::get('audit-logs', [SystemAdminController::class, 'auditLogs'])->name('audit-logs');

    // Page Visits
    Route::get('page-visits', [SystemAdminController::class, 'pageVisits'])->name('page-visits');

    // Support Tickets
    Route::get('support', [SystemAdminController::class, 'supportTickets'])->name('support.index');
    Route::post('support', [SystemAdminController::class, 'storeSupportTicket'])->name('support.store');
    Route::post('support/update-status', [SystemAdminController::class, 'updateTicketStatus'])->name('support.update.status');
    Route::get('support/{ticket}', [SystemAdminController::class, 'showSupportTicket'])->name('support.show');
    Route::post('support/{ticket}/reply', [SystemAdminController::class, 'replySupportTicket'])->name('support.reply');
    Route::put('support/{ticket}', [SystemAdminController::class, 'updateSupportTicket'])->name('support.update');
    Route::delete('support/{ticket}', [SystemAdminController::class, 'destroySupportTicket'])->name('support.destroy');

    // Admin Tasks
    Route::get('tasks', [SystemAdminController::class, 'tasks'])->name('tasks.index');
    Route::get('tasks/create', [SystemAdminController::class, 'createTask'])->name('tasks.create');
    Route::post('tasks', [SystemAdminController::class, 'storeTask'])->name('tasks.store');
    Route::get('tasks/{task}/edit', [SystemAdminController::class, 'editTask'])->name('tasks.edit');
    Route::put('tasks/{task}', [SystemAdminController::class, 'updateTask'])->name('tasks.update');
    Route::patch('tasks/{task}/status', [SystemAdminController::class, 'updateTaskStatus'])->name('tasks.update-status');
    Route::delete('tasks/{task}', [SystemAdminController::class, 'destroyTask'])->name('tasks.destroy');

    // Exit church context
    Route::post('exit-church', [SystemAdminController::class, 'exitChurchContext'])->name('exit-church');
});

// Protected routes (require verified email)
Route::middleware(['auth', 'verified', 'church', 'onboarding'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/charts', [DashboardController::class, 'chartData'])->name('dashboard.charts');
    Route::get('dashboard/birthdays', [DashboardController::class, 'birthdays'])->name('dashboard.birthdays');
    Route::post('dashboard/layout', [DashboardController::class, 'saveLayout'])->name('dashboard.layout.save');
    Route::get('dashboard/calendar-events', [DashboardController::class, 'calendarEventsApi'])->name('dashboard.calendar-events');

    // People
    Route::resource('people', PersonController::class);
    Route::post('people/{person}/restore', [PersonController::class, 'restore'])->name('people.restore');
    Route::post('people/{person}/update-role', [PersonController::class, 'updateRole'])->name('people.update-role');
    Route::post('people/{person}/update-email', [PersonController::class, 'updateEmail'])->name('people.update-email');
    Route::post('people/{person}/create-account', [PersonController::class, 'createAccount'])->name('people.create-account');
    Route::post('people/{person}/reset-password', [PersonController::class, 'resetPassword'])->name('people.reset-password');
    Route::post('people/{person}/update-shepherd', [PersonController::class, 'updateShepherd'])->name('people.update-shepherd');
    Route::get('people-export', [PersonController::class, 'export'])->name('people.export');
    Route::post('people-import', [PersonController::class, 'import'])->name('people.import');
    Route::post('people-bulk-action', [PersonController::class, 'bulkAction'])->name('people.bulk-action');
    Route::get('people-quick-edit', [PersonController::class, 'quickEdit'])->name('people.quick-edit');
    Route::post('people-quick-save', [PersonController::class, 'quickSave'])->name('people.quick-save');
    Route::post('people/{person}/upload-photo', [PersonController::class, 'uploadPhoto'])->name('people.upload-photo');
    Route::delete('people/{person}/delete-photo', [PersonController::class, 'deletePhoto'])->name('people.delete-photo');

    // Family Relationships
    Route::post('people/{person}/family', [\App\Http\Controllers\FamilyRelationshipController::class, 'store'])->name('family.store');
    Route::delete('family/{familyRelationship}', [\App\Http\Controllers\FamilyRelationshipController::class, 'destroy'])->name('family.destroy');
    Route::get('people/{person}/family/search', [\App\Http\Controllers\FamilyRelationshipController::class, 'search'])->name('family.search');

    // Migration tools
    Route::prefix('migrate')->name('migration.')->group(function () {
        Route::get('planning-center', [MigrationController::class, 'planningCenter'])->name('planning-center');
        Route::post('planning-center/preview', [MigrationController::class, 'preview'])->name('planning-center.preview');
        Route::post('planning-center/import', [MigrationController::class, 'import'])->name('planning-center.import');
    });

    // Tags
    Route::resource('tags', TagController::class)->except(['show', 'create', 'edit']);

    // Ministries
    Route::resource('ministries', MinistryController::class);
    Route::post('ministries/{ministry}/members', [MinistryController::class, 'addMember'])->name('ministries.members.add');
    Route::delete('ministries/{ministry}/members/{person}', [MinistryController::class, 'removeMember'])->name('ministries.members.remove');
    Route::put('ministries/{ministry}/members/{person}', [MinistryController::class, 'updateMemberPositions'])->name('ministries.members.update');
    Route::post('ministries/{ministry}/toggle-privacy', [MinistryController::class, 'togglePrivacy'])->name('ministries.toggle-privacy');
    Route::post('ministries/{ministry}/update-visibility', [MinistryController::class, 'updateVisibility'])->name('ministries.update-visibility');
    Route::post('ministries/{ministry}/worship-roles', [MinistryController::class, 'storeWorshipRole'])->name('ministries.worship-roles.store');
    Route::put('ministries/{ministry}/worship-roles/{role}', [MinistryController::class, 'updateWorshipRole'])->name('ministries.worship-roles.update');
    Route::delete('ministries/{ministry}/worship-roles/{role}', [MinistryController::class, 'destroyWorshipRole'])->name('ministries.worship-roles.destroy');

    // Ministry Resources
    Route::get('ministries/{ministry}/resources', [ResourceController::class, 'ministryIndex'])->name('ministries.resources');
    Route::get('ministries/{ministry}/resources/folder/{folder}', [ResourceController::class, 'ministryIndex'])->name('ministries.resources.folder');
    Route::post('ministries/{ministry}/resources/folder', [ResourceController::class, 'ministryCreateFolder'])->name('ministries.resources.folder.create');
    Route::post('ministries/{ministry}/resources/upload', [ResourceController::class, 'ministryUpload'])->name('ministries.resources.upload');
    Route::post('ministries/{ministry}/resources/document', [ResourceController::class, 'ministryCreateDocument'])->name('ministries.resources.document.create');
    Route::put('resources/{resource}/content', [ResourceController::class, 'updateDocument'])->name('resources.updateContent');

    // Worship Team (for ministries with is_worship_ministry)
    Route::get('ministries/{ministry}/worship-events', [WorshipTeamController::class, 'events'])->name('ministries.worship-events');
    Route::get('ministries/{ministry}/worship-stats', [WorshipTeamController::class, 'stats'])->name('ministries.worship-stats');
    Route::get('ministries/{ministry}/worship-events/{event}', [WorshipTeamController::class, 'eventShow'])->name('ministries.worship-events.show');
    Route::get('ministries/{ministry}/worship-events/{event}/data', [WorshipTeamController::class, 'eventData'])->name('ministries.worship-events.data');
    Route::post('events/{event}/songs', [WorshipTeamController::class, 'addSong'])->name('events.songs.add');
    Route::delete('events/{event}/songs/{song}', [WorshipTeamController::class, 'removeSong'])->name('events.songs.remove');
    Route::post('events/{event}/songs/reorder', [WorshipTeamController::class, 'reorderSongs'])->name('events.songs.reorder');
    Route::post('events/{event}/worship-team', [WorshipTeamController::class, 'addTeamMember'])->name('events.worship-team.add');
    Route::delete('events/{event}/worship-team/{member}', [WorshipTeamController::class, 'removeTeamMember'])->name('events.worship-team.remove');

    // Worship Roles Settings
    Route::get('settings/worship-roles', [WorshipTeamController::class, 'roles'])->name('settings.worship-roles');
    Route::post('settings/worship-roles', [WorshipTeamController::class, 'storeRole'])->name('settings.worship-roles.store');
    Route::put('settings/worship-roles/{role}', [WorshipTeamController::class, 'updateRole'])->name('settings.worship-roles.update');
    Route::delete('settings/worship-roles/{role}', [WorshipTeamController::class, 'destroyRole'])->name('settings.worship-roles.destroy');

    // Person worship skills
    Route::put('people/{person}/worship-skills', [WorshipTeamController::class, 'updateSkills'])->name('people.worship-skills.update');
    Route::get('worship-roles/{role}/members', [WorshipTeamController::class, 'getMembersWithSkill'])->name('worship-roles.members');

    // Ministry Goals & Tasks
    Route::prefix('ministries/{ministry}/goals')->name('ministries.goals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MinistryGoalController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\MinistryGoalController::class, 'storeGoal'])->name('store');
        Route::put('{goal}', [\App\Http\Controllers\MinistryGoalController::class, 'updateGoal'])->name('update');
        Route::delete('{goal}', [\App\Http\Controllers\MinistryGoalController::class, 'destroyGoal'])->name('destroy');
    });
    Route::post('ministries/{ministry}/vision', [\App\Http\Controllers\MinistryGoalController::class, 'updateVision'])->name('ministries.vision.update');
    Route::prefix('ministries/{ministry}/tasks')->name('ministries.tasks.')->group(function () {
        Route::post('/', [\App\Http\Controllers\MinistryGoalController::class, 'storeTask'])->name('store');
        Route::put('{task}', [\App\Http\Controllers\MinistryGoalController::class, 'updateTask'])->name('update');
        Route::post('{task}/toggle', [\App\Http\Controllers\MinistryGoalController::class, 'toggleTask'])->name('toggle');
        Route::patch('{task}/status', [\App\Http\Controllers\MinistryGoalController::class, 'updateTaskStatus'])->name('status');
        Route::delete('{task}', [\App\Http\Controllers\MinistryGoalController::class, 'destroyTask'])->name('destroy');
    });

    // Positions
    Route::post('ministries/{ministry}/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('positions/reorder', [PositionController::class, 'reorder'])->name('positions.reorder');

    // Ministry Meetings
    Route::prefix('ministries/{ministry}/meetings')->name('meetings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MeetingController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\MeetingController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\MeetingController::class, 'store'])->name('store');
        Route::get('{meeting}', [\App\Http\Controllers\MeetingController::class, 'show'])->name('show');
        Route::get('{meeting}/edit', [\App\Http\Controllers\MeetingController::class, 'edit'])->name('edit');
        Route::put('{meeting}', [\App\Http\Controllers\MeetingController::class, 'update'])->name('update');
        Route::delete('{meeting}', [\App\Http\Controllers\MeetingController::class, 'destroy'])->name('destroy');
        Route::get('{meeting}/copy', [\App\Http\Controllers\MeetingController::class, 'copy'])->name('copy');
        Route::post('{meeting}/copy', [\App\Http\Controllers\MeetingController::class, 'storeCopy'])->name('copy.store');

        // Agenda items
        Route::post('{meeting}/agenda', [\App\Http\Controllers\MeetingController::class, 'storeAgendaItem'])->name('agenda.store');
        Route::post('{meeting}/agenda/reorder', [\App\Http\Controllers\MeetingController::class, 'reorderAgendaItems'])->name('agenda.reorder');

        // Materials
        Route::post('{meeting}/materials', [\App\Http\Controllers\MeetingController::class, 'storeMaterial'])->name('materials.store');

        // Attendees
        Route::post('{meeting}/attendees', [\App\Http\Controllers\MeetingController::class, 'storeAttendee'])->name('attendees.store');
        Route::post('{meeting}/attendees/mark-all', [\App\Http\Controllers\MeetingController::class, 'markAllAttended'])->name('attendees.mark-all');
    });

    // Meeting items (standalone routes)
    Route::put('agenda-items/{item}', [\App\Http\Controllers\MeetingController::class, 'updateAgendaItem'])->name('meetings.agenda.update');
    Route::post('agenda-items/{item}/toggle', [\App\Http\Controllers\MeetingController::class, 'toggleAgendaItem'])->name('meetings.agenda.toggle');
    Route::delete('agenda-items/{item}', [\App\Http\Controllers\MeetingController::class, 'destroyAgendaItem'])->name('meetings.agenda.destroy');
    Route::delete('meeting-materials/{material}', [\App\Http\Controllers\MeetingController::class, 'destroyMaterial'])->name('meetings.materials.destroy');
    Route::put('meeting-attendees/{attendee}', [\App\Http\Controllers\MeetingController::class, 'updateAttendee'])->name('meetings.attendees.update');
    Route::delete('meeting-attendees/{attendee}', [\App\Http\Controllers\MeetingController::class, 'destroyAttendee'])->name('meetings.attendees.destroy');

    // Schedule/Events
    Route::resource('events', EventController::class);
    Route::get('schedule', [EventController::class, 'schedule'])->name('schedule');
    Route::get('calendar', [EventController::class, 'calendar'])->name('calendar');
    Route::get('qr-scanner', [QrCheckinController::class, 'scanner'])->name('qr-scanner');
    Route::post('events/{event}/toggle-qr-checkin', [QrCheckinController::class, 'toggleQrCheckin'])->name('events.toggle-qr-checkin');
    Route::post('events/{event}/generate-qr', [QrCheckinController::class, 'generateQr'])->name('events.generate-qr');
    Route::post('events/{event}/attendance', [EventController::class, 'saveAttendance'])->name('events.attendance.save');

    // Event Responsibilities
    Route::prefix('events/{event}/responsibilities')->name('events.responsibilities.')->group(function () {
        Route::post('/', [\App\Http\Controllers\EventResponsibilityController::class, 'store'])->name('store');
        Route::get('/poll', [\App\Http\Controllers\EventResponsibilityController::class, 'poll'])->name('poll');
    });
    Route::prefix('responsibilities')->name('responsibilities.')->group(function () {
        Route::post('{responsibility}/assign', [\App\Http\Controllers\EventResponsibilityController::class, 'assign'])->name('assign');
        Route::post('{responsibility}/unassign', [\App\Http\Controllers\EventResponsibilityController::class, 'unassign'])->name('unassign');
        Route::post('{responsibility}/confirm', [\App\Http\Controllers\EventResponsibilityController::class, 'confirm'])->name('confirm');
        Route::post('{responsibility}/decline', [\App\Http\Controllers\EventResponsibilityController::class, 'decline'])->name('decline');
        Route::post('{responsibility}/resend', [\App\Http\Controllers\EventResponsibilityController::class, 'resend'])->name('resend');
        Route::put('{responsibility}', [\App\Http\Controllers\EventResponsibilityController::class, 'update'])->name('update');
        Route::delete('{responsibility}', [\App\Http\Controllers\EventResponsibilityController::class, 'destroy'])->name('destroy');
    });

    // Service Plan
    Route::prefix('events/{event}/plan')->name('events.plan.')->group(function () {
        Route::post('/', [ServicePlanController::class, 'store'])->name('store');
        Route::get('/print', [ServicePlanController::class, 'print'])->name('print');
        Route::post('/reorder', [ServicePlanController::class, 'reorder'])->name('reorder');
        Route::post('/quick-add', [ServicePlanController::class, 'quickAdd'])->name('quick-add');
        Route::post('/apply-template', [ServicePlanController::class, 'applyTemplate'])->name('apply-template');
        Route::post('/bulk-add', [ServicePlanController::class, 'bulkAdd'])->name('bulk-add');
        Route::post('/parse-text', [ServicePlanController::class, 'parseText'])->name('parse-text');
        Route::post('/duplicate/{source}', [ServicePlanController::class, 'duplicate'])->name('duplicate');
        Route::get('/{item}/data', [ServicePlanController::class, 'itemData'])->name('item.data');
        Route::put('/{item}', [ServicePlanController::class, 'update'])->name('update');
        Route::delete('/{item}', [ServicePlanController::class, 'destroy'])->name('destroy');
        Route::post('/{item}/status', [ServicePlanController::class, 'updateStatus'])->name('status');
        Route::post('/{item}/notify', [ServicePlanController::class, 'sendNotification'])->name('notify');
    });

    // Service Plan Templates
    Route::prefix('service-plan-templates')->name('service-plan-templates.')->group(function () {
        Route::get('/', [ServicePlanTemplateController::class, 'index'])->name('index');
        Route::post('/from-event/{event}', [ServicePlanTemplateController::class, 'store'])->name('store');
        Route::post('/apply/{event}/{template}', [ServicePlanTemplateController::class, 'apply'])->name('apply');
        Route::delete('/{template}', [ServicePlanTemplateController::class, 'destroy'])->name('destroy');
    });

    // Calendar Export/Import
    Route::get('calendar/export', [EventController::class, 'exportIcal'])->name('calendar.export');
    Route::get('calendar/import', [EventController::class, 'importForm'])->name('calendar.import');
    Route::post('calendar/import', [EventController::class, 'importIcal'])->name('calendar.import.store');
    Route::get('events/{event}/google', [EventController::class, 'addToGoogle'])->name('events.google');

    // Rotation
    Route::prefix('rotation')->name('rotation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RotationController::class, 'index'])->name('index');
        Route::get('ministry/{ministry}', [\App\Http\Controllers\RotationController::class, 'ministry'])->name('ministry');
        Route::post('ministry/{ministry}/auto-assign', [\App\Http\Controllers\RotationController::class, 'autoAssignBulk'])->name('ministry.auto-assign');
        Route::post('event/{event}/auto-assign', [\App\Http\Controllers\RotationController::class, 'autoAssignEvent'])->name('event.auto-assign');
        Route::get('event/{event}/preview', [\App\Http\Controllers\RotationController::class, 'previewAutoAssign'])->name('event.preview');
        Route::get('report/{ministry}', [\App\Http\Controllers\RotationController::class, 'report'])->name('report');
        Route::get('volunteer/{person}/stats', [\App\Http\Controllers\RotationController::class, 'volunteerStats'])->name('volunteer.stats');
    });

    // Checklists
    Route::prefix('checklists')->name('checklists.')->group(function () {
        // Templates
        Route::get('templates', [ChecklistController::class, 'templates'])->name('templates');
        Route::get('templates/create', [ChecklistController::class, 'createTemplate'])->name('templates.create');
        Route::post('templates', [ChecklistController::class, 'storeTemplate'])->name('templates.store');
        Route::get('templates/{template}/edit', [ChecklistController::class, 'editTemplate'])->name('templates.edit');
        Route::put('templates/{template}', [ChecklistController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('templates/{template}', [ChecklistController::class, 'destroyTemplate'])->name('templates.destroy');

        // Event checklists
        Route::post('events/{event}', [ChecklistController::class, 'createForEvent'])->name('events.create');
        Route::delete('{checklist}', [ChecklistController::class, 'deleteChecklist'])->name('destroy');
        Route::post('{checklist}/items', [ChecklistController::class, 'addItem'])->name('items.add');
        Route::post('items/{item}/toggle', [ChecklistController::class, 'toggleItem'])->name('items.toggle');
        Route::put('items/{item}', [ChecklistController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [ChecklistController::class, 'deleteItem'])->name('items.delete');
    });

    // Finances
    Route::middleware('permission:finances')->prefix('finances')->name('finances.')->group(function () {
        // Dashboard
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('chart-data', [FinanceController::class, 'chartData'])->name('chart-data');

        // Journal (Ledger)
        Route::get('journal', [FinanceController::class, 'journal'])->name('journal');
        Route::get('journal/export', [FinanceController::class, 'journalExport'])->name('journal.export');

        // Incomes (using Transaction model)
        Route::get('incomes', [FinanceController::class, 'incomes'])->name('incomes');
        Route::get('incomes/create', [FinanceController::class, 'createIncome'])->name('incomes.create');
        Route::post('incomes', [FinanceController::class, 'storeIncome'])->name('incomes.store');
        Route::get('incomes/{transaction}/edit', [FinanceController::class, 'editIncome'])->name('incomes.edit');
        Route::put('incomes/{transaction}', [FinanceController::class, 'updateIncome'])->name('incomes.update');
        Route::delete('incomes/{transaction}', [FinanceController::class, 'destroyIncome'])->name('incomes.destroy');

        // Expenses (using Transaction model)
        Route::get('expenses', [FinanceController::class, 'expenses'])->name('expenses.index');
        Route::get('expenses/create', [FinanceController::class, 'createExpense'])->name('expenses.create');
        Route::post('expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::get('expenses/{transaction}/edit', [FinanceController::class, 'editExpense'])->name('expenses.edit');
        Route::put('expenses/{transaction}', [FinanceController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('expenses/{transaction}', [FinanceController::class, 'destroyExpense'])->name('expenses.destroy');

        // Currency Exchange
        Route::get('exchange', [FinanceController::class, 'createExchange'])->name('exchange.create');
        Route::post('exchange', [FinanceController::class, 'storeExchange'])->name('exchange.store');

        // Categories
        Route::get('categories', [FinanceController::class, 'categories'])->name('categories.index');
        Route::post('categories', [FinanceController::class, 'storeCategory'])->name('categories.store');
        Route::put('categories/{category}', [FinanceController::class, 'updateCategory'])->name('categories.update');
        Route::delete('categories/{category}', [FinanceController::class, 'destroyCategory'])->name('categories.destroy');

        // Team Budgets
        Route::get('budgets', [FinanceController::class, 'budgets'])->name('budgets');
        Route::post('budgets/{ministry}', [FinanceController::class, 'updateBudget'])->name('budgets.update');

        // Unified Cards Page
        Route::get('cards', [FinanceController::class, 'cards'])->name('cards');

        // Monobank Integration
        Route::prefix('monobank')->name('monobank.')->group(function () {
            Route::get('/', [MonobankSyncController::class, 'index'])->name('index');
            Route::get('setup', [MonobankSyncController::class, 'setup'])->name('setup');
            Route::post('connect', [MonobankSyncController::class, 'connect'])->name('connect');
            Route::post('select-account', [MonobankSyncController::class, 'selectAccount'])->name('select-account');
            Route::delete('disconnect', [MonobankSyncController::class, 'disconnect'])->name('disconnect');
            Route::post('sync', [MonobankSyncController::class, 'sync'])->name('sync');
            Route::post('toggle-auto-sync', [MonobankSyncController::class, 'toggleAutoSync'])->name('toggle-auto-sync');
            Route::post('setup-webhook', [MonobankSyncController::class, 'setupWebhook'])->name('setup-webhook');
            Route::get('{monoTransaction}/suggestions', [MonobankSyncController::class, 'getSuggestions'])->name('suggestions');
            Route::post('{monoTransaction}/import', [MonobankSyncController::class, 'import'])->name('import');
            Route::post('{monoTransaction}/ignore', [MonobankSyncController::class, 'ignore'])->name('ignore');
            Route::post('{monoTransaction}/restore', [MonobankSyncController::class, 'restore'])->name('restore');
            Route::post('bulk-import', [MonobankSyncController::class, 'bulkImport'])->name('bulk-import');
            Route::post('bulk-ignore', [MonobankSyncController::class, 'bulkIgnore'])->name('bulk-ignore');
            Route::get('transactions', [MonobankSyncController::class, 'getTransactions'])->name('transactions');
        });

        // PrivatBank Integration
        Route::prefix('privatbank')->name('privatbank.')->group(function () {
            Route::get('/', [PrivatbankSyncController::class, 'index'])->name('index');
            Route::post('connect', [PrivatbankSyncController::class, 'connect'])->name('connect');
            Route::delete('disconnect', [PrivatbankSyncController::class, 'disconnect'])->name('disconnect');
            Route::post('sync', [PrivatbankSyncController::class, 'sync'])->name('sync');
            Route::post('toggle-auto-sync', [PrivatbankSyncController::class, 'toggleAutoSync'])->name('toggle-auto-sync');
            Route::post('{privatTransaction}/import', [PrivatbankSyncController::class, 'import'])->name('import');
            Route::post('{privatTransaction}/ignore', [PrivatbankSyncController::class, 'ignore'])->name('ignore');
            Route::post('{privatTransaction}/restore', [PrivatbankSyncController::class, 'restore'])->name('restore');
            Route::post('bulk-import', [PrivatbankSyncController::class, 'bulkImport'])->name('bulk-import');
            Route::post('bulk-ignore', [PrivatbankSyncController::class, 'bulkIgnore'])->name('bulk-ignore');
        });
    });

    // Legacy expenses routes redirect
    Route::middleware('permission:finances')->group(function () {
        Route::get('expenses', fn() => redirect()->route('finances.expenses.index'))->name('expenses.index');
        Route::get('expenses/create', fn() => redirect()->route('finances.expenses.create'))->name('expenses.create');
    });

    // Attendance
    Route::resource('attendance', AttendanceController::class);
    Route::get('attendance-stats', [AttendanceController::class, 'stats'])->name('attendance.stats');

    // Settings
    Route::middleware('permission:settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('church', [SettingsController::class, 'updateChurch'])->name('church');
        Route::put('telegram', [SettingsController::class, 'updateTelegram'])->name('telegram');
        Route::post('telegram/test', [SettingsController::class, 'testTelegram'])->name('telegram.test');
        Route::post('telegram/webhook', [SettingsController::class, 'setupWebhook'])->name('telegram.webhook');
        Route::get('telegram/status', [SettingsController::class, 'getTelegramStatus'])->name('telegram.status');
        Route::put('notifications', [SettingsController::class, 'updateNotifications'])->name('notifications');
        Route::put('self-registration', [SettingsController::class, 'updateSelfRegistration'])->name('self-registration');
        Route::put('public-site', [SettingsController::class, 'updatePublicSite'])->name('public-site');
        Route::put('payments', [SettingsController::class, 'updatePaymentSettings'])->name('payments');
        Route::put('theme-color', [SettingsController::class, 'updateThemeColor'])->name('theme-color');
        Route::put('design-theme', [SettingsController::class, 'updateDesignTheme'])->name('design-theme');
        Route::put('menu-position', [SettingsController::class, 'updateMenuPosition'])->name('menu-position');
        Route::put('finance', [SettingsController::class, 'updateFinance'])->name('finance');
        Route::put('currencies', [SettingsController::class, 'updateCurrencies'])->name('currencies');

        // Role permissions management (inline in settings page)
        Route::get('permissions', fn() => redirect()->route('settings.index', ['tab' => 'permissions']))->name('permissions.index');
        Route::put('permissions', [\App\Http\Controllers\RolePermissionController::class, 'update'])->name('permissions.update');
        Route::post('permissions/reset', [\App\Http\Controllers\RolePermissionController::class, 'reset'])->name('permissions.reset');

        // Google Calendar integration
        Route::get('google-calendar/redirect', [\App\Http\Controllers\GoogleCalendarController::class, 'redirect'])->name('google-calendar.redirect');
        Route::get('google-calendar/callback', [\App\Http\Controllers\GoogleCalendarController::class, 'callback'])->name('google-calendar.callback');
        Route::post('google-calendar/disconnect', [\App\Http\Controllers\GoogleCalendarController::class, 'disconnect'])->name('google-calendar.disconnect');
        Route::get('google-calendar/calendars', [\App\Http\Controllers\GoogleCalendarController::class, 'calendars'])->name('google-calendar.calendars');
        Route::post('google-calendar/sync', [\App\Http\Controllers\GoogleCalendarController::class, 'sync'])->name('google-calendar.sync');
        Route::post('google-calendar/full-sync', [\App\Http\Controllers\GoogleCalendarController::class, 'fullSync'])->name('google-calendar.full-sync');
        Route::post('google-calendar/import', [\App\Http\Controllers\GoogleCalendarController::class, 'importFromGoogle'])->name('google-calendar.import');
        Route::post('google-calendar/preview-import', [\App\Http\Controllers\GoogleCalendarController::class, 'previewImport'])->name('google-calendar.preview-import');
        Route::post('google-calendar/import-with-resolution', [\App\Http\Controllers\GoogleCalendarController::class, 'importWithResolution'])->name('google-calendar.import-with-resolution');
        Route::post('google-calendar/delete-events', [\App\Http\Controllers\GoogleCalendarController::class, 'deleteEvents'])->name('google-calendar.delete-events');

        // Expense categories
        Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        // Income categories removed -- replaced by unified transaction categories (see transaction-categories routes below)

        // Transaction categories (unified)
        Route::post('transaction-categories', [SettingsController::class, 'storeTransactionCategory'])->name('transaction-categories.store');
        Route::put('transaction-categories/{category}', [SettingsController::class, 'updateTransactionCategory'])->name('transaction-categories.update');
        Route::delete('transaction-categories/{category}', [SettingsController::class, 'destroyTransactionCategory'])->name('transaction-categories.destroy');

        // Ministry types
        Route::post('ministry-types', [\App\Http\Controllers\MinistryTypeController::class, 'store'])->name('ministry-types.store');
        Route::put('ministry-types/{ministryType}', [\App\Http\Controllers\MinistryTypeController::class, 'update'])->name('ministry-types.update');
        Route::delete('ministry-types/{ministryType}', [\App\Http\Controllers\MinistryTypeController::class, 'destroy'])->name('ministry-types.destroy');

        // Ministries management from settings
        Route::put('ministries/{ministry}/type', [\App\Http\Controllers\MinistryTypeController::class, 'updateMinistryType'])->name('ministries.update-type');
        Route::delete('ministries/{ministry}', [\App\Http\Controllers\MinistryTypeController::class, 'destroyMinistry'])->name('ministries.destroy');

        // Users management
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
        Route::post('users/{user}/invite', [\App\Http\Controllers\UserController::class, 'sendInvite'])->name('users.invite');
        Route::get('users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'getPermissions'])->name('users.permissions');
        Route::put('users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermissions'])->name('users.permissions.update');

        // Audit Logs
        Route::get('audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('audit-logs.show');

        // Church Roles
        Route::get('church-roles', [\App\Http\Controllers\ChurchRoleController::class, 'index'])->name('church-roles.index');
        Route::post('church-roles', [\App\Http\Controllers\ChurchRoleController::class, 'store'])->name('church-roles.store');
        Route::put('church-roles/{churchRole}', [\App\Http\Controllers\ChurchRoleController::class, 'update'])->name('church-roles.update');
        Route::delete('church-roles/{churchRole}', [\App\Http\Controllers\ChurchRoleController::class, 'destroy'])->name('church-roles.destroy');
        Route::post('church-roles/{churchRole}/set-default', [\App\Http\Controllers\ChurchRoleController::class, 'setDefault'])->name('church-roles.set-default');
        Route::post('church-roles/{churchRole}/toggle-admin', [\App\Http\Controllers\ChurchRoleController::class, 'toggleAdmin'])->name('church-roles.toggle-admin');
        Route::get('church-roles/{churchRole}/permissions', [\App\Http\Controllers\ChurchRolePermissionController::class, 'getPermissions'])->name('church-roles.permissions');
        Route::put('church-roles/{churchRole}/permissions', [\App\Http\Controllers\ChurchRolePermissionController::class, 'update'])->name('church-roles.permissions.update');
        Route::post('church-roles/reorder', [\App\Http\Controllers\ChurchRoleController::class, 'reorder'])->name('church-roles.reorder');
        Route::post('church-roles/reset', [\App\Http\Controllers\ChurchRoleController::class, 'resetToDefaults'])->name('church-roles.reset');

        // Shepherds
        Route::get('shepherds', [\App\Http\Controllers\ShepherdController::class, 'index'])->name('shepherds.index');
        Route::post('shepherds', [\App\Http\Controllers\ShepherdController::class, 'store'])->name('shepherds.store');
        Route::delete('shepherds/{person}', [\App\Http\Controllers\ShepherdController::class, 'destroy'])->name('shepherds.destroy');
        Route::post('shepherds/toggle-feature', [\App\Http\Controllers\ShepherdController::class, 'toggleFeature'])->name('shepherds.toggle-feature');

        // Attendance
        Route::post('attendance/toggle-feature', [\App\Http\Controllers\AttendanceController::class, 'toggleFeature'])->name('attendance.toggle-feature');
    });

    // Telegram (admin only)
    Route::middleware('role:admin')->prefix('telegram')->name('telegram.')->group(function () {
        Route::get('broadcast', [TelegramBroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('broadcast', [TelegramBroadcastController::class, 'send'])->name('broadcast.send');
        Route::get('chat', [TelegramChatController::class, 'index'])->name('chat.index');
        Route::get('chat/{person}', [TelegramChatController::class, 'show'])->name('chat.show');
        Route::post('chat/{person}', [TelegramChatController::class, 'send'])->name('chat.send');
    });

    // Website Builder (admin only)
    Route::middleware('permission:website')->prefix('website-builder')->name('website-builder.')->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\WebsiteBuilder\WebsiteBuilderController::class, 'index'])->name('index');
        Route::get('preview', [\App\Http\Controllers\WebsiteBuilder\WebsiteBuilderController::class, 'preview'])->name('preview');

        // Templates
        Route::get('templates', [\App\Http\Controllers\WebsiteBuilder\TemplateController::class, 'index'])->name('templates.index');
        Route::post('templates/{template}/apply', [\App\Http\Controllers\WebsiteBuilder\TemplateController::class, 'apply'])->name('templates.apply');

        // Sections (drag & drop manager)
        Route::get('sections', [\App\Http\Controllers\WebsiteBuilder\SectionController::class, 'index'])->name('sections.index');
        Route::post('sections', [\App\Http\Controllers\WebsiteBuilder\SectionController::class, 'update'])->name('sections.update');
        Route::post('sections/{section}/toggle', [\App\Http\Controllers\WebsiteBuilder\SectionController::class, 'toggle'])->name('sections.toggle');

        // Design (colors, fonts, hero, navigation)
        Route::get('design', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'index'])->name('design.index');
        Route::post('design/colors', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateColors'])->name('design.colors');
        Route::post('design/fonts', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateFonts'])->name('design.fonts');
        Route::post('design/hero', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateHero'])->name('design.hero');
        Route::post('design/hero/image', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'uploadHeroImage'])->name('design.hero.image');
        Route::post('design/navigation', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateNavigation'])->name('design.navigation');
        Route::post('design/footer', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateFooter'])->name('design.footer');
        Route::post('design/css', [\App\Http\Controllers\WebsiteBuilder\DesignController::class, 'updateCustomCss'])->name('design.css');

        // About Us content
        Route::get('about', [\App\Http\Controllers\WebsiteBuilder\AboutController::class, 'edit'])->name('about.edit');
        Route::put('about', [\App\Http\Controllers\WebsiteBuilder\AboutController::class, 'update'])->name('about.update');

        // Staff/Team management
        Route::resource('team', \App\Http\Controllers\WebsiteBuilder\TeamController::class)->parameters(['team' => 'staffMember']);
        Route::post('team/reorder', [\App\Http\Controllers\WebsiteBuilder\TeamController::class, 'reorder'])->name('team.reorder');

        // Sermons management
        Route::resource('sermons', \App\Http\Controllers\WebsiteBuilder\SermonController::class);
        Route::get('sermons-series', [\App\Http\Controllers\WebsiteBuilder\SermonController::class, 'seriesIndex'])->name('sermons.series.index');
        Route::post('sermons-series', [\App\Http\Controllers\WebsiteBuilder\SermonController::class, 'seriesStore'])->name('sermons.series.store');
        Route::put('sermons-series/{series}', [\App\Http\Controllers\WebsiteBuilder\SermonController::class, 'seriesUpdate'])->name('sermons.series.update');
        Route::delete('sermons-series/{series}', [\App\Http\Controllers\WebsiteBuilder\SermonController::class, 'seriesDestroy'])->name('sermons.series.destroy');

        // Gallery management
        Route::resource('gallery', \App\Http\Controllers\WebsiteBuilder\GalleryController::class);
        Route::post('gallery/{gallery}/photos', [\App\Http\Controllers\WebsiteBuilder\GalleryController::class, 'uploadPhotos'])->name('gallery.photos.upload');
        Route::delete('gallery/photos/{photo}', [\App\Http\Controllers\WebsiteBuilder\GalleryController::class, 'deletePhoto'])->name('gallery.photos.delete');
        Route::post('gallery/{gallery}/photos/reorder', [\App\Http\Controllers\WebsiteBuilder\GalleryController::class, 'reorderPhotos'])->name('gallery.photos.reorder');
        Route::post('gallery/reorder', [\App\Http\Controllers\WebsiteBuilder\GalleryController::class, 'reorder'])->name('gallery.reorder');

        // Blog management
        Route::resource('blog', \App\Http\Controllers\WebsiteBuilder\BlogController::class)->parameters(['blog' => 'blogPost']);
        Route::get('blog-categories', [\App\Http\Controllers\WebsiteBuilder\BlogController::class, 'categoriesIndex'])->name('blog.categories.index');
        Route::post('blog-categories', [\App\Http\Controllers\WebsiteBuilder\BlogController::class, 'categoryStore'])->name('blog.categories.store');
        Route::put('blog-categories/{category}', [\App\Http\Controllers\WebsiteBuilder\BlogController::class, 'categoryUpdate'])->name('blog.categories.update');
        Route::delete('blog-categories/{category}', [\App\Http\Controllers\WebsiteBuilder\BlogController::class, 'categoryDestroy'])->name('blog.categories.destroy');
        Route::post('blog/{blogPost}/publish', [\App\Http\Controllers\WebsiteBuilder\BlogController::class, 'publish'])->name('blog.publish');

        // FAQ management
        Route::resource('faq', \App\Http\Controllers\WebsiteBuilder\FaqController::class)->except(['show']);
        Route::post('faq/reorder', [\App\Http\Controllers\WebsiteBuilder\FaqController::class, 'reorder'])->name('faq.reorder');

        // Testimonials management
        Route::resource('testimonials', \App\Http\Controllers\WebsiteBuilder\TestimonialController::class);
        Route::post('testimonials/reorder', [\App\Http\Controllers\WebsiteBuilder\TestimonialController::class, 'reorder'])->name('testimonials.reorder');

        // Public prayer requests inbox
        Route::get('prayer-inbox', [\App\Http\Controllers\WebsiteBuilder\PublicPrayerController::class, 'index'])->name('prayer-inbox.index');
        Route::get('prayer-inbox/{prayerRequest}', [\App\Http\Controllers\WebsiteBuilder\PublicPrayerController::class, 'show'])->name('prayer-inbox.show');
        Route::put('prayer-inbox/{prayerRequest}/status', [\App\Http\Controllers\WebsiteBuilder\PublicPrayerController::class, 'updateStatus'])->name('prayer-inbox.status');
        Route::delete('prayer-inbox/{prayerRequest}', [\App\Http\Controllers\WebsiteBuilder\PublicPrayerController::class, 'destroy'])->name('prayer-inbox.destroy');
    });

    // My profile (for volunteers)
    Route::get('my-schedule', [EventController::class, 'mySchedule'])->name('my-schedule');
    Route::get('my-profile', [PersonController::class, 'myProfile'])->name('my-profile');
    Route::get('my-giving', [PersonController::class, 'myGiving'])->name('my-giving');
    Route::put('my-profile', [PersonController::class, 'updateMyProfile'])->name('my-profile.update');
    Route::post('my-profile/unavailable', [PersonController::class, 'addUnavailableDate'])->name('my-profile.unavailable.add');
    Route::delete('my-profile/unavailable/{unavailableDate}', [PersonController::class, 'removeUnavailableDate'])->name('my-profile.unavailable.remove');
    Route::post('my-profile/telegram/generate-code', [PersonController::class, 'generateTelegramCode'])->name('my-profile.telegram.generate');
    Route::delete('my-profile/telegram/unlink', [PersonController::class, 'unlinkTelegram'])->name('my-profile.telegram.unlink');
    Route::post('my-profile/theme', [PersonController::class, 'updateTheme'])->name('my-profile.theme');
    Route::post('my-profile/menu-position', [PersonController::class, 'updateMenuPosition'])->name('my-profile.menu-position');

    // Music Stand (for musicians)
    Route::prefix('music-stand')->name('music-stand.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MusicStandController::class, 'index'])->name('index');
        Route::get('event/{event}', [\App\Http\Controllers\MusicStandController::class, 'show'])->name('show');
        Route::get('event/{event}/song/{song}', [\App\Http\Controllers\MusicStandController::class, 'song'])->name('song');
        Route::get('song/{song}/data', [\App\Http\Controllers\MusicStandController::class, 'songData'])->name('song.data');
    });

    // Support
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('{ticket}', [SupportController::class, 'show'])->name('show');
        Route::post('{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
        Route::post('{ticket}/close', [SupportController::class, 'close'])->name('close');
    });

    // Blockout Dates (volunteer availability)
    Route::prefix('blockouts')->name('blockouts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BlockoutDateController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\BlockoutDateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\BlockoutDateController::class, 'store'])->name('store');
        Route::get('{blockout}/edit', [\App\Http\Controllers\BlockoutDateController::class, 'edit'])->name('edit');
        Route::put('{blockout}', [\App\Http\Controllers\BlockoutDateController::class, 'update'])->name('update');
        Route::delete('{blockout}', [\App\Http\Controllers\BlockoutDateController::class, 'destroy'])->name('destroy');
        Route::post('{blockout}/cancel', [\App\Http\Controllers\BlockoutDateController::class, 'cancel'])->name('cancel');
        Route::post('quick', [\App\Http\Controllers\BlockoutDateController::class, 'quickStore'])->name('quick');
        Route::get('calendar', [\App\Http\Controllers\BlockoutDateController::class, 'calendar'])->name('calendar');
    });

    // Scheduling Preferences — temporarily disabled
    // Route::prefix('scheduling-preferences')->name('scheduling-preferences.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\SchedulingPreferenceController::class, 'index'])->name('index');
    //     Route::put('/', [\App\Http\Controllers\SchedulingPreferenceController::class, 'update'])->name('update');
    //     Route::put('ministry/{ministry}', [\App\Http\Controllers\SchedulingPreferenceController::class, 'updateMinistry'])->name('ministry.update');
    //     Route::delete('ministry/{ministry}', [\App\Http\Controllers\SchedulingPreferenceController::class, 'deleteMinistry'])->name('ministry.delete');
    //     Route::put('position/{position}', [\App\Http\Controllers\SchedulingPreferenceController::class, 'updatePosition'])->name('position.update');
    //     Route::delete('position/{position}', [\App\Http\Controllers\SchedulingPreferenceController::class, 'deletePosition'])->name('position.delete');
    // });

    // Groups
    Route::resource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('groups/{group}/members/{person}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::put('groups/{group}/members/{person}/role', [GroupController::class, 'updateMemberRole'])->name('groups.members.role');

    // Group Attendance
    Route::prefix('groups/{group}/attendance')->name('groups.attendance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GroupAttendanceController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\GroupAttendanceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\GroupAttendanceController::class, 'store'])->name('store');
        Route::get('checkin', [\App\Http\Controllers\GroupAttendanceController::class, 'quickCheckin'])->name('checkin');
        Route::get('{attendance}', [\App\Http\Controllers\GroupAttendanceController::class, 'show'])->name('show');
        Route::get('{attendance}/edit', [\App\Http\Controllers\GroupAttendanceController::class, 'edit'])->name('edit');
        Route::put('{attendance}', [\App\Http\Controllers\GroupAttendanceController::class, 'update'])->name('update');
        Route::delete('{attendance}', [\App\Http\Controllers\GroupAttendanceController::class, 'destroy'])->name('destroy');
        Route::post('{attendance}/toggle', [\App\Http\Controllers\GroupAttendanceController::class, 'togglePresence'])->name('toggle');
    });

    // Global Search
    Route::get('search', [SearchController::class, 'search'])->name('search');
    Route::get('quick-actions', [SearchController::class, 'quickActions'])->name('quick-actions');

    // User Preferences
    Route::post('preferences/theme', [UserPreferencesController::class, 'updateTheme'])->name('preferences.theme');
    Route::post('preferences', [UserPreferencesController::class, 'updatePreferences'])->name('preferences.update');

    // Onboarding Wizard
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [OnboardingController::class, 'show'])->name('show');
        Route::get('step/{step}', [OnboardingController::class, 'step'])->name('step');
        Route::post('step/{step}', [OnboardingController::class, 'saveStep'])->name('save');
        Route::post('step/{step}/skip', [OnboardingController::class, 'skip'])->name('skip');
        Route::post('complete', [OnboardingController::class, 'complete'])->name('complete');
        Route::post('restart', [OnboardingController::class, 'restart'])->name('restart');
        Route::post('dismiss-hint', [OnboardingController::class, 'dismissHint'])->name('dismiss-hint');
    });

    // Messages (mass mailing)
    Route::middleware('permission:announcements,create')->prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('create', [MessageController::class, 'create'])->name('create');
        Route::post('preview', [MessageController::class, 'preview'])->name('preview');
        Route::post('send', [MessageController::class, 'send'])->name('send');
        Route::post('templates', [MessageController::class, 'storeTemplate'])->name('templates.store');
        Route::delete('templates/{template}', [MessageController::class, 'destroyTemplate'])->name('templates.destroy');
    });

    // Kanban Boards
    Route::prefix('boards')->name('boards.')->group(function () {
        Route::get('/', [BoardController::class, 'index'])->name('index');
        Route::get('create', [BoardController::class, 'create'])->name('create');
        Route::post('/', [BoardController::class, 'store'])->name('store');
        Route::get('archived', [BoardController::class, 'archived'])->name('archived');
        Route::post('create-from-entity', [BoardController::class, 'createFromEntity'])->name('create-from-entity');
        Route::get('linked-cards', [BoardController::class, 'getLinkedCards'])->name('linked-cards');
        Route::get('{board}', [BoardController::class, 'show'])->name('show');
        Route::get('{board}/edit', [BoardController::class, 'edit'])->name('edit');
        Route::put('{board}', [BoardController::class, 'update'])->name('update');
        Route::delete('{board}', [BoardController::class, 'destroy'])->name('destroy');
        Route::post('{board}/archive', [BoardController::class, 'archive'])->name('archive');
        Route::post('{board}/restore', [BoardController::class, 'restore'])->name('restore');

        // Columns
        Route::post('{board}/columns', [BoardController::class, 'storeColumn'])->name('columns.store');
        Route::post('{board}/columns/reorder', [BoardController::class, 'reorderColumns'])->name('columns.reorder');
        Route::put('columns/{column}', [BoardController::class, 'updateColumn'])->name('columns.update');
        Route::delete('columns/{column}', [BoardController::class, 'destroyColumn'])->name('columns.destroy');

        // Cards
        Route::post('columns/{column}/cards', [BoardController::class, 'storeCard'])->name('cards.store');
        Route::get('cards/{card}', [BoardController::class, 'showCard'])->name('cards.show');
        Route::put('cards/{card}', [BoardController::class, 'updateCard'])->name('cards.update');
        Route::delete('cards/{card}', [BoardController::class, 'destroyCard'])->name('cards.destroy');
        Route::post('cards/{card}/move', [BoardController::class, 'moveCard'])->name('cards.move');
        Route::post('cards/{card}/toggle', [BoardController::class, 'toggleCardComplete'])->name('cards.toggle');

        // Card Comments
        Route::post('cards/{card}/comments', [BoardController::class, 'storeComment'])->name('cards.comments.store');
        Route::put('comments/{comment}', [BoardController::class, 'updateComment'])->name('comments.update');
        Route::delete('comments/{comment}', [BoardController::class, 'destroyComment'])->name('comments.destroy');

        // Card Checklist
        Route::post('cards/{card}/checklist', [BoardController::class, 'storeChecklistItem'])->name('cards.checklist.store');
        Route::post('cards/checklist/{item}/toggle', [BoardController::class, 'toggleChecklistItem'])->name('cards.checklist.toggle');
        Route::delete('cards/checklist/{item}', [BoardController::class, 'destroyChecklistItem'])->name('cards.checklist.destroy');

        // Card Attachments
        Route::post('cards/{card}/attachments', [BoardController::class, 'storeAttachment'])->name('cards.attachments.store');
        Route::delete('attachments/{attachment}', [BoardController::class, 'destroyAttachment'])->name('attachments.destroy');

        // Related Cards
        Route::post('cards/{card}/related', [BoardController::class, 'addRelatedCard'])->name('cards.related.store');
        Route::delete('cards/{card}/related/{relatedCard}', [BoardController::class, 'removeRelatedCard'])->name('cards.related.destroy');

        // Duplicate Card
        Route::post('cards/{card}/duplicate', [BoardController::class, 'duplicateCard'])->name('cards.duplicate');

        // Epics
        Route::post('{board}/epics', [BoardController::class, 'storeEpic'])->name('epics.store');
        Route::put('epics/{epic}', [BoardController::class, 'updateEpic'])->name('epics.update');
        Route::delete('epics/{epic}', [BoardController::class, 'destroyEpic'])->name('epics.destroy');
    });

    // Private Messages
    Route::prefix('pm')->name('pm.')->group(function () {
        Route::get('/', [PrivateMessageController::class, 'index'])->name('index');
        Route::get('create', [PrivateMessageController::class, 'create'])->name('create');
        Route::post('/', [PrivateMessageController::class, 'store'])->name('store');
        Route::get('unread-count', [PrivateMessageController::class, 'unreadCount'])->name('unread-count');
        Route::get('{user}', [PrivateMessageController::class, 'show'])->name('show');
        Route::get('{user}/poll', [PrivateMessageController::class, 'poll'])->name('poll');
    });

    // Church Announcements
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('create', [AnnouncementController::class, 'create'])->name('create')->middleware('permission:announcements,create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store')->middleware('permission:announcements,create');
        Route::get('{announcement}', [AnnouncementController::class, 'show'])->name('show');
        Route::get('{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit')->middleware('permission:announcements,edit');
        Route::put('{announcement}', [AnnouncementController::class, 'update'])->name('update')->middleware('permission:announcements,edit');
        Route::delete('{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy')->middleware('permission:announcements,delete');
        Route::post('{announcement}/pin', [AnnouncementController::class, 'togglePin'])->name('pin')->middleware('permission:announcements,edit');
    });

    // Donations
    Route::middleware('permission:finances')->prefix('donations')->name('donations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DonationController::class, 'index'])->name('index');
        Route::get('qr', [\App\Http\Controllers\DonationController::class, 'qrCode'])->name('qr');
        Route::get('export', [\App\Http\Controllers\DonationController::class, 'export'])->name('export');
        Route::post('campaigns', [\App\Http\Controllers\DonationController::class, 'storeCampaign'])->name('campaigns.store');
        Route::post('campaigns/{campaign}/toggle', [\App\Http\Controllers\DonationController::class, 'toggleCampaign'])->name('campaigns.toggle');
        Route::delete('campaigns/{campaign}', [\App\Http\Controllers\DonationController::class, 'destroyCampaign'])->name('campaigns.destroy');
    });

    // Songs Library
    Route::middleware('permission:ministries')->prefix('songs')->name('songs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SongController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\SongController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SongController::class, 'store'])->name('store');
        Route::get('import', [\App\Http\Controllers\SongController::class, 'importPage'])->name('import.page');
        Route::post('import/preview', [\App\Http\Controllers\SongController::class, 'importPreview'])->name('import.preview');
        Route::post('import/process', [\App\Http\Controllers\SongController::class, 'importProcess'])->name('import.process');
        Route::get('template', [\App\Http\Controllers\SongController::class, 'downloadTemplate'])->name('template');
        Route::get('{song}', [\App\Http\Controllers\SongController::class, 'show'])->name('show');
        Route::get('{song}/edit', [\App\Http\Controllers\SongController::class, 'edit'])->name('edit');
        Route::put('{song}', [\App\Http\Controllers\SongController::class, 'update'])->name('update');
        Route::delete('{song}', [\App\Http\Controllers\SongController::class, 'destroy'])->name('destroy');
        Route::post('{song}/add-to-event', [\App\Http\Controllers\SongController::class, 'addToEvent'])->name('add-to-event');
    });

    // Resources (files & folders)
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ResourceController::class, 'index'])->name('index');
        Route::get('folder/{folder}', [\App\Http\Controllers\ResourceController::class, 'index'])->name('folder');
        Route::post('folder', [\App\Http\Controllers\ResourceController::class, 'createFolder'])->name('folder.create');
        Route::post('upload', [\App\Http\Controllers\ResourceController::class, 'upload'])->name('upload');
        Route::get('{resource}/download', [\App\Http\Controllers\ResourceController::class, 'download'])->name('download');
        Route::put('{resource}/rename', [\App\Http\Controllers\ResourceController::class, 'rename'])->name('rename');
        Route::put('{resource}/move', [\App\Http\Controllers\ResourceController::class, 'move'])->name('move');
        Route::delete('{resource}', [\App\Http\Controllers\ResourceController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::middleware('permission:reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
        Route::get('attendance', [\App\Http\Controllers\ReportsController::class, 'attendance'])->name('attendance');
        Route::get('finances', [\App\Http\Controllers\ReportsController::class, 'finances'])->name('finances');
        Route::get('volunteers', [\App\Http\Controllers\ReportsController::class, 'volunteers'])->name('volunteers');
        Route::get('export/finances', [\App\Http\Controllers\ReportsController::class, 'exportFinances'])->name('export-finances');
        Route::get('export/attendance', [\App\Http\Controllers\ReportsController::class, 'exportAttendance'])->name('export-attendance');
        Route::get('export/volunteers', [\App\Http\Controllers\ReportsController::class, 'exportVolunteers'])->name('export-volunteers');
    });

    // Prayer Requests - временно отключено
    // Route::prefix('prayer-requests')->name('prayer-requests.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\PrayerRequestController::class, 'index'])->name('index');
    //     Route::get('wall', [\App\Http\Controllers\PrayerRequestController::class, 'wall'])->name('wall');
    //     Route::get('create', [\App\Http\Controllers\PrayerRequestController::class, 'create'])->name('create');
    //     Route::post('/', [\App\Http\Controllers\PrayerRequestController::class, 'store'])->name('store');
    //     Route::get('{prayerRequest}', [\App\Http\Controllers\PrayerRequestController::class, 'show'])->name('show');
    //     Route::get('{prayerRequest}/edit', [\App\Http\Controllers\PrayerRequestController::class, 'edit'])->name('edit');
    //     Route::put('{prayerRequest}', [\App\Http\Controllers\PrayerRequestController::class, 'update'])->name('update');
    //     Route::delete('{prayerRequest}', [\App\Http\Controllers\PrayerRequestController::class, 'destroy'])->name('destroy');
    //     Route::post('{prayerRequest}/pray', [\App\Http\Controllers\PrayerRequestController::class, 'pray'])->name('pray');
    //     Route::post('{prayerRequest}/answered', [\App\Http\Controllers\PrayerRequestController::class, 'markAnswered'])->name('mark-answered');
    // });
});
