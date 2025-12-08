<div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-6 border border-gray-200/50 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Редактирай проект</h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=project/update" class="space-y-4">
        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Име на проекта
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="<?php echo htmlspecialchars($project['name']); ?>"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Въведи име на проекта">
        </div>

        <div class="flex gap-3">
            <button type="submit" 
                    class="bg-green-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors">
                Запази промените
            </button>
            <a href="index.php?url=project/index" 
               class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Отказ
            </a>
        </div>
    </form>
</div>

