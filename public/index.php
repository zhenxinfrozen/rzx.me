<?php
// 前端控制器 - 站点唯一入口
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view_renderer.php';
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Controllers/page_data_handler.php';

// 初始化路由器
$router = new Router();

// 匹配当前路由
$route = $router->match();

// 处理API请求
if ($route && $router->isApiRoute($route)) {
    require_once __DIR__ . '/../app/Controllers/api_comic_handler.php';
    
    // 调用对应的处理器函数
    if (isset($route['handler']) && function_exists($route['handler'])) {
        call_user_func($route['handler']);
    } else {
        handle_api_request(); // 默认处理器
    }
    exit;
}

// 处理页面请求
if (!$route || !$router->isPageRoute($route)) {
    // 未找到路由，使用404
    $route = $router->get404Route();
    http_response_code(404);
}

// 向后兼容：支持旧路由格式
if (!$route) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = trim($path, '/');
    if (substr($path, -4) === '.php') {
        $path = substr($path, 0, -4);
    }
    
    // 旧路由表
    $legacyRoutes = [
        '' => 'pages/index-body.php',
        'index' => 'pages/index-body.php',
        'ray-comic' => 'pages/ray-comic-body.php',
        'ray-pictures' => 'pages/ray-pictures-body.php',
        'ray-animation' => 'pages/ray-animation-body.php',
        'ray-latest' => 'pages/ray-latest-body.php',
        'ray-sites' => 'pages/ray-sites-body.php',
        'ray-sketch' => 'pages/ray-sketch-body.php',
        'ray-about' => 'pages/ray-about-body.php',
        'sketch-dream' => 'pages/ray-comic-reader-body.php',
    ];
    
    $viewFile = $legacyRoutes[$path] ?? null;
    
    // 动态匹配
    if ($viewFile === null && $path !== '') {
        $candidateView = 'pages/' . $path . '-body.php';
        if (file_exists(__DIR__ . '/../app/Views/' . $candidateView)) {
            $viewFile = $candidateView;
        }
    }
    
    if ($viewFile) {
        $route = [
            'view' => $viewFile,
            'title' => config('views.default_title'),
            'handler' => 'get_page_data'
        ];
    }
}

// 获取页面数据
$pageData = [];
if (isset($route['handler']) && function_exists($route['handler'])) {
    if ($route['handler'] === 'get_page_specific_data') {
        $pageData = get_page_specific_data($route['view'] ?? null);
    } else {
        $pageData = call_user_func($route['handler'], $route['view'] ?? null);
    }
} else {
    $pageData = get_page_specific_data($route['view'] ?? null);
}

// 设置页面变量
$title = $pageData['page_title'] ?? $router->getPageTitle($route);
$page_id = $pageData['page_id'] ?? 'default';
$css_file = $pageData['css_file'] ?? '';
$meta_keywords = $pageData['meta_keywords'] ?? config('app.name', 'RZX.ME');
$meta_description = $pageData['meta_description'] ?? '';
$meta_copyright = $pageData['meta_copyright'] ?? '';
$meta_author = $pageData['meta_author'] ?? 'Ray';

// 渲染页面
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="<?php echo config('app.charset', 'UTF-8'); ?>" />
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
    $headerPath = __DIR__ . '/../app/Views/' . config('views.header', 'layouts/header.php');
    echo render_template($headerPath, ['title' => $title]);
} catch (Exception $e) {
    if (config('app.debug')) {
        echo "<!-- Header Error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
}

// 渲染主内容
$viewFile = $route['view'] ?? null;
$viewsPath = __DIR__ . '/../app/Views/';
if ($viewFile && file_exists($viewsPath . $viewFile)) {
    try {
        echo render_template($viewsPath . $viewFile, $pageData);
    } catch (Exception $e) {
        http_response_code(500);
        if (config('app.debug')) {
            echo '<h1>服务器错误</h1><p>无法加载视图: ' . htmlspecialchars($e->getMessage()) . '</p>';
        } else {
            echo '<h1>服务器错误</h1><p>无法加载视图。</p>';
        }
    }
} else {
    // 404页面
    if (!isset($route['type']) || $route['type'] !== 'error') {
        http_response_code(404);
    }
    echo "<h1>404 Not Found</h1><p>请求的页面不存在。</p>";
}

// 渲染页脚（某些页面不显示）
if (!isset($page_id) || !in_array($page_id, ['sketch', 'comic-reader'])):
try {
    $footerPath = __DIR__ . '/../app/Views/' . config('views.footer', 'layouts/footer.php');
    echo render_template($footerPath);
} catch (Exception $e) {
    if (config('app.debug')) {
        echo "<!-- Footer Error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
}
endif;
?>

</body>
</html>