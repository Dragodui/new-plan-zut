<?php
namespace App\Controllers;

use App\Controller;
use App\Database as AppDatabase;

class SubjectController extends Controller
{
    public function getSubject()
    {
        $subject = $_GET['subject'] ?? null;

        if (!$subject) {
            $this->jsonResponse(['error' => 'Subject is required'], 400);
        }

        try {
            $db  = AppDatabase::getConnection();
            $query = $db->prepare("SELECT item FROM subjects WHERE item LIKE :subject");
            $query->bindValue(':subject', '%' . $subject . '%', \PDO::PARAM_STR); 

            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch subject schedule', 'details' => $e->getMessage()], 500);
        }
    }
}