<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\Helper;

class User
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? AND deleted_at IS NULL');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (outlet_id, username, email, password, full_name, role, phone, status) ' .
                'VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['outlet_id'],
                $data['username'],
                $data['email'] ?? null,
                Helper::hashPassword($data['password']),
                $data['full_name'],
                $data['role'] ?? 'staff',
                $data['phone'] ?? null,
                $data['status'] ?? 'active'
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }

            $values[] = $id;
            $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getByOutlet(int $outletId, string $role = null): array
    {
        $query = 'SELECT * FROM users WHERE outlet_id = ? AND deleted_at IS NULL';
        $params = [$outletId];

        if ($role) {
            $query .= ' AND role = ?';
            $params[] = $role;
        }

        $query .= ' ORDER BY full_name';
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function changePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, ['password' => Helper::hashPassword($newPassword)]);
    }

    public function verify(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return null;
        }

        if (Helper::verifyPassword($password, $user['password'])) {
            return $user;
        }

        return null;
    }
}
