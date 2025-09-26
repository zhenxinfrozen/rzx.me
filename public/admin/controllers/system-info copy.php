<?php
/**
 * 系统信息页面
 * 显示服务器环境、PHP配置、系统状态等详细信息
 */

// 设置页面信息
$page_title = 'ℹ️ 系统信息';
$page_subtitle = '显示服务器环境、PHP配置、系统状态等详细信息';
$_GET['page'] = 'system-info';

require_once '../views/layouts/header.php';

// 获取详细系统信息
function getSystemInfo() {
    return [
        'php' => [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'os' => PHP_OS,
            'architecture' => php_uname('m'),
            'build_date' => phpversion(),
        ],
        'server' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'port' => $_SERVER['SERVER_PORT'] ?? '80',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
        ],
        'system' => [
            'hostname' => gethostname(),
            'load_average' => sys_getloadavg(),
            'uptime' => function_exists('sys_getloadavg') ? 'Available' : 'Not available',
            'temp_dir' => sys_get_temp_dir(),
        ],
        'memory' => [
            'limit' => ini_get('memory_limit'),
            'usage' => formatBytes(memory_get_usage(true)),
            'peak_usage' => formatBytes(memory_get_peak_usage(true)),
            'real_usage' => formatBytes(memory_get_usage(false)),
        ],
        'extensions' => [
            'gd' => extension_loaded('gd'),
            'imagick' => extension_loaded('imagick'),
            'curl' => extension_loaded('curl'),
            'json' => extension_loaded('json'),
            'mbstring' => extension_loaded('mbstring'),
            'opcache' => extension_loaded('opcache'),
            'zip' => extension_loaded('zip'),
            'openssl' => extension_loaded('openssl'),
            'pdo' => extension_loaded('pdo'),
            'mysqli' => extension_loaded('mysqli'),
            'fileinfo' => extension_loaded('fileinfo'),
            'iconv' => extension_loaded('iconv'),
        ],
        'limits' => [
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ]
    ];
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getDiskInfo() {
    $total = disk_total_space('.');
    $free = disk_free_space('.');
    $used = $total - $free;
    
    return [
        'total' => formatBytes($total),
        'used' => formatBytes($used),
        'free' => formatBytes($free),
        'usage_percent' => round(($used / $total) * 100, 2)
    ];
}

$system_info = getSystemInfo();
$disk_info = getDiskInfo();
?>

<div class="admin-page-content">

<!-- 页面标题 -->
<div class="d-flex align-items-center mb-4">
    <div class="me-3">
        <div class="bg-gradient rounded-circle p-3" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
            <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,7H13V9H11V7M11,11H13V17H11V11Z"/>
            </svg>
        </div>
    </div>
    <div>
        <h1 class="mb-1 fw-bold">系统信息</h1>
        <p class="text-muted mb-0">查看服务器环境、PHP配置和系统运行状态</p>
    </div>
</div>

