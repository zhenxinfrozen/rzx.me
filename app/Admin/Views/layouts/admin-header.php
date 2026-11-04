<?php
/**
 * 后台管理系统 - 公共布局头部
 * 基于 SharedAdmin 通用架构套件
 */

// 设置正确的字符编码
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// 简单的认证检查
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: /admin?page=login');
    exit;
}

require_once __DIR__ . '/../../../../shared-admin/src/AdminCore.php';
$adminConfig = require __DIR__ . '/../../../Config/admin.php';

use SharedAdmin\AdminCore;
AdminCore::init($adminConfig);

// 保持变量向下兼容
$assets_base = $adminConfig['assets_url'] ?? '/shared-admin/assets';
$GLOBALS['assets_base'] = $assets_base;
$nav_base = '/admin?page=';

$current_page = $GLOBALS['current_page'] ?? $_GET['page'] ?? 'dashboard';
$title = $GLOBALS['page_title'] ?? $page_title ?? '控制台';
$subtitle = $GLOBALS['page_subtitle'] ?? $page_subtitle ?? '';

AdminCore::renderHeader([
    'title'        => $title,
    'subtitle'     => $subtitle,
    'current_page' => $current_page,
]);
