# Прехвърляне на Projify към MacBook

## Стъпка 1: Прехвърляне на файловете

### Вариант А: Чрез Git (Препоръчително)
```bash
cd ~/htdocs  # или /Applications/XAMPP/htdocs
git clone https://github.com/s3chkin/Projify.git
cd Projify
```

### Вариант Б: Чрез USB/Cloud
1. Архивирай проекта на Windows (ZIP)
2. Прехвърли архива на MacBook
3. Разархивирай в `/Applications/XAMPP/htdocs/Projify` или `~/htdocs/Projify`

## Стъпка 2: Настройка на базата данни

### 2.1. Стартирай MySQL в XAMPP
1. Отвори XAMPP Control Panel
2. Стартирай Apache и MySQL

### 2.2. Провери порта на MySQL
**ВАЖНО:** На Mac XAMPP обикновено използва порт **3306**, а не 3307!

Провери порта:
1. Отвори Terminal
2. Изпълни: `lsof -i :3306` или `lsof -i :3307`
3. Виж кой порт използва MySQL

### 2.3. Промени порта в конфигурацията (ако е нужно)

Ако MySQL използва порт 3306 на Mac, промени в 2 файла:

**1. database/config.php:**
```php
$port = 3306; // Промени от 3307 на 3306
```

**2. app/core/Database.php:**
```php
$port = "3306"; // Промени от "3307" на "3306"
```

### 2.4. Създай базата данни
1. Отвори phpMyAdmin: http://localhost/phpmyadmin
2. Създай нова база данни с име `projify`
3. Избери кодиране: `utf8mb4_general_ci`

### 2.5. Изпълни SQL скриптовете
В phpMyAdmin изпълни в следния ред:

1. **Създаване на таблици:**
   - Отвори в браузъра: `http://localhost/Projify/database/create_tables.php`
   - Или копирай SQL заявките от файла и ги изпълни в phpMyAdmin

2. **Добавяне на данни:**
   - Отвори в браузъра: `http://localhost/Projify/database/seeder.php`
   - Или копирай SQL заявките от файла и ги изпълни в phpMyAdmin

3. **Stored Procedures (опционално):**
   - Отвори `database/stored_procedures.sql` в phpMyAdmin
   - Изпълни всички заявки

## Стъпка 3: Настройка на конфигурацията

### 3.1. Провери database/config.php
Отвори `database/config.php` и провери настройките:

```php
$host = 'localhost';
$port = 3306;  // На Mac обикновено е 3306, не 3307!
$user = 'root';
$password = '';  // Обикновено празно на XAMPP
```

### 3.2. Провери app/core/Database.php
Отвори `app/core/Database.php` и провери настройките:

```php
$port = "3306";  // На Mac обикновено е 3306, не 3307!
```

### 3.3. Провери пътищата
На MacBook пътищата са различни:
- Windows: `C:\xampp\htdocs\Projify`
- Mac: `/Applications/XAMPP/htdocs/Projify` или `~/htdocs/Projify`

Провери дали всички `require_once` пътища работят.

## Стъпка 4: Тестване

### 4.1. Отвори проекта в браузъра
```
http://localhost/Projify/public/
```

Или ако си в `/Applications/XAMPP/htdocs/Projify`:
```
http://localhost/Projify/public/
```

### 4.2. Провери дали работи
- Регистрация
- Вход
- Създаване на проект
- Създаване на задача

## Стъпка 5: Допълнителни неща (ако са нужни)

### PHPUnit (за unit тестове)
Ако искаш да използваш unit тестовете:

**Вариант А: Чрез Composer (Препоръчително)**
```bash
cd ~/htdocs/Projify
composer install
vendor/bin/phpunit --configuration phpunit-phar.xml tests/Core/
```

**Вариант Б: Директно PHPUnit PHAR**
```bash
cd ~/htdocs/Projify
php phpunit.phar --configuration phpunit-phar.xml tests/Core/
```

