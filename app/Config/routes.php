<?php
// app/Config/routes.php - 路由配置文件

return [
    // 页面路由
    'pages' => [
        '/' => [
            'view' => 'pages/home.php',
            'title' => 'Ray的个人主页',
            'handler' => 'get_page_data'
        ],
        '/about' => [
            'view' => 'pages/about.php',
            'title' => '关于 Ray - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/animation' => [
            'view' => 'pages/animation.php',
            'title' => '动画 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/comic' => [
            'view' => 'pages/comic.php',
            'title' => '漫画 - RZX.ME',
            'handler' => 'get_comic_data'
        ],
        '/ray-comic-reader' => [
            'view' => 'pages/comic-gallery.php',
            'title' => '漫画阅读器 - RZX.ME',
            'handler' => 'get_comic_data'
        ],
        '/latest' => [
            'view' => 'pages/latest.php',
            'title' => '最新 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/pictures' => [
            'view' => 'pages/pictures.php',
            'title' => '图片 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/sites' => [
            'view' => 'pages/sites.php',
            'title' => '网站 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/sketch' => [
            'view' => 'pages/sketch.php',
            'title' => '素描 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/sketch-dream' => [
            'view' => 'pages/comic-gallery.php',
            'title' => 'Dream Gallery - RZX.ME',
            'handler' => 'get_page_data'
        ],
        '/galleries' => [
            'view' => 'pages/galleries.php',
            'title' => '画廊 - RZX.ME',
            'handler' => 'get_page_data'
        ],
        // 动态画廊路由模式: /gallery-{name}
        '~^\/gallery-([^\/]+)$~' => [
            'view' => 'pages/gallery.php',
            'title' => '画廊 - RZX.ME',
            'handler' => 'get_page_data'
        ],
    ],

    // API路由
    'api' => [
        '/api' => [
            'handler' => 'handle_api_request',
            'method' => ['GET', 'POST']
        ],
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