<?php
declare(strict_types=1);

namespace App\Http;

use App\Config\Config;

final class SecurityHeaders
{
    public static function apply(Config $config): void
    {
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('X-XSS-Protection: 0');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\'; base-uri \'none\'; form-action \'none\';');
        header('X-Powered-By: ' . $config->appName());
    }
}
