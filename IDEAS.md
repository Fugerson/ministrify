# ChurchHub - Ideas Backlog

## Priority Features (To Develop)

### 2. Unified Finance Dashboard
**Status:** Planning
**Priority:** High
**Complexity:** Medium (3-4 days)

**Goal:** Єдина картина фінансів церкви

**Features:**
- [ ] Головний дашборд: Баланс | Дохід | Витрати
- [ ] Графіки трендів (6-12 місяців)
- [ ] Budget vs Actual по категоріях
- [ ] Прогноз на наступний місяць
- [ ] Аларми при перевищенні бюджету
- [ ] PDF звіти для керівництва
- [ ] Порівняння періодів (цей рік vs минулий)

**Technical:**
- Використати існуючу модель Transaction
- Додати агрегуючі запити
- Chart.js для візуалізації
- Кешування статистик

---

### 3. Communication Hub
**Status:** Planning
**Priority:** High
**Complexity:** High (5-7 days)

**Goal:** Централізована комунікація з членами

**Features:**
- [ ] Email кампанії (масові розсилки)
- [ ] Шаблони повідомлень з персоналізацією
- [ ] Сегментація аудиторії (по групах, служіннях, ролях)
- [ ] Історія комунікації з кожною людиною
- [ ] Планування розсилок (scheduled)
- [ ] Аналітика: відкриття, кліки

**Technical:**
- Нова модель `CommunicationCampaign`
- Нова модель `CommunicationLog`
- Queue jobs для масових розсилок
- Інтеграція з існуючим Telegram

---

### 7. Ministry Performance Reporting
**Status:** Planning
**Priority:** Medium
**Complexity:** Medium (4-5 days)

**Goal:** KPIs та звітність для служінь

**Features:**
- [ ] Dashboard з метриками для кожного служіння
- [ ] Залученість членів (% активних)
- [ ] Використання бюджету (план vs факт)
- [ ] Відвідуваність подій служіння
- [ ] Зростання команди за період
- [ ] Квартальні/річні звіти
- [ ] Рекомендації на основі даних

**Metrics:**
- Member engagement rate
- Budget utilization %
- Event attendance avg
- Team growth rate
- Activity score

**Technical:**
- Розширити модель Ministry
- Агрегуючі запити по attendance, transactions
- PDF генерація звітів

---

### 8. Resource Management System
**Status:** Planning
**Priority:** Medium
**Complexity:** Medium (3-4 days)

**Goal:** Облік та бронювання обладнання/ресурсів

**Features:**
- [ ] Інвентаризація обладнання
- [ ] Категорії: Звук, Світло, Меблі, Транспорт
- [ ] Статуси: Справний / Ремонт / Списаний
- [ ] QR-коди для швидкої ідентифікації
- [ ] Календар бронювання
- [ ] Історія використання
- [ ] Maintenance tracking (планові ТО)

**Technical:**
- Модель Resource вже існує - розширити
- Нова модель `ResourceBooking`
- Calendar view для бронювань
- QR генерація

---

## Future Ideas (Backlog)

### 1. Member Onboarding Funnel
Воронка для новачків: Guest → Member → Active

### 4. Prayer Request System
Публічна форма + dashboard для молитовної команди

### 5. Member Giving Analytics
Особистий кабінет пожертв для членів

### 6. Event RSVP & Guest Management
Реєстрація на події + облік гостей

---

## Notes

- Фокус на покращенні існуючої логіки
- Нові фічі розробляємо в окремих гілках
- Merge тільки після тестування
