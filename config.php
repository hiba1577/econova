<?php

class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=vivora_db;charset=utf8mb4',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION'"
                    ]
                );
            } catch (Exception $e) {
                die('Error ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
