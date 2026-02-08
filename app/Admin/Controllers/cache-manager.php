<?php
/**
 * 缓存管理器
 * 管理网站的各种缓存，包括缩略图、页面缓存等
 */

// 设置页面信息
$page_title = '🛠️ 缓存管理';
$page_subtitle = '管理和优化网站缓存，提升加载性能';
$_GET['page'] = 'cache-manager';

require_once __DIR__ . '/../Views/layouts/admin-header.php';

// 处理缓存操作
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'clear_thumbnail_cache':
            try {
                $cleared_count = 0;
                $cache_dirs = [
                    '../assets/images/single-works/thumbs',
                    '../assets/images/galleries/thumbs'
                ];

                foreach ($cache_dirs as $dir) {
                    if (is_dir($dir)) {
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($dir),
                            RecursiveIteratorIterator::CHILD_FIRST
                        );

                        foreach ($iterator as $file) {
                            if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                unlink($file->getPathname());
                                $cleared_count++;
                            }
                        }
                    }
                }

                $message = "已清理 $cleared_count 个缩略图缓存文件";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "清理缩略图缓存失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;

        case 'clear_temp_files':
            try {
                $temp_dirs = [
                    sys_get_temp_dir(),
                    '../temp'
                ];

                $cleared_count = 0;
                foreach ($temp_dirs as $dir) {
                    if (is_dir($dir)) {
                        $files = glob($dir . '/rzx_*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                                $cleared_count++;
                            }
                        }
                    }
                }

                $message = "已清理 $cleared_count 个临时文件";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "清理临时文件失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;

        case 'optimize_images':
            $message = "图片优化功能（待开发 - 需要ImageMagick或GD扩展支持）";
            $message_type = 'info';
            break;
    }
}

// 获取缓存统计信息
function getCacheStats() {
    $stats = [
        'thumbnail_cache' => ['size' => 0, 'files' => 0],
        'temp_files' => ['size' => 0, 'files' => 0],
        'log_files' => ['size' => 0, 'files' => 0]
    ];

    // 缩略图缓存统计
    $thumb_dirs = ['../assets/images/single-works/thumbs', '../assets/images/galleries/thumbs'];
    foreach ($thumb_dirs as $dir) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $stats['thumbnail_cache']['size'] += $file->getSize();
                    $stats['thumbnail_cache']['files']++;
                }
            }
        }
    }

    // 临时文件统计
    $temp_files = glob(sys_get_temp_dir() . '/rzx_*');
    if ($temp_files) {
        foreach ($temp_files as $file) {
            if (is_file($file)) {
                $stats['temp_files']['size'] += filesize($file);
                $stats['temp_files']['files']++;
            }
        }
    }

    return $stats;
}

$cache_stats = getCacheStats();

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>

<div class="page-header">
    <p>临时占位页头</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 缓存统计概览 -->
<div class="content-card">
    <h3>📊 缓存统计概览</h3>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="image"></i>
            </div>
            <div class="stat-info">
                <h3><?= formatBytes($cache_stats['thumbnail_cache']['size']) ?></h3>
                <p>缩略图缓存</p>
                <small><?= $cache_stats['thumbnail_cache']['files'] ?> 个文件</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="file"></i>
            </div>
            <div class="stat-info">
                <h3><?= formatBytes($cache_stats['temp_files']['size']) ?></h3>
                <p>临时文件</p>
                <small><?= $cache_stats['temp_files']['files'] ?> 个文件</small>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="activity"></i>
            </div>
            <div class="stat-info">
                <h3><?= formatBytes(memory_get_peak_usage(true)) ?></h3>
                <p>内存使用峰值</p>
                <small>当前进程</small>
            </div>
        </div>
    </div>
</div>

