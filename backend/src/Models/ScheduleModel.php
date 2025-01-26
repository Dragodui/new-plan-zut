<?php
namespace App\Models;

use PDO;

class ScheduleModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insertGroupsAndStudents($number, $groups)
    {
        try {
            $groupQuery = $this->db->prepare("INSERT OR IGNORE INTO groups (item) VALUES (:group)");
            $studentQuery = $this->db->prepare("INSERT OR IGNORE INTO students (item, groupNumber) VALUES (:number, :group)");

            foreach ($groups as $group) {
                $groupQuery->bindValue(':group', $group, PDO::PARAM_STR);
                $groupQuery->execute();

                $studentQuery->bindValue(':number', $number, PDO::PARAM_STR);
                $studentQuery->bindValue(':group', $group, PDO::PARAM_STR);
                $studentQuery->execute();
            }
            return true;
        } catch (\PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public function getGroupsByStudentNumber($number)
    {
        $studentQuery = $this->db->prepare("SELECT groupNumber FROM students WHERE item = :number");
        $studentQuery->bindValue(':number', $number, PDO::PARAM_STR);
        $studentQuery->execute();
        return $studentQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduleByGroups($groups, $startDate, $endDate, $teacher = null, $classroom = null, $subject = null)
    {
        $schedule = [];
    
        foreach ($groups as $groupRow) {
            $group = $groupRow['groupNumber'];
    
            $sql = "
                SELECT * 
                FROM schedule 
                WHERE groupName = :group AND start >= :start AND end <= :end
            ";
    
            if ($teacher !== null) {
                $sql .= " AND worker LIKE :teacher";
            }
            if ($classroom !== null) {
                $sql .= " AND room LIKE :classroom";
            }
            if ($subject !== null) {
                $sql .= " AND title LIKE :subject";
            }
    
            $scheduleQuery = $this->db->prepare($sql);
    
            $scheduleQuery->bindValue(':group', $group, PDO::PARAM_STR);
            $scheduleQuery->bindValue(':start', $startDate, PDO::PARAM_STR);
            $scheduleQuery->bindValue(':end', $endDate, PDO::PARAM_STR);
    
            if ($teacher !== null) {
                $scheduleQuery->bindValue(':teacher', '%' . $teacher . '%', PDO::PARAM_STR);
            }
            if ($classroom !== null) {
                $scheduleQuery->bindValue(':classroom', '%' . $classroom . '%', PDO::PARAM_STR);
            }
            if ($subject !== null) {
                $scheduleQuery->bindValue(':subject', '%' . $subject . '%', PDO::PARAM_STR);
            }
    
            $scheduleQuery->execute();
    
            $groupSchedule = $scheduleQuery->fetchAll(PDO::FETCH_ASSOC);
    
            if (!empty($groupSchedule)) {
                $schedule = array_merge($schedule, $groupSchedule);
            }
        }
        return $schedule;
    }

    public function getScheduleByFilters($filters)
    {
        $queryParts = [];
        $params = [];

        if (isset($filters['teacher'])) {
            $queryParts[] = "worker LIKE :teacher";
            $params[':teacher'] = '%' . $filters['teacher'] . '%';
        }

        if (isset($filters['subject'])) {
            $queryParts[] = "title LIKE :subject";
            $params[':subject'] = '%' . $filters['subject'] . '%';
        }

        if (isset($filters['classroom'])) {
            $queryParts[] = "room LIKE :classroom";
            $params[':classroom'] = '%' . $filters['classroom'] . '%';
        }

        if (isset($filters['start'])) {
            $queryParts[] = "start >= :start";
            $params[':start'] = $filters['start'];
        }

        if (isset($filters['end'])) {
            $queryParts[] = "end <= :end";
            $params[':end'] = $filters['end'];
        }

        $sql = "SELECT * FROM schedule";
        if (!empty($queryParts)) {
            $sql .= " WHERE " . implode(" AND ", $queryParts);
        }

        $query = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $query->bindValue($key, $value, PDO::PARAM_STR);
        }

        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}