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
        // 優先級：系統環境變量 > 文件載入 > 默認值
        // 這樣 Docker 環境變量可以覆蓋 .env 文件
        
        // 1. 首先檢查系統環境變量（Docker, 命令行等）
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }
        
        // 2. 然後從文件載入的配置讀取
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }
        
        // 3. 回退到 $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        return $default;
    }
}
