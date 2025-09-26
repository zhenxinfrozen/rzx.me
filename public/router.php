<?php
/**
 * PHP内置服务器路由器
 * 用法: php -S localhost:8000 -t public router.php
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$filePath = __DIR__ . $requestPath;

// 静态文件直接访问的目录
$allowDirectAccess = [
    '/dev/',
    '/admin/assets/',
    '/admin/css/', 
    '/admin/js/',
    '/admin/images/',
    '/assets/'
];

// 检查是否为允许直接访问的目录
foreach ($allowDirectAccess as $prefix) {
    if (strpos($requestPath, $prefix) === 0) {
        // 检查文件是否存在
        if (file_exists($filePath)) {
            // 让PHP内置服务器处理静态文件
            return false;
        } else {
            // 文件不存在
            http_response_code(404);
            echo "File not found: " . htmlspecialchars($requestUri);
            return true;
        }
    }
}

// 其他请求通过index.php处理
require_once __DIR__ . '/index.php';