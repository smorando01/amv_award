<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use PDO;
use PDOException;

final class VoteController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function vote(array $user): void
    {
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
        $candidateId = filter_var($payload['candidate_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$candidateId) {
            Response::json(['error' => 'Seleccioná un candidato válido'], 422);
            return;
        }

        if ($candidateId === (int)$user['id']) {
            Response::json(['error' => 'No podés votarte a vos mismo/a'], 422);
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT u.id, u.name, u.is_active, r.can_be_voted
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $candidateId]);
        $candidate = $stmt->fetch();

        if (!$candidate || (int)$candidate['is_active'] !== 1 || !(bool)$candidate['can_be_voted']) {
            Response::json(['error' => 'El candidato no está habilitado'], 400);
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->prepare("SELECT id FROM votes WHERE voter_id = :voter FOR UPDATE");
            $lock->execute(['voter' => $user['id']]);
            if ($lock->fetchColumn()) {
                $this->pdo->rollBack();
                Response::json(['error' => 'Ya registraste tu voto'], 409);
                return;
            }

            $insert = $this->pdo->prepare("
                INSERT INTO votes (voter_id, candidate_id, weight)
                VALUES (:voter, :candidate, :weight)
            ");
            $insert->execute([
                'voter' => $user['id'],
                'candidate' => $candidateId,
                'weight' => $user['vote_weight'] ?? 1,
            ]);

            $this->pdo->commit();
            Response::json([
                'message' => 'Voto registrado',
                'candidate' => [
                    'id' => (int)$candidate['id'],
                    'name' => (string)$candidate['name'],
                ],
                'weight' => (int)$user['vote_weight'],
            ], 201);
        } catch (PDOException) {
            $this->pdo->rollBack();
            Response::json(['error' => 'No se pudo registrar el voto'], 500);
        }
    }
}

