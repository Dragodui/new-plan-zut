<?php
namespace App\Models;

use PDO;

class TeacherModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getTeachers($teacher)
    {
        try {
            $query = $this->db->prepare("SELECT item FROM teachers WHERE item LIKE :teacher");
            $query->bindValue(':teacher', '%' . $teacher . '%', PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
}