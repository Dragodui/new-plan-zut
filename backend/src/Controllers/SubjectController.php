<?php
namespace App\Controllers;

use App\Controller;

class SubjectController extends Controller
{
    public function getSubject()
    {
        $subject = $_GET['subject'] ?? null;

        if (!$subject) {
            $this->jsonResponse(['error' => 'Subject is required'], 400);
        }

        $apiUrl = "https://plan.zut.edu.pl/schedule.php?kind=subject&query=" . urlencode($subject);

        try {
            $data = $this->httpGet($apiUrl);
            if ($data === false) {
                throw new \Exception('Failed to fetch data');
            }
            $this->jsonResponse(json_decode($data, true));
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch subject schedule', 'details' => $e->getMessage()], 500);
        }
    }
}
