<?php $__env->startSection('title', 'Журнал дій'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Пошук за дією або моделлю..."
                   class="flex-1 min-w-64 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">

            <select name="church_id"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">Всі церкви</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $churches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($church->id); ?>" <?php echo e(request('church_id') == $church->id ? 'selected' : ''); ?>>
                    <?php echo e($church->name); ?>

                </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>

            <button type="submit" class="px-6 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg">Фільтрувати</button>
            <a href="<?php echo e(route('system.audit-logs')); ?>" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg">Скинути</a>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-green-400"><?php echo e($logs->where('action', 'created')->count()); ?></p>
            <p class="text-gray-400 text-sm">Створено</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-blue-400"><?php echo e($logs->where('action', 'updated')->count()); ?></p>
            <p class="text-gray-400 text-sm">Оновлено</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-red-400"><?php echo e($logs->where('action', 'deleted')->count()); ?></p>
            <p class="text-gray-400 text-sm">Видалено</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
            <p class="text-2xl font-bold text-white"><?php echo e($logs->total()); ?></p>
            <p class="text-gray-400 text-sm">Всього</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Модель</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-700/30" x-data="{ expanded: false }">
                        <td class="px-6 py-4 text-sm text-gray-300">
                            <div><?php echo e($log->created_at->format('d.m.Y')); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($log->created_at->format('H:i:s')); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->user): ?>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center mr-2">
                                    <span class="text-xs font-medium text-white"><?php echo e(mb_substr($log->user->name, 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="text-sm text-white"><?php echo e($log->user->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($log->user->email); ?></p>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="text-gray-500">System</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->church): ?>
                            <a href="<?php echo e(route('system.churches.show', $log->church)); ?>" class="text-blue-400 hover:text-blue-300 text-sm">
                                <?php echo e($log->church->name); ?>

                            </a>
                            <?php else: ?>
                            <span class="text-gray-500">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php echo e($log->action === 'created' ? 'bg-green-600/20 text-green-400' : ''); ?>

                                <?php echo e($log->action === 'updated' ? 'bg-blue-600/20 text-blue-400' : ''); ?>

                                <?php echo e($log->action === 'deleted' ? 'bg-red-600/20 text-red-400' : ''); ?>

                            "><?php echo e($log->action); ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            <?php echo e(class_basename($log->model_type)); ?>

                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400">
                            <?php echo e($log->model_id); ?>

                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo e($log->ip_address ?? '—'); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">Записів не знайдено</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-700">
            <?php echo e($logs->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/system-admin/audit-logs.blade.php ENDPATH**/ ?>