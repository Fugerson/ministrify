# Ministrify (ChurchHub) - Повна документація

> Комплексна платформа для управління церквою

**Версія:** 1.0
**Мова інтерфейсу:** Українська
**Технології:** Laravel 10+, PHP 8.2, MySQL 8, Redis, Tailwind CSS, Alpine.js
**Розгортання:** Docker

---

## Зміст

1. [Огляд системи](#1-огляд-системи)
2. [Архітектура](#2-архітектура)
3. [Встановлення та розгортання](#3-встановлення-та-розгортання)
4. [Моделі даних](#4-моделі-даних)
5. [Модулі та функціонал](#5-модулі-та-функціонал)
6. [API](#6-api)
7. [Маршрути (Routes)](#7-маршрути-routes)
8. [Сервіси](#8-сервіси)
9. [Конфігурація](#9-конфігурація)
10. [Ролі та права доступу](#10-ролі-та-права-доступу)
11. [Інтеграції](#11-інтеграції)
12. [Структура файлів](#12-структура-файлів)

---

## 1. Огляд системи

### Що таке Ministrify?

**Ministrify** — це багатофункціональна SaaS-платформа для управління церквою, яка охоплює:

- **Управління людьми** — члени церкви, сім'ї, ролі, теги
- **Координація служителів** — розклад, ротація, конфлікти
- **Управління подіями** — богослужіння, зустрічі, реєстрація
- **Фінанси** — десятини, пожертви, витрати, бюджети
- **Комунікація** — повідомлення, оголошення, Telegram-бот
- **Публічний сайт** — конструктор сайту, події, пожертви онлайн
- **Проєктний менеджмент** — Kanban-дошки, завдання

### Ключові можливості

| Модуль | Опис |
|--------|------|
| Люди | CRM для членів церкви з сімейними зв'язками |
| Служіння | Управління відділами та позиціями |
| Події | Календар, планування богослужінь, QR check-in |
| Групи | Домашні групи, відвідуваність |
| Фінанси | Доходи, витрати, категорії, звіти |
| Ротація | Автоматичний розподіл служителів |
| Повідомлення | Внутрішні листи, Telegram-розсилка |
| Дошки | Kanban для проєктів |
| Конструктор сайту | Публічний сайт церкви |
| Білінг | Підписки, оплата LiqPay/Monobank |

---

## 2. Архітектура

### Технологічний стек

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                              │
│  Alpine.js │ Tailwind CSS │ Blade Templates │ Livewire      │
├─────────────────────────────────────────────────────────────┤
│                        Backend                               │
│  Laravel 10+ │ PHP 8.2 │ Eloquent ORM │ Sanctum Auth        │
├─────────────────────────────────────────────────────────────┤
│                       Database                               │
│  MySQL 8 │ Redis (Cache/Sessions)                           │
├─────────────────────────────────────────────────────────────┤
│                      Infrastructure                          │
│  Docker │ Nginx │ Docker Compose                            │
└─────────────────────────────────────────────────────────────┘
```

### Структура директорій

```
churchhub/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # 69 контролерів
│   │   ├── Middleware/      # Аутентифікація, ролі, церква
│   │   └── Requests/        # Валідація запитів
│   ├── Models/              # 85+ Eloquent моделей
│   ├── Services/            # 11 сервісів бізнес-логіки
│   ├── Notifications/       # Email/Push сповіщення
│   ├── Exports/             # Excel експорти
│   └── Imports/             # Excel імпорти
├── config/                  # Конфігураційні файли
├── database/
│   ├── migrations/          # 77 міграцій
│   └── seeders/             # Сідери даних
├── resources/
│   ├── views/               # Blade шаблони (30+ папок)
│   ├── js/                  # JavaScript
│   └── css/                 # Стилі
├── routes/
│   ├── web.php              # Веб-маршрути
│   └── api.php              # API-маршрути
├── docker/                  # Docker конфігурація
├── docker-compose.yml       # Локальна розробка
└── docker-compose.prod.yml  # Продакшн
```

### Multi-tenant архітектура

Система підтримує багато церков (multi-tenant):

```
┌─────────────────────────────────────────┐
│              Super Admin                │
│   (Глобальний адміністратор системи)    │
├─────────────────────────────────────────┤
│  Church 1  │  Church 2  │  Church 3     │
│  ────────  │  ────────  │  ────────     │
│  Users     │  Users     │  Users        │
│  People    │  People    │  People       │
│  Events    │  Events    │  Events       │
│  ...       │  ...       │  ...          │
└─────────────────────────────────────────┘
```

Кожна церква має власні дані, ізольовані через `church_id`.

---

## 3. Встановлення та розгортання

### Вимоги

- Docker & Docker Compose
- Git
- 2GB RAM мінімум
- 10GB вільного місця

### Локальна розробка

```bash
# Клонування репозиторію
git clone https://github.com/Fugerson/ministrify.git
cd ministrify

# Копіювання конфігурації
cp .env.example .env

# Запуск Docker
docker-compose up -d --build

# Встановлення залежностей
docker exec ministrify_app composer install

# Генерація ключа
docker exec ministrify_app php artisan key:generate

# Міграції
docker exec ministrify_app php artisan migrate

# Сідери (опційно)
docker exec ministrify_app php artisan db:seed

# Символічне посилання для storage
docker exec ministrify_app php artisan storage:link
```

### Продакшн розгортання

```bash
# SSH на сервер
ssh root@your-server

# Клонування
cd /var/www
git clone https://github.com/Fugerson/ministrify.git
cd ministrify

# Налаштування .env
cp .env.example .env
nano .env  # Налаштувати DB, MAIL, LIQPAY тощо

# Запуск продакшн контейнерів
docker compose -f docker-compose.prod.yml up -d --build

# Міграції та кеш
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
```

### Docker-контейнери

| Контейнер | Опис | Порт |
|-----------|------|------|
| ministrify_app | PHP-FPM додаток | - |
| ministrify_nginx | Веб-сервер | 80, 443 |
| ministrify_mysql | База даних | 3306 |
| ministrify_redis | Кеш/сесії | 6379 |
| ministrify_queue | Черга завдань | - |
| ministrify_scheduler | Cron scheduler | - |
| ministrify_phpmyadmin | Адміністрування БД | 8080 |

### Оновлення

```bash
# На продакшні
ssh root@49.12.100.17
cd /var/www/ministrify
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
```

---

## 4. Моделі даних

### Основні моделі

#### Church (Церква)
```php
// Головна модель організації
- id, name, slug, city, address
- logo, primary_color, theme
- telegram_bot_token (encrypted)
- public_site_enabled, public_site_settings
- pastor_name, pastor_photo, pastor_message
- service_times, website_url, social links
- subscription_plan_id, subscription_ends_at

// Зв'язки
hasMany: User, Person, Ministry, Event, Group, Tag, Transaction...
belongsTo: SubscriptionPlan

// Методи
getTotalIncome(), getTotalExpense(), getCurrentBalance()
hasFeature($feature), canAdd($resource), getUsageStats()
```

#### User (Користувач системи)
```php
- id, church_id, name, email, password
- role: 'admin' | 'leader' | 'volunteer'
- is_super_admin: boolean
- theme, preferences (JSON)
- onboarding_completed, onboarding_state

// Зв'язки
belongsTo: Church
hasOne: Person

// Методи
isSuperAdmin(), isAdmin(), isLeader(), isVolunteer()
canManageMinistry(Ministry)
```

#### Person (Член церкви)
```php
- id, church_id, user_id (optional)
- first_name, last_name, phone, email
- telegram_username, telegram_chat_id
- photo, address, birth_date, baptism_date
- church_role_id, membership_status
- shepherd_id, is_shepherd

// Статуси членства
'guest', 'newcomer', 'member', 'active'

// Зв'язки
belongsTo: Church, User, ChurchRole, Person (shepherd)
belongsToMany: Ministry (with position_ids), Group, Tag
hasMany: EventResponsibility, BlockoutDate, Transaction

// Методи
getAge(), getAgeCategory(), isAvailableOn($date)
getFamilyMembers(), getAttendanceStats()
```

#### Event (Подія)
```php
- id, church_id, ministry_id, title
- date, time, location, notes
- is_public, is_service, service_type
- allow_registration, registration_limit
- qr_checkin_enabled, checkin_token
- recurrence_rule, parent_event_id

// Типи богослужінь
SUNDAY_SERVICE, YOUTH_MEETING, PRAYER_MEETING, SPECIAL_SERVICE

// Зв'язки
belongsTo: Church, Ministry, Event (parent)
hasMany: EventResponsibility, ServicePlanItem, EventRegistration

// Методи
isFullyStaffed(), getUnfilledPositions()
canAcceptRegistrations(), generateCheckinToken()
```

#### Ministry (Служіння)
```php
- id, church_id, type_id, name, slug
- description, icon, color, leader_id
- monthly_budget, is_public

// Зв'язки
belongsTo: Church, MinistryType, Person (leader)
hasMany: Position, Event, Expense
belongsToMany: Person

// Методи
getSpentThisMonth(), getBudgetUsagePercent()
isBudgetWarning(), isBudgetExceeded()
```

#### Transaction (Фінансова транзакція)
```php
- id, church_id, direction: 'in' | 'out'
- source_type: 'tithe', 'offering', 'donation', 'expense', 'income'
- amount, currency, date, category_id
- person_id, ministry_id, campaign_id
- payment_method: 'cash', 'card', 'transfer', 'liqpay', 'monobank'
- status: 'pending', 'completed', 'failed', 'refunded'
- description, notes

// Зв'язки
belongsTo: Church, TransactionCategory, Person, Ministry
```

### Діаграма зв'язків (спрощена)

```
                    ┌─────────────┐
                    │   Church    │
                    └──────┬──────┘
           ┌───────────────┼───────────────┐
           │               │               │
     ┌─────▼─────┐   ┌─────▼─────┐   ┌─────▼─────┐
     │   User    │   │  Person   │   │ Ministry  │
     └─────┬─────┘   └─────┬─────┘   └─────┬─────┘
           │               │               │
           │         ┌─────┴─────┐   ┌─────▼─────┐
           │         │   Tags    │   │ Position  │
           │         │  Family   │   └───────────┘
           │         │ Blockouts │
           │         └───────────┘
           │               │
           │         ┌─────▼─────────────────────┐
           └────────►│         Event             │
                     └─────┬─────────────────────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
        ┌─────▼─────┐ ┌────▼─────┐ ┌───▼────┐
        │Responsibility│ ServicePlan│ Checklist│
        └───────────┘ └──────────┘ └────────┘
```

### Повний список моделей (85+)

| Модель | Призначення |
|--------|-------------|
| **Ядро** ||
| Church | Церква/організація |
| User | Користувач системи |
| Person | Член церкви/контакт |
| **Служіння** ||
| Ministry | Служіння/відділ |
| MinistryType | Тип служіння |
| Position | Позиція в служінні |
| MinistryMeeting | Зустріч служіння |
| MinistryJoinRequest | Заявка на вступ |
| **Події** ||
| Event | Подія/богослужіння |
| EventResponsibility | Призначення на подію |
| EventRegistration | Реєстрація на подію |
| EventChecklist | Чек-лист події |
| ServicePlanItem | Елемент плану богослужіння |
| **Групи** ||
| Group | Домашня група |
| GroupJoinRequest | Заявка на вступ в групу |
| **Відвідуваність** ||
| Attendance | Відвідуваність (уніфікована) |
| AttendanceRecord | Запис відвідуваності |
| **Розклад** ||
| BlockoutDate | Недоступні дати служителя |
| SchedulingPreference | Налаштування розкладу |
| SchedulingConflict | Конфлікт розкладу |
| **Фінанси** ||
| Transaction | Фінансова операція |
| TransactionCategory | Категорія транзакції |
| IncomeCategory | Категорія доходу |
| DonationCampaign | Кампанія пожертв |
| OnlineDonation | Онлайн пожертва |
| **Комунікація** ||
| PrivateMessage | Приватне повідомлення |
| Announcement | Оголошення |
| MessageTemplate | Шаблон повідомлення |
| TelegramMessage | Telegram повідомлення |
| PrayerRequest | Молитовне прохання |
| **Дошки** ||
| Board | Kanban дошка |
| BoardColumn | Колонка дошки |
| BoardCard | Картка завдання |
| BoardCardComment | Коментар до картки |
| **Публічний сайт** ||
| Sermon | Проповідь |
| SermonSeries | Серія проповідей |
| Gallery | Галерея |
| GalleryPhoto | Фото галереї |
| StaffMember | Член персоналу |
| BlogPost | Стаття блогу |
| BlogCategory | Категорія блогу |
| Faq | Питання/відповідь |
| Testimonial | Свідчення |
| **Система** ||
| Tag | Тег для людей |
| ChurchRole | Церковна роль |
| FamilyRelationship | Сімейний зв'язок |
| Resource | Файл/папка |
| AuditLog | Журнал аудиту |
| SubscriptionPlan | Тарифний план |
| Payment | Платіж |
| SupportTicket | Тікет підтримки |
| PushSubscription | PWA підписка |

---

## 5. Модулі та функціонал

### 5.1 Управління людьми

**Шлях:** `/people`

#### Можливості:
- CRUD операції для членів церкви
- Сімейні зв'язки (дружина/чоловік, діти, батьки, брати/сестри)
- Теги для категоризації
- Статуси членства: гість → новачок → член → активний член
- Церковні ролі (налаштовуються)
- Вікові категорії: дитина, підліток, молодь, дорослий, старший
- Статистика відвідуваності
- Історія пожертв
- Фото/аватар
- Призначення пастиря (shepherding)
- Імпорт/експорт Excel

#### Контролер: `PersonController`

```php
// Основні дії
index()           // Список людей з фільтрами
create() / store() // Створення
show()            // Перегляд профілю
edit() / update()  // Редагування
destroy()         // Видалення

// Додаткові
createAccount()   // Створення облікового запису
resetPassword()   // Скидання пароля
updateRole()      // Оновлення ролі
updateShepherd()  // Призначення пастиря
export()          // Експорт в Excel
import()          // Імпорт з Excel
```

### 5.2 Служіння

**Шлях:** `/ministries`

#### Можливості:
- Створення та управління служіннями
- Позиції всередині служіння
- Призначення лідера
- Місячний бюджет з попередженнями
- Управління членами
- Події служіння
- Зустрічі з порядком денним

#### Бюджетна система:
```php
// Попередження при 80%+ використання
isBudgetWarning() → помаранчевий індикатор

// Перевищення бюджету
isBudgetExceeded() → червоний індикатор + блокування

// Методи
getSpentThisMonth()
getBudgetUsagePercent()
getRemainingBudget()
canAddExpense($amount) → ['allowed' => bool, 'warning' => bool, 'message' => string]
```

### 5.3 Розклад та ротація служителів

**Шлях:** `/rotation`, `/schedule`, `/events`

#### Автоматична ротація:
```php
// SchedulingService
- Виявлення конфліктів (blockouts, одночасні призначення, сім'я)
- Перевірка доступності
- Scoring algorithm (баланс 40%, навички 30%, доступність 30%)
- Batch операції

// RotationService
- Розрахунок балів служителів
- Балансування навантаження
- Авто-призначення на рівні служіння або події
- Звіти ротації
```

#### Blockout dates (недоступні дати):
- Створення періодів недоступності
- Повторювані blockouts
- Причини (відпустка, хвороба, тощо)
- Інтеграція з календарем

#### Scheduling preferences:
- Максимум призначень на місяць
- Бажані служіння/позиції
- Доступні часові слоти

### 5.4 Події та богослужіння

**Шлях:** `/events`

#### Можливості:
- Календар подій
- Типи богослужінь (недільне, молодіжне, молитовне, особливе)
- Повторювані події
- План богослужіння (ServicePlan)
- QR check-in
- Публічна реєстрація
- Чек-листи
- Експорт iCal
- Google Calendar синхронізація

#### План богослужіння:
```php
ServicePlanItem:
- type: 'song', 'prayer', 'sermon', 'reading', 'announcement', 'multimedia'
- title, description, duration_minutes
- start_time, end_time, sort_order
- responsible_id, status
```

#### QR Check-in:
```php
// Генерація токену
$event->generateCheckinToken()

// URL для check-in
/checkin/{token}

// API для мобільного
POST /api/checkin/{token}/confirm
```

### 5.5 Фінанси

**Шлях:** `/finances`

#### Уніфікована система транзакцій:
```php
Transaction:
- direction: 'in' (дохід) | 'out' (витрата)
- source_type: 'tithe', 'offering', 'donation', 'income', 'expense'
- payment_method: 'cash', 'card', 'transfer', 'liqpay', 'monobank'
- status: 'pending', 'completed', 'failed', 'refunded'
```

#### Розділи:
- `/finances` — Dashboard
- `/finances/incomes` — Доходи
- `/finances/expenses` — Витрати
- `/finances/categories` — Категорії

#### Розрахунок балансу:
```php
$church->getCurrentBalance() =
    initial_balance + getTotalIncome() - getTotalExpense()
```

### 5.6 Групи

**Шлях:** `/groups`

#### Можливості:
- Домашні групи / Bible study
- Лідер та помічник
- Розклад зустрічей
- Відвідуваність
- Публічний доступ
- Заявки на вступ

#### Ролі в групі:
```php
const ROLES = [
    'leader' => 'Лідер',
    'assistant' => 'Помічник',
    'member' => 'Учасник'
];
```

### 5.7 Комунікації

#### Приватні повідомлення
**Шлях:** `/pm`
- Переписка між користувачами
- Непрочитані повідомлення
- Polling для оновлень

#### Оголошення
**Шлях:** `/announcements`
- Церковні оголошення
- Закріплення
- Термін дії

#### Telegram
**Шлях:** `/telegram/broadcast`, `/telegram/chat`
- Масові розсилки
- Прямі повідомлення
- Бот-інтеграція

### 5.8 Kanban дошки

**Шлях:** `/boards`

#### Структура:
```
Board
└── Column
    └── Card
        ├── Comments
        ├── Checklists
        ├── Attachments
        └── Activities
```

### 5.9 Конструктор сайту

**Шлях:** `/website-builder`

#### Модулі:
| Розділ | Шлях | Опис |
|--------|------|------|
| Dashboard | `/website-builder` | Статистика та preview |
| Шаблони | `/website-builder/templates` | Вибір шаблону |
| Секції | `/website-builder/sections` | Drag-drop редактор |
| Дизайн | `/website-builder/design` | Кольори, шрифти, CSS |
| Про нас | `/website-builder/about` | Сторінка "Про церкву" |
| Команда | `/website-builder/team` | Персонал |
| Проповіді | `/website-builder/sermons` | Архів проповідей |
| Галерея | `/website-builder/gallery` | Фотогалереї |
| Блог | `/website-builder/blog` | Статті |
| FAQ | `/website-builder/faq` | Питання/відповіді |
| Свідчення | `/website-builder/testimonials` | Свідчення людей |
| Молитви | `/website-builder/prayer-inbox` | Молитовні прохання |

#### Публічний сайт:
```
/c/{church_slug}/           # Головна
/c/{church_slug}/events     # Події
/c/{church_slug}/ministries # Служіння
/c/{church_slug}/groups     # Групи
/c/{church_slug}/donate     # Пожертви
/c/{church_slug}/contact    # Контакти
```

### 5.10 Білінг та підписки

**Шлях:** `/billing`

#### Тарифні плани:
```php
SubscriptionPlan:
- price_monthly, price_yearly
- max_people, max_ministries, max_events_per_month, max_users
- has_telegram_bot, has_finances, has_forms
- has_website_builder, has_custom_domain
- has_api_access, has_boards
```

#### Платіжні методи:
- LiqPay
- Monobank (в розробці)

---

## 6. API

### Публічні endpoints

```php
// Календар (з токеном)
GET /api/calendar/feed/{token}        # iCal feed
GET /api/calendar/events              # JSON події
GET /api/calendar/ministries          # JSON служіння

// Telegram webhook
POST /api/telegram/webhook

// Платіжні webhook
POST /api/webhooks/liqpay
```

### Захищені endpoints (auth required)

```php
// PWA / мобільний
GET  /api/pwa/my-schedule             # Мій розклад
POST /api/responsibility/{id}/confirm # Підтвердити
POST /api/responsibility/{id}/decline # Відхилити

// Push сповіщення
POST /api/push/subscribe
POST /api/push/unsubscribe
POST /api/push/test

// QR Check-in
GET  /api/checkin/today-events
POST /api/checkin/{token}/verify
POST /api/checkin/{token}/confirm
```

---

## 7. Маршрути (Routes)

### Структура web.php

```php
// 1. Публічні (без auth)
Route::get('/checkin/{token}', ...)          // QR check-in
Route::get('/', [LandingController, 'home']) // Landing
Route::get('/c/{slug}/...', ...)             // Публічний сайт церкви

// 2. Авторизація
Route::get('/login', ...)
Route::post('/login', ...)
Route::get('/register', ...)
Route::post('/logout', ...)

// 3. System Admin (super_admin only)
Route::prefix('system-admin')
    ->middleware(['auth', 'super_admin'])
    ->group(function () {
        // Churches, users, audit, support, tasks
    });

// 4. Захищені (auth + church + onboarding)
Route::middleware(['auth', 'church', 'onboarding'])
    ->group(function () {
        // Dashboard, people, ministries, events, groups...
    });

// 5. За ролями
Route::middleware(['role:admin'])->group(...);     // Settings, billing
Route::middleware(['role:admin,leader'])->group(...); // Reports, messages
```

### Кількість маршрутів за модулями

| Модуль | Кількість |
|--------|-----------|
| People | ~15 |
| Events | ~20 |
| Ministries | ~15 |
| Groups | ~12 |
| Finances | ~15 |
| Website Builder | ~40 |
| System Admin | ~30 |
| Settings | ~25 |
| API | ~15 |
| **Загалом** | **~200+** |

---

## 8. Сервіси

### SchedulingService
**Шлях:** `app/Services/SchedulingService.php`

```php
class SchedulingService
{
    // Конфігурація
    const MIN_REST_DAYS = 7;
    const MAX_ASSIGNMENTS_PER_MONTH = 4;

    // Ваги для scoring
    const WEIGHT_BALANCE = 0.4;
    const WEIGHT_SKILL = 0.3;
    const WEIGHT_AVAILABILITY = 0.3;

    // Методи
    detectConflicts(Person, Event): array
    checkAvailability(Person, DateTime): bool
    calculateScore(Person, Position, Event): float
    autoAssign(Event, array $options): array
}
```

### RotationService
**Шлях:** `app/Services/RotationService.php`

```php
class RotationService
{
    getVolunteerScore(Person, Ministry): float
    autoAssignMinistry(Ministry, DateRange): array
    autoAssignEvent(Event): array
    generateRotationReport(Ministry): array
}
```

### PaymentService
**Шлях:** `app/Services/PaymentService.php`

```php
class PaymentService
{
    createPayment(Church, Plan, array $options): Payment
    processCallback(array $data): bool
    verifySignature(string $data, string $signature): bool
    getPaymentStatus(string $orderId): string
}
```

### TelegramService
**Шлях:** `app/Services/TelegramService.php`

```php
class TelegramService
{
    setWebhook(string $botToken, string $url): bool
    sendMessage(string $chatId, string $text): bool
    sendBroadcast(Church, array $chatIds, string $message): array
    handleWebhook(array $update): void
}
```

### CalendarService
**Шлях:** `app/Services/CalendarService.php`

```php
class CalendarService
{
    generateICalFeed(Church): string
    parseRecurrenceRule(string $rule): array
    syncWithGoogle(Church, Event): bool
}
```

---

## 9. Конфігурація

### Основні файли

| Файл | Опис |
|------|------|
| `config/app.php` | Назва, timezone, providers |
| `config/auth.php` | Guards, password reset |
| `config/database.php` | DB connections |
| `config/services.php` | Telegram, LiqPay keys |
| `config/public_site_templates.php` | Шаблони сайту |
| `config/security.php` | Security headers |

### Ключові .env змінні

```env
# Додаток
APP_NAME=Ministrify
APP_URL=https://ministrify.app

# База даних
DB_HOST=mysql
DB_DATABASE=churchhub
DB_USERNAME=root
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_USERNAME=...
MAIL_PASSWORD=...

# Telegram
TELEGRAM_BOT_TOKEN=...

# LiqPay
LIQPAY_PUBLIC_KEY=...
LIQPAY_PRIVATE_KEY=...

# Queue
QUEUE_CONNECTION=redis
```

---

## 10. Ролі та права доступу

### Рівні ролей

```
┌────────────────────────────────────────────────────────────┐
│                    Super Admin                              │
│  Повний доступ до всіх церков, system-admin панель         │
├────────────────────────────────────────────────────────────┤
│                       Admin                                 │
│  Повний доступ до своєї церкви, налаштування, білінг       │
├────────────────────────────────────────────────────────────┤
│                      Leader                                 │
│  Управління своїми служіннями, люди, події, звіти          │
├────────────────────────────────────────────────────────────┤
│                     Volunteer                               │
│  Перегляд розкладу, підтвердження призначень               │
└────────────────────────────────────────────────────────────┘
```

### Middleware

```php
// CheckRole middleware
Route::middleware(['role:admin'])->group(...);
Route::middleware(['role:admin,leader'])->group(...);

// EnsureChurchContext middleware
// Забезпечує church_id контекст для всіх запитів

// CheckOnboarding middleware
// Перенаправляє нових admins на onboarding wizard
```

### Права за модулями

| Модуль | Volunteer | Leader | Admin |
|--------|-----------|--------|-------|
| Dashboard | ✓ | ✓ | ✓ |
| Мій розклад | ✓ | ✓ | ✓ |
| Люди (перегляд) | ✓ | ✓ | ✓ |
| Люди (редагування) | ✗ | ✓ | ✓ |
| Служіння (перегляд) | ✓ | ✓ | ✓ |
| Служіння (управління) | ✗ | Свої | ✓ |
| Події | ✓ | ✓ | ✓ |
| Ротація | ✗ | ✓ | ✓ |
| Фінанси | ✗ | ✗ | ✓ |
| Налаштування | ✗ | ✗ | ✓ |
| Білінг | ✗ | ✗ | ✓ |
| Конструктор сайту | ✗ | ✗ | ✓ |

---

## 11. Інтеграції

### Telegram Bot

```php
// Налаштування webhook
TelegramService::setWebhook($token, $url);

// Обробка команд
/start    — Початок, прив'язка акаунту
/schedule — Мій розклад
/help     — Допомога

// Broadcast
TelegramBroadcastController::send($churchId, $message, $recipients);
```

### LiqPay

```php
// Ініціація платежу
$liqpay->createPayment([
    'amount' => $plan->price_monthly,
    'currency' => 'UAH',
    'description' => "Підписка {$plan->name}",
    'order_id' => $orderId,
    'result_url' => route('billing.callback'),
    'server_url' => route('api.webhooks.liqpay'),
]);

// Callback обробка
PaymentService::processCallback($request->all());
```

### Google Calendar

```php
// Синхронізація подій
CalendarService::syncWithGoogle($church, $event);

// iCal feed
GET /api/calendar/feed/{token}
```

### PWA & Push Notifications

```php
// Підписка
POST /api/push/subscribe
{
    "endpoint": "...",
    "keys": {
        "p256dh": "...",
        "auth": "..."
    }
}

// Відправка
WebPushService::send($userId, $title, $body, $data);
```

---

## 12. Структура файлів

### Views структура

```
resources/views/
├── layouts/
│   ├── app.blade.php           # Основний layout
│   ├── guest.blade.php         # Для неавторизованих
│   ├── landing.blade.php       # Landing pages
│   └── system-admin.blade.php  # Super admin panel
├── components/
│   ├── button.blade.php
│   ├── modal.blade.php
│   ├── card.blade.php
│   └── ...
├── dashboard/
├── people/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── edit.blade.php
├── ministries/
├── events/
├── groups/
├── finances/
│   ├── index.blade.php         # Dashboard
│   ├── incomes/
│   └── expenses/
├── website-builder/
│   ├── index.blade.php
│   ├── templates/
│   ├── sections/
│   ├── design/
│   └── ...
├── settings/
├── system-admin/
└── public/                      # Публічний сайт церкви
```

### Controllers структура

```
app/Http/Controllers/
├── Controller.php               # Base controller
├── Auth/
│   ├── AuthController.php
│   └── RegisterController.php
├── Api/
│   ├── CalendarController.php
│   ├── TelegramController.php
│   └── ...
├── WebsiteBuilder/
│   ├── WebsiteBuilderController.php
│   ├── TemplateController.php
│   ├── DesignController.php
│   └── ...
├── PersonController.php
├── MinistryController.php
├── EventController.php
├── GroupController.php
├── FinanceController.php
├── RotationController.php
├── SettingsController.php
├── SystemAdminController.php
└── ...
```

### Models структура

```
app/Models/
├── Church.php
├── User.php
├── Person.php
├── Ministry.php
├── Position.php
├── Event.php
├── EventResponsibility.php
├── Group.php
├── Transaction.php
├── Board.php
├── BoardColumn.php
├── BoardCard.php
├── Sermon.php
├── Gallery.php
├── SubscriptionPlan.php
└── ... (85+ моделей)
```

---

## Додаток A: Команди Artisan

### Безпечні команди

```bash
# Міграції (ТІЛЬКИ для локальної розробки або продакшн з --force)
php artisan migrate
php artisan migrate --force    # На продакшні

# Кеш
php artisan config:cache
php artisan view:clear
php artisan route:clear
php artisan cache:clear

# Queue
php artisan queue:work
php artisan queue:restart

# Scheduler
php artisan schedule:run

# Custom
php artisan storage:link
```

### НЕБЕЗПЕЧНІ КОМАНДИ - НЕ ВИКОРИСТОВУВАТИ НА ПРОДАКШН!

```bash
# ВИДАЛЯЄ ВСІ ДАНІ БЕЗ МОЖЛИВОСТІ ВІДНОВЛЕННЯ!!!
php artisan migrate:fresh    # ЗАБОРОНЕНО
php artisan migrate:fresh --seed  # ЗАБОРОНЕНО
php artisan migrate:reset    # ЗАБОРОНЕНО
php artisan db:wipe          # ЗАБОРОНЕНО
```

Ці команди ЗНИЩУЮТЬ всю базу даних. Використовуйте лише на локальному середовищі!

## Додаток B: Troubleshooting

### 500 Error
```bash
# Перевірити логи
docker exec app tail -100 storage/logs/laravel.log

# Очистити кеш
php artisan config:cache
php artisan view:clear
```

### Проблеми з Docker
```bash
# Перезапуск контейнерів
docker compose down
docker compose up -d --build

# Перегляд логів
docker logs ministrify_app
```

### Проблеми з правами
```bash
docker exec app chmod -R 775 storage bootstrap/cache
docker exec app chown -R www:www storage bootstrap/cache
```

---

## Додаток C: Контакти

**Репозиторій:** https://github.com/Fugerson/ministrify
**Продакшн:** https://ministrify.app
**Підтримка:** Через систему тікетів у додатку

---

*Документація оновлена: Січень 2026*
