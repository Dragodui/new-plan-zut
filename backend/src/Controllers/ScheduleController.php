<?php
namespace App\Controllers;

use App\Controller;

class ScheduleController extends Controller
{
    public function getSchedule()
    {
        if (empty($_GET)) {
            $this->jsonResponse(['error' => 'At least one query parameter is required'], 400);
        }

        $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . http_build_query($_GET);

        try {
            $data = $this->httpGet($apiUrl);
            if ($data === false) {
                throw new \Exception('Failed to fetch data');
            }
            $this->jsonResponse(json_decode($data, true));
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()], 500);
        }
    }
}