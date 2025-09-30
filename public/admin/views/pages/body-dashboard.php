<?php
/**
 * RZX.ME 后台管理系统 - 控制台内容部分
 */

// 页面配置
$page_title = '控制台';
$page_subtitle = '欢迎使用 RZX.ME 管理后台';
$_GET['page'] = 'dashboard';

// 获取统计信息
function getStats() {
    return [
        'total_pages' => 156,
        'total_galleries' => 12,
        'total_images' => 2431,
        'storage_used' => '2.4GB',
        'uptime_days' => 15,
        'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s')
    ];
}

$stats = getStats();
?>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white border-0 shadow-sm h-100 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold" id="totalWorks"><?= $stats['total_pages'] ?></h4>
                        <small class="opacity-75 d-block">总作品数</small>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> 12% 相比上月
                        </small>
                    </div>
                    <div>
                        <i class="bi bi-images fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white border-0 shadow-sm h-100 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold" id="totalImages"><?= number_format($stats['total_images']) ?></h4>
                        <small class="opacity-75 d-block">图片总数</small>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> 8% 相比上月
                        </small>
                    </div>
                    <div>
                        <i class="bi bi-image fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white border-0 shadow-sm h-100 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold" id="uptime"><?= $stats['uptime_days'] ?>天</h4>
                        <small class="opacity-75 d-block">系统运行</small>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> 持续运行中
                        </small>
                    </div>
                    <div>
                        <i class="bi bi-server fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white border-0 shadow-sm h-100 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold" id="storage"><?= $stats['storage_used'] ?></h4>
                        <small class="opacity-75 d-block">存储使用</small>
                        <small class="text-muted">
                            <i class="bi bi-hdd"></i> 总计可用
                        </small>
                    </div>
                    <div>
                        <i class="bi bi-database fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 主要内容区域 -->
<div class="row">
    <!-- 快速操作 -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-lightning-charge me-2 text-primary"></i>
                    快速操作
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/sort-config.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-image fs-4 mb-2"></i>
                            <span class="fw-medium">作品分类管理</span>
                            <small class="text-muted mt-1">管理作品分类配置</small>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/gallery-manager.php" class="btn btn-outline-success w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-folder fs-4 mb-2"></i>
                            <span class="fw-medium">画廊管理</span>
                            <small class="text-muted mt-1">管理图片画廊</small>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/thumbnail-config-manager.php" class="btn btn-outline-info w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-sliders fs-4 mb-2"></i>
                            <span class="fw-medium">缩略图配置</span>
                            <small class="text-muted mt-1">管理缩略图设置</small>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/cache-manager.php" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-arrow-clockwise fs-4 mb-2"></i>
                            <span class="fw-medium">缓存管理</span>
                            <small class="text-muted mt-1">清理系统缓存</small>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/tools.php" class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-tools fs-4 mb-2"></i>
                            <span class="fw-medium">系统工具</span>
                            <small class="text-muted mt-1">各种管理工具</small>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <a href="controllers/system-info.php" class="btn btn-outline-dark w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                            <i class="bi bi-info-circle fs-4 mb-2"></i>
                            <span class="fw-medium">系统信息</span>
                            <small class="text-muted mt-1">查看系统状态</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 系统状态和最近更新 -->
    <div class="col-lg-4">
        <!-- 系统状态 -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-speedometer2 me-2 text-success"></i>
                    系统状态
                </h5>
            </div>
            <div class="card-body">
                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>PHP 版本</span>
                        <span class="badge bg-success"><?= $stats['php_version'] ?></span>
                    </div>
                </div>
                
                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>内存使用</span>
                        <span><?= $stats['memory_usage'] ?>MB</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>磁盘空间</span>
                        <span id="diskUsage">65% 已使用</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="system-status-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>服务器时间</span>
                        <span class="text-muted" id="serverTime"><?= $stats['server_time'] ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 最近更新 -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-clock-history me-2 text-info"></i>
                    最近更新
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-medium">系统优化</h6>
                        <p class="mb-1 text-muted small">提升了页面加载速度</p>
                        <small class="text-muted">2小时前</small>
                    </div>
                </div>
                
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-medium">新功能上线</h6>
                        <p class="mb-1 text-muted small">缩略图配置管理系统</p>
                        <small class="text-muted">昨天</small>
                    </div>
                </div>
                
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-medium">系统维护</h6>
                        <p class="mb-1 text-muted small">优化了数据库性能</p>
                        <small class="text-muted">3天前</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 详细更新时间线 -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    详细更新日志
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">后台架构重构完成</h6>
                            <p class="text-muted mb-1">完成了标准化的MVC架构改造，统一了CSS类名和布局结构</p>
                            <small class="text-muted">2025-09-29 <?= date('H:i') ?></small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">模板系统优化</h6>
                            <p class="text-muted mb-1">重构了admin模板结构，实现了页面内容的动态加载</p>
                            <small class="text-muted">2025-09-29 15:30</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">系统性能监控</h6>
                            <p class="text-muted mb-1">添加了实时系统状态监控和性能指标显示</p>
                            <small class="text-muted">2025-09-28 10:15</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 自定义样式 */
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.btn:hover {
    transform: translateY(-1px);
}

/* 统计卡片动画 */
.stat-card:hover {
    transform: translateY(-3px);
}

/* 快速操作按钮 */
.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-secondary:hover,
.btn-outline-dark:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* 时间线样式 */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

/* 系统状态进度条 */
.system-status-item .progress {
    background: rgba(255,255,255,0.2);
}

/* 数字动画 */
@keyframes countUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.stat-card h4 {
    animation: countUp 0.6s ease-out;
}
</style>

<script>
function dashboardInit() {
    // 页面初始化逻辑
    console.log('Enhanced Dashboard页面已加载');
    
    // 延迟执行动画效果
    setTimeout(() => {
        updateStats();
        animateNumbers();
    }, 500);
    
    // 定时更新服务器时间
    setInterval(updateServerTime, 1000);
}

// 动态更新统计数据
function updateStats() {
    // 模拟实时数据更新
    const stats = {
        totalWorks: <?= $stats['total_pages'] ?>,
        totalImages: <?= $stats['total_images'] ?>,
        uptime: '<?= $stats['uptime_days'] ?>天',
        storage: '<?= $stats['storage_used'] ?>'
    };
    
    // 更新磁盘使用情况等动态数据
    document.getElementById('diskUsage').textContent = '65% 已使用';
}

// 数字动画效果
function animateNumbers() {
    const counters = document.querySelectorAll('#totalWorks, #totalImages');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
        if (target && !isNaN(target)) {
            let current = 0;
            const increment = target / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    if (counter.id === 'totalImages') {
                        counter.textContent = target.toLocaleString();
                    } else {
                        counter.textContent = target;
                    }
                    clearInterval(timer);
                } else {
                    if (counter.id === 'totalImages') {
                        counter.textContent = Math.floor(current).toLocaleString();
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }
            }, 50);
        }
    });
}

// 更新服务器时间
function updateServerTime() {
    const now = new Date();
    const timeString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + ' ' +
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0') + ':' + 
                      String(now.getSeconds()).padStart(2, '0');
    const timeElement = document.getElementById('serverTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// 页面加载后执行
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', dashboardInit);
} else {
    dashboardInit();
}
</script>