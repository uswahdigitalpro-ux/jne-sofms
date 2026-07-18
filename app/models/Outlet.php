<?php

namespace App\Models;

use App\Config\Database;

class Outlet
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM outlets WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM outlets WHERE owner_id = ? AND status = "active" ORDER BY name'
        );
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO outlets (owner_id, name, code, address, phone, email, logo_path, daily_target, status) ' .
                'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['owner_id'],
                $data['name'],
                $data['code'] ?? null,
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['logo_path'] ?? null,
                $data['daily_target'] ?? 0,
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
            $stmt = $this->db->prepare('UPDATE outlets SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }
}
