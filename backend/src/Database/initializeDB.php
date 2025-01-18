<?php

namespace App\Database;

require_once __DIR__ . '/Database.php';

use App\Database;

class InitializeDB
{
    public static function run()
    {
        try {
            $db = Database::getConnection();

            $db->exec("
            CREATE TABLE IF NOT EXISTS schedules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_number TEXT NOT NULL,
                classroom TEXT NOT NULL,
                subject TEXT NOT NULL,
                teacher TEXT NOT NULL,
                start_time TEXT NOT NULL,
                end_time TEXT NOT NULL
            )
        ");

            $db->exec("
            CREATE TABLE IF NOT EXISTS subjects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item TEXT NOT NULL
            )
        ");

            $db->exec("
            CREATE TABLE IF NOT EXISTS teachers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item TEXT NOT NULL
            )
        ");

            $db->exec("
            CREATE TABLE IF NOT EXISTS classrooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item TEXT NOT NULL,
                building TEXT NOT NULL
            )
        ");

        } catch (\PDOException $e) {
            echo "Error while creating tables: " . $e->getMessage();
        }
    }
}
