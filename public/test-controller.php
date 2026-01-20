<?php
/**
 * 直接测试控制器
 */

// 模拟AJAX请求
$_GET['ajax'] = 'thumbnails';
$_GET['category'] = 'Game+color';

echo "=== 开始测试 ===\n";
echo "Category: " . $_GET['category'] . "\n";
echo "Ajax Action: " . $_GET['ajax'] . "\n\n";

// 加载控制器
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/Admin/Controllers/single-works.php';

echo "\n=== 测试结束 ===\n";
