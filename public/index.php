<?php
// 前端控制器 - 站点唯一入口
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view_renderer.php';
require_once __DIR__ . '/../app/Controllers/page_data_handler.php'; // 引入页面数据处理器

// 基本路由：将请求路径映射到 app/views 下的视图模板
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// 规范化路径：移除首尾斜杠，并移除 .php 后缀（如果存在）
$path = trim($path, '/');
if (substr($path, -4) === '.php') {
    $path = substr($path, 0, -4);
}

// 路由表现在使用不带 .php 的键
$routes = [
    '' => 'index-body.php',
    'index' => 'index-body.php',
    'ray-comic' => 'ray-comic-body.php',
    'ray-pictures' => 'ray-pictures-body.php',
    'ray-animation' => 'ray-animation-body.php',
    'ray-latest' => 'ray-latest-body.php',
    'ray-sites' => 'ray-sites-body.php',
    'ray-sketch' => 'ray-sketch-body.php',
    'ray-about' => 'ray-about-body.php',
    'sketch-dream' => 'ray-comic-reader-body.php', // 使用新的模板文件
];

// API路由处理 - 支持现代RESTful和传统格式
if ($path === 'api' || strpos($path, 'api/') === 0) {
    // 解析API路径: api/comic/123 或 api?id=123
    if (preg_match('/^api\/comic\/(\w+)$/', $path, $matches)) {
        // RESTful格式: /api/comic/{id}
        $_GET['id'] = $matches[1];
    } elseif ($path === 'api' || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api.php') !== false) {
        // 传统格式: /api.php?id=123 (向后兼容)
    }
    
    require_once __DIR__ . '/../app/Controllers/api_comic_handler.php';
    handle_api_request();
    exit;
}

$viewFile = $routes[$path] ?? null;

// 如果路由表中未找到，则进行动态匹配
if ($viewFile === null && $path !== '') {
    // 将 'path-name' 转换为 'path-name-body.php'
    $candidateView = $path . '-body.php';
    if (file_exists(__DIR__ . '/../app/Views/' . $candidateView)) {
        $viewFile = $candidateView;
    }
}

// 获取页面特定数据
$pageData = get_page_specific_data($viewFile);

// 从页面数据处理器获取所有页面变量（已包含默认值）
$title = $pageData['page_title'];
$page_id = $pageData['page_id'];
$css_file = $pageData['css_file'];
$meta_keywords = $pageData['meta_keywords'];
$meta_description = $pageData['meta_description'];
$meta_copyright = $pageData['meta_copyright'];
$meta_author = $pageData['meta_author'];

// 渲染标准页面布局
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="copyright" content="<?php echo htmlspecialchars($meta_copyright, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
    <meta name="description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
    <meta name="author" content="<?php echo htmlspecialchars($meta_author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" />
    <title><?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></title>
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico" />
    <?php if ($css_file): ?>
    <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($css_file, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php endif; ?>
</head>
<body id="<?php echo htmlspecialchars($page_id, ENT_QUOTES, 'UTF-8'); ?>">

<?php
// 渲染页眉
try {
    echo render_template(__DIR__ . '/../app/Views/header.php', ['title' => $title]);
} catch (Exception $e) {
    // 出现异常时静默处理并继续
}

// 渲染主内容
if ($viewFile && file_exists(__DIR__ . '/../app/Views/' . $viewFile)) {
    try {
        echo render_template(__DIR__ . '/../app/Views/' . $viewFile);
    } catch (Exception $e) {
        http_response_code(500);
        echo '<h1>服务器错误</h1><p>无法加载视图。</p>';
    }
} else {
    // 如果找不到视图，显示 404 页面或消息
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>请求的页面不存在。</p>";
}

if (!isset($page_id) || !in_array($page_id, ['sketch', 'comic-reader'])):
// 渲染页脚
try {
    echo render_template(__DIR__ . '/../app/Views/footer.php');
} catch (Exception $e) {}

endif;
?>

</body>
</html>