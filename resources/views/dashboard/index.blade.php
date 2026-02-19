@extends('layouts.app')

@section('title', __('Головна'))

@section('content')
<!-- Onboarding Reminder for new admins -->
<x-onboarding-reminder />

<div class="space-y-4 lg:space-y-6 page-transition">
    <!-- Mobile Welcome -->
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Привіт, :name!', ['name' => explode(' ', auth()->user()->name)[0]]) }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->locale('uk')->translatedFormat('l, d F') }}</p>
    </div>

    @hasChurchRole
    {{-- Dashboard Builder --}}
    <div x-data="dashboardBuilder()" x-cloak>

        {{-- Edit Mode Toggle (admin only) --}}
        @admin
        <div class="flex items-center justify-end mt-2 lg:mt-0">
            <button
                @click="toggleEditMode()"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl transition-all"
                :class="editMode
                    ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span x-text="editMode ? '{{ __('Редагування...') }}' : '{{ __('Редагувати дашборд') }}'"></span>
            </button>
        </div>
        @endadmin

        {{-- Edit Mode Toolbar --}}
        <div x-show="editMode" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-primary-200 dark:border-primary-800 p-4 mt-2">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Перетягуйте віджети для зміни порядку. Змінюйте ширину або видаляйте непотрібні.') }}
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Add Widget --}}
                    <div class="relative" x-data="{ addOpen: false }">
                        <button
                            @click="addOpen = !addOpen"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Додати віджет') }}
                        </button>
                        <div
                            x-show="addOpen"
                            @click.outside="addOpen = false"
                            x-transition
                            class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-y-auto"
                        >
                            <div class="p-3 space-y-1">
                                <template x-for="widget in getDisabledWidgets()" :key="widget.id">
                                    <button
                                        @click="addWidget(widget.id); addOpen = false"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="widget.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="widget.description"></p>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="getDisabledWidgets().length === 0" class="text-center py-4 text-sm text-gray-400">
                                    {{ __('Всі віджети вже додано') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Save --}}
                    <button
                        @click="saveLayout()"
                        :disabled="saving"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                    >
                        <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ __('Зберегти') }}
                    </button>

                    {{-- Cancel --}}
                    <button
                        @click="cancelEdit()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                        {{ __('Скасувати') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Widget Grid --}}
        <div id="dashboard-grid" class="grid grid-cols-1 md:grid-cols-12 gap-4 lg:gap-6 mt-4 lg:mt-6">
            @php
                $widgetRegistry = config('dashboard_widgets.widgets', []);
            @endphp

            @foreach($layout['widgets'] as $index => $widget)
                @if($widget['enabled'] && isset($widgetRegistry[$widget['id']]))
                    @php $wConfig = $widgetRegistry[$widget['id']]; @endphp
                    <div
                        class="col-span-1 {{ $colClasses[$widget['cols']] ?? 'md:col-span-12' }} widget-item transition-all duration-300"
                        data-widget-id="{{ $widget['id'] }}"
                        :class="editMode ? 'relative' : ''"
                    >
                        {{-- Edit Mode Chrome --}}
                        <div x-show="editMode" class="absolute inset-0 z-10 pointer-events-none rounded-2xl border-2 border-dashed border-primary-300 dark:border-primary-700"></div>
                        <div x-show="editMode" class="absolute -top-3 left-0 right-0 z-20 flex items-center justify-between px-2">
                            {{-- Drag Handle --}}
                            <div class="drag-handle flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-grab active:cursor-grabbing pointer-events-auto">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $wConfig['name'] }}</span>
                            </div>

                            <div class="flex items-center gap-1 pointer-events-auto">
                                {{-- Width Buttons --}}
                                @php $minCols = $wConfig['min_cols'] ?? 4; @endphp
                                @if($minCols <= 4)
                                <button @click="changeWidth('{{ $widget['id'] }}', 4)" class="w-7 h-7 rounded-md text-xs font-bold transition-colors" :class="getWidgetCols('{{ $widget['id'] }}') === 4 ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 hover:bg-gray-50'" title="1/3">
                                    <span class="text-[10px]">1/3</span>
                                </button>
                                @endif
                                @if($minCols <= 6)
                                <button @click="changeWidth('{{ $widget['id'] }}', 6)" class="w-7 h-7 rounded-md text-xs font-bold transition-colors" :class="getWidgetCols('{{ $widget['id'] }}') === 6 ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 hover:bg-gray-50'" title="1/2">
                                    <span class="text-[10px]">1/2</span>
                                </button>
                                @endif
                                @if($minCols <= 8)
                                <button @click="changeWidth('{{ $widget['id'] }}', 8)" class="w-7 h-7 rounded-md text-xs font-bold transition-colors" :class="getWidgetCols('{{ $widget['id'] }}') === 8 ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 hover:bg-gray-50'" title="2/3">
                                    <span class="text-[10px]">2/3</span>
                                </button>
                                @endif
                                <button @click="changeWidth('{{ $widget['id'] }}', 12)" class="w-7 h-7 rounded-md text-xs font-bold transition-colors" :class="getWidgetCols('{{ $widget['id'] }}') === 12 ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 hover:bg-gray-50'" title="{{ __('Повна ширина') }}">
                                    <span class="text-[10px]">Full</span>
                                </button>

                                {{-- Remove Button --}}
                                <button @click="removeWidget('{{ $widget['id'] }}')" class="w-7 h-7 rounded-md bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors ml-1" title="{{ __('Видалити') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Widget Content --}}
                        <div :class="editMode ? 'mt-4 pointer-events-none opacity-80' : ''">
                            @include($wConfig['partial'])
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @else
    <!-- Pending Approval Message for users without church role -->
    <div class="mt-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-6 lg:p-8">
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Очікування підтвердження') }}</h2>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                {{ __('Ваш акаунт створено, але адміністратор ще не надав вам доступ до системи.') }}
                {{ __('Зверніться до адміністратора вашої церкви для отримання доступу.') }}
            </p>
            <a href="{{ route('my-profile') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ __('Мій профіль') }}
            </a>
        </div>
    </div>
    @endhasChurchRole
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function dashboardBuilder() {
    const initialLayout = @json($layout);
    const allWidgets = @json($allWidgets);
    const saveUrl = '{{ route("dashboard.layout.save") }}';
    const csrfToken = '{{ csrf_token() }}';

    // Column class mapping for dynamic updates
    const colClassMap = {
        3: 'md:col-span-3',
        4: 'md:col-span-4',
        6: 'md:col-span-6',
        8: 'md:col-span-8',
        12: 'md:col-span-12',
    };

    return {
        editMode: false,
        widgets: JSON.parse(JSON.stringify(initialLayout.widgets)),
        originalWidgets: null,
        saving: false,
        sortableInstance: null,

        toggleEditMode() {
            this.editMode = !this.editMode;
            if (this.editMode) {
                this.originalWidgets = JSON.parse(JSON.stringify(this.widgets));
                this.$nextTick(() => this.initSortable());
            } else {
                this.destroySortable();
            }
        },

        initSortable() {
            const grid = document.getElementById('dashboard-grid');
            if (!grid) return;

            this.sortableInstance = new Sortable(grid, {
                animation: 200,
                ghostClass: 'opacity-40',
                chosenClass: 'scale-[1.02]',
                dragClass: 'shadow-2xl',
                handle: '.drag-handle',
                draggable: '.widget-item',
                onEnd: (evt) => {
                    // Update order based on DOM position
                    const items = grid.querySelectorAll('.widget-item');
                    items.forEach((item, index) => {
                        const widgetId = item.dataset.widgetId;
                        const widget = this.widgets.find(w => w.id === widgetId);
                        if (widget) {
                            widget.order = index;
                        }
                    });
                }
            });
        },

        destroySortable() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }
        },

        getWidgetCols(widgetId) {
            const widget = this.widgets.find(w => w.id === widgetId);
            return widget ? widget.cols : 12;
        },

        changeWidth(widgetId, cols) {
            const widget = this.widgets.find(w => w.id === widgetId);
            if (!widget) return;
            widget.cols = cols;

            // Update DOM class immediately
            const el = document.querySelector(`[data-widget-id="${widgetId}"]`);
            if (el) {
                // Remove all col-span classes
                Object.values(colClassMap).forEach(cls => el.classList.remove(cls));
                // Add the new one
                el.classList.add(colClassMap[cols]);
            }
        },

        removeWidget(widgetId) {
            const widget = this.widgets.find(w => w.id === widgetId);
            if (widget) {
                widget.enabled = false;
            }
            // Hide the DOM element
            const el = document.querySelector(`[data-widget-id="${widgetId}"]`);
            if (el) {
                el.style.display = 'none';
            }
        },

        getDisabledWidgets() {
            const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
            return this.widgets
                .filter(w => !w.enabled)
                .map(w => ({
                    id: w.id,
                    name: allWidgets[w.id]?.name || w.id,
                    description: allWidgets[w.id]?.description || '',
                    admin_only: allWidgets[w.id]?.admin_only || false,
                }))
                .filter(w => isAdmin || !w.admin_only);
        },

        addWidget(widgetId) {
            const widget = this.widgets.find(w => w.id === widgetId);
            if (widget) {
                widget.enabled = true;
                // Need to save + reload since we need server-rendered content
                this.saveLayout();
            }
        },

        cancelEdit() {
            if (this.originalWidgets) {
                this.widgets = JSON.parse(JSON.stringify(this.originalWidgets));
            }
            this.editMode = false;
            this.destroySortable();
            // Reload to restore original DOM state
            window.location.reload();
        },

        async saveLayout() {
            this.saving = true;
            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ widgets: this.widgets }),
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const err = await response.json().catch(() => ({}));
                    alert('{{ __('Помилка збереження:') }} ' + (err.message || '{{ __('Невідома помилка') }}'));
                }
            } catch (e) {
                console.error('Save failed:', e);
                alert('{{ __('Помилка мережі при збереженні') }}');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
@endsection
