<?php
namespace App\Controllers;

use App\Controller;
use App\Models\SubjectModel;
use App\Database as AppDatabase;

class SubjectController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new SubjectModel(AppDatabase::getConnection());
    }

    public function getSubject()
    {
        $subject = $_GET['subject'] ?? null;

        if (!$subject) {
            $this->jsonResponse(['error' => 'Subject is required'], 400);
            return;
        }

        try {
            $subjects = $this->model->getSubjects($subject);
            $this->jsonResponse($subjects);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch subject schedule', 'details' => $e->getMessage()], 500);
        }
    }
}
