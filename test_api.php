<?php
// 测试API处理器
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Controllers/api_comic_handler.php';

echo "=== API Handler Test ===\n";

// 模拟GET请求参数
$_GET['id'] = 'wine';

echo "Testing handle_api_request()...\n";
echo "GET parameters: ";
var_dump($_GET);

// 捕获输出
ob_start();
handle_api_request();
$output = ob_get_clean();

echo "API Response:\n";
echo $output . "\n";