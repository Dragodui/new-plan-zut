<?php
namespace App\Controllers;

use App\Controller;
use App\Database as AppDatabase;
use \PDOException;

class ScheduleController extends Controller
{
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

        $apiUrl = "https://plan.zut.edu.pl/schedule_student.php?" . http_build_query($_GET);

        try {
            $db = AppDatabase::getConnection();
            if ($number) {
                $studentQuery = $db->prepare("SELECT groupNumber FROM students WHERE item = :number");
                $studentQuery->bindValue(':number', $number , \PDO::PARAM_STR);
                $studentQuery->execute();
                $groupName = $studentQuery->fetch(\PDO::FETCH_ASSOC);
                if ($groupName) {
                    $group = $groupName['groupNumber'];
                    $scheduleQuery = $db->prepare("SELECT * FROM schedule WHERE groupName = :group AND start >= :start AND end <= :end");
                    $scheduleQuery->bindValue(':group', $group , \PDO::PARAM_STR);
                    $scheduleQuery->bindValue(':start', $startDate, \PDO::PARAM_STR);
                    $scheduleQuery->bindValue(':end', $endDate, \PDO::PARAM_STR);
                    $scheduleQuery->execute();

                    $schedule = $scheduleQuery->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($schedule)) {
                        $this->jsonResponse($schedule);
                        return;
                    }
                } else {
                    $data = $this->httpGet($apiUrl);
                    if ($data === false) {
                        throw new \Exception('Failed to fetch data');
                    }
                    
                    if ($data === null) {
                        throw new \Exception('Failed to decode JSON');
                    }
                    // NOT WORTH IT

                    // $jsonData = json_decode($data);
                    // $dataLength = count($jsonData);
                    // if ($dataLength < 2) {
                    //    $secondItem = null;
                    // } else {
                    // $secondItem = $jsonData[1];   
                    // }
                    // if ($secondItem !== null) {
                    //     $currentGroup = $secondItem->group_name;
                    //     $groupQuery = $db->prepare("INSERT INTO groups (item) VALUES (:group)");
                    //     $studentQuery = $db->prepare("INSERT INTO students (item, groupNumber) VALUES (:number, :group)");

                    //     $groupQuery->bindValue(':group', $currentGroup, \PDO::PARAM_STR); 
                    //     $studentQuery->bindValue(':number', $number, \PDO::PARAM_STR);   
                    //     $studentQuery->bindValue(':group', $currentGroup, \PDO::PARAM_STR); 

                        // $groupQuery->execute();
                        // $studentQuery->execute();
                    // }

                    $this->jsonResponse(json_decode($data, true));
                    return;
                }
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
                
                $query = $db->prepare($sql);
                
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