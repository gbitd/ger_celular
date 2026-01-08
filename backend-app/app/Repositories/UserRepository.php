<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UserRepository
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE id = :id"
        );

        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
