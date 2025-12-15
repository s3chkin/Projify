<?php
require_once "../app/core/CSRF.php";
require_once "../app/models/Report.php";
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="index.php?url=admin/index" 
               class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block">
                ← Назад към списъка с потребители
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
            </h1>
            <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'; ?>">
                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
            </span>
            <?php if ($user['role'] !== 'admin' && $user['id'] != Session::get('user_id')): ?>
                <form method="POST" action="index.php?url=admin/delete" 
                      onsubmit="return confirm('Сигурни ли сте, че искате да изтриете този потребител?');" 
                      class="inline">
                    <?php echo CSRF::getTokenField(); ?>
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Изтрий потребител
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Статистики -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
            <div class="text-sm text-blue-600 font-medium mb-1">Общо проекти</div>
            <div class="text-2xl font-bold text-blue-900"><?php echo count($projects); ?></div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
            <div class="text-sm text-green-600 font-medium mb-1">Общо задачи</div>
            <div class="text-2xl font-bold text-green-900"><?php echo count($tasks); ?></div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
            <div class="text-sm text-purple-600 font-medium mb-1">Завършени задачи</div>
            <div class="text-2xl font-bold text-purple-900"><?php echo $reports['completed_tasks']; ?></div>
        </div>
        <div class="bg-red-50 rounded-lg p-4 border border-red-100">
            <div class="text-sm text-red-600 font-medium mb-1">Просрочени задачи</div>
            <div class="text-2xl font-bold text-red-900"><?php echo count($reports['overdue_tasks']); ?></div>
        </div>
    </div>

    <!-- Проекти -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Проекти (<?php echo count($projects); ?>)</h2>
        </div>
        <?php if (empty($projects)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>Няма проекти</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($projects as $project): ?>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <a href="index.php?url=project/show&id=<?php echo $project['id']; ?>" 
                               class="font-semibold text-gray-900 hover:text-blue-600">
                                <?php echo htmlspecialchars($project['name']); ?>
                            </a>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo ($project['role_type'] ?? 'member') === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                <?php echo ($project['role_type'] ?? 'member') === 'owner' ? 'Собственик' : 'Член'; ?>
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">ID: <?php echo $project['id']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Задачи -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Задачи (<?php echo count($tasks); ?>)</h2>
        </div>
        <?php if (empty($tasks)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>Няма задачи</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Заглавие</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Проект</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Статус</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Крайна дата</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($tasks as $task): ?>
                            <?php $isOverdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status_name'] != 'Done'; ?>
                            <tr class="hover:bg-gray-50 <?php echo $isOverdue ? 'bg-red-50/50' : ''; ?>">
                                <td class="px-4 py-3">
                                    <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                       class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo ($task['status_name'] == 'Done') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm <?php echo $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600'; ?>">
                                    <?php echo $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : 'N/A'; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        Виж
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Справки -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Справки</h2>
            <a href="index.php?url=report/exportUser&user_id=<?php echo $user['id']; ?>" 
               class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Изтегли PDF справка
            </a>
        </div>

        <!-- Статистики за натовареност -->
        <?php if (isset($reports['workload_stats'])): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Статистики за натовареност</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-600 mb-1">Общо задачи</div>
                        <div class="text-lg font-bold text-gray-900"><?php echo $reports['workload_stats']['total_tasks'] ?? 0; ?></div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <div class="text-xs text-green-600 mb-1">Завършени</div>
                        <div class="text-lg font-bold text-green-700"><?php echo $reports['workload_stats']['completed_tasks'] ?? 0; ?></div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <div class="text-xs text-red-600 mb-1">Просрочени</div>
                        <div class="text-lg font-bold text-red-700"><?php echo $reports['workload_stats']['overdue_tasks'] ?? 0; ?></div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3">
                        <div class="text-xs text-yellow-600 mb-1">Днес</div>
                        <div class="text-lg font-bold text-yellow-700"><?php echo $reports['workload_stats']['due_today'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Задачи по статус -->
        <?php if (!empty($reports['tasks_by_status'])): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Задачи по статус</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left">Статус</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Брой задачи</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports['tasks_by_status'] as $row): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['status_name']); ?></td>
                                    <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Задачи по проект -->
        <?php if (!empty($reports['tasks_by_project'])): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Задачи по проект</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left">Проект</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Брой задачи</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports['tasks_by_project'] as $row): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['project_name']); ?></td>
                                    <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Просрочени задачи -->
        <?php if (!empty($reports['overdue_tasks'])): ?>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Просрочени задачи (<?php echo count($reports['overdue_tasks']); ?>)</h3>
                <div class="space-y-2">
                    <?php foreach ($reports['overdue_tasks'] as $task): ?>
                        <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                            <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                               class="font-medium text-red-700 hover:text-red-800">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </a>
                            <div class="text-xs text-red-600 mt-1">
                                Проект: <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?> | 
                                Крайна дата: <?php echo $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : 'N/A'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

