<?php
/**
 * RZX.ME 后台管理系统 - 入口文件
 * 
 * 新架构：使用 app/Controllers/Admin/AdminIndexController.php 统一处理
 * 控制器位于：app/Controllers/Admin/
 * 视图位于：app/Views/Admin/
 */

// 加载新的管理控制器
require_once __DIR__ . '/../../app/Controllers/Admin/AdminIndexController.php';

// 处理请求
AdminIndexController::handle();