<?php
/**
 * Single-Works 分类排序配置管理界面
 * 提供拖拽排序、显示名称编辑、描述信息管理和删除功能
 */

$current_page = 'sort-config';
require_once '../views/layouts/header.php';

// 处理表单提交
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_config':
            try {
                $configPath = '../../../app/Config/single_works_sort.php';
                
                // 构建新的配置数组
                $newConfig = [
                    'sort_method' => $_POST['sort_method'] ?? 'custom_order',
                    'custom_order' => explode(',', $_POST['custom_order'] ?? ''),
                    'display_names' => $_POST['display_names'] ?? [],
                    'descriptions' => $_POST['descriptions'] ?? []
                ];
                
                // 过滤空值
                $newConfig['custom_order'] = array_filter($newConfig['custom_order']);
                $newConfig['display_names'] = array_filter($newConfig['display_names']);
                $newConfig['descriptions'] = array_filter($newConfig['descriptions']);
                
                // 生成配置文件内容
                $configContent = "<?php\n";
                $configContent .= "/**\n";
                $configContent .= " * Single-Works 页面排序配置\n";
                $configContent .= " * 自动生成于: " . date('Y-m-d H:i:s') . "\n";
                $configContent .= " */\n\n";
                $configContent .= "return " . var_export($newConfig, true) . ";\n";
                
                // 写入配置文件
                if (file_put_contents($configPath, $configContent)) {
                    $message = "配置保存成功！";
                    $message_type = 'success';
                } else {
                    $message = "配置保存失败，请检查文件权限";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "保存失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'delete_category':
            try {
                $category = $_POST['category'] ?? '';
                if ($category) {
                    $trashDir = '../assets/images/trash';
                    if (!is_dir($trashDir)) {
                        mkdir($trashDir, 0755, true);
                    }
                    
                    $sourceDir = "../assets/images/single-works/$category";
                    if (is_dir($sourceDir)) {
                        $trashPath = $trashDir . '/' . date('Y-m-d_H-i-s') . '_' . $category;
                        if (rename($sourceDir, $trashPath)) {
                            $message = "分类 '$category' 已移至回收站";
                            $message_type = 'success';
                        } else {
                            $message = "删除失败，请检查权限";
                            $message_type = 'error';
                        }
                    } else {
                        $message = "分类目录不存在";
                        $message_type = 'error';
                    }
                }
            } catch (Exception $e) {
                $message = "删除失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
    }
}

// 读取当前配置
$configPath = '../../../app/Config/single_works_sort.php';
$currentConfig = file_exists($configPath) ? include $configPath : [
    'sort_method' => 'custom_order',
    'custom_order' => [],
    'display_names' => [],
    'descriptions' => []
];

// 获取所有分类
require_once '../../../app/Utils/GalleryManager.php';
$galleryManager = new GalleryManager();
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
?>

<div class="page-header">
    <h1>作品分类管理</h1>
    <p>管理 Single-Works 页面的分类排序和显示设置</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 排序方式设置 -->
<div class="content-card">
    <h3>🎯 排序方式</h3>
    
    <form method="POST" id="configForm">
        <input type="hidden" name="action" value="save_config">
        <input type="hidden" name="custom_order" id="customOrderInput" value="<?= implode(',', $customOrder) ?>">
        
        <div class="form-group">
            <label for="sort_method">选择排序方式</label>
            <select name="sort_method" id="sort_method" class="form-control">
                <option value="custom_order" <?= $currentConfig['sort_method'] === 'custom_order' ? 'selected' : '' ?>>
                    自定义排序（拖拽调整）
                </option>
                <option value="alphabetical" <?= $currentConfig['sort_method'] === 'alphabetical' ? 'selected' : '' ?>>
                    按字母顺序
                </option>
                <option value="prefix_sort" <?= $currentConfig['sort_method'] === 'prefix_sort' ? 'selected' : '' ?>>
                    按前缀排序
                </option>
                <option value="date_modified" <?= $currentConfig['sort_method'] === 'date_modified' ? 'selected' : '' ?>>
                    按修改时间
                </option>
            </select>
        </div>
    </form>
</div>

<!-- 分类管理 -->
<div class="content-card">
    <h3>📂 分类管理 <small>(拖拽 ⋮⋮ 图标调整顺序)</small></h3>
    
    <div id="categoryManagement" style="margin-top: 20px;">
        <?php if (empty($orderedCategories)): ?>
            <div class="alert alert-info">
                暂无分类。请在 <code>/public/assets/images/single-works/</code> 目录下创建文件夹来添加分类。
            </div>
        <?php else: ?>
            <ul class="sortable" id="categoryOrder" style="list-style: none; padding: 0;">
                <?php foreach ($orderedCategories as $category): ?>
                    <?php
                    $displayName = $currentConfig['display_names'][$category] ?? $category;
                    $description = $currentConfig['descriptions'][$category] ?? '';
                    $imageCount = count(glob("../assets/images/single-works/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
                    ?>
                    <li data-category="<?= htmlspecialchars($category) ?>" 
                        style="background: var(--bg-secondary); border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                        
                        <!-- 拖拽头部 -->
                        <div class="drag-handle" style="background: var(--primary-color); color: white; padding: 12px 20px; cursor: move; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="drag-handle-icon" style="font-size: 16px; cursor: grab;">⋮⋮</span>
                                <strong><?= htmlspecialchars($category) ?></strong>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 13px; opacity: 0.9;"><?= $imageCount ?> 张图片</span>
                                <button type="button" class="btn-delete" onclick="deleteCategory('<?= htmlspecialchars($category) ?>')" 
                                        style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                    🗑️ 删除
                                </button>
                            </div>
                        </div>
                        
                        <!-- 内容区域 -->
                        <div style="padding: 20px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>显示名称</label>
                                    <input type="text" 
                                           name="display_names[<?= htmlspecialchars($category) ?>]" 
                                           value="<?= htmlspecialchars($displayName) ?>" 
                                           class="form-control"
                                           form="configForm">
                                </div>
                                <div class="form-group">
                                    <label>描述信息</label>
                                    <textarea name="descriptions[<?= htmlspecialchars($category) ?>]" 
                                              class="form-control" 
                                              rows="2"
                                              form="configForm"
                                              placeholder="为该分类添加描述信息"><?= htmlspecialchars($description) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- 操作按钮 -->
<div class="content-card">
    <div class="btn-group">
        <button type="submit" form="configForm" class="btn btn-primary">
            <i data-feather="save"></i>
            保存配置
        </button>
        
        <button type="button" class="btn btn-outline" onclick="resetOrder()">
            <i data-feather="rotate-ccw"></i>
            重置顺序
        </button>
        
        <a href="trash.php" class="btn btn-outline">
            <i data-feather="trash-2"></i>
            查看回收站
        </a>
    </div>
</div>

<!-- 拖拽排序脚本 -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let sortable;

// 页面特定的JavaScript
function pageInit() {
    // 初始化拖拽排序
    const categoryList = document.getElementById('categoryOrder');
    if (categoryList) {
        sortable = Sortable.create(categoryList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateCustomOrder();
            }
        });
    }
    
    // 删除分类函数
    window.deleteCategory = function(category) {
        if (confirm(`确定要删除分类 "${category}" 吗？\n\n此操作会将分类移至回收站，可以在回收站中恢复。`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="category" value="${category}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    // 重置顺序
    window.resetOrder = function() {
        if (confirm('确定要重置为默认顺序吗？')) {
            location.href = location.pathname + '?reset=1';
        }
    };
    
    // 更新自定义排序
    function updateCustomOrder() {
        const items = document.querySelectorAll('#categoryOrder li');
        const order = Array.from(items).map(item => item.dataset.category);
        document.getElementById('customOrderInput').value = order.join(',');
    }
}

// 添加CSS样式
const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.5;
        background: var(--bg-primary) !important;
    }
    
    .sortable-chosen {
        cursor: grabbing !important;
    }
    
    .sortable-drag {
        transform: rotate(5deg);
        box-shadow: var(--shadow-heavy) !important;
    }
    
    .drag-handle:hover {
        background: var(--primary-hover) !important;
    }
    
    .btn-delete:hover {
        background: rgba(255,255,255,0.3) !important;
    }
`;
document.head.appendChild(style);
</script>

<?php require_once '../views/layouts/footer.php'; ?>