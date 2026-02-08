<?php
/**
 * 后台管理页面标准模板 - New 架构 v1.0.0
 * 使用说明：复制此文件，全局替换 [MODULE] 和 [module]
 *
 * ⚠️ 注意：本文件包含占位数据和占位图片，仅用于模板演示
 * 📁 占位资源：/assets/images/template/ (占位符图片)
 * 🤖 AI Agent 提示：所有带 TEMPLATE PLACEHOLDER 注释的都是占位内容
 */

$page_title = $page_title ?? '🛠️ [MODULE] 管理 (新版)';
$page_subtitle = $page_subtitle ?? '管理 [MODULE] 内容';
$_GET['page'] = $_GET['page'] ?? '[module]-new';

// ============================================
// 🎭 TEMPLATE PLACEHOLDER DATA - 占位数据
// 实际使用时替换为真实数据加载逻辑
// ============================================
$categoryData = $categoryData ?? [
    [
        'id' => 'example-category-1',
        'display_name' => '示例分类 1 (占位)',
        'description' => '这是模板占位数据',
        'thumbnail' => '/assets/images/template/placeholder-1.svg',
        'image_count' => 5,
        'position' => 1
    ],
    [
        'id' => 'example-category-2',
        'display_name' => '示例分类 2 (占位)',
        'description' => '这是模板占位数据',
        'thumbnail' => '/assets/images/template/placeholder-2.svg',
        'image_count' => 8,
        'position' => 2
    ],
    [
        'id' => 'example-category-3',
        'display_name' => '示例分类 3 (占位)',
        'description' => '这是模板占位数据',
        'thumbnail' => '/assets/images/template/placeholder-3.svg',
        'image_count' => 3,
        'position' => 3
    ],
];
// ============================================
$totalCategories = count($categoryData);
?>

<link rel="stylesheet" href="/assets/admin/css/admin-common.css?v=1.0.0">
<link rel="stylesheet" href="/assets/admin/css/admin-three-column.css?v=1.0.0">
<link rel="stylesheet" href="/assets/admin/css/admin-image-manager.css?v=1.0.0">

<style>
/* 添加按钮样式 */
.add-category-btn {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: #108e1d;
    color: white;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(16, 142, 29, 0.6);
}
.add-category-btn:hover {
    background: #47a854;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(16, 142, 29, 0.8);
}
.add-category-btn:active {
    transform: translateY(-50%) scale(0.95);
}

/* 列表项样式 */
.admin-list-item {
    /* border-radius: 8px;
    background: white;
    border: 1px solid #e9ecef; */
    transition: all 0.2s ease;
    cursor: move;
}

.admin-list-item:hover {
    background: #f8f9fa;
    border-color: #28a745;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
    transform: translateY(-1px);
}

/* 选中状态 - 使用 sketchbook-new 的蓝色外发光效果 */
.admin-list-item.active {
    background: #e3f2fd;
    border-color: #007bff;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
}

/* 拖拽手柄 */
.admin-drag-handle {
    color: #6c757d;
    font-size: 18px;
    margin-right: 12px;
    cursor: grab;
    user-select: none;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.admin-list-item:hover .admin-drag-handle {
    opacity: 1;
}

.admin-drag-handle:active {
    cursor: grabbing;
}

/* 缩略图样式 */
.admin-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #dee2e6;
    transition: all 0.2s ease;
}

.admin-thumbnail-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    border: 2px dashed #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #6c757d;
}

/* 徽章样式 */
.admin-badge {
    min-width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    padding: 0 8px;
}

.admin-badge-primary {
    background: #007bff;
    color: white;
}

/* 文本样式 */
.admin-text-muted {
    color: #6c757d;
    font-size: 13px;
}

/* 图片管理器样式 */
.admin-thumbnail-grid-container {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.admin-image-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    transition: all 0.2s ease;
    cursor: move;
    flex-shrink: 0;
}

