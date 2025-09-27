<?php
/**
 * 管理工具页面
 * 系统维护和优化工具集合
 */

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: ../login.php');
    exit;
}

// 设置页面信息
$page_title = '🔧 管理工具';
$page_subtitle = '系统维护和优化工具集合';
$_GET['page'] = 'tools';

// 处理业务逻辑
$message = "";
$message_type = "";

if ($_POST) {
    // 处理工具操作...
}

// 包含头部
require_once '../views/layouts/header.php';
?>

<div class="admin-page-content">
<!-- 工具卡片网格 -->
<div class="row g-3">
    <!-- 缩略图管理器 -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-primary text-white border-0 position-relative">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                        </svg>
                    </div>
                    <h5 class="mb-0 fw-semibold">缩略图管理器</h5>
                </div>
                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                    <i class="bi bi-gear fs-5"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-4 flex-grow-1">
                    统一的缩略图生成、清理和管理工具。支持所有Gallery类型，提供批量处理和进度监控。
                </p>
                <a href="thumbnail-manager.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right-circle me-2"></i>打开工具
                </a>
            </div>
        </div>
    </div>

    <!-- 缩略图配置管理 -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-success text-white border-0 position-relative">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                        </svg>
                    </div>
                    <h5 class="mb-0 fw-semibold">缩略图配置管理</h5>
                </div>
                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                    <i class="bi bi-nut fs-5"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-4 flex-grow-1">
                    配置和管理缩略图生成参数。支持预设管理、自定义配置、实时测试和预览功能。
                </p>
                <a href="thumbnail-config-manager.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-success btn-lg">
                    <i class="bi bi-arrow-right-circle me-2"></i>配置管理
                </a>
            </div>
        </div>
    </div>

    <!-- 缓存清理 -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-info text-white border-0 position-relative">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M9,3V4H4V6H5V19A2,2 0 0,0 7,21H17A2,2 0 0,0 19,19V6H20V4H15V3H9M7,6H17V19H7V6M9,8V17H11V8H9M13,8V17H15V8H13Z"/>
                        </svg>
                    </div>
                    <h5 class="mb-0 fw-semibold">缓存清理</h5>
                </div>
                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                    <i class="bi bi-arrow-clockwise fs-5"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-4 flex-grow-1">
                    清理系统缓存文件，包括临时文件、日志文件和过期数据。
                </p>
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-clock me-2"></i>即将推出
                </button>
            </div>
        </div>
    </div>

    <!-- 数据分析 -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-warning text-dark border-0 position-relative">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z"/>
                        </svg>
                    </div>
                    <h5 class="mb-0 fw-semibold">数据分析</h5>
                </div>
                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                    <i class="bi bi-graph-up fs-5"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-4 flex-grow-1">
                    分析网站数据，生成统计报告，监控系统性能和用户访问情况。
                </p>
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-clock me-2"></i>即将推出
                </button>
            </div>
        </div>
    </div>

    <!-- 系统诊断 -->
    <div class="col-lg-6 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-secondary text-white border-0 position-relative">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                            <path d="M17,17H7V7H17M21,11V9H19V7C19,5.89 18.1,5 17,5H15V3H13V5H11V3H9V5H7C5.89,5 5.01,5.89 5.01,7L5,19C5,20.1 5.89,21 7,21H19C20.1,21 21,20.1 21,19V17H23V15H21V13H23V11M17,13V15H15V17H13V15H11V13H13V11H15V13H17Z"/>
                        </svg>
                    </div>
                    <h5 class="mb-0 fw-semibold">系统诊断</h5>
                </div>
                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted mb-4 flex-grow-1">
                    检查系统配置，诊断潜在问题，提供优化建议和修复方案。
                </p>
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-clock me-2"></i>即将推出
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 工具使用说明 -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-info-circle text-primary me-2"></i>工具使用说明
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-image"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">缩略图管理器</h6>
                        <p class="text-muted mb-0 small">
                            用于批量生成、更新或清理图片缩略图，支持所有Gallery类型的统一管理。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-sliders"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">缩略图配置管理</h6>
                        <p class="text-muted mb-0 small">
                            配置缩略图生成参数，管理预设配置，支持自定义尺寸、质量、格式等设置。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-terminal"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">命令行工具</h6>
                        <p class="text-muted mb-0 small">
                            可通过 <code class="bg-light px-2 py-1 rounded">php app/Console/GenerateThumbnails.php</code> 执行命令行操作。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-lightning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">自动维护</h6>
                        <p class="text-muted mb-0 small">
                            页面访问时会自动生成缺失的缩略图，无需手动干预。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">性能优化</h6>
                        <p class="text-muted mb-0 small">
                            定期使用这些工具可以提升网站性能，优化用户访问体验。
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">系统维护</h6>
                        <p class="text-muted mb-0 small">
                            定期维护确保系统稳定运行，及时发现和解决潜在问题。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 确保Bootstrap Icons加载 */
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css');

