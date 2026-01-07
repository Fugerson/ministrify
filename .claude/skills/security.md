# /security - Security Audit Agent

Виконай security аудит проекту.

## Кроки:

1. **CSRF Protection:**
   ```bash
   grep -rL "@csrf" resources/views/ --include="*.blade.php" | xargs grep -l "method=\"POST\"\|method=\"PUT\"\|method=\"DELETE\""
   ```
   Всі форми з POST/PUT/DELETE мають мати @csrf

2. **SQL Injection:**
   - Знайди raw queries: `grep -rn "DB::raw\|whereRaw\|selectRaw" app/`
   - Перевір що параметри escaped

3. **XSS Prevention:**
   - Знайди unescaped output: `grep -rn "{!!\|{!!" resources/views/`
   - Переконайся що це безпечно

4. **Authorization:**
   - Всі контролери мають перевірку авторизації
   - `$this->authorize()` або `Gate::allows()`
   - Перевірка `church_id` в запитах

5. **Mass Assignment:**
   - Моделі мають `$fillable` або `$guarded`
   - Sensitive fields захищені

6. **Authentication:**
   - Password hashing (bcrypt)
   - Session security
   - Remember me tokens

7. **Sensitive Data:**
   ```bash
   grep -rn "password\|secret\|token\|key" .env.example
   ```
   - Перевір що секрети не в коді
   - .env в .gitignore

8. **File Upload:**
   - Валідація типів файлів
   - Обмеження розміру
   - Безпечне зберігання

## Формат звіту:

```markdown
## Security Audit Report

### Critical Vulnerabilities
- [ ] Issue → file:line
  - Risk: Critical
  - Fix: Description

### High Risk
- [ ] Issue → file:line

### Medium Risk
- [ ] Issue → file:line

### Low Risk / Informational
- [ ] Issue → file:line

### Checklist
- [ ] CSRF on all forms
- [ ] No SQL injection
- [ ] XSS prevented
- [ ] Authorization in place
- [ ] Mass assignment protected
- [ ] Secrets not in code
- [ ] File uploads validated

### Recommendations
1. Priority security fix
```
