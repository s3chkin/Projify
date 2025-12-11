<?php if (Session::has('user_id')): ?>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">
                    Добре дошли, <?php echo htmlspecialchars(explode(' ', Session::get('user_name'))[0]); ?>!
                </h1>
                <p class="text-sm text-gray-500">Ето общ преглед на вашите проекти и задачи</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Проекти</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_projects'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Задачи</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_tasks'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">За днес</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['today_tasks'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Просрочени</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $stats['overdue_tasks'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Завършени</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['completed_tasks'] ?? 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Просрочени задачи -->
                <?php if (!empty($overdueTasks)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Просрочени задачи</h2>
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-medium">
                                <?php echo count($overdueTasks); ?>
                            </span>
                        </div>
                        <div class="space-y-2">
                            <?php foreach ($overdueTasks as $task): ?>
                                <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                   class="flex items-center gap-3 p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-all border-l-4 border-red-500 group">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-red-700">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?> • 
                                            <?php echo $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : 'N/A'; ?>
                                        </p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-red-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($overdueTasks) > 5): ?>
                            <a href="index.php?url=task/index" class="mt-4 block text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Виж всички просрочени задачи →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Задачи за днес -->
                <?php if (!empty($todayTasks)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Задачи за днес</h2>
                            <span class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-xs font-medium">
                                <?php echo count($todayTasks); ?>
                            </span>
                        </div>
                        <div class="space-y-2">
                            <?php foreach ($todayTasks as $task): ?>
                                <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                   class="flex items-center gap-3 p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-all border-l-4 border-yellow-500 group">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-yellow-700">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?>
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                        <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-yellow-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="index.php?url=task/index" class="mt-4 block text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Виж всички задачи →
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Последни проекти -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Моите проекти</h2>
                        <a href="index.php?url=project/index" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Виж всички
                        </a>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Все още нямаш проекти</p>
                            <a href="index.php?url=project/create" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Създай проект
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($projects as $project): ?>
                                <a href="index.php?url=project/show&id=<?php echo $project['id']; ?>" 
                                   class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition-all group border border-transparent hover:border-blue-200">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-700">
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="index.php?url=project/create" 
                           class="mt-4 block w-full py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors text-center">
                            + Добави нов проект
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Последни задачи -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Последни задачи</h2>
                        <a href="index.php?url=task/index" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Виж всички
                        </a>
                    </div>
                    
                    <?php if (empty($tasks)): ?>
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-500">Няма задачи</p>
                            <a href="index.php?url=task/index" 
                               class="mt-3 inline-block text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Създай задача
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($tasks as $task): ?>
                                <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                   class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg transition-all group">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-700">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?>
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 flex-shrink-0">
                                        <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Бързи действия -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Бързи действия</h2>
                    <div class="space-y-2">
                        <a href="index.php?url=project/create" 
                           class="flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-blue-700">Създай проект</span>
                        </a>
                        <a href="index.php?url=task/index" 
                           class="flex items-center gap-3 p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-green-700">Създай задача</span>
                        </a>
                        <a href="index.php?url=report/index" 
                           class="flex items-center gap-3 p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-purple-700">Справки</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="min-h-[calc(100vh-8rem)] flex items-center justify-center py-12">
        <div class="text-center bg-white rounded-xl shadow-sm border border-gray-200 p-12 max-w-md">
            <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center mb-6">
                <span class="text-white font-bold text-2xl">P</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Добре дошли в Projify!</h1>
            <p class="text-gray-600 mb-8 text-sm leading-relaxed">
                Система за управление на проекти и задачи. Започни да организираш работата си още днес.
            </p>
            <div class="flex gap-3 justify-center">
                <a href="index.php?url=auth/login" 
                   class="px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    Вход
                </a>
                <a href="index.php?url=auth/register" 
                   class="px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Регистрация
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
