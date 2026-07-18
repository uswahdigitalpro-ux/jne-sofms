<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function check(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        return true;
    }

    public static function checkRole(string $requiredRole): bool
    {
        if (!self::check()) {
            return false;
        }

        $userRole = $_SESSION['user_role'] ?? null;
        if ($requiredRole === 'any') {
            return true;
        }

        return $userRole === $requiredRole;
    }

    public static function checkAny(array $roles): bool
    {
        if (!self::check()) {
            return false;
        }

        $userRole = $_SESSION['user_role'] ?? null;
        return in_array($userRole, $roles);
    }

    public static function guard(string $role = 'any'): void
    {
        if ($role === 'any') {
            if (!self::check()) {
                redirect('/login', 'Silakan login terlebih dahulu');
            }
        } else {
            if (!self::checkRole($role)) {
                redirect('/dashboard', 'Anda tidak memiliki akses ke halaman ini');
            }
        }
    }

    public static function session(): void
    {
        if (!self::check()) {
            return;
        }

        $sessionTimeout = $_ENV['SESSION_TIMEOUT'] ?? 3600;
        $lastActivity = $_SESSION['last_activity'] ?? time();

        if ((time() - $lastActivity) > $sessionTimeout) {
            session_destroy();
            redirect('/login', 'Sesi Anda telah berakhir');
        }

        $_SESSION['last_activity'] = time();
    }
}
