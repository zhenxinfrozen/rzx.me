<?php
/**
 * RZX.ME 后台管理系统 - 通用模板
 * 标准化结构：头部 + 内容 + 底部
 */

// 获取页面参数，默认为dashboard
$current_page = $_GET['page'] ?? 'dashboard';

// 根据页面参数确定要加载的内容文件（使用绝对路径确保任意入口都可用）
$views_base = __DIR__ . '/views';
$content_file = $views_base . "/pages/body-{$current_page}.php";

// 检查内容文件是否存在
if (!file_exists($content_file)) {
    $content_file = $views_base . '/pages/body-dashboard.php'; // 默认回退到dashboard
    $current_page = 'dashboard';
}

// 包含头部布局
require_once $views_base . '/layouts/header.php';
?>

<div class="admin-page-content">
    <?php require_once $content_file; ?>
</div>

<?php
// 包含底部布局
require_once $views_base . '/layouts/footer.php';