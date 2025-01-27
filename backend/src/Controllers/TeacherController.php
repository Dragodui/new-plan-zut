<?php
namespace App\Controllers;

use App\Controller;
use App\Models\TeacherModel;
use App\Database as AppDatabase;

class TeacherController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new TeacherModel(AppDatabase::getConnection());
    }

    public function getTeacher()
    {
        $teacher = $_GET['teacher'] ?? null;

        if (!$teacher) {
            $this->jsonResponse(['error' => 'Teacher is required'], 400);
            return;
        }

        try {
            $teachers = TeacherModel::findBy(AppDatabase::getConnection(), ['item' => $teacher]);
            $this->jsonResponse($teachers);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch teacher schedule', 'details' => $e->getMessage()], 500);
        }
    }
}