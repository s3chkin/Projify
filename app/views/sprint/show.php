<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($sprint['name']); ?></h1>
            <p class="mt-1 text-sm text-gray-500">
                <?php echo htmlspecialchars($project['name']); ?> • 
                <?php echo date('d.m.Y', strtotime($sprint['start_date'])); ?> - 
                <?php echo date('d.m.Y', strtotime($sprint['end_date'])); ?>
            </p>
        </div>
        <a href="index.php?url=sprint/index&project_id=<?php echo $sprint['project_id']; ?>" 
           class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
            Назад
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Задачи в Sprint</h2>
        
        <?php if (empty($tasks)): ?>
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">Няма задачи в този sprint</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($tasks as $task): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                               class="text-sm font-medium text-gray-900 hover:text-blue-700">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </a>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700">
                                    <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                </span>
                                <?php if ($task['first_name'] && $task['last_name']): ?>
                                    <span><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

