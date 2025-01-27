<?php
namespace App\Models;

use PDO;
use PDOException;

class StudentModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insertStudent(string $number, string $group): bool
    {
        try {
            $query = $this->db->prepare("INSERT OR IGNORE INTO students (item, groupNumber) VALUES (:number, :group)");
            $query->bindValue(':number', $number, PDO::PARAM_STR);
            $query->bindValue(':group', $group, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
}