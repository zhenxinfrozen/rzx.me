<?php
// app/Config/app.php - 应用主配置文件

return [
    // 应用基本配置
    'app' => [
        'name' => 'RZX.ME Personal Website',
        'version' => '0.8.1',
        'environment' => 'development', // development, production
        'debug' => true,
        'timezone' => 'Asia/Shanghai',
        'charset' => 'UTF-8',
        'use_pretty_urls' => true, // Set to true if you have URL rewriting (pseudo-static) enabled
    ],

    // 路径配置
    'paths' => [
        'app' => __DIR__ . '/..',
        'public' => __DIR__ . '/../../public',
        'views' => __DIR__ . '/../Views',
        'assets' => __DIR__ . '/../../public/assets',
        'uploads' => __DIR__ . '/../../public/uploads',
        'logs' => __DIR__ . '/../storage/logs',
        'cache' => __DIR__ . '/../storage/cache',
    ],

    // 视图配置
    'views' => [
        'header' => 'layouts/header.php',
        'footer' => 'layouts/footer.php',
        'default_title' => 'RZX.ME - Ray的个人网站',
        'cache_enabled' => false,
    ],

    // 安全配置
    'security' => [
        'csrf_protection' => false,
        'session_lifetime' => 120, // 分钟
        'password_hash_algo' => PASSWORD_DEFAULT,
    ],

    // 性能配置
    'performance' => [
        'enable_compression' => true,
        'enable_caching' => false,
        'cache_lifetime' => 3600, // 秒
    ]
];