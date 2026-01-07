# /plan [feature] - Feature Planning Agent

Створи детальний план реалізації фічі.

## Використання:
```
/plan notification system
/plan add recurring events
/plan member check-in via QR
```

## Кроки:

1. **Аналіз вимог:**
   - Що саме потрібно зробити
   - Хто буде користуватись
   - Які сценарії використання

2. **Дослідження коду:**
   - Які існуючі компоненти можна використати
   - Які моделі задіяні
   - Які контролери потрібно змінити/створити

3. **Database Design:**
   - Нові таблиці
   - Зміни в існуючих таблицях
   - Relationships
   - Indexes

4. **Backend Plan:**
   - Models
   - Controllers
   - Services
   - Policies
   - Events/Listeners

5. **Frontend Plan:**
   - Views (Blade)
   - Components
   - Alpine.js interactions
   - API endpoints (якщо потрібно)

6. **Testing Plan:**
   - Unit tests
   - Feature tests
   - Edge cases

## Формат:

```markdown
## Feature Plan: [Feature Name]

### Overview
Brief description of the feature.

### User Stories
- As a [role], I want to [action], so that [benefit]

### Database Changes

#### New Tables
```sql
CREATE TABLE table_name (
  id BIGINT PRIMARY KEY,
  ...
);
```

#### Modified Tables
- Add column X to table Y

### Implementation Steps

#### Phase 1: Database
1. Create migration for X
2. Update Model Y

#### Phase 2: Backend
1. Create XController
2. Add routes
3. Create XService

#### Phase 3: Frontend
1. Create views
2. Add to navigation

#### Phase 4: Testing
1. Write tests

### Files to Create/Modify
- `app/Models/X.php` (create)
- `app/Http/Controllers/XController.php` (create)
- `database/migrations/xxx_create_x_table.php` (create)
- `resources/views/x/index.blade.php` (create)
- `routes/web.php` (modify)

### Estimated Complexity
- Database: Low/Medium/High
- Backend: Low/Medium/High
- Frontend: Low/Medium/High
- Overall: Low/Medium/High

### Dependencies
- Requires X to be done first

### Risks & Considerations
- Risk 1
- Consideration 2
```
