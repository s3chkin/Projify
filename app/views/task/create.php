<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Създай нова задача</h1>
        <p class="mt-1 text-sm text-gray-500">Проект: <strong class="text-gray-900"><?php echo htmlspecialchars($project['name']); ?></strong></p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mb-6">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <form method="POST" action="index.php?url=task/store" class="space-y-6">
            <?php echo CSRF::getTokenField(); ?>
            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Заглавие <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                       placeholder="Въведи заглавие на задачата">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Описание
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                          placeholder="Въведи описание на задачата"></textarea>
            </div>

            <div>
                <label for="status_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Статус <span class="text-red-500">*</span>
                </label>
                <select id="status_id" 
                        name="status_id" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                    <option value="">Избери статус</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>">
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Начална дата
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Крайна дата
                    </label>
                    <input type="date" 
                           id="due_date" 
                           name="due_date"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                </div>
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                    Приоритет
                </label>
                <select id="priority" 
                        name="priority"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                    <option value="">Не е зададен</option>
                    <option value="1">Много висок</option>
                    <option value="2">Висок</option>
                    <option value="3" selected>Среден</option>
                    <option value="4">Нисък</option>
                </select>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    Създай задача
                </button>
                <a href="index.php?url=task/index&project_id=<?php echo $project['id']; ?>" 
                   class="px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Отказ
                </a>
            </div>
        </form>
    </div>
</div>

