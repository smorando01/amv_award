<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\TokenService;
use App\Http\Response;
use PDO;

final class AuthController
{
    private PDO $pdo;
    private TokenService $tokens;

    public function __construct(PDO $pdo, TokenService $tokens)
    {
        $this->pdo = $pdo;
        $this->tokens = $tokens;
    }

    public function login(): void
    {
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
        $identifier = trim((string)($payload['identifier'] ?? ''));
        $password = (string)($payload['password'] ?? '');

        if ($identifier === '' || $password === '') {
            Response::json(['error' => 'Ingresá cédula/correo y contraseña'], 422);
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT u.*, r.name AS role_name, r.vote_weight, r.can_be_voted
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE (u.email = :id OR u.ci = :id)
            LIMIT 1
        ");
        $stmt->execute(['id' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || (int)$user['is_active'] !== 1 || !password_verify($password, (string)$user['password_hash'])) {
            Response::json(['error' => 'Credenciales inválidas'], 401);
            return;
        }

        $token = $this->tokens->issueToken((int)$user['id'], $_SERVER['HTTP_USER_AGENT'] ?? null);
        $this->pdo->prepare("UPDATE users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id")->execute(['id' => $user['id']]);

        Response::json([
            'token' => $token,
            'user' => $this->publicUser($user),
            'has_voted' => $this->hasVoted((int)$user['id']),
        ]);
    }

    public function me(array $user): void
    {
        Response::json([
            'user' => $this->publicUser($user),
            'has_voted' => $this->hasVoted((int)$user['id']),
        ]);
    }

    public function logout(): void
    {
        $token = $this->tokens->extractBearerToken();
        $this->tokens->revoke($token);
        Response::json(['message' => 'Sesión cerrada']);
    }

    private function hasVoted(int $userId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM votes WHERE voter_id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    private function publicUser(array $user): array
    {
        return [
            'id' => (int)$user['id'],
            'ci' => (string)$user['ci'],
            'name' => (string)$user['name'],
            'email' => (string)$user['email'],
            'sector' => $user['sector'],
            'role' => (string)$user['role_name'],
            'vote_weight' => (int)$user['vote_weight'],
            'can_be_voted' => (bool)$user['can_be_voted'],
        ];
    }
}

