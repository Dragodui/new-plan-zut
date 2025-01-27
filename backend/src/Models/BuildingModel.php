<?php
namespace App\Models;

use PDO;
use PDOException;

class BuildingModel
{
    private $db;
    private $id;
    private $item;

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

    public function save(): bool
    {
        try {
            if ($this->id) {
                $query = $this->db->prepare("UPDATE buildings SET item = :item WHERE id = :id");
                $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            } else {
                $query = $this->db->prepare("INSERT INTO buildings (item) VALUES (:item)");
            }

            $query->bindValue(':item', $this->item, PDO::PARAM_STR);
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
            $query = $db->prepare("SELECT * FROM buildings WHERE id = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();

            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            $building = new self($db);
            $building->id = $data['id'];
            $building->item = $data['item'];

            return $building;
        } catch (PDOException $e) {
            throw new \Exception('Database operation failed: ' . $e->getMessage());
        }
    }

    public static function findBy(PDO $db, array $criteria): array
    {
        try {
            $sql = "SELECT * FROM buildings WHERE 1=1";
            $params = [];

            if (isset($criteria['item'])) {
                $sql .= " AND item LIKE :item";
                $params[':item'] = '%' . $criteria['item'] . '%';
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