<?php

namespace App\Core\Config;

class Config {
    private static array $config = [];

    public static function init(): void {
        // Application settings
        self::$config['app'] = [
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
            'locale' => $_ENV['APP_LOCALE'] ?? 'en',
            'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000'
        ];

        // Database settings
        self::$config['database'] = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'name' => $_ENV['DB_NAME'] ?? 'forum',
            'user' => $_ENV['DB_USER'] ?? 'postgres',
            'password' => $_ENV['DB_PASS'] ?? '',
            'schema' => $_ENV['DB_SCHEMA'] ?? 'public',
            'ssl_mode' => $_ENV['DB_SSL_MODE'] ?? 'disable'
        ];

        // Redis settings
        self::$config['redis'] = [
            'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => $_ENV['REDIS_PORT'] ?? '6379',
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => $_ENV['REDIS_DB'] ?? '0'
        ];

        // CORS settings
        self::$config['cors'] = [
            'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'),
            'allowed_methods' => explode(',', $_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS'),
            'allowed_headers' => explode(',', $_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization,X-Requested-With'),
            'max_age' => (int)($_ENV['CORS_MAX_AGE'] ?? 86400)
        ];

        // Upload settings
        self::$config['upload'] = [
            'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760),
            'allowed_types' => explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,gif,pdf'),
            'path' => $_ENV['UPLOAD_PATH'] ?? '/uploads'
        ];

        // Cache settings
        self::$config['cache'] = [
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'redis',
            'prefix' => $_ENV['CACHE_PREFIX'] ?? 'forum_',
            'ttl' => (int)($_ENV['CACHE_TTL'] ?? 3600)
        ];

        // Rate limiting
        self::$config['rate_limit'] = [
            'requests' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 60),
            'minutes' => (int)($_ENV['RATE_LIMIT_MINUTES'] ?? 1)
        ];

        // JWT settings
        self::$config['jwt'] = [
            'secret' => $_ENV['JWT_SECRET'],
            'ttl' => (int)($_ENV['JWT_TTL'] ?? 3600),
            'refresh_ttl' => (int)($_ENV['JWT_REFRESH_TTL'] ?? 604800)
        ];
    }

    public static function get(string $key, $default = null) {
        $keys = explode('.', $key);
        $config = self::$config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public static function set(string $key, $value): void {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $config = &self::$config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config[$lastKey] = $value;
    }
} 