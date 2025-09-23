<?php
// 调试路由配置
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Router.php';

echo "=== Debug Routes ===\n";
echo "Routes config:\n";
var_dump(config('routes'));

echo "\n=== Router Test ===\n";
$router = new Router();
echo "Current path: " . $router->getCurrentPath() . "\n";

// 模拟请求 /api
$_SERVER['REQUEST_URI'] = '/api?id=wine';
$route = $router->match();
echo "Matched route for /api:\n";
var_dump($route);

echo "\n=== API Route Check ===\n";
echo "Is API route: " . ($router->isApiRoute($route) ? 'YES' : 'NO') . "\n";