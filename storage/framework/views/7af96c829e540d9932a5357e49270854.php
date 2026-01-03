<?php $__env->startSection('title', 'Головна'); ?>

<?php $__env->startSection('content'); ?>
<!-- Onboarding Reminder for new admins -->
<?php if (isset($component)) { $__componentOriginal8176321cbecc59d39774eb5f1a09a50e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8176321cbecc59d39774eb5f1a09a50e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding-reminder','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('onboarding-reminder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8176321cbecc59d39774eb5f1a09a50e)): ?>
<?php $attributes = $__attributesOriginal8176321cbecc59d39774eb5f1a09a50e; ?>
<?php unset($__attributesOriginal8176321cbecc59d39774eb5f1a09a50e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8176321cbecc59d39774eb5f1a09a50e)): ?>
<?php $component = $__componentOriginal8176321cbecc59d39774eb5f1a09a50e; ?>
<?php unset($__componentOriginal8176321cbecc59d39774eb5f1a09a50e); ?>
<?php endif; ?>

<div class="space-y-4 lg:space-y-6 page-transition">
    <!-- Mobile Welcome -->
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Привіт, <?php echo e(explode(' ', auth()->user()->name)[0]); ?>!</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e(now()->locale('uk')->translatedFormat('l, d F')); ?></p>
    </div>

    <!-- Stats Grid - Informative Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mt-4 lg:mt-6">
        <!-- People Stats -->
        <a href="<?php echo e(route('people.index')); ?>" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:border-blue-200 dark:hover:border-blue-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['people_trend'] > 0): ?>
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +<?php echo e($stats['people_trend']); ?>

                    </span>
                    <?php elseif($stats['people_trend'] < 0): ?>
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        <?php echo e($stats['people_trend']); ?>

                    </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/50 px-2 py-1 rounded-lg">Люди</span>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total_people']); ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">за 3 місяці</p>
            <div class="mt-2 space-y-1.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['age_stats']['children'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-amber-600 dark:text-amber-400">Діти (0-12)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['age_stats']['children']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['age_stats']['teens'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-purple-600 dark:text-purple-400">Підлітки (13-17)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['age_stats']['teens']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['age_stats']['youth'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-blue-600 dark:text-blue-400">Молодь (18-35)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['age_stats']['youth']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['age_stats']['adults'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-600 dark:text-green-400">Дорослі (36-59)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['age_stats']['adults']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['age_stats']['seniors'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600 dark:text-gray-400">Старші (60+)</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['age_stats']['seniors']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </a>

        <!-- Ministries Stats -->
        <a href="<?php echo e(route('ministries.index')); ?>" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:border-green-200 dark:hover:border-green-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-green-50 dark:bg-green-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['volunteers_trend'] > 0): ?>
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +<?php echo e($stats['volunteers_trend']); ?>

                    </span>
                    <?php elseif($stats['volunteers_trend'] < 0): ?>
                    <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 px-2 py-1 rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        <?php echo e($stats['volunteers_trend']); ?>

                    </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/50 px-2 py-1 rounded-lg">Служіння</span>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['volunteers_count']); ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">служителів</p>
            <div class="mt-2 space-y-1.5 max-h-32 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stats['ministries_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ministry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600 dark:text-gray-400 truncate mr-2"><?php echo e($ministry->name); ?></span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300 flex-shrink-0"><?php echo e($ministry->members_count); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </a>

        <!-- Groups Stats -->
        <a href="<?php echo e(route('groups.index')); ?>" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:border-purple-200 dark:hover:border-purple-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-purple-50 dark:bg-purple-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/50 px-2 py-1 rounded-lg">Групи</span>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total_groups'] ?? 0); ?></p>
            <div class="mt-3 space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">Активних</span>
                    </div>
                    <span class="font-semibold text-green-600 dark:text-green-400"><?php echo e($stats['active_groups']); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['paused_groups'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">На паузі</span>
                    </div>
                    <span class="font-semibold text-yellow-600 dark:text-yellow-400"><?php echo e($stats['paused_groups']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['vacation_groups'] > 0): ?>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-gray-500 dark:text-gray-400">У відпустці</span>
                    </div>
                    <span class="font-semibold text-blue-600 dark:text-blue-400"><?php echo e($stats['vacation_groups']); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Учасників</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['total_group_members']); ?></span>
                </div>
            </div>
        </a>

        <!-- Events Stats -->
        <a href="<?php echo e(route('schedule')); ?>" class="stagger-item card-hover bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5 hover:border-amber-200 dark:hover:border-amber-800 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/50 px-2 py-1 rounded-lg"><?php echo e(now()->locale('uk')->translatedFormat('F')); ?></span>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['events_this_month']); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1 mb-2">подій цього місяця</p>
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Проведено</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?php echo e($stats['past_events']); ?></span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Заплановано</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400"><?php echo e($stats['upcoming_events']); ?></span>
                </div>
            </div>
        </a>
    </div>

    

    <!-- Birthdays This Month -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($birthdaysThisMonth->isNotEmpty()): ?>
    <div class="bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/30 dark:to-purple-900/30 rounded-2xl border border-pink-100 dark:border-pink-800 p-4 mt-4 lg:mt-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900 flex items-center justify-center">
                <span class="text-xl">🎂</span>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Дні народження в цьому місяці</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($birthdaysThisMonth->count()); ?> <?php echo e(trans_choice('осіб|особи|осіб', $birthdaysThisMonth->count())); ?></p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $birthdaysThisMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('people.show', $person)); ?>" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 rounded-xl hover:shadow-md transition-shadow">
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <span class="text-xs font-medium text-primary-600 dark:text-primary-400"><?php echo e(mb_substr($person->first_name, 0, 1)); ?></span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($person->full_name); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($person->birth_date->format('d')); ?> <?php echo e($person->birth_date->translatedFormat('M')); ?></p>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Pending Assignments Alert -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($pendingAssignments) > 0): ?>
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl border border-amber-100 dark:border-amber-800 p-4 mt-4 lg:mt-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 dark:text-white">Очікує підтвердження</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">У вас <?php echo e(count($pendingAssignments)); ?> призначень</p>
                <div class="mt-3 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pendingAssignments->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white text-sm truncate"><?php echo e($assignment->event->title); ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($assignment->event->date->format('d.m')); ?> &bull; <?php echo e($assignment->position->name); ?></p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form method="POST" action="<?php echo e(route('assignments.confirm', $assignment)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-11 h-11 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-xl flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 active:bg-green-300 dark:active:bg-green-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('assignments.decline', $assignment)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-11 h-11 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400 rounded-xl flex items-center justify-center hover:bg-red-200 dark:hover:bg-red-800 active:bg-red-300 dark:active:bg-red-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Events & Attendance Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mt-4 lg:mt-6">
        <!-- Upcoming Events -->
        <div class="md:col-span-2 lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Найближчі події</h2>
                <a href="<?php echo e(route('schedule')); ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $upcomingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('events.show', $event)); ?>" class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: <?php echo e($event->ministry?->color ?? '#3b82f6'); ?>20;">
                        <svg class="w-6 h-6" style="color: <?php echo e($event->ministry?->color ?? '#3b82f6'); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-900 dark:text-white truncate"><?php echo e($event->title); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->isFullyStaffed()): ?>
                            <span class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></span>
                            <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($event->date->format('d.m')); ?> &bull; <?php echo e($event->time->format('H:i')); ?></p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($event->filled_positions_count); ?>/<?php echo e($event->total_positions_count); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">позицій</p>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Немає запланованих подій</p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Attendance Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Відвідуваність богослужінь</h2>
            <div class="h-48">
                <canvas id="attendanceChart"></canvas>
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary-500"></span>
                    <span>За останні 4 тижні</span>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
    <!-- Analytics Charts Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-4 lg:mt-6">
        <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Аналітика
            </h2>
            <div class="flex rounded-xl bg-gray-100 dark:bg-gray-700 p-1 overflow-x-auto">
                <button type="button" data-chart="growth" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm whitespace-nowrap">
                    Зростання
                </button>
                <button type="button" data-chart="financial" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                    Фінанси
                </button>
                <button type="button" data-chart="attendance" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                    Відвідуваність
                </button>
                <button type="button" data-chart="ministries" class="chart-tab px-2 sm:px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white whitespace-nowrap">
                    Служіння
                </button>
            </div>
        </div>
        <div class="p-4 lg:p-6">
            <div class="h-72 relative">
                <div id="chartLoader" class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <canvas id="analyticsChart"></canvas>
            </div>
            <div id="chartLegend" class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <!-- Legend will be dynamically updated -->
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards (for admins) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['income_this_month']) || isset($stats['expenses_this_month'])): ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mt-4 lg:mt-6">
        <?php if(isset($stats['income_this_month'])): ?>
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl border border-green-100 dark:border-green-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stats['income_this_month'], 0, ',', ' ')); ?> ₴</p>
            <p class="text-xs lg:text-sm text-green-600 dark:text-green-400 mt-0.5">Доходи за місяць</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['expenses_this_month'])): ?>
        <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/30 rounded-2xl border border-red-100 dark:border-red-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stats['expenses_this_month'], 0, ',', ' ')); ?> ₴</p>
            <p class="text-xs lg:text-sm text-red-600 dark:text-red-400 mt-0.5">Витрати за місяць</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(isset($stats['income_this_month']) && isset($stats['expenses_this_month'])): ?>
        <?php $balance = $stats['income_this_month'] - $stats['expenses_this_month']; ?>
        <div class="bg-gradient-to-br <?php echo e($balance >= 0 ? 'from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 border-blue-100 dark:border-blue-800' : 'from-amber-50 to-orange-50 dark:from-amber-900/30 dark:to-orange-900/30 border-amber-100 dark:border-amber-800'); ?> rounded-2xl border p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl <?php echo e($balance >= 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-amber-100 dark:bg-amber-900'); ?> flex items-center justify-center">
                    <svg class="w-5 h-5 <?php echo e($balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($balance >= 0 ? '+' : ''); ?><?php echo e(number_format($balance, 0, ',', ' ')); ?> ₴</p>
            <p class="text-xs lg:text-sm <?php echo e($balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'); ?> mt-0.5">Баланс за місяць</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($growthData) > 0): ?>
        <?php $lastMonth = end($growthData); ?>
        <div class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/30 dark:to-violet-900/30 rounded-2xl border border-purple-100 dark:border-purple-800 p-4 lg:p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">+<?php echo e($lastMonth['count']); ?></p>
            <p class="text-xs lg:text-sm text-purple-600 dark:text-purple-400 mt-0.5">Нових за місяць</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Admin Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mt-4 lg:mt-6">
        <!-- Ministry Budgets -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($ministryBudgets) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Бюджети служінь</h2>
                <a href="<?php echo e(route('finances.index')); ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Звіт</a>
            </div>
            <div class="p-4 lg:p-5 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ministryBudgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $budget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($budget['icon']); ?> <?php echo e($budget['name']); ?></span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <?php echo e(number_format($budget['spent'], 0, ',', ' ')); ?> / <?php echo e(number_format($budget['budget'], 0, ',', ' ')); ?> ₴
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                            <?php echo e($budget['percentage'] > 90 ? 'bg-red-500' : ($budget['percentage'] > 70 ? 'bg-amber-500' : 'bg-green-500')); ?>"
                             style="width: <?php echo e(min(100, $budget['percentage'])); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Expenses This Month -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['expenses_this_month'])): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 lg:p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900 dark:text-white">Витрати за місяць</h2>
                <a href="<?php echo e(route('finances.expenses.index')); ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium">Всі</a>
            </div>

            <!-- Total -->
            <div class="text-center pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stats['expenses_this_month'], 0, ',', ' ')); ?> ₴</p>
            </div>

            <!-- Breakdown by category -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expensesByCategory->isNotEmpty()): ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $expensesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $percentage = $stats['expenses_this_month'] > 0 ? ($category['amount'] / $stats['expenses_this_month']) * 100 : 0;
                ?>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-700 dark:text-gray-300"><?php echo e($category['name']); ?></span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e(number_format($category['amount'], 0, ',', ' ')); ?> ₴</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: <?php echo e($percentage); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Немає витрат за цей місяць</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- People Needing Attention -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($needAttention) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 lg:px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Потребують уваги</h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Не відвідували 3+ тижні</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $needAttention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between p-4">
                    <a href="<?php echo e(route('people.show', $person)); ?>" class="flex items-center gap-3 hover:opacity-80">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300"><?php echo e(mb_substr($person->first_name, 0, 1)); ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($person->full_name); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->phone): ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($person->phone); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->phone): ?>
                    <a href="tel:<?php echo e($person->phone); ?>" class="w-9 h-9 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400 rounded-lg flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Small attendance chart
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(collect($attendanceData)->pluck('date'), 15, 512) ?>,
                datasets: [{
                    label: 'Відвідуваність',
                    data: <?php echo json_encode(collect($attendanceData)->pluck('count'), 15, 512) ?>,
                    borderColor: '<?php echo e($currentChurch->primary_color ?? "#3b82f6"); ?>',
                    backgroundColor: '<?php echo e($currentChurch->primary_color ?? "#3b82f6"); ?>20',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '<?php echo e($currentChurch->primary_color ?? "#3b82f6"); ?>',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, stepSize: 10 }
                    }
                }
            }
        });
    }

    // Analytics Charts (Admin section)
    const analyticsCtx = document.getElementById('analyticsChart');
    if (!analyticsCtx) return;

    let analyticsChart = null;
    const chartLoader = document.getElementById('chartLoader');
    const chartLegend = document.getElementById('chartLegend');
    const chartTabs = document.querySelectorAll('.chart-tab');
    const primaryColor = '<?php echo e($currentChurch->primary_color ?? "#3b82f6"); ?>';

    const chartColors = {
        primary: primaryColor,
        success: '#22c55e',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6',
        purple: '#8b5cf6',
        pink: '#ec4899',
        teal: '#14b8a6',
        orange: '#f97316',
        cyan: '#06b6d4',
    };

    const colorPalette = [
        chartColors.primary, chartColors.success, chartColors.danger,
        chartColors.warning, chartColors.purple, chartColors.pink,
        chartColors.teal, chartColors.orange, chartColors.cyan, chartColors.info
    ];

    function getChartOptions(type) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#374151' : '#f3f4f6';

        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#ffffff' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#6b7280',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: type === 'financial' ? {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('uk-UA').format(context.raw) + ' ₴';
                        }
                    } : {}
                }
            },
            scales: type === 'ministries' ? {} : {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        callback: type === 'financial' ? function(value) {
                            return new Intl.NumberFormat('uk-UA', { notation: 'compact' }).format(value) + ' ₴';
                        } : undefined
                    }
                }
            }
        };
    }

    async function loadChart(type) {
        chartLoader.classList.remove('hidden');

        try {
            const response = await fetch(`<?php echo e(route('dashboard.charts')); ?>?type=${type}`);
            const data = await response.json();

            if (analyticsChart) {
                analyticsChart.destroy();
            }

            chartLoader.classList.add('hidden');

            const config = buildChartConfig(type, data);
            analyticsChart = new Chart(analyticsCtx, config);
            updateLegend(type, data);

        } catch (error) {
            console.error('Error loading chart:', error);
            chartLoader.classList.add('hidden');
        }
    }

    function buildChartConfig(type, data) {
        const isDark = document.documentElement.classList.contains('dark');

        switch(type) {
            case 'growth':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Загальна кількість',
                            data: data.map(d => d.value),
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.primary,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }, {
                            label: 'Нові',
                            data: data.map(d => d.new),
                            borderColor: chartColors.success,
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointBackgroundColor: chartColors.success,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                        }]
                    },
                    options: getChartOptions('growth')
                };

            case 'financial':
                return {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Доходи',
                            data: data.map(d => d.income),
                            backgroundColor: chartColors.success + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }, {
                            label: 'Витрати',
                            data: data.map(d => d.expenses),
                            backgroundColor: chartColors.danger + 'cc',
                            borderRadius: 6,
                            borderSkipped: false,
                        }, {
                            label: 'Залишок',
                            data: data.map(d => d.balance),
                            type: 'line',
                            borderColor: chartColors.info,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            yAxisID: 'y',
                        }]
                    },
                    options: getChartOptions('financial')
                };

            case 'attendance':
                return {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: 'Середня відвідуваність',
                            data: data.map(d => d.value),
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: chartColors.info,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                        }]
                    },
                    options: getChartOptions('attendance')
                };

            case 'ministries':
                return {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            data: data.map(d => d.value),
                            backgroundColor: data.map((d, i) => d.color || colorPalette[i % colorPalette.length]),
                            borderWidth: 0,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        ...getChartOptions('ministries'),
                        cutout: '60%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' учасників';
                                    }
                                }
                            }
                        }
                    }
                };

            default:
                return { type: 'line', data: { labels: [], datasets: [] }, options: {} };
        }
    }

    function updateLegend(type, data) {
        let html = '';

        switch(type) {
            case 'growth':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.primary}"></span>
                        <span>Загальна кількість</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Нові за місяць</span>
                    </div>
                `;
                break;

            case 'financial':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.success}"></span>
                        <span>Доходи</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.danger}"></span>
                        <span>Витрати</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Залишок</span>
                    </div>
                `;
                break;

            case 'attendance':
                html = `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${chartColors.info}"></span>
                        <span>Середня відвідуваність за 12 місяців</span>
                    </div>
                `;
                break;

            case 'ministries':
                html = data.slice(0, 5).map((d, i) => `
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: ${d.color || colorPalette[i % colorPalette.length]}"></span>
                        <span>${d.label}: ${d.value}</span>
                    </div>
                `).join('');
                if (data.length > 5) {
                    html += `<span class="text-gray-400">+${data.length - 5} більше</span>`;
                }
                break;
        }

        chartLegend.innerHTML = html;
    }

    // Tab click handlers
    chartTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            chartTabs.forEach(t => {
                t.classList.remove('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
                t.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            this.classList.add('bg-white', 'dark:bg-gray-600', 'text-gray-900', 'dark:text-white', 'shadow-sm');
            this.classList.remove('text-gray-600', 'dark:text-gray-400');

            // Load chart
            loadChart(this.dataset.chart);
        });
    });

    // Load initial chart
    loadChart('growth');
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>