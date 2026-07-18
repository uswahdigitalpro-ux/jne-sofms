<?php

namespace App\Models;

use App\Config\Database;

class PaymentMethod
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM payment_methods WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM payment_methods WHERE status = "active" ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByType(string $type): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM payment_methods WHERE type = ? AND status = "active" ORDER BY name'
        );
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO payment_methods (name, type, code, icon, status) ' .
                'VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['name'],
                $data['type'],
                $data['code'] ?? null,
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
            $stmt = $this->db->prepare('UPDATE payment_methods SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }
}
