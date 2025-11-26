<?php
declare(strict_types=1);

use App\Config\Config;
use App\Database;
use App\Support\Env;
use PDO;

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

ensureSuperAdmin($config, $pdo);

function ensureSuperAdmin(App\Config\Config $config, PDO $pdo): void
{
    $super = $config->superAdmin();
    $email = strtolower(trim((string)$super['email']));
    if ($email === '') {
        return;
    }

    // Asegurar rol encargado
    $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'encargado' LIMIT 1");
    $roleStmt->execute();
    $roleId = $roleStmt->fetchColumn();
    if ($roleId === false) {
        $pdo->prepare("INSERT INTO roles (name, vote_weight, can_be_voted) VALUES ('encargado', 2, 0)")->execute();
        $roleId = (int)$pdo->lastInsertId();
    }

    // Crear/ajustar super admin
    $stmt = $pdo->prepare("SELECT id, role_id, ci, name, sector, is_active FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $ins = $pdo->prepare("
            INSERT INTO users (ci, name, email, password_hash, sector, role_id, is_active)
            VALUES (:ci, :name, :email, :hash, :sector, :role_id, 1)
        ");
        $ins->execute([
            'ci' => $super['ci'],
            'name' => $super['name'],
            'email' => $super['email'],
            'hash' => $super['password_hash'],
            'sector' => $super['sector'],
            'role_id' => $roleId,
        ]);
        return;
    }

    $upd = $pdo->prepare("
        UPDATE users
        SET ci = :ci, name = :name, sector = :sector, role_id = :role_id, is_active = 1
        WHERE id = :id
    ");
    $upd->execute([
        'ci' => $super['ci'],
        'name' => $super['name'],
        'sector' => $super['sector'],
        'role_id' => $roleId,
        'id' => $user['id'],
    ]);
}

return [
    'config' => $config,
    'pdo' => $pdo,
];
