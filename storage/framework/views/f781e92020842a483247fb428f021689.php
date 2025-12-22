<?php $__env->startSection('title', $church->name); ?>

<?php $__env->startSection('actions'); ?>
<div class="flex items-center gap-3">
    <form method="POST" action="<?php echo e(route('system.churches.switch', $church)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
            Увійти в церкву
        </button>
    </form>
    <a href="<?php echo e(route('system.churches.index')); ?>" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white font-medium rounded-lg">
        ← Назад
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Church Info -->
    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <div class="flex items-start gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($church->logo): ?>
            <img src="/storage/<?php echo e($church->logo); ?>" class="w-24 h-24 rounded-xl object-cover">
            <?php else: ?>
            <div class="w-24 h-24 rounded-xl bg-blue-600/20 flex items-center justify-center">
                <span class="text-4xl">⛪</span>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-white"><?php echo e($church->name); ?></h2>
                <p class="text-gray-400 mt-1"><?php echo e($church->city); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($church->address): ?>
                <p class="text-gray-500 text-sm mt-1"><?php echo e($church->address); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="flex items-center gap-4 mt-4">
                    <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-sm">ID: <?php echo e($church->id); ?></span>
                    <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-sm">Slug: <?php echo e($church->slug); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($church->public_site_enabled): ?>
                    <span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-sm">Публічний сайт</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->users_count); ?></p>
            <p class="text-gray-400 text-sm">Користувачів</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->people_count); ?></p>
            <p class="text-gray-400 text-sm">Людей</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->ministries_count); ?></p>
            <p class="text-gray-400 text-sm">Служінь</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->groups_count); ?></p>
            <p class="text-gray-400 text-sm">Груп</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->events_count); ?></p>
            <p class="text-gray-400 text-sm">Подій</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($church->boards_count); ?></p>
            <p class="text-gray-400 text-sm">Дошок</p>
        </div>
    </div>

    <!-- Finances -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6">
            <p class="text-green-100 text-sm">Надходження</p>
            <p class="text-3xl font-bold text-white mt-2"><?php echo e(number_format($finances['income'], 0, ',', ' ')); ?> ₴</p>
        </div>
        <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-2xl p-6">
            <p class="text-red-100 text-sm">Витрати</p>
            <p class="text-3xl font-bold text-white mt-2"><?php echo e(number_format($finances['expenses'], 0, ',', ' ')); ?> ₴</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h2 class="font-semibold text-white">Користувачі церкви</h2>
            </div>
            <div class="divide-y divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center px-6 py-3">
                    <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-white"><?php echo e(mb_substr($user->name, 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-white truncate"><?php echo e($user->name); ?></p>
                        <p class="text-sm text-gray-400 truncate"><?php echo e($user->email); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full
                            <?php echo e($user->role === 'admin' ? 'bg-red-600/20 text-red-400' : ''); ?>

                            <?php echo e($user->role === 'leader' ? 'bg-blue-600/20 text-blue-400' : ''); ?>

                            <?php echo e($user->role === 'volunteer' ? 'bg-green-600/20 text-green-400' : ''); ?>

                        "><?php echo e($user->role); ?></span>
                        <a href="<?php echo e(route('system.users.edit', $user)); ?>" class="p-1 text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-6 py-4 text-gray-400">Немає користувачів</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700">
                <h2 class="font-semibold text-white">Останні події</h2>
            </div>
            <div class="divide-y divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-6 py-3">
                    <p class="font-medium text-white"><?php echo e($event->title); ?></p>
                    <p class="text-sm text-gray-400"><?php echo e($event->start_date->format('d.m.Y H:i')); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-6 py-4 text-gray-400">Немає подій</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="font-semibold text-white">Останні дії</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Модель</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-3 text-sm text-gray-300"><?php echo e($log->created_at->format('d.m H:i')); ?></td>
                        <td class="px-6 py-3 text-sm text-white"><?php echo e($log->user?->name ?? 'System'); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php echo e($log->action === 'created' ? 'bg-green-600/20 text-green-400' : ''); ?>

                                <?php echo e($log->action === 'updated' ? 'bg-blue-600/20 text-blue-400' : ''); ?>

                                <?php echo e($log->action === 'deleted' ? 'bg-red-600/20 text-red-400' : ''); ?>

                            "><?php echo e($log->action); ?></span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-400"><?php echo e(class_basename($log->model_type)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-400">Немає записів</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/system-admin/churches/show.blade.php ENDPATH**/ ?>