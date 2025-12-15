<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Админ панел</h1>
            <p class="mt-1 text-sm text-gray-500">Управление на потребители</p>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Потребител</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Роля</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Проекти</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Задачи</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $userData): 
                        $user = $userData['user'];
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <a href="index.php?url=admin/user&id=<?php echo $user['id']; ?>" 
                                           class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $userData['projects_count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $userData['tasks_count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="index.php?url=admin/user&id=<?php echo $user['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                        Виж детайли
                                    </a>
                                    <?php if ($user['role'] !== 'admin' && $user['id'] != Session::get('user_id')): ?>
                                        <span class="text-gray-300">|</span>
                                        <form method="POST" action="index.php?url=admin/delete" 
                                              onsubmit="return confirm('Сигурни ли сте, че искате да изтриете този потребител? Това ще изтрие всички негови проекти и ще премахне назначенията на задачите му.');" 
                                              class="inline">
                                            <?php echo CSRF::getTokenField(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium transition-colors">
                                                Изтрий
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
