# ChurchHub (Ministrify)

Laravel 10 + Blade + Alpine.js + Tailwind CSS. Multi-tenancy через church_id.

## Deployment

**Сервер:** `root@49.12.100.17` (SSH порт: **2222**)

```bash
# Деплой (повний rebuild обов'язковий для blade змін!)
ssh -p 2222 root@49.12.100.17 "cd /var/www/ministrify && git pull && docker compose -f docker-compose.prod.yml down && docker compose -f docker-compose.prod.yml up -d --build && sleep 15 && docker compose -f docker-compose.prod.yml exec -T app php artisan optimize:clear"
```

## 🚨 ЗАБОРОНЕНО НА ПРОДІ

```bash
# НІКОЛИ - видаляє всі дані!
php artisan migrate:fresh
php artisan migrate:reset
php artisan db:wipe
```

**Перед міграціями:** `./backup.sh`
**Бекапи:** `/var/www/ministrify/backups/` (кожні 6 годин, 30 останніх)

## Структура

```
app/Http/Controllers/  - Контролери (RequiresChurch trait)
app/Models/            - Church, Person, User, Event, Ministry, Group
app/Services/          - Бізнес логіка
app/Traits/            - Auditable, RequiresChurch
resources/views/       - Blade + Alpine.js
```
