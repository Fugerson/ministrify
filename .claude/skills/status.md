# /status - Project Status Agent

Покажи поточний стан проекту.

## Збери інформацію:

1. **Git Status:**
   ```bash
   git status --short
   git log --oneline -5
   git branch -a
   ```

2. **Uncommitted Changes:**
   ```bash
   git diff --stat
   ```

3. **Database:**
   ```bash
   php artisan migrate:status
   ```

4. **Dependencies:**
   ```bash
   composer outdated --direct
   ```

5. **Структура проекту:**
   - Кількість контролерів
   - Кількість моделей
   - Кількість views

6. **Код метрики:**
   ```bash
   find app -name "*.php" | wc -l
   find resources/views -name "*.blade.php" | wc -l
   ```

## Формат звіту:

```markdown
## Project Status Report

### Git
- Branch: `main`
- Last commit: [hash] [message]
- Uncommitted changes: X files

### Database
- Pending migrations: X
- Status: ✅ Up to date / ⚠️ Needs migration

### Dependencies
- Outdated packages: X
- Security issues: X

### Code Stats
- Controllers: X
- Models: X
- Views: X
- Total PHP files: X

### Health Check
- [ ] All migrations applied
- [ ] No uncommitted changes
- [ ] Dependencies up to date
- [ ] No errors in logs

### Recommended Actions
1. Action item
```
