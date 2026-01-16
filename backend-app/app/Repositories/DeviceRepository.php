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

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO devices
            (name, location, purchase_date, in_use, user_id, created_at)
            VALUES (:name, :location, :purchase_date, :in_use, :user_id, NOW())
        ");

        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function findByUser($userId, $filters)
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

        $sql .= " ORDER BY purchase_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function update($id, $userId, array $data)
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

        return $stmt->execute([...$data, 'id' => $id, 'user_id' => $userId]);
    }

    public function softDelete($id, $userId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE devices
            SET deleted_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function toggleUse($id, $userId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE devices
            SET in_use = NOT in_use
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
