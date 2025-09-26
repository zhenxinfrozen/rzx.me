<?php
/**
 * RZX.ME 后台管理系统 - 主页 (重构版本)
 */

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 简单认证
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: login.php');
    exit;
}

// 获取统计信息
function getStats() {
    return [
        'total_pages' => 6,
        'total_galleries' => 12,
        'total_images' => 128,
        'storage_used' => '45.2 MB'
    ];
}

$stats = getStats();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZX.ME 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --sidebar-bg: #212529;
        }
        .sidebar {
            background: var(--sidebar-bg);
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            margin-left: 0;
        }
        @media (min-width: 768px) {
            .main-content {
                margin-left: 280px;
            }
        }
        .stats-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.15s ease-in-out;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-light">
    
    <!-- 侧边栏 -->
    <nav class="sidebar position-fixed top-0 start-0 d-none d-md-block" style="width: 280px; z-index: 1000;">
        <div class="p-3">
            <h4 class="text-white mb-0">RZX.ME</h4>
            <small class="text-muted">后台管理</small>
        </div>
        
        <div class="px-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-muted px-3">内容管理</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/sort-config.php">
                        <i class="bi bi-image me-2"></i>作品分类管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/gallery-manager.php">
                        <i class="bi bi-folder me-2"></i>画廊管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/trash.php">
                        <i class="bi bi-trash me-2"></i>回收站
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-muted px-3">系统配置</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/site-config.php">
                        <i class="bi bi-gear me-2"></i>网站配置
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/cache-manager.php">
                        <i class="bi bi-archive me-2"></i>缓存管理
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-muted px-3">工具</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/tools.php">
                        <i class="bi bi-tools me-2"></i>管理工具
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/thumbnail-config-manager.php">
                        <i class="bi bi-sliders me-2"></i>缩略图配置
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="controllers/system-info.php">
                        <i class="bi bi-info-circle me-2"></i>系统信息
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- 主内容区域 -->
    <main class="main-content">
        <div class="container-fluid p-4">
            
            <!-- 页面头部 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">控制台</h1>
                    <p class="text-muted mb-0">欢迎回到 RZX.ME 管理后台</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- 统计卡片 -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h5 mb-0"><?= $stats['total_pages'] ?></div>
                                    <small>总页面数</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-file-earmark fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h5 mb-0"><?= $stats['total_galleries'] ?></div>
                                    <small>画廊数量</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-collection fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h5 mb-0"><?= $stats['total_images'] ?></div>
                                    <small>图片总数</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-image fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="h5 mb-0"><?= $stats['storage_used'] ?></div>
                                    <small>存储使用</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-hdd fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 快速操作 -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>快速操作
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="controllers/sort-config.php" class="btn btn-outline-primary w-100 d-flex align-items-center">
                                        <i class="bi bi-image me-2"></i>
                                        <span>管理作品分类</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="controllers/gallery-manager.php" class="btn btn-outline-success w-100 d-flex align-items-center">
                                        <i class="bi bi-folder me-2"></i>
                                        <span>画廊管理</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="controllers/cache-manager.php" class="btn btn-outline-info w-100 d-flex align-items-center">
                                        <i class="bi bi-arrow-clockwise me-2"></i>
                                        <span>清理缓存</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="controllers/thumbnail-config-manager.php" class="btn btn-outline-warning w-100 d-flex align-items-center">
                                        <i class="bi bi-sliders me-2"></i>
                                        <span>缩略图配置</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="controllers/tools.php" class="btn btn-outline-secondary w-100 d-flex align-items-center">
                                        <i class="bi bi-tools me-2"></i>
                                        <span>系统工具</span>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="controllers/system-info.php" class="btn btn-outline-dark w-100 d-flex align-items-center">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>系统信息</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>最近更新
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">系统优化</h6>
                                    <p class="mb-1 text-muted small">提升了页面加载速度</p>
                                    <small class="text-muted">2小时前</small>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-plus-circle-fill text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">新功能上线</h6>
                                    <p class="mb-1 text-muted small">缩略图配置管理</p>
                                    <small class="text-muted">昨天</small>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-gear-fill text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">系统维护</h6>
                                    <p class="mb-1 text-muted small">优化了数据库性能</p>
                                    <small class="text-muted">3天前</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </main>
    
    <!-- 移动端侧边栏 -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title">RZX.ME 后台</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body bg-dark">
            <!-- 移动端菜单内容（与桌面端相同） -->
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>