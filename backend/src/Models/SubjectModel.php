<?php
namespace App\Models;

use PDO;

class SubjectModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getSubjects($subject)
    {
        try {
            $query = $this->db->prepare("SELECT item FROM subjects WHERE item LIKE :subject");
            $query->bindValue(':subject', '%' . $subject . '%', PDO::PARAM_STR);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
}