**Вариант В: Чрез скрипт (ако работи на Mac)**
```bash
chmod +x RUN_TESTS.bat  # На Mac може да не работи .bat файл
# По-добре използвай командата директно
```

### Composer (ако нямаш)
```bash
# Инсталирай Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## Стъпка 6: Проблеми и решения

### Проблем: Не мога да се свържа с базата данни
**Решение:**
- Провери дали MySQL е стартиран в XAMPP
- Провери порта (3306 или 3307) - на Mac обикновено е 3306
- Провери настройките в `database/config.php` и `app/core/Database.php`
- Провери дали базата `projify` съществува

### Проблем: Пътищата не работят
**Решение:**
- На Mac използвай `/Applications/XAMPP/htdocs/Projify`
- Или създай символна връзка:
```bash
ln -s ~/htdocs/Projify /Applications/XAMPP/htdocs/Projify
```

### Проблем: PHP версия
**Решение:**
- Провери PHP версията: `/Applications/XAMPP/xamppfiles/bin/php -v`
- Трябва да е PHP 8.0 или по-нова
- XAMPP за Mac обикновено включва PHP 8.x

### Проблем: Права на достъп
**Решение:**
```bash
chmod -R 755 ~/htdocs/Projify
chmod -R 777 ~/htdocs/Projify/app/views  # Ако има проблеми с кеша
```

### Проблем: RUN_TESTS.bat не работи на Mac
**Решение:**
На Mac използвай командата директно:
```bash
/Applications/XAMPP/xamppfiles/bin/php phpunit.phar --configuration phpunit-phar.xml tests/Core/
```

Или създай shell скрипт:
```bash
#!/bin/bash
/Applications/XAMPP/xamppfiles/bin/php phpunit.phar --configuration phpunit-phar.xml tests/Core/
```

## Стъпка 7: Проверка на всичко

### Проверка списък:
- [ ] Файловете са прехвърлени
- [ ] Портът е проверен и променен (ако е нужно)
- [ ] Базата данни е създадена
- [ ] Таблиците са създадени
- [ ] Seeder данните са добавени
- [ ] Проектът се отваря в браузъра
- [ ] Можеш да се регистрираш
- [ ] Можеш да влезеш
- [ ] Можеш да създадеш проект
- [ ] Можеш да създадеш задача

## Бърз старт (ако вече имаш Git)

```bash
# 1. Клонирай проекта
cd ~/htdocs  # или /Applications/XAMPP/htdocs
git clone https://github.com/s3chkin/Projify.git
cd Projify

# 2. Провери и промени порта (ако е нужно)
# Отвори database/config.php и app/core/Database.php
# Промени порта от 3307 на 3306 (ако MySQL използва 3306)

# 3. Създай базата данни в phpMyAdmin
# 4. Отвори database/create_tables.php в браузъра
# 5. Отвори database/seeder.php в браузъра
# 6. Отвори http://localhost/Projify/public/
```

## Важни разлики Windows vs Mac

| Аспект | Windows | Mac |
|--------|---------|-----|
| Път | `C:\xampp\htdocs\Projify` | `/Applications/XAMPP/htdocs/Projify` |
| MySQL порт | 3307 | 3306 (обикновено) |
| PHP път | `C:\xampp\php\php.exe` | `/Applications/XAMPP/xamppfiles/bin/php` |
| Скриптове | `.bat` файлове | `.sh` файлове или директни команди |

## Заключение

На MacBook с XAMPP **НЕ ТРЯБВА** да инсталираш допълнителни неща, освен ако:
- Искаш да използваш Composer (за unit тестове) - опционално
- Искаш да използваш Git (за версиониране) - обикновено вече е инсталиран

**Основното е:**
1. ✅ Прехвърли файловете (чрез Git или USB/Cloud)
2. ✅ Провери и промени порта на MySQL (3306 вместо 3307)
3. ✅ Създай базата данни
4. ✅ Изпълни SQL скриптовете
5. ✅ Готово!

**Единствената важна промяна е портът на MySQL!**
