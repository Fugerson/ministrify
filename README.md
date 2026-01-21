# Ministrify

Веб-додаток для управління церквою. Допомагає з розкладом служінь, обліком людей, витратами та відвідуваністю.

## Технології

- **Backend:** Laravel 10+ (PHP 8.2)
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **База даних:** MySQL 8
- **Кеш:** Redis
- **Сповіщення:** Telegram Bot API
- **Деплой:** Docker + Nginx

## Швидкий старт

### 1. Клонуйте репозиторій

```bash
git clone <repository-url>
cd ministrify
```

### 2. Скопіюйте файл конфігурації

```bash
cp .env.example .env
```

### 3. Запустіть Docker

```bash
docker-compose up -d
```

### 4. Встановіть залежності

```bash
docker-compose exec app composer install
``` 

### 5. Згенеруйте ключ додатку

```bash
docker-compose exec app php artisan key:generate
```

### 6. Запустіть міграції

```bash
docker-compose exec app php artisan migrate
```

### 7. (Опційно) Заповніть демо-даними

```bash
docker-compose exec app php artisan db:seed
```

### 8. Відкрийте в браузері

```
http://localhost
```

**Демо-вхід:** admin@ministrify.app / password

## Модулі

- **Люди** - CRUD, теги, пошук, фільтри
- **Служіння** - управління служіннями, позиції, учасники
- **Розклад** - події, призначення людей, статуси
- **Витрати** - облік витрат, бюджети, звіти
- **Відвідуваність** - check-in, статистика
- **Telegram бот** - сповіщення, підтвердження

## Ролі

| Роль | Можливості |
|------|------------|
| Адмін | Повний доступ |
| Лідер | Управління своїм служінням |
| Волонтер | Перегляд розкладу, підтвердження |

## Telegram бот

1. Створіть бота через @BotFather
2. Отримайте токен
3. Введіть токен в Налаштуваннях
4. Налаштуйте webhook:

```bash
docker-compose exec app php artisan telegram:set-webhook
```

## Розробка

```bash
# Запуск тестів
docker-compose exec app php artisan test

# Форматування коду
docker-compose exec app ./vendor/bin/pint

# Очистка кешу
docker-compose exec app php artisan optimize:clear
```

## Ліцензія

MIT
