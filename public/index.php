<?php
// 前端控制器 - 站点唯一入口
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view_renderer.php';

// 基本路由：将请求路径映射到 app/views 下的视图模板
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// 规范化路径：移除首尾斜杠
$path = trim($path, '/');

// Simple routing table (extend as needed)
$routes = [
    '' => 'index-body.php',
    'index.php' => 'index-body.php',
    'ray-comic.php' => 'ray-comic-body.php',
    'ray-pictures.php' => 'ray-pictures-body.php',
    'ray-animation.php' => 'ray-animation-body.php',
    'ray-latest.php' => 'ray-latest-body.php',
    'ray-sites.php' => 'ray-sites-body.php',
    'ray-sketch.php' => 'ray-sketch-body.php',
    'ray-about.php' => 'ray-about-body.php',
    'api.php' => null, // 单独处理（API 直接由 api.php 处理）
];

// 如果请求为 api.php，直接包含 API 处理程序
if ($path === 'api.php' || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api.php') !== false) {
    require_once __DIR__ . '/api.php';
    exit;
}

$viewFile = $routes[$path] ?? null;

// 如果路由表中未找到，但看起来像 app/views 下的文件，则尝试直接映射
if ($viewFile === null && $path !== '') {
    $candidate = __DIR__ . '/../app/views/' . basename($path);
    if (file_exists($candidate)) {
        $viewFile = basename($path);
    }
}

// 准备页面标题（简单默认）
$title = 'rzx.me';

// 渲染标准页面布局
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="icon" href="favicon.ico" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(rtrim(ASSET_URL, '/'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>/css/home_style.css" />
</head>
<body>
<?php
// 页眉
try {
    echo render_template(__DIR__ . '/../app/views/header.php', ['title' => $title]);
} catch (Exception $e) {
    // 出现异常时静默处理并继续
}

// 主体内容
if ($viewFile) {
    try {
        echo render_template(__DIR__ . '/../app/views/' . $viewFile);
    } catch (Exception $e) {
        http_response_code(500);
        echo '<h1>服务器错误</h1><p>无法加载视图。</p>';
    }
} else {
    // 回退：404
    http_response_code(404);
    echo '<h1>404 Not Found</h1><p>请求的页面不存在。</p>';
}

// 页脚
try {
    echo render_template(__DIR__ . '/../app/views/footer.php');
} catch (Exception $e) {}
?>
</body>
</html>