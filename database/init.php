<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

echo "=== Kadrora — Инициализация базы данных ===\n\n";

// Подключаемся к postgres (не к kadrora — она ещё не существует)
try {
    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=postgres', DB_HOST, DB_PORT),
        DB_USER, DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("❌ Не удалось подключиться к PostgreSQL: " . $e->getMessage() . "\n");
}

// Создаём БД если не существует
$exists = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '" . DB_NAME . "'")->fetch();
if ($exists) {
    echo "✔  База данных «" . DB_NAME . "» уже существует\n";
} else {
    $pdo->exec("CREATE DATABASE " . DB_NAME . " WITH ENCODING='UTF8' TEMPLATE=template0");
    echo "✔  База данных «" . DB_NAME . "» создана\n";
}

// Подключаемся к kadrora
try {
    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME),
        DB_USER, DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("❌ Не удалось подключиться к базе «" . DB_NAME . "»: " . $e->getMessage() . "\n");
}

// Проверяем — уже применена ли схема
$tableExists = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name='users'")->fetch();
if ($tableExists) {
    echo "✔  Схема уже применена, пропускаем\n";
} else {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "✔  Схема (таблицы, индексы, триггеры) создана\n";

    // Тестовые данные
    $sql = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($sql);
    echo "✔  Тестовые данные добавлены\n";
}

echo "\n✅ Готово! Открывай: http://localhost:8000\n";
echo "   Логин: ivan@example.com / password123\n\n";
