<?php $__env->startSection('title', 'Розклад'); ?>

<?php $__env->startSection('actions'); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
<a href="<?php echo e(route('events.create')); ?>"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Нова подія
</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginal20d4401d9b60c1786c7c83b5130cef6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-help','data' => ['page' => 'schedule']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-help'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['page' => 'schedule']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b)): ?>
<?php $attributes = $__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b; ?>
<?php unset($__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20d4401d9b60c1786c7c83b5130cef6b)): ?>
<?php $component = $__componentOriginal20d4401d9b60c1786c7c83b5130cef6b; ?>
<?php unset($__componentOriginal20d4401d9b60c1786c7c83b5130cef6b); ?>
<?php endif; ?>

<?php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
    $daysShort = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
    $daysFull = ['Понеділок', 'Вівторок', 'Середа', 'Четвер', "П'ятниця", 'Субота', 'Неділя'];

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;

    $prevWeek = $currentWeek ? ($currentWeek == 1 ? 52 : $currentWeek - 1) : null;
    $prevWeekYear = $currentWeek == 1 ? $year - 1 : $year;
    $nextWeek = $currentWeek ? ($currentWeek == 52 ? 1 : $currentWeek + 1) : null;
    $nextWeekYear = $currentWeek == 52 ? $year + 1 : $year;
?>

