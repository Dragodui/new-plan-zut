<?php
namespace App\Controllers;

use App\Controller;
use App\Models\ClassroomModel;
use App\Database as AppDatabase;

class ClassroomController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new ClassroomModel(AppDatabase::getConnection());
    }

    public function getClassroom()
    {
        $room = $_GET['room'] ?? null;
        $building = $_GET['building'] ?? null;

        if (!$room) {
            $this->jsonResponse(['error' => 'Room is required'], 400);
            return;
        }

        try {
            $criteria = ['item' => $room];
            if ($building) {
                $criteria['building'] = $building;
            }

            $classrooms = ClassroomModel::findBy(AppDatabase::getConnection(), $criteria);
            $this->jsonResponse($classrooms);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch classroom schedule', 'details' => $e->getMessage()], 500);
        }
    }
}