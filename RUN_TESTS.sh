#!/bin/bash

echo "========================================"
echo "Стартиране на Unit тестове за Projify"
echo "========================================"
echo ""

# Проверка дали PHPUnit.phar съществува
if [ ! -f "phpunit.phar" ]; then
    echo "ГРЕШКА: phpunit.phar не е намерен!"
    echo "Моля, изтегли PHPUnit от: https://phar.phpunit.de/phpunit-10.phar"
    read -p "Натисни Enter за изход"
    exit 1
fi

# Проверка дали PHP съществува
PHP_PATH="/Applications/XAMPP/xamppfiles/bin/php"
if [ ! -f "$PHP_PATH" ]; then
    # Опитваме се с обикновения php
    PHP_PATH="php"
    if ! command -v php &> /dev/null; then
        echo "ГРЕШКА: PHP не е намерен!"
        read -p "Натисни Enter за изход"
        exit 1
    fi
fi

echo "Стартиране на тестовете..."
echo ""

$PHP_PATH phpunit.phar --configuration phpunit-phar.xml tests/Core/

echo ""
echo "========================================"
echo "Тестовете са завършени!"
echo "========================================"
read -p "Натисни Enter за изход"

