<?php
/**
 * 前端控制器 - 应用程序统一入口点
 *
 * 功能:
 * - 接收所有HTTP请求 (通过.htaccess重定向)
 * - 初始化路由系统和依赖
 * - 分发请求到对应的处理器
 * - 渲染页面模板和输出HTML
 *
 * 流程: 请求 → Router匹配 → Controller处理 → View渲染 → 响应
 * 配置: 使用 app/Config/ 下的配置文件
 */

// 开启错误报告以便调试
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../app/bootstrap.php';
    require_once __DIR__ . '/../app/view_renderer.php';
    require_once __DIR__ . '/../app/Router.php';
    require_once __DIR__ . '/../app/Controllers/page_data_handler.php';
} catch (Exception $e) {
    die('Error loading dependencies: ' . $e->getMessage());
}

// 初始化路由器
$router = new Router();

// 匹配当前路由
$route = $router->match();

// 处理静态文件请求 (dev目录等)
if ($route && $router->isStaticRoute($route)) {
    // 静态文件请求，让Web服务器处理
    // 不做任何处理，直接退出让服务器查找物理文件
    $requestUri = $_SERVER['REQUEST_URI'];
    $filePath = __DIR__ . parse_url($requestUri, PHP_URL_PATH);

    // 检查文件是否存在
    if (file_exists($filePath)) {
        // 文件存在，让Web服务器处理
        return false; // 这会让PHP内置服务器继续处理
    } else {
        // 文件不存在，返回404
        http_response_code(404);
        echo "File not found: " . htmlspecialchars($requestUri);
        exit;
    }
}

// 处理admin AJAX请求（必须在admin页面请求之前处理）
if ($route && $router->isAdminAjaxRoute($route)) {
        // Admin AJAX 请求处理
        $controller = $_GET['controller'] ?? '';

        if (empty($controller)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing controller']);
            exit;
        }

        // 允许的控制器列表
        $allowedControllers = [
            'sketchbook' => __DIR__ . '/../app/Admin/Controllers/sketchbook.php',
            'drafts' => __DIR__ . '/../app/Admin/Controllers/drafts.php',
            'videos' => __DIR__ . '/../app/Admin/Controllers/videos.php',
            'comics' => __DIR__ . '/../app/Admin/Controllers/comics.php',
            'thumbnail-center' => __DIR__ . '/../app/Admin/Controllers/thumbnail-center.php',
            'media-manager' => __DIR__ . '/../app/Admin/Controllers/media-manager.php',
            'galleries' => __DIR__ . '/../app/Admin/Controllers/galleries.php',
        ];

        // 验证控制器
        if (!isset($allowedControllers[$controller])) {
            http_response_code(404);
            echo json_encode(['error' => 'Controller not found']);
            exit;
        }

        $controllerFile = $allowedControllers[$controller];

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo json_encode(['error' => 'Controller file not found']);
            exit;
        }

        // 加载必要的依赖
        require_once __DIR__ . '/../app/bootstrap.php';

        // 包含并执行控制器
        require_once $controllerFile;
        exit;
}

// 处理admin后台页面请求
if ($route && $router->isAdminRoute($route)) {
    // 加载 AdminIndexController
    require_once __DIR__ . '/../app/Admin/Controllers/AdminIndexController.php';

    // 处理请求
    AdminIndexController::handle();
    exit;
}

// 处理常规API请求
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
// 检测当前URL路径来决定是否显示页脚
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$hideFooterPages = ['sketch', 'comic-reader'];
$hideFooter = false;

// 检查固定页面ID
if (isset($page_id) && in_array($page_id, $hideFooterPages)) {
    $hideFooter = true;
}

// 检查动态gallery页面 (匹配 /gallery-* 模式)
if (preg_match('/^\/gallery-[^\/]+$/', $currentPath)) {
    $hideFooter = true;
}

if (!$hideFooter):
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
