<?php
// 系统信息页面
$page_title = '系统信息';
$page_subtitle = '查看服务器环境和系统运行状态';
$_GET['page'] = 'system-info';

// 确保会话已启动
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 包含布局文件
require_once __DIR__ . '/../Views/layouts/admin-header.php';
?>


<div class="ray-body-box-useless">
    <!-- 页面标题 -->
    <div class="d-flex align-items-center mb-4">
        <div class="me-3">
            <div class="bg-primary bg-gradient rounded-circle p-3">
                <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
        </div>
        <div>
            <h1 class="mb-1 fw-bold">系统信息</h1>
            <p class="text-muted mb-0">查看服务器环境和系统运行状态</p>
        </div>
    </div>

    <!-- 系统信息卡片 -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-gradient text-white d-flex align-items-center">
                    <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h5 class="mb-0">PHP 环境信息</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">PHP 版本</div>
                                <div class="fw-bold text-primary"><?= PHP_VERSION ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">SAPI</div>
                                <div class="fw-bold text-success"><?= PHP_SAPI ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">操作系统</div>
                                <div class="fw-bold text-info"><?= PHP_OS_FAMILY ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">内存限制</div>
                                <div class="fw-bold text-warning"><?= ini_get('memory_limit') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- 服务器信息 -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">服务器软件</div>
                                <div class="fw-bold text-secondary"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">文档根目录</div>
                                <div class="fw-bold text-secondary small"><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light">
                                <div class="small text-muted mb-1">
                                    当前时间
                                    <span class="pulse-dot success ms-1"></span>
                                </div>
                                <div class="fw-bold text-secondary current-time"><?= date('Y-m-d H:i:s') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 性能监控 -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning bg-gradient text-white d-flex align-items-center">
                    <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <h5 class="mb-0">内存使用情况</h5>
                </div>
                <div class="card-body">
                    <?php
                    $memory_usage = memory_get_usage(true);
                    $memory_peak = memory_get_peak_usage(true);
                    $memory_limit = ini_get('memory_limit');
                    $memory_limit_bytes = 0;

                    if ($memory_limit != -1) {
                        $unit = strtoupper(substr($memory_limit, -1));
                        $memory_limit_numeric = (int)$memory_limit;
                        switch($unit) {
                            case 'G': $memory_limit_bytes = $memory_limit_numeric * 1024 * 1024 * 1024; break;
                            case 'M': $memory_limit_bytes = $memory_limit_numeric * 1024 * 1024; break;
                            case 'K': $memory_limit_bytes = $memory_limit_numeric * 1024; break;
                            default: $memory_limit_bytes = $memory_limit_numeric;
                        }
                    }

                    $usage_percent = $memory_limit_bytes > 0 ? round(($memory_usage / $memory_limit_bytes) * 100, 1) : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">当前使用</span>
                            <strong><?= number_format($memory_usage / 1024 / 1024, 2) ?> MB</strong>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <div class="progress mb-3 progress-animated" style="height: 12px;">
                                    <div class="progress-bar bg-<?= $usage_percent > 80 ? 'danger' : ($usage_percent > 60 ? 'warning' : 'success') ?>"
                                         style="width: <?= min($usage_percent, 100) ?>%">
                                        <small class="text-white fw-bold"><?= $usage_percent ?>%</small>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <div class="h6 mb-0 text-warning metric-value"><?= number_format($memory_peak / 1024 / 1024, 2) ?></div>
                                            <small class="text-muted">峰值使用 MB</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="h6 mb-0 text-info"><?= $usage_percent ?>%</div>
                                        <small class="text-muted">使用率</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="chart-container">
                                    <canvas id="memoryChart" width="100" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 磁盘空间 -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info bg-gradient text-white d-flex align-items-center">
                    <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                        <path d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/>
                    </svg>
                    <h5 class="mb-0">磁盘空间</h5>
                </div>
                <div class="card-body">
                    <?php
                    $disk_total = disk_total_space('.');
                    $disk_free = disk_free_space('.');
                    $disk_used = $disk_total - $disk_free;
                    $disk_percent = round(($disk_used / $disk_total) * 100, 1);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">已使用</span>
                            <strong><?= number_format($disk_used / 1024 / 1024 / 1024, 2) ?> GB</strong>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-8">
                                <div class="progress mb-3 progress-animated" style="height: 12px;">
                                    <div class="progress-bar bg-<?= $disk_percent > 90 ? 'danger' : ($disk_percent > 75 ? 'warning' : 'primary') ?>"
                                         style="width: <?= $disk_percent ?>%">
                                        <small class="text-white fw-bold"><?= $disk_percent ?>%</small>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <div class="h6 mb-0 text-success metric-value"><?= number_format($disk_free / 1024 / 1024 / 1024, 2) ?></div>
                                            <small class="text-muted">可用 GB</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="h6 mb-0 text-primary metric-value"><?= number_format($disk_total / 1024 / 1024 / 1024, 2) ?></div>
                                        <small class="text-muted">总容量 GB</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="chart-container">
                                    <canvas id="diskChart" width="100" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP配置信息 -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark bg-gradient text-white d-flex align-items-center">
                    <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                        <path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0L19.2 12l-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/>
                    </svg>
                    <h5 class="mb-0">重要配置项</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php
                        $important_settings = [
                            'max_execution_time' => ['label' => '最大执行时间', 'unit' => '秒', 'icon' => '⏱️'],
                            'post_max_size' => ['label' => 'POST最大大小', 'unit' => '', 'icon' => '📤'],
                            'upload_max_filesize' => ['label' => '上传文件限制', 'unit' => '', 'icon' => '📁'],
                            'max_file_uploads' => ['label' => '最大文件数', 'unit' => '个', 'icon' => '📂'],
                            'default_socket_timeout' => ['label' => '套接字超时', 'unit' => '秒', 'icon' => '🌐'],
                            'auto_prepend_file' => ['label' => '自动前置文件', 'unit' => '', 'icon' => '📋']
                        ];

                        foreach ($important_settings as $setting => $info):
                            $value = ini_get($setting) ?: '未设置';
                        ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light d-flex align-items-center">
                                <span class="me-2" style="font-size: 1.2em;"><?= $info['icon'] ?></span>
                                <div class="flex-grow-1">
                                    <div class="small text-muted"><?= $info['label'] ?></div>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($value) ?><?= $info['unit'] ? ' ' . $info['unit'] : '' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统状态指示器 -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-secondary bg-gradient text-white d-flex align-items-center">
                    <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h5 class="mb-0">系统状态</h5>
                </div>
                <div class="card-body">
                    <?php
                    $status_items = [
                        ['name' => 'Zlib 压缩', 'status' => extension_loaded('zlib'), 'icon' => '🗜️'],
                        ['name' => 'GD 图像处理', 'status' => extension_loaded('gd'), 'icon' => '🖼️'],
                        ['name' => 'cURL 网络', 'status' => extension_loaded('curl'), 'icon' => '🌐'],
                        ['name' => 'JSON 支持', 'status' => extension_loaded('json'), 'icon' => '📋'],
                        ['name' => 'OpenSSL 加密', 'status' => extension_loaded('openssl'), 'icon' => '🔐'],
                        ['name' => '文件上传', 'status' => (bool)ini_get('file_uploads'), 'icon' => '📤']
                    ];
                    ?>
                    <?php foreach ($status_items as $item): ?>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom status-item">
                        <div class="d-flex align-items-center">
                            <span class="me-3 fs-5"><?= $item['icon'] ?></span>
                            <div>
                                <div class="fw-medium"><?= $item['name'] ?></div>
                                <small class="text-muted">
                                    <?= $item['status'] ? '功能正常运行' : '功能未启用' ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="pulse-dot <?= $item['status'] ? 'success' : 'danger' ?> me-2"></span>
                            <span class="badge bg-<?= $item['status'] ? 'success' : 'danger' ?>">
                                <?= $item['status'] ? '✓ 启用' : '✗ 禁用' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 扩展模块信息 -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success bg-gradient text-white d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        <h5 class="mb-0">已加载的PHP扩展</h5>
                    </div>
                    <span class="badge bg-light text-dark"><?= count(get_loaded_extensions()) ?> 个扩展</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $extensions = get_loaded_extensions();
                        sort($extensions);
                        $core_extensions = ['Core', 'standard', 'SPL', 'Reflection', 'json', 'date'];
                        $security_extensions = ['openssl', 'hash', 'filter'];
                        $database_extensions = ['pdo', 'mysqli', 'sqlite3'];
                        $web_extensions = ['curl', 'session', 'zlib'];

                        foreach ($extensions as $ext):
                            $badge_class = 'secondary';
                            $icon = '📦';

                            if (in_array($ext, $core_extensions)) {
                                $badge_class = 'primary';
                                $icon = '⚙️';
                            } elseif (in_array($ext, $security_extensions)) {
                                $badge_class = 'warning';
                                $icon = '🔐';
                            } elseif (in_array($ext, $database_extensions)) {
                                $badge_class = 'info';
                                $icon = '🗄️';
                            } elseif (in_array($ext, $web_extensions)) {
                                $badge_class = 'success';
                                $icon = '🌐';
                            }
                        ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                            <span class="badge bg-<?= $badge_class ?> me-1 d-inline-flex align-items-center extension-badge"
                                  title="<?= htmlspecialchars($ext) ?> 扩展模块">
                                <span class="me-1"><?= $icon ?></span>
                                <?= htmlspecialchars($ext) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 系统信息页面专用样式 */
