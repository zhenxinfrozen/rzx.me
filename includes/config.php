<?php
// includes/config.php
// 仅负责配置（环境变量、错误级别、路径等），不执行启动逻辑。

if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'production');
}

// 根据环境设置错误显示与级别
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// 指向模板片段的绝对路径（位于 includes/views/ 或 includes/templates/）
$INCLUDE_HEADER = __DIR__ . '/views/header.php';
$INCLUDE_FOOTER = __DIR__ . '/views/footer.php';

// 向后兼容地提供常量形式的包含路径，便于在模板中使用常量而不是全局变量。
if (!defined('INCLUDE_HEADER')) {
    define('INCLUDE_HEADER', __DIR__ . '/views/header.php');
}
if (!defined('INCLUDE_FOOTER')) {
    define('INCLUDE_FOOTER', __DIR__ . '/views/footer.php');
}

// 站点路径/资源基址：可在环境中覆盖（例如部署到子目录或 CDN）
if (!defined('BASE_URL')) {
    // 默认站点根
    define('BASE_URL', '/');
}

if (!defined('ASSET_URL')) {
    // 资源默认放在 BASE_URL 下的 assets 目录；允许云端/CDN 前缀覆盖
    define('ASSET_URL', rtrim(BASE_URL, '/') . '/assets/');
}

// 其他全局配置变量可在此处添加（数据库 DSN、站点名等），但不要在此执行会话或自动加载逻辑。