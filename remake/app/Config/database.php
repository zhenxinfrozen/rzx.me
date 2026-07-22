<?php
// app/Config/database.php - 数据库配置文件

return [
    // 默认数据库连接
    'default' => 'file',

    // 数据库连接配置
    'connections' => [
        // 文件数据库 (当前使用)
        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/data',
        ],

        // MySQL配置 (预留)
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'rzx_me',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],

        // SQLite配置 (预留)
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../storage/database.sqlite',
        ],
    ],
];