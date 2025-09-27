<?php
/**
 * 控制台页面内容
 * 只包含页面主体内容，不包含 HTML 结构
 */
?>

<!-- 页面标题 -->
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-1"><?= $pageTitle ?></h1>
            <p class="text-muted mb-0"><?= $pageSubtitle ?></p>
        </div>
        <div>
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> 快速操作
            </button>
        </div>
    </div>
</div>

<!-- 统计卡片 -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">总作品数</h6>
                        <h2 class="mb-0 text-primary" id="totalWorks">-</h2>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> 12% 相比上月
                        </small>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-images text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">图片总数</h6>
                        <h2 class="mb-0 text-info" id="totalImages">-</h2>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> 8% 相比上月
                        </small>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-photo-video text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">系统运行</h6>
                        <h2 class="mb-0 text-success" id="uptime">-</h2>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 持续运行中
                        </small>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-server text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">存储空间</h6>
                        <h2 class="mb-0 text-warning" id="storage">-</h2>
                        <small class="text-muted">
                            <i class="fas fa-hdd"></i> 总计可用
                        </small>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-database text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 快速操作和系统状态 -->
<div class="row g-4">
    <!-- 快速操作 -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt text-warning me-2"></i>快速操作
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="?page=sort-config" class="btn btn-outline-primary w-100 quick-action-btn">
                            <i class="fas fa-sort mb-2"></i><br>
                            作品排序
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=gallery-manager" class="btn btn-outline-info w-100 quick-action-btn">
                            <i class="fas fa-images mb-2"></i><br>
                            图片管理
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=cache-manager" class="btn btn-outline-success w-100 quick-action-btn">
                            <i class="fas fa-broom mb-2"></i><br>
                            清理缓存
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?page=system-info" class="btn btn-outline-secondary w-100 quick-action-btn">
                            <i class="fas fa-info-circle mb-2"></i><br>
                            系统信息
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 系统状态 -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-heartbeat text-danger me-2"></i>系统状态
                </h5>
            </div>
            <div class="card-body">
                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>PHP 版本</span>
                        <span class="badge bg-success"><?= PHP_VERSION ?></span>
                    </div>
                </div>
                
                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>内存使用</span>
                        <span><?= round(memory_get_usage(true) / 1024 / 1024, 2) ?>MB</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="system-status-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>磁盘空间</span>
                        <span id="diskUsage">检测中...</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="system-status-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>服务器时间</span>
                        <span class="text-muted" id="serverTime"><?= date('Y-m-d H:i:s') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最近更新日志 -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history text-primary me-2"></i>最近更新
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">后台架构重构完成</h6>
                            <p class="text-muted mb-1">完成了标准化的MVC架构改造，统一了CSS类名和布局结构</p>
                            <small class="text-muted">2025-09-27 <?= date('H:i') ?></small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">响应式设计优化</h6>
                            <p class="text-muted mb-1">改进了移动端显示效果，优化了触摸操作体验</p>
                            <small class="text-muted">2025-09-26 15:30</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">系统性能监控</h6>
                            <p class="text-muted mb-1">添加了实时系统状态监控和性能指标显示</p>
                            <small class="text-muted">2025-09-25 10:15</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardInit() {
    // 动态更新统计数据
    function updateStats() {
        // 模拟数据加载
        document.getElementById('totalWorks').textContent = '156';
        document.getElementById('totalImages').textContent = '2,431';
        document.getElementById('uptime').textContent = '15天';
        document.getElementById('storage').textContent = '2.4GB';
        
        // 更新磁盘使用情况
        document.getElementById('diskUsage').textContent = '65% 已使用';
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
        document.getElementById('serverTime').textContent = timeString;
    }

    // 动画效果
    function animateNumbers() {
        const counters = document.querySelectorAll('[id^="total"]');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
            if (target && !isNaN(target)) {
                let current = 0;
                const increment = target / 30;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = counter.textContent.replace(/[\d,]+/, target.toLocaleString());
                        clearInterval(timer);
                    } else {
                        counter.textContent = counter.textContent.replace(/[\d,]+/, Math.floor(current).toLocaleString());
                    }
                }, 50);
            }
        });
    }

    // 初始化
    setTimeout(updateStats, 500);
    setTimeout(animateNumbers, 800);
    
    // 定时更新服务器时间
    setInterval(updateServerTime, 1000);
    
    console.log('Dashboard 页面已初始化');
}

// 页面加载后执行
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', dashboardInit);
} else {
    dashboardInit();
}
</script>