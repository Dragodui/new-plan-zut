<?php
namespace App\Controllers;

use App\Controller;
use App\Database as AppDatabase;

class TeacherController extends Controller
{
    public function getTeacher()
    {
        $teacher = $_GET['teacher'] ?? null;

        if (!$teacher) {
            $this->jsonResponse(['error' => 'Teacher is required'], 400);
        }

        $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=teacher&query=" . urlencode($teacher);

        try {
            $db  = AppDatabase::getConnection();
            $query = $db->prepare("SELECT item FROM teachers WHERE item LIKE :teacher");
            $query->bindValue(':teacher', '%' . $teacher . '%', \PDO::PARAM_STR);

            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch teacher schedule', 'details' => $e->getMessage()], 500);
        }
    }
}
