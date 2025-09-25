<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single-Works 排序配置</title>
    <style>
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            margin: 40px; 
            background: #f5f5f5; 
            line-height: 1.6;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: #333; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        .form-group { 
            margin-bottom: 25px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold; 
            color: #555; 
        }
        select { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #ddd; 
            border-radius: 6px; 
            font-size: 14px;
            background: white;
        }
        select:focus {
            border-color: #007cba;
            outline: none;
        }
        
        /* 分组列表样式 */
        .sortable { 
            list-style: none; 
            padding: 0; 
            margin: 0;
        }
        .sortable li { 
            background: white; 
            margin: 10px 0; 
            border-radius: 8px; 
            border: 2px solid #e9ecef;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .sortable li:hover {
            border-color: #007cba;
            box-shadow: 0 2px 8px rgba(0,124,186,0.1);
        }
        
        /* 拖拽手柄 */
        .drag-handle {
            background: #007cba;
            color: white;
            padding: 15px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: bold;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .drag-handle-icon {
            font-size: 18px;
            margin-right: 10px;
        }
        .drag-handle-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* 分组内容区域 */
        .category-content {
            padding: 20px;
            background: #f8f9fa;
        }
        
        /* 输入框样式统一 */
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-weight: normal;
            text-transform: uppercase;
        }
        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }
        .input-group input:focus,
        .input-group textarea:focus {
            border-color: #007cba;
            outline: none;
            background-color: #f8f9ff;
        }
        .input-group textarea {
            resize: vertical;
            min-height: 60px;
        }
        
        /* 按钮样式 */
        .btn { 
            background: #007cba; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 14px; 
            margin-right: 10px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        .btn:hover { 
            background: #005a87; 
            transform: translateY(-1px);
        }
        .btn-secondary { 
            background: #6c757d !important; 
        }
        .btn-secondary:hover { 
            background: #545b62 !important; 
        }
        .btn-danger {
            background: #dc3545 !important;
            padding: 8px 12px;
            font-size: 12px;
            margin: 0;
        }
        .btn-danger:hover {
            background: #c82333 !important;
        }
        
        /* 图片计数 */
        .image-count { 
            background: rgba(255,255,255,0.2); 
            color: white; 
            padding: 4px 8px; 
            border-radius: 12px; 
            font-size: 12px;
            font-weight: normal;
        }
        
        /* 表单操作区 */
        .form-actions { 
            display: flex; 
            align-items: center; 
            gap: 15px;
            padding: 20px 0;
            border-top: 1px solid #e9ecef;
            margin-top: 30px;
        }
        
        /* 预览区 */
        .preview { 
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%); 
            padding: 20px; 
            border-radius: 6px; 
            margin-top: 25px;
            border: 1px solid #b3d9ff;
        }
        .preview h3 {
            margin-top: 0;
            color: #007cba;
        }
        .preview a {
            color: #007cba;
            text-decoration: none;
            font-weight: 500;
        }
        .preview a:hover {
            text-decoration: underline;
        }
        
        /* 拖拽状态 */
        .sortable li.dragging {
            opacity: 0.5;
            transform: rotate(2deg);
        }
        .sortable li.drag-over {
            border-color: #28a745;
            background: #f8fff9;
        }
        
        /* 响应式 */
        @media (max-width: 768px) {
            body { margin: 20px; }
            .container { padding: 20px; }
            .drag-handle { padding: 12px; }
            .category-content { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Single-Works 分组排序配置</h1>
        
        <form method="post" id="configForm">
            <?php
            // 获取当前配置
            $configPath = '../app/Config/single_works_sort.php';
            $currentConfig = file_exists($configPath) ? require $configPath : [
                'sort_method' => 'custom_order',
                'custom_order' => [],
                'display_names' => [],
                'descriptions' => []
            ];
            $currentSortMethod = $currentConfig['sort_method'] ?? 'custom_order';
            ?>
            
            <div class="form-group">
                <label for="sort_method">🔢 排序方式</label>
                <select name="sort_method" id="sort_method">
                    <option value="custom_order" <?= $currentSortMethod === 'custom_order' ? 'selected' : '' ?>>自定义排序（拖拽调整）</option>
                    <option value="alphabetical" <?= $currentSortMethod === 'alphabetical' ? 'selected' : '' ?>>字母排序（A-Z）</option>
                    <option value="prefix_sort" <?= $currentSortMethod === 'prefix_sort' ? 'selected' : '' ?>>前缀排序（01-，02-）</option>
                    <option value="date_modified" <?= $currentSortMethod === 'date_modified' ? 'selected' : '' ?>>修改时间排序（最新优先）</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>📂 分组管理（拖拽 ⋮⋮ 图标调整顺序）</label>
                <ul class="sortable" id="categoryOrder">
                    <?php
                    // 引入管理器
                    require_once '../app/Utils/GalleryManager.php';
                    $galleryManager = new GalleryManager();
                    
                    // 获取所有实际存在的分组
                    $categories = $galleryManager->getGalleryCategories('single-works');
                    
                    // 处理自定义顺序
                    $customOrder = $currentConfig['custom_order'] ?? [];
                    $orderedCategories = [];
                    
                    // 先添加自定义顺序中的分组
                    foreach ($customOrder as $cat) {
                        if (in_array($cat, $categories)) {
                            $orderedCategories[] = $cat;
                        }
                    }
                    
                    // 再添加剩余的分组
                    foreach ($categories as $cat) {
                        if (!in_array($cat, $orderedCategories)) {
                            $orderedCategories[] = $cat;
                        }
                    }
                    
                    foreach ($orderedCategories as $category):
                        $displayName = $currentConfig['display_names'][$category] ?? $category;
                        $description = $currentConfig['descriptions'][$category] ?? '';
                        $imageCount = count(glob("../public/assets/images/single-works/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
                    ?>
                    <li data-category="<?= htmlspecialchars($category) ?>">
                        <div class="drag-handle">
                            <div style="display: flex; align-items: center;">
                                <span class="drag-handle-icon">⋮⋮</span>
                                <strong><?= htmlspecialchars($category) ?></strong>
                            </div>
                            <div class="drag-handle-right">
                                <span class="image-count"><?= $imageCount ?> 张图片</span>
                                <button type="button" class="btn btn-danger" onclick="deleteCategory('<?= htmlspecialchars($category) ?>')" title="删除分组">
                                    🗑️ 删除
                                </button>
                            </div>
                        </div>
                        <div class="category-content">
                            <div class="input-group">
                                <label>显示名称</label>
                                <input type="text" 
                                       name="display_names[<?= htmlspecialchars($category) ?>]" 
                                       value="<?= htmlspecialchars($displayName) ?>"
                                       placeholder="例如: 动物世界">
                            </div>
                            <div class="input-group">
                                <label>描述信息</label>
                                <textarea name="descriptions[<?= htmlspecialchars($category) ?>]" 
                                         placeholder="例如: 各种动物主题的插画作品"><?= htmlspecialchars($description) ?></textarea>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <input type="hidden" name="category_order" id="categoryOrderInput" value="">
            <input type="hidden" name="deleted_categories" id="deletedCategoriesInput" value="">
            
            <div class="form-actions">
                <button type="submit" class="btn">💾 保存配置</button>
                <button type="button" class="btn btn-secondary" onclick="resetToDefault()">🔄 重置为默认</button>
                <span style="color: #666; font-size: 12px;">保存后将自动刷新页面</span>
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
        // 拖拽排序功能
        const sortable = document.getElementById('categoryOrder');
        let draggedElement = null;
        let deletedCategories = [];
        
        // 只对拖拽手柄启用拖拽
        document.querySelectorAll('.drag-handle').forEach(handle => {
            handle.draggable = true;
            
            handle.addEventListener('dragstart', function(e) {
                draggedElement = e.target.closest('li');
                draggedElement.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            
            handle.addEventListener('dragend', function(e) {
                draggedElement.classList.remove('dragging');
                document.querySelectorAll('.sortable li').forEach(li => {
                    li.classList.remove('drag-over');
                });
                updateCategoryOrder();
                draggedElement = null;
            });
        });
        
        // 设置拖拽目标
        sortable.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const afterElement = getDragAfterElement(sortable, e.clientY);
            const dragging = document.querySelector('.dragging');
            
            // 清除之前的高亮
            document.querySelectorAll('.sortable li').forEach(li => {
                li.classList.remove('drag-over');
            });
            
            if (afterElement == null) {
                sortable.appendChild(dragging);
            } else {
                afterElement.classList.add('drag-over');
                sortable.insertBefore(dragging, afterElement);
            }
        });
        
        sortable.addEventListener('drop', function(e) {
            e.preventDefault();
            updateCategoryOrder();
        });
        
        // 获取拖拽后的插入位置
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('li:not(.dragging)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
        
        // 更新分组顺序
        function updateCategoryOrder() {
            const order = [];
            document.querySelectorAll('#categoryOrder li').forEach(li => {
                order.push(li.dataset.category);
            });
            document.getElementById('categoryOrderInput').value = order.join(',');
        }
        
        // 删除分组功能
        function deleteCategory(categoryName) {
            if (confirm(`确定要删除分组"${categoryName}"吗？\n\n删除后该分组的文件夹将被移动到 trash 目录，可以手动恢复。`)) {
                // 添加到删除列表
                deletedCategories.push(categoryName);
                document.getElementById('deletedCategoriesInput').value = deletedCategories.join(',');
                
                // 从界面中移除
                const listItem = document.querySelector(`li[data-category="${categoryName}"]`);
                if (listItem) {
                    listItem.style.transition = 'all 0.3s ease';
                    listItem.style.opacity = '0.5';
                    listItem.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        listItem.remove();
                        updateCategoryOrder();
                    }, 300);
                }
            }
        }
        
        // 重置为默认
        function resetToDefault() {
            if (confirm('确定要重置为默认配置吗？这将清除所有自定义设置。')) {
                // 清空所有输入
                document.querySelectorAll('input[type="text"], textarea').forEach(input => {
                    if (input.name.includes('display_names')) {
                        // 重置显示名称为分组名称
                        const category = input.name.match(/\[(.*?)\]/)[1];
                        input.value = category;
                    } else if (input.name.includes('descriptions')) {
                        input.value = '';
                    }
                });
                
                // 重置排序方式
                document.getElementById('sort_method').value = 'custom_order';
                
                // 重置删除列表
                deletedCategories = [];
                document.getElementById('deletedCategoriesInput').value = '';
                
                updateCategoryOrder();
            }
        }
        
        // 表单提交时更新顺序
        document.getElementById('configForm').addEventListener('submit', function() {
            updateCategoryOrder();
        });
        
        // 初始化顺序
        updateCategoryOrder();
    </script>
</body>
</html>

<?php
// 处理表单提交
if ($_POST) {
    try {
        // 处理删除的分组
        if (!empty($_POST['deleted_categories'])) {
            $deletedCategories = explode(',', $_POST['deleted_categories']);
            $trashDir = '../public/assets/images/trash';
            
            // 创建trash目录
            if (!is_dir($trashDir)) {
                mkdir($trashDir, 0755, true);
            }
            
            foreach ($deletedCategories as $category) {
                $category = trim($category);
                if (empty($category)) continue;
                
                $sourceDir = "../public/assets/images/single-works/$category";
                $targetDir = "$trashDir/$category-" . date('Y-m-d-H-i-s');
                
                if (is_dir($sourceDir)) {
                    if (rename($sourceDir, $targetDir)) {
                        error_log("分组 '$category' 已移动到 trash 目录: $targetDir");
                    } else {
                        error_log("无法移动分组 '$category' 到 trash 目录");
                    }
                }
            }
        }
        
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
        
        // 处理分组顺序
        if (!empty($_POST['category_order'])) {
            $newConfig['custom_order'] = array_filter(explode(',', $_POST['category_order']));
        }
        
        // 保存配置文件
        $configContent = "<?php\n/**\n * Single-Works 分组排序配置\n * 自动生成于: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($newConfig, true) . ";";
        
        if (file_put_contents($configPath, $configContent)) {
            echo '<script>
                alert("✅ 配置保存成功！");
                window.location.href = window.location.pathname;
            </script>';
        } else {
            echo '<script>alert("❌ 配置保存失败，请检查文件权限！");</script>';
        }
        
    } catch (Exception $e) {
        echo '<script>alert("❌ 保存过程中发生错误: ' . addslashes($e->getMessage()) . '");</script>';
    }
}
?>