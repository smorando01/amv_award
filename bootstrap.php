<?php
declare(strict_types=1);

use App\Config\Config;
use App\Database;
use App\Support\Env;

error_reporting(E_ALL);
ini_set('display_errors', '0');
date_default_timezone_set('UTC');

require __DIR__ . '/src/Support/autoload.php';

$env = Env::load(
    __DIR__ . '/.env',
    __DIR__ . '/public/.env'
);

$config = new Config($env);
$pdo = (new Database($config))->pdo();

return [
    'config' => $config,
    'pdo' => $pdo,
];

