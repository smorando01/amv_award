<?php
declare(strict_types=1);

namespace App\Config;

final class Config
{
    private array $env;

    public function __construct(array $env)
    {
        $this->env = $env;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->env[$key] ?? $default;
    }

    public function appName(): string
    {
        return (string)($this->env['APP_NAME'] ?? 'AMV Store Award');
    }

    public function db(): array
    {
        return [
            'host' => (string)($this->env['DB_HOST'] ?? '127.0.0.1'),
            'name' => (string)($this->env['DB_NAME'] ?? ''),
            'user' => (string)($this->env['DB_USER'] ?? ''),
            'password' => (string)($this->env['DB_PASSWORD'] ?? ''),
            'charset' => 'utf8mb4',
        ];
    }

    public function corsAllowedOrigins(): string
    {
        return (string)($this->env['CORS_ALLOWED_ORIGINS'] ?? '*');
    }

    public function tokenTtlHours(): int
    {
        $ttl = (int)($this->env['TOKEN_TTL_HOURS'] ?? 72);
        return $ttl > 0 ? $ttl : 72;
    }

    public function superAdmin(): array
    {
        return [
            'name' => (string)($this->env['SUPERADMIN_NAME'] ?? 'Santiago Morando'),
            'email' => (string)($this->env['SUPERADMIN_EMAIL'] ?? 'santiago@amvuy.com'),
            'ci' => (string)($this->env['SUPERADMIN_CI'] ?? '47601099'),
            'sector' => (string)($this->env['SUPERADMIN_SECTOR'] ?? 'Super Administrador'),
            // Hash de "Fmuñoz3147"
            'password_hash' => (string)($this->env['SUPERADMIN_PASSWORD_HASH'] ?? '$2y$10$vJMfRaWe8IWRuZhGf4BWG.AAhf97C35mf3g31b/VoVmhG/3yAL6ra'),
        ];
    }

    public function isSuperAdmin(array $user): bool
    {
        $superEmail = strtolower((string)$this->superAdmin()['email']);
        $userEmail = strtolower((string)($user['email'] ?? ''));
        return $superEmail !== '' && $superEmail === $userEmail;
    }
}
