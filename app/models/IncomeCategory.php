<?php

namespace App\Models;

use App\Config\Database;

class IncomeCategory
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM income_categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM income_categories WHERE status = "active" ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO income_categories (name, description, icon, status) ' .
                'VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['icon'] ?? null,
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
            $stmt = $this->db->prepare('UPDATE income_categories SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }
}
