<div x-data="{
    form: {
        name: '{{ $church->name ?? '' }}',
        city: '{{ $church->city ?? '' }}',
        address: '{{ $church->address ?? '' }}',
        public_email: '{{ $church->public_email ?? '' }}',
        public_phone: '{{ $church->public_phone ?? '' }}'
    },
    errors: {},
    touched: {},
    preview: '{{ $church->logo ? Storage::url($church->logo) : '' }}',

    validateField(field) {
        this.touched[field] = true;
        this.errors[field] = null;
        switch(field) {
            case 'name':
                if (!this.form.name.trim()) this.errors.name = 'Назва обов\'язкова';
                else if (this.form.name.length > 255) this.errors.name = 'Максимум 255 символів';
                break;
            case 'public_email':
                if (this.form.public_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.public_email))
                    this.errors.public_email = 'Невірний формат';
                break;
        }
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-0.5">КРОК 2</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Профіль церкви</h2>
            </div>
        </div>
    </div>

    <form class="space-y-6" enctype="multipart/form-data">
        <!-- Logo Upload -->
        <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-slate-700/50 dark:to-slate-800/50 rounded-2xl border border-gray-200/50 dark:border-slate-600/50">
            <div class="relative group">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white dark:bg-slate-700 shadow-lg border-4 border-white dark:border-slate-600 flex items-center justify-center">
                    <template x-if="preview">
                        <img :src="preview" class="w-full h-full object-cover" alt="Logo">
                    </template>
                    <template x-if="!preview">
                        <svg class="w-10 h-10 text-gray-300 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </template>
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Логотип церкви</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">PNG або JPG, до 2MB</p>
                <label class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-600 border border-gray-200 dark:border-slate-500 rounded-xl cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-500 transition-colors shadow-sm">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Вибрати файл</span>
                    <input type="file" name="logo" accept="image/*" class="hidden"
                           @change="const f=$event.target.files[0]; if(f){if(f.size>2*1024*1024){alert('Макс. 2MB');$event.target.value='';return}preview=URL.createObjectURL(f)}">
                </label>
            </div>
        </div>

        <!-- Church Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Назва церкви <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name"
                   x-model="form.name"
                   @blur="validateField('name')"
                   @input="touched.name && validateField('name')"
                   :class="{'ring-2 ring-red-500 border-red-500': errors.name, 'ring-2 ring-green-500 border-green-500': touched.name && !errors.name && form.name}"
                   class="w-full px-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-lg"
                   placeholder="Церква Благодаті">
            <p x-show="errors.name" x-cloak class="mt-2 text-sm text-red-500 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="errors.name"></span>
            </p>
        </div>

        <!-- Location Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Місто</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <input type="text" name="city" x-model="form.city"
                           class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                           placeholder="Київ">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Адреса</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </span>
                    <input type="text" name="address" x-model="form.address"
                           class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                           placeholder="вул. Хрещатик, 1">
                </div>
            </div>
        </div>

        <!-- Contact Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input type="email" name="public_email" x-model="form.public_email"
                           @blur="validateField('public_email')"
                           :class="{'ring-2 ring-red-500 border-red-500': errors.public_email}"
                           class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                           >
                </div>
                <p x-show="errors.public_email" x-cloak class="mt-2 text-sm text-red-500" x-text="errors.public_email"></p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Телефон</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <input type="tel" name="public_phone" x-model="form.public_phone"
                           class="w-full pl-12 pr-4 py-3 border border-gray-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                           >
                </div>
            </div>
        </div>
    </form>
</div>
