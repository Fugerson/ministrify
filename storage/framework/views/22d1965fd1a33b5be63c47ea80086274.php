<?php $__env->startSection('title', 'Фінансовий звіт'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $months = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
?>

<div class="space-y-6">
    <!-- Summary -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900"><?php echo e($months[$month - 1]); ?> <?php echo e($year); ?></h2>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Загальний бюджет</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($totalBudget, 0, ',', ' ')); ?> &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Витрачено</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo e(number_format($totalSpent, 0, ',', ' ')); ?> &#8372;</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Залишок</p>
                <p class="text-3xl font-bold <?php echo e($totalBudget - $totalSpent < 0 ? 'text-red-600' : 'text-green-600'); ?>">
                    <?php echo e(number_format($totalBudget - $totalSpent, 0, ',', ' ')); ?> &#8372;
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By ministry -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">По служіннях</h3>
            </div>
            <div class="p-6 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byMinistry; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ministry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700"><?php echo e($ministry['icon']); ?> <?php echo e($ministry['name']); ?></span>
                            <span class="text-sm text-gray-500">
                                <?php echo e(number_format($ministry['spent'], 0, ',', ' ')); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ministry['budget']): ?>
                                    / <?php echo e(number_format($ministry['budget'], 0, ',', ' ')); ?> &#8372;
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ministry['budget']): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full <?php echo e($ministry['percentage'] > 90 ? 'bg-red-500' : ($ministry['percentage'] > 70 ? 'bg-yellow-500' : 'bg-green-500')); ?>"
                                     style="width: <?php echo e(min(100, $ministry['percentage'])); ?>%"></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- By category -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">По категоріях</h3>
            </div>
            <div class="p-6 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->total > 0): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700"><?php echo e($category->name); ?></span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e(number_format($category->total, 0, ',', ' ')); ?> &#8372;</span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent expenses -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Останні витрати</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Служіння</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Опис</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Хто</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentExpenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($expense->date->format('d.m')); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($expense->ministry->icon); ?> <?php echo e($expense->ministry->name); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo e($expense->description); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right"><?php echo e(number_format($expense->amount, 0, ',', ' ')); ?> &#8372;</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($expense->user->name); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="<?php echo e(route('expenses.index')); ?>" class="inline-block text-gray-600 hover:text-gray-900">
        &larr; Назад
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/expenses/report.blade.php ENDPATH**/ ?>