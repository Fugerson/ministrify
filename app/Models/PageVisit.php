<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'church_id',
        'url',
        'route_name',
        'method',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function routeLabel(): string
    {
        $labels = [
            'dashboard' => __('app.route_dashboard'),
            'people.index' => __('app.route_people_index'),
            'people.show' => __('app.route_people_show'),
            'people.create' => __('app.route_people_create'),
            'people.edit' => __('app.route_people_edit'),
            'people.quick-edit' => __('app.route_people_quick_edit'),
            'events.index' => __('app.route_events_index'),
            'events.show' => __('app.route_events_show'),
            'events.create' => __('app.route_events_create'),
            'events.edit' => __('app.route_events_edit'),
            'ministries.index' => __('app.route_ministries_index'),
            'ministries.show' => __('app.route_ministries_show'),
            'ministries.create' => __('app.route_ministries_create'),
            'ministries.edit' => __('app.route_ministries_edit'),
            'groups.index' => __('app.route_groups_index'),
            'groups.show' => __('app.route_groups_show'),
            'groups.create' => __('app.route_groups_create'),
            'groups.edit' => __('app.route_groups_edit'),
            'finance.index' => __('app.route_finance_index'),
            'finance.transactions' => __('app.route_finance_transactions'),
            'finance.budgets' => __('app.route_finance_budgets'),
            'finance.reports' => __('app.route_finance_reports'),
            'boards.index' => __('app.route_boards_index'),
            'boards.show' => __('app.route_boards_show'),
            'settings.index' => __('app.route_settings_index'),
            'settings.church' => __('app.route_settings_church'),
            'settings.roles' => __('app.route_settings_roles'),
            'schedule.index' => __('app.route_schedule_index'),
            'attendance.index' => __('app.route_attendance_index'),
            'songs.index' => __('app.route_songs_index'),
            'announcements.index' => __('app.route_announcements_index'),
            'prayer-requests.index' => __('app.route_prayer_requests_index'),
            'my-profile' => __('app.route_my_profile'),
            'support.index' => __('app.route_support_index'),
            'support.create' => __('app.route_support_create'),
            'gallery.index' => __('app.route_gallery_index'),
            'sermons.index' => __('app.route_sermons_index'),
        ];

        return $labels[$this->route_name] ?? $this->route_name ?? $this->url;
    }
}
