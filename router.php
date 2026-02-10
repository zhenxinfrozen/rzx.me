<?php
// 简单路由器用于本地测试404页面
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$file = __DIR__ . $path;

// 如果文件存在，就正常提供
if (file_exists($file) && is_file($file)) {
    return false; // PHP内置服务器处理
}

// 如果不存在，返回404页面
http_response_code(404);
include __DIR__ . '/404.html';
?>
