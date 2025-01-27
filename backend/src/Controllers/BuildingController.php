<?php
namespace App\Controllers;

use App\Controller;
use App\Models\BuildingModel;
use App\Database as AppDatabase;

class BuildingController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new BuildingModel(AppDatabase::getConnection());
    }

    public function getBuilding()
    {
        $building = $_GET['building'] ?? null;

        if (!$building) {
            $this->jsonResponse(['error' => 'Building is required'], 400);
            return;
        }

        try {
            $buildings = BuildingModel::findBy(AppDatabase::getConnection(), ['item' => $building]);
            $this->jsonResponse($buildings);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch building data', 'details' => $e->getMessage()], 500);
        }
    }
}