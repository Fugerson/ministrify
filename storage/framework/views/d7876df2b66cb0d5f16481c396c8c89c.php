<?php $__env->startSection('title', 'Створити служіння'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <a href="<?php echo e(route('ministries.index')); ?>" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад
    </a>

    <form method="POST" action="<?php echo e(route('ministries.store')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Основна інформація</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Назва *</label>
                    <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Прославлення, Медіа, Дитяче...">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?php echo e(old('description')); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Лідер</label>
                    <?php if (isset($component)) { $__componentOriginaldc128e045e365e722151ae7c115e6fd4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc128e045e365e722151ae7c115e6fd4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.person-select','data' => ['name' => 'leader_id','people' => $people,'selected' => old('leader_id'),'placeholder' => 'Пошук лідера...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('person-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'leader_id','people' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($people),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('leader_id')),'placeholder' => 'Пошук лідера...']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc128e045e365e722151ae7c115e6fd4)): ?>
<?php $attributes = $__attributesOriginaldc128e045e365e722151ae7c115e6fd4; ?>
<?php unset($__attributesOriginaldc128e045e365e722151ae7c115e6fd4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc128e045e365e722151ae7c115e6fd4)): ?>
<?php $component = $__componentOriginaldc128e045e365e722151ae7c115e6fd4; ?>
<?php unset($__componentOriginaldc128e045e365e722151ae7c115e6fd4); ?>
<?php endif; ?>
                </div>

                <div>
                    <label for="monthly_budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Бюджет на місяць (грн)</label>
                    <input type="number" name="monthly_budget" id="monthly_budget" value="<?php echo e(old('monthly_budget')); ?>" min="0" step="1"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="5000">
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір</label>
                    <input type="color" name="color" id="color" value="<?php echo e(old('color', '#3b82f6')); ?>"
                           class="w-16 h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Позиції</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Додайте позиції, на які можна призначати людей</p>

            <div class="space-y-2" id="positions-container">
                <input type="text" name="positions[]" placeholder="Наприклад: Вокал"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <input type="text" name="positions[]" placeholder="Наприклад: Гітара"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <input type="text" name="positions[]" placeholder="Наприклад: Звук"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <button type="button" onclick="addPosition()" class="mt-2 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-500">
                + Додати позицію
            </button>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="<?php echo e(route('ministries.index')); ?>" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Скасувати
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Створити
            </button>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function addPosition() {
    const container = document.getElementById('positions-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'positions[]';
    input.placeholder = 'Назва позиції';
    input.className = 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
    container.appendChild(input);
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/ministries/create.blade.php ENDPATH**/ ?>