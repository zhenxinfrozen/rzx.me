<?php
/**
 * 后台管理系统 - 公共布局底部
 * 基于 SharedAdmin 通用架构套件
 */
$appRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 4);
require_once $appRoot . '/shared-admin/src/AdminCore.php';

\SharedAdmin\AdminCore::renderFooter([
    'inline_js' => "if (typeof pageInit === 'function') { pageInit(); }"
]);
