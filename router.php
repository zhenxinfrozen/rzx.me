<?php
// 本地路由器：模拟常见静态站点规则（文件优先、目录索引、扩展名回退、404）。
$docRoot = __DIR__;
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = rawurldecode(parse_url($requestUri, PHP_URL_PATH) ?: '/');

if ($path === '') {
    $path = '/';
}

// 过滤路径穿越。
if (strpos($path, '..') !== false) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Bad Request';
    return true;
}

$target = $docRoot . $path;

// 1) 真实文件：交给内置服务器处理（保留正确的 MIME/缓存行为）。
if (is_file($target)) {
    return false;
}

// 2) 目录请求（包括 /）：优先返回目录下 index.html。
if (is_dir($target)) {
    $indexFile = rtrim($target, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.html';
    if (is_file($indexFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($indexFile);
        return true;
    }
}

// 3) 无后缀请求：尝试回退到同名 .html（例如 /web/Animation -> /web/Animation.html）。
$basename = basename($path);
if ($basename !== '' && strpos($basename, '.') === false && substr($path, -1) !== '/') {
    $htmlFile = $docRoot . $path . '.html';
    if (is_file($htmlFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($htmlFile);
        return true;
    }
}

// 4) 不存在的资源：返回自定义 404 页面。
http_response_code(404);
$notFoundFile = $docRoot . DIRECTORY_SEPARATOR . '404.html';
if (is_file($notFoundFile)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($notFoundFile);
    return true;
}

header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
return true;
?>
