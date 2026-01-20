<?php
/**
 * 后台管理系统 - 公共布局头部
 */

// 设置正确的字符编码
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    // 强制跳转到 admin 目录下的 login.php
    header('Location: /admin/login.php');
    exit;
}

// 新架构：所有资源使用绝对路径或项目根路径
// 当前文件在 app/Views/Admin/layouts/header.php
// 资源文件在 public/assets/

// 使用 Web 根路径（推荐）
$assets_base = '/assets';

$GLOBALS['assets_base'] = $assets_base;

// Admin 导航使用 /admin 作为基础
$nav_base = '/admin?page=';

// 获取当前页面信息（从页面文件中继承）
$current_page = $_GET['page'] ?? 'dashboard';

// 如果页面没有设置标题，使用默认值
if (!isset($page_title)) {
    $page_titles = [
        'dashboard' => '控制台',
    'single-works' => 'Single-Works 分类管理',
    'sketchbook' => 'Sketchbook 管理',
    'comics' => '漫画管理',
    'video-gallery' => 'Video Gallery 管理',
        'gallery-manager' => '画廊管理',
        'trash' => '回收站',
        'site-config' => '网站配置',
        'cache-manager' => '缓存管理',
        'tools' => '管理工具',
    'thumbnail-center' => '缩略图中心',
        'system-info' => '系统信息',
        'docs' => '项目文档'
    ];
    $page_title = $page_titles[$current_page] ?? '未知页面';
}

// 如果页面没有设置副标题，使用默认值
if (!isset($page_subtitle)) {
    $page_subtitle = '管理和配置您的网站内容';
}

// 获取当前用户信息
$current_user = [
    'name' => 'RZX',
    'email' => 'admin@rzx.me',
    'role' => 'Administrator'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - RZX.ME 后台管理</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons (纯 JavaScript 渲染，无需 CSS 文件) -->
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (用于页面中的 bi- 图标) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- 自定义样式 -->
    <link rel="stylesheet" href="<?= $assets_base ?>/css/admin.css">
    <style>
        /* 页面特定样式 */
        .page-content {
            padding: var(--spacing-xl);
            flex: 1;
        }
        
        .page-header {
            margin-bottom: var(--spacing-xl);
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
            color: var(--text-primary);
        }
        
        .page-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .content-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-medium);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-light);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .form-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: var(--spacing-md);
            color: var(--text-primary);
        }
        
        .form-group {
            margin-bottom: var(--spacing-md);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-control {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-small);
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
        }
        
        .btn-group {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-small);
            margin-bottom: var(--spacing-md);
        }
        
        .alert-success {
            background-color: #f0f9ff;
            color: #00a32a;
            border: 1px solid #bfdbfe;
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #d63638;
            border: 1px solid #fecaca;
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #0073aa;
            border: 1px solid #dbeafe;
        }
        .fa-solid {
            font-size: 24px;
            color: #0073aa;
        }
    </style>
</head>
<body class="admin-body">
    <!-- 侧边栏导航 -->
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <h2 class="site-title">RZX.ME</h2>
            <span class="site-subtitle">后台管理</span>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <a href="/admin/index.php">
                    <i data-feather="home"></i>
                    <span>控制台</span>
                </a>
            </li>

            <li class="menu-section">
                <span class="section-title">内容管理</span>
            </li>

            <li class="menu-item <?= $current_page === 'single-works' ? 'active' : '' ?>">
                <a href="/admin?page=single-works">
                    <i data-feather="image"></i>
                    <span>Single-Works分类管理</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'sketchbook' ? 'active' : '' ?>">
                <a href="/admin?page=sketchbook">
                    <i data-feather="book-open"></i>
                    <span>Sketchbook管理</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'comics' ? 'active' : '' ?>">
                <a href="/admin?page=comics">
                    <i data-feather="layers"></i>
                    <span>Comics管理</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'video-gallery' ? 'active' : '' ?>">
                <a href="/admin?page=video-gallery">
                    <i data-feather="film"></i>
                    <span>Video Gallery管理</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'gallery-manager' ? 'active' : '' ?>">
                <a href="/admin?page=gallery-manager">
                    <i data-feather="folder"></i>
                    <span>gallery管理</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'trash' ? 'active' : '' ?>">
                <a href="/admin?page=trash">
                    <i data-feather="trash-2"></i>
                    <span>回收站</span>
                </a>
            </li>

            <li class="menu-section">
                <span class="section-title">系统设置</span>
            </li>

            <li class="menu-item <?= $current_page === 'site-config' ? 'active' : '' ?>">
                <a href="/admin?page=site-config">
                    <i data-feather="settings"></i>
                    <span>网站配置</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'cache-manager' ? 'active' : '' ?>">
                <a href="/admin?page=cache-manager">
                    <i data-feather="database"></i>
                    <span>缓存管理</span>
                </a>
            </li>

            <li class="menu-section">
                <span class="section-title">工具</span>
            </li>

            <li class="menu-item <?= $current_page === 'tools' ? 'active' : '' ?>">
                <a href="/admin?page=tools<?= isset($_GET['dev']) ? '&dev' : '' ?>">
                    <i data-feather="tool"></i>
                    <span>管理工具</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'thumbnail-center' ? 'active' : '' ?>">
                <a href="/admin?page=thumbnail-center<?= isset($_GET['dev']) ? '&dev' : '' ?>">
                    <i data-feather="image"></i>
                    <span>缩略图中心</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'system-info' ? 'active' : '' ?>">
                <a href="/admin?page=system-info">
                    <i data-feather="info"></i>
                    <span>系统信息</span>
                </a>
            </li>

            <li class="menu-item <?= $current_page === 'docs' ? 'active' : '' ?>">
                <a href="/admin?page=docs">
                    <i data-feather="book"></i>
                    <span>项目文档</span>
                </a>
            </li>
            <li class="menu-item <?= $current_page === 'system-info' ? 'active' : '' ?>">
                <a href="/dev/fontawesome-icons-preview.html">
                    <i class="fa-solid fa-book"></i>
                    <span>fontawesome</span>
                </a>
            </li>
            <li class="menu-item <?= $current_page === 'system-info' ? 'active' : '' ?>">
                <a href="/dev/feather-icons-preview.html">
                    <i data-feather="printer"></i>
                    <span>feather-icons</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">R</div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($current_user['name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars($current_user['role']) ?></span>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- 主内容区域 -->
    <main class="admin-main">
        <!-- 顶部导航栏 -->
        <header class="admin-header">
            <div class="header-left">
                <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
                <span class="page-subtitle"><?= htmlspecialchars($page_subtitle) ?></span>
            </div>
            
            <div class="header-right">
                <button class="btn btn-outline" onclick="window.open('/', '_blank')">
                    <i data-feather="external-link"></i>
                    访问网站
                </button>
                
                <div class="user-menu">
                    <button class="user-menu-trigger">
                        <div class="user-avatar small">R</div>
                        <i data-feather="chevron-down"></i>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- 页面内容区域开始 -->