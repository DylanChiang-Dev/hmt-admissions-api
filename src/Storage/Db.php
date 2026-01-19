<?php

namespace App\Storage;

use App\Config;
use PDO;
use PDOException;

class Db
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = Config::get('DB_HOST', '127.0.0.1');
            $db   = Config::get('DB_NAME', 'hmt_admissions');
            $user = Config::get('DB_USER', 'root');
            $pass = Config::get('DB_PASS', '');
            $port = Config::get('DB_PORT', '3306');
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // If connection fails, throw an exception that can be caught
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
