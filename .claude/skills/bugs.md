# /bugs - Bug Hunter Agent

Знайди потенційні баги в коді.

## Кроки:

1. **Перевір TODO/FIXME:**
   ```bash
   grep -rn "TODO\|FIXME\|BUG\|HACK\|XXX" app/ resources/ --include="*.php" --include="*.blade.php"
   ```

2. **Перевір логи на помилки:**
   ```bash
   tail -200 storage/logs/laravel.log | grep -i "error\|exception\|warning"
   ```

3. **Знайди потенційні null pointer:**
   - Виклики методів без null check: `$user->person->name` без `?`
   - Optional chaining відсутній

4. **Перевір exception handling:**
   - Контролери без try-catch де потрібно
   - Swallowed exceptions (catch без логування)

5. **Перевір edge cases:**
   - Ділення на нуль
   - Пусті масиви без перевірки
   - Missing default values

6. **Перевір race conditions:**
   - Concurrent updates
   - Missing database transactions

7. **Перевір валідацію:**
   - Store/Update без валідації
   - Incomplete validation rules

## Формат звіту:

```markdown
## Bug Hunt Report

### Confirmed Bugs
- [ ] Bug description → file:line
  - Impact: High/Medium/Low
  - Fix: Description

### Potential Issues
- [ ] Description → file:line
  - Risk: High/Medium/Low

### Code Smells
- [ ] Description → file:line

### TODOs Found
- [ ] TODO text → file:line

### Recommendations
1. Priority fixes
```