<!-- 缓存清理操作 -->
<div class="content-card">
    <h3>🧹 缓存清理操作</h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <!-- 缩略图缓存清理 -->
        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 20px;">
            <h4 style="color: var(--primary-color); margin: 0 0 10px 0;">🖼️ 缩略图缓存</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">
                清理所有自动生成的缩略图文件。清理后会在下次访问时重新生成。
            </p>
            <p><strong>当前大小:</strong> <?= formatBytes($cache_stats['thumbnail_cache']['size']) ?></p>
            <p><strong>文件数量:</strong> <?= $cache_stats['thumbnail_cache']['files'] ?> 个</p>

            <form method="POST" style="margin-top: 15px;" onsubmit="return confirm('确定要清理缩略图缓存吗？清理后需要重新生成缩略图。')">
                <input type="hidden" name="action" value="clear_thumbnail_cache">
                <button type="submit" class="btn btn-outline" style="width: 100%;">
                    <i data-feather="trash-2"></i>
                    清理缩略图缓存
                </button>
            </form>
        </div>

        <!-- 临时文件清理 -->
        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 20px;">
            <h4 style="color: var(--primary-color); margin: 0 0 10px 0;">📄 临时文件</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">
                清理系统产生的临时文件和日志文件。
            </p>
            <p><strong>当前大小:</strong> <?= formatBytes($cache_stats['temp_files']['size']) ?></p>
            <p><strong>文件数量:</strong> <?= $cache_stats['temp_files']['files'] ?> 个</p>

            <form method="POST" style="margin-top: 15px;" onsubmit="return confirm('确定要清理临时文件吗？')">
                <input type="hidden" name="action" value="clear_temp_files">
                <button type="submit" class="btn btn-outline" style="width: 100%;">
                    <i data-feather="trash-2"></i>
                    清理临时文件
                </button>
            </form>
        </div>

        <!-- 图片优化 -->
        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 20px;">
            <h4 style="color: var(--primary-color); margin: 0 0 10px 0;">⚡ 图片优化</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">
                压缩和优化图片文件，减少存储空间和加载时间。
            </p>
            <p><strong>状态:</strong> <span style="color: var(--warning-color);">需要扩展支持</span></p>
            <p><strong>支持格式:</strong> JPG, PNG, WebP</p>

            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="optimize_images">
                <button type="submit" class="btn btn-outline" style="width: 100%;" disabled>
                    <i data-feather="zap"></i>
                    优化图片（开发中）
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 缓存设置 -->
<div class="content-card">
    <h3>⚙️ 缓存设置</h3>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <div>
            <h4>缩略图设置</h4>
            <div class="form-group">
                <label for="thumb_quality">缩略图质量 (1-100)</label>
                <input type="range" id="thumb_quality" min="1" max="100" value="85" class="form-control" oninput="updateQualityValue(this.value)">
                <small>当前值: <span id="quality_value">85</span></small>
            </div>

            <div class="form-group">
                <label for="thumb_size">缩略图尺寸</label>
                <select id="thumb_size" class="form-control">
                    <option value="150">150px (小)</option>
                    <option value="200" selected>200px (中)</option>
                    <option value="300">300px (大)</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="auto_webp" checked>
                    自动生成WebP格式缩略图
                </label>
            </div>
        </div>

        <div>
            <h4>清理设置</h4>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="auto_cleanup">
                    启用自动清理 (每周)
                </label>
            </div>

            <div class="form-group">
                <label for="max_cache_size">最大缓存大小 (MB)</label>
                <input type="number" id="max_cache_size" class="form-control" value="500" min="100" max="5000">
            </div>

            <div class="form-group">
                <label for="cleanup_days">文件保留天数</label>
                <input type="number" id="cleanup_days" class="form-control" value="30" min="1" max="365">
            </div>
        </div>
    </div>

    <div class="btn-group" style="margin-top: 20px;">
        <button class="btn btn-primary" onclick="saveCacheSettings()">
            <i data-feather="save"></i>
            保存设置
        </button>
        <button class="btn btn-outline" onclick="resetCacheSettings()">
            <i data-feather="rotate-ccw"></i>
            恢复默认
        </button>
    </div>
</div>

<!-- 系统信息 -->
<div class="content-card">
    <h3>🔧 系统缓存信息</h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
            <strong>PHP OPcache:</strong><br>
            <span style="color: <?= extension_loaded('opcache') ? 'var(--success-color)' : 'var(--error-color)' ?>;">
                <?= extension_loaded('opcache') ? '已启用' : '未启用' ?>
            </span>
        </div>

        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
            <strong>内存限制:</strong><br>
            <span><?= ini_get('memory_limit') ?></span>
        </div>

        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
            <strong>上传限制:</strong><br>
            <span><?= ini_get('upload_max_filesize') ?></span>
        </div>

        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
            <strong>磁盘可用空间:</strong><br>
            <span><?= formatBytes(disk_free_space('.')) ?></span>
        </div>
    </div>
</div>

<script>
// 页面特定的JavaScript
function pageInit() {
    window.updateQualityValue = function(value) {
        document.getElementById('quality_value').textContent = value;
    };

    window.saveCacheSettings = function() {
        // 收集设置
        const settings = {
            thumb_quality: document.getElementById('thumb_quality').value,
            thumb_size: document.getElementById('thumb_size').value,
            auto_webp: document.getElementById('auto_webp').checked,
            auto_cleanup: document.getElementById('auto_cleanup').checked,
            max_cache_size: document.getElementById('max_cache_size').value,
            cleanup_days: document.getElementById('cleanup_days').value
        };

        console.log('保存缓存设置:', settings);
        alert('缓存设置已保存（演示功能）');
    };

    window.resetCacheSettings = function() {
        if (confirm('确定要恢复默认设置吗？')) {
            document.getElementById('thumb_quality').value = 85;
            document.getElementById('thumb_size').value = 200;
            document.getElementById('auto_webp').checked = true;
            document.getElementById('auto_cleanup').checked = false;
            document.getElementById('max_cache_size').value = 500;
            document.getElementById('cleanup_days').value = 30;
            updateQualityValue(85);
            alert('已恢复默认设置');
        }
    };
}
</script>

<?php require_once __DIR__ . '/../Views/layouts/admin-footer.php'; ?>
