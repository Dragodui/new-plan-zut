<?php
namespace App;

use PDO;

class Database
{
    private static $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            if (!file_exists(__DIR__ . '/database.sqlite')) {
                touch(__DIR__ . '/database.sqlite');
            }
            self::$instance = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$instance;
    }
}
