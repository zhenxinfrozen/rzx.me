<?php
/**
 * Admin AJAX 请求处理器
 * 
 * 处理所有后台 AJAX 请求，路由到对应的控制器
 * URL 格式：/admin/ajax.php?controller=xxx&ajax=action
 */

// 设置错误显示
if (isset($_GET['dev'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// 验证 AJAX 请求
$controller = $_GET['controller'] ?? '';
$ajaxAction = $_GET['ajax'] ?? ($_POST['ajax_action'] ?? null);

if (empty($controller) || empty($ajaxAction)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing controller or action']);
    exit;
}

// 允许的控制器列表
$allowedControllers = [
    'sketchbook' => __DIR__ . '/../../app/Admin/Controllers/sketchbook.php',
    'single-works' => __DIR__ . '/../../app/Admin/Controllers/single-works.php',
    'video-gallery' => __DIR__ . '/../../app/Admin/Controllers/video-gallery.php',
    'comics' => __DIR__ . '/../../app/Admin/Controllers/comics.php',
    'thumbnail-center' => __DIR__ . '/../../app/Admin/Controllers/thumbnail-center.php',
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
require_once __DIR__ . '/../../app/bootstrap.php';

// 包含并执行控制器
// 控制器会检查 $_GET['ajax'] 或 $_POST['ajax_action'] 并处理请求
require_once $controllerFile;
