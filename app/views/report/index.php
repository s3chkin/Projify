<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Справки</h1>
            <p class="mt-1 text-sm text-gray-500">Анализ и статистика за проектите и задачите</p>
        </div>
        <a href="index.php?url=report/export" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Експорт в PDF
        </a>
    </div>

    <!-- Обща статистика -->
    <?php if (isset($reports['overall_stats']) && $reports['overall_stats']): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Обща статистика</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Проекти</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $reports['overall_stats']['total_projects']; ?></p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Задачи</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo $reports['overall_stats']['total_tasks']; ?></p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Потребители</p>
                    <p class="text-2xl font-bold text-purple-600"><?php echo $reports['overall_stats']['total_users']; ?></p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Просрочени</p>
                    <p class="text-2xl font-bold text-red-600"><?php echo $reports['overall_stats']['overdue_tasks']; ?></p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Завършени</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $reports['overall_stats']['completed_tasks']; ?></p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Средна продължителност</p>
                    <p class="text-2xl font-bold text-indigo-600"><?php echo round($reports['overall_stats']['avg_task_duration'] ?? 0, 1); ?> дни</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Задачи по статус -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Задачи по статус</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Брой задачи</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['tasks_by_status'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-medium text-gray-900"><?php echo htmlspecialchars($row['status_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-gray-600"><?php echo $row['task_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Задачи по проект -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Задачи по проект</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Брой задачи</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['tasks_by_project'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-medium text-gray-900"><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-gray-600"><?php echo $row['task_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Натовареност по човек -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Натовареност по човек</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Потребител</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Брой задачи</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['workload_by_person'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $row['task_count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Просрочени задачи -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Просрочени задачи</h2>
        <?php if (empty($reports['overdue_tasks'])): ?>
            <p class="text-gray-600 text-center py-8">Няма просрочени задачи.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Заглавие</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Назначен</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Крайна дата</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reports['overdue_tasks'] as $task): ?>
                            <tr class="hover:bg-red-50/50 transition-colors bg-red-50/30">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-medium text-gray-900"><?php echo htmlspecialchars($task['title']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-gray-600"><?php echo htmlspecialchars($task['project_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-gray-600"><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-red-600 font-semibold"><?php echo date('d.m.Y', strtotime($task['due_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-gray-600"><?php echo htmlspecialchars($task['status_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Просрочени задачи по проекти -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Просрочени задачи по проекти</h2>
        <?php if (empty($reports['overdue_tasks_by_project'])): ?>
            <p class="text-gray-600 text-center py-8">Няма просрочени задачи по проекти.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Брой просрочени</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Най-ранна крайна дата</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Най-късна крайна дата</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reports['overdue_tasks_by_project'] as $row): ?>
                            <tr class="hover:bg-red-50/40 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $row['overdue_count']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo $row['earliest_due_date'] ? date('d.m.Y', strtotime($row['earliest_due_date'])) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo $row['latest_due_date'] ? date('d.m.Y', strtotime($row['latest_due_date'])) : 'N/A'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Задачи по приоритет -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Задачи по приоритет</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Приоритет</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Брой задачи</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Средна продължителност (дни)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['tasks_by_priority'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900<?php echo htmlspecialchars($row['priority_name']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo round($row['avg_duration'] ?? 0, 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Throughput по етапи -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Throughput по етапи</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Брой задачи</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Средна продължителност (дни)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['throughput_by_stage'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900<?php echo htmlspecialchars($row['status_name']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo round($row['avg_duration'] ?? 0, 1); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Проекти с най-много задачи -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Проекти с най-много задачи</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Общо задачи</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Завършени</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['projects_with_most_tasks'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900<?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['completed_tasks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Потребители с най-много задачи -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Потребители с най-много задачи</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Потребител</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Общо задачи</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Завършени</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($reports['users_with_most_tasks'] as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['task_count']; ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo $row['completed_tasks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Задачи по спринтове -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Задачи по спринтове</h2>
        <?php if (empty($reports['tasks_by_sprint'])): ?>
            <p class="text-gray-600 text-center py-8">Няма създадени спринтове.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Спринт</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проект</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Брой задачи</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Начална дата</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Крайна дата</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reports['tasks_by_sprint'] as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['sprint_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($row['project_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $row['task_count']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo $row['start_date'] ? date('d.m.Y', strtotime($row['start_date'])) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo $row['end_date'] ? date('d.m.Y', strtotime($row['end_date'])) : 'N/A'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