/* 管理页面内容区域优化 */
.admin-page-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
    padding: 2rem !important;
}

/* 工具卡片优化 */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

/* 卡片头部渐变 */
.card-header.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.card-header.bg-success {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%) !important;
}

.card-header.bg-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
}

.card-header.bg-warning {
    background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%) !important;
}

.card-header.bg-secondary {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%) !important;
}

/* 按钮优化 */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}

/* 禁用按钮样式 */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 图标优化 */
.bi {
    vertical-align: -0.125em;
}

/* 说明区域优化 */

.card .row .col-lg-6 .d-flex {
    position: relative;
    padding: 1rem;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.card .row .col-lg-6 .d-flex:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* 响应式优化 */
@media (max-width: 1200px) {
    .admin-main {
        margin-left: 0;
        padding-left: 260px;
    }
}

@media (max-width: 768px) {
    .admin-main {
        margin-left: 0;
        padding-left: 0;
    }
    
    .admin-page-content {
        padding: 1rem;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .row {
        margin: 0 -0.5rem;
    }
    
    .row > * {
        padding: 0 0.5rem;
    }
}

/* 文字优化 */
h5, h6 {
    font-weight: 600;
    letter-spacing: 0.025em;
}

.text-muted {
    color: #6c757d !important;
    line-height: 1.5;
}

/* 圆形图标容器优化 */
.rounded-circle {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 卡片头部图标容器 */
.card-header .bg-white.bg-opacity-20 {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50% !important;
}

.card-header .bg-white.bg-opacity-20 i {
    line-height: 1;
    font-size: 1.25rem !important;
    display: inline-block;
}

/* Bootstrap Icons样式修复 */
.bi::before {
    display: inline-block;
    content: "";
    vertical-align: -.125em;
    background-image: var(--bs-icon);
    background-repeat: no-repeat;
    background-size: 1rem 1rem;
}

/* 强制显示图标 */
.bi-image::before { content: "\f3f2"; }
.bi-sliders::before { content: "\f465"; }
.bi-trash::before { content: "\f5de"; }
.bi-bar-chart::before { content: "\f1b8"; }
.bi-cpu::before { content: "\f32a"; }

/* 代码块优化 */
code {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    font-size: 0.85em;
}

/* 页面加载动画 */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.card {
    animation: fadeIn 0.3s ease-out;
}

/* 按钮脉冲效果 */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

.btn-primary:not(:disabled):hover {
    animation: pulse 1.5s infinite;
}

/* 图标旋转效果 */
.card-header .position-absolute i {
    transition: transform 0.3s ease;
}

.card:hover .card-header .position-absolute i {
    transform: rotate(15deg);
}
</style>

<script>
function pageInit() {
    // 页面初始化逻辑
    console.log('管理工具页面已加载');
}
</script>

</div>
<!-- admin-page-content 结束 -->

<?php require_once '../views/layouts/footer.php'; ?>