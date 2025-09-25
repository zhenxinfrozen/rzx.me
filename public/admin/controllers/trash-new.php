<?php
/**
 * 回收站管理
 * 管理已删除的分类，提供恢复和永久删除功能
 */

$current_page = 'trash';
require_once '../views/layouts/header.php';

// 处理操作
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $trashDir = '../assets/images/trash';
    $singleWorksDir = '../assets/images/single-works';
    
    switch ($action) {
        case 'restore':
            try {
                $trashItemDir = $trashDir . '/' . $_POST['trash_dir'];
                $category = $_POST['category'];
                $targetDir = $singleWorksDir . '/' . $category;
                
                if (is_dir($trashItemDir)) {
                    if (is_dir($targetDir)) {
                        $message = "分类 '$category' 已存在，无法恢复";
                        $message_type = 'error';
                    } else {
                        if (rename($trashItemDir, $targetDir)) {
                            $message = "分类 '$category' 恢复成功";
                            $message_type = 'success';
                        } else {
                            $message = "恢复失败，请检查权限";
                            $message_type = 'error';
                        }
                    }
                } else {
                    $message = "回收站项目不存在";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "恢复失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'permanent_delete':
            try {
                $trashItemDir = $trashDir . '/' . $_POST['trash_dir'];
                if (is_dir($trashItemDir)) {
                    // 递归删除目录
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($trashItemDir),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isDir()) {
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    rmdir($trashItemDir);
                    
                    $message = "项目已永久删除";
                    $message_type = 'success';
                } else {
                    $message = "回收站项目不存在";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "删除失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'empty_trash':
            try {
                if (is_dir($trashDir)) {
                    $deleted_count = 0;
                    $dirs = scandir($trashDir);
                    
                    foreach ($dirs as $dir) {
                        if ($dir === '.' || $dir === '..') continue;
                        $dirPath = $trashDir . '/' . $dir;
                        
                        if (is_dir($dirPath)) {
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($dirPath),
                                RecursiveIteratorIterator::CHILD_FIRST
                            );
                            
                            foreach ($iterator as $file) {
                                if ($file->isDir()) {
                                    rmdir($file->getRealPath());
                                } else {
                                    unlink($file->getRealPath());
                                }
                            }
                            rmdir($dirPath);
                            $deleted_count++;
                        }
                    }
                    
                    $message = "已清空回收站，删除了 $deleted_count 个项目";
                    $message_type = 'success';
                } else {
                    $message = "回收站目录不存在";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "清空失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
    }
}

// 获取回收站项目
$trashDir = '../assets/images/trash';
$trashItems = [];

if (is_dir($trashDir)) {
    $dirs = scandir($trashDir);
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        if (is_dir($trashDir . '/' . $dir)) {
            // 解析文件名获取时间戳和原始分类名
            $parts = explode('_', $dir, 4);
            if (count($parts) >= 4) {
                $date = $parts[0];
                $time = $parts[1] . ':' . $parts[2] . ':' . $parts[3];
                $category = implode('_', array_slice($parts, 4));
            } else {
                $date = 'Unknown';
                $time = 'Unknown';
                $category = $dir;
            }
            
            $itemPath = $trashDir . '/' . $dir;
            $fileCount = count(glob($itemPath . '/*'));
            
            $trashItems[] = [
                'dir' => $dir,
                'category' => $category,
                'deleted_date' => $date,
                'deleted_time' => str_replace('-', ':', $time),
                'file_count' => $fileCount,
                'size' => formatBytes(getDirSize($itemPath))
            ];
        }
    }
}

// 按删除时间排序（最新的在前）
usort($trashItems, function($a, $b) {
    return strcmp($b['dir'], $a['dir']);
});

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getDirSize($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : getDirSize($each);
    }
    return $size;
}
?>

<div class="page-header">
    <h1>回收站</h1>
    <p>管理已删除的分类，可以恢复或永久删除</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 回收站统计 -->
<div class="content-card">
    <h3>📊 回收站统计</h3>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="trash-2"></i>
            </div>
            <div class="stat-info">
                <h3><?= count($trashItems) ?></h3>
                <p>回收站项目</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="file"></i>
            </div>
            <div class="stat-info">
                <h3><?= array_sum(array_column($trashItems, 'file_count')) ?></h3>
                <p>文件总数</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i data-feather="hard-drive"></i>
            </div>
            <div class="stat-info">
                <h3><?= formatBytes(array_sum(array_map(function($item) use ($trashDir) {
                    return getDirSize($trashDir . '/' . $item['dir']);
                }, $trashItems))) ?></h3>
                <p>占用空间</p>
            </div>
        </div>
    </div>
</div>

<!-- 回收站项目列表 -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>🗑️ 回收站项目</h3>
        <?php if (!empty($trashItems)): ?>
            <form method="POST" style="display: inline;" onsubmit="return confirm('确定要清空整个回收站吗？此操作不可恢复！')">
                <input type="hidden" name="action" value="empty_trash">
                <button type="submit" class="btn btn-outline" style="color: var(--error-color); border-color: var(--error-color);">
                    <i data-feather="trash-2"></i>
                    清空回收站
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (empty($trashItems)): ?>
        <div class="alert alert-info">
            <i data-feather="info"></i>
            回收站是空的。删除的分类会出现在这里，您可以选择恢复或永久删除。
        </div>
    <?php else: ?>
        <div style="margin-top: 20px;">
            <?php foreach ($trashItems as $item): ?>
                <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 20px; margin-bottom: 15px; background: var(--bg-secondary);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <h4 style="color: var(--primary-color); margin: 0 0 10px 0;">
                                📁 <?= htmlspecialchars($item['category']) ?>
                            </h4>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 15px;">
                                <div>
                                    <small style="color: var(--text-secondary);">删除时间</small><br>
                                    <strong><?= htmlspecialchars($item['deleted_date']) ?></strong><br>
                                    <span style="font-size: 13px;"><?= htmlspecialchars($item['deleted_time']) ?></span>
                                </div>
                                <div>
                                    <small style="color: var(--text-secondary);">文件数量</small><br>
                                    <strong><?= $item['file_count'] ?> 个文件</strong>
                                </div>
                                <div>
                                    <small style="color: var(--text-secondary);">占用空间</small><br>
                                    <strong><?= $item['size'] ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-left: 20px;">
                            <!-- 恢复按钮 -->
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('确定要恢复分类 \'<?= htmlspecialchars($item['category']) ?>\' 吗？')">
                                <input type="hidden" name="action" value="restore">
                                <input type="hidden" name="trash_dir" value="<?= htmlspecialchars($item['dir']) ?>">
                                <input type="hidden" name="category" value="<?= htmlspecialchars($item['category']) ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="rotate-ccw"></i>
                                    恢复
                                </button>
                            </form>
                            
                            <!-- 永久删除按钮 -->
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('确定要永久删除分类 \'<?= htmlspecialchars($item['category']) ?>\' 吗？\n\n此操作不可恢复！')">
                                <input type="hidden" name="action" value="permanent_delete">
                                <input type="hidden" name="trash_dir" value="<?= htmlspecialchars($item['dir']) ?>">
                                <button type="submit" class="btn btn-outline" style="color: var(--error-color); border-color: var(--error-color);">
                                    <i data-feather="x"></i>
                                    永久删除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 回收站管理 -->
<div class="content-card">
    <h3>⚙️ 回收站设置</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <div>
            <h4>自动清理设置</h4>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="auto_cleanup" onchange="toggleAutoCleanup()">
                    启用自动清理（30天后自动删除）
                </label>
            </div>
            
            <div class="form-group">
                <label for="cleanup_days">保留天数</label>
                <input type="number" id="cleanup_days" class="form-control" value="30" min="1" max="365" disabled>
            </div>
        </div>
        
        <div>
            <h4>存储限制</h4>
            <div class="form-group">
                <label for="max_size">最大回收站大小 (MB)</label>
                <input type="number" id="max_size" class="form-control" value="1000" min="100" max="10000">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="compress_items">
                    压缩存储（节省空间）
                </label>
            </div>
        </div>
    </div>
    
    <div class="btn-group" style="margin-top: 20px;">
        <button class="btn btn-primary" onclick="saveTrashSettings()">
            <i data-feather="save"></i>
            保存设置
        </button>
        
        <button class="btn btn-outline" onclick="resetTrashSettings()">
            <i data-feather="rotate-ccw"></i>
            恢复默认
        </button>
    </div>
</div>

<script>
// 页面特定的JavaScript
function pageInit() {
    window.toggleAutoCleanup = function() {
        const enabled = document.getElementById('auto_cleanup').checked;
        document.getElementById('cleanup_days').disabled = !enabled;
    };
    
    window.saveTrashSettings = function() {
        const settings = {
            auto_cleanup: document.getElementById('auto_cleanup').checked,
            cleanup_days: document.getElementById('cleanup_days').value,
            max_size: document.getElementById('max_size').value,
            compress_items: document.getElementById('compress_items').checked
        };
        
        console.log('保存回收站设置:', settings);
        alert('回收站设置已保存（演示功能）');
    };
    
    window.resetTrashSettings = function() {
        if (confirm('确定要恢复默认设置吗？')) {
            document.getElementById('auto_cleanup').checked = false;
            document.getElementById('cleanup_days').value = 30;
            document.getElementById('cleanup_days').disabled = true;
            document.getElementById('max_size').value = 1000;
            document.getElementById('compress_items').checked = false;
            alert('已恢复默认设置');
        }
    };
}
</script>

<?php require_once '../views/layouts/footer.php'; ?>