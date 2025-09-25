<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分组回收站管理</title>
    <style>
        body { font-family: 'Microsoft YaHei', sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .trash-item { background: #f8f9fa; padding: 20px; margin-bottom: 15px; border-radius: 6px; border-left: 4px solid #dc3545; }
        .trash-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .trash-name { font-weight: bold; color: #dc3545; font-size: 16px; }
        .trash-date { color: #666; font-size: 12px; }
        .trash-path { background: #e9ecef; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 12px; color: #495057; margin: 10px 0; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 8px; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .empty-trash { text-align: center; padding: 40px; color: #666; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="admin-sort-config.php">← 返回分组配置</a>
        </div>
        
        <h1>🗑️ 分组回收站</h1>
        
        <?php
        $trashDir = '../../public/assets/images/trash';
        $trashItems = [];
        
        if (is_dir($trashDir)) {
            $dirs = scandir($trashDir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                if (is_dir($trashDir . '/' . $dir)) {
                    // 解析目录名 (category-2023-12-25-14-30-45)
                    $parts = explode('-', $dir);
                    if (count($parts) >= 4) {
                        $category = $parts[0];
                        $deleteTime = implode('-', array_slice($parts, 1, 3)) . ' ' . implode(':', array_slice($parts, 4));
                        
                        $trashItems[] = [
                            'dir' => $dir,
                            'category' => $category,
                            'delete_time' => $deleteTime,
                            'path' => $trashDir . '/' . $dir,
                            'size' => count(glob($trashDir . '/' . $dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE))
                        ];
                    }
                }
            }
            
            // 按删除时间排序（最新的在前）
            usort($trashItems, function($a, $b) {
                return strtotime($b['delete_time']) - strtotime($a['delete_time']);
            });
        }
        
        if (empty($trashItems)): ?>
            <div class="empty-trash">
                <h3>🎉 回收站为空</h3>
                <p>没有被删除的分组</p>
            </div>
        <?php else: ?>
            <p>以下是已删除的分组，可以选择恢复或永久删除：</p>
            
            <?php foreach ($trashItems as $item): ?>
                <div class="trash-item">
                    <div class="trash-header">
                        <span class="trash-name"><?= htmlspecialchars($item['category']) ?></span>
                        <span class="trash-date">删除于: <?= htmlspecialchars($item['delete_time']) ?></span>
                    </div>
                    <div class="trash-path">
                        路径: <?= htmlspecialchars($item['path']) ?>
                        <br>包含: <?= $item['size'] ?> 张图片
                    </div>
                    <div>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="trash_dir" value="<?= htmlspecialchars($item['dir']) ?>">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($item['category']) ?>">
                            <button type="submit" class="btn btn-success" onclick="return confirm('确定要恢复分组 \'<?= htmlspecialchars($item['category']) ?>\' 吗？')">
                                ↩️ 恢复
                            </button>
                        </form>
                        
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete_permanent">
                            <input type="hidden" name="trash_dir" value="<?= htmlspecialchars($item['dir']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('确定要永久删除分组 \'<?= htmlspecialchars($item['category']) ?>\' 吗？\n\n此操作不可恢复！')">
                                🗑️ 永久删除
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="empty_trash">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('确定要清空整个回收站吗？\n\n此操作将永久删除所有已删除的分组，不可恢复！')">
                        🗑️ 清空回收站
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    // 处理操作
    if ($_POST) {
        $action = $_POST['action'] ?? '';
        $trashDir = '../../public/assets/images/trash';
        $singleWorksDir = '../../public/assets/images/single-works';
        
        switch ($action) {
            case 'restore':
                $trashItemDir = $trashDir . '/' . $_POST['trash_dir'];
                $category = $_POST['category'];
                $targetDir = $singleWorksDir . '/' . $category;
                
                if (is_dir($trashItemDir)) {
                    if (is_dir($targetDir)) {
                        echo '<script>alert("❌ 恢复失败：分组 \'' . addslashes($category) . '\' 已存在！");</script>';
                    } else {
                        if (rename($trashItemDir, $targetDir)) {
                            echo '<script>alert("✅ 分组 \'' . addslashes($category) . '\' 已成功恢复！"); window.location.reload();</script>';
                        } else {
                            echo '<script>alert("❌ 恢复失败，请检查文件权限！");</script>';
                        }
                    }
                }
                break;
                
            case 'delete_permanent':
                $trashItemDir = $trashDir . '/' . $_POST['trash_dir'];
                if (is_dir($trashItemDir)) {
                    // 递归删除目录
                    function deleteDirectory($dir) {
                        if (!is_dir($dir)) return false;
                        $files = array_diff(scandir($dir), array('.', '..'));
                        foreach ($files as $file) {
                            $path = $dir . '/' . $file;
                            is_dir($path) ? deleteDirectory($path) : unlink($path);
                        }
                        return rmdir($dir);
                    }
                    
                    if (deleteDirectory($trashItemDir)) {
                        echo '<script>alert("✅ 已永久删除！"); window.location.reload();</script>';
                    } else {
                        echo '<script>alert("❌ 删除失败，请检查文件权限！");</script>';
                    }
                }
                break;
                
            case 'empty_trash':
                if (is_dir($trashDir)) {
                    $dirs = array_diff(scandir($trashDir), array('.', '..'));
                    $deleted = 0;
                    
                    foreach ($dirs as $dir) {
                        $path = $trashDir . '/' . $dir;
                        if (is_dir($path)) {
                            function deleteDirectory($dir) {
                                if (!is_dir($dir)) return false;
                                $files = array_diff(scandir($dir), array('.', '..'));
                                foreach ($files as $file) {
                                    $path = $dir . '/' . $file;
                                    is_dir($path) ? deleteDirectory($path) : unlink($path);
                                }
                                return rmdir($dir);
                            }
                            
                            if (deleteDirectory($path)) {
                                $deleted++;
                            }
                        }
                    }
                    
                    echo '<script>alert("✅ 已清空回收站，共删除 ' . $deleted . ' 个分组！"); window.location.reload();</script>';
                }
                break;
        }
    }
    ?>
</body>
</html>