.system-info-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.system-info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.progress-animated {
    position: relative;
    overflow: hidden;
}

.progress-animated::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: progress-shine 2s infinite;
}

@keyframes progress-shine {
    0% { left: -100%; }
    50% { left: 100%; }
    100% { left: 100%; }
}

.status-item {
    transition: background-color 0.2s ease;
}

.status-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.extension-badge {
    transition: transform 0.2s ease;
    cursor: pointer;
}

.extension-badge:hover {
    transform: scale(1.05);
}

.metric-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 4px solid #007bff;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #495057;
}

.pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.pulse-dot.success {
    background-color: #28a745;
}

.pulse-dot.danger {
    background-color: #dc3545;
}

@keyframes pulse {
    0% { transform: scale(0.95); opacity: 1; }
    70% { transform: scale(1); opacity: 0.7; }
    100% { transform: scale(0.95); opacity: 1; }
}

.chart-container {
    position: relative;
    height: 120px;
}

.chart-bar {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 4px;
    transition: width 1s ease-in-out;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function pageInit() {
    console.log('系统信息页面已加载');

    // 添加卡片悬停效果
    document.querySelectorAll('.card').forEach(card => {
        card.classList.add('system-info-card');
    });

    // 为进度条添加动画效果
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
            bar.parentElement.classList.add('progress-animated');
        }, 500);
    });

    // 添加数字动画效果
    animateNumbers();

    // 创建内存使用图表
    createMemoryChart();

    // 创建磁盘使用图表
    createDiskChart();
}

