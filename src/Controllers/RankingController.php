<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use PDO;

final class RankingController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ranking(): void
    {
        $sql = "
            SELECT c.id,
                   c.name,
                   c.sector,
                   SUM(v.weight) AS points,
                   COUNT(v.id) AS total_votes,
                   SUM(CASE WHEN v.weight > 1 THEN 1 ELSE 0 END) AS double_votes
            FROM votes v
            JOIN users c ON c.id = v.candidate_id
            JOIN roles r ON r.id = c.role_id
            WHERE c.is_active = 1 AND r.can_be_voted = 1
            GROUP BY c.id, c.name, c.sector
            ORDER BY points DESC, c.name ASC
        ";

        $rows = $this->pdo->query($sql)->fetchAll();
        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int)$row['points']);
        }

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'sector' => $row['sector'],
                'points' => (int)$row['points'],
                'total_votes' => (int)$row['total_votes'],
                'double_votes' => (int)$row['double_votes'],
                'percentage' => $max > 0 ? round(((int)$row['points'] / $max) * 100) : 0,
            ];
        }

        Response::json([
            'ranking' => $data,
            'meta' => [
                'max_points' => $max,
            ],
        ]);
    }
}

