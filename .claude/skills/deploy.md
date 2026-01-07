# /deploy - Deployment Checklist Agent

Перевір готовність до деплою.

## Pre-Deployment Checklist:

1. **Code Quality:**
   ```bash
   git status
   git diff --stat origin/main
   ```
   - [ ] Всі зміни закомічені
   - [ ] Код review пройдено

2. **Tests:**
   ```bash
   php artisan test
   ```
   - [ ] Всі тести проходять

3. **Database:**
   ```bash
   php artisan migrate:status
   ```
   - [ ] Міграції готові
   - [ ] Seeders оновлені (якщо потрібно)

4. **Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   - [ ] composer.lock актуальний
   - [ ] Немає dev dependencies в production

5. **Environment:**
   - [ ] .env.production налаштовано
   - [ ] APP_DEBUG=false
   - [ ] APP_ENV=production
   - [ ] Proper LOG_LEVEL

6. **Caching:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Assets:**
   ```bash
   npm run build
   ```
   - [ ] CSS/JS compiled
   - [ ] Assets versioned

8. **Security:**
   - [ ] No debug info exposed
   - [ ] HTTPS configured
   - [ ] CORS configured

## Deployment Commands:

```bash
# On server
cd /var/www/ministrify
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Post-Deployment:

1. **Verify:**
   - [ ] Site loads correctly
   - [ ] Login works
   - [ ] Key features work

2. **Monitor:**
   - [ ] Check error logs
   - [ ] Monitor performance

## Формат звіту:

```markdown
## Deployment Readiness Report

### Pre-flight Checks
- [ ] Code committed
- [ ] Tests passing
- [ ] Migrations ready
- [ ] Dependencies locked

### Warnings
- Warning description

### Ready to Deploy?
✅ Yes / ❌ No - fix issues first

### Deployment Commands
[Commands to run]

### Rollback Plan
[How to rollback if needed]
```
