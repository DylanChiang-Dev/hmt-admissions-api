<?php

namespace App;

class Config
{
    private static array $data = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Simple cleanup for quotes
                $value = trim($value, '"\'');

                self::$data[$key] = $value;

                // Populate $_ENV as well for compatibility
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$data[$key] ?? $_ENV[$key] ?? $default;
    }
}
