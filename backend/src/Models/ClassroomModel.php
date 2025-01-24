<?php
namespace App\Models;

use PDO;

class ClassroomModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getClassrooms($room, $building = null)
    {
        try {
            if ($building) {
                $query = $this->db->prepare("SELECT item FROM classrooms WHERE item LIKE :room AND building LIKE :building");
                $query->bindValue(':room', '%' . $room . '%', PDO::PARAM_STR);
                $query->bindValue(':building', '%' . $building . '%', PDO::PARAM_STR);
            } else {
                $query = $this->db->prepare("SELECT item FROM classrooms WHERE item LIKE :room");
                $query->bindValue(':room', '%' . $room . '%', PDO::PARAM_STR);
            }

            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
}
