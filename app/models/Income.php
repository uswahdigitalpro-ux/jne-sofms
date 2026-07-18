<?php

namespace App\Models;

use App\Config\Database;

class Income
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, ic.name as category_name, pm.name as payment_method_name, u.full_name as staff_name ' .
            'FROM income i ' .
            'LEFT JOIN income_categories ic ON i.income_category_id = ic.id ' .
            'LEFT JOIN payment_methods pm ON i.payment_method_id = pm.id ' .
            'LEFT JOIN users u ON i.staff_id = u.id ' .
            'WHERE i.id = ? AND i.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO income ' .
                '(outlet_id, opening_cash_id, staff_id, income_category_id, payment_method_id, description, amount, proof_image_path, notes) ' .
                'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['outlet_id'],
                $data['opening_cash_id'],
                $data['staff_id'],
                $data['income_category_id'],
                $data['payment_method_id'],
                $data['description'] ?? null,
                $data['amount'],
                $data['proof_image_path'] ?? null,
                $data['notes'] ?? null
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
            $stmt = $this->db->prepare('UPDATE income SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getByOpeningCash(int $openingCashId): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, ic.name as category_name, pm.name as payment_method_name ' .
            'FROM income i ' .
            'LEFT JOIN income_categories ic ON i.income_category_id = ic.id ' .
            'LEFT JOIN payment_methods pm ON i.payment_method_id = pm.id ' .
            'WHERE i.opening_cash_id = ? AND i.deleted_at IS NULL ' .
            'ORDER BY i.created_at DESC'
        );
        $stmt->execute([$openingCashId]);
        return $stmt->fetchAll();
    }

    public function getTotalByMethod(int $openingCashId, int $paymentMethodId): float
    {
        $stmt = $this->db->prepare(
            'SELECT SUM(amount) as total FROM income ' .
            'WHERE opening_cash_id = ? AND payment_method_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$openingCashId, $paymentMethodId]);
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    public function getTotalByCategory(int $openingCashId, int $categoryId): float
    {
        $stmt = $this->db->prepare(
            'SELECT SUM(amount) as total FROM income ' .
            'WHERE opening_cash_id = ? AND income_category_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$openingCashId, $categoryId]);
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    public function softDelete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE income SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
