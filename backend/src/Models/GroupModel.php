<?php
namespace App\Models;

use PDO;
use PDOException;

class GroupModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insertGroup(string $group): bool
    {
        try {
            $query = $this->db->prepare("INSERT OR IGNORE INTO groups (item) VALUES (:group)");
            $query->bindValue(':group', $group, PDO::PARAM_STR);
            $query->execute();
            return true;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public function getGroupsByStudentNumber(string $number): array
    {
        try {
            $query = $this->db->prepare("
              SELECT groupNumber from students
              WHERE item LIKE :number
            ");
            $query->bindValue(':number', $number, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
}