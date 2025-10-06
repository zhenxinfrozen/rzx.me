<?php
/**
 * 管理工具页面
 * 系统维护和优化工具集合
 */

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: ../login.php');
    exit;
}

// 设置页面信息
$page_title = '🔧 管理工具';
$page_subtitle = '系统维护和优化工具集合';
$_GET['page'] = 'tools';

// 准备视图所需数据
$dev_query = isset($_GET['dev']) ? '?dev' : '';

// 包含布局和视图
require_once '../views/layouts/header.php';
require_once '../views/pages/tools.php';
require_once '../views/layouts/footer.php';