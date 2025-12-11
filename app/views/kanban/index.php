<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Kanban Board</h1>
            <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($project['name']); ?></p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (!empty($projects)): ?>
                <select onchange="window.location.href='index.php?url=kanban/index&project_id=' + this.value" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <?php foreach ($projects as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $project['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <a href="index.php?url=task/create&project_id=<?php echo $project['id']; ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Нова задача
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($tasksByStatus as $statusGroup): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">
                        <?php echo htmlspecialchars($statusGroup['status']['name']); ?>
                    </h3>
                    <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-medium">
                        <?php echo count($statusGroup['tasks']); ?>
                    </span>
                </div>
                
                <div class="space-y-3 min-h-[200px]">
                    <?php if (empty($statusGroup['tasks'])): ?>
                        <div class="text-center py-8 text-gray-400 text-sm">
                            Няма задачи
                        </div>
                    <?php else: ?>
                        <?php foreach ($statusGroup['tasks'] as $task): ?>
                            <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                               class="block bg-gray-50 hover:bg-gray-100 rounded-lg p-3 border border-gray-200 hover:border-blue-300 transition-all cursor-pointer group">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900 group-hover:text-blue-700 flex-1">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h4>
                                    <?php
                                    $priorityColors = [
                                        1 => 'bg-red-500',
                                        2 => 'bg-orange-500',
                                        3 => 'bg-yellow-500',
                                        4 => 'bg-green-500'
                                    ];
                                    $priority = $task['priority'] ?? 3;
                                    ?>
                                    <div class="w-2 h-2 rounded-full <?php echo $priorityColors[$priority] ?? 'bg-gray-400'; ?> flex-shrink-0 ml-2"></div>
                                </div>
                                
                                <?php if ($task['description']): ?>
                                    <p class="text-xs text-gray-600 line-clamp-2 mb-2">
                                        <?php echo htmlspecialchars(substr($task['description'], 0, 60)); ?>
                                        <?php echo strlen($task['description']) > 60 ? '...' : ''; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <?php if ($task['first_name'] && $task['last_name']): ?>
                                        <span><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">Не назначен</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($task['due_date']): ?>
                                        <span class="<?php echo (strtotime($task['due_date']) < time() && $task['status_name'] != 'Done') ? 'text-red-600 font-semibold' : ''; ?>">
                                            <?php echo date('d.m', strtotime($task['due_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

