<?php
// app/Config/routes.php - 路由配置文件

return [
    // 页面路由
    'pages' => [
        '/' => [
            'view' => 'index-body.php',
            'title' => 'Ray的个人主页',
            'handler' => 'get_page_data'
        ],
        '/ray-about' => [
            'view' => 'ray-about-body.php',
            'title' => '关于 Ray - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/ray-animation' => [
            'view' => 'ray-animation-body.php',
            'title' => '动画 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/ray-comic' => [
            'view' => 'ray-comic-body.php',
            'title' => '漫画 - RZX.ME',
            'handler' => 'get_comic_data'
        ],
        '/ray-comic-reader' => [
            'view' => 'ray-comic-reader-body.php',
            'title' => '漫画阅读器 - RZX.ME',
            'handler' => 'get_comic_data'
        ],
        '/ray-latest' => [
            'view' => 'ray-latest-body.php',
            'title' => '最新 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/ray-pictures' => [
            'view' => 'ray-pictures-body.php',
            'title' => '图片 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/ray-sites' => [
            'view' => 'ray-sites-body.php',
            'title' => '网站 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/ray-sketch' => [
            'view' => 'ray-sketch-body.php',
            'title' => '素描 - RZX.ME',
            'handler' => 'get_page_data'
        ],
    ],

    // API路由
    'api' => [
        '/api/comic/list' => [
            'handler' => 'get_comic_list',
            'method' => 'GET'
        ],
        '/api/comic/thumbnails' => [
            'handler' => 'generate_thumbnails',
            'method' => 'POST'
        ],
        // 保持向后兼容
        '/api.php' => [
            'handler' => 'handle_legacy_api',
            'method' => ['GET', 'POST']
        ],
    ],

    // 错误页面
    'errors' => [
        '404' => [
            'view' => '404.php',
            'title' => '页面未找到 - RZX.ME'
        ],
        '500' => [
            'view' => '500.php',
            'title' => '服务器错误 - RZX.ME'
        ]
    ]
];