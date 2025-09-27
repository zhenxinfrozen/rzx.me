<?php
// 简单的调试脚本
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing includes...\n";

// 测试bootstrap
try {
    require_once __DIR__ . '/../app/bootstrap.php';
    echo "Bootstrap loaded successfully\n";
} catch (Exception $e) {
    echo "Bootstrap error: " . $e->getMessage() . "\n";
    exit;
}

// 测试Router
try {
    require_once __DIR__ . '/../app/Router.php';
    echo "Router loaded successfully\n";
} catch (Exception $e) {
    echo "Router error: " . $e->getMessage() . "\n";
    exit;
}

// 测试路由匹配
try {
    $router = new Router();
    echo "Router created successfully\n";
    
    $route = $router->match('/single-works');
    echo "Route match result: ";
    var_dump($route);
} catch (Exception $e) {
    echo "Route matching error: " . $e->getMessage() . "\n";
}
?>