<?php
declare(strict_types=1);

namespace App\Http;

use App\Config\Config;

final class Cors
{
    public static function apply(Config $config): void
    {
        $allowedOrigins = $config->corsAllowedOrigins();
        header('Access-Control-Allow-Origin: ' . $allowedOrigins);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 600');
    }
}

