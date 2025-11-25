<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use PDO;

final class CandidateController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function list(): void
    {
        $stmt = $this->pdo->query("
            SELECT u.id, u.name, u.ci, u.email, u.sector
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.is_active = 1 AND r.can_be_voted = 1
            ORDER BY u.name ASC
        ");

        $candidates = [];
        foreach ($stmt->fetchAll() as $row) {
            $candidates[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'ci' => (string)$row['ci'],
                'email' => (string)$row['email'],
                'sector' => $row['sector'],
            ];
        }

        Response::json(['candidates' => $candidates]);
    }
}

