<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Редактирай проект</h1>
        <p class="mt-1 text-sm text-gray-500">Обнови информацията за проекта</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mb-6">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form method="POST" action="index.php?url=project/update" class="space-y-6">
            <?php echo CSRF::getTokenField(); ?>
            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Име на проекта <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?php echo htmlspecialchars($project['name']); ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                       placeholder="Например: Уеб приложение за управление">
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                    Запази промените
                </button>
                <a href="index.php?url=project/index" 
                   class="px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Отказ
                </a>
            </div>
        </form>
    </div>
</div>
