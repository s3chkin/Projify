<div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-6 border border-gray-200/50 max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($project['name']); ?></h1>
        <div class="flex gap-2">
            <a href="index.php?url=project/edit&id=<?php echo $project['id']; ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                Редактирай
            </a>
            <a href="index.php?url=project/index" 
               class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                Назад
            </a>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-gray-50 rounded-lg p-4">
            <h2 class="text-sm font-medium text-gray-500 mb-1">ID на проекта</h2>
            <p class="text-lg font-semibold text-gray-900">#<?php echo $project['id']; ?></p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <h2 class="text-sm font-medium text-gray-500 mb-1">Име</h2>
            <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($project['name']); ?></p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <h2 class="text-sm font-medium text-gray-500 mb-1">Собственик ID</h2>
            <p class="text-lg font-semibold text-gray-900"><?php echo $project['owner_id']; ?></p>
        </div>
    </div>
</div>

