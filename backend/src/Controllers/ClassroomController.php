<?php
namespace App\Controllers;

use App\Controller;
use App\Database as AppDatabase;

class ClassroomController extends Controller
{
    public function getClassroom()
    {
        $room = $_GET['room'] ?? null;
        $building = $_GET['building'] ?? null;

        if (!$room) {
            $this->jsonResponse(['error' => 'Room is required'], 400);
            return;
        }

        try {
            $db  = AppDatabase::getConnection();
            if ($building) {
                
            $query = $db->prepare("SELECT item FROM classrooms WHERE item LIKE :room AND building LIKE :building");
            $query->bindValue(':room', '%' . $room . '%', \PDO::PARAM_STR);
            $query->bindValue(':building', '%' . $building . '%', \PDO::PARAM_STR);
            }
            else {
                $query = $db->prepare("SELECT item FROM classrooms WHERE item LIKE :room");
                $query->bindValue(':room', '%' . $room . '%', \PDO::PARAM_STR); 
            }

            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $this->jsonResponse($result);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch classroom schedule', 'details' => $e->getMessage()], 500);
        }
    }
}