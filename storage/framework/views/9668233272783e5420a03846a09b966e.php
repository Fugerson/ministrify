<?php $__env->startSection('title', 'System Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Церков</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo e($stats['churches']); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Користувачів</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo e($stats['users']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Людей</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo e($stats['people']); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Подій</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo e($stats['events']); ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6">
            <p class="text-green-100 text-sm">Загальні надходження</p>
            <p class="text-3xl font-bold text-white mt-2"><?php echo e(number_format($finances['total_income'], 0, ',', ' ')); ?> ₴</p>
        </div>
        <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-2xl p-6">
            <p class="text-red-100 text-sm">Загальні витрати</p>
            <p class="text-3xl font-bold text-white mt-2"><?php echo e(number_format($finances['total_expenses'], 0, ',', ' ')); ?> ₴</p>
        </div>
    </div>

    <!-- Growth Chart -->
    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <h2 class="text-lg font-semibold text-white mb-4">Зростання за 6 місяців</h2>
        <canvas id="growthChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Churches -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-white">Останні церкви</h2>
                <a href="<?php echo e(route('system.churches.index')); ?>" class="text-red-400 hover:text-red-300 text-sm">Всі →</a>
            </div>
            <div class="divide-y divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentChurches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $church): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('system.churches.show', $church)); ?>" class="flex items-center px-6 py-4 hover:bg-gray-700/50">
                    <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center mr-4">
                        <span class="text-lg">⛪</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-white truncate"><?php echo e($church->name); ?></p>
                        <p class="text-sm text-gray-400"><?php echo e($church->city); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-300"><?php echo e($church->users_count); ?> користувачів</p>
                        <p class="text-xs text-gray-500"><?php echo e($church->people_count); ?> людей</p>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-6 py-4 text-gray-400">Немає церков</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-white">Останні користувачі</h2>
                <a href="<?php echo e(route('system.users.index')); ?>" class="text-red-400 hover:text-red-300 text-sm">Всі →</a>
            </div>
            <div class="divide-y divide-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentUsers->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center px-6 py-3">
                    <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-white"><?php echo e(mb_substr($user->name, 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?php echo e($user->name); ?></p>
                        <p class="text-xs text-gray-400 truncate"><?php echo e($user->email); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->is_super_admin): ?>
                        <span class="px-2 py-0.5 bg-red-600/20 text-red-400 text-xs rounded-full">Super</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="px-2 py-0.5 bg-gray-700 text-gray-300 text-xs rounded-full"><?php echo e($user->role); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="px-6 py-4 text-gray-400">Немає користувачів</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-white">Останні дії в системі</h2>
            <a href="<?php echo e(route('system.audit-logs')); ?>" class="text-red-400 hover:text-red-300 text-sm">Всі →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Час</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Дія</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Модель</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentLogs->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-3 text-sm text-gray-300"><?php echo e($log->created_at->format('d.m H:i')); ?></td>
                        <td class="px-6 py-3 text-sm text-white"><?php echo e($log->user?->name ?? 'System'); ?></td>
                        <td class="px-6 py-3 text-sm text-gray-400"><?php echo e($log->church?->name ?? '-'); ?></td>
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
                        <td colspan="5" class="px-6 py-4 text-center text-gray-400">Немає записів</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('growthChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyGrowth, 'month')); ?>,
        datasets: [
            {
                label: 'Церкви',
                data: <?php echo json_encode(array_column($monthlyGrowth, 'churches')); ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Користувачі',
                data: <?php echo json_encode(array_column($monthlyGrowth, 'users')); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Люди',
                data: <?php echo json_encode(array_column($monthlyGrowth, 'people')); ?>,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: '#9ca3af' }
            }
        },
        scales: {
            x: {
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(75, 85, 99, 0.3)' }
            },
            y: {
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(75, 85, 99, 0.3)' }
            }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/system-admin/index.blade.php ENDPATH**/ ?>