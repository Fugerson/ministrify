<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'church_id',
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'model_name',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the audited model
     */
    public function auditable()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Get action label in Ukrainian
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Створено',
            'updated' => 'Оновлено',
            'deleted' => 'Видалено',
            'restored' => 'Відновлено',
            'login' => 'Вхід',
            'logout' => 'Вихід',
            'exported' => 'Експортовано',
            'imported' => 'Імпортовано',
            'sent' => 'Надіслано',
            'impersonate' => 'Увійшов як',
            'stop_impersonate' => 'Вийшов з',
            'assigned' => 'Призначено',
            'unassigned' => 'Знято',
            'approved' => 'Підтверджено',
            'rejected' => 'Відхилено',
            'completed' => 'Завершено',
            // Custom actions
            'member_added' => 'Учасника додано',
            'member_removed' => 'Учасника видалено',
            'member_role_changed' => 'Роль учасника змінено',
            'bulk_action' => 'Масова дія',
            'bulk_deleted' => 'Масово видалено',
            'bulk_ministry_assigned' => 'Масово додано до служіння',
            'bulk_tag_assigned' => 'Масово додано тег',
            'bulk_message_sent' => 'Масово надіслано повідомлення',
            'bulk_access_granted' => 'Масово надано доступ',
            'password_reset' => 'Скидання пароля',
            'account_created' => 'Акаунт створено',
            'telegram_linked' => 'Telegram підключено',
            'telegram_unlinked' => 'Telegram відключено',
            'attendance_saved' => 'Відвідуваність збережено',
            'calendar_synced' => 'Календар синхронізовано',
            'settings_updated' => 'Налаштування оновлено',
            'notification_settings_updated' => 'Налаштування сповіщень оновлено',
            'payment_settings_updated' => 'Налаштування оплати оновлено',
            'finance_settings_updated' => 'Фінансові налаштування оновлено',
            'currency_settings_updated' => 'Налаштування валют оновлено',
            'theme_updated' => 'Тему оновлено',
            'public_site_updated' => 'Публічний сайт оновлено',
            'photo_uploaded' => 'Фото завантажено',
            'photo_deleted' => 'Фото видалено',
            'positions_updated' => 'Позиції оновлено',
            'quick_edit_saved' => 'Швидке редагування збережено',
            'visibility_updated' => 'Видимість оновлено',
            'privacy_toggled' => 'Приватність змінено',
            'checklist_created' => 'Чекліст створено',
            'checklist_item_toggled' => 'Пункт чеклісту змінено',
            'plan_item_added' => 'Елемент плану додано',
            'plan_item_reordered' => 'План переупорядковано',
            'notification_sent' => 'Сповіщення надіслано',
            'role_changed' => 'Роль змінено',
            'email_changed' => 'Email змінено',
            'shepherd_assigned' => 'Опікуна призначено',
            'google_calendar_connected' => 'Google Календар підключено',
            'google_calendar_disconnected' => 'Google Календар відключено',
            'monobank_synced' => 'Monobank синхронізовано',
            'privatbank_synced' => 'Приватбанк синхронізовано',
            'budget_updated' => 'Бюджет оновлено',
            'receipt_uploaded' => 'Чек завантажено',
            'receipt_deleted' => 'Чек видалено',
            default => $this->action,
        };
    }

    /**
     * Get human-readable description
     */
    public function getDescriptionAttribute(): string
    {
        $modelLabel = $this->model_label;
        $modelName = $this->model_name;
        $actionLabel = $this->action_label;

        // Special cases
        if ($this->action === 'impersonate') {
            return "Увійшов як користувач: {$modelName}";
        }
        if ($this->action === 'stop_impersonate') {
            return "Вийшов з режиму імперсонації: {$modelName}";
        }
        if ($this->action === 'login') {
            return 'Вхід в систему';
        }
        if ($this->action === 'logout') {
            return 'Вихід з системи';
        }

        // Default: "Action ModelType: ModelName"
        if ($modelName) {
            return "{$actionLabel} {$modelLabel}: {$modelName}";
        }

        return "{$actionLabel} {$modelLabel}";
    }

    /**
     * Get model type label
     */
    public function getModelLabelAttribute(): string
    {
        return match($this->model_type) {
            'App\\Models\\Person' => 'Член церкви',
            'App\\Models\\User' => 'Користувач',
            'App\\Models\\Event' => 'Подія',
            'App\\Models\\Ministry' => 'Служіння',
            'App\\Models\\Group' => 'Група',
            'App\\Models\\Expense' => 'Витрата',
            'App\\Models\\Income' => 'Дохід',
            'App\\Models\\Transaction' => 'Транзакція',
            'App\\Models\\TransactionCategory' => 'Категорія фінансів',
            'App\\Models\\Budget' => 'Бюджет',
            'App\\Models\\BudgetItem' => 'Стаття бюджету',
            'App\\Models\\Donation' => 'Пожертва',
            'App\\Models\\Board' => 'Дошка',
            'App\\Models\\BoardCard' => 'Картка',
            'App\\Models\\BoardColumn' => 'Колонка дошки',
            'App\\Models\\Assignment' => 'Призначення',
            'App\\Models\\Attendance' => 'Відвідуваність',
            'App\\Models\\Church' => 'Церква',
            'App\\Models\\Tag' => 'Тег',
            'App\\Models\\ExpenseCategory' => 'Категорія витрат',
            'App\\Models\\ChurchRole' => 'Церковна роль',
            'App\\Models\\Position' => 'Позиція',
            'App\\Models\\BlockoutDate' => 'Блокування дат',
            'App\\Models\\EventRegistration' => 'Реєстрація на подію',
            'App\\Models\\PrayerRequest' => 'Молитовна потреба',
            'App\\Models\\Announcement' => 'Оголошення',
            'App\\Models\\GroupAttendance' => 'Відвідуваність групи',
            'App\\Models\\MinistryTask' => 'Завдання служіння',
            'App\\Models\\MinistryGoal' => 'Ціль служіння',
            'App\\Models\\MinistryMeeting' => 'Зустріч служіння',
            'App\\Models\\ChurchRolePermission' => 'Дозвіл ролі',
            'App\\Models\\BlogPost' => 'Блог-пост',
            'App\\Models\\BlogCategory' => 'Категорія блогу',
            'App\\Models\\DonationCampaign' => 'Кампанія пожертв',
            'App\\Models\\OnlineDonation' => 'Онлайн-пожертва',
            'App\\Models\\Gallery' => 'Галерея',
            'App\\Models\\GalleryPhoto' => 'Фото галереї',
            'App\\Models\\Sermon' => 'Проповідь',
            'App\\Models\\SermonSeries' => 'Серія проповідей',
            'App\\Models\\Song' => 'Пісня',
            'App\\Models\\Testimonial' => 'Свідчення',
            'App\\Models\\Faq' => 'FAQ',
            'App\\Models\\StaffMember' => 'Співробітник',
            'App\\Models\\SupportTicket' => 'Тікет підтримки',
            'App\\Models\\BoardEpic' => 'Епік дошки',
            'App\\Models\\ChecklistTemplate' => 'Шаблон чеклісту',
            'App\\Models\\EventTaskTemplate' => 'Шаблон завдань події',
            'App\\Models\\IncomeCategory' => 'Категорія доходів',
            'App\\Models\\MessageTemplate' => 'Шаблон повідомлення',
            'App\\Models\\MinistryType' => 'Тип служіння',
            'App\\Models\\ServicePlanTemplate' => 'Шаблон плану служби',
            'App\\Models\\Resource' => 'Ресурс',
            'App\\Models\\FamilyRelationship' => 'Сімейний звʼязок',
            'App\\Models\\MinistryBudget' => 'Бюджет служіння',
            default => class_basename($this->model_type ?? ''),
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'M12 4v16m8-8H4',
            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'deleted' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'restored' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
            'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created', 'account_created', 'member_added', 'telegram_linked' => 'green',
            'updated', 'settings_updated', 'positions_updated', 'visibility_updated', 'quick_edit_saved' => 'blue',
            'deleted', 'member_removed', 'telegram_unlinked', 'bulk_deleted', 'photo_deleted', 'receipt_deleted' => 'red',
            'restored' => 'purple',
            'login', 'logout' => 'gray',
            'exported', 'imported' => 'indigo',
            'sent', 'notification_sent', 'bulk_message_sent' => 'cyan',
            'assigned', 'bulk_ministry_assigned', 'bulk_tag_assigned', 'shepherd_assigned' => 'teal',
            'password_reset', 'email_changed', 'role_changed', 'member_role_changed' => 'amber',
            'attendance_saved', 'calendar_synced', 'monobank_synced', 'privatbank_synced' => 'sky',
            'bulk_access_granted' => 'emerald',
            'theme_updated', 'public_site_updated' => 'violet',
            'photo_uploaded', 'receipt_uploaded' => 'lime',
            default => 'gray',
        };
    }

    /**
     * Human-readable field names
     */
    protected static array $fieldLabels = [
        // Common
        'name' => 'Назва',
        'title' => 'Заголовок',
        'description' => 'Опис',
        'email' => 'Email',
        'phone' => 'Телефон',
        'address' => 'Адреса',
        'status' => 'Статус',
        'notes' => 'Примітки',
        'color' => 'Колір',
        'is_active' => 'Активний',
        'type' => 'Тип',
        'currency' => 'Валюта',

        // Person
        'first_name' => 'Ім\'я',
        'last_name' => 'Прізвище',
        'middle_name' => 'По батькові',
        'birth_date' => 'Дата народження',
        'gender' => 'Стать',
        'marital_status' => 'Сімейний стан',
        'membership_status' => 'Членство',
        'membership_date' => 'Дата членства',
        'baptism_date' => 'Дата хрещення',
        'city' => 'Місто',
        'occupation' => 'Професія',
        'photo' => 'Фото',
        'telegram_id' => 'Telegram',
        'telegram_username' => 'Telegram нік',

        // Event
        'start_date' => 'Початок',
        'end_date' => 'Кінець',
        'start_time' => 'Час початку',
        'end_time' => 'Час завершення',
        'location' => 'Місце',
        'is_recurring' => 'Повторювана',
        'recurrence_pattern' => 'Шаблон повторення',
        'all_day' => 'Весь день',
        'ministry_id' => 'Служіння',
        'event_type' => 'Тип події',

        // Ministry
        'leader_id' => 'Лідер',
        'meeting_day' => 'День зустрічі',
        'meeting_time' => 'Час зустрічі',

        // Group
        'max_members' => 'Макс. учасників',
        'meeting_frequency' => 'Частота зустрічей',

        // User
        'role' => 'Роль',
        'theme' => 'Тема',
        'church_role_id' => 'Церковна роль',

        // Finance / Transaction
        'amount' => 'Сума',
        'category' => 'Категорія',
        'category_id' => 'Категорія',
        'transaction_category_id' => 'Категорія',
        'date' => 'Дата',
        'payment_method' => 'Спосіб оплати',
        'person_id' => 'Особа',
        'transaction_date' => 'Дата операції',
        'source' => 'Джерело',
        'purpose' => 'Призначення',
        'is_tithe' => 'Десятина',
        'is_recurring' => 'Регулярний',
        'recurring_day' => 'День повторення',
        'reference_number' => 'Номер',

        // Budget
        'budget_id' => 'Бюджет',
        'planned_amount' => 'Планова сума',
        'actual_amount' => 'Фактична сума',
        'period_start' => 'Початок періоду',
        'period_end' => 'Кінець періоду',
        'year' => 'Рік',
        'month' => 'Місяць',

        // Board/Cards
        'position' => 'Позиція',
        'due_date' => 'Термін',
        'priority' => 'Пріоритет',
        'board_id' => 'Дошка',
        'column_id' => 'Колонка',
        'assigned_to' => 'Призначено',

        // Donation
        'donor_name' => 'Жертводавець',
        'donor_email' => 'Email жертводавця',
        'donation_type' => 'Тип пожертви',
        'anonymous' => 'Анонімно',

        // Settings
        'sort_order' => 'Порядок',
        'icon' => 'Іконка',

        // Event — додаткові поля
        'track_attendance' => 'Облік відвідуваності',
        'is_service' => 'Богослужіння',
        'service_type' => 'Тип богослужіння',
        'allow_registration' => 'Дозволити реєстрацію',
        'registration_limit' => 'Ліміт реєстрацій',
        'registration_deadline' => 'Дедлайн реєстрації',
        'public_description' => 'Публічний опис',
        'cover_image' => 'Обкладинка',
        'checkin_token' => 'Токен чекіну',
        'qr_checkin_enabled' => 'QR чекін',
        'recurrence_rule' => 'Правило повторення',
        'parent_event_id' => 'Батьківська подія',
        'is_public' => 'Публічний',
        'reminder_settings' => 'Нагадування',
        'google_event_id' => 'Google подія',
        'google_calendar_id' => 'Google календар',
        'google_synced_at' => 'Синхронізовано з Google',
        'google_sync_status' => 'Статус синхронізації',
        'time' => 'Час',

        // Person — додаткові поля
        'iban' => 'IBAN',
        'telegram_chat_id' => 'Telegram чат ID',
        'anniversary' => 'Річниця',
        'first_visit_date' => 'Перший візит',
        'joined_date' => 'Дата приєднання',
        'last_scheduled_at' => 'Останнє призначення',
        'times_scheduled_this_month' => 'Призначень цього місяця',
        'times_scheduled_this_year' => 'Призначень цього року',
        'is_shepherd' => 'Опікун',
        'shepherd_id' => 'Опікун',
        'user_id' => 'Користувач',

        // Ministry — додаткові поля
        'vision' => 'Бачення',
        'monthly_budget' => 'Місячний бюджет',
        'is_worship_ministry' => 'Музичне служіння',
        'slug' => 'Slug',
        'allow_registrations' => 'Дозволити реєстрацію',
        'is_private' => 'Приватний',
        'visibility' => 'Видимість',
        'allowed_person_ids' => 'Дозволені особи',
        'ministry_label' => 'Назва служіння',

        // Group — додаткові поля
        'meeting_location' => 'Місце зустрічі',
        'meeting_schedule' => 'Розклад зустрічей',

        // Transaction — додаткові поля
        'direction' => 'Напрямок',
        'source_type' => 'Тип джерела',
        'expense_type' => 'Тип витрати',
        'amount_uah' => 'Сума в грн',
        'campaign_id' => 'Кампанія',
        'donor_phone' => 'Телефон жертводавця',
        'is_anonymous' => 'Анонімний',
        'transaction_id' => 'ID транзакції',
        'order_id' => 'Номер замовлення',
        'payment_data' => 'Дані оплати',
        'recorded_by' => 'Записав',
        'paid_at' => 'Дата оплати',
        'related_transaction_id' => 'Повʼязана транзакція',

        // Assignment — додаткові поля
        'event_id' => 'Подія',
        'position_id' => 'Позиція',
        'notified_at' => 'Сповіщено',
        'responded_at' => 'Відповідь',
        'email_sent_at' => 'Лист надіслано',
        'email_opened_at' => 'Лист прочитано',
        'blockout_override' => 'Ігнорувати блокування',
        'preference_override' => 'Ігнорувати вподобання',
        'conflict_override' => 'Ігнорувати конфлікт',
        'decline_reason' => 'Причина відмови',
        'assignment_notes' => 'Примітки призначення',

        // Board — додаткові поля
        'is_archived' => 'Архівований',

        // User — додаткові поля
        'google_id' => 'Google ID',
        'is_super_admin' => 'Суперадмін',
        'preferences' => 'Налаштування',
        'onboarding_completed' => 'Онбординг завершено',
        'onboarding_state' => 'Стан онбордингу',
        'onboarding_started_at' => 'Онбординг розпочато',
        'onboarding_completed_at' => 'Онбординг завершено',

        // ChurchRole — додаткові поля
        'is_default' => 'За замовчуванням',
        'is_admin_role' => 'Адмін роль',

        // PrayerRequest — додаткові поля
        'submitter_name' => 'Імʼя подавача',
        'submitter_email' => 'Email подавача',
        'is_from_public' => 'З публічної форми',
        'notify_on_prayer' => 'Сповіщати про молитви',
        'answer_testimony' => 'Свідчення відповіді',
        'answered_at' => 'Дата відповіді',
        'prayer_count' => 'Кількість молитов',
        'is_urgent' => 'Терміновий',
        'content' => 'Зміст',

        // Attendance — додаткові поля
        'attendable_type' => 'Тип обʼєкта',
        'attendable_id' => 'ID обʼєкта',
        'total_count' => 'Загальна кількість',
        'members_present' => 'Присутніх членів',
        'guests_count' => 'Гостей',

        // Announcement — додаткові поля
        'author_id' => 'Автор',
        'is_pinned' => 'Закріплений',

        // MinistryGoal/Task/Meeting — додаткові поля
        'goal_id' => 'Ціль',
        'created_by' => 'Створив',
        'completed_by' => 'Завершив',
        'period' => 'Період',
        'summary' => 'Підсумок',
        'copied_from_id' => 'Скопійовано з',

        // Church settings — додаткові поля
        'shepherds_enabled' => 'Опікуни увімкнені',
        'attendance_enabled' => 'Облік відвідуваності',
    ];

    /**
     * Get human-readable field label
     */
    public static function getFieldLabel(string $field): string
    {
        return self::$fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display
     */
    protected function formatValue($value, string $field): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Boolean fields
        $booleanFields = [
            'is_active', 'is_recurring', 'all_day', 'is_service',
            'track_attendance', 'is_public', 'is_private', 'is_shepherd',
            'is_worship_ministry', 'allow_registration', 'allow_registrations',
            'qr_checkin_enabled', 'is_tithe', 'anonymous', 'is_anonymous',
            'is_archived', 'is_pinned', 'is_urgent', 'is_from_public',
            'notify_on_prayer', 'is_super_admin', 'is_default', 'is_admin_role',
            'onboarding_completed', 'blockout_override', 'preference_override',
            'conflict_override', 'shepherds_enabled', 'attendance_enabled',
        ];
        if (is_bool($value) || in_array($field, $booleanFields)) {
            return $value ? 'Так' : 'Ні';
        }

        // Date fields
        if (str_contains($field, '_date') || str_contains($field, '_at') || $field === 'date') {
            try {
                return \Carbon\Carbon::parse($value)->format('d.m.Y');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Time fields
        if (str_contains($field, '_time') || $field === 'time') {
            try {
                return \Carbon\Carbon::parse($value)->format('H:i');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Position/Order fields - show as ordinal number
        if (in_array($field, ['position', 'order', 'sort_order'])) {
            $pos = (int) $value + 1; // Convert 0-indexed to 1-indexed for display
            return "№{$pos}";
        }

        // Gender
        if ($field === 'gender') {
            return match($value) {
                'male' => 'Чоловік',
                'female' => 'Жінка',
                default => $value,
            };
        }

        // Status fields
        if ($field === 'status' || $field === 'membership_status') {
            return match($value) {
                'active' => 'Активний',
                'inactive' => 'Неактивний',
                'member' => 'Член',
                'regular' => 'Постійний відвідувач',
                'visitor' => 'Гість',
                'new' => 'Новий',
                'pending' => 'Очікує',
                'completed' => 'Завершено',
                'cancelled' => 'Скасовано',
                'archived' => 'Архівований',
                'draft' => 'Чернетка',
                'published' => 'Опубліковано',
                'confirmed' => 'Підтверджено',
                'declined' => 'Відхилено',
                default => $value,
            };
        }

        // Marital status
        if ($field === 'marital_status') {
            return match($value) {
                'single' => 'Неодружений/а',
                'married' => 'Одружений/а',
                'divorced' => 'Розлучений/а',
                'widowed' => 'Вдівець/вдова',
                default => $value,
            };
        }

        // Role
        if ($field === 'role') {
            return match($value) {
                'admin' => 'Адміністратор',
                'leader' => 'Лідер',
                'volunteer' => 'Волонтер',
                'member' => 'Учасник',
                default => $value,
            };
        }

        // Church role ID - look up role name
        if ($field === 'church_role_id') {
            $role = ChurchRole::find($value);
            return $role ? $role->name : $value;
        }

        // Priority
        if ($field === 'priority') {
            return match($value) {
                'low' => 'Низький',
                'medium' => 'Середній',
                'high' => 'Високий',
                'urgent' => 'Терміновий',
                default => $value,
            };
        }

        // Payment method
        if ($field === 'payment_method') {
            return match($value) {
                'cash' => 'Готівка',
                'card' => 'Картка',
                'bank_transfer' => 'Переказ',
                'online' => 'Онлайн',
                default => $value,
            };
        }

        // Service type
        if ($field === 'service_type') {
            return match($value) {
                'sunday' => 'Недільне',
                'midweek' => 'Середа',
                'prayer' => 'Молитовне',
                'youth' => 'Молодіжне',
                'special' => 'Особливе',
                default => $value,
            };
        }

        // Visibility
        if ($field === 'visibility') {
            return match($value) {
                'public' => 'Публічний',
                'members' => 'Тільки учасники',
                'leaders' => 'Тільки лідери',
                'specific' => 'Обрані особи',
                'private' => 'Приватний',
                default => $value,
            };
        }

        // Direction (transaction)
        if ($field === 'direction') {
            return match($value) {
                'income' => 'Надходження',
                'expense' => 'Витрата',
                default => $value,
            };
        }

        // Event type
        if ($field === 'event_type') {
            return match($value) {
                'service' => 'Богослужіння',
                'meeting' => 'Зустріч',
                'rehearsal' => 'Репетиція',
                'prayer' => 'Молитва',
                'other' => 'Інше',
                default => $value,
            };
        }

        // Google sync status
        if ($field === 'google_sync_status') {
            return match($value) {
                'synced' => 'Синхронізовано',
                'pending' => 'Очікує',
                'failed' => 'Помилка',
                default => $value,
            };
        }

        // Transaction type
        if ($field === 'type') {
            return match($value) {
                'income' => 'Надходження',
                'expense' => 'Витрата',
                default => $value,
            };
        }

        // Amount (money)
        if (in_array($field, ['amount', 'planned_amount', 'actual_amount', 'initial_balance', 'amount_uah', 'monthly_budget'])) {
            return number_format((float)$value, 2, '.', ' ') . ' ₴';
        }

        // Arrays/JSON
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): array
    {
        if (!$this->old_values && !$this->new_values) {
            return [];
        }

        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        // Skip technical fields
        $skip = [
            'id', 'church_id', 'created_at', 'updated_at', 'deleted_at',
            'password', 'remember_token', 'email_verified_at',
            // Технічні поля
            'checkin_token', 'google_event_id', 'google_calendar_id',
            'google_id', 'calendar_token', 'telegram_bot_token',
        ];

        // Collect raw changes first
        $rawChanges = [];
        foreach ($new as $key => $value) {
            if (in_array($key, $skip)) continue;

            $oldValue = $old[$key] ?? null;
            if ($oldValue !== $value) {
                $rawChanges[$key] = ['old' => $oldValue, 'new' => $value];
            }
        }

        // Smart descriptions for common patterns
        $modelClass = class_basename($this->auditable_type ?? '');

        // BoardCard: only position changed = card was reordered
        if ($modelClass === 'BoardCard' && count($rawChanges) === 1 && isset($rawChanges['position'])) {
            $oldPos = (int)$rawChanges['position']['old'];
            $newPos = (int)$rawChanges['position']['new'];
            $direction = $newPos < $oldPos ? 'вгору' : 'вниз';
            return [[
                'field' => 'Дія',
                'old' => null,
                'new' => "Картку переміщено {$direction} в списку",
            ]];
        }

        // BoardCard: column_id changed = card moved to another column
        if ($modelClass === 'BoardCard' && isset($rawChanges['column_id'])) {
            $changes[] = [
                'field' => 'Дія',
                'old' => null,
                'new' => 'Картку переміщено в іншу колонку',
            ];
            unset($rawChanges['column_id']);
            unset($rawChanges['position']); // position change is implied
        }

        // Convert remaining raw changes to formatted changes
        foreach ($rawChanges as $key => $vals) {
            $changes[] = [
                'field' => self::getFieldLabel($key),
                'old' => $this->formatValue($vals['old'], $key),
                'new' => $this->formatValue($vals['new'], $key),
            ];
        }

        return $changes;
    }

    /**
     * Get compact text summary of changes for display in table
     */
    public function getChangesSummaryTextAttribute(): ?string
    {
        $changes = $this->changes_summary;

        if (empty($changes)) {
            return null;
        }

        $parts = [];
        $maxChanges = 3; // Show max 3 changes

        foreach (array_slice($changes, 0, $maxChanges) as $change) {
            $old = $change['old'] ?? '—';
            $new = $change['new'] ?? '—';

            // Truncate long values
            if (mb_strlen($old) > 20) $old = mb_substr($old, 0, 17) . '...';
            if (mb_strlen($new) > 20) $new = mb_substr($new, 0, 17) . '...';

            $parts[] = "{$change['field']}: {$old} → {$new}";
        }

        $result = implode('; ', $parts);

        if (count($changes) > $maxChanges) {
            $result .= ' (+' . (count($changes) - $maxChanges) . ')';
        }

        return $result;
    }

    /**
     * Scope for church
     */
    public function scopeForChurch($query, int $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    /**
     * Scope for model
     */
    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
