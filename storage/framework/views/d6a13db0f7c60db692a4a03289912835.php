<?php $__env->startSection('title', 'Користувачі'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Пошук за іменем або email..."
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

            <select name="role"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">Всі ролі</option>
                <option value="admin" <?php echo e(request('role') == 'admin' ? 'selected' : ''); ?>>Адміністратор</option>
                <option value="leader" <?php echo e(request('role') == 'leader' ? 'selected' : ''); ?>>Лідер</option>
                <option value="volunteer" <?php echo e(request('role') == 'volunteer' ? 'selected' : ''); ?>>Служитель</option>
            </select>

            <label class="flex items-center gap-2 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white cursor-pointer">
                <input type="checkbox" name="super_admin" value="1" <?php echo e(request()->has('super_admin') ? 'checked' : ''); ?>

                       class="rounded bg-gray-600 border-gray-500 text-red-600 focus:ring-red-500">
                <span>Super Admin</span>
            </label>

            <button type="submit" class="px-6 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg">Фільтрувати</button>
            <a href="<?php echo e(route('system.users.index')); ?>" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg">Скинути</a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Користувач</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Церква</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Роль</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Статус</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase">Створено</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full <?php echo e($user->is_super_admin ? 'bg-red-600' : 'bg-gray-600'); ?> flex items-center justify-center mr-3">
                                    <span class="text-sm font-medium text-white"><?php echo e(mb_substr($user->name, 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-white"><?php echo e($user->name); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($user->email); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->church): ?>
                            <a href="<?php echo e(route('system.churches.show', $user->church)); ?>" class="text-blue-400 hover:text-blue-300">
                                <?php echo e($user->church->name); ?>

                            </a>
                            <?php else: ?>
                            <span class="text-gray-500">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php echo e($user->role === 'admin' ? 'bg-red-600/20 text-red-400' : ''); ?>

                                <?php echo e($user->role === 'leader' ? 'bg-blue-600/20 text-blue-400' : ''); ?>

                                <?php echo e($user->role === 'volunteer' ? 'bg-green-600/20 text-green-400' : ''); ?>

                            "><?php echo e($user->role); ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->is_super_admin): ?>
                            <span class="px-2 py-1 bg-red-600/20 text-red-400 text-xs rounded-full">Super Admin</span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-gray-600/20 text-gray-400 text-xs rounded-full">Звичайний</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center text-gray-300 text-sm">
                            <?php echo e($user->created_at->format('d.m.Y')); ?>

                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->id !== auth()->id()): ?>
                                <form method="POST" action="<?php echo e(route('system.users.impersonate', $user)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="p-2 text-gray-400 hover:text-green-400 hover:bg-gray-700 rounded-lg" title="Увійти як <?php echo e($user->name); ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <a href="<?php echo e(route('system.users.edit', $user)); ?>"
                                   class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg" title="Редагувати">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->id !== auth()->id()): ?>
                                <form method="POST" action="<?php echo e(route('system.users.destroy', $user)); ?>"
                                      onsubmit="return confirm('Видалити користувача <?php echo e($user->name); ?>?')" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded-lg" title="Видалити">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Користувачів не знайдено</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($users->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-700">
            <?php echo e($users->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.system-admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/system-admin/users/index.blade.php ENDPATH**/ ?>