<?php
// Bootstrap - 初始化应用运行时（仅此文件负责引导）

// 定义运行时常量，防止直接包含模板或包含文件被误访问
if (!defined('APP_RUNNING')) {
    define('APP_RUNNING', true);
}

// 加载新的配置管理器
require_once __DIR__ . '/Config/ConfigManager.php';

// 初始化配置
$config = ConfigManager::getInstance();

// 设置时区
if ($timezone = config('app.timezone')) {
    date_default_timezone_set($timezone);
}

// 设置字符编码
if ($charset = config('app.charset')) {
    mb_internal_encoding($charset);
}

// 错误报告配置
if (config('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 加载旧的配置文件（向后兼容）
require_once __DIR__ . '/config.php';

// 如果使用 Composer，请在此处启用自动加载（推荐）
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 可在此处添加启动时需要执行的一次性逻辑，例如 session 启动、错误/异常处理器注册
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
