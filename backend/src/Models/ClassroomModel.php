<?php
namespace App\Models;

use PDO;
use PDOException;

class ClassroomModel
{
    private $db;
    private $id;
    private $item;
    private $building;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?string
    {
        return $this->item;
    }

    public function setItem(string $item): void
    {
        $this->item = $item;
    }

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(string $building): void
    {
        $this->building = $building;
    }

    public function save(): bool
    {
        try {
            if ($this->id) {
                $query = $this->db->prepare("UPDATE classrooms SET item = :item, building = :building WHERE id = :id");
                $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            } else {
                $query = $this->db->prepare("INSERT INTO classrooms (item, building) VALUES (:item, :building)");
            }

            $query->bindValue(':item', $this->item, PDO::PARAM_STR);
            $query->bindValue(':building', $this->building, PDO::PARAM_STR);
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
            $query = $db->prepare("SELECT * FROM classrooms WHERE id = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();

            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            $classroom = new self($db);
            $classroom->id = $data['id'];
            $classroom->item = $data['item'];
            $classroom->building = $data['building'];

            return $classroom;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public static function findBy(PDO $db, array $criteria)
    {
        try {
            $sql = "SELECT * FROM classrooms WHERE 1=1";
            $params = [];

            if (isset($criteria['item'])) {
                $sql .= " AND item LIKE :item";
                $params[':item'] = '%' . $criteria['item'] . '%';
            }

            if (isset($criteria['building'])) {
                $sql .= " AND building LIKE :building";
                $params[':building'] = '%' . $criteria['building'] . '%';
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