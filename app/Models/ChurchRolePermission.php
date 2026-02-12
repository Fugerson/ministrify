<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class ChurchRolePermission extends Model
{
    use Auditable;

    protected $fillable = [
        'church_role_id',
        'module',
        'actions',
    ];

    protected $casts = [
        'actions' => 'array',
    ];

    // Available modules with their allowed actions
    public const MODULES = [
        'dashboard' => [
            'label' => 'Головна',
            'icon' => 'home',
            'actions' => ['view'],
            'description' => 'Статистика та огляд',
        ],
        'people' => [
            'label' => 'Люди',
            'icon' => 'users',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Члени церкви, гості, контакти',
        ],
        'groups' => [
            'label' => 'Домашні групи',
            'icon' => 'user-group',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Малі групи та їх учасники',
        ],
        'ministries' => [
            'label' => 'Команди',
            'icon' => 'heart',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Команди служіння',
        ],
        'events' => [
            'label' => 'Розклад',
            'icon' => 'calendar',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Події, богослужіння, зустрічі',
        ],
        'finances' => [
            'label' => 'Фінанси',
            'icon' => 'currency-dollar',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Доходи, витрати, пожертви',
        ],
        'reports' => [
            'label' => 'Звіти',
            'icon' => 'chart-bar',
            'actions' => ['view'],
            'description' => 'Аналітика та статистика',
        ],
        'resources' => [
            'label' => 'Ресурси',
            'icon' => 'folder',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Файли та документи',
        ],
        'boards' => [
            'label' => 'Дошки завдань',
            'icon' => 'view-boards',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Kanban дошки та завдання',
        ],
        'announcements' => [
            'label' => 'Комунікації',
            'icon' => 'speakerphone',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Оголошення та повідомлення',
        ],
        'website' => [
            'label' => 'Веб-сайт',
            'icon' => 'globe',
            'actions' => ['view', 'edit'],
            'description' => 'Публічний сайт церкви',
        ],
        'attendance' => [
            'label' => 'Відвідуваність',
            'icon' => 'clipboard-check',
            'actions' => ['view', 'create', 'edit', 'delete'],
            'description' => 'Облік відвідуваності',
        ],
        'settings' => [
            'label' => 'Налаштування',
            'icon' => 'cog',
            'actions' => ['view', 'edit'],
            'description' => 'Налаштування церкви',
        ],
    ];

    // All possible actions (for reference)
    public const ACTIONS = [
        'view' => 'Переглядати',
        'create' => 'Створювати',
        'edit' => 'Редагувати',
        'delete' => 'Видаляти',
    ];

    /**
     * Get allowed actions for a specific module
     */
    public static function getAllowedActions(string $module): array
    {
        return self::MODULES[$module]['actions'] ?? [];
    }

    public function churchRole(): BelongsTo
    {
        return $this->belongsTo(ChurchRole::class);
    }
}
