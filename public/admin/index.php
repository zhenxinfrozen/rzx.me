<?php
/**
 * RZX.ME 后台管理系统 - 主页
 * 
 * 现代化后台管理界面，参考WordPress设计风格
 * 提供网站内容管理、系统配置等功能
 */

require_once __DIR__ . '/../../app/bootstrap.php';

// 简单的认证检查（未来可扩展为完整的用户系统）
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    // 开发期间使用 ?dev 参数跳过认证
    header('Location: login.php');
    exit;
}

// 获取当前用户信息（模拟）
$current_user = [
    'name' => 'RZX',
    'email' => 'admin@rzx.me',
    'role' => 'Administrator'
];

// 系统统计信息
$stats = [
    'total_pages' => 6,
    'total_galleries' => count(glob(__DIR__ . '/../assets/images/galleries/*', GLOB_ONLYDIR)),
    'total_images' => count(glob(__DIR__ . '/../assets/images/galleries/*/*.{jpg,jpeg,png,gif}', GLOB_BRACE)),
    'storage_used' => formatBytes(getDirSize(__DIR__ . '/../assets'))
];

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getDirSize($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : getDirSize($each);
    }
    return $size;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZX.ME 后台管理</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- 侧边栏导航 -->
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <h2 class="site-title">RZX.ME</h2>
            <span class="site-subtitle">后台管理</span>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-item active">
                <a href="index.php">
                    <i data-feather="home"></i>
                    <span>控制台</span>
                </a>
            </li>
            
            <li class="menu-section">
                <span class="section-title">内容管理</span>
            </li>
            
            <li class="menu-item">
                <a href="controllers/sort-config.php">
                    <i data-feather="image"></i>
                    <span>作品分类管理</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="controllers/gallery-manager.php">
                    <i data-feather="folder"></i>
                    <span>画廊管理</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="controllers/trash.php">
                    <i data-feather="trash-2"></i>
                    <span>回收站</span>
                </a>
            </li>
            
            <li class="menu-section">
                <span class="section-title">系统设置</span>
            </li>
            
            <li class="menu-item">
                <a href="controllers/site-config.php">
                    <i data-feather="settings"></i>
                    <span>网站配置</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="controllers/cache-manager.php">
                    <i data-feather="database"></i>
                    <span>缓存管理</span>
                </a>
            </li>
            
            <li class="menu-section">
                <span class="section-title">工具</span>
            </li>
            
            <li class="menu-item">
                <a href="controllers/system-info.php">
                    <i data-feather="info"></i>
                    <span>系统信息</span>
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
                <h1 class="page-title">控制台</h1>
                <span class="page-subtitle">欢迎回到 RZX.ME 管理后台</span>
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
        
        <!-- 控制台内容 -->
        <div class="admin-content">
            <!-- 统计卡片 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i data-feather="file-text"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_pages'] ?></h3>
                        <p>总页面数</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i data-feather="folder"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_galleries'] ?></h3>
                        <p>画廊数量</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i data-feather="image"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_images'] ?></h3>
                        <p>图片总数</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i data-feather="hard-drive"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['storage_used'] ?></h3>
                        <p>存储使用</p>
                    </div>
                </div>
            </div>
            
            <!-- 快速操作和最近活动 -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>快速操作</h3>
                        <p>常用管理功能</p>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="controllers/sort-config.php" class="quick-action">
                            <i data-feather="image"></i>
                            <span>管理作品分类</span>
                        </a>
                        
                        <a href="controllers/gallery-manager.php" class="quick-action">
                            <i data-feather="upload"></i>
                            <span>上传新作品</span>
                        </a>
                        
                        <a href="controllers/cache-manager.php" class="quick-action">
                            <i data-feather="refresh-cw"></i>
                            <span>清理缓存</span>
                        </a>
                        
                        <a href="controllers/site-config.php" class="quick-action">
                            <i data-feather="settings"></i>
                            <span>网站设置</span>
                        </a>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>系统状态</h3>
                        <p>运行状况概览</p>
                    </div>
                    
                    <div class="system-status">
                        <div class="status-item">
                            <span class="status-label">PHP 版本</span>
                            <span class="status-value"><?= PHP_VERSION ?></span>
                            <span class="status-indicator good"></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label">内存使用</span>
                            <span class="status-value"><?= formatBytes(memory_get_peak_usage(true)) ?></span>
                            <span class="status-indicator good"></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label">图片处理</span>
                            <span class="status-value"><?= extension_loaded('gd') ? '支持' : '不支持' ?></span>
                            <span class="status-indicator <?= extension_loaded('gd') ? 'good' : 'error' ?>"></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label">缩略图缓存</span>
                            <span class="status-value">正常</span>
                            <span class="status-indicator good"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 最近更新 -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h3>最近更新</h3>
                    <p>系统变更记录</p>
                </div>
                
                <div class="recent-updates">
                    <div class="update-item">
                        <div class="update-icon">
                            <i data-feather="plus-circle"></i>
                        </div>
                        <div class="update-content">
                            <h4>后台管理系统上线</h4>
                            <p>创建了现代化的后台管理界面，支持作品分类管理和系统配置</p>
                            <span class="update-time">刚刚</span>
                        </div>
                    </div>
                    
                    <div class="update-item">
                        <div class="update-icon">
                            <i data-feather="image"></i>
                        </div>
                        <div class="update-content">
                            <h4>作品分类排序功能</h4>
                            <p>完善了single-works页面的分类管理，支持拖拽排序和自定义显示名称</p>
                            <span class="update-time">今天</span>
                        </div>
                    </div>
                    
                    <div class="update-item">
                        <div class="update-icon">
                            <i data-feather="folder"></i>
                        </div>
                        <div class="update-content">
                            <h4>缩略图系统优化</h4>
                            <p>修复了缩略图路径问题，提升了画廊加载性能</p>
                            <span class="update-time">昨天</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>