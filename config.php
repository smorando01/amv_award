<?php
// === AMV STORE AWARD 2025 - CONFIG ===

// 1. CARGA DE SECRETOS (Seguridad)
// Buscamos el archivo .env en varias ubicaciones comunes para hosting compartido
$candidateEnvPaths = [
    __DIR__ . '/.env',               // raíz del proyecto
    __DIR__ . '/../.env',            // un nivel arriba
    __DIR__ . '/public/.env',        // dentro de /public
    dirname(__DIR__) . '/public/.env',
];

$envPath = null;
foreach ($candidateEnvPaths as $path) {
    if (file_exists($path)) {
        $envPath = $path;
        break;
    }
}

if ($envPath) {
    $env = parse_ini_file($envPath);
} else {
    // Si no hay archivo .env, intentamos usar variables de entorno del servidor (ej. panel de hosting)
    $env = [
        'DB_HOST' => getenv('DB_HOST'),
        'DB_NAME' => getenv('DB_NAME'),
        'DB_USER' => getenv('DB_USER'),
        'DB_PASSWORD' => getenv('DB_PASSWORD'),
        'ADMIN_PASSWORD' => getenv('ADMIN_PASSWORD'),
    ];

    $missing = array_filter($env, fn($value) => $value === false || $value === null || $value === '');

    if (count($missing) > 0) {
        // Si faltan credenciales, detenemos todo por seguridad e indicamos todas las rutas probadas
        $pathsList = implode("\n- ", $candidateEnvPaths);
        die("Error de configuración: No se encuentra el archivo .env de credenciales ni variables de entorno.\n" .
            "Verificá que el archivo esté en alguna de estas rutas:\n- {$pathsList}\n" .
            "O definí las variables DB_HOST, DB_NAME, DB_USER, DB_PASSWORD y ADMIN_PASSWORD en el entorno del servidor.");
    }
}

// Database credentials (Cargadas desde el archivo .env)
define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASSWORD']); // La contraseña ya no está visible aquí
define('DB_CHARSET', 'utf8mb4');

// Admin password for /admin/* pages
// Ahora la toma del archivo .env, así puedes cambiarla sin tocar el código
define('ADMIN_PASSWORD', $env['ADMIN_PASSWORD']);

// Site settings
define('SITE_NAME', 'AMV Store Award 2025');
define('ALLOW_DOMAINS_REGEX', '/@(amvuy\.com|amvstore\.com\.uy)$/i'); 

// Session & security
session_name('amv_award_2025');
session_start();

// CSRF token helper
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
function csrf_input() {
  echo '<input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf']).'">';
}
function csrf_check() {
  if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(400);
    exit('CSRF token inválido.');
  }
}
?>
