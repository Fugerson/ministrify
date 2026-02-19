@extends('layouts.app')

@section('title', 'Імпорт пісень')

@section('content')
<div class="max-w-6xl mx-auto" x-data="songImportWizard()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('songs.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Імпорт пісень</h1>
        </div>
        <p class="text-gray-600 dark:text-gray-400">Імпортуйте пісні з CSV файлу у бібліотеку</p>
    </div>

    <!-- Steps Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <template x-for="(stepName, index) in ['Завантаження', 'Мапінг полів', 'Імпорт']" :key="index">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full transition-colors"
                         :class="step > index + 1 ? 'bg-green-500 text-white' :
                                 step === index + 1 ? 'bg-primary-600 text-white' :
                                 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                        <template x-if="step > index + 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <span x-show="step <= index + 1" x-text="index + 1"></span>
                    </div>
                    <span class="ml-2 text-sm font-medium"
                          :class="step >= index + 1 ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                          x-text="stepName"></span>
                    <template x-if="index < 2">
                        <div class="w-24 h-0.5 mx-4"
                             :class="step > index + 1 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Step 1: File Upload -->
    <div x-show="step === 1" x-cloak class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Завантажте CSV файл</h2>

        <!-- Drop Zone -->
        <div class="border-2 border-dashed rounded-xl p-8 text-center transition-colors"
             :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-300 dark:border-gray-600'"
             @dragover.prevent="dragOver = true"
             @dragleave.prevent="dragOver = false"
             @drop.prevent="handleDrop($event)">

            <template x-if="!file">
                <div>
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">Перетягніть CSV файл сюди або</p>
                    <label class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg cursor-pointer hover:bg-primary-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Виберіть файл
                        <input type="file" accept=".csv,.txt" class="hidden" @change="handleFileSelect($event)">
                    </label>
                </div>
            </template>

            <template x-if="file">
                <div class="flex items-center justify-center gap-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-left">
                        <p class="font-medium text-gray-900 dark:text-white" x-text="file.name"></p>
                        <p class="text-sm text-gray-500" x-text="formatFileSize(file.size)"></p>
                    </div>
                    <button @click="file = null" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <h3 class="font-medium text-gray-900 dark:text-white mb-2">Формат CSV файлу:</h3>
            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 list-disc list-inside">
                <li>Перший рядок повинен містити назви колонок</li>
                <li>Обов'язкова колонка: Назва пісні (title, name, назва)</li>
                <li>Опціональні: виконавець, тональність, bpm, посилання, теги, текст, акорди, нотатки</li>
                <li>Підтримується будь-який CSV з будь-якими назвами колонок</li>
            </ul>
        </div>

        <div class="mt-6 flex justify-end">
            <button @click="uploadAndPreview()"
                    :disabled="!file || loading"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                <template x-if="loading">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="loading ? 'Обробка...' : 'Далі'"></span>
                <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Step 2: Column Mapping -->
    <div x-show="step === 2" x-cloak class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Мапінг полів</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Вкажіть відповідність між колонками CSV та полями пісень. Знайдено <span class="font-medium text-primary-600" x-text="totalRows"></span> записів.
        </p>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Song Fields -->
            <div class="space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">Поля пісні</h3>

                <template x-for="(field, key) in songFields" :key="key">
                    <div class="flex items-center gap-4">
                        <label class="w-32 text-sm text-gray-700 dark:text-gray-300" x-text="field.label"></label>
                        <select x-model="mappings[key]"
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">-- Не імпортувати --</option>
                            <template x-for="header in headers" :key="header">
                                <option :value="header" x-text="header"></option>
                            </template>
                        </select>
                        <span x-show="field.required" class="text-red-500 text-xs">*</span>
                    </div>
                </template>
            </div>

            <!-- Preview Table -->
            <div class="overflow-hidden">
                <h3 class="font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Попередній перегляд</h3>
                <div class="overflow-x-auto max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">#</th>
                                <template x-for="(field, key) in songFields" :key="key">
                                    <th x-show="mappings[key]" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400" x-text="field.label"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(row, index) in preview" :key="index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 py-2 text-gray-500" x-text="index + 1"></td>
                                    <template x-for="(field, key) in songFields" :key="key">
                                        <td x-show="mappings[key]" class="px-3 py-2 text-gray-900 dark:text-white truncate max-w-[150px]"
                                            x-text="row[mappings[key]] || '-'"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <button @click="step = 1"
                    class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Назад
            </button>
            <button @click="startImport()"
                    :disabled="!mappings.title || loading"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                <template x-if="loading">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="loading ? 'Імпортування...' : 'Імпортувати ' + totalRows + ' пісень'"></span>
            </button>
        </div>
    </div>

    <!-- Step 3: Results -->
    <div x-show="step === 3" x-cloak class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <template x-if="importResult.success">
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Імпорт завершено!</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Успішно імпортовано <span class="font-bold text-primary-600" x-text="importResult.imported"></span> пісень.
                    <span x-show="importResult.skipped > 0" class="text-amber-600">
                        Пропущено: <span x-text="importResult.skipped"></span>
                    </span>
                </p>

                <template x-if="importResult.errors && importResult.errors.length > 0">
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-left max-w-md mx-auto">
                        <h4 class="font-medium text-amber-800 dark:text-amber-200 mb-2">Попередження:</h4>
                        <ul class="text-sm text-amber-700 dark:text-amber-300 space-y-1">
                            <template x-for="error in importResult.errors" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                <a href="{{ route('songs.index') }}"
                   class="inline-flex items-center px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Перейти до бібліотеки
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </template>

        <template x-if="!importResult.success && importResult.error">
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Помилка імпорту</h2>
                <p class="text-red-600 dark:text-red-400 mb-6" x-text="importResult.error"></p>
                <button @click="step = 1; file = null; importResult = {}"
                        class="px-6 py-2.5 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Спробувати знову
                </button>
            </div>
        </template>
    </div>

    <!-- Error Toast -->
    <div x-show="errorMessage"
         x-transition
         class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span x-text="errorMessage"></span>
        <button @click="errorMessage = ''" class="ml-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<script>
