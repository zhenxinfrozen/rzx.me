<?php
// 调试index.php路由处理
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/view_renderer.php';
require_once __DIR__ . '/app/Router.php';
require_once __DIR__ . '/app/Controllers/page_data_handler.php';

// 模拟API请求
$_SERVER['REQUEST_URI'] = '/api?id=wine';
$_SERVER['REQUEST_METHOD'] = 'GET';

echo "=== Index.php Debug ===\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";

// 初始化路由器
$router = new Router();
echo "Router initialized\n";

// 匹配当前路由
$route = $router->match();
echo "Route matched: ";
var_dump($route);

// 处理API请求
if ($route && $router->isApiRoute($route)) {
    echo "This is an API route!\n";
    require_once __DIR__ . '/app/Controllers/api_comic_handler.php';
    
    // 调用对应的处理器函数
    if (isset($route['handler']) && function_exists($route['handler'])) {
        echo "Calling handler: " . $route['handler'] . "\n";
        call_user_func($route['handler']);
    } else {
        echo "Using default API handler\n";
        handle_api_request(); // 默认处理器
    }
    exit;
} else {
    echo "Not an API route\n";
    echo "Route: ";
    var_dump($route);
    echo "isApiRoute: " . ($router->isApiRoute($route) ? 'YES' : 'NO') . "\n";
}