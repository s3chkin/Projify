<?php
require_once "../app/models/ProjectMember.php";
require_once "../app/models/User.php";
require_once "../app/models/Task.php";

$projectMemberModel = new ProjectMember();
$userModel = new User();
$taskModel = new Task();

$members = $projectMemberModel->getByProject($project['id']);
$owner = $userModel->getById($project['owner_id']);
$tasks = $taskModel->getByProject($project['id']);
$currentUserId = Session::get('user_id');
$isOwner = $project['owner_id'] == $currentUserId;
$isAdmin = Session::get('user_role') === 'admin';
$canManage = $isOwner || $isAdmin;
?>

<div class="max-w-6xl mx-auto space-y-6">
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
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($project['name']); ?>
                    <?php if (isset($project['status']) && $project['status'] === 'completed'): ?>
                        <span class="ml-3 px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-700">Завършен</span>
                    <?php endif; ?>
                </h1>
                <p class="mt-1 text-sm text-gray-500">ID: #<?php echo $project['id']; ?></p>
            </div>
            <div class="flex gap-2">
                <?php if ($canManage): ?>
                    <?php 
                    require_once "../app/models/Project.php";
                    $projectModel = new Project();
                    $canComplete = !isset($project['status']) || $project['status'] !== 'completed';
                    $canBeCompleted = $projectModel->canBeCompleted($project['id']);
                    ?>
                    <?php if ($canComplete && $canBeCompleted): ?>
                    <form method="POST" action="index.php?url=project/complete" class="inline">
                        <?php echo CSRF::getTokenField(); ?>
                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                                onclick="return confirm('Сигурен ли си, че искаш да завършиш този проект? Всички задачи трябва да са завършени.');">
                            ✓ Завърши проект
                        </button>
                    </form>
                    <?php elseif ($canComplete && !$canBeCompleted): ?>
                    <button type="button" 
                            class="px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed"
                            title="Не всички задачи са завършени">
                        ⚠ Не може да се завърши
                    </button>
                    <?php else: ?>
                    <form method="POST" action="index.php?url=project/reopen" class="inline">
                        <?php echo CSRF::getTokenField(); ?>
                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                        <button type="submit" 
                                class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                            ↻ Отвори отново
                        </button>
                    </form>
                    <?php endif; ?>
                    <a href="index.php?url=project/edit&id=<?php echo $project['id']; ?>" 
                       class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Редактирай
                    </a>
                <?php endif; ?>
                <a href="index.php?url=task/index&project_id=<?php echo $project['id']; ?>" 
                   class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Виж задачи
                </a>
                <a href="index.php?url=project/index" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                    Назад
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <div class="text-sm text-blue-600 font-medium mb-1">Собственик</div>
                <div class="text-lg font-semibold text-blue-900">
                    <?php echo htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']); ?>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                <div class="text-sm text-green-600 font-medium mb-1">Членове</div>
                <div class="text-lg font-semibold text-green-900"><?php echo count($members); ?></div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                <div class="text-sm text-purple-600 font-medium mb-1">Задачи</div>
                <div class="text-lg font-semibold text-purple-900"><?php echo count($tasks); ?></div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php 
            require_once "../app/models/ProjectMember.php";
            $pmModel = new ProjectMember();
            $canAccess = ($project['owner_id'] == $currentUserId) || $isAdmin || $pmModel->isMember($project['id'], $currentUserId);
            if ($canAccess): 
            ?>
                <a href="index.php?url=task/create&project_id=<?php echo $project['id']; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Добави задача
                </a>
            <?php endif; ?>
            <?php if ($canManage): ?>
                <a href="index.php?url=project/members&id=<?php echo $project['id']; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Управление на членове
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Задачи на проекта</h2>
            <a href="index.php?url=task/index&project_id=<?php echo $project['id']; ?>" 
               class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Виж всички →
            </a>
        </div>
        <?php if (empty($tasks)): ?>
            <div class="text-center py-8">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="text-gray-500 mb-4">Все още няма задачи в този проект</p>
                <?php 
                $pmModel = new ProjectMember();
                $canAccess = ($project['owner_id'] == $currentUserId) || $isAdmin || $pmModel->isMember($project['id'], $currentUserId);
                if ($canAccess): 
                ?>
                    <a href="index.php?url=task/create&project_id=<?php echo $project['id']; ?>" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Създай първата задача
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Заглавие</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Статус</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Назначен на</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Крайна дата</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Приоритет</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($tasks as $task): ?>
                            <?php
                            $isOverdue = $task['due_date'] && strtotime($task['due_date']) < time() && $task['status_name'] != 'Done';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo $isOverdue ? 'bg-red-50/50' : ''; ?>">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                       class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo ($task['status_name'] == 'Done') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($task['first_name'] && $task['last_name']): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                                <?php echo strtoupper(substr($task['first_name'], 0, 1) . substr($task['last_name'], 0, 1)); ?>
                                            </div>
                                            <span class="text-sm text-gray-700">
                                                <?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">Не назначен</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm <?php echo $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600'; ?>">
                                    <?php if ($task['due_date']): ?>
                                        <?php echo date('d.m.Y', strtotime($task['due_date'])); ?>
                                        <?php if ($isOverdue): ?>
                                            <span class="ml-2 text-xs text-red-600">(Просрочена)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php
                                    $priorityColors = [
                                        1 => 'bg-red-100 text-red-700',
                                        2 => 'bg-orange-100 text-orange-700',
                                        3 => 'bg-yellow-100 text-yellow-700',
                                        4 => 'bg-green-100 text-green-700'
                                    ];
                                    $priorityText = ['1' => 'Много висок', '2' => 'Висок', '3' => 'Среден', '4' => 'Нисък'];
                                    $priority = $task['priority'] ?? null;
                                    ?>
                                    <?php if ($priority): ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo $priorityColors[$priority] ?? 'bg-gray-100 text-gray-700'; ?>">
                                            <?php echo $priorityText[$priority] ?? 'Среден'; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <?php if ($task['status_name'] != 'Done' && $canAccess): ?>
                                        <form method="POST" action="index.php?url=task/complete" class="inline">
                                            <?php echo CSRF::getTokenField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                            <input type="hidden" name="redirect" value="index.php?url=project/show&id=<?php echo $project['id']; ?>">
                                            <button type="submit" 
                                                    class="text-blue-600 hover:text-blue-700 font-medium transition-colors"
                                                    title="Завърши задача"
                                                    onclick="return confirm('Сигурен ли си, че искаш да завършиш тази задача?');">
                                                ✓
                                            </button>
                                        </form>
                                        <span class="text-gray-300">|</span>
                                        <?php endif; ?>
                                        <a href="index.php?url=task/show&id=<?php echo $task['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                            Виж
                                        </a>
                                        <span class="text-gray-300">|</span>
                                        <a href="index.php?url=task/edit&id=<?php echo $task['id']; ?>" 
                                           class="text-green-600 hover:text-green-700 font-medium transition-colors">
                                            Редактирай
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Членове на проекта</h2>
        <?php if (empty($members)): ?>
            <p class="text-gray-500 text-center py-8">Все още няма членове (освен собственика).</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($members as $member): ?>
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($member['email']); ?></div>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full <?php echo ($member['actual_role'] ?? $member['role']) === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                            <?php echo ($member['actual_role'] ?? $member['role']) === 'owner' ? 'Собственик' : 'Член'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

