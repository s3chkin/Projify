<?php

// Задаване на базов път
$basePath = dirname(__DIR__);
define('BASE_PATH', $basePath);

// Промяна на текущата директория, за да работят относителните пътища в моделите
chdir($basePath);

// Зареждане на autoloader (ако съществува)
if (file_exists($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
}

// Зареждане на основните класове
require_once $basePath . '/app/core/Database.php';
require_once $basePath . '/app/core/Model.php';
require_once $basePath . '/app/core/Session.php';
require_once $basePath . '/app/core/CSRF.php';
require_once $basePath . '/app/core/Controller.php';
require_once $basePath . '/app/core/Router.php';

// Задаване на тестова среда
define('TESTING', true);
