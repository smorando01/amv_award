<?php
// === AMV STORE AWARD 2025 - CONFIG ===

// 1. CARGA DE SECRETOS (Seguridad)
// Buscamos el archivo .env un nivel arriba de esta carpeta
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
} else {
    // Si no existe el archivo (ej. olvidaste subirlo o crearlo), detenemos todo por seguridad.
    die('Error de configuración: No se encuentra el archivo .env de credenciales.');
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
