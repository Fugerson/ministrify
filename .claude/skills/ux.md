# /ux - UX/UI Review Agent

Виконай аудит користувацького досвіду.

## Кроки:

1. **Перевір консистентність UI:**
   - Знайди всі кнопки: `grep -r "bg-primary-600\|bg-blue-600" resources/views/`
   - Переконайся що всі використовують `bg-primary-600`
   - Перевір padding кнопок (має бути консистентний)

2. **Перевір touch targets:**
   - Знайди маленькі кнопки: `grep -r "p-1\|p-1.5" resources/views/`
   - Всі інтерактивні елементи мають бути мінімум 44x44px

3. **Перевір форми:**
   - Всі інпути мають label
   - Є візуальна індикація помилок (червоний border)
   - Required поля позначені

4. **Перевір responsive:**
   - Таблиці адаптовані для мобільних
   - Меню працює на мобільних
   - Текст читабельний на малих екранах

5. **Перевір темну тему:**
   - Всі елементи мають `dark:` варіанти
   - Достатній контраст кольорів

6. **Перевір empty states:**
   - Що бачить користувач коли даних немає
   - Чи є helpful message

## Формат звіту:

```markdown
## UX Audit Report

### Critical Issues (блокують користувача)
- [ ] Issue description → file:line

### UX Problems (погіршують досвід)
- [ ] Issue description → file:line

### Inconsistencies (непослідовність)
- [ ] Issue description

### Accessibility
- [ ] Issue description

### Recommendations
1. Recommendation with priority
```