function songImportWizard() {
    return {
        step: 1,
        file: null,
        dragOver: false,
        loading: false,
        errorMessage: '',

        headers: [],
        preview: [],
        totalRows: 0,

        mappings: {
            title: '',
            artist: '',
            key: '',
            bpm: '',
            link: '',
            tags: '',
            lyrics: '',
            chords: '',
            notes: '',
        },

        songFields: {
            title: { label: 'Назва', required: true },
            artist: { label: 'Виконавець', required: false },
            key: { label: 'Тональність', required: false },
            bpm: { label: 'BPM', required: false },
            link: { label: 'Посилання', required: false },
            tags: { label: 'Теги / Категорія', required: false },
            lyrics: { label: 'Текст', required: false },
            chords: { label: 'Акорди', required: false },
            notes: { label: 'Нотатки', required: false },
        },

        importResult: {},

        handleDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) this.file = files[0];
        },

        handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) this.file = files[0];
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        async uploadAndPreview() {
            if (!this.file) return;
            this.loading = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("songs.import.preview") }}', {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));

                if (data.success) {
                    this.headers = data.headers;
                    this.preview = data.preview;
                    this.totalRows = data.totalRows;

                    for (const [field, header] of Object.entries(data.autoMappings)) {
                        if (this.mappings.hasOwnProperty(field)) {
                            this.mappings[field] = header;
                        }
                    }
                    this.step = 2;
                } else {
                    this.errorMessage = data.error || 'Помилка обробки файлу';
                }
            } catch (error) {
                this.errorMessage = 'Помилка з\'єднання з сервером';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async startImport() {
            if (!this.mappings.title) {
                this.errorMessage = "Поле 'Назва' є обов'язковим для мапінгу";
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('_token', '{{ csrf_token() }}');

            for (const [key, value] of Object.entries(this.mappings)) {
                formData.append(`mappings[${key}]`, value);
            }

            try {
                const response = await fetch('{{ route("songs.import.process") }}', {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                this.importResult = data;
                this.step = 3;
            } catch (error) {
                this.errorMessage = 'Помилка з\'єднання з сервером';
                console.error(error);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection
