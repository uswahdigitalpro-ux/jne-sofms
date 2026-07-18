<?php

namespace App\Helpers;

/**
 * Global helper functions
 */

/**
 * Redirect to URL with optional message
 */
function redirect(string $url, string $message = '', string $type = 'info'): never
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    // Handle absolute URL vs relative
    if (strpos($url, 'http') === 0) {
        header("Location: $url");
    } else {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/jne-sofms-main';
        header("Location: $baseUrl$url");
    }

    exit;
}

/**
 * Set flash message
 */
function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get flash message
 */
function getFlash(): array
{
    $message = $_SESSION['flash_message'] ?? null;
    $type = $_SESSION['flash_type'] ?? 'info';

    // Clear flash after reading
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);

    return [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Check if user authenticated
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function getCurrentUser(): ?array
{
    if (!isAuthenticated()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'outlet_id' => $_SESSION['outlet_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
    ];
}

/**
 * Check user role
 */
function hasRole(string $role): bool
{
    if (!isAuthenticated()) {
        return false;
    }

    $userRole = $_SESSION['user_role'] ?? null;
    if ($role === 'any') {
        return true;
    }

    return $userRole === $role;
}

/**
 * Check any role
 */
function hasAnyRole(array $roles): bool
{
    if (!isAuthenticated()) {
        return false;
    }

    $userRole = $_SESSION['user_role'] ?? null;
    return in_array($userRole, $roles);
}

/**
 * Abort with error
 */
function abort(int $code = 404, string $message = ''): never
{
    http_response_code($code);

    $messages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];

    $title = $messages[$code] ?? 'Error';
    $detail = $message ?: $title;

    echo json_encode([
        'status' => 'error',
        'code' => $code,
        'message' => $detail
    ]);

    exit;
}

/**
 * Dump variable
 */
function dd(...$vars): never
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    exit;
}

/**
 * Dump variable without exit
 */
function dump(...$vars): void
{
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

/**
 * Asset URL
 */
function asset(string $path): string
{
    $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/jne-sofms-main';
    return $baseUrl . '/public/' . ltrim($path, '/');
}

/**
 * URL helper
 */
function url(string $path = ''): string
{
    $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost/jne-sofms-main';
    return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get environment variable
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}

/**
 * Check if current page
 */
function isCurrentPage(string $page): bool
{
    $current = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($current, $page) !== false;
}

/**
 * Get CSRF token
 */
function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Log activity
 */
function logActivity(string $action, string $description = '', ?int $userId = null): void
{
    $userId = $userId ?? ($_SESSION['user_id'] ?? null);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    // TODO: Save to activity_logs table
    // For now, just log to file for debugging
    $logFile = dirname(__DIR__) . '/logs/activity.log';
    $logEntry = sprintf(
        "[%s] User:%s | Action:%s | Description:%s | IP:%s\n",
        date('Y-m-d H:i:s'),
        $userId ?? 'Unknown',
        $action,
        $description,
        $ip
    );

    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
