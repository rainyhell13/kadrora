<?php

define('DB_HOST',     'localhost');
define('DB_PORT',     '5432');
define('DB_NAME',     'kadrora');
define('DB_USER',     'postgres');
define('DB_PASSWORD', '1');
define('DB_CHARSET',  'utf8');

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::ensureDatabase();

            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$instance->exec("SET NAMES 'UTF8'");
            } catch (PDOException $e) {
                http_response_code(500);
                die('<h2>Ошибка подключения к базе данных</h2><pre>' . $e->getMessage() . '</pre>');
            }
        }
        return self::$instance;
    }

    private static function ensureDatabase(): void
    {
        $flagFile = __DIR__ . '/../database/.initialized';

        // Если флаг уже стоит — пропускаем
        if (file_exists($flagFile)) return;

        try {
            // Подключаемся к системной БД postgres
            $sys = new PDO(
                sprintf('pgsql:host=%s;port=%s;dbname=postgres', DB_HOST, DB_PORT),
                DB_USER, DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Создаём БД если не существует
            $exists = $sys->query(
                "SELECT 1 FROM pg_database WHERE datname = '" . DB_NAME . "'"
            )->fetch();

            if (!$exists) {
                $sys->exec("CREATE DATABASE " . DB_NAME . " WITH ENCODING='UTF8' TEMPLATE=template0");
            }

            // Подключаемся к kadrora и применяем схему
            $db = new PDO(
                sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME),
                DB_USER, DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $tableExists = $db->query(
                "SELECT 1 FROM information_schema.tables WHERE table_name='users'"
            )->fetch();

            if (!$tableExists) {
                $db->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/seed.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/groups.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/profile_features.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/bookmarks_documents.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/privacy_social.sql'));
                $db->exec(file_get_contents(__DIR__ . '/../database/moderation.sql'));
            }

            // Ставим флаг — больше не проверяем
            file_put_contents($flagFile, date('Y-m-d H:i:s'));

        } catch (PDOException $e) {
            http_response_code(500);
            die('<h2>Ошибка инициализации базы данных</h2><pre>' . $e->getMessage() . '</pre>');
        }
    }
}
