<?php
declare(strict_types=1);

namespace App\Support;

final class Env
{
    /**
     * Load configuration values from multiple .env files plus real environment variables.
     */
    public static function load(string ...$paths): array
    {
        $env = [];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                $loaded = parse_ini_file($path, false, INI_SCANNER_TYPED);
                if (is_array($loaded)) {
                    $env = array_merge($env, $loaded);
                }
            }
        }

        $keys = [
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD',
            'APP_NAME',
            'CORS_ALLOWED_ORIGINS',
            'TOKEN_TTL_HOURS',
        ];

        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                $env[$key] = $value;
            }
        }

        return $env;
    }
}

