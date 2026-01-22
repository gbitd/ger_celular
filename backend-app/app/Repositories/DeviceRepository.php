<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DeviceRepository
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO devices
            (name, location, purchase_date, in_use, user_id, created_at)
            VALUES (:name, :location, :purchase_date, :in_use, :user_id, NOW())
        ");

        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function findByUserFiltered(int $userId, array $filters): array
    {
        $sql = "SELECT * FROM devices
                WHERE user_id = :user_id
                AND deleted_at IS NULL";

        $params = ['user_id' => $userId];

        if (isset($filters['in_use'])) {
            $sql .= " AND in_use = :in_use";
            $params['in_use'] = $filters['in_use'];
        }

        if (!empty($filters['location'])) {
            $sql .= " AND location = :location";
            $params['location'] = $filters['location'];
        }

        if (!empty($filters['from'])) {
            $sql .= " AND purchase_date >= :from";
            $params['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $sql .= " AND purchase_date <= :to";
            $params['to'] = $filters['to'];
        }

        $sql .= " ORDER BY purchase_date DESC, id ASC";

        if (isset($filters['page'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = $filters['per_page'];
            $params['offset'] = ($filters['page'] - 1) * $filters['per_page'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByUserTotal(int $userId): array
    {
         $sql = "SELECT * FROM devices
                WHERE user_id = :user_id
                AND deleted_at IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countTotalByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM devices
                WHERE user_id = :user_id
                AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchColumn();
    }

    public function findById(int $id, int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM devices
            WHERE id = :id
            AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

    }

    public function update(int $id, int $userId, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE devices SET
                name = :name,
                location = :location,
                purchase_date = :purchase_date,
                updated_at = NOW()
            WHERE id = :id
            AND user_id = :user_id
            AND deleted_at IS NULL
        ");

        $stmt->execute([...$data, 'id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;   // Se nenhuma row é afetada, propaga erro de negócio
    }

    public function softDelete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE devices
            SET deleted_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    public function toggleUse(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE devices
            SET in_use = NOT in_use
            WHERE id = :id AND user_id = :user_id
        ");

        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}
