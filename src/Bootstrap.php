<?php

namespace App;

class Bootstrap
{
    private static array $services = [];

    public static function init(): void
    {
        self::registerAutoloader();
        self::loadConfig();
        self::setupEnvironment();
        self::registerServices();
    }

    public static function getService(string $name)
    {
        return self::$services[$name] ?? null;
    }

    private static function registerServices(): void
    {
        $repoType = Config::get('REPO_TYPE', 'memory');

        // Lesson Pack Repository
        if ($repoType === 'memory') {
            self::$services['lesson_pack_repo'] = new \App\Repositories\Memory\MemoryLessonPackRepository();
        } else {
            self::$services['lesson_pack_repo'] = new \App\Repositories\Memory\MemoryLessonPackRepository();
        }

        // Attempt Repository
        if ($repoType === 'memory') {
            self::$services['attempt_repo'] = new \App\Repositories\Memory\MemoryAttemptRepository();
        } else {
            self::$services['attempt_repo'] = new \App\Repositories\Memory\MemoryAttemptRepository();
        }

        // Progress Repository
        if ($repoType === 'memory') {
            self::$services['progress_repo'] = new \App\Repositories\MemoryProgressRepository();
        } else {
            self::$services['progress_repo'] = new \App\Repositories\MemoryProgressRepository();
        }

        // Review Repository
        if ($repoType === 'memory') {
            self::$services['review_repo'] = new \App\Repositories\MemoryReviewRepository();
        } else {
            self::$services['review_repo'] = new \App\Repositories\MemoryReviewRepository();
        }
    }

    private static function registerAutoloader(): void
    {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $base_dir = __DIR__ . '/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }

    private static function loadConfig(): void
    {
        // Assuming .env is in the project root (parent of src)
        $rootDir = dirname(__DIR__);
        $envFile = $rootDir . '/.env';
        $envExample = $rootDir . '/.env.example';

        // Load .env if exists, otherwise try .env.example or fail gracefully
        if (file_exists($envFile)) {
            Config::load($envFile);
        } elseif (file_exists($envExample)) {
            Config::load($envExample); // Fallback for dev/testing if .env missing
        }
    }

    private static function setupEnvironment(): void
    {
        $debug = Config::get('APP_DEBUG', 'false') === 'true';

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }
}
