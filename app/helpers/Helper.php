<?php

namespace App\Helpers;

class Helper
{
    /**
     * Format currency to Indonesian Rupiah
     */
    public static function formatCurrency(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Parse currency from string
     */
    public static function parseCurrency(string $amount): float
    {
        $amount = str_replace(['Rp', '.', ','], '', $amount);
        return (float) $amount;
    }

    /**
     * Format date to Indonesian format
     */
    public static function formatDate(string $date, string $format = 'd M Y'): string
    {
        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar',
            '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Agu', '09' => 'Sep',
            '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];

        $timestamp = strtotime($date);
        if (!$timestamp) return $date;

        return date($format, $timestamp);
    }

    /**
     * Format datetime to Indonesian format
     */
    public static function formatDateTime(string $datetime): string
    {
        return date('d M Y H:i', strtotime($datetime));
    }

    /**
     * Generate random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Sanitize input
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Calculate age
     */
    public static function calculateAge(string $birthDate): int
    {
        $birthDate = new \DateTime($birthDate);
        $today = new \DateTime('today');
        return $birthDate->diff($today)->y;
    }

    /**
     * Time ago format
     */
    public static function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' menit lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari lalu';
        } else {
            $weeks = floor($diff / 604800);
            return $weeks . ' minggu lalu';
        }
    }

    /**
     * Slug generator
     */
    public static function slug(string $text): string
    {
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\\w]+~', '', $text);
        $text = preg_replace('~-+~', '-', $text);
        $text = trim($text, '-');
        return strtolower($text);
    }

    /**
     * Generate UUID v4
     */
    public static function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