<!-- PHP 信息 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">🐘 PHP 环境信息</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($system_info['php'] as $key => $value): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted fw-semibold"><?= str_replace('_', ' ', ucwords($key)) ?></div>
                        <div class="fs-6 font-monospace text-break"><?= htmlspecialchars($value) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 服务器信息 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">🖥️ 服务器信息</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($system_info['server'] as $key => $value): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted fw-semibold"><?= str_replace('_', ' ', ucwords($key)) ?></div>
                        <div class="fs-6 font-monospace text-break"><?= htmlspecialchars($value) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 内存使用情况 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">💾 内存使用情况</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($system_info['memory'] as $key => $value): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="text-center border rounded p-3 h-100">
                        <div class="small text-muted fw-semibold"><?= str_replace('_', ' ', ucwords($key)) ?></div>
                        <div class="fs-4 fw-bold text-primary"><?= htmlspecialchars($value) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 磁盘空间 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">💿 磁盘空间</h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-semibold">磁盘使用情况</span>
            <span class="fw-bold"><?= $disk_info['usage_percent'] ?>% 已使用</span>
        </div>
        
        <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar" role="progressbar" style="width: <?= $disk_info['usage_percent'] ?>%" 
                 aria-valuenow="<?= $disk_info['usage_percent'] ?>" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4 text-center">
                <div class="border rounded p-3">
                    <div class="small text-muted fw-semibold">总容量</div>
                    <div class="fs-5 fw-bold text-secondary"><?= $disk_info['total'] ?></div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="border rounded p-3">
                    <div class="small text-muted fw-semibold">已使用</div>
                    <div class="fs-5 fw-bold text-warning"><?= $disk_info['used'] ?></div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="border rounded p-3">
                    <div class="small text-muted fw-semibold">可用空间</div>
                    <div class="fs-5 fw-bold text-success"><?= $disk_info['free'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PHP 扩展 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">🧩 PHP 扩展</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($system_info['extensions'] as $extension => $loaded): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded p-3 text-center h-100">
                        <div class="fw-semibold mb-2"><?= htmlspecialchars($extension) ?></div>
                        <span class="badge <?= $loaded ? 'bg-success' : 'bg-danger' ?> fs-6">
                            <?= $loaded ? '✓ 已加载' : '✗ 未加载' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- PHP 限制 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">⚙️ PHP 配置限制</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($system_info['limits'] as $key => $value): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted fw-semibold"><?= str_replace('_', ' ', ucwords($key)) ?></div>
                        <div class="fs-6 font-monospace fw-bold text-primary">
                            <?= htmlspecialchars($value === '' ? '无限制' : $value) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 环境变量 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">🌍 重要环境变量</h5>
    </div>
    <div class="card-body p-0">
        <?php 
        $important_vars = [
            'PATH', 'HOME', 'USER', 'TEMP', 'TMP', 'PHPRC'
        ];
        ?>
        
        <div class="table-responsive" style="max-height: 400px;">
            <table class="table table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th class="fw-semibold">变量名</th>
                        <th class="fw-semibold">值</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($important_vars as $var): ?>
                        <?php if (getenv($var)): ?>
                            <tr>
                                <td class="font-monospace fw-bold"><?= htmlspecialchars($var) ?></td>
                                <td class="font-monospace small text-break"><?= htmlspecialchars(getenv($var)) ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 系统工具 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">🔧 系统工具</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <button class="btn btn-outline-primary w-100" onclick="downloadPhpInfo()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    导出 PHP 信息
                </button>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <button class="btn btn-outline-success w-100" onclick="checkSystemHealth()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                    </svg>
                    系统健康检查
                </button>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <button class="btn btn-outline-warning w-100" onclick="viewErrorLog()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                        <path d="M4.5 5.5A.5.5 0 0 1 5 5h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0 2A.5.5 0 0 1 5 7h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0 2A.5.5 0 0 1 5 9h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    查看错误日志
                </button>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <button class="btn btn-outline-info w-100" onclick="testPerformance()">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="M6.5 0C3.48 0 1 2.48 1 5.5c0 2.52 1.7 4.65 4 5.28v1.97c-.6.34-1 .98-1 1.75 0 1.1.9 2 2 2s2-.9 2-2c0-.77-.4-1.41-1-1.75v-1.97c2.3-.63 4-2.76 4-5.28C12 2.48 9.52 0 6.5 0zm0 1C8.97 1 11 3.03 11 5.5S8.97 10 6.5 10 2 7.97 2 5.5 4.03 1 6.5 1z"/>
                        <path d="M6.5 2C4.57 2 3 3.57 3 5.5S4.57 9 6.5 9 10 7.43 10 5.5 8.43 2 6.5 2zm0 1C7.88 3 9 4.12 9 5.5S7.88 8 6.5 8 4 6.88 4 5.5 5.12 3 6.5 3z"/>
                    </svg>
                    性能测试
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* 系统信息页面特定样式 */
.progress-bar {
    background: linear-gradient(45deg, #28a745, #20c997);
    transition: width 0.6s ease;
}

.card-header {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-info)) !important;
}

.card-header.bg-success {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
}

.card-header.bg-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
}

