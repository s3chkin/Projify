@echo off
echo ========================================
echo Стартиране на Unit тестове за Projify
echo ========================================
echo.

REM Проверка дали PHPUnit.phar съществува
if not exist "phpunit.phar" (
    echo ГРЕШКА: phpunit.phar не е намерен!
    echo Моля, изтегли PHPUnit от: https://phar.phpunit.de/phpunit-10.phar
    pause
    exit /b 1
)

REM Проверка дали PHP съществува
if not exist "C:\xampp\php\php.exe" (
    echo ГРЕШКА: PHP не е намерен в C:\xampp\php\php.exe
    pause
    exit /b 1
)

echo Стартиране на тестовете...
echo.

C:\xampp\php\php.exe phpunit.phar --configuration phpunit-phar.xml tests/Core/

echo.
echo ========================================
echo Тестовете са завършени!
echo ========================================
pause

