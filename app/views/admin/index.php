<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Админ панел</h1>
            <p class="mt-1 text-sm text-gray-500">Преглед на потребители, проекти и задачи</p>
        </div>
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

    <div class="grid gap-6">
        <?php foreach ($users as $userData): 
            $user = $userData['user'];
            $projects = $userData['projects'];
            $tasks = $userData['tasks'];
        ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </h2>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Роля</div>
                                <div class="text-sm font-semibold <?php echo $user['role'] === 'admin' ? 'text-red-600' : 'text-gray-700'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                </div>
                            </div>
                            <div class="px-4 py-2 bg-blue-100 rounded-lg">
                                <div class="text-xs text-gray-600">Проекти</div>
                                <div class="text-lg font-bold text-blue-700"><?php echo $userData['projects_count']; ?></div>
                            </div>
                            <div class="px-4 py-2 bg-green-100 rounded-lg">
                                <div class="text-xs text-gray-600">Задачи</div>
                                <div class="text-lg font-bold text-green-700"><?php echo $userData['tasks_count']; ?></div>
                            </div>
                            <?php if ($user['role'] !== 'admin' && $user['id'] != Session::get('user_id')): ?>
                                <form method="POST" action="index.php?url=admin/delete" onsubmit="return confirm('Сигурни ли сте, че искате да изтриете този потребител? Това ще изтрие всички негови проекти и ще премахне назначенията на задачите му.');" class="inline">
                                    <?php echo CSRF::getTokenField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                        Изтрий
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <?php if (!empty($projects)): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Проекти (<?php echo count($projects); ?>)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($projects as $project): ?>
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                                        <div class="flex items-center justify-between mb-2">
                                            <a href="index.php?url=project/show&id=<?php echo $project['id']; ?>" class="font-semibold text-gray-900 hover:text-blue-600">
                                                <?php echo htmlspecialchars($project['name']); ?>
                                            </a>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $project['role_type'] === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                                <?php echo $project['role_type'] === 'owner' ? 'Собственик' : 'Член'; ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">ID: <?php echo $project['id']; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p>Няма проекти</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tasks)): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                Задачи (<?php echo count($tasks); ?>)
                            </h3>
                            <div class="space-y-2">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" class="font-semibold text-gray-900 hover:text-green-600 block mb-1">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </a>
                                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                        </svg>
                                                        <?php echo htmlspecialchars($task['project_name'] ?? 'Без проект'); ?>
                                                    </span>
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-700">
                                                        <?php echo htmlspecialchars($task['status_name'] ?? 'Без статус'); ?>
                                                    </span>
                                                    <?php if ($task['due_date']): ?>
                                                        <span class="text-xs <?php echo strtotime($task['due_date']) < time() ? 'text-red-600 font-semibold' : 'text-gray-500'; ?>">
                                                            Крайна дата: <?php echo date('d.m.Y', strtotime($task['due_date'])); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <p>Няма задачи</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

