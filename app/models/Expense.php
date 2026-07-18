<?php

namespace App\Models;

use App\Config\Database;

class Expense
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, ec.name as category_name, u.full_name as staff_name ' .
            'FROM expenses e ' .
            'LEFT JOIN expense_categories ec ON e.expense_category_id = ec.id ' .
            'LEFT JOIN users u ON e.staff_id = u.id ' .
            'WHERE e.id = ? AND e.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO expenses ' .
                '(outlet_id, opening_cash_id, staff_id, expense_category_id, description, amount, receipt_image_path, notes, requires_approval, approval_status) ' .
                'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $requiresApproval = isset($data['requires_approval']) ? $data['requires_approval'] : false;
            $approvalStatus = $requiresApproval ? 'pending' : 'approved';

            $stmt->execute([
                $data['outlet_id'],
                $data['opening_cash_id'],
                $data['staff_id'],
                $data['expense_category_id'],
                $data['description'],
                $data['amount'],
                $data['receipt_image_path'] ?? null,
                $data['notes'] ?? null,
                $requiresApproval ? 1 : 0,
                $approvalStatus
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
            $stmt = $this->db->prepare('UPDATE expenses SET ' . implode(', ', $fields) . ' WHERE id = ?');
            return $stmt->execute($values);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getByOpeningCash(int $openingCashId, string $status = null): array
    {
        $query = 'SELECT e.*, ec.name as category_name FROM expenses e ' .
                 'LEFT JOIN expense_categories ec ON e.expense_category_id = ec.id ' .
                 'WHERE e.opening_cash_id = ? AND e.deleted_at IS NULL';
        $params = [$openingCashId];

        if ($status) {
            $query .= ' AND e.approval_status = ?';
            $params[] = $status;
        }

        $query .= ' ORDER BY e.created_at DESC';
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalByCategory(int $openingCashId, int $categoryId): float
    {
        $stmt = $this->db->prepare(
            'SELECT SUM(amount) as total FROM expenses ' .
            'WHERE opening_cash_id = ? AND expense_category_id = ? AND deleted_at IS NULL AND approval_status = "approved"'
        );
        $stmt->execute([$openingCashId, $categoryId]);
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    public function approve(int $id, int $approverId, string $notes = null): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE expenses SET approval_status = "approved", approved_by = ?, approval_notes = ? WHERE id = ?'
            );
            return $stmt->execute([$approverId, $notes, $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function reject(int $id, int $approverId, string $notes = null): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE expenses SET approval_status = "rejected", approved_by = ?, approval_notes = ? WHERE id = ?'
            );
            return $stmt->execute([$approverId, $notes, $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE expenses SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
