<?php
namespace App\Controllers;

use App\Controller;
use App\Models\ScheduleModel;
use App\Models\GroupModel;
use App\Models\StudentModel;
use App\Database as AppDatabase;
use function App\Utils\getSemesterDates;

require_once __DIR__ . '/../Utils/GetCurrentSemester.php';

class ScheduleController extends Controller
{
    private $scheduleModel;
    private $groupModel;
    private $studentModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel(AppDatabase::getConnection());
        $this->groupModel = new GroupModel(AppDatabase::getConnection());
        $this->studentModel = new StudentModel(AppDatabase::getConnection());
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
        $building = $_GET['building'] ?? null;

        if ($subject !== null) {
            $words = explode(' ', $subject);
            $subject = implode(' ', array_slice($words, 0, 2));
        }

        try {
            if ($number) {
                $groups = $this->groupModel->getGroupsByStudentNumber($number);

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

                    foreach ($uniqueGroups as $group) {
                        $this->groupModel->insertGroup($group);
                        $this->studentModel->insertStudent($number, $group);
                    }

                    $groups = $this->groupModel->getGroupsByStudentNumber($number);
                }

                $schedule = [];
                foreach ($groups as $group) {
                    $criteria = [
                        'groupName' => $group['groupNumber'], 
                        'worker' => $teacher,
                        'room' => $classroom,
                        'title' => $subject,
                        'start' => $startDate,
                        'end' => $endDate,
                        'building' => $building, 
                    ];

                    $groupSchedule = ScheduleModel::findBy(AppDatabase::getConnection(), $criteria);
                    $schedule = array_merge($schedule, $groupSchedule);
                }

                $this->jsonResponse($schedule);
            } else {
                $filters = [
                    'worker' => $teacher,
                    'title' => $subject,
                    'room' => $classroom,
                    'building' => $building,
                    'start' => $startDate,
                    'end' => $endDate,
                ];

                $schedule = ScheduleModel::findBy(AppDatabase::getConnection(), $filters);
                $this->jsonResponse($schedule);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()], 500);
        }
    }
}