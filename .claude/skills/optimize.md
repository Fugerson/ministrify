# /optimize - Performance Optimizer Agent

Знайди проблеми продуктивності.

## Кроки:

1. **N+1 Queries:**
   - Шукай цикли з запитами в контролерах
   - Перевір що `with()` використовується для eager loading
   ```bash
   grep -rn "->get()\|->all()\|->find(" app/Http/Controllers/
   ```

2. **Missing Indexes:**
   - Перевір foreign keys в міграціях
   - Колонки в WHERE мають мати індекси
   ```bash
   grep -rn "->foreign\|->index" database/migrations/
   ```

3. **Large Queries:**
   - Запити без `limit()` або `paginate()`
   - `->get()` на великих таблицях

4. **Inefficient Loops:**
   - Queries всередині foreach
   - Multiple database calls де можна один

5. **Caching:**
   - Чи кешуються часті запити
   - Config/route caching

6. **Asset Optimization:**
   - CSS/JS мініфікація
   - Image optimization
   - Lazy loading

7. **Database:**
   ```bash
   grep -rn "whereHas\|with\|load" app/
   ```
   - Правильне використання relationships

## Формат звіту:

```markdown
## Performance Optimization Report

### Critical (Major Impact)
- [ ] Issue → file:line
  - Impact: High
  - Fix: Use eager loading with()

### High Priority
- [ ] Issue → file:line

### Medium Priority
- [ ] Issue → file:line

### Optimization Opportunities
- [ ] Description

### Database
- Missing indexes: X
- N+1 queries found: X

### Caching Recommendations
1. Cache X query
2. Use route caching

### Quick Wins
1. Easy optimization
```
