<?php
declare(strict_types=1);

namespace App\Auth;

use App\Config\Config;
use App\Http\Response;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;

final class TokenService
{
    private PDO $pdo;
    private Config $config;

    public function __construct(PDO $pdo, Config $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function issueToken(int $userId, ?string $userAgent = null): string
    {
        $plain = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plain);

        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('+' . $this->config->tokenTtlHours() . ' hours')
            ->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("
            INSERT INTO auth_tokens (user_id, token_hash, expires_at, user_agent)
            VALUES (:user_id, :hash, :expires_at, :user_agent)
        ");
        $stmt->execute([
            'user_id' => $userId,
            'hash' => $hash,
            'expires_at' => $expiresAt,
            'user_agent' => $userAgent,
        ]);

        return $plain;
    }

    public function revoke(?string $token): void
    {
        if (!$token) {
            return;
        }

        $hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare("DELETE FROM auth_tokens WHERE token_hash = :hash");
        $stmt->execute(['hash' => $hash]);
    }

    public function userFromToken(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        $hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.ci, u.name, u.email, u.sector, u.role_id, u.is_active,
                   r.name AS role_name, r.vote_weight, r.can_be_voted,
                   t.expires_at
            FROM auth_tokens t
            JOIN users u ON u.id = t.user_id
            JOIN roles r ON r.id = u.role_id
            WHERE t.token_hash = :hash
            LIMIT 1
        ");
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        if ((int)$row['is_active'] !== 1 || strtotime((string)$row['expires_at']) <= time()) {
            $this->revoke($token);
            return null;
        }

        $this->touch($hash);
        return $row;
    }

    public function requireUser(): array
    {
        $token = $this->extractBearerToken();
        $user = $this->userFromToken($token);
        if (!$user) {
            Response::json(['error' => 'No autorizado'], 401);
            exit;
        }
        $user['_token'] = $token;
        return $user;
    }

    public function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return null;
    }

    private function touch(string $tokenHash): void
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE auth_tokens SET last_used_at = UTC_TIMESTAMP() WHERE token_hash = :hash");
            $stmt->execute(['hash' => $tokenHash]);
        } catch (PDOException) {
            // Do not leak DB errors to clients
        }
    }
}
