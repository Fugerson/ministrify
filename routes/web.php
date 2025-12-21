<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth', 'church'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // People
    Route::resource('people', PersonController::class);
    Route::post('people/{person}/restore', [PersonController::class, 'restore'])->name('people.restore');
    Route::get('people-export', [PersonController::class, 'export'])->name('people.export');
    Route::post('people-import', [PersonController::class, 'import'])->name('people.import');

    // Tags
    Route::resource('tags', TagController::class)->except(['show']);

    // Ministries
    Route::resource('ministries', MinistryController::class);
    Route::post('ministries/{ministry}/members', [MinistryController::class, 'addMember'])->name('ministries.members.add');
    Route::delete('ministries/{ministry}/members/{person}', [MinistryController::class, 'removeMember'])->name('ministries.members.remove');
    Route::put('ministries/{ministry}/members/{person}', [MinistryController::class, 'updateMemberPositions'])->name('ministries.members.update');

    // Positions
    Route::post('ministries/{ministry}/positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('positions/reorder', [PositionController::class, 'reorder'])->name('positions.reorder');

    // Schedule/Events
    Route::resource('events', EventController::class);
    Route::get('schedule', [EventController::class, 'schedule'])->name('schedule');
    Route::get('calendar', [EventController::class, 'calendar'])->name('calendar');

    // Assignments
    Route::post('events/{event}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::post('assignments/{assignment}/confirm', [AssignmentController::class, 'confirm'])->name('assignments.confirm');
    Route::post('assignments/{assignment}/decline', [AssignmentController::class, 'decline'])->name('assignments.decline');
    Route::post('events/{event}/notify-all', [AssignmentController::class, 'notifyAll'])->name('assignments.notify-all');

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

    // Expenses (admin and leaders)
    Route::middleware('role:admin,leader')->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::get('expenses-report', [ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('expenses-export', [ExpenseController::class, 'export'])->name('expenses.export');
    });

    // Attendance
    Route::resource('attendance', AttendanceController::class);
    Route::get('attendance-stats', [AttendanceController::class, 'stats'])->name('attendance.stats');

    // Settings (admin only)
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('church', [SettingsController::class, 'updateChurch'])->name('church');
        Route::put('telegram', [SettingsController::class, 'updateTelegram'])->name('telegram');
        Route::post('telegram/test', [SettingsController::class, 'testTelegram'])->name('telegram.test');
        Route::put('notifications', [SettingsController::class, 'updateNotifications'])->name('notifications');

        // Expense categories
        Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['show']);

        // Users management
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::post('users/{user}/invite', [\App\Http\Controllers\UserController::class, 'sendInvite'])->name('users.invite');
    });

    // My profile (for volunteers)
    Route::get('my-schedule', [EventController::class, 'mySchedule'])->name('my-schedule');
    Route::get('my-profile', [PersonController::class, 'myProfile'])->name('my-profile');
    Route::put('my-profile', [PersonController::class, 'updateMyProfile'])->name('my-profile.update');
    Route::post('my-profile/unavailable', [PersonController::class, 'addUnavailableDate'])->name('my-profile.unavailable.add');
    Route::delete('my-profile/unavailable/{unavailableDate}', [PersonController::class, 'removeUnavailableDate'])->name('my-profile.unavailable.remove');

    // Groups (Home Groups)
    Route::resource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('groups/{group}/members/{person}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::post('groups/{group}/attendance', [GroupController::class, 'attendance'])->name('groups.attendance');

    // Global Search
    Route::get('search', [SearchController::class, 'search'])->name('search');
    Route::get('quick-actions', [SearchController::class, 'quickActions'])->name('quick-actions');

    // User Preferences
    Route::post('preferences/theme', [UserPreferencesController::class, 'updateTheme'])->name('preferences.theme');
    Route::post('preferences', [UserPreferencesController::class, 'updatePreferences'])->name('preferences.update');
    Route::post('onboarding/complete', [UserPreferencesController::class, 'completeOnboarding'])->name('onboarding.complete');

    // Messages (admin and leaders)
    Route::middleware('role:admin,leader')->prefix('messages')->name('messages.')->group(function () {
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
        Route::delete('comments/{comment}', [BoardController::class, 'destroyComment'])->name('comments.destroy');

        // Card Checklist
        Route::post('cards/{card}/checklist', [BoardController::class, 'storeChecklistItem'])->name('cards.checklist.store');
        Route::post('checklist/{item}/toggle', [BoardController::class, 'toggleChecklistItem'])->name('cards.checklist.toggle');
        Route::delete('checklist/{item}', [BoardController::class, 'destroyChecklistItem'])->name('cards.checklist.destroy');
    });
});
