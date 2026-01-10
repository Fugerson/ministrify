<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchRolePermission extends Model
{
    protected $fillable = [
        'church_role_id',
        'module',
        'actions',
    ];

    protected $casts = [
        'actions' => 'array',
    ];

    // Available modules (same as RolePermission)
    public const MODULES = [
        'dashboard' => ['label' => 'Головна', 'icon' => 'home'],
        'people' => ['label' => 'Люди', 'icon' => 'users'],
        'groups' => ['label' => 'Домашні групи', 'icon' => 'user-group'],
        'ministries' => ['label' => 'Служіння', 'icon' => 'heart'],
        'events' => ['label' => 'Розклад', 'icon' => 'calendar'],
        'finances' => ['label' => 'Фінанси', 'icon' => 'currency-dollar'],
        'reports' => ['label' => 'Звіти', 'icon' => 'chart-bar'],
        'resources' => ['label' => 'Ресурси', 'icon' => 'folder'],
        'boards' => ['label' => 'Дошки завдань', 'icon' => 'view-boards'],
        'announcements' => ['label' => 'Комунікації', 'icon' => 'speakerphone'],
        'website' => ['label' => 'Веб-сайт', 'icon' => 'globe'],
        'settings' => ['label' => 'Налаштування', 'icon' => 'cog'],
    ];

    // Available actions
    public const ACTIONS = [
        'view' => 'Переглядати',
        'create' => 'Створювати',
        'edit' => 'Редагувати',
        'delete' => 'Видаляти',
    ];

    public function churchRole(): BelongsTo
    {
        return $this->belongsTo(ChurchRole::class);
    }
}
