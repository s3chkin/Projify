<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Задачи</h1>
            <p class="mt-1 text-sm text-gray-500">Управлявай всичките си задачи</p>
        </div>
        <?php if (isset($selectedProjectId) && $selectedProjectId && isset($canCreate) && $canCreate): ?>
            <a href="index.php?url=task/create&project_id=<?php echo $selectedProjectId; ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Нова задача
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <p class="text-sm text-green-700"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <!-- Търсене и филтри -->
    <div class="mb-4 space-y-3">
        <form method="GET" action="index.php?url=task/index" class="flex gap-2">
            <input type="hidden" name="url" value="task/index">
            <input type="text" 
                   name="search" 
                   value="<?php echo htmlspecialchars($search ?? ''); ?>"
                   placeholder="Търсене по заглавие или описание..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Търси
            </button>
            <?php if ($search): ?>
                <a href="index.php?url=task/index<?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?>" 
                   class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Изчисти
                </a>
            <?php endif; ?>
        </form>
        
        <div class="flex gap-2 flex-wrap">
            <?php if (!empty($projects)): ?>
                <select onchange="window.location.href='index.php?url=task/index&project_id=' + this.value + '<?php echo $search ? '&search=' . urlencode($search) : ''; ?>'" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">Всички проекти</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>" 
                                <?php echo (isset($selectedProjectId) && $selectedProjectId == $project['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            
            <?php if (!empty($statuses)): ?>
                <select onchange="window.location.href='index.php?url=task/index<?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?>&status_id=' + (this.value ? this.value : '') + '<?php echo $search ? '&search=' . urlencode($search) : ''; ?>'" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                    <option value="">Всички статуси</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['id']; ?>" 
                                <?php echo (isset($selectedStatusId) && $selectedStatusId == $status['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            
            <?php if (isset($selectedStatusId) && $selectedStatusId): ?>
                <a href="index.php?url=task/index<?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                    Изчисти филтър
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                <?php if (isset($selectedStatusId) && $selectedStatusId): ?>
                    Няма задачи с избрания статус
                <?php elseif (isset($selectedProjectId) && $selectedProjectId): ?>
                    Няма задачи в този проект
                <?php elseif (isset($search) && $search): ?>
                    Няма резултати от търсенето
                <?php else: ?>
                    Няма задачи
                <?php endif; ?>
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                <?php if (isset($selectedStatusId) && $selectedStatusId || isset($search) && $search): ?>
                    <a href="index.php?url=task/index<?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?>" 
                       class="text-blue-600 hover:text-blue-700 underline">
                        Изчисти филтрите
                    </a> или 
                <?php endif; ?>
                <?php if (isset($selectedProjectId) && $selectedProjectId): ?>
                    създай първата задача в проекта
                <?php else: ?>
                    избери проект и създай задача
                <?php endif; ?>
            </p>
            <?php if (isset($selectedProjectId) && $selectedProjectId): ?>
                <a href="index.php?url=task/create&project_id=<?php echo $selectedProjectId; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Създай задача
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Заглавие</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Създадена от</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Назначен</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Крайна дата</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Приоритет</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($tasks as $task): ?>
                            <?php
                            $isOverdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status_name'] != 'Done';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $isOverdue ? 'bg-red-50/50' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                       class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <a href="index.php?url=project/show&id=<?php echo $task['project_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700 hover:underline">
                                        <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php 
                                    if (!empty($task['creator_first_name']) && !empty($task['creator_last_name'])) {
                                        echo htmlspecialchars($task['creator_first_name'] . ' ' . $task['creator_last_name']);
                                    } else {
                                        echo '<span class="text-gray-400">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo ($task['status_name'] == 'Done') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php 
                                    if ($task['first_name'] && $task['last_name']) {
                                        echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']);
                                    } else {
                                        echo '<span class="text-gray-400">Не назначен</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600'; ?>">
                                    <?php echo $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : '<span class="text-gray-400">N/A</span>'; ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="ml-2 text-xs text-red-600">(Просрочена)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $priorityColors = [
                                        1 => 'bg-red-100 text-red-700',
                                        2 => 'bg-orange-100 text-orange-700',
                                        3 => 'bg-yellow-100 text-yellow-700',
                                        4 => 'bg-green-100 text-green-700'
                                    ];
                                    $priorityText = ['1' => 'Много висок', '2' => 'Висок', '3' => 'Среден', '4' => 'Нисък'];
                                    $priority = $task['priority'] ?? 3;
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo $priorityColors[$priority] ?? 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo $priorityText[$priority] ?? 'Среден'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-3">
                                        <?php if ($task['status_name'] != 'Done' && isset($canCreate) && $canCreate): ?>
                                        <form method="POST" action="index.php?url=task/complete" class="inline">
                                            <?php echo CSRF::getTokenField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                            <input type="hidden" name="redirect" value="index.php?url=task/index<?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?>">
                                            <button type="submit" 
                                                    class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
                                                    title="Завърши задача"
                                                    onclick="return confirm('Сигурен ли си, че искаш да завършиш тази задача?');">
                                                ✓
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700 font-medium transition-colors">Виж</a>
                                        <?php if (isset($task['canEdit']) && $task['canEdit']): ?>
                                        <a href="index.php?url=task/edit&id=<?php echo $task['id']; ?>" 
                                           class="text-green-600 hover:text-green-700 font-medium transition-colors">Редактирай</a>
                                        <a href="index.php?url=task/delete&id=<?php echo $task['id']; ?>" 
                                           class="text-red-600 hover:text-red-700 font-medium transition-colors"
                                           onclick="return confirm('Сигурен ли си, че искаш да изтриеш тази задача?')">Изтрий</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Страниране -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Показване на страница <span class="font-semibold"><?php echo $page; ?></span> от <span class="font-semibold"><?php echo $totalPages; ?></span>
                        (Общо <span class="font-semibold"><?php echo $totalTasks; ?></span> задачи)
                    </p>
                    <div class="flex items-center gap-2">
                        <?php if ($page > 1): ?>
                            <a href="index.php?url=task/index&page=<?php echo $page - 1; ?><?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                Предишна
                            </a>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-1">
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="index.php?url=task/index&page=<?php echo $i; ?><?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="index.php?url=task/index&page=<?php echo $page + 1; ?><?php echo $selectedProjectId ? '&project_id=' . $selectedProjectId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                Следваща
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

