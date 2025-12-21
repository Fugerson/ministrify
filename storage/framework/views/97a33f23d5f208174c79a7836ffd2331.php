<?php $__env->startSection('title', 'Розклад'); ?>

<?php $__env->startSection('actions'); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
<a href="<?php echo e(route('events.create')); ?>"
   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Подія
</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
    $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;
?>

<div class="bg-white rounded-lg shadow">
    <!-- Month navigation -->
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <a href="<?php echo e(route('schedule', ['year' => $prevYear, 'month' => $prevMonth])); ?>"
           class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        <h2 class="text-xl font-semibold text-gray-900"><?php echo e($months[$month - 1]); ?> <?php echo e($year); ?></h2>

        <a href="<?php echo e(route('schedule', ['year' => $nextYear, 'month' => $nextMonth])); ?>"
           class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Events by date -->
    <div class="divide-y">
        <?php
            $currentDate = $startOfMonth->copy();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php while($currentDate <= $endOfMonth): ?>
            <?php
                $dateKey = $currentDate->format('Y-m-d');
                $dayEvents = $events->get($dateKey, collect());
                $isToday = $currentDate->isToday();
                $isPast = $currentDate->isPast() && !$isToday;
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dayEvents->count() > 0 || $isToday): ?>
                <div class="p-4 <?php echo e($isToday ? 'bg-primary-50' : ''); ?> <?php echo e($isPast ? 'opacity-60' : ''); ?>">
                    <div class="flex items-baseline mb-3">
                        <span class="text-lg font-semibold text-gray-900 <?php echo e($isToday ? 'text-primary-600' : ''); ?>">
                            <?php echo e($currentDate->format('d')); ?>

                        </span>
                        <span class="ml-2 text-sm text-gray-500">
                            <?php echo e(['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П\'ятниця', 'Субота'][$currentDate->dayOfWeek]); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isToday): ?>
                                <span class="ml-1 text-primary-600 font-medium">(Сьогодні)</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>

                    <?php if($dayEvents->count() > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('events.show', $event)); ?>"
                                   class="block p-3 border rounded-lg hover:bg-white hover:shadow-sm transition-all">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-xl"><?php echo e($event->ministry->icon); ?></span>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900"><?php echo e($event->title); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo e($event->ministry->name); ?> &bull; <?php echo e($event->time->format('H:i')); ?></p>
                                            </div>
                                        </div>
                                        <div class="text-sm">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->isFullyStaffed()): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">
                                                    &#9989; <?php echo e($event->confirmed_assignments_count); ?>/<?php echo e($event->total_positions_count); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">
                                                    &#9888; <?php echo e($event->filled_positions_count); ?>/<?php echo e($event->total_positions_count); ?>

                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Немає подій</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php $currentDate->addDay(); ?>
        <?php endwhile; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($events->isEmpty()): ?>
            <div class="p-12 text-center text-gray-500">
                <p>Немає подій у цьому місяці</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('leader')): ?>
                <a href="<?php echo e(route('events.create')); ?>" class="mt-2 inline-block text-primary-600 hover:text-primary-500">
                    Створити подію
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/schedule/calendar.blade.php ENDPATH**/ ?>