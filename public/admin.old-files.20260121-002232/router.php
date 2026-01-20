<?php
/**
 * Admin路由处理器 - PHP Fallback
 * 当nginx/Apache配置不完善时的备用路由处理
 * 
 * 用途:
 * - 为admin目录提供独立的路由处理
 * - 兼容nginx和Apache环境
 * - 处理controller访问和静态资源
 */

// 获取请求路径
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = '/admin';

// 移除base path和query string
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');

// 如果是空路径，显示admin首页
if (empty($path)) {
    require_once __DIR__ . '/index.php';
    exit;
}

// 检查是否是静态资源
$staticDirs = ['assets', 'css', 'js', 'images'];
$pathParts = explode('/', $path);

if (!empty($pathParts[0]) && in_array($pathParts[0], $staticDirs)) {
    // 静态资源请求 - 让Web服务器处理
    $filePath = __DIR__ . '/' . $path;
    if (file_exists($filePath)) {
        // 设置正确的Content-Type
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript', 
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2'
        ];
        
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo "File not found: " . htmlspecialchars($path);
        exit;
    }
}

// 检查是否是controller请求
$controllerName = $pathParts[0];
$controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    // 设置admin环境变量
    define('ADMIN_PATH', __DIR__);
    define('ADMIN_URL', $basePath);
    
    // 包含controller
    require_once $controllerFile;
    exit;
} else {
    // Controller不存在，检查是否有对应的页面文件
    $pageFile = __DIR__ . '/' . $path . '.php';
    if (file_exists($pageFile)) {
        require_once $pageFile;
        exit;
    }
    
    // 都不存在，返回404或重定向到admin首页
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>页面未找到 - Admin</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; text-align: center; }
            .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .back-link { color: #007bff; text-decoration: none; }
            .back-link:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>页面未找到</h1>
        <div class="error">
            <p>请求的资源 <code><?php echo htmlspecialchars($path); ?></code> 不存在。</p>
        </div>
        <p><a href="<?php echo $basePath; ?>/" class="back-link">← 返回后台首页</a></p>
        
        <h3>可用的Controller:</h3>
        <ul style="text-align: left; display: inline-block;">
            <?php
            $controllers = glob(__DIR__ . '/controllers/*.php');
            foreach ($controllers as $controller) {
                $name = basename($controller, '.php');
                echo '<li><a href="' . $basePath . '/' . $name . '" class="back-link">' . $name . '</a></li>';
            }
            ?>
        </ul>
    </body>
    </html>
    <?php
    exit;
}
?>