<div class="space-y-4">
    <!-- View Toggle & Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- View Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-xl p-1">
                <a href="<?php echo e(route('schedule', ['view' => 'week', 'year' => $year, 'week' => now()->weekOfYear])); ?>"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?php echo e($view === 'week' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'); ?>">
                    Тиждень
                </a>
                <a href="<?php echo e(route('schedule', ['view' => 'month', 'year' => $year, 'month' => $month])); ?>"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?php echo e($view === 'month' ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'); ?>">
                    Місяць
                </a>
            </div>

            <!-- Date Navigation -->
            <div class="flex items-center justify-between sm:justify-center gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'week'): ?>
                    <a href="<?php echo e(route('schedule', ['view' => 'week', 'year' => $prevWeekYear, 'week' => $prevWeek])); ?>"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[200px] text-center">
                        <?php echo e($startDate->format('d.m')); ?> - <?php echo e($endDate->format('d.m.Y')); ?>

                    </h2>
                    <a href="<?php echo e(route('schedule', ['view' => 'week', 'year' => $nextWeekYear, 'week' => $nextWeek])); ?>"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('schedule', ['view' => 'month', 'year' => $prevYear, 'month' => $prevMonth])); ?>"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white min-w-[180px] text-center">
                        <?php echo e($months[$month - 1]); ?> <?php echo e($year); ?>

                    </h2>
                    <a href="<?php echo e(route('schedule', ['view' => 'month', 'year' => $nextYear, 'month' => $nextMonth])); ?>"
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('schedule', ['view' => $view])); ?>"
                   class="px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    Сьогодні
                </a>

                <!-- Google Calendar Sync Button -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
                <?php
                    $hasGoogleSync = $church->getSetting('google_calendar_url') && $church->getSetting('google_calendar_ministry_id');
                    $lastSync = $church->getSetting('google_calendar_last_sync');
                    $syncMinistry = $hasGoogleSync ? \App\Models\Ministry::find($church->getSetting('google_calendar_ministry_id')) : null;
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasGoogleSync): ?>
                    <form action="<?php echo e(route('calendar.sync')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg transition-all shadow-sm hover:shadow"
                                title="Синхронізація: <?php echo e($syncMinistry?->name); ?><?php echo e($lastSync ? ' | Остання: ' . \Carbon\Carbon::parse($lastSync)->diffForHumans() : ''); ?>">
                            <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zM9.857 17.143H6.857v-3h3v3zm0-4.286H6.857V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3z"/>
                            </svg>
                            Синхронізувати
                        </button>
                    </form>
                <?php else: ?>
                    <button onclick="showGoogleSyncModal()"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zM9.857 17.143H6.857v-3h3v3zm0-4.286H6.857V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3z"/>
                        </svg>
                        Google Calendar
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Export/Import Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                            class="p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>

                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 z-50">
                        <div class="py-1">
                            <a href="<?php echo e(route('calendar.export')); ?>"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Експорт (.ics)
                            </a>
                            <a href="<?php echo e(route('calendar.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')])); ?>"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Експорт поточного періоду
                            </a>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
                            <a href="<?php echo e(route('calendar.import')); ?>"
                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Імпорт з Google Calendar
                            </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                            <button onclick="showSubscriptionModal()"
                                    class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Підписатися на календар
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'week'): ?>
        <!-- Week View -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700">
                <?php $dayDate = $startDate->copy(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 7; $i++): ?>
                    <?php
                        $isToday = $dayDate->isToday();
                    ?>
                    <div class="p-3 text-center border-r border-gray-100 dark:border-gray-700 last:border-r-0 <?php echo e($isToday ? 'bg-primary-50 dark:bg-primary-900/30' : ''); ?>">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e($daysShort[$i]); ?></p>
                        <p class="text-lg font-semibold mt-1 <?php echo e($isToday ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white'); ?>">
                            <?php echo e($dayDate->format('d')); ?>

                        </p>
                    </div>
                    <?php $dayDate->addDay(); ?>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Events Grid -->
            <div class="grid grid-cols-7 min-h-[400px]">
                <?php $dayDate = $startDate->copy(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 7; $i++): ?>
                    <?php
                        $dateKey = $dayDate->format('Y-m-d');
                        $dayEvents = $events->get($dateKey, collect());
                        $isToday = $dayDate->isToday();
                        $isPast = $dayDate->isPast() && !$isToday;
                    ?>
                    <div class="border-r border-gray-100 dark:border-gray-700 last:border-r-0 p-2 <?php echo e($isToday ? 'bg-primary-50/50 dark:bg-primary-900/20' : ''); ?> <?php echo e($isPast ? 'opacity-60' : ''); ?>">
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->type === 'meeting'): ?>
                                    <a href="<?php echo e(route('meetings.show', [$item->ministry_id, $item->id])); ?>"
                                       class="block p-2 rounded-lg text-xs transition-all hover:shadow-md"
                                       style="background-color: <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>20; border-left: 3px solid <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>;">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            <?php echo e($item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '--:--'); ?>

                                        </p>
                                        <p class="text-gray-600 dark:text-gray-300 truncate"><?php echo e($item->title); ?></p>
                                        <div class="flex items-center mt-1">
                                            <span class="w-2 h-2 rounded-full bg-purple-500" title="Зустріч"></span>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('events.show', $item->original)); ?>"
                                       class="block p-2 rounded-lg text-xs transition-all hover:shadow-md"
                                       style="background-color: <?php echo e($item->ministry->color ?? '#3b82f6'); ?>20; border-left: 3px solid <?php echo e($item->ministry->color ?? '#3b82f6'); ?>;">
                                        <p class="font-medium text-gray-900 dark:text-white truncate"><?php echo e($item->time ? $item->time->format('H:i') : '--:--'); ?></p>
                                        <p class="text-gray-600 dark:text-gray-300 truncate"><?php echo e($item->title); ?></p>
                                        <div class="flex items-center mt-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->original->isFullyStaffed()): ?>
                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            <?php else: ?>
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php $dayDate->addDay(); ?>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Month View - Calendar Grid -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Days Header -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daysShort; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3 text-center">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase"><?php echo e($day); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Calendar Grid -->
            <?php
                $firstDayOfMonth = $startDate->copy()->startOfMonth();
                $startDayOfWeek = $firstDayOfMonth->dayOfWeekIso - 1;
                $calendarStart = $firstDayOfMonth->copy()->subDays($startDayOfWeek);
                $calendarDate = $calendarStart->copy();
            ?>

            <div class="grid grid-cols-7">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($week = 0; $week < 6; $week++): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($day = 0; $day < 7; $day++): ?>
                        <?php
                            $dateKey = $calendarDate->format('Y-m-d');
                            $dayEvents = $events->get($dateKey, collect());
                            $isToday = $calendarDate->isToday();
                            $isCurrentMonth = $calendarDate->month == $month;
                            $isPast = $calendarDate->isPast() && !$isToday;
                        ?>
                        <div class="min-h-[100px] lg:min-h-[120px] border-b border-r border-gray-100 dark:border-gray-700 p-1.5 lg:p-2 <?php echo e(!$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-800/50' : ''); ?> <?php echo e($isToday ? 'bg-primary-50 dark:bg-primary-900/20' : ''); ?>">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium <?php echo e($isToday ? 'w-7 h-7 flex items-center justify-center rounded-full bg-primary-600 text-white' : ($isCurrentMonth ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500')); ?>">
                                    <?php echo e($calendarDate->format('j')); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dayEvents->count() > 2): ?>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+<?php echo e($dayEvents->count() - 2); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="space-y-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayEvents->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->type === 'meeting'): ?>
                                        <a href="<?php echo e(route('meetings.show', [$item->ministry_id, $item->id])); ?>"
                                           class="block px-1.5 py-0.5 rounded text-xs truncate transition-colors hover:opacity-80 <?php echo e($isPast && !$isToday ? 'opacity-60' : ''); ?>"
                                           style="background-color: <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>20; color: <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>;">
                                            <span class="hidden lg:inline"><?php echo e($item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : ''); ?></span>
                                            📋 <?php echo e(Str::limit($item->title, 12)); ?>

                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('events.show', $item->original)); ?>"
                                           class="block px-1.5 py-0.5 rounded text-xs truncate transition-colors hover:opacity-80 <?php echo e($isPast && !$isToday ? 'opacity-60' : ''); ?>"
                                           style="background-color: <?php echo e($item->ministry->color ?? '#3b82f6'); ?>20; color: <?php echo e($item->ministry->color ?? '#3b82f6'); ?>;">
                                            <span class="hidden lg:inline"><?php echo e($item->time ? $item->time->format('H:i') : ''); ?></span>
                                            <?php echo e(Str::limit($item->title, 15)); ?>

                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php $calendarDate->addDay(); ?>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($calendarDate->month > $month && $calendarDate->year >= $year): ?>
                        <?php break; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Events List (below calendar) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Події місяця</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                    $currentDate = $startDate->copy();
                    $endOfMonth = $startDate->copy()->endOfMonth();
                ?>

                <?php while($currentDate <= $endOfMonth): ?>
                    <?php
                        $dateKey = $currentDate->format('Y-m-d');
                        $dayEvents = $events->get($dateKey, collect());
                        $isToday = $currentDate->isToday();
                        $isPast = $currentDate->isPast() && !$isToday;
                        $dayOfWeek = ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'][$currentDate->dayOfWeek];
                    ?>

                    <?php if($dayEvents->count() > 0): ?>
                        <div class="p-4 <?php echo e($isToday ? 'bg-primary-50 dark:bg-primary-900/20' : ''); ?> <?php echo e($isPast ? 'opacity-60' : ''); ?>">
                            <div class="flex items-baseline mb-3">
                                <span class="text-lg font-bold <?php echo e($isToday ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white'); ?>">
                                    <?php echo e($currentDate->format('d')); ?>

                                </span>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e($dayOfWeek); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isToday): ?>
                                        <span class="ml-1 text-primary-600 dark:text-primary-400 font-medium">(Сьогодні)</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </div>

                            <div class="space-y-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->type === 'meeting'): ?>
                                        <a href="<?php echo e(route('meetings.show', [$item->ministry_id, $item->id])); ?>"
                                           class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>20;">
                                                    <svg class="w-5 h-5" style="color: <?php echo e($item->ministry->color ?? '#8b5cf6'); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo e($item->title); ?></p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($item->ministry->name ?? ''); ?> &bull; <?php echo e($item->time ? \Carbon\Carbon::parse($item->time)->format('H:i') : '-'); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                                    Зустріч
                                                </span>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('events.show', $item->original)); ?>"
                                           class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: <?php echo e($item->ministry->color ?? '#3b82f6'); ?>20;">
                                                    <svg class="w-5 h-5" style="color: <?php echo e($item->ministry->color ?? '#3b82f6'); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo e($item->title); ?></p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($item->ministry->name ?? 'Без служіння'); ?> &bull; <?php echo e($item->time ? $item->time->format('H:i') : '-'); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->original->isFullyStaffed()): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                                                        <?php echo e($item->original->confirmed_assignments_count); ?>/<?php echo e($item->original->total_positions_count); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                                                        <?php echo e($item->original->filled_positions_count); ?>/<?php echo e($item->original->total_positions_count); ?>

                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php $currentDate->addDay(); ?>
                <?php endwhile; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($events->isEmpty()): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Немає подій у цьому місяці</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
                        <a href="<?php echo e(route('events.create')); ?>" class="mt-3 inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Створити подію
                        </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<!-- Subscription Modal -->
