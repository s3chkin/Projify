<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Управление на членове</h1>
                <p class="mt-1 text-sm text-gray-500">Проект: <strong class="text-gray-900"><?php echo htmlspecialchars($project['name']); ?></strong></p>
            </div>
            <a href="index.php?url=project/show&id=<?php echo $project['id']; ?>" 
               class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Назад към проекта
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg mb-6">
            <p class="text-sm text-green-700"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mb-6">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Текущи членове</h2>
        
        <?php if (empty($members)): ?>
            <p class="text-gray-500 text-center py-8">Все още няма членове в проекта (освен собственика).</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($members as $member): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($member['email']); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo ($member['actual_role'] ?? $member['role']) === 'owner' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                <?php echo ($member['actual_role'] ?? $member['role']) === 'owner' ? 'Собственик' : 'Член'; ?>
                            </span>
                            <?php if (($member['actual_role'] ?? $member['role']) !== 'owner'): ?>
                                <form method="POST" action="index.php?url=project/removeMember" 
                                      onsubmit="return confirm('Сигурни ли сте, че искате да премахнете <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?> от проекта?');">
                                    <?php echo CSRF::getTokenField(); ?>
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                    <button type="submit" 
                                            class="px-3 py-1 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                        Премахни
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($availableUsers)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Добави нов член</h2>
            <form method="POST" action="index.php?url=project/addMember" class="space-y-4">
                <?php echo CSRF::getTokenField(); ?>
                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Избери потребител
                    </label>
                    <select id="user_id" 
                            name="user_id" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white">
                        <option value="">-- Избери потребител --</option>
                        <?php foreach ($availableUsers as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" 
                        class="w-full px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    Добави към проекта
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-gray-500">Всички потребители вече са членове на проекта.</p>
        </div>
    <?php endif; ?>
</div>

