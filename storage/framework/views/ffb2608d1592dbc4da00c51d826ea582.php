<?php $__env->startSection('title', 'Люди'); ?>

<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('people.create')); ?>" class="hidden lg:inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
    </svg>
    Додати
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginal20d4401d9b60c1786c7c83b5130cef6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-help','data' => ['page' => 'people']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page-help'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['page' => 'people']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b)): ?>
<?php $attributes = $__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b; ?>
<?php unset($__attributesOriginal20d4401d9b60c1786c7c83b5130cef6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20d4401d9b60c1786c7c83b5130cef6b)): ?>
<?php $component = $__componentOriginal20d4401d9b60c1786c7c83b5130cef6b; ?>
<?php unset($__componentOriginal20d4401d9b60c1786c7c83b5130cef6b); ?>
<?php endif; ?>

<div class="space-y-4 lg:space-y-6">
    <!-- Mobile Header -->
    <div class="lg:hidden flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Люди</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($people->total()); ?> <?php echo e(trans_choice('людина|людини|людей', $people->total())); ?></p>
        </div>
        <a href="<?php echo e(route('people.create')); ?>" class="w-10 h-10 bg-primary-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </a>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <form method="GET" class="space-y-3 lg:space-y-0 lg:flex lg:items-center lg:gap-3">
            <!-- Search -->
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Пошук..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-primary-500/20 transition-all">
            </div>

            <!-- Filters -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
                <select name="tag" class="flex-shrink-0 px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-gray-700 dark:text-gray-300">
                    <option value="">Теги</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($tag->id); ?>" <?php echo e(request('tag') == $tag->id ? 'selected' : ''); ?>><?php echo e($tag->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>

                <select name="ministry" class="flex-shrink-0 px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl focus:ring-2 focus:ring-primary-500/20 text-gray-700 dark:text-gray-300">
                    <option value="">Служіння</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ministries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ministry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ministry->id); ?>" <?php echo e(request('ministry') == $ministry->id ? 'selected' : ''); ?>><?php echo e($ministry->icon); ?> <?php echo e($ministry->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>

                <button type="submit" class="flex-shrink-0 px-5 py-3 bg-gray-900 dark:bg-gray-600 text-white rounded-xl font-medium hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors">
                    Знайти
                </button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['search', 'tag', 'ministry'])): ?>
                <a href="<?php echo e(route('people.index')); ?>" class="flex-shrink-0 px-4 py-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    Скинути
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>
    </div>

    <!-- People Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 lg:gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $people; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('people.show', $person)); ?>"
           class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-lg hover:border-primary-100 dark:hover:border-primary-900 hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-start gap-3">
                <!-- Avatar -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->photo): ?>
                <img src="<?php echo e(Storage::url($person->photo)); ?>" alt="<?php echo e($person->full_name); ?>"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                <?php else: ?>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-base font-semibold text-white"><?php echo e(mb_substr($person->first_name, 0, 1)); ?><?php echo e(mb_substr($person->last_name, 0, 1)); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">
                        <?php echo e($person->full_name); ?>

                    </h3>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->phone): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate"><?php echo e($person->phone); ?></p>
                    <?php elseif($person->telegram_username): ?>
                    <p class="text-sm text-primary-600 dark:text-primary-400 truncate"><?php echo e($person->telegram_username); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Ministries icons -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->ministries->isNotEmpty()): ?>
                    <div class="flex items-center gap-1 mt-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $person->ministries->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ministry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="text-base" title="<?php echo e($ministry->name); ?>"><?php echo e($ministry->icon); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->ministries->count() > 3): ?>
                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">+<?php echo e($person->ministries->count() - 3); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Tags -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->tags->isNotEmpty()): ?>
            <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-gray-50 dark:border-gray-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $person->tags->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md"
                      style="background-color: <?php echo e($tag->color ?? '#3b82f6'); ?>15; color: <?php echo e($tag->color ?? '#3b82f6'); ?>">
                    <?php echo e($tag->name); ?>

                </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($person->tags->count() > 2): ?>
                <span class="text-xs text-gray-400 dark:text-gray-500">+<?php echo e($person->tags->count() - 2); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['search', 'tag', 'ministry'])): ?>
                        Нікого не знайдено
                    <?php else: ?>
                        Поки що нікого немає
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['search', 'tag', 'ministry'])): ?>
                        Спробуйте змінити параметри пошуку
                    <?php else: ?>
                        Додайте першу людину до бази
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!request()->hasAny(['search', 'tag', 'ministry'])): ?>
                <a href="<?php echo e(route('people.create')); ?>" class="inline-flex items-center px-5 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 shadow-lg shadow-primary-500/30 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Додати людину
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($people->hasPages()): ?>
    <div class="flex justify-center py-2">
        <?php echo e($people->links()); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Export/Import -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Експорт / Імпорт</h3>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?php echo e(route('people.export')); ?>" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Завантажити Excel
            </a>
            <form action="<?php echo e(route('people.import')); ?>" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col sm:flex-row gap-2">
                <?php echo csrf_field(); ?>
                <label class="flex-1 flex items-center justify-center px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Оберіть файл</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden">
                </label>
                <button type="submit" class="px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                    Імпорт
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/people/index.blade.php ENDPATH**/ ?>