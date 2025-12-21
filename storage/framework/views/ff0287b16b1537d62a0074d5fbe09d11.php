<?php $__env->startSection('title', 'Вхід'); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('login')); ?>">
    <?php echo csrf_field(); ?>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required autofocus
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
        <input type="password" name="password" id="password" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <div class="flex items-center justify-between mb-6">
        <label class="flex items-center">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <span class="ml-2 text-sm text-gray-600">Запам'ятати мене</span>
        </label>

        <a href="<?php echo e(route('password.request')); ?>" class="text-sm text-primary-600 hover:text-primary-500">
            Забули пароль?
        </a>
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
        Увійти
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
        Немає акаунту?
        <a href="<?php echo e(route('register')); ?>" class="text-primary-600 hover:text-primary-500 font-medium">
            Зареєструвати церкву
        </a>
    </p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>