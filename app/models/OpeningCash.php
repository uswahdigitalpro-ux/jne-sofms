<?php

namespace App\Models;

use App\Config\Database;

class OpeningCash
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM opening_cash WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getActive(int $outletId, int $staffId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM opening_cash WHERE outlet_id = ? AND staff_id = ? AND status = "open" ' .
            'ORDER BY opened_at DESC LIMIT 1'
        );
        $stmt->execute([$outletId, $staffId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO opening_cash (outlet_id, staff_id, shift_id, initial_capital, notes, status) ' .
                'VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $data['outlet_id'],
                $data['staff_id'],
                $data['shift_id'],
                $data['initial_capital'],
                $data['notes'] ?? null,
                'open'
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function close(int $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE opening_cash SET status = "closed", closed_at = NOW() WHERE id = ?'
            );
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTodayActive(int $outletId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oc.*, u.full_name FROM opening_cash oc ' .
            'LEFT JOIN users u ON oc.staff_id = u.id ' .
            'WHERE oc.outlet_id = ? AND oc.status = "open" ' .
            'AND DATE(oc.opened_at) = CURDATE()'
        );
        $stmt->execute([$outletId]);
        return $stmt->fetchAll();
    }
}