.admin-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.admin-image-item:hover {
    border-color: #007bff;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.admin-image-item.is-thumbnail {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40,167,69,.25);
}

/* 图片操作按钮 */
.admin-image-actions {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.admin-image-item:hover .admin-image-actions {
    opacity: 1;
}

.admin-image-action-btn {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    padding: 0;
}

.admin-image-action-btn:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.admin-image-action-btn.star {
    color: #ffc107;
}

.admin-image-action-btn.delete {
    color: #dc3545;
}

.admin-image-action-btn i {
    width: 14px;
    height: 14px;
}

/* 上传按钮 */
.admin-image-upload-btn {
    width: 80px;
    height: 80px;
    border: 2px dashed #007bff;
    border-radius: 6px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 12px;
    text-align: center;
    gap: 4px;
    flex-shrink: 0;
}

.admin-image-upload-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.05);
}

.admin-image-upload-btn i {
    width: 24px;
    height: 24px;
}

/* 统计卡片 */
.admin-stat-card {
    padding: 20px;
    border-radius: 8px;
    background: white;
    text-align: center;
}

.admin-stat-card h3 {
    font-size: 32px;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.admin-stat-card p {
    margin: 8px 0 0 0;
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.admin-stat-card small {
    display: block;
    margin-top: 4px;
    font-size: 11px;
}

/* 渐变背景的统计卡片文字优化 */
.admin-stat-card[style*="gradient"] h3,
.admin-stat-card[style*="gradient"] p {
    color: white !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* 按钮样式 */
.admin-btn {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.admin-btn i {
    width: 16px;
    height: 16px;
}

.admin-btn-success {
    background: #28a745;
    color: white;
}

.admin-btn-success:hover {
    background: #218838;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.admin-btn-primary {
    background: #007bff;
    color: white;
}

.admin-btn-primary:hover {
    background: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.admin-btn-danger {
    background: #dc3545;
    color: white;
}

.admin-btn-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.admin-btn-secondary {
    background: #6c757d;
    color: white;
}

.admin-btn-secondary:hover {
    background: #5a6268;
}

/* 默认占位符样式 */
.admin-edit-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #6c757d;
    text-align: center;
}

.admin-edit-placeholder p {
    margin: 0;
    font-size: 15px;
}

/* 缩略图编辑区域 */
.thumbnail-edit-section {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.thumbnail-preview {
    position: relative;
    display: inline-block;
}

.thumbnail-delete-hover {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
}

.thumbnail-preview:hover .thumbnail-delete-hover {
    opacity: 1;
}

.thumbnail-delete-hover:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}
</style>

<div class="admin-three-column">
    <!-- 左栏：绿色 -->
    <div class="admin-left-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white position-relative">
                <h6 class="card-title mb-0">
                    <i data-feather="list" class="me-2"></i>[MODULE]顺序
                </h6>
                <button class="add-category-btn" onclick="showAddPanel()">+</button>
            </div>
            <div class="card-body">
                <ul class="admin-category-list list-unstyled mb-0" id="categoryList">
                    <?php foreach ($categoryData as $category): ?>
                    <!-- TEMPLATE PLACEHOLDER: 列表项结构示例 -->
                    <li class="admin-list-item" data-category="<?= htmlspecialchars($category['id']) ?>">
                        <div class="d-flex align-items-center p-3">
                            <span class="admin-drag-handle" title="拖拽排序">⋮⋮</span>

                            <?php if (!empty($category['thumbnail'])): ?>
                                <img src="<?= htmlspecialchars($category['thumbnail']) ?>"
                                     alt="缩略图" class="admin-thumbnail me-2">
                            <?php else: ?>
                                <div class="admin-thumbnail-placeholder me-2">
                                    <i data-feather="image"></i>
                                </div>
                            <?php endif; ?>

                            <div class="flex-grow-1" style="cursor: pointer;"
                                 onclick="editCategory('<?= htmlspecialchars($category['id']) ?>')">
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($category['display_name'] ?? $category['id']) ?>
                                </div>
                                <small class="admin-text-muted">
                                    <?= (int)($category['image_count'] ?? 0) ?> 项
                                </small>
                            </div>

                            <span class="badge bg-secondary" style="opacity: 0.5;">
                                <?= (int)($category['position'] ?? 0) ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- 中栏：蓝色 -->
    <div class="admin-center-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="edit-3" class="me-2"></i>
                    <span id="edit-title">编辑[MODULE]</span>
                </h6>
                <small class="opacity-75">选择左侧项目编辑</small>
            </div>
            <div class="card-body">
                <!-- 默认占位符 -->
                <div id="edit-placeholder" class="admin-edit-placeholder">
                    <i data-feather="arrow-left" style="width: 48px; height: 48px;"></i>
                    <p class="mt-3">点击左侧开始编辑</p>
                </div>

                <!-- TEMPLATE PLACEHOLDER: 添加表单示例 -->
                <div id="add-panel" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">名称 *</label>
                        <input type="text" id="new-name" class="form-control"
                               placeholder="输入名称（英文）">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">显示名称</label>
                            <input type="text" class="form-control"
                                   placeholder="显示给用户的名称">
                        </div>
                        <div class="col-6">
                            <label class="form-label">排序位置</label>
                            <select class="form-select">
                                <option value="first">第一个</option>
                                <option value="last" selected>最后一个</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">描述</label>
                        <textarea class="form-control" rows="2"
                                  placeholder="可选的描述信息"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="admin-btn admin-btn-success" onclick="createItem()">
                            <i data-feather="plus"></i>创建
                        </button>
                        <button class="admin-btn admin-btn-secondary" onclick="cancelAdd()">
                            <i data-feather="x"></i>取消
                        </button>
                    </div>
                </div>

                <!-- TEMPLATE PLACEHOLDER: 编辑表单示例 -->
                <div id="edit-panel" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">显示名称</label>
                        <input type="text" id="edit-name" class="form-control"
                               value="示例分类 1 (占位)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">描述</label>
                        <textarea class="form-control" rows="2" id="edit-description">这是模板占位数据</textarea>
                    </div>

                    <!-- 缩略图编辑 -->
                    <div class="mb-3">
                        <label class="form-label">缩略图</label>
                        <div class="thumbnail-edit-section">
                            <div class="d-flex align-items-center gap-3">
                                <!-- 当前缩略图显示 -->
                                <div class="thumbnail-preview" id="edit-thumbnail-preview">
                                    <img id="edit-thumbnail-img"
                                         src="/assets/images/template/placeholder-1.svg"
                                         style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; border: 2px solid #28a745;">
                                    <!-- hover删除按钮 -->
                                    <button class="thumbnail-delete-hover" onclick="alert('移除缩略图功能 - 占位')" title="移除缩略图">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                                <!-- 上传/更换缩略图按钮 -->
                                <div>
                                    <div class="admin-thumbnail-upload-btn" onclick="alert('上传缩略图功能 - 占位')" title="上传/更换缩略图">
                                        <i data-feather="upload"></i>
                                        <span>上传缩略图</span>
                                    </div>
                                    <input type="file" id="thumbnail-file-input" accept="image/*" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 图片管理示例 -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i data-feather="image"></i>
                            图片管理 (占位示例)
                        </label>
                        <div class="admin-thumbnail-grid-container">
                            <div class="admin-image-item">
                                <img src="/assets/images/template/placeholder-1.svg" alt="示例">
                                <div class="admin-image-actions">
                                    <button class="admin-image-action-btn star">
                                        <i data-feather="star"></i>
                                    </button>
                                    <button class="admin-image-action-btn delete">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="admin-image-item">
                                <img src="/assets/images/template/placeholder-2.svg" alt="示例">
                                <div class="admin-image-actions">
                                    <button class="admin-image-action-btn star">
                                        <i data-feather="star"></i>
                                    </button>
                                    <button class="admin-image-action-btn delete">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="admin-image-item">
                                <img src="/assets/images/template/placeholder-3.svg" alt="示例">
                                <div class="admin-image-actions">
                                    <button class="admin-image-action-btn star">
                                        <i data-feather="star"></i>
                                    </button>
                                    <button class="admin-image-action-btn delete">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="admin-image-upload-btn" onclick="alert('上传图片功能 - 占位')">
                                <i data-feather="plus"></i>
                                <span>上传图片</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="admin-btn admin-btn-primary" onclick="saveItem()">
                            <i data-feather="save"></i>保存
                        </button>
                        <button class="admin-btn admin-btn-danger" onclick="deleteItem()">
                            <i data-feather="trash-2"></i>删除
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 右栏：青色 -->
    <div class="admin-right-panel">
        <!-- 统计信息 -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="eye" class="me-2"></i>统计
                </h6>
            </div>
            <div class="card-body">
                <!-- TEMPLATE PLACEHOLDER: 统计数据示例 -->
                <div class="admin-stat-card mb-3">
                    <h3><?= $totalCategories ?></h3>
                    <p>[MODULE]总数</p>
                    <small class="text-muted" style="font-size: 11px; opacity: 0.7;">(模板占位)</small>
                </div>
                <div class="admin-stat-card"
                     style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                    <h3>99</h3>
                    <p>总项目数</p>
                    <small style="font-size: 11px; opacity: 0.8;">(模板占位)</small>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="zap" class="me-2"></i>快速操作
                </h6>
            </div>
            <div class="card-body">
                <!-- TEMPLATE PLACEHOLDER: 快速操作按钮示例 -->
                <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="location.reload()">
                    <i data-feather="refresh-cw"></i> 刷新数据
                </button>
                <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="alert('API测试功能 - 占位')">
                    <i data-feather="server"></i> API 测试
                </button>
                <button class="btn btn-sm btn-outline-info w-100" onclick="alert('导出功能 - 占位')">
                    <i data-feather="download"></i> 导出配置
                </button>
            </div>
        </div>

        <!-- 帮助信息 -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="help-circle" class="me-2"></i>使用提示
                </h6>
            </div>
            <div class="card-body">
                <!-- TEMPLATE PLACEHOLDER: 使用说明示例 -->
                <small class="admin-text-muted">
                    <p><strong>拖拽排序：</strong>按住⋮⋮图标拖拽列表项</p>
                    <p><strong>编辑项目：</strong>点击列表项进入编辑模式</p>
                    <p><strong>图片管理：</strong>支持上传、删除和设置封面</p>
                    <p class="mb-0"><strong>⚠️ 模板说明：</strong>所有数据均为占位示例</p>
                </small>
            </div>
        </div>
    </div>
</div>

<script src="/assets/admin/js/admin-utils.js?v=2.4"></script>
<script src="/assets/admin/js/admin-drag-sort.js?v=2.4"></script>
<script src="/assets/admin/js/admin-image-manager.js?v=2.4"></script>

<script>
// =============================================================================
// TEMPLATE PLACEHOLDER: JavaScript 功能示例
// =============================================================================

// 全局变量
const controllerUrl = '/admin/ajax?controller=[module]';
let currentEditId = null;

// 初始化拖拽排序（立即执行，不等待DOM）
console.log('========== 模板页面加载 ==========');
console.log('AdminDragSort 可用:', typeof AdminDragSort !== 'undefined');

<?php if (!empty($categoryData)): ?>
const dragSort = new AdminDragSort('categoryList', {
    itemSelector: '.admin-list-item',
    handleSelector: '.admin-drag-handle',
    onReorder: (order) => {
        console.log('==== 拖拽排序触发 ====');
        console.log('新排序:', order);
        console.log('提示：实际使用时请调用 saveSort() 保存到服务器');
        // TEMPLATE: saveSort(order);
    }
});
console.log('✓ 拖拽排序已初始化');
<?php endif; ?>

/**
 * 显示添加面板
 */
function showAddPanel() {
    console.log('显示添加面板');
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('add-panel').style.display = 'block';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('edit-title').textContent = '添加新[MODULE]';
}

/**
 * 取消添加
 */
function cancelAdd() {
    console.log('取消添加');
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-placeholder').style.display = 'flex';
    document.getElementById('edit-title').textContent = '编辑[MODULE]';
}

/**
 * 编辑指定分类
 */
function editCategory(categoryId) {
    currentEditId = categoryId;

    // 高亮当前项
    document.querySelectorAll('.admin-list-item').forEach(item => {
        if (item.dataset.category === categoryId) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // 隐藏占位符和添加面板
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'block';

    // 更新标题
    document.getElementById('edit-title').textContent = '编辑 - ' + categoryId;

    // TEMPLATE PLACEHOLDER: 加载并填充占位数据
    const categoryData = <?= json_encode($categoryData, JSON_UNESCAPED_UNICODE) ?>;
    const category = categoryData.find(c => c.id === categoryId);

    if (category) {
        document.getElementById('edit-name').value = category.display_name || categoryId;
        document.getElementById('edit-description').value = category.description || '';

        if (category.thumbnail) {
            document.getElementById('edit-thumbnail-img').src = category.thumbnail;
        }
    }

    // 重新初始化 Feather Icons
    setTimeout(() => {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 50);
}

/**
 * 创建新项目（占位函数）
 */
function createItem() {
    const name = document.getElementById('new-name').value;
    if (!name) {
        alert('请输入名称');
        return;
    }

    // TEMPLATE: 发送 AJAX 请求
    console.log('创建新项目:', name);
    alert('创建功能 - 占位\n名称: ' + name);

    // 创建成功后刷新页面
    // location.reload();
}

/**
 * 保存当前项目（占位函数）
 */
function saveItem() {
    if (!currentEditId) {
        alert('未选中项目');
        return;
    }

    // TEMPLATE: 发送 AJAX 请求
    console.log('保存项目:', currentEditId);
    alert('保存功能 - 占位\nID: ' + currentEditId);

    // 保存成功后刷新页面
    // location.reload();
}

/**
 * 删除当前项目（占位函数）
 */
function deleteItem() {
    if (!currentEditId) {
        alert('未选中项目');
        return;
    }

    if (!confirm('确定要删除这个项目吗？')) {
        return;
    }

    // TEMPLATE: 发送 AJAX 请求
    console.log('删除项目:', currentEditId);
    alert('删除功能 - 占位\nID: ' + currentEditId);

    // 删除成功后刷新页面
    // location.reload();
}

/**
 * 保存排序（占位函数）
 */
function saveSort() {
    const items = document.querySelectorAll('.admin-list-item');
    const order = Array.from(items).map((item, index) => {
        return {
            id: item.dataset.category,
            position: index + 1
        };
    });

    console.log('保存排序:', order);
    alert('排序功能 - 占位\n共 ' + order.length + ' 项');

    // TEMPLATE: 发送 AJAX 请求
    // fetch('/admin/ajax?controller=[module]&ajax=save-sort', { ... })
}


// 初始化 Feather Icons - 延迟到页面完全加载后
window.addEventListener('load', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
        console.log(' Feather Icons 已初始化');
    }
});

console.log('========== 模板页面已加载 ==========');
console.log('AdminDragSort:', typeof AdminDragSort !== 'undefined' ? '' : '');
console.log('editCategory:', typeof editCategory !== 'undefined' ? '' : '');
<?php if (!empty($categoryData)): ?>
console.log(' 当前显示的是模板占位数据，共 <?= count($categoryData) ?> 项');
<?php endif; ?>
</script>
