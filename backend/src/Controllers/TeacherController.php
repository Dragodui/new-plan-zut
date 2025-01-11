<?php
namespace App\Controllers;

use App\Controller;

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
            $data = $this->httpGet($apiUrl);
            if ($data === false) {
                throw new \Exception('Failed to fetch data');
            }
            $this->jsonResponse(json_decode($data, true));
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch teacher schedule', 'details' => $e->getMessage()], 500);
        }
    }
}