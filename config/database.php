<?php
/**
 * Edutrack Computer Training College
 * Database Configuration
 */

return [
    
    // Default Database Connection
    'default' => 'mysql',
    
    // Database Connections
    'connections' => [
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_NAME') ?: 'edutrack_lms',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ],
        ],
        
    ],
    
    // Database Backup Settings
    'backup' => [
        'enabled' => true,
        'path' => dirname(__DIR__) . '/storage/backups',
        'keep_days' => 30,
        'auto_backup' => false,
        'schedule' => 'daily', // daily, weekly, monthly
    ],
    
];