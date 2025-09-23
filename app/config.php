<?php
// app/config.php
// 仅负责配置（环境变量、错误级别、路径等），不执行启动逻辑。

if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'production');
}

if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$INCLUDE_HEADER = __DIR__ . '/Views/header.php';
$INCLUDE_FOOTER = __DIR__ . '/Views/footer.php';

if (!defined('INCLUDE_HEADER')) {
    define('INCLUDE_HEADER', __DIR__ . '/Views/header.php');
}
if (!defined('INCLUDE_FOOTER')) {
    define('INCLUDE_FOOTER', __DIR__ . '/Views/footer.php');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

if (!defined('ASSET_URL')) {
    define('ASSET_URL', rtrim(BASE_URL, '/') . '/assets/');
}
