<?php
namespace App\Controllers;

use App\Controller;

class ClassroomController extends Controller
{
    public function getClassroom()
    {
        $room = $_GET['room'] ?? null;

        if (!$room) {
            $this->jsonResponse(['error' => 'Room is required'], 400);
        }

        $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=room&query=" . urlencode($room);

        try {
            $data = $this->httpGet($apiUrl);
            if ($data === false) {
                throw new \Exception('Failed to fetch data');
            }
            $this->jsonResponse(json_decode($data, true));
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch classroom schedule', 'details' => $e->getMessage()], 500);
        }
    }
}



