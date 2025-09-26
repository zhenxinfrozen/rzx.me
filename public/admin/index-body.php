<?php
/**
 * RZX.ME 后台管理系统 - 主页内容
 */

// 页面配置
$page_title = '控制台';
$page_subtitle = '欢迎回到 RZX.ME 管理后台';
$current_page = 'dashboard';

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

// 包含公共头部
require_once 'views/layouts/header.php';
?>

<!-- 统计卡片 -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $stats['total_pages'] ?></h4>
                        <small class="opacity-75">总页面数</small>
                    </div>
                    <div>
                        <i class="bi bi-file-earmark fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $stats['total_galleries'] ?></h4>
                        <small class="opacity-75">画廊数量</small>
                    </div>
                    <div>
                        <i class="bi bi-collection fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $stats['total_images'] ?></h4>
                        <small class="opacity-75">图片总数</small>
                    </div>
                    <div>
                        <i class="bi bi-image fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $stats['storage_used'] ?></h4>
                        <small class="opacity-75">存储使用</small>
                    </div>
                    <div>
                        <i class="bi bi-hdd fs-1 opacity-50"></i>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">服务器状态</span>
                    <span class="badge bg-success">正常</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">内存使用</span>
                    <span class="text-primary fw-medium">68%</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">磁盘空间</span>
                    <span class="text-warning fw-medium">82%</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">在线用户</span>
                    <span class="text-info fw-medium">1</span>
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
.card.bg-primary:hover,
.card.bg-success:hover,
.card.bg-info:hover,
.card.bg-warning:hover {
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
</style>

<script>
function pageInit() {
    // 页面初始化逻辑
    console.log('Dashboard页面已加载');
    
    // 添加统计数字动画效果
    animateNumbers();
}

function animateNumbers() {
    const numberElements = document.querySelectorAll('.card h4');
    
    numberElements.forEach(element => {
        const finalNumber = parseInt(element.textContent);
        if (!isNaN(finalNumber)) {
            let currentNumber = 0;
            const increment = finalNumber / 20;
            const timer = setInterval(() => {
                currentNumber += increment;
                if (currentNumber >= finalNumber) {
                    element.textContent = finalNumber;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(currentNumber);
                }
            }, 50);
        }
    });
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>