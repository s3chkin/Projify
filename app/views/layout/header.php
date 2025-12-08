<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projify - Управление на проекти</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            background-color: #333;
            color: white;
            padding: 1rem 0;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
        }
        nav a:hover {
            background-color: #555;
            border-radius: 3px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <h1>Projify</h1>
                <ul>
                    <?php if (Session::has('user_id')): ?>
                        <li><a href="index.php?url=home/index">Начало</a></li>
                        <li><a href="index.php?url=project/index">Проекти</a></li>
                        <li><a href="index.php?url=task/index">Задачи</a></li>
                        <li><span>Здравей, <?php echo htmlspecialchars(Session::get('user_name')); ?>!</span></li>
                        <li><a href="index.php?url=auth/logout">Изход</a></li>
                    <?php else: ?>
                        <li><a href="index.php?url=auth/login">Вход</a></li>
                        <li><a href="index.php?url=auth/register">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>

