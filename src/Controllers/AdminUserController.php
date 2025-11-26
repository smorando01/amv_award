<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use PDO;
use PDOException;

final class AdminUserController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function list(array $authUser): void
    {
        if (!$this->isManager($authUser)) {
            Response::json(['error' => 'No autorizado'], 403);
            return;
        }

        $stmt = $this->pdo->query("
            SELECT u.id, u.ci, u.name, u.email, u.sector, r.name AS role, u.is_active
            FROM users u
            JOIN roles r ON r.id = u.role_id
            ORDER BY u.is_active DESC, u.name ASC
        ");
        $users = $stmt->fetchAll();

        Response::json(['users' => $users]);
    }

    public function create(array $authUser): void
    {
        if (!$this->isManager($authUser)) {
            Response::json(['error' => 'No autorizado'], 403);
            return;
        }

        $payload = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];

        $name = trim((string)($payload['name'] ?? ''));
        $email = trim((string)($payload['email'] ?? ''));
        $ci = preg_replace('/\D+/', '', (string)($payload['ci'] ?? ''));
        $sector = trim((string)($payload['sector'] ?? ''));
        $roleName = strtolower(trim((string)($payload['role'] ?? 'empleado')));
        $password = (string)($payload['password'] ?? '');

        if ($name === '' || $email === '' || $ci === '' || $password === '') {
            Response::json(['error' => 'Completá nombre, email, CI y contraseña'], 422);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['error' => 'Email inválido'], 422);
            return;
        }
        if (!in_array($roleName, ['empleado', 'encargado'], true)) {
            Response::json(['error' => 'Rol inválido'], 422);
            return;
        }

        $roleId = $this->roleIdFor($roleName);
        if ($roleId === null) {
            Response::json(['error' => 'Rol no encontrado'], 500);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (ci, name, email, password_hash, sector, role_id, is_active)
                VALUES (:ci, :name, :email, :hash, :sector, :role_id, 1)
            ");
            $stmt->execute([
                'ci' => $ci,
                'name' => $name,
                'email' => $email,
                'hash' => $hash,
                'sector' => $sector ?: null,
                'role_id' => $roleId,
            ]);

            Response::json([
                'message' => 'Usuario creado',
                'id' => (int)$this->pdo->lastInsertId(),
            ], 201);
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                Response::json(['error' => 'CI o email ya existen'], 409);
            } else {
                Response::json(['error' => 'No se pudo crear el usuario'], 500);
            }
        }
    }

    public function delete(array $authUser): void
    {
        if (!$this->isManager($authUser)) {
            Response::json(['error' => 'No autorizado'], 403);
            return;
        }

        $payload = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
        $id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            Response::json(['error' => 'ID inválido'], 422);
            return;
        }
        if ($id === (int)$authUser['id']) {
            Response::json(['error' => 'No podés eliminar tu propio usuario'], 422);
            return;
        }

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() === 0) {
            Response::json(['error' => 'Usuario no encontrado'], 404);
            return;
        }

        Response::json(['message' => 'Usuario eliminado']);
    }

    private function isManager(array $user): bool
    {
        return isset($user['role_name']) && strtolower((string)$user['role_name']) === 'encargado';
    }

    private function roleIdFor(string $roleName): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $roleName]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }
}

