<?php
namespace App\Controllers;

use App\Controller;
use App\Models\ScheduleModel;
use App\Database as AppDatabase;
use function App\Utils\getSemesterDates;

require_once __DIR__ . '/../Utils/GetCurrentSemester.php';

class ScheduleController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new ScheduleModel(AppDatabase::getConnection());
    }

    public function getSchedule()
    {
        if (empty($_GET)) {
            $this->jsonResponse(['error' => 'At least one query parameter is required'], 400);
            return;
        }
    
        $number = $_GET['number'] ?? null;
        $classroom = $_GET['room'] ?? null;
        $teacher = $_GET['teacher'] ?? null;
        $startDate = $_GET['start'] ?? null;
        $endDate = $_GET['end'] ?? null;
        $subject = $_GET['subject'] ?? null;
    
        if ($subject !== null) {
            $words = explode(' ', $subject);
            $subject = implode(' ', array_slice($words, 0, 2));
        }
    
        try {
            if ($number) {
                $groups = $this->model->getGroupsByStudentNumber($number);
    
                if (empty($groups)) {
                    $semesterDates = getSemesterDates();
                    $_GET['start'] = $semesterDates->start;
                    $_GET['end'] = $semesterDates->end;
                    $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . http_build_query($_GET);
    
                    $data = $this->httpGet($apiUrl);
    
                    if ($data === false) {
                        throw new \Exception('Failed to fetch data');
                    }
    
                    $jsonData = json_decode($data);
                    $jsonData = array_filter($jsonData, function ($item) {
                        return !is_array($item) && isset($item->title);
                    });
    
                    $jsonData = array_values($jsonData);
    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('JSON decode error: ' . json_last_error_msg());
                    }
    
                    $uniqueGroups = [];
    
                    foreach ($jsonData as $item) {
                        if (!isset($item->group_name)) {
                            continue;
                        }
                        $groupName = $item->group_name;
                        if (!in_array($groupName, $uniqueGroups)) {
                            $uniqueGroups[] = $groupName;
                        }
                    }
    
                    $this->model->insertGroupsAndStudents($number, $uniqueGroups);
    
                    $groups = $this->model->getGroupsByStudentNumber($number);
                }
    
                $schedule = $this->model->getScheduleByGroups($groups, $startDate, $endDate, $teacher, $classroom, $subject);
                $this->jsonResponse($schedule);
            } else {
                $filters = [
                    'teacher' => $teacher,
                    'subject' => $subject,
                    'classroom' => $classroom,
                    'start' => $startDate,
                    'end' => $endDate,
                ];
    
                $schedule = $this->model->getScheduleByFilters($filters);
                $this->jsonResponse($schedule);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()], 500);
        }
    }
}