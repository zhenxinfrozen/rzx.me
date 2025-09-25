<?php
require_once '../../../app/bootstrap.php';

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 处理表单提交
if ($_POST) {
    try {
        // 配置文件路径
        $configPath = '../../../app/Config/single_works_sort.php';
        
        // 处理删除的分组
        if (!empty($_POST['deleted_categories'])) {
            $deletedCategories = explode(',', $_POST['deleted_categories']);
            $trashDir = '../assets/images/trash';
            
            if (!is_dir($trashDir)) {
                mkdir($trashDir, 0755, true);
            }
            
            foreach ($deletedCategories as $category) {
                $category = trim($category);
                if (empty($category)) continue;
                
                $sourceDir = "../../assets/images/single-works/$category";
                $targetDir = "$trashDir/$category-" . date('Y-m-d-H-i-s');
                
                if (is_dir($sourceDir)) {
                    rename($sourceDir, $targetDir);
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
            $_SESSION['save_message'] = ['type' => 'success', 'text' => '配置保存成功！'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $_SESSION['save_message'] = ['type' => 'error', 'text' => '配置保存失败，请检查文件权限！'];
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['save_message'] = ['type' => 'error', 'text' => '保存过程中发生错误: ' . $e->getMessage()];
    }
}

// 加载配置
$configPath = '../../../app/Config/single_works_sort.php';
$currentConfig = file_exists($configPath) ? include($configPath) : [
    'sort_method' => 'custom_order',
    'custom_order' => [],
    'prefix_settings' => ['remove_prefix' => true, 'separator' => '-'],
    'display_names' => [],
    'descriptions' => []
];

$page_title = 'Single-Works 排序配置';
require_once '../views/layouts/header.php';
?>

<style>
.category-list { max-height: 500px; overflow-y: auto; padding: 10px; }
.category-item { 
    margin-bottom: 10px; 
    transition: all 0.3s ease;
}
.category-row { 
    transition: all 0.2s ease; 
    border-radius: 8px;
    background: white;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
}
.category-row:hover { 
    background: #f8f9fa !important; 
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
    transform: translateY(-1px);
}
.category-row.active { 
    background: #e3f2fd !important; 
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
}
.category-item.dragging {
    opacity: 0.8;
    transform: scale(1.02) rotate(2deg);
    z-index: 1000;
    box-shadow: 0 8px 25px rgba(0,0,0,0.3) !important;
}
.category-item.drag-over {
    border-top: 3px solid #007bff;
    margin-top: 15px;
}
.drag-handle { 
    color: #6c757d; 
    cursor: grab; 
    user-select: none; 
    font-size: 18px;
    padding: 5px;
    margin-right: 5px;
    border-radius: 4px;
    transition: all 0.2s ease;
}
.drag-handle:hover {
    background: rgba(0,123,255,0.1);
    color: #007bff;
}
.drag-handle:active, .drag-handle.grabbing { 
    cursor: grabbing; 
    background: rgba(0,123,255,0.2);
}
.category-content {
    cursor: pointer;
    flex: 1;
    min-width: 0;
}
.thumbnail-container { min-height: 200px; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #f8f9fa; }
.thumbnail-item { width: 80px; height: 80px; margin: 5px; border-radius: 4px; overflow: hidden; display: inline-block; border: 2px solid #dee2e6; }
.thumbnail-item img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-item:hover { border-color: #007bff; }
.add-image-btn {
    width: 80px; 
    height: 80px; 
    margin: 5px; 
    border-radius: 4px; 
    border: 2px dashed #007bff; 
    background: #f8f9fa;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 24px;
}
.add-image-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
}
.add-category-btn {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: #108e1dff;
    color: white;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(9, 115, 133, 1);
}
.add-category-btn:hover {
    background: #47a854ff;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(39, 39, 39, 0.72);
}
.add-category-btn:active {
    transform: translateY(-50%) scale(0.95);
}
.action-buttons {
    position: relative;
    margin-top: 15px;
    text-align: right;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

/* 页面内提示样式 */
.alert-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1055;
    min-width: 300px;
    animation: slideInRight 0.3s ease-out;
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.alert-toast.fade-out {
    animation: slideOutRight 0.3s ease-in;
}
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>

<!-- 消息提示区域 -->
<?php if (isset($_SESSION['save_message'])): ?>
<div class="alert alert-<?= $_SESSION['save_message']['type'] === 'success' ? 'success' : 'danger' ?> alert-toast" role="alert">
    <i data-feather="<?= $_SESSION['save_message']['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="me-2"></i>
    <?= htmlspecialchars($_SESSION['save_message']['text']) ?>
</div>
<?php unset($_SESSION['save_message']); endif; ?>

<!-- 主要内容区域 -->
<div class="main-content">
    <div class="content-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i data-feather="layers" class="me-2"></i>
            Single-Works 排序配置
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">仪表板</a></li>
                <li class="breadcrumb-item active">排序配置</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <!-- 排序方式选择 -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i data-feather="settings" class="me-2"></i>
                    排序方式设置
                </h5>
            </div>
            <div class="card-body">
                <form method="post" id="configForm">
                    <?php $currentSortMethod = $currentConfig['sort_method'] ?? 'custom_order'; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="sort_method" class="form-label">排序方式</label>
                            <select name="sort_method" id="sort_method" class="form-select">
                                <option value="custom_order" <?= $currentSortMethod === 'custom_order' ? 'selected' : '' ?>>自定义排序（拖拽调整）</option>
                                <option value="alphabetical" <?= $currentSortMethod === 'alphabetical' ? 'selected' : '' ?>>字母排序（A-Z）</option>
                                <option value="prefix_sort" <?= $currentSortMethod === 'prefix_sort' ? 'selected' : '' ?>>前缀排序（01-，02-）</option>
                                <option value="date_modified" <?= $currentSortMethod === 'date_modified' ? 'selected' : '' ?>>修改时间排序（最新优先）</option>
                            </select>
                        </div>
                    </div>
            </div>
        </div>

        <!-- 三栏布局 -->
        <div class="row g-3">
            <!-- 左栏：分组列表 -->
            <div class="col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white position-relative">
                        <h6 class="card-title mb-0">
                            <i data-feather="list" class="me-2"></i>
                            分组顺序
                        </h6>
                        <button type="button" class="add-category-btn" onclick="showAddCategoryPanel()" title="添加新分组">
                            +
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <ul class="category-list list-unstyled mb-0" id="categoryOrder">
                            <?php
                            // 引入管理器
                            require_once '../../../app/Utils/GalleryManager.php';
                            $galleryManager = new GalleryManager();
                            $categories = $galleryManager->getGalleryCategories('single-works');
                            
                            // 处理自定义顺序
                            $customOrder = $currentConfig['custom_order'] ?? [];
                            $orderedCategories = [];
                            
                            foreach ($customOrder as $cat) {
                                if (in_array($cat, $categories)) {
                                    $orderedCategories[] = $cat;
                                }
                            }
                            
                            foreach ($categories as $cat) {
                                if (!in_array($cat, $orderedCategories)) {
                                    $orderedCategories[] = $cat;
                                }
                            }
                            
                            foreach ($orderedCategories as $index => $category):
                                $displayName = $currentConfig['display_names'][$category] ?? $category;
                                $description = $currentConfig['descriptions'][$category] ?? '';
                                $imageCount = count(glob("../../assets/images/single-works/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE));
                            ?>
                            <li class="category-item" data-category="<?= htmlspecialchars($category) ?>" draggable="true">
                                <div class="category-row d-flex align-items-center p-3">
                                    <span class="drag-handle" title="拖拽排序">⋮⋮</span>
                                    <div class="category-content d-flex align-items-center flex-grow-1" onclick="editCategory('<?= htmlspecialchars($category) ?>')">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold category-name"><?= htmlspecialchars($displayName ?: $category) ?></div>
                                            <small class="text-muted"><?= $imageCount ?> 张图片</small>
                                        </div>
                                        <span class="badge bg-secondary"><?= $index + 1 ?></span>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="display_names[<?= htmlspecialchars($category) ?>]" 
                                       value="<?= htmlspecialchars($displayName) ?>" class="display-name-input">
                                <input type="hidden" name="descriptions[<?= htmlspecialchars($category) ?>]" 
                                       value="<?= htmlspecialchars($description) ?>" class="description-input">
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 中栏：编辑区域 -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i data-feather="edit-3" class="me-2" id="edit-icon"></i>
                            <span id="edit-title">编辑分组</span>
                        </h6>
                        <small id="edit-status" class="opacity-75">选择左侧分组进行编辑</small>
                    </div>
                    <div class="card-body">
                        <!-- 编辑提示 -->
                        <div id="edit-panel-placeholder" class="text-center text-muted py-4">
                            <i data-feather="arrow-left" style="width: 48px; height: 48px; opacity: 0.5;"></i>
                            <p class="mt-3">点击左侧分组开始编辑</p>
                        </div>
                        
                        <!-- 添加分组面板 -->
                        <div id="add-category-panel" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">新分组名称</label>
                                    <input type="text" id="new-category-name" class="form-control" placeholder="输入分组名称（英文，不含空格）">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">显示名称</label>
                                    <input type="text" id="new-display-name" class="form-control" placeholder="显示给用户的名称">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">排序位置</label>
                                    <select id="new-category-position" class="form-select">
                                        <option value="first">第一个</option>
                                        <option value="last" selected>最后一个</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">描述信息</label>
                                <textarea id="new-description" class="form-control" rows="2" placeholder="可选的描述信息"></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="createNewCategory()">
                                    <i data-feather="plus" class="me-1"></i>创建分组
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="cancelAddCategory()">
                                    <i data-feather="x" class="me-1"></i>取消
                                </button>
                            </div>
                        </div>
                        
                        <!-- 编辑面板 -->
                        <div id="edit-panel" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">显示名称</label>
                                    <input type="text" id="edit-display-name" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted">文件夹名</label>
                                    <input type="text" id="edit-folder-name" class="form-control" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">描述信息</label>
                                <textarea id="edit-description" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <h6><i data-feather="image" class="me-1"></i>图片管理</h6>
                            <div id="thumbnail-grid" class="thumbnail-container">
                                <!-- 缩略图将在这里显示 -->
                            </div>
                            
                            <div class="action-buttons d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="saveCategory()">
                                    <i data-feather="save" class="me-1"></i>更新
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteCurrentCategory()">
                                    <i data-feather="trash-2" class="me-1"></i>删除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 右栏：预览和管理 -->
            <div class="col-lg-3">
                <div class="d-flex flex-column gap-3 h-100">
                    <!-- 预览区 -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i data-feather="eye" class="me-2"></i>预览
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/single-works" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i data-feather="external-link" class="me-1"></i>前台页面
                                </a>
                                <a href="/api/single-works" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i data-feather="code" class="me-1"></i>API数据
                                </a>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between">
                                    <span>总分组:</span>
                                    <span><?= count($orderedCategories) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 管理区 -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i data-feather="settings" class="me-2"></i>管理
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="trash.php" class="btn btn-outline-warning btn-sm">
                                    <i data-feather="trash-2" class="me-1"></i>回收站
                                </a>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="showPhpConfigInfo()">
                                    <i data-feather="info" class="me-1"></i>PHP配置
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetToDefault()">
                                    <i data-feather="refresh-cw" class="me-1"></i>重置配置
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 底部保存 -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <input type="hidden" name="category_order" id="categoryOrderInput" value="">
                <input type="hidden" name="deleted_categories" id="deletedCategoriesInput" value="">
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="me-1"></i>保存所有配置
                        </button>
                    </div>
                    <small class="text-muted">保存后将自动刷新页面</small>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// 全局变量
let currentEditingCategory = null;
let deletedCategories = [];
let draggedElement = null;

// 编辑分组功能
function editCategory(categoryName) {
    currentEditingCategory = categoryName;
    
    // 更新活动状态
    document.querySelectorAll('.category-row').forEach(row => row.classList.remove('active'));
    document.querySelector(`[data-category="${categoryName}"] .category-row`).classList.add('active');
    
    // 获取当前数据
    const displayNameInput = document.querySelector(`input[name="display_names[${categoryName}]"]`);
    const descriptionInput = document.querySelector(`input[name="descriptions[${categoryName}]"]`);
    
    // 填充编辑表单
    document.getElementById('edit-display-name').value = displayNameInput ? displayNameInput.value : categoryName;
    document.getElementById('edit-folder-name').value = categoryName;
    document.getElementById('edit-description').value = descriptionInput ? descriptionInput.value : '';
    
    // 隐藏其他面板，显示编辑面板
    document.getElementById('edit-panel-placeholder').style.display = 'none';
    document.getElementById('add-category-panel').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'block';
    
    // 更新标题和状态
    document.getElementById('edit-title').textContent = '编辑分组';
    document.getElementById('edit-status').textContent = `正在编辑: ${categoryName}`;
    
    // 更新图标
    const icon = document.getElementById('edit-icon');
    icon.setAttribute('data-feather', 'edit-3');
    feather.replace();
    
    // 加载缩略图
    loadCategoryThumbnails(categoryName);
}

// 保存分组修改
function saveCategory() {
    if (!currentEditingCategory) return;
    
    const displayName = document.getElementById('edit-display-name').value;
    const description = document.getElementById('edit-description').value;
    
    // 更新隐藏表单字段
    const displayNameInput = document.querySelector(`input[name="display_names[${currentEditingCategory}]"]`);
    const descriptionInput = document.querySelector(`input[name="descriptions[${currentEditingCategory}]"]`);
    
    if (displayNameInput) displayNameInput.value = displayName;
    if (descriptionInput) descriptionInput.value = description;
    
    // 更新左侧显示
    const categoryNameEl = document.querySelector(`[data-category="${currentEditingCategory}"] .category-name`);
    if (categoryNameEl) categoryNameEl.textContent = displayName || currentEditingCategory;
    
    showToast('success', '分组信息已更新');
}

// 删除当前分组
function deleteCurrentCategory() {
    if (!currentEditingCategory) return;
    if (confirm(`确定要删除分组"${currentEditingCategory}"吗？`)) {
        deletedCategories.push(currentEditingCategory);
        document.getElementById('deletedCategoriesInput').value = deletedCategories.join(',');
        
        const listItem = document.querySelector(`.category-item[data-category="${currentEditingCategory}"]`);
        if (listItem) listItem.remove();
        
        // 取消编辑状态
        document.getElementById('edit-panel').style.display = 'none';
        document.getElementById('edit-panel-placeholder').style.display = 'block';
        currentEditingCategory = null;
        
        updateCategoryOrder();
    }
}

// 加载缩略图
function loadCategoryThumbnails(categoryName) {
    const container = document.getElementById('thumbnail-grid');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> 加载中...</div>';
    
    // 通过AJAX获取缩略图列表
    fetch(`get-thumbnails.php?category=${encodeURIComponent(categoryName)}`)
        .then(response => response.json())
        .then(data => {
            let html = '<div class="d-flex flex-wrap">';
            
            // 添加上传按钮（第一个位置）
            html += `
                <div class="add-image-btn" onclick="showImageUpload('${categoryName}')" title="添加图片">
                    <i data-feather="plus"></i>
                </div>
            `;
            
            if (data.success && data.images.length > 0) {
                data.images.forEach(image => {
                    html += `
                        <div class="thumbnail-item" title="${image.name}" onclick="selectImage('${image.name}')">
                            <img src="${image.thumb_path}" alt="${image.name}" loading="lazy">
                        </div>
                    `;
                });
                html += '</div>';
                html += `<div class="mt-2 small text-muted">共 ${data.images.length} 张图片</div>`;
            } else {
                html += '</div>';
                html += `<div class="mt-2 small text-muted">暂无图片，点击 [+] 添加</div>`;
            }
            
            container.innerHTML = html;
            feather.replace();
        })
        .catch(error => {
            console.error('加载缩略图失败:', error);
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i data-feather="alert-circle" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                    <p class="mt-2 small">加载失败，请重试</p>
                </div>
            `;
            feather.replace();
        });
}

// 拖拽排序
function initializeDragAndDrop() {
    const categoryList = document.getElementById('categoryOrder');
    let draggedItem = null;
    
    // 为每个分组项添加拖拽事件
    document.querySelectorAll('.category-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
            
            // 设置拖拽手柄状态
            const handle = this.querySelector('.drag-handle');
            if (handle) handle.classList.add('grabbing');
            
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', '');
        });
        
        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            
            // 移除手柄状态
            const handle = this.querySelector('.drag-handle');
            if (handle) handle.classList.remove('grabbing');
            
            // 移除所有拖拽样式
            document.querySelectorAll('.category-item').forEach(item => {
                item.classList.remove('drag-over');
            });
            
            draggedItem = null;
            updateCategoryOrder();
        });
        
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (draggedItem && draggedItem !== this) {
                const rect = this.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                
                // 清除所有拖拽样式
                document.querySelectorAll('.category-item').forEach(item => {
                    item.classList.remove('drag-over');
                });
                
                // 根据鼠标位置决定插入位置
                if (e.clientY < midY) {
                    categoryList.insertBefore(draggedItem, this);
                } else {
                    categoryList.insertBefore(draggedItem, this.nextSibling);
                }
                
                this.classList.add('drag-over');
            }
        });
        
        item.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
    });
}

// 更新分组顺序
function updateCategoryOrder() {
    const order = [];
    document.querySelectorAll('#categoryOrder .category-item').forEach(item => {
        order.push(item.dataset.category);
    });
    document.getElementById('categoryOrderInput').value = order.join(',');
}

// 显示PHP配置信息
function showPhpConfigInfo() {
    fetch('check-php-config.php')
        .then(response => response.json())
        .then(data => {
            let message = `PHP上传配置：\n`;
            message += `• 单文件大小限制: ${data.config.upload_max_filesize}\n`;
            message += `• POST数据限制: ${data.config.post_max_size}\n`;
            message += `• 最大文件数: ${data.config.max_file_uploads}\n`;
            message += `• 执行时间限制: ${data.config.max_execution_time}秒\n`;
            
            if (data.issues.length > 0) {
                message += `\n问题：\n`;
                data.issues.forEach(issue => {
                    message += `• ${issue}\n`;
                });
                
                message += `\n建议：\n`;
                data.recommendations.forEach(rec => {
                    message += `• ${rec}\n`;
                });
            } else {
                message += `\n✅ 配置良好，支持大文件上传`;
            }
            
            alert(message);
        })
        .catch(error => {
            showToast('error', '获取PHP配置失败');
        });
}

// 重置配置
function resetToDefault() {
    if (confirm('确定要重置为默认配置吗？')) {
        location.reload();
    }
}

// 显示添加分组面板
function showAddCategoryPanel() {
    // 隐藏其他面板
    document.getElementById('edit-panel-placeholder').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('add-category-panel').style.display = 'block';
    
    // 更新标题
    document.getElementById('edit-title').textContent = '添加分组';
    document.getElementById('edit-status').textContent = '创建新的作品分组';
    
    // 更新图标
    const icon = document.getElementById('edit-icon');
    icon.setAttribute('data-feather', 'plus');
    feather.replace();
    
    // 清空表单
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';
}

// 取消添加分组
function cancelAddCategory() {
    document.getElementById('add-category-panel').style.display = 'none';
    document.getElementById('edit-panel-placeholder').style.display = 'block';
    
    // 恢复标题
    document.getElementById('edit-title').textContent = '编辑分组';
    document.getElementById('edit-status').textContent = '选择左侧分组进行编辑';
    
    // 恢复图标
    const icon = document.getElementById('edit-icon');
    icon.setAttribute('data-feather', 'edit-3');
    feather.replace();
}

// 创建新分组
function createNewCategory() {
    const categoryName = document.getElementById('new-category-name').value.trim();
    const displayName = document.getElementById('new-display-name').value.trim();
    const description = document.getElementById('new-description').value.trim();
    const position = document.getElementById('new-category-position').value;
    
    if (!categoryName) {
        showToast('error', '请输入分组名称');
        return;
    }
    
    if (!/^[a-zA-Z0-9_-]+$/.test(categoryName)) {
        showToast('error', '分组名称只能包含字母、数字、下划线和连字符');
        return;
    }
    
    // 检查是否重复
    const existingCategories = Array.from(document.querySelectorAll('.category-item')).map(item => item.dataset.category);
    if (existingCategories.includes(categoryName)) {
        showToast('error', '分组名称已存在');
        return;
    }
    
    // 创建目录
    fetch('create-category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            category: categoryName,
            displayName: displayName || categoryName,
            description: description,
            position: position
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', '分组创建成功！页面将刷新');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || '创建失败');
        }
    })
    .catch(error => {
        console.error('创建分组失败:', error);
        showToast('error', '创建失败，请重试');
    });
}

// 显示图片上传
function showImageUpload(categoryName) {
    // 创建文件输入元素
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.multiple = true;
    
    input.onchange = function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            uploadImages(categoryName, files);
        }
    };
    
    input.click();
}

// 上传图片
function uploadImages(categoryName, files) {
    // 验证文件大小
    const maxSize = 50 * 1024 * 1024; // 50MB
    const oversizedFiles = Array.from(files).filter(file => file.size > maxSize);
    
    if (oversizedFiles.length > 0) {
        showToast('error', `有 ${oversizedFiles.length} 个文件超过50MB限制`);
        return;
    }
    
    // 检查是否有大文件，提醒PHP配置
    const largeFiles = Array.from(files).filter(file => file.size > 8 * 1024 * 1024); // 8MB
    if (largeFiles.length > 0) {
        showToast('warning', '检测到大文件，如果上传失败请检查PHP配置');
    }
    
    const formData = new FormData();
    formData.append('category', categoryName);
    
    for (let i = 0; i < files.length; i++) {
        formData.append('images[]', files[i]);
    }
    
    const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
    const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(1);
    showToast('info', `正在上传 ${files.length} 张图片 (${totalSizeMB}MB)...`);
    
    fetch('upload-images.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', `成功上传 ${data.uploaded} 张图片`);
            // 重新加载缩略图
            loadCategoryThumbnails(categoryName);
        } else {
            showToast('error', data.message || '上传失败');
        }
    })
    .catch(error => {
        console.error('上传失败:', error);
        showToast('error', '上传失败。如果文件较大，请检查PHP配置：upload_max_filesize和post_max_size');
    });
}

// 选择图片
function selectImage(imageName) {
    // 这里可以添加图片选择/编辑功能
    showToast('info', `选择了图片: ${imageName}`);
}

// 显示页面内提示
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-toast`;
    toast.innerHTML = `
        <i data-feather="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    feather.replace();
    
    // 3秒后自动隐藏
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// 检查PHP配置
function checkPhpConfig() {
    fetch('check-php-config.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'warning' && data.issues.length > 0) {
                const message = `PHP配置可能影响文件上传：${data.issues.slice(0, 2).join('; ')}`;
                showToast('warning', message);
                console.log('PHP配置建议:', data.recommendations);
            }
        })
        .catch(error => {
            console.log('PHP配置检查失败:', error);
        });
}

// 页面初始化
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    updateCategoryOrder();
    feather.replace();
    
    // 检查PHP配置
    setTimeout(checkPhpConfig, 1000);
    
    // 自动隐藏已存在的提示
    const existingToast = document.querySelector('.alert-toast');
    if (existingToast) {
        setTimeout(() => {
            existingToast.classList.add('fade-out');
            setTimeout(() => {
                if (existingToast.parentNode) {
                    existingToast.parentNode.removeChild(existingToast);
                }
            }, 300);
        }, 3000);
    }
});
</script>

<?php require_once '../views/layouts/footer.php'; ?>