function animateNumbers() {
    document.querySelectorAll('.metric-value').forEach(element => {
        const finalValue = parseFloat(element.textContent);
        if (!isNaN(finalValue)) {
            animateValue(element, 0, finalValue, 1500);
        }
    });
}

function animateValue(element, start, end, duration) {
    const increment = (end - start) / (duration / 16);
    let current = start;
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = current.toFixed(2);
    }, 16);
}

function createMemoryChart() {
    const ctx = document.getElementById('memoryChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['已使用', '可用'],
            datasets: [{
                data: [<?= $usage_percent ?>, <?= 100 - $usage_percent ?>],
                backgroundColor: ['#007bff', '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function createDiskChart() {
    const ctx = document.getElementById('diskChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['已使用', '可用'],
            datasets: [{
                data: [<?= $disk_percent ?>, <?= 100 - $disk_percent ?>],
                backgroundColor: ['#17a2b8', '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// 实时更新功能
setInterval(() => {
    updateRealTimeInfo();
}, 30000); // 每30秒更新一次

function updateRealTimeInfo() {
    // 这里可以添加AJAX调用来实时更新数据
    console.log('更新实时信息...');

    // 更新当前时间
    const timeElement = document.querySelector('.current-time');
    if (timeElement) {
        timeElement.textContent = new Date().toLocaleString('zh-CN');
    }
}
</script>

<?php require_once __DIR__ . '/../Views/layouts/admin-footer.php'; ?>
