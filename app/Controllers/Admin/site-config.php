<?php
/**
 * 网站配置管理
 * 管理网站的基本设置和配置选项
 */

// 设置页面信息
$page_title = '🛠️ 网站配置';
$page_subtitle = '管理网站的基本设置和系统配置';
$_GET['page'] = 'site-config';

require_once __DIR__ . '/../../Views/Admin/layouts/header.php';

// 处理配置更新
$message = '';
$message_type = '';

// 配置文件路径
$config_files = [
    'page_config' => '../../../app/Config/page_config.php',
    'single_works_sort' => '../../../app/Config/single_works_sort.php'
];

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_site_info':
            try {
                // 这里可以更新网站基本信息
                // 由于当前使用的是硬编码配置，这里只做演示
                $message = "网站信息更新成功（演示功能）";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "更新失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'reset_config':
            $config_type = $_POST['config_type'] ?? '';
            if ($config_type && isset($config_files[$config_type])) {
                try {
                    // 备份现有配置
                    $config_path = $config_files[$config_type];
                    $backup_path = $config_path . '.backup.' . date('Y-m-d-H-i-s');
                    
                    if (file_exists($config_path)) {
                        copy($config_path, $backup_path);
                        $message = "配置已重置，备份文件: " . basename($backup_path);
                        $message_type = 'success';
                    } else {
                        $message = "配置文件不存在";
                        $message_type = 'error';
                    }
                } catch (Exception $e) {
                    $message = "重置失败: " . $e->getMessage();
                    $message_type = 'error';
                }
            }
            break;
    }
}

// 读取当前配置
$current_configs = [];
foreach ($config_files as $name => $path) {
    if (file_exists($path)) {
        $current_configs[$name] = [
            'path' => $path,
            'size' => filesize($path),
            'modified' => filemtime($path),
            'readable' => is_readable($path),
            'writable' => is_writable($path)
        ];
    }
}

// 获取系统信息
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'max_upload_size' => ini_get('upload_max_filesize'),
    'max_post_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'timezone' => date_default_timezone_get()
];
?>

<div class="page-header">
    <h1>网站配置</h1>
    <p>管理网站的基本设置和系统配置</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 网站基本信息 -->
<div class="content-card">
    <h3>🌐 网站基本信息</h3>
    
    <form method="POST" style="margin-top: 20px;">
        <input type="hidden" name="action" value="update_site_info">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="site_title">网站标题</label>
                <input type="text" id="site_title" name="site_title" class="form-control" value="RZX.ME - 个人作品展示">
            </div>
            
            <div class="form-group">
                <label for="site_subtitle">网站副标题</label>
                <input type="text" id="site_subtitle" name="site_subtitle" class="form-control" value="创意作品 | 设计展示">
            </div>
            
            <div class="form-group">
                <label for="site_description">网站描述</label>
                <textarea id="site_description" name="site_description" class="form-control" rows="3">RZX的个人作品展示网站，包含动画、漫画、设计作品等创意内容</textarea>
            </div>
            
            <div class="form-group">
                <label for="site_keywords">关键词</label>
                <textarea id="site_keywords" name="site_keywords" class="form-control" rows="3">个人作品, 动画, 漫画, 设计, 创意, 艺术</textarea>
            </div>
        </div>
        
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <i data-feather="save"></i>
                保存设置
            </button>
        </div>
    </form>
</div>

<!-- 配置文件管理 -->
<div class="content-card">
    <h3>⚙️ 配置文件管理</h3>
    
    <div style="margin-top: 20px;">
        <?php foreach ($current_configs as $name => $config): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 20px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: var(--primary-color);">
                    <?= $name === 'page_config' ? '页面配置' : 'Single-Works 排序配置' ?>
                </h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div>
                        <strong>文件大小:</strong><br>
                        <span><?= round($config['size'] / 1024, 2) ?> KB</span>
                    </div>
                    <div>
                        <strong>最后修改:</strong><br>
                        <span><?= date('Y-m-d H:i:s', $config['modified']) ?></span>
                    </div>
                    <div>
                        <strong>权限:</strong><br>
                        <span style="color: <?= $config['readable'] ? 'var(--success-color)' : 'var(--error-color)' ?>">
                            读取: <?= $config['readable'] ? '✅' : '❌' ?>
                        </span><br>
                        <span style="color: <?= $config['writable'] ? 'var(--success-color)' : 'var(--error-color)' ?>">
                            写入: <?= $config['writable'] ? '✅' : '❌' ?>
                        </span>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-outline" onclick="viewConfig('<?= htmlspecialchars($name) ?>')">
                        <i data-feather="eye"></i>
                        查看配置
                    </button>
                    
                    <button class="btn btn-outline" onclick="editConfig('<?= htmlspecialchars($name) ?>')">
                        <i data-feather="edit"></i>
                        编辑配置
                    </button>
                    
                    <form method="POST" style="display: inline;" onsubmit="return confirm('确定要重置此配置吗？')">
                        <input type="hidden" name="action" value="reset_config">
                        <input type="hidden" name="config_type" value="<?= htmlspecialchars($name) ?>">
                        <button type="submit" class="btn btn-outline" style="color: var(--warning-color); border-color: var(--warning-color);">
                            <i data-feather="rotate-ccw"></i>
                            重置配置
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 系统信息 -->
<div class="content-card">
    <h3>🔧 系统环境信息</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
        <?php foreach ($system_info as $key => $value): ?>
            <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
                <strong style="color: var(--primary-color);">
                    <?= str_replace('_', ' ', ucwords($key)) ?>:
                </strong><br>
                <span style="font-family: monospace; font-size: 13px;">
                    <?= htmlspecialchars($value) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 快速操作 -->
<div class="content-card">
    <h3>⚡ 快速操作</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <button class="btn btn-outline" onclick="clearCache()">
            <i data-feather="trash-2"></i>
            清理系统缓存
        </button>
        
        <button class="btn btn-outline" onclick="optimizeDatabase()">
            <i data-feather="database"></i>
            优化数据库
        </button>
        
        <button class="btn btn-outline" onclick="backupConfig()">
            <i data-feather="download"></i>
            备份配置文件
        </button>
        
        <button class="btn btn-outline" onclick="checkUpdates()">
            <i data-feather="refresh-cw"></i>
            检查系统更新
        </button>
    </div>
</div>

<script>
// 页面特定的JavaScript
function pageInit() {
    window.viewConfig = function(configName) {
        alert('查看配置功能：' + configName + '（待开发）');
    };
    
    window.editConfig = function(configName) {
        alert('编辑配置功能：' + configName + '（待开发）');
    };
    
    window.clearCache = function() {
        if (confirm('确定要清理系统缓存吗？')) {
            alert('清理缓存功能（待开发）');
        }
    };
    
    window.optimizeDatabase = function() {
        if (confirm('确定要优化数据库吗？')) {
            alert('优化数据库功能（待开发）');
        }
    };
    
    window.backupConfig = function() {
        alert('备份配置功能（待开发）');
    };
    
    window.checkUpdates = function() {
        alert('检查更新功能（待开发）');
    };
}
</script>

<?php require_once __DIR__ . '/../../Views/Admin/layouts/footer.php'; ?>