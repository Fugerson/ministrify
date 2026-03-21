<select onchange="switchLocale(this.value)"
        class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5 px-2 bg-white/80 backdrop-blur-sm">
    <option value="uk" {{ app()->getLocale() === 'uk' ? 'selected' : '' }}>🇺🇦 Українська</option>
    <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
</select>
