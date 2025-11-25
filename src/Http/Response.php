<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function json(array $body, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    public static function empty(int $status = 204): void
    {
        http_response_code($status);
    }
}

