<?php
/**
 * 系统信息页面
 * 显示服务器环境、PHP配置、系统状态等详细信息
 */

$current_page = 'system-info';
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

<div class="page-header">
    <h1>系统信息</h1>
    <p>查看服务器环境、PHP配置和系统运行状态</p>
</div>

<!-- PHP 信息 -->
<div class="content-card">
    <h3>🐘 PHP 环境信息</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info['php'] as $key => $value): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
                <strong style="color: var(--primary-color);"><?= str_replace('_', ' ', ucwords($key)) ?>:</strong><br>
                <span style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($value) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 服务器信息 -->
<div class="content-card">
    <h3>🖥️ 服务器信息</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info['server'] as $key => $value): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
                <strong style="color: var(--primary-color);"><?= str_replace('_', ' ', ucwords($key)) ?>:</strong><br>
                <span style="font-family: monospace; font-size: 13px; word-break: break-all;"><?= htmlspecialchars($value) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 内存使用情况 -->
<div class="content-card">
    <h3>💾 内存使用情况</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info['memory'] as $key => $value): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px; text-align: center;">
                <strong style="color: var(--primary-color);"><?= str_replace('_', ' ', ucwords($key)) ?></strong><br>
                <span style="font-size: 18px; font-weight: 600;"><?= htmlspecialchars($value) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 磁盘空间 -->
<div class="content-card">
    <h3>💿 磁盘空间</h3>
    
    <div style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <span><strong>磁盘使用情况</strong></span>
            <span><strong><?= $disk_info['usage_percent'] ?>% 已使用</strong></span>
        </div>
        
        <div style="background-color: var(--bg-primary); border-radius: 8px; height: 20px; overflow: hidden;">
            <div style="background-color: var(--primary-color); height: 100%; width: <?= $disk_info['usage_percent'] ?>%; transition: width 0.3s ease;"></div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px;">
            <div style="text-align: center; padding: 10px;">
                <strong>总容量</strong><br>
                <span style="font-size: 18px; color: var(--text-secondary);"><?= $disk_info['total'] ?></span>
            </div>
            <div style="text-align: center; padding: 10px;">
                <strong>已使用</strong><br>
                <span style="font-size: 18px; color: var(--warning-color);"><?= $disk_info['used'] ?></span>
            </div>
            <div style="text-align: center; padding: 10px;">
                <strong>可用空间</strong><br>
                <span style="font-size: 18px; color: var(--success-color);"><?= $disk_info['free'] ?></span>
            </div>
        </div>
    </div>
</div>

<!-- PHP 扩展 -->
<div class="content-card">
    <h3>🧩 PHP 扩展</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info['extensions'] as $extension => $loaded): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px; text-align: center;">
                <strong><?= htmlspecialchars($extension) ?></strong><br>
                <span style="font-size: 18px; color: <?= $loaded ? 'var(--success-color)' : 'var(--error-color)' ?>;">
                    <?= $loaded ? '✅ 已加载' : '❌ 未加载' ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PHP 限制 -->
<div class="content-card">
    <h3>⚙️ PHP 配置限制</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info['limits'] as $key => $value): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
                <strong style="color: var(--primary-color);"><?= str_replace('_', ' ', ucwords($key)) ?>:</strong><br>
                <span style="font-family: monospace; font-size: 14px; font-weight: 600;">
                    <?= htmlspecialchars($value === '' ? '无限制' : $value) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 环境变量 -->
<div class="content-card">
    <h3>🌍 重要环境变量</h3>
    
    <?php 
    $important_vars = [
        'PATH', 'HOME', 'USER', 'TEMP', 'TMP', 'PHPRC'
    ];
    ?>
    
    <div style="margin-top: 20px; max-height: 400px; overflow-y: auto; border: 1px solid var(--border-light); border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: var(--bg-primary); position: sticky; top: 0;">
                <tr>
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--border-light);">变量名</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid var(--border-light);">值</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($important_vars as $var): ?>
                    <?php if (getenv($var)): ?>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid var(--border-light); font-family: monospace; font-weight: 600;">
                                <?= htmlspecialchars($var) ?>
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid var(--border-light); font-family: monospace; font-size: 12px; word-break: break-all;">
                                <?= htmlspecialchars(getenv($var)) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 系统工具 -->
<div class="content-card">
    <h3>🔧 系统工具</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <button class="btn btn-outline" onclick="downloadPhpInfo()">
            <i data-feather="download"></i>
            导出 PHP 信息
        </button>
        
        <button class="btn btn-outline" onclick="checkSystemHealth()">
            <i data-feather="activity"></i>
            系统健康检查
        </button>
        
        <button class="btn btn-outline" onclick="viewErrorLog()">
            <i data-feather="file-text"></i>
            查看错误日志
        </button>
        
        <button class="btn btn-outline" onclick="testPerformance()">
            <i data-feather="zap"></i>
            性能测试
        </button>
    </div>
</div>

<script>
// 页面特定的JavaScript
function pageInit() {
    // 实时更新内存使用情况
    setInterval(updateMemoryUsage, 5000);
    
    window.downloadPhpInfo = function() {
        window.open('system-info.php?export=phpinfo', '_blank');
    };
    
    window.checkSystemHealth = function() {
        alert('系统健康检查功能（待开发）');
    };
    
    window.viewErrorLog = function() {
        alert('错误日志查看功能（待开发）');
    };
    
    window.testPerformance = function() {
        alert('性能测试功能（待开发）');
    };
    
    function updateMemoryUsage() {
        // 这里可以通过AJAX获取最新的内存使用情况
        // 暂时省略实现
    }
}

// 如果请求导出phpinfo
<?php if (isset($_GET['export']) && $_GET['export'] === 'phpinfo'): ?>
    // 导出phpinfo
    window.open('data:text/html;charset=utf-8,' + encodeURIComponent(`
        <!DOCTYPE html>
        <html><head><title>PHP Information</title></head>
        <body>
        <?php phpinfo(); ?>
        </body></html>
    `));
<?php endif; ?>
</script>

<?php require_once '../views/layouts/footer.php'; ?>