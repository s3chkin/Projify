<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h1>
            <p class="mt-1 text-sm text-gray-500">Детайли за задачата</p>
        </div>
        <div class="flex gap-2">
            <?php if (isset($canAccess) && $canAccess): ?>
            <?php if ($task['status_name'] != 'Done'): ?>
            <form method="POST" action="index.php?url=task/complete" class="inline">
                <?php echo CSRF::getTokenField(); ?>
                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                <input type="hidden" name="redirect" value="index.php?url=task/show&id=<?php echo $task['id']; ?>">
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                        onclick="return confirm('Сигурни ли сте, че искате да завършите тази задача?');">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Завърши задача
                </button>
            </form>
            <?php endif; ?>
            <a href="index.php?url=task/edit&id=<?php echo $task['id']; ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Редактирай
            </a>
            <a href="index.php?url=task/delete&id=<?php echo $task['id']; ?>" 
               onclick="return confirm('Сигурни ли сте, че искате да изтриете тази задача?');"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Изтрий
            </a>
            <?php endif; ?>
            <a href="index.php?url=task/index&project_id=<?php echo $task['project_id']; ?>" 
               class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Назад
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-6">
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Описание</h2>
            <p class="text-base text-gray-900 leading-relaxed"><?php echo htmlspecialchars($task['description'] ?? 'Няма описание'); ?></p>
        </div>

        <div class="grid grid-cols-2 gap-6 pt-6 border-t border-gray-200">
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Проект</h3>
                <p class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($task['project_name'] ?? 'N/A'); ?></p>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Статус</h3>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium <?php echo ($task['status_name'] == 'Done') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'; ?>">
                    <?php echo htmlspecialchars($task['status_name'] ?? 'N/A'); ?>
                </span>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Назначен на</h3>
                <p class="text-base font-semibold text-gray-900">
                    <?php 
                    if ($task['first_name'] && $task['last_name']) {
                        echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']);
                    } else {
                        echo '<span class="text-gray-400">Не назначен</span>';
                    }
                    ?>
                </p>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Приоритет</h3>
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
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium <?php echo $priorityColors[$priority] ?? 'bg-gray-100 text-gray-700'; ?>">
                    <?php echo $priorityText[$priority] ?? 'Среден'; ?>
                </span>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Начална дата</h3>
                <p class="text-base font-semibold text-gray-900">
                    <?php echo $task['start_date'] ? date('d.m.Y', strtotime($task['start_date'])) : '<span class="text-gray-400">Не е зададена</span>'; ?>
                </p>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Крайна дата</h3>
                <p class="text-base font-semibold <?php 
                    echo ($task['due_date'] && strtotime($task['due_date']) < time() && $task['status_name'] != 'Done') ? 'text-red-600' : 'text-gray-900'; 
                ?>">
                    <?php echo $task['due_date'] ? date('d.m.Y', strtotime($task['due_date'])) : '<span class="text-gray-400">Не е зададена</span>'; ?>
                    <?php if ($task['due_date'] && strtotime($task['due_date']) < time() && $task['status_name'] != 'Done'): ?>
                        <span class="ml-2 text-xs text-red-600">(Просрочена)</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Labels -->
        <div class="pt-6 border-t border-gray-200">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Labels</h3>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php if (!empty($taskLabels)): ?>
                    <?php foreach ($taskLabels as $label): ?>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
                            <?php echo htmlspecialchars($label['name']); ?>
                            <?php if (isset($canAccess) && $canAccess): ?>
                            <a href="index.php?url=task/removeLabel&task_id=<?php echo $task['id']; ?>&label_id=<?php echo $label['id']; ?>" 
                               class="hover:text-purple-900 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-sm text-gray-400">Няма labels</span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($allLabels) && isset($canAccess) && $canAccess): ?>
                <form method="POST" action="index.php?url=task/addLabel" class="flex gap-2">
                    <?php echo CSRF::getTokenField(); ?>
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <select name="label_id" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white">
                        <option value="">Добави label</option>
                        <?php foreach ($allLabels as $label): ?>
                            <?php 
                            $isAttached = false;
                            foreach ($taskLabels as $taskLabel) {
                                if ($taskLabel['id'] == $label['id']) {
                                    $isAttached = true;
                                    break;
                                }
                            }
                            ?>
                            <?php if (!$isAttached): ?>
                                <option value="<?php echo $label['id']; ?>">
                                    <?php echo htmlspecialchars($label['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        Добави
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Comments -->
        <div class="pt-6 border-t border-gray-200">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Коментари</h3>
            
            <!-- Форма за добавяне на коментар -->
            <form method="POST" action="index.php?url=comment/store" class="mb-6">
                <?php echo CSRF::getTokenField(); ?>
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <div class="flex gap-2">
                    <textarea name="text" 
                              rows="3"
                              required
                              class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                              placeholder="Напиши коментар..."></textarea>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors self-start">
                        Изпрати
                    </button>
                </div>
            </form>
            
            <!-- Списък с коментари -->
            <div class="space-y-4">
                <?php if (empty($comments)): ?>
                    <p class="text-sm text-gray-400 text-center py-4">Няма коментари</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                        <?php echo strtoupper(substr($comment['first_name'], 0, 1) . substr($comment['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($comment['author_id'] == Session::get('user_id') || Session::get('user_role') == 'admin'): ?>
                                    <div class="flex gap-2">
                                        <a href="index.php?url=comment/delete&id=<?php echo $comment['id']; ?>&task_id=<?php echo $task['id']; ?>" 
                                           class="text-red-600 hover:text-red-700 text-xs"
                                           onclick="return confirm('Сигурен ли си, че искаш да изтриеш този коментар?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($comment['text'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

