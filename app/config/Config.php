<?php

namespace App\Config;

class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_ENV[$key] = $value;
    }

    public static function all(): array
    {
        return $_ENV;
    }

    // Application
    public static function appName(): string
    {
        return self::get('APP_NAME', 'JNE SOFMS');
    }

    public static function appUrl(): string
    {
        return self::get('APP_URL', 'http://localhost:8000');
    }

    public static function appEnv(): string
    {
        return self::get('APP_ENV', 'production');
    }

    public static function appDebug(): bool
    {
        return self::get('APP_DEBUG', false) === 'true' || self::get('APP_DEBUG') === true;
    }

    // Timezone
    public static function timezone(): string
    {
        return self::get('TIMEZONE', 'Asia/Jakarta');
    }

    // Session
    public static function sessionTimeout(): int
    {
        return (int) self::get('SESSION_TIMEOUT', 3600);
    }

    // File Upload
    public static function maxUploadSize(): int
    {
        return (int) self::get('MAX_UPLOAD_SIZE', 5242880);
    }

    public static function allowedExtensions(): array
    {
        $extensions = self::get('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,pdf');
        return explode(',', $extensions);
    }
}
