<div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Моите проекти</h1>
        <a href="index.php?url=project/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
            + Нов проект
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
        <div class="text-center py-8 text-gray-500">
            <p>Все още нямаш проекти.</p>
            <a href="index.php?url=project/create" class="text-blue-600 hover:underline mt-2 inline-block">Създай първия проект</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($projects as $project): ?>
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($project['name']); ?>
                    </h3>
                    <div class="flex gap-2 mt-4">
                        <a href="index.php?url=project/show&id=<?php echo $project['id']; ?>" 
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Виж
                        </a>
                        <a href="index.php?url=project/edit&id=<?php echo $project['id']; ?>" 
                           class="text-green-600 hover:text-green-700 text-sm font-medium">
                            Редактирай
                        </a>
                        <a href="index.php?url=project/delete&id=<?php echo $project['id']; ?>" 
                           class="text-red-600 hover:text-red-700 text-sm font-medium"
                           onclick="return confirm('Сигурен ли си, че искаш да изтриеш този проект?')">
                            Изтрий
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

