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
            'dashboard' => 'Дашборд',
            'people.index' => 'Список людей',
            'people.show' => 'Профіль людини',
            'people.create' => 'Додавання людини',
            'people.edit' => 'Редагування людини',
            'people.quick-edit' => 'Швидке редагування',
            'events.index' => 'Список подій',
            'events.show' => 'Деталі події',
            'events.create' => 'Створення події',
            'events.edit' => 'Редагування події',
            'ministries.index' => 'Список служінь',
            'ministries.show' => 'Деталі служіння',
            'ministries.create' => 'Створення служіння',
            'ministries.edit' => 'Редагування служіння',
            'groups.index' => 'Список груп',
            'groups.show' => 'Деталі групи',
            'groups.create' => 'Створення групи',
            'groups.edit' => 'Редагування групи',
            'finance.index' => 'Фінанси',
            'finance.transactions' => 'Транзакції',
            'finance.budgets' => 'Бюджети',
            'finance.reports' => 'Фінансові звіти',
            'boards.index' => 'Дошки',
            'boards.show' => 'Дошка',
            'settings.index' => 'Налаштування',
            'settings.church' => 'Налаштування церкви',
            'settings.roles' => 'Ролі',
            'schedule.index' => 'Розклад',
            'attendance.index' => 'Відвідуваність',
            'songs.index' => 'Пісні',
            'announcements.index' => 'Оголошення',
            'prayer-requests.index' => 'Молитовні потреби',
            'my-profile' => 'Мій профіль',
            'support.index' => 'Підтримка',
            'support.create' => 'Створення тікету',
            'gallery.index' => 'Галерея',
            'sermons.index' => 'Проповіді',
        ];

        return $labels[$this->route_name] ?? $this->route_name ?? $this->url;
    }
}
