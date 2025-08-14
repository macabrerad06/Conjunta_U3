<?php
declare(strict_types=1);

namespace App\Config;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null)
        {
            $host       = 'localhost';
            $dbName     = 'eduhack';
            $username   = 'root';
            $passwd     = '';
            $charset    = 'utf8mb4';

            $dsn = "mysql:host={$host};port=3307;dbname={$dbName};charset={$charset}";
            $options =[
                PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES      => false
            ];

            self::$instance = new PDO($dsn,$username,$passwd,$options);
        }

        return self::$instance;
    }
}