.card-header.bg-secondary {
    background: linear-gradient(135deg, #6c757d, #495057) !important;
}

.card-header.bg-dark {
    background: linear-gradient(135deg, #343a40, #212529) !important;
}

.card-header.bg-info {
    background: linear-gradient(135deg, #17a2b8, #007bff) !important;
}

.badge {
    font-size: 0.8em !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}
</style>

<script>
// 页面特定的JavaScript
function pageInit() {
    // 实时更新内存使用情况
    setInterval(updateMemoryUsage, 30000); // 30秒更新一次
    
    window.downloadPhpInfo = function() {
        // 创建一个隐藏的表单来提交phpinfo请求
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'system-info.php';
        form.target = '_blank';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'export_phpinfo';
        input.value = '1';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };
    
    window.checkSystemHealth = function() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>检查中...';
        
        // 模拟健康检查
        setTimeout(() => {
            const issues = [];
            
            // 检查内存使用率
            const memoryUsage = parseInt(document.querySelector('.progress-bar').style.width) || 0;
            if (memoryUsage > 80) {
                issues.push('内存使用率过高');
            }
            
            // 检查扩展
            const extensions = document.querySelectorAll('.badge.bg-danger');
            if (extensions.length > 0) {
                issues.push('部分重要PHP扩展未加载');
            }
            
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2"><path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/></svg>系统健康检查';
            
            if (issues.length === 0) {
                alert('✅ 系统健康状况良好！');
            } else {
                alert('⚠️ 发现以下问题：\\n' + issues.join('\\n'));
            }
        }, 2000);
    };
    
    window.viewErrorLog = function() {
        const logContent = `[<?= date('Y-m-d H:i:s') ?>] PHP Notice: 这是一个示例日志条目
[<?= date('Y-m-d H:i:s', time()-3600) ?>] PHP Warning: 另一个示例警告
[<?= date('Y-m-d H:i:s', time()-7200) ?>] PHP Error: 示例错误信息`;
        
        const newWindow = window.open('', '_blank', 'width=800,height=600');
        newWindow.document.write(`
            <html>
                <head><title>PHP错误日志</title></head>
                <body style="font-family: monospace; padding: 20px; background: #f8f9fa;">
                    <h2>PHP错误日志 (示例)</h2>
                    <pre style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">${logContent}</pre>
                    <p><em>注意：这是模拟数据，实际日志位置可能在 php.ini 的 log_errors 配置中指定的位置。</em></p>
                </body>
            </html>
        `);
    };
    
    window.testPerformance = function() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>测试中...';
        
        // 简单的性能测试
        const startTime = performance.now();
        let result = 0;
        
        // 执行一些计算密集型操作
        for (let i = 0; i < 1000000; i++) {
            result += Math.sqrt(i);
        }
        
        const endTime = performance.now();
        const executionTime = (endTime - startTime).toFixed(2);
        
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2"><path d="M6.5 0C3.48 0 1 2.48 1 5.5c0 2.52 1.7 4.65 4 5.28v1.97c-.6.34-1 .98-1 1.75 0 1.1.9 2 2 2s2-.9 2-2c0-.77-.4-1.41-1-1.75v-1.97c2.3-.63 4-2.76 4-5.28C12 2.48 9.52 0 6.5 0zm0 1C8.97 1 11 3.03 11 5.5S8.97 10 6.5 10 2 7.97 2 5.5 4.03 1 6.5 1z"/><path d="M6.5 2C4.57 2 3 3.57 3 5.5S4.57 9 6.5 9 10 7.43 10 5.5 8.43 2 6.5 2zm0 1C7.88 3 9 4.12 9 5.5S7.88 8 6.5 8 4 6.88 4 5.5 5.12 3 6.5 3z"/></svg>性能测试';
            
            alert(`📊 性能测试结果：\\n执行时间: ${executionTime}ms\\n计算结果: ${result.toFixed(2)}`);
        }, 1500);
    };
    
    function updateMemoryUsage() {
        // 这里可以通过AJAX获取最新的内存使用情况
        // 暂时省略实现
    }
}

<?php 
// 处理phpinfo导出请求
if (isset($_POST['export_phpinfo'])) {
    ob_start();
    phpinfo();
    $phpinfo_content = ob_get_contents();
    ob_end_clean();
    
    // 输出phpinfo到新窗口
    echo '<script>';
    echo 'const phpInfoWindow = window.open("", "_blank", "width=1200,height=800");';
    echo 'phpInfoWindow.document.write(' . json_encode($phpinfo_content) . ');';
    echo 'phpInfoWindow.document.close();';
    echo '</script>';
    exit;
}
?>
</script>

</div>
<!-- admin-page-content 结束 -->

<?php require_once '../views/layouts/footer.php'; ?>