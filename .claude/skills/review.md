# /review - Code Review Agent

Виконай комплексний code review проекту.

## Кроки:

1. Подивись останні зміни:
   - `git diff HEAD~5 --name-only` - які файли змінились
   - `git log --oneline -10` - останні коміти

2. Для кожного зміненого файлу перевір:
   - **Безпека**: SQL injection, XSS, missing CSRF, authorization
   - **Продуктивність**: N+1 queries, missing eager loading
   - **Якість**: DRY violations, code duplication, complexity
   - **Стиль**: Консистентність з рештою проекту

3. Перевір специфічні патерни Laravel:
   - Чи використовується `$this->authorize()` в контролерах
   - Чи є валідація в `store/update` методах
   - Чи правильно використовується `getCurrentChurch()`

4. Створи звіт у форматі:

```markdown
## Code Review Report

### Files Reviewed
- file1.php
- file2.blade.php

### Issues Found

#### Critical
- [ ] Description (file:line)

#### Warnings
- [ ] Description (file:line)

#### Suggestions
- [ ] Description

### Summary
✅ Ready to merge / ⚠️ Needs fixes / ❌ Major issues
```
