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
}