<div id="subscriptionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="hideSubscriptionModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Підписка на календар</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Додайте календар до Google, Apple або Outlook</p>
            </div>
            <div class="px-6 py-4 space-y-4">
                <!-- iCal Feed URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        URL для підписки (iCal)
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="icalUrl" readonly
                               value="<?php echo e($church->calendar_feed_url); ?>"
                               class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white">
                        <button onclick="copyIcalUrl()" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                            Копіювати
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Як підписатися?</h4>
                    <div class="text-sm text-blue-700 dark:text-blue-400 space-y-2">
                        <details class="cursor-pointer">
                            <summary class="font-medium">Google Calendar</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>Відкрийте Google Calendar</li>
                                <li>Натисніть "+" біля "Інші календарі"</li>
                                <li>Виберіть "З URL-адреси"</li>
                                <li>Вставте скопійовану URL-адресу</li>
                                <li>Натисніть "Додати календар"</li>
                            </ol>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="font-medium">Apple Calendar (iPhone/Mac)</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>Відкрийте "Налаштування" > "Календар"</li>
                                <li>Виберіть "Облікові записи" > "Додати обліковий запис"</li>
                                <li>Виберіть "Інше" > "Додати підписку на календар"</li>
                                <li>Вставте URL-адресу</li>
                            </ol>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="font-medium">Outlook</summary>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>Відкрийте Outlook Calendar</li>
                                <li>Виберіть "Додати календар" > "З інтернету"</li>
                                <li>Вставте URL-адресу</li>
                            </ol>
                        </details>
                    </div>
                </div>

                <!-- Direct Add Links -->
                <div class="flex flex-wrap gap-2">
                    <a href="https://calendar.google.com/calendar/r?cid=<?php echo e(urlencode($church->calendar_feed_url)); ?>" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.5 3h-15A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-15A1.5 1.5 0 0019.5 3z"/>
                        </svg>
                        Google Calendar
                    </a>
                </div>

                <!-- API Info -->
                <details class="text-sm text-gray-500 dark:text-gray-400">
                    <summary class="cursor-pointer font-medium">API для розробників</summary>
                    <div class="mt-2 space-y-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <p><strong>JSON API:</strong></p>
                        <code class="block text-xs bg-gray-100 dark:bg-gray-600 p-2 rounded overflow-x-auto">
                            GET <?php echo e(url('/api/calendar/events')); ?>?token=<?php echo e($church->getCalendarToken()); ?>

                        </code>
                        <p class="text-xs mt-2">Параметри: <code>start</code>, <code>end</code>, <code>ministry</code></p>
                    </div>
                </details>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <button onclick="hideSubscriptionModal()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                    Закрити
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Google Sync Modal -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
<div id="googleSyncModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="hideGoogleSyncModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zM9.857 17.143H6.857v-3h3v3zm0-4.286H6.857V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3zm4.286 4.286h-3v-3h3v3zm0-4.286h-3V9.857h3v3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Google Calendar</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Налаштування синхронізації</p>
                    </div>
                </div>
                <button onclick="hideGoogleSyncModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('calendar.import.url')); ?>" method="POST" class="p-6 space-y-5">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="save_settings" value="1">

                <!-- Instructions -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-700 dark:text-blue-300">
                            <p class="font-medium mb-1">Як отримати посилання:</p>
                            <ol class="list-decimal list-inside space-y-0.5 text-blue-600 dark:text-blue-400">
                                <li>Відкрийте Google Calendar</li>
                                <li>Налаштування календаря → Інтеграція</li>
                                <li>Скопіюйте "Публічна адреса iCal"</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar URL -->
                <div>
                    <label for="google_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Посилання Google Calendar (iCal)
                    </label>
                    <input type="url" name="calendar_url" id="google_url" required
                           placeholder="https://calendar.google.com/calendar/ical/..."
                           value="<?php echo e($church->getSetting('google_calendar_url')); ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Ministry -->
                <div>
                    <label for="google_ministry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Служіння для імпортованих подій
                    </label>
                    <select name="ministry_id" id="google_ministry" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Виберіть служіння...</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ministries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ministry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ministry->id); ?>" <?php echo e($church->getSetting('google_calendar_ministry_id') == $ministry->id ? 'selected' : ''); ?>>
                                <?php echo e($ministry->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">З дати</label>
                        <input type="date" name="start_date" value="<?php echo e(now()->subMonth()->format('Y-m-d')); ?>"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">До дати</label>
                        <input type="date" name="end_date" value="<?php echo e(now()->addMonths(3)->format('Y-m-d')); ?>"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="hideGoogleSyncModal()"
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        Скасувати
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-xl transition-all shadow-sm hover:shadow inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Синхронізувати
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<script>
function showSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.remove('hidden');
}

function hideSubscriptionModal() {
    document.getElementById('subscriptionModal').classList.add('hidden');
}

function showGoogleSyncModal() {
    document.getElementById('googleSyncModal').classList.remove('hidden');
}

function hideGoogleSyncModal() {
    document.getElementById('googleSyncModal').classList.add('hidden');
}

function copyIcalUrl() {
    const input = document.getElementById('icalUrl');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Скопійовано!';
        setTimeout(() => btn.textContent = originalText, 2000);
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideSubscriptionModal();
        hideGoogleSyncModal();
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/schedule/calendar.blade.php ENDPATH**/ ?>