<?php if (Session::has('user_id')): ?>
    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 p-4 lg:p-6">
        <!-- Header Section -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Добре дошли, <?php echo htmlspecialchars(explode(' ', Session::get('user_name'))[0]); ?>!</h1>
                <p class="text-sm text-gray-600">Ето твоята програма за днес</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" placeholder="Търсене..." class="pl-9 pr-3 py-2 w-48 sm:w-56 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm transition-all shadow-sm">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <span class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm">Pro</span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Calendar -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900"><?php echo date('F Y'); ?></h2>
                        <div class="flex gap-1">
                            <button class="p-1.5 hover:bg-gray-100 rounded-md transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button class="p-1.5 hover:bg-gray-100 rounded-md transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Н</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">П</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">В</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">С</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">Ч</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">П</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">С</div>
                        <?php
                        $firstDay = date('w', mktime(0, 0, 0, date('m'), 1, date('Y')));
                        $daysInMonth = date('t');
                        $currentDay = date('j');
                        $firstDay = $firstDay == 0 ? 6 : $firstDay - 1;
                        
                        for ($i = 0; $i < $firstDay; $i++) {
                            echo '<div class="text-center py-1.5"></div>';
                        }
                        
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $isToday = ($day == $currentDay);
                            if ($isToday) {
                                echo '<div class="text-center py-1.5"><span class="inline-flex items-center justify-center w-7 h-7 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg text-xs font-semibold shadow-sm">' . $day . '</span></div>';
                            } else {
                                echo '<div class="text-center py-1.5"><span class="inline-flex items-center justify-center w-7 h-7 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-medium transition-colors cursor-pointer">' . $day . '</span></div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Urgent Tasks -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-900">Спешни задачи</h2>
                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-md text-xs font-medium">3</span>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-2.5 bg-red-50/50 hover:bg-red-50 rounded-lg border-l-3 border-red-500 transition-all cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-2 focus:ring-red-500 cursor-pointer">
                            <span class="flex-1 text-sm text-gray-800 font-medium">Завърши месечен отчет</span>
                            <span class="text-xs text-red-600 bg-red-100 px-2 py-0.5 rounded font-medium">Днес</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 bg-orange-50/50 hover:bg-orange-50 rounded-lg border-l-3 border-orange-500 transition-all cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-2 focus:ring-orange-500 cursor-pointer">
                            <span class="flex-1 text-sm text-gray-800 font-medium">Подписване на отчет</span>
                            <span class="text-xs text-orange-600 bg-orange-100 px-2 py-0.5 rounded font-medium">Днес</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 bg-yellow-50/50 hover:bg-yellow-50 rounded-lg border-l-3 border-yellow-500 transition-all cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 text-yellow-600 rounded border-gray-300 focus:ring-2 focus:ring-yellow-500 cursor-pointer">
                            <span class="flex-1 text-sm text-gray-800 font-medium">Ключова бележка за пазарен преглед</span>
                            <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded font-medium">Днес</span>
                        </label>
                    </div>
                </div>

                <!-- Project Directory -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-900">Проекти</h2>
                        <span class="text-xs text-gray-500">4 проекта</span>
                    </div>
                    <div class="space-y-1.5">
                        <a href="index.php?url=project/index" class="flex items-center gap-3 p-2.5 hover:bg-blue-50/50 rounded-lg transition-all group border border-transparent hover:border-blue-200">
                            <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm text-gray-800 font-medium group-hover:text-blue-700">Пазарни изследвания 2024</span>
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white shadow-sm"></div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-green-400 to-green-600 border-2 border-white shadow-sm"></div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-white shadow-sm"></div>
                            </div>
                        </a>
                        <a href="index.php?url=project/index" class="flex items-center gap-3 p-2.5 hover:bg-red-50/50 rounded-lg transition-all group border border-transparent hover:border-red-200">
                            <div class="p-2 bg-red-100 rounded-lg group-hover:bg-red-200 transition-colors">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm text-gray-800 font-medium group-hover:text-red-700">Нови предложения</span>
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-red-400 to-red-600 border-2 border-white shadow-sm"></div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 border-2 border-white shadow-sm"></div>
                            </div>
                        </a>
                        <a href="index.php?url=project/index" class="flex items-center gap-3 p-2.5 hover:bg-indigo-50/50 rounded-lg transition-all group border border-transparent hover:border-indigo-200">
                            <div class="p-2 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm text-gray-800 font-medium group-hover:text-indigo-700">Бранд спринтове</span>
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 border-2 border-white shadow-sm"></div>
                            </div>
                        </a>
                        <a href="index.php?url=project/index" class="flex items-center gap-3 p-2.5 hover:bg-pink-50/50 rounded-lg transition-all group border border-transparent hover:border-pink-200">
                            <div class="p-2 bg-pink-100 rounded-lg group-hover:bg-pink-200 transition-colors">
                                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <span class="flex-1 text-sm text-gray-800 font-medium group-hover:text-pink-700">Клиентско изживяване Q3</span>
                            <div class="flex -space-x-1.5">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-white shadow-sm"></div>
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white shadow-sm"></div>
                            </div>
                        </a>
                    </div>
                    <button class="mt-3 w-full py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-medium hover:from-blue-600 hover:to-blue-700 shadow-sm hover:shadow transition-all">+ Добави проект</button>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <!-- New Comments -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-900">Коментари</h2>
                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-md text-xs font-medium">2</span>
                    </div>
                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2.5 p-2.5 hover:bg-blue-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-blue-200">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">ИС</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-800 font-semibold mb-0.5">Иван С. в Пазарни изследвания</p>
                                <p class="text-xs text-gray-600 line-clamp-2">Намерих моята ключова бележка прикачена...</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-600 transition-colors flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="flex items-start gap-2.5 p-2.5 hover:bg-green-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-green-200">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">МР</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-800 font-semibold mb-0.5">Мария Р. в Пазарни изследвания</p>
                                <p class="text-xs text-gray-600 line-clamp-2">Добавих нови данни. Нека обсъдим...</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-green-600 transition-colors flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200 space-y-1.5">
                        <a href="#" class="flex items-center justify-between p-2 bg-blue-50/50 hover:bg-blue-100 rounded-lg transition-all group">
                            <span class="text-xs text-blue-800 font-medium">#Изследване Дизайн</span>
                            <svg class="w-3.5 h-3.5 text-blue-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        <a href="#" class="flex items-center justify-between p-2 bg-purple-50/50 hover:bg-purple-100 rounded-lg transition-all group">
                            <span class="text-xs text-purple-800 font-medium">#Стратегия SWOT</span>
                            <svg class="w-3.5 h-3.5 text-purple-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        <a href="#" class="flex items-center justify-between p-2 bg-green-50/50 hover:bg-green-100 rounded-lg transition-all group">
                            <span class="text-xs text-green-800 font-medium">#Операции Структура</span>
                            <svg class="w-3.5 h-3.5 text-green-600 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Team Directory -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-4 border border-gray-200/50">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-gray-900">Екип</h2>
                        <span class="text-xs text-gray-500">4 члена</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 p-2 hover:bg-blue-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-blue-200">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">МР</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 group-hover:text-blue-700">Мария Р.</p>
                                <p class="text-xs text-gray-500 truncate">Мениджър проекти</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2.5 p-2 hover:bg-green-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-green-200">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">ИС</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 group-hover:text-green-700">Иван С.</p>
                                <p class="text-xs text-gray-500 truncate">Ключов акаунт</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2.5 p-2 hover:bg-purple-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-purple-200">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">НВ</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 group-hover:text-purple-700">Нанси В.</p>
                                <p class="text-xs text-gray-500 truncate">Мениджър акаунти</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                        <div class="flex items-center gap-2.5 p-2 hover:bg-pink-50/50 rounded-lg transition-all cursor-pointer group border border-transparent hover:border-pink-200">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-pink-400 to-pink-600 flex-shrink-0 shadow-sm flex items-center justify-center text-white text-xs font-bold">ЯМ</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 group-hover:text-pink-700">Ян М.</p>
                                <p class="text-xs text-gray-500 truncate">Дигитален мениджър</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 p-6 flex items-center justify-center">
        <div class="text-center bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-8 max-w-md border border-gray-200/50">
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Добре дошли в Projify!</h1>
            <p class="text-gray-600 mb-6 text-sm">
                Моля, <a href="index.php?url=auth/login" class="text-blue-600 hover:text-blue-700 font-semibold underline decoration-2 underline-offset-2 transition-colors">влез</a> или 
                <a href="index.php?url=auth/register" class="text-blue-600 hover:text-blue-700 font-semibold underline decoration-2 underline-offset-2 transition-colors">се регистрирай</a>, 
                за да започнеш да използваш системата.
            </p>
        </div>
    </div>
<?php endif; ?>
