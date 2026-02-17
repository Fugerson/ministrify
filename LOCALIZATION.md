# 🌍 ChurchHub Localization (i18n)

## Overview

ChurchHub now supports multiple languages! Users can switch between Ukrainian (UK) and English (EN) with their preference being automatically saved.

## Available Languages

- 🇺🇦 **Ukrainian (uk)** - Default language
- 🇬🇧 **English (en)** - Fully translated

## How Language Selection Works

### 1. Priority Order

The SetLocale middleware determines the user's language in this order:

1. **Cookie** (highest priority) - If user manually switched language via the toggle
2. **User Preference** - Stored in `users.preferences['locale']`
3. **Default** - `uk` (Ukrainian)

### 2. Language Switcher

The language switcher component is available on:
- Public pages (landing, events, etc.)
- Guest pages (login, register, etc.)
- Public church sites

```blade
<x-locale-switcher />
```

### 3. Switching Language

Users can switch language by:
- Clicking the language toggle button (🇺🇦 Укр / 🇬🇧 Eng)
- The preference is saved to both:
  - Browser cookie (expires in 1 year)
  - User preferences (if authenticated)

## Translation Files

### File Structure

```
lang/
├── uk/                      # Ukrainian (source language)
│   ├── auth.php
│   ├── passwords.php
│   └── pagination.php
│
└── en/                      # English (new!)
    ├── app.php             # Main application strings (183 translations)
    ├── ui.php              # UI components (98 translations)
    ├── forms.php           # Form elements (91 translations)
    ├── common.php          # Common UI (141 translations)
    ├── messages.php        # Messages & notifications (94 translations)
    ├── validation.php      # Form validation (19 translations)
    ├── auth.php            # Authentication
    ├── passwords.php       # Password reset
    └── pagination.php      # Pagination controls
```

### Total Translations

- **925 lines** of English translations across all files
- **~800+ unique strings** translated

## Using Translations in Code

### Blade Templates

```blade
<!-- Simple translation -->
<h1>{{ __('app.dashboard') }}</h1>

<!-- With parameters -->
<p>{{ __('messages.welcome_name', ['name' => $user->name]) }}</p>

<!-- Using dot notation -->
<span>{{ __('ui.loading') }}</span>
```

### PHP Code

```php
$title = __('app.dashboard');
$message = __('messages.item_added');

// With parameters
$greeting = __('messages.welcome_name', ['name' => 'John']);
```

## Adding New Translations

When adding new UI text:

1. **Add the Ukrainian version first** (in Blade or messages):
```blade
<button>{{ __('Нова кнопка') }}</button>
```

2. **Add to locale files** - Once the feature is stable, add to:
   - `lang/uk/*.php` (create if needed)
   - `lang/en/*.php` (English version)

3. **Use string keys** for consistency:
```blade
<!-- Before -->
<button>{{ __('Нова кнопка') }}</button>

<!-- After - organized -->
<button>{{ __('ui.new_button') }}</button>
```

## LocalizationFiles Reference

### lang/en/app.php
Main application strings including:
- Dashboard & navigation
- People, groups, ministries
- Events & schedule
- Attendance & finances
- Settings & roles
- 180+ common action strings

### lang/en/ui.php
UI component text including:
- Buttons & status messages
- Modals & dialogs
- Lists & search
- Loading states
- 98 UI-specific strings

### lang/en/forms.php
Form-related strings including:
- Form labels
- Form buttons
- Placeholders
- Error messages
- 91 form-specific strings

### lang/en/common.php
Navigation & common UI including:
- Main menu items
- Common buttons
- Date/time strings
- Pagination
- 141 common strings

### lang/en/messages.php
Application messages including:
- Dashboard widgets
- Notifications
- Status updates
- Task & team messages
- 94 message strings

### lang/en/validation.php
Form validation messages (Laravel standard)

### lang/en/auth.php
Authentication messages (Laravel standard)

### lang/en/passwords.php
Password reset messages (Laravel standard)

### lang/en/pagination.php
Pagination controls (Laravel standard)

## Missing Translations

If you see untranslated text (still in Ukrainian) when viewing the site in English:

1. **Check if the string exists** in the corresponding `lang/en/*.php` file
2. **Add missing translations** to the appropriate file:
   - `app.php` - Main strings
   - `ui.php` - UI elements
   - `forms.php` - Form text
   - etc.

3. **Use consistent key naming**:
   - Lowercase with underscores
   - Clear, descriptive names
   - Group related strings in same file

Example:
```php
// lang/en/ui.php
return [
    'confirm_delete' => 'Are you sure you want to delete this?',
    'loading' => 'Loading...',
    'no_results' => 'No results found',
];
```

## Configuration

### app.php

```php
'locale' => 'uk',                           // Default language
'fallback_locale' => 'en',                  // Fallback if translation not found
'available_locales' => ['uk', 'en'],        // Available languages for switching
```

### SetLocale Middleware

Located at `app/Http/Middleware/SetLocale.php`

The middleware:
- Checks user's cookie preference first
- Falls back to user preference in DB
- Uses default locale if neither exists
- Sets locale and Carbon locale

## Testing Language Switching

1. **Visit the application**
2. **Click the language toggle** (top-right corner)
3. **Observe:**
   - URL doesn't change (language is in cookie/preference)
   - UI updates to English
   - Language preference is saved
   - Page maintains state

## Adding a New Language

To add a new language (e.g., Spanish - `es`):

1. Create new directory: `lang/es/`
2. Copy all files from `lang/en/`:
   ```bash
   cp -r lang/en/* lang/es/
   ```
3. Translate all strings in Spanish
4. Update `config/app.php`:
   ```php
   'available_locales' => ['uk', 'en', 'es'],
   ```
5. Update locale-switcher component to show Spanish button

## Best Practices

✅ **Do:**
- Use `__()` helper for all user-facing text
- Store translations in `lang/*/` files
- Keep translations organized by category
- Use consistent key naming (snake_case)
- Provide context/parameters when translating

❌ **Don't:**
- Hardcode text in Blade templates
- Mix translation files (keep Ukrainian in `uk/`, English in `en/`)
- Create new translation files without checking existing ones
- Use PascalCase or camelCase for keys
- Rely on fallback language for all strings

## Performance

- Translations are cached by Laravel
- No database queries for translations
- Minimal overhead (simple array lookups)
- Clear cache after deploying new translations: `php artisan cache:clear`

## Troubleshooting

### Translations not appearing?

1. Check file exists: `lang/{locale}/{file}.php`
2. Check key syntax: `__('file.key')`
3. Clear cache: `php artisan cache:clear`
4. Check locale is being set correctly

### Language not switching?

1. Ensure SetLocale middleware is active
2. Check cookie is being set: `Route::post('/locale/{locale}', ...)`
3. Verify `available_locales` includes the language
4. Check browser console for errors

## Future Improvements

- [ ] Add more languages (Russian, French, Spanish, etc.)
- [ ] Create translation management interface
- [ ] Automated translation services integration
- [ ] RTL language support (Arabic, Hebrew)
- [ ] Translation key extraction tool
- [ ] Missing translation warnings in development

---

**Last Updated:** February 2026
**Localization Support:** Ukrainian (uk), English (en)
