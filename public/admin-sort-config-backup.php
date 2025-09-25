<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single-Works 排序配置</title>
    <style>
        body { font-family: 'Microsoft YaHei', sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        select, input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .category-item { background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 4px; border-left: 4px solid #007cba; }
        .category-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .category-name { font-weight: bold; color: #007cba; font-size: 16px; }
        .image-count { background: #007cba; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .category-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .category-inputs > div { display: flex; flex-direction: column; }
        .category-inputs label { font-size: 12px; color: #666; margin-bottom: 4px; font-weight: normal; }
        .btn { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn:hover { background: #005a87; }
        .btn-secondary { background: #6c757d !important; }
        .btn-secondary:hover { background: #545b62 !important; }
        .form-actions { display: flex; align-items: center; gap: 10px; }
        .preview { background: #f0f8ff; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .sortable { list-style: none; padding: 0; }
        .sortable li { background: white; margin: 5px 0; padding: 10px; border-radius: 4px; cursor: move; border-left: 4px solid #007cba; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Single-Works 分组排序配置</h1>
        
        <form method="post">
            <?php
            // 获取当前配置用于显示
            $configPath = '../app/Config/single_works_sort.php';
            $currentConfig = file_exists($configPath) ? require $configPath : ['sort_method' => 'custom_order'];
            $currentSortMethod = $currentConfig['sort_method'] ?? 'custom_order';
            ?>
            
            <div class="form-group">
                <label for="sort_method">排序方式</label>
                <select name="sort_method" id="sort_method">
                    <option value="custom_order" <?= $currentSortMethod === 'custom_order' ? 'selected' : '' ?>>自定义排序</option>
                    <option value="alphabetical" <?= $currentSortMethod === 'alphabetical' ? 'selected' : '' ?>>字母排序</option>
                    <option value="prefix_sort" <?= $currentSortMethod === 'prefix_sort' ? 'selected' : '' ?>>前缀排序</option>
                    <option value="date_modified" <?= $currentSortMethod === 'date_modified' ? 'selected' : '' ?>>修改时间排序</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>分组排序（拖拽调整顺序）</label>
                <ul class="sortable" id="categoryOrder">
                    <?php
                    // 修正路径引用
                    require_once '../app/Utils/GalleryManager.php';
                    $galleryManager = new GalleryManager();
                    
                    // 读取配置文件
                    $configPath = '../app/Config/single_works_sort.php';
                    $config = file_exists($configPath) ? require $configPath : [
                        'sort_method' => 'custom_order',
                        'custom_order' => [],
                        'display_names' => [],
                        'descriptions' => []
                    ];
                    
                    // 获取所有实际存在的分组
                    $categories = $galleryManager->getGalleryCategories('single-works');
                    
                    // 处理自定义顺序，确保所有分组都被显示
                    $customOrder = $config['custom_order'] ?? [];
                    $allCategories = array_unique(array_merge($customOrder, $categories));
                    
                    foreach ($allCategories as $category):
                        // 只显示实际存在的分组
                        if (!in_array($category, $categories)) continue;
                        
                        $displayName = $config['display_names'][$category] ?? $category;
                        $description = $config['descriptions'][$category] ?? '';
                        $imageCount = count(glob("assets/images/single-works/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
                    ?>
                    <li data-category="<?= htmlspecialchars($category) ?>">
                        <div class="category-item">
                            <div class="category-header">
                                <span class="category-name"><?= htmlspecialchars($category) ?></span>
                                <span class="image-count"><?= $imageCount ?> 张图片</span>
                            </div>
                            <div class="category-inputs">
                                <div>
                                    <label>显示名称:</label>
                                    <input type="text" 
                                           name="display_names[<?= htmlspecialchars($category) ?>]" 
                                           value="<?= htmlspecialchars($displayName) ?>"
                                           placeholder="例如: 动物世界">
                                </div>
                                <div>
                                    <label>描述信息:</label>
                                    <textarea name="descriptions[<?= htmlspecialchars($category) ?>]" 
                                             rows="2"
                                             placeholder="例如: 各种动物主题的插画作品"><?= htmlspecialchars($description) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <input type="hidden" name="category_order" id="categoryOrderInput" value="">
            
            <div class="form-actions">
                <button type="submit" class="btn">💾 保存配置</button>
                <button type="button" class="btn btn-secondary" onclick="resetToDefault()">🔄 重置为默认</button>
            </div>
        </form>
        
        <div class="preview">
            <h3>📋 当前效果预览</h3>
            <p>配置保存后，可以通过以下链接查看效果：</p>
            <p><a href="/single-works" target="_blank">🔗 查看 Single-Works 页面</a></p>
            <p><a href="admin-trash.php">🗑️ 管理回收站</a> - 恢复或永久删除已删除的分组</p>
            <p><strong>提示：</strong> 拖拽分组上方的 ⋮⋮ 图标来调整显示顺序</p>
        </div>
    </div>
    
    <script>
        // 简单的拖拽排序功能
        const sortable = document.getElementById('categoryOrder');
        let draggedElement = null;
        
        sortable.addEventListener('dragstart', function(e) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        });
        
        sortable.addEventListener('dragend', function(e) {
            e.target.style.opacity = '';
            draggedElement = null;
        });
        
        sortable.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        sortable.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement !== e.target && e.target.tagName === 'LI') {
                const allItems = Array.from(sortable.children);
                const draggedIndex = allItems.indexOf(draggedElement);
                const targetIndex = allItems.indexOf(e.target);
                
                if (draggedIndex > targetIndex) {
                    sortable.insertBefore(draggedElement, e.target);
                } else {
                    sortable.insertBefore(draggedElement, e.target.nextSibling);
                }
            }
        });
        
        // 为所有li元素添加draggable属性
        document.querySelectorAll('.sortable li').forEach(li => {
            li.draggable = true;
        });
        
        // 保存排序顺序到隐藏字段
        function updateSortOrder() {
            const order = Array.from(sortable.children).map(li => li.dataset.category);
            document.getElementById('categoryOrderInput').value = order.join(',');
        }
        
        // 在拖拽结束后更新排序
        sortable.addEventListener('drop', function() {
            setTimeout(updateSortOrder, 100);
        });
        
        // 初始化排序
        updateSortOrder();
        
        // 重置为默认配置
        function resetToDefault() {
            if (confirm('确定要重置为默认配置吗？这将清除所有自定义设置。')) {
                // 重置排序方式
                document.getElementById('sort_method').value = 'alphabetical';
                
                // 重置所有输入框
                document.querySelectorAll('input[name^="display_names"]').forEach(input => {
                    const category = input.name.match(/\[(.*?)\]/)[1];
                    input.value = category;
                });
                
                document.querySelectorAll('textarea[name^="descriptions"]').forEach(textarea => {
                    textarea.value = '';
                });
                
                alert('已重置为默认配置，点击保存按钮来应用更改。');
            }
        }
    </script>
</body>
</html>

<?php
// 处理表单提交
if ($_POST) {
    $configPath = '../app/Config/single_works_sort.php';
    
    // 构建新配置
    $newConfig = [
        'sort_method' => $_POST['sort_method'] ?? 'custom_order',
        'custom_order' => [],
        'prefix_settings' => [
            'remove_prefix' => true,
            'separator' => '-',
        ],
        'display_names' => $_POST['display_names'] ?? [],
        'descriptions' => $_POST['descriptions'] ?? []
    ];
    
    // 获取新的排序顺序
    $categoryOrder = $_POST['category_order'] ?? '';
    if (!empty($categoryOrder)) {
        $newConfig['custom_order'] = explode(',', $categoryOrder);
    } else {
        // 如果没有排序信息，保持原有顺序
        $originalConfig = file_exists($configPath) ? require $configPath : [];
        $newConfig['custom_order'] = $originalConfig['custom_order'] ?? [];
    }
    
    // 生成格式化的配置文件内容
    $configContent = "<?php\n/**\n * Single-Works 分组排序配置\n * \n * 支持多种排序方式：\n * 1. custom_order: 自定义排序数组\n * 2. prefix_sort: 按目录前缀排序\n * 3. alphabetical: 字母排序\n * 4. date_modified: 按修改时间排序\n */\n\nreturn " . var_export($newConfig, true) . ";";
    
    // 保存配置文件
    if (file_put_contents($configPath, $configContent)) {
        echo '<script>
            alert("✅ 配置保存成功！\\n\\n页面将刷新以显示最新配置。"); 
            window.location.reload();
        </script>';
    } else {
        echo '<script>alert("❌ 配置保存失败！请检查文件权限。");</script>';
    }
}
?>