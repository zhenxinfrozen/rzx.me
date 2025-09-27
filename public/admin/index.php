<?php
/**
 * RZX.ME 后台管理系统 - 控制台首页
 * 标准化结构：头部 + 内容 + 底部
 */

// 设置页面信息
$page_title = '控制台';
$page_subtitle = '欢迎使用 RZX.ME 管理后台';
$_GET['page'] = 'dashboard';

// 包含头部布局
require_once 'views/layouts/header.php';
?>

<div class="admin-page-content">
    <?php require_once 'views/pages/dashboard-content.php'; ?>
</div>

<?php
// 包含底部布局
require_once 'views/layouts/footer.php';