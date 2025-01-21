<?php
namespace App\Controllers;

use function App\Utils\getSemesterDates;
use App\Controller;
use App\Database as AppDatabase;

require_once __DIR__ . '/../Utils/GetCurrentSemester.php';
//TODO: fix the reload page error
class ScheduleController extends Controller
{
    private $db;

    public function __construct() {
        $this->db = AppDatabase::getConnection();
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    }
    public function InsertDataToDB($number) {
        try {
            $semesterDates = getSemesterDates();

        $_GET['start'] = $semesterDates->start;
        $_GET['end'] = $semesterDates->end;
        $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . http_build_query($_GET);


        $data = $this->httpGet($apiUrl);
        
        if ($data === false) {
            throw new \Exception('Failed to fetch data');
        }
        
        if ($data === null) {
            throw new \Exception('Failed to decode JSON');
        }
        // // NOT WORTH IT
        // Okay, maybe it is worth it

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
        $studentNumber = $number;

        $groupQuery = $this->db->prepare("INSERT OR IGNORE INTO groups (item) VALUES (:group)");
        $studentQuery = $this->db->prepare("INSERT OR IGNORE INTO students (item, groupNumber) VALUES (:number, :group)");
        
        foreach ($uniqueGroups as $group) {
            try {
                $groupQuery->bindValue(':group', $group, \PDO::PARAM_STR);
                $groupQuery->execute();

                $studentQuery->bindValue(':number', $studentNumber, \PDO::PARAM_STR);
                $studentQuery->bindValue(':group', $group, \PDO::PARAM_STR);
                $studentQuery->execute();
            } catch (\PDOException $e) {
                // error_log('Database error: ' . $e->getMessage()); 
                $this->jsonResponse(['error' => 'Database operation failed', 'details' => $e->getMessage()], 500);
                return;
            }
        }
        return;
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()], 500);
        }
    }

    public function getDataFromDB($groups, $startDate, $endDate) {
        $schedule = [];
    
        foreach ($groups as $groupRow) {
            $group = $groupRow['groupNumber'];

            $scheduleQuery = $this->db->prepare("
                SELECT * 
                FROM schedule 
                WHERE groupName = :group AND start >= :start AND end <= :end
            ");
            $scheduleQuery->bindValue(':group', $group, \PDO::PARAM_STR);
            $scheduleQuery->bindValue(':start', $startDate, \PDO::PARAM_STR);
            $scheduleQuery->bindValue(':end', $endDate, \PDO::PARAM_STR);
            $scheduleQuery->execute();

            $groupSchedule = $scheduleQuery->fetchAll(\PDO::FETCH_ASSOC);
            
            if (!empty($groupSchedule)) {
                $schedule = array_merge($schedule, $groupSchedule); 
            }
        }
        $this->jsonResponse($schedule);
    }

    public function getSchedule()
    {
        if (empty($_GET)) {
            $this->jsonResponse(['error' => 'At least one query parameter is required'], 400);
        } 
        $number = $_GET['number'] ?? null;
        $classroom = $_GET['room'] ?? null;
        // $building = $_GET['building'] ?? null;
        $teacher = $_GET['teacher'] ?? null;
        $startDate = $_GET['start'] ?? null;
        $endDate = $_GET['end'] ?? null;
        $subject = $_GET['subject'] ?? null;
        if ($subject !== null) {
            $words = explode(' ', $subject);
            $subject = implode(' ', array_slice($words, 0, 2));
        }

        try {
            if (!$this->db) {
                throw new \Exception('Failed to connect to the database');
            }
            if ($number) {
                $studentQuery = $this->db->prepare("SELECT groupNumber FROM students WHERE item = :number");
                $studentQuery->bindValue(':number', $number , \PDO::PARAM_STR);
                $studentQuery->execute();
                $groups = $studentQuery->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($groups)) {
                   $this->getDataFromDB($groups, $startDate, $endDate);
                } else {
                    $this->InsertDataToDB($number);

                    $studentQuery = $this->db->prepare("SELECT groupNumber FROM students WHERE item = :number");
                    $studentQuery->bindValue(':number', $number , \PDO::PARAM_STR);
                    $studentQuery->execute();
                    $groups = $studentQuery->fetchAll(\PDO::FETCH_ASSOC);
    
                    $this->getDataFromDB($groups, $startDate, $endDate);
                }
                return;
            } else {
                $queryParts = [];
                $params = [];
                
                if ($teacher !== null) {
                    $queryParts[] = "worker LIKE :teacher";
                    $params[':teacher'] = '%' . $teacher . '%';
                }
                
                if ($subject !== null) {
                    $queryParts[] = "title LIKE :subject";
                    $params[':subject'] = '%' . $subject . '%';
                }
                
                if ($classroom !== null) {
                    $queryParts[] = "room LIKE :classroom";
                    $params[':classroom'] = '%' . $classroom . '%';
                }

                if ($startDate !== null) {
                    $queryParts[] = "start >= :start";
                    $params[':start'] = $startDate;
                }

                if ($endDate !== null) {
                    $queryParts[] = "end <= :end";
                    $params[':end'] = $endDate;
                }
                
                $sql = "SELECT * FROM schedule";
                if (!empty($queryParts)) {
                    $sql .= " WHERE " . implode(" AND ", $queryParts);
                }
                
                $query = $this->db->prepare($sql);
                
                foreach ($params as $key => $value) {
                    $query->bindValue($key, $value, \PDO::PARAM_STR);
                }
                
                $query->execute();
                $schedule = $query->fetchAll(\PDO::FETCH_ASSOC);
                $this->jsonResponse($schedule);
                return;
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Unable to fetch schedule', 'details' => $e->getMessage()], 500);
        }
    }
}