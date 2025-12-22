<?php $__env->startSection('title', 'Церкви'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('system.churches.create')); ?>" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
    + Додати церкву
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Search -->
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Пошук церкви..."
                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            <button type="submit" class="px-6 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg">Шукати</button>
        </form>
    </div>

    <!-- Churches Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Місто</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Користувачі</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Люди</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Служіння</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Події</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Публ. сайт</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($church->logo): ?>
                                <img src="/storage/<?php echo e($church->logo); ?>" class="w-10 h-10 rounded-lg object-cover mr-3">
                                <?php else: ?>
                                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center mr-3">
                                    <span class="text-lg">⛪</span>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div>
                                    <p class="font-medium text-white"><?php echo e($church->name); ?></p>
                                    <p class="text-xs text-gray-400">ID: <?php echo e($church->id); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300"><?php echo e($church->city); ?></td>
                        <td class="px-6 py-4 text-center text-gray-300"><?php echo e($church->users_count); ?></td>
                        <td class="px-6 py-4 text-center text-gray-300"><?php echo e($church->people_count); ?></td>
                        <td class="px-6 py-4 text-center text-gray-300"><?php echo e($church->ministries_count); ?></td>
                        <td class="px-6 py-4 text-center text-gray-300"><?php echo e($church->events_count); ?></td>
                        <td class="px-6 py-4 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($church->public_site_enabled): ?>
                            <span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded-full">Увімкнено</span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-gray-600/20 text-gray-400 text-xs rounded-full">Вимкнено</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('system.churches.show', $church)); ?>"
                                   class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="<?php echo e(route('system.churches.switch', $church)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="p-2 text-gray-400 hover:text-green-400 hover:bg-gray-700 rounded-lg" title="Увійти в церкву">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">Церкви не знайдено</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($churches->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-700">
            <?php echo e($churches->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/system-admin/churches/index.blade.php ENDPATH**/ ?>