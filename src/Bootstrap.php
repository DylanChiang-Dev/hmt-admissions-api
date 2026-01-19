<?php

namespace App;

use App\Services\AttemptService;
use App\Services\AuthService;

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

        if ($repoType === 'mysql') {
            // Repositories
            $userRepo = new \App\Repositories\MySql\MySqlUserRepository();
            $questionRepo = new \App\Repositories\MySql\MySqlQuestionRepository();
            $lessonPackRepo = new \App\Repositories\MySql\MySqlLessonPackRepository($questionRepo);
            $attemptRepo = new \App\Repositories\MySql\MySqlAttemptRepository();
            $progressRepo = new \App\Repositories\MySql\MySqlProgressRepository();
            $reviewRepo = new \App\Repositories\MySql\MySqlReviewRepository();

            self::$services['user_repo'] = $userRepo;
            self::$services['question_repo'] = $questionRepo;
            self::$services['lesson_pack_repo'] = $lessonPackRepo;
            self::$services['attempt_repo'] = $attemptRepo;
            self::$services['progress_repo'] = $progressRepo;
            self::$services['review_repo'] = $reviewRepo;

            // Services
            self::$services['auth_service'] = new AuthService($userRepo);
            self::$services['attempt_service'] = new AttemptService($attemptRepo, $progressRepo, $questionRepo);
        } else {
            // Memory Repositories
            $questionRepo = new \App\Repositories\Memory\MemoryQuestionRepository();
            $lessonPackRepo = new \App\Repositories\Memory\MemoryLessonPackRepository();
            $attemptRepo = new \App\Repositories\Memory\MemoryAttemptRepository();
            $progressRepo = new \App\Repositories\MemoryProgressRepository();
            $reviewRepo = new \App\Repositories\MemoryReviewRepository();

            self::$services['question_repo'] = $questionRepo;
            self::$services['lesson_pack_repo'] = $lessonPackRepo;
            self::$services['attempt_repo'] = $attemptRepo;
            self::$services['progress_repo'] = $progressRepo;
            self::$services['review_repo'] = $reviewRepo;

            // Services
            // Note: AuthService is not available in memory mode as we don't have MemoryUserRepository
            self::$services['attempt_service'] = new AttemptService($attemptRepo, $progressRepo, $questionRepo);
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
