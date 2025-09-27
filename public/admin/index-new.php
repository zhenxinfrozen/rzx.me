<?php
/**
 * 后台管理系统 - 单一入口文件
 * 类似前台 index.php 的标准化模板加载模式
 */

// 基础配置
require_once __DIR__ . '/../../app/bootstrap.php';

// 获取当前请求的页面
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// 定义允许的页面和对应的文件
$allowedPages = [
    'dashboard' => [
        'title' => '控制台',
        'file' => 'dashboard.php',
        'subtitle' => '系统概览和快速操作'
    ],
    'sort-config' => [
        'title' => '作品排序',
        'file' => 'sort-config.php',
        'subtitle' => '管理作品显示顺序'
    ],
    'system-info' => [
        'title' => '系统信息',
        'file' => 'system-info.php',
        'subtitle' => '查看服务器和系统状态'
    ],
    'tools' => [
        'title' => '工具集合',
        'file' => 'tools.php',
        'subtitle' => '实用工具和功能'
    ],
    'gallery-manager' => [
        'title' => '图片库管理',
        'file' => 'gallery-manager.php',
        'subtitle' => '管理图片和相册'
    ],
    'site-config' => [
        'title' => '站点配置',
        'file' => 'site-config.php',
        'subtitle' => '网站基础设置'
    ],
    'cache-manager' => [
        'title' => '缓存管理',
        'file' => 'cache-manager.php',
        'subtitle' => '清理和管理系统缓存'
    ],
    'trash' => [
        'title' => '回收站',
        'file' => 'trash.php',
        'subtitle' => '已删除内容管理'
    ]
];

// 验证页面
if (!isset($allowedPages[$page])) {
    $page = 'dashboard'; // 默认页面
}

$currentPage = $allowedPages[$page];
$pageTitle = $currentPage['title'];
$pageSubtitle = $currentPage['subtitle'];
$pageFile = $currentPage['file'];

// 设置页面变量供 header 使用
$_GET['page'] = $page; // 确保导航高亮正确

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - RZX.ME 管理后台</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 自定义样式 -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Chart.js (如果需要图表) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

<?php
// 包含头部布局（导航栏等）
include 'views/layouts/header.php';
?>

<!-- 主要内容区域 -->
<div class="admin-page-content">
    <?php
    // 包含对应的页面内容文件
    $contentFile = __DIR__ . '/pages/' . $pageFile;
    if (file_exists($contentFile)) {
        include $contentFile;
    } else {
        // 如果页面文件不存在，显示404
        echo '<div class="alert alert-danger">';
        echo '<h4><i class="fas fa-exclamation-triangle"></i> 页面未找到</h4>';
        echo '<p>请求的页面 "' . htmlspecialchars($pageFile) . '" 不存在。</p>';
        echo '</div>';
    }
    ?>
</div>

<?php
// 包含底部布局（脚本等）
include 'views/layouts/footer.php';
?>

</body>
</html>