<?php
namespace App\Models;

use PDO;
use PDOException;

class ScheduleModel
{
    private $db;
    private $id;
    private $groupName;
    private $worker;
    private $room;
    private $title;
    private $start;
    private $end;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getWorker(): ?string
    {
        return $this->worker;
    }

    public function setWorker(string $worker): void
    {
        $this->worker = $worker;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(string $room): void
    {
        $this->room = $room;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(string $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(string $end): void
    {
        $this->end = $end;
    }

    public function save(): bool
    {
        try {
            if ($this->id) {
                $query = $this->db->prepare("
                    UPDATE schedule 
                    SET groupName = :groupName, worker = :worker, room = :room, title = :title, start = :start, end = :end 
                    WHERE id = :id
                ");
                $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            } else {
                $query = $this->db->prepare("
                    INSERT INTO schedule (groupName, worker, room, title, start, end) 
                    VALUES (:groupName, :worker, :room, :title, :start, :end)
                ");
            }

            $query->bindValue(':groupName', $this->groupName, PDO::PARAM_STR);
            $query->bindValue(':worker', $this->worker, PDO::PARAM_STR);
            $query->bindValue(':room', $this->room, PDO::PARAM_STR);
            $query->bindValue(':title', $this->title, PDO::PARAM_STR);
            $query->bindValue(':start', $this->start, PDO::PARAM_STR);
            $query->bindValue(':end', $this->end, PDO::PARAM_STR);
            $query->execute();

            if (!$this->id) {
                $this->id = $this->db->lastInsertId(); 
            }

            return true;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public static function find(PDO $db, int $id): ?self
    {
        try {
            $query = $db->prepare("SELECT * FROM schedule WHERE id = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();

            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            $schedule = new self($db);
            $schedule->id = $data['id'];
            $schedule->groupName = $data['groupName'];
            $schedule->worker = $data['worker'];
            $schedule->room = $data['room'];
            $schedule->title = $data['title'];
            $schedule->start = $data['start'];
            $schedule->end = $data['end'];

            return $schedule;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public static function findBy(PDO $db, array $criteria): array
    {
        try {
            $sql = "SELECT * FROM schedule WHERE 1=1";
            $params = [];
    
            if (isset($criteria['groupName'])) {
                $sql .= " AND groupName = :groupName";
                $params[':groupName'] = $criteria['groupName'];
            }
    
            if (isset($criteria['worker'])) {
                $sql .= " AND worker LIKE :worker";
                $params[':worker'] = '%' . $criteria['worker'] . '%';
            }
    
            if (isset($criteria['room'])) {
                $sql .= " AND room LIKE :room";
                $params[':room'] = '%' . $criteria['room'] . '%';
            }
    
            if (isset($criteria['title'])) {
                $sql .= " AND title LIKE :title";
                $params[':title'] = '%' . $criteria['title'] . '%';
            }
    
            if (isset($criteria['building'])) {
                $sql .= " AND room LIKE :building";
                $params[':building'] = '%' . $criteria['building'] . '%';
            }
    
            if (isset($criteria['start'])) {
                $sql .= " AND start >= :start";
                $params[':start'] = $criteria['start'];
            }
    
            if (isset($criteria['end'])) {
                $sql .= " AND end <= :end";
                $params[':end'] = $criteria['end'];
            }
    
            $query = $db->prepare($sql);
    
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value, PDO::PARAM_STR);
            }
    
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }
    
}