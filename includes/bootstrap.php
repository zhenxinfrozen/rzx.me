<?php
// Bootstrap - 初始化应用运行时（仅此文件负责引导）

// 定义运行时常量，防止直接包含模板或包含文件被误访问
if (!defined('APP_RUNNING')) {
    define('APP_RUNNING', true);
}

// 加载配置（config.php 仅包含配置，不做启动行为）
require_once __DIR__ . '/config.php';

// 如果使用 Composer，请在此处启用自动加载（推荐）
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 可在此处添加启动时需要执行的一次性逻辑，例如 session 启动、错误/异常处理器注册
// 例如：
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

