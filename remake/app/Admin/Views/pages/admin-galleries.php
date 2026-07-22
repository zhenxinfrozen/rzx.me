<?php
/**
 * Galleries 画廊管理页面 - 新版本
 * 使用标准化组件和三栏布局
 *
 * @version 2.5.6
 * @date 2026-01-22
 *
 * v2.5.6 更新日志:
 * - 统一：feather icons初始化逻辑，使用window.addEventListener('load')和setTimeout
 * - 统一：与drafts-new保持一致的架构标准
 * - 统一：CSS版本号改为v=1.0.0（与组件库同步）
 *
 * v2.5.5 更新日志:
 * - 修复：拖拽排序数据格式错误（order是对象数组而非DOM元素数组）
 * - 增强：添加详细的调试日志输出
 *
 * v2.5.4 更新日志:
 * - 修复：移动列表排序功能（添加save_category_order API）
 * - 修复：前端调用改为使用fetch+JSON格式
 * - 数据存储：app/storage/data/galleries-sort.json
 *
 * v2.5.3 更新日志:
 * - 优化：缩略图删除按钮移到右上角
 * - 优化：当前缩略图只长驻显示星标，hover后显示所有按钮
 * - 修复：星标活跃状态使用深色背景(#ff9800)，避免与黄色图标重复
 * - 优化：删除重复的提示文字，保留JS动态生成的那行
 *
 * v2.5.2 更新日志:
 * - 修复：设置缩略图功能（增强错误处理和日志）
 * - 优化：CSS样式，操作按钮移到右上角竖直排列
 * - 移除：移动按钮（拖拽功能已满足需求）
 * - 改进：当前缩略图始终显示星标，hover时显示其他操作
 *
 * v2.5.1 更新日志:
 * - 修复：缩略图扩展名不匹配问题（动态查找任意格式的缩略图文件）
 * - 改进：getCategoryThumbnailInfo 函数现在会验证配置中的缩略图是否存在
 * - 改进：如果配置的缩略图文件不存在，自动查找同名但不同扩展名的文件
 *
 * v2.5.0 更新日志:
 * - 修复：显示名称参数名称错误(display_name → displayName)，现在能正确保存
 * - 新增：文件夹重命名功能，支持修改category文件夹名
 * - 新增：后端自动处理文件夹重命名和配置更新
 * - 改进：前端验证文件夹名格式（仅允许英文、数字、下划线和短横线）
 */

$page_title = $page_title ?? '�️ Galleries 画廊管理';
$page_subtitle = $page_subtitle ?? '管理前台 galleries 页面显示的画廊集合';
$_GET['page'] = $_GET['page'] ?? 'galleries';

// 加载 GalleryManager
require_once __DIR__ . '/../../../Utils/GalleryManager.php';
$galleryManager = new GalleryManager();

$thumbnailConfig = [];
$thumbnailConfigFile = __DIR__ . '/../../../storage/data/galleries-sort.json';
if (file_exists($thumbnailConfigFile)) {
    $loadedConfig = json_decode(file_get_contents($thumbnailConfigFile), true);
    if (is_array($loadedConfig)) {
        $thumbnailConfig = $loadedConfig;
    }
}

// 加载数据
if (!isset($categoryData)) {
    $galleries = $galleryManager->scanGalleries();
    $categoryData = [];
    foreach ($galleries as $gallery) {
        $customThumbnail = $thumbnailConfig['category_thumbnails'][$gallery['name']] ?? null;
        $customThumbnailPath = $customThumbnail ? __DIR__ . '/../../../public' . $customThumbnail : null;

        $debugInfo = "None";
        if ($customThumbnail) {
            // 只保留基本无效检查，不再强制检查文件是否存在
            // 这样可以避免因路径解析问题导致配置失效
            if (!$customThumbnailPath || $customThumbnail === '') {
                $customThumbnail = null;
            }
        }

        $categoryData[] = [
            'id' => $gallery['name'],
            'display_name' => $gallery['display_name'],
            'description' => $gallery['description'],
            'thumbnail' => $customThumbnail ?: ($gallery['icon_default'] ?? '/assets/images/404.jpg'),
            'thumbnail_debug' => $debugInfo,
            'first_image_thumb' => $gallery['icon_default'],
            'image_count' => $gallery['image_count'],
            'position' => $gallery['position'],
        ];
    }
    usort($categoryData, function($a, $b) {
        return $a['position'] - $b['position'];
    });
}

$categoryData = $categoryData ?? [];
$currentConfig = $currentConfig ?? [
    'sort_method' => 'custom_order',
    'custom_order' => [],
    'prefix_settings' => ['remove_prefix' => true, 'separator' => '-'],
    'display_names' => [],
    'descriptions' => [],
];
$flashMessage = $flashMessage ?? null;
$totalCategories = count($categoryData);
?>

<!-- 引入新组件样式 -->
<link rel="stylesheet" href="/assets/admin/css/admin-common.css?v=1.0.0">
<link rel="stylesheet" href="/assets/admin/css/admin-three-column.css?v=1.0.0">
<link rel="stylesheet" href="/assets/admin/css/admin-image-manager.css?v=1.0.0">

<!-- 自定义样式（覆盖和补充） -->
<style>
/* 添加按钮样式（参考旧版本） */
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
    font-weight: bold;
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

/* 缩略图编辑区域 */
.thumbnail-edit-section {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

/* 调整图片管理操作按钮样式 */
.admin-image-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.admin-image-item:hover .admin-image-actions {
    opacity: 1;
}

/* 当前缩略图只显示星标按钮 */
.admin-image-item.is-thumbnail .admin-image-action-btn.star {
    opacity: 1;
}

/* hover时显示所有按钮 */
.admin-image-item.is-thumbnail:hover .admin-image-actions {
    opacity: 1;
}

.admin-image-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.admin-image-action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

.admin-image-action-btn.star {
    color: #ffc107;
}

/* 活跃状态使用深色背景，避免与黄色图标重复 */
.admin-image-action-btn.star.active {
    background: #ff9800;
    color: white;
}

.admin-image-action-btn.delete {
    color: #dc3545;
}

.admin-image-action-btn.delete:hover {
    background: #dc3545;
    color: white;
}

.thumbnail-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    background: #f8f9fa;
}

/* hover删除按钮 - 右上角 */
.thumbnail-delete-hover {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(220, 53, 69, 0.95);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.thumbnail-preview:hover .thumbnail-delete-hover {
    opacity: 1;
}

.thumbnail-delete-hover:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.admin-thumbnail-upload-btn {
    width: 120px;
    height: 120px;
    border: 2px dashed #007bff;
    border-radius: 8px;
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
    gap: 8px;
}

.admin-thumbnail-upload-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.02);
}

.admin-thumbnail-upload-btn i {
    width: 24px;
    height: 24px;
}

/* 图片网格对齐 - 居中表格布局 */
.admin-image-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
    align-items: flex-start;
    justify-content: flex-start;
}

.admin-add-image-btn {
    width: 80px;
    height: 80px;
    border: 2px dashed #007bff;
    border-radius: 6px;
    background: #f8f9fa;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 24px;
    font-weight: bold;
    flex-shrink: 0;
    margin: 0;
}

.admin-add-image-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.05);
}

/* 图片项样式增强 */
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
    margin: 0;
}

.admin-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.admin-image-item.is-thumbnail {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40,167,69,.25);
}

/* 操作按钮（参考旧版本） */
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
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    padding: 0;
}

.admin-image-action-btn:hover {
    transform: scale(1.15);
}

.admin-image-action-btn svg {
    width: 12px;
    height: 12px;
}

.admin-image-action-btn.star {
    color: #6c757d;
}

.admin-image-action-btn.star.active,
.admin-image-action-btn.star:hover {
    color: #ffc107;
}

.admin-image-action-btn.move {
    color: #007bff;
    cursor: move;
}

.admin-image-action-btn.delete {
    color: #dc3545;
}
</style>

<!-- 消息提示 -->
<?php if ($flashMessage): ?>
<div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
    <i data-feather="<?= $flashMessage['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>"></i>
    <?= htmlspecialchars($flashMessage['text']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>


<!-- 三栏布局 -->
<div class="admin-three-column">

    <!-- 左栏：画廊列表 -->
    <div class="admin-left-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white position-relative">
                <h6 class="card-title mb-0">
                    <i data-feather="list" class="me-2"></i>
                    画廊顺序
                </h6>
                <button type="button" class="add-category-btn" onclick="showAddPanel()" title="添加新画廊">+</button>
            </div>
            <div class="card-body">
                <ul class="admin-category-list list-unstyled mb-0" id="categoryList">
                    <?php foreach ($categoryData as $category): ?>
                    <li class="admin-list-item"
                        data-category="<?= htmlspecialchars($category['id']) ?>"
                        data-debug-thumb="<?= htmlspecialchars($category['thumbnail_debug'] ?? 'N/A') ?>"
                        title="Debug: <?= htmlspecialchars($category['thumbnail_debug'] ?? 'N/A') ?>">
                        <div class="d-flex align-items-center p-3">
                            <span class="admin-drag-handle" title="拖拽排序">⋮⋮</span>

                            <!-- 缩略图 -->
                            <?php
                            $thumbnailSrc = '';
                            if (!empty($category['thumbnail'])) {
                                $thumbnailSrc = $category['thumbnail'];
                            } elseif (!empty($category['first_image_thumb'])) {
                                $thumbnailSrc = $category['first_image_thumb'];
                            }
                            ?>
                            <?php if ($thumbnailSrc): ?>
                                <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="缩略图" class="admin-thumbnail me-2">
                            <?php else: ?>
                                <div class="admin-thumbnail-placeholder me-2">
                                    <i data-feather="image"></i>
                                </div>
                            <?php endif; ?>

                            <!-- 信息 -->
                            <div class="flex-grow-1" style="cursor: pointer;" onclick="editCategory('<?= htmlspecialchars($category['id']) ?>')">
                                <div class="fw-semibold"><?= htmlspecialchars($category['display_name'] ?: $category['id']) ?></div>
                                <?php
                                $descText = trim($category['description'] ?? '');
                                if ($descText === '') {
                                    $descText = '【占位-描述信息】';
                                }
                                ?>
                                <small class="admin-text-muted"><?= htmlspecialchars($descText) ?></small>
                            </div>

                            <span class="admin-badge admin-badge-primary"><?= (int) $category['image_count'] ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- 中栏：编辑区域 -->
    <div class="admin-center-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="edit-3" class="me-2" id="edit-icon"></i>
                    <span id="edit-title">编辑画廊</span>
                </h6>
                <small id="edit-status" class="opacity-75">选择左侧画廊进行编辑</small>
            </div>
            <div class="card-body">

                <!-- 占位符 -->
                <div id="edit-placeholder" class="admin-edit-placeholder">
                    <i data-feather="arrow-left" style="width: 48px; height: 48px;"></i>
                    <p class="mt-3">点击左侧画廊开始编辑</p>
                </div>

                <!-- 添加面板 -->
                <div id="add-panel" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">新画廊名称</label>
                        <input type="text" id="new-category-name" class="form-control" placeholder="输入画廊名称（英文）">
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
                        <textarea id="new-description" class="form-control" rows="2" placeholder="可选的描述"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="admin-btn admin-btn-success" onclick="createCategory()">
                            <i data-feather="plus"></i>创建画廊
                        </button>
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="cancelAdd()">
                            <i data-feather="x"></i>取消
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
                            <label class="form-label text-muted">文件夹名 <small class="text-danger">(修改会重命名文件夹)</small></label>
                            <input type="text" id="edit-folder-name" class="form-control" placeholder="英文名称">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">描述信息</label>
                        <textarea id="edit-description" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- 缩略图编辑 -->
                    <div class="mb-3">
                        <label class="form-label">缩略图</label>
                        <div class="thumbnail-edit-section">
                            <div class="d-flex align-items-center gap-3">
                                <!-- 当前缩略图显示 -->
                                <div class="thumbnail-preview" id="edit-thumbnail-preview" style="position: relative;">
                                    <img id="edit-thumbnail-img" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; border: 2px solid #28a745; display: none;">
                                    <!-- hover删除按钮 -->
                                    <button class="thumbnail-delete-hover" id="thumbnail-delete-hover" onclick="removeThumbnail()" style="display: none;" title="移除缩略图">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                                <!-- 上传/更换缩略图按钮 -->
                                <div>
                                    <div class="admin-thumbnail-upload-btn" id="edit-thumbnail-upload" onclick="selectThumbnailFile()" title="上传/更换缩略图">
                                        <i data-feather="upload"></i>
                                        <span>上传缩略图</span>
                                    </div>
                                    <!-- 隐藏的文件输入 -->
                                    <input type="file" id="thumbnail-file-input" accept="image/*" style="display: none;" onchange="handleThumbnailUpload(event)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 图片管理 -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i data-feather="image"></i>
                            图片管理
                        </label>
                        <div id="imageManager" class="admin-thumbnail-grid-container">
                            <!-- 由 JS 动态生成 -->
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="admin-btn admin-btn-primary" onclick="saveCategory()">
                            <i data-feather="save"></i>更新
                        </button>
                        <button type="button" class="admin-btn admin-btn-danger" onclick="deleteCategory()">
                            <i data-feather="trash-2"></i>删除
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 右栏：预览和工具 -->
    <div class="admin-right-panel">

        <!-- 统计信息 -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="eye" class="me-2"></i>
                    预览
                </h6>
            </div>
            <div class="card-body">
                <div class="admin-stat-card">
                    <h3><?= $totalCategories ?></h3>
                    <p>画廊总数</p>
                </div>

                <div class="admin-stat-card" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                    <h3 id="total-images">0</h3>
                    <p>总图片数</p>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="zap" class="me-2"></i>
                    快速操作
                </h6>
            </div>
            <div class="card-body">
                <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="refreshData()">
                    <i data-feather="refresh-cw"></i> 刷新数据
                </button>
                <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="testAPI()">
                    <i data-feather="server"></i> API 测试
                </button>
                <button class="btn btn-sm btn-outline-info w-100" onclick="exportData()">
                    <i data-feather="download"></i> 导出配置
                </button>
            </div>
        </div>

        <!-- 帮助信息 -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="help-circle" class="me-2"></i>
                    使用提示
                </h6>
            </div>
            <div class="card-body">
                <small class="admin-text-muted">
                    <p><strong>拖拽排序：</strong>按住⋮⋮拖拽列表项调整顺序</p>
                    <p><strong>设置封面：</strong>点击图片上的星标按钮</p>
                    <p><strong>上传图片：</strong>点击 + 号选择图片文件</p>
                </small>
            </div>
        </div>

    </div>

</div>



<!-- 版本提示 -->
<div class="alert alert-info alert-dismissible fade show">
    <i data-feather="info"></i>
    <strong>新版本 v2.5.5</strong> - ✓ 修复拖拽排序 ✓ 增强调试日志 ✓ 开发者工具(F12)查看。
    <a href="/admin?page=Galleries" class="alert-link">返回旧版本</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- 引入新组件 JS -->
<script src="/assets/admin/js/admin-utils.js?v=2.4"></script>
<script src="/assets/admin/js/admin-drag-sort.js?v=2.4"></script>
<script src="/assets/admin/js/admin-image-manager.js?v=2.4"></script>

<script>
// 全局变量
const controllerUrl = '/admin/ajax?controller=galleries';
const existingMeta = <?= json_encode($categoryData, JSON_UNESCAPED_UNICODE) ?>;

// 当前编辑的分类
let currentCategory = null;
let imageManager = null;

// 初始化拖拽排序
console.log('初始化AdminDragSort...');
console.log('AdminDragSort可用:', typeof AdminDragSort !== 'undefined');

const dragSort = new AdminDragSort('categoryList', {
    itemSelector: '.admin-list-item',
    handleSelector: '.admin-drag-handle',
    onReorder: (order) => {
        console.log('==== 拖拽排序触发 ====');
        console.log('新排序:', order);
        console.log('order类型:', typeof order);
        console.log('order长度:', order.length);
        saveOrder(order);
    }
});

console.log('AdminDragSort实例:', dragSort);

// 显示添加面板
function showAddPanel() {
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('add-panel').style.display = 'block';

    document.getElementById('edit-title').textContent = '添加新画廊';
    document.getElementById('edit-status').textContent = '创建新的画廊';

    // 清空输入
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';
}

// 取消添加
function cancelAdd() {
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-placeholder').style.display = 'flex';

    document.getElementById('edit-title').textContent = '编辑画廊';
    document.getElementById('edit-status').textContent = '选择左侧画廊进行编辑';
}

// 编辑分类
function editCategory(categoryId) {
    currentCategory = categoryId;

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

    // 更新标题（立即从列表数据获取）
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    const displayName = listItem ? listItem.querySelector('.fw-semibold').textContent : categoryId;

    document.getElementById('edit-title').textContent = displayName;
    document.getElementById('edit-status').textContent = '加载中...';
    document.getElementById('edit-display-name').value = displayName;
    document.getElementById('edit-folder-name').value = categoryId;

    // 使用通用 media-manager 控制器
    const controllerUrl = '/admin/ajax?controller=media-manager&module=galleries';

    // 先加载缩略图列表
    fetch(`${controllerUrl}&ajax=thumbnails&category=${encodeURIComponent(categoryId)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit-status').textContent = `${data.images.length} 张图片`;

                // 转换为新格式并初始化图片管理器
                const images = data.images.map((img, index) => ({
                    id: img.name,
                    name: img.name,
                    path: img.path,
                    thumb_path: img.thumb_path,
                    is_thumbnail: data.current_thumbnail &&
                                (img.thumb_name === data.current_thumbnail || img.name === data.current_thumbnail),
                    position: index
                }));

                initImageManager(images, data.current_thumbnail);
            } else {
                document.getElementById('edit-status').textContent = '0 张图片';
                initImageManager([]);
            }
        })
        .catch(err => {
            console.error('加载图片失败:', err);
            AdminUtils.showMessage('加载图片失败', 'error');
            document.getElementById('edit-status').textContent = '加载失败';
            initImageManager([]);
        });

    // 加载分类元数据（描述等）
    const metaItem = existingMeta.find(cat => cat.id === categoryId);
    if (metaItem) {
        document.getElementById('edit-description').value = metaItem.description || '';
    }

    // 加载分类缩略图
    loadCategoryThumbnail(categoryId);
}

// 初始化图片管理器
function initImageManager(images, currentThumbnail) {
    const container = document.getElementById('imageManager');
    container.innerHTML = '';

    // 简化版图片展示
    const grid = document.createElement('div');
    grid.className = 'admin-image-grid';
    grid.id = 'image-grid-' + Date.now(); // 唯一ID

    // 添加按钮
    const addBtn = document.createElement('div');
    addBtn.className = 'admin-add-image-btn';
    addBtn.innerHTML = '+';
    addBtn.title = '上传图片';
    addBtn.onclick = selectImages;
    grid.appendChild(addBtn);

    if (images.length === 0) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'admin-empty-state';
        emptyDiv.innerHTML = '<p>暂无图片，点击 + 上传</p>';
        grid.appendChild(emptyDiv);
    }

    // 渲染图片
    images.forEach((img, index) => {
        const item = document.createElement('div');
        item.className = 'admin-image-item';
        if (img.is_thumbnail) item.classList.add('is-thumbnail');
        item.dataset.imageId = img.id || img.name;
        item.dataset.imageName = img.name;
        item.draggable = true;

        const imgEl = document.createElement('img');
        imgEl.src = img.thumb_path || img.path;
        imgEl.alt = img.name;
        imgEl.loading = 'lazy';
        item.appendChild(imgEl);

        // 操作按钮（右上角竖直排列）
        const actions = document.createElement('div');
        actions.className = 'admin-image-actions';

        // 星标按钮
        const starBtn = document.createElement('button');
        starBtn.className = 'admin-image-action-btn star' + (img.is_thumbnail ? ' active' : '');
        starBtn.innerHTML = '<i data-feather="star"></i>';
        starBtn.title = img.is_thumbnail ? '当前封面' : '设为封面';
        starBtn.onclick = (e) => {
            e.stopPropagation();
            console.log('点击星标:', img.name, img.thumb_name);
            setThumbnail(img.name, img.thumb_name || img.name);
        };
        actions.appendChild(starBtn);

        // 删除按钮
        const delBtn = document.createElement('button');
        delBtn.className = 'admin-image-action-btn delete';
        delBtn.innerHTML = '<i data-feather="trash-2"></i>';
        delBtn.title = '删除图片';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            deleteImage(img.name);
        };
        actions.appendChild(delBtn);

        item.appendChild(actions);
        grid.appendChild(item);
    });

    // 添加提示信息
    if (images.length > 0) {
        const hint = document.createElement('small');
        hint.className = 'admin-text-muted mt-2 d-block';
        hint.textContent = `共 ${images.length} 张图片。拖拽图片可调整顺序，点击星标设为封面。`;
        grid.appendChild(hint);
    }

    container.appendChild(grid);

    // 重新激活Feather图标 - 延迟执行确保DOM更新完成
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 50);

    // 初始化图片拖拽排序
    if (images.length > 1) {
        // 稍微延迟以确保omready
        setTimeout(() => {
            new AdminImageDragSort(grid.id, {
                itemSelector: '.admin-image-item',
                onReorder: (order) => {
                    console.log('图片排序已更改:', order);
                    saveImageOrder(order);
                }
            });
        }, 100);
    }

    // 刷新 feather icons - 延迟执行
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 50);
}

// 选择图片
function selectImages() {
    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.accept = 'image/*';
    input.onchange = (e) => {
        const files = Array.from(e.target.files);
        uploadImages(files);
    };
    input.click();
}

// 上传图片
function uploadImages(files) {
    if (!currentCategory) {
        AdminUtils.showMessage('请先选择一个画廊', 'warning');
        return;
    }

    if (!files || files.length === 0) return;

    const formData = new FormData();
    formData.append('category', currentCategory);
    Array.from(files).forEach(file => formData.append('images[]', file));

    const totalSize = (Array.from(files).reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(1);
    AdminUtils.showMessage(`正在上传 ${files.length} 张图片 (${totalSize}MB)...`, 'info');

    const controllerUrl = '/admin/ajax?controller=media-manager&module=galleries';

    fetch(`${controllerUrl}&ajax=upload_images`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage(data.message || '上传成功', 'success');

            // 重新加载图片列表
            editCategory(currentCategory);

            // 更新列表中的图片数量
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (listItem && data.total_count) {
                const countEl = listItem.querySelector('.admin-text-muted');
                if (countEl) {
                    countEl.textContent = `${data.total_count} 张图片`;
                }
            }

            // 如果自动设置了缩略图，更新显示
            if ((data.auto_set_thumbnail || data.thumbnail_set) && data.thumbnail_url) {
                const editImg = document.getElementById('edit-thumbnail-img');
                const listImg = listItem?.querySelector('img');
                if (editImg) {
                    editImg.src = data.thumbnail_url + '?t=' + Date.now();
                    editImg.style.display = 'block';
                }
                if (listImg) {
                    listImg.src = data.thumbnail_url + '?t=' + Date.now();
                }
            }
        } else {
            AdminUtils.showMessage(data.message || '上传失败', 'error');
        }
    })
    .catch(err => {
        console.error('上传失败:', err);
        AdminUtils.showMessage('上传失败。如果文件较大，请检查PHP配置', 'error');
    });
}

// 设置封面
function setThumbnail(imageName, thumbName) {
    if (!currentCategory) return;

    console.log('设置缩略图:', { category: currentCategory, image: imageName, thumb: thumbName });

    const controllerUrl = '/admin/ajax?controller=media-manager&module=galleries';

    // 使用旧版本的 API - 注意参数名是 thumb 不是 thumb_name
    fetch(`${controllerUrl}&ajax=set_thumbnail`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            image: imageName,
            thumb: thumbName || imageName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('已设为封面图', 'success');

            // 1. 更新编辑区域的12 0x120缩略图预览
            const editImg = document.getElementById('edit-thumbnail-img');
            const editPreview = document.getElementById('edit-thumbnail-preview');
            if (editImg && data.thumbnail_url) {
                editImg.src = data.thumbnail_url + '?t=' + Date.now();
                editImg.style.display = 'block';
                // 移除placeholder
                const placeholder = editPreview.querySelector('span');
                if (placeholder) placeholder.remove();
            }

            // 2. 更新左侧列表的44x44缩略图
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (listItem && data.thumbnail_url) {
                const listImg = listItem.querySelector('img');
                if (listImg) {
                    listImg.src = data.thumbnail_url + '?t=' + Date.now();
                }
            }

            // 3. 更新图片管理区域的星标状态（不重新加载）
            const imageItems = document.querySelectorAll('.admin-image-item');
            imageItems.forEach(item => {
                const itemName = item.dataset.imageName;
                const starBtn = item.querySelector('.admin-image-action-btn.star');
                if (itemName === imageName) {
                    // 当前图片设为活跃
                    item.classList.add('is-thumbnail');
                    if (starBtn) {
                        starBtn.classList.add('active');
                        starBtn.title = '当前封面';
                    }
                } else {
                    // 其他图片移除活跃状态
                    item.classList.remove('is-thumbnail');
                    if (starBtn) {
                        starBtn.classList.remove('active');
                        starBtn.title = '设为封面';
                    }
                }
            });
        } else {
            AdminUtils.showMessage(data.message || '设置失败', 'error');
        }
    })
    .catch(err => {
        console.error('设置封面失败:', err);
        AdminUtils.showMessage('设置封面失败', 'error');
    });
}

// 删除图片
function deleteImage(imageName) {
    if (!currentCategory) return;

    if (!confirm(`确定要删除图片 "${imageName}" 吗？`)) {
        return;
    }

    const controllerUrl = '/admin/ajax?controller=galleries';

    fetch(`${controllerUrl}&ajax=delete_image`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            image: imageName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('图片已删除', 'success');
            // 重新加载图片列表
            editCategory(currentCategory);

            // 更新列表中的图片计数
            updateImageCount(currentCategory);
        } else {
            AdminUtils.showMessage(data.message || '删除失败', 'error');
        }
    })
    .catch(err => {
        console.error('删除失败:', err);
        AdminUtils.showMessage('删除图片失败', 'error');
    });
}

// 更新图片计数
function updateImageCount(categoryId) {
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    if (listItem) {
        const countElement = listItem.querySelector('.admin-text-muted');
        if (countElement) {
            const currentText = countElement.textContent;
            const match = currentText.match(/(\d+)/);
            if (match) {
                const count = Math.max(0, parseInt(match[1]) - 1);
                countElement.textContent = `${count} 张图片`;
            }
        }
    }
}

// 保存图片排序
function saveImageOrder(orderData) {
    if (!currentCategory) return;

    const controllerUrl = '/admin/ajax?controller=galleries';

    // 将对象数组转换为字符串数组（按顺序排列的图片名）
    const imageOrder = Array.isArray(orderData)
        ? orderData.map(item => item.id || item)
        : orderData;

    fetch(`${controllerUrl}&ajax=reorder_images`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            order: imageOrder
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('排序已保存', 'success');
        } else {
            console.warn('排序保存失败:', data.message);
            AdminUtils.showMessage(data.message || '排序保存失败', 'warning');
        }
    })
    .catch(err => {
        console.error('排序保存错误:', err);
        AdminUtils.showMessage('排序保存失败', 'error');
    });
}

// 保存排序
function saveOrder(order) {
    console.log('==== saveOrder被调用 ====');
    console.log('接收到的order:', order);

    const controllerUrl = '/admin/ajax?controller=galleries';

    // order 是对象数组 [{id: 'xxx', position: 1}, ...]
    // 提取所有的 id 并组成逗号分隔的字符串
    const categoryOrder = order.map(item => {
        console.log('处理item:', item);
        return item.id;
    }).join(',');

    console.log('保存分类排序:', categoryOrder);
    console.log('请求URL:', `${controllerUrl}&ajax=save_category_order`);

    fetch(`${controllerUrl}&ajax=save_category_order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category_order: categoryOrder
        })
    })
    .then(res => {
        console.log('收到响应:', res);
        return res.json();
    })
    .then(data => {
        console.log('响应数据:', data);
        if (data.success) {
            AdminUtils.showMessage('排序已保存', 'success');
        } else {
            AdminUtils.showMessage(data.message || '保存失败', 'error');
        }
    })
    .catch(err => {
        console.error('保存排序失败:', err);
        AdminUtils.showMessage('保存失败', 'error');
    });
}

// 创建分类
function createCategory() {
    const name = document.getElementById('new-category-name').value.trim();
    const displayName = document.getElementById('new-display-name').value.trim();
    const description = document.getElementById('new-description').value.trim();

    if (!name) {
        AdminUtils.showMessage('请输入画廊名称', 'warning');
        return;
    }

    AdminUtils.showMessage('创建功能待实现', 'info');
}

// 保存分类
function saveCategory() {
    if (!currentCategory) return;

    const displayName = document.getElementById('edit-display-name').value.trim();
    const folderName = document.getElementById('edit-folder-name').value.trim();
    const description = document.getElementById('edit-description').value.trim();

    // 检查文件夹名是否有效
    if (folderName && !/^[a-zA-Z0-9_-]+$/.test(folderName)) {
        AdminUtils.showMessage('文件夹名只能包含英文、数字、下划线和短横线', 'error');
        return;
    }

    const willRename = folderName && folderName !== currentCategory;
    const confirmMsg = willRename
        ? `确定要保存并将文件夹从 "${currentCategory}" 重命名为 "${folderName}" 吗？`
        : `确定要保存对画廊 "${currentCategory}" 的修改吗？`;

    if (!confirm(confirmMsg)) {
        return;
    }

    const controllerUrl = '/admin/ajax?controller=galleries';

    // 使用驼峰式参数名（与后端一致）
    fetch(`${controllerUrl}&ajax=save_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            displayName: displayName,
            description: description,
            newFolderName: willRename ? folderName : null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('画廊信息已保存', 'success');

            // 如果重命名了文件夹
            if (data.renamed && data.newCategory) {
                const oldCategory = currentCategory;
                currentCategory = data.newCategory;

                // 更新existingMeta
                const metaIndex = existingMeta.findIndex(cat => cat.id === oldCategory);
                if (metaIndex >= 0) {
                    existingMeta[metaIndex].id = data.newCategory;
                    existingMeta[metaIndex].display_name = displayName;
                    existingMeta[metaIndex].description = description;
                }

                // 更新列表项
                const listItem = document.querySelector(`.admin-list-item[data-category="${oldCategory}"]`);
                if (listItem) {
                    listItem.dataset.category = data.newCategory;
                    const nameEl = listItem.querySelector('.fw-semibold');
                    if (nameEl) nameEl.textContent = displayName || data.newCategory;
                }

                // 更新编辑面板
                document.getElementById('edit-title').textContent = displayName || data.newCategory;
                document.getElementById('edit-folder-name').value = data.newCategory;

                AdminUtils.showMessage(`文件夹已重命名为: ${data.newCategory}`, 'success');
            } else {
                // 更新existingMeta中的数据，防止重新加载时被重置
                const metaItem = existingMeta.find(cat => cat.id === currentCategory);
                if (metaItem) {
                    metaItem.display_name = displayName;
                    metaItem.description = description;
                }

                // 更新列表中的显示名称
                updateCategoryInList(currentCategory, displayName);

                // 更新编辑面板标题
                document.getElementById('edit-title').textContent = displayName || currentCategory;
            }
        } else {
            AdminUtils.showMessage(data.message || '保存失败', 'error');
        }
    })
    .catch(err => {
        console.error('保存失败:', err);
        AdminUtils.showMessage('保存失败', 'error');
    });
}

// 更新列表中的分类信息
function updateCategoryInList(categoryId, displayName) {
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    if (listItem) {
        const nameElement = listItem.querySelector('.fw-semibold');
        if (nameElement) {
            nameElement.textContent = displayName || categoryId;
        }
    }
}

// 获取当前分类排序
function getCurrentCategoryOrder() {
    const items = document.querySelectorAll('.admin-list-item');
    return Array.from(items).map((item, index) => ({
        id: item.dataset.category,
        position: index + 1
    }));
}

// 删除分类
function deleteCategory() {
    if (!currentCategory) return;

    if (!confirm(`确定要删除画廊 "${currentCategory}" 吗？\n这将删除该画廊下的所有图片文件。`)) {
        return;
    }

    const controllerUrl = '/admin/ajax?controller=media-manager&module=galleries';

    fetch(`${controllerUrl}&ajax=delete_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 从列表中移除
            const item = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (item) item.remove();

            // 返回初始状态
            document.getElementById('edit-panel').style.display = 'none';
            document.getElementById('edit-placeholder').style.display = 'flex';
            currentCategory = null;

            AdminUtils.showMessage('画廊已删除', 'success');

            // 更新统计
            updateTotalCount();
        } else {
            AdminUtils.showMessage(data.message || '删除失败', 'error');
        }
    })
    .catch(err => {
        console.error('删除失败:', err);
        AdminUtils.showMessage('删除失败', 'error');
    });
}

// 更新统计总数
function updateTotalCount() {
    const totalCategories = document.querySelectorAll('.admin-list-item').length;
    const statCard = document.querySelector('.admin-stat-card h3');
    if (statCard) {
        statCard.textContent = totalCategories;
    }
}

// 快速操作
function refreshData() {
    location.reload();
}

function testAPI() {
    AdminUtils.showMessage('API 测试：连接正常', 'success');
}

function exportData() {
    AdminUtils.showMessage('导出功能待实现', 'info');
}

// 缩略图编辑功能
function selectThumbnailFile() {
    document.getElementById('thumbnail-file-input')?.click();
}

function handleThumbnailUpload(event) {
    const file = event.target.files[0];
    if (!file || !currentCategory) return;

    // 验证文件类型
    if (!file.type.startsWith('image/')) {
        AdminUtils.showMessage('请选择图片文件', 'error');
        return;
    }

    // 创建FormData
    const formData = new FormData();
    formData.append('thumbnail', file);
    formData.append('category', currentCategory);

    AdminUtils.showMessage('正在上传缩略图...', 'info');

    // 上传到服务器
    fetch('/admin/ajax?controller=media-manager&module=galleries&ajax=upload_thumbnail', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('缩略图上传成功', 'success');
            // 更新预览
            const img = document.getElementById('edit-thumbnail-img');
            const preview = document.getElementById('edit-thumbnail-preview');
            const deleteBtn = document.getElementById('thumbnail-delete-hover');

            if (img && data.thumbnail_url) {
                img.src = data.thumbnail_url + '?t=' + Date.now();
                img.style.display = 'block';
                preview.querySelector('span')?.remove();
            }
            // 显示hover删除按钮
            if (deleteBtn) deleteBtn.style.display = 'block';

            // 更新左侧列表缩略图
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"] img`);
            if (listItem && data.thumbnail_url) {
                listItem.src = data.thumbnail_url + '?t=' + Date.now();
            }

            // 重新激活feather图标 - 延迟执行
            setTimeout(function() {
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }, 50);
        } else {
            AdminUtils.showMessage(data.message || '上传失败', 'error');
        }
    })
    .catch(err => {
        console.error('上传失败:', err);
        AdminUtils.showMessage('上传失败', 'error');
    })
    .finally(() => {
        // 清空input以允许重复上传同一文件
        event.target.value = '';
    });
}

function removeThumbnail() {
    if (!currentCategory) return;

    if (!confirm('确定要移除当前缩略图吗？')) return;

    fetch('/admin/ajax?controller=media-manager&module=galleries&ajax=delete_thumbnail', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: currentCategory })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('缩略图已移除', 'success');
            // 清除预览
            const img = document.getElementById('edit-thumbnail-img');
            const preview = document.getElementById('edit-thumbnail-preview');
            const deleteBtn = document.getElementById('thumbnail-delete-hover');

            if (img) {
                img.style.display = 'none';
                img.src = '';
            }
            if (deleteBtn) {
                deleteBtn.style.display = 'none';
            }
            if (preview && !preview.querySelector('span')) {
                const placeholder = document.createElement('span');
                placeholder.style.cssText = 'color: #999; font-size: 12px;';
                placeholder.textContent = '无缩略图';
                preview.appendChild(placeholder);
            }
        } else {
            AdminUtils.showMessage(data.message || '移除失败', 'error');
        }
    })
    .catch(err => {
        console.error('移除失败:', err);
        AdminUtils.showMessage('移除失败', 'error');
    });
}

function loadCategoryThumbnail(categoryId) {
    // 查找当前分类的缩略图
    const metaItem = existingMeta.find(cat => cat.id === categoryId);
    const img = document.getElementById('edit-thumbnail-img');
    const preview = document.getElementById('edit-thumbnail-preview');
    const deleteBtn = document.getElementById('thumbnail-delete-hover');

    if (!img || !preview) return;

    // 清除旧的placeholder
    const oldPlaceholder = preview.querySelector('span');
    if (oldPlaceholder) oldPlaceholder.remove();

    if (metaItem && metaItem.thumbnail) {
        // 有自定义缩略图
        img.src = metaItem.thumbnail;
        img.style.display = 'block';
        if (deleteBtn) deleteBtn.style.display = 'block';
    } else if (metaItem && metaItem.first_image_thumb) {
        // 使用第一张图片作为缩略图
        img.src = metaItem.first_image_thumb;
        img.style.display = 'block';
        if (deleteBtn) deleteBtn.style.display = 'none';
    } else {
        // 无缩略图
        img.style.display = 'none';
        img.src = '';
        const placeholder = document.createElement('span');
        placeholder.style.cssText = 'color: #999; font-size: 12px;';
        placeholder.textContent = '无缩略图';
        preview.appendChild(placeholder);
        if (deleteBtn) deleteBtn.style.display = 'none';
    }

    // 重新激活feather图标 - 延迟执行
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 50);
}

// 统计图片总数
let totalImages = 0;
<?php foreach ($categoryData as $cat): ?>
totalImages += <?= (int) $cat['image_count'] ?>;
<?php endforeach; ?>
document.getElementById('total-images').textContent = totalImages;

// 初始化 feather icons - 延迟到页面完全加载后
window.addEventListener('load', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
        console.log('✓ Feather icons 已初始化');
    } else {
        console.warn('⚠ Feather icons 库未加载');
    }
});

// 调试信息 - 版本 2.5.6
console.log('Galleries 画廊管理页面 v2.5.6 已加载');
console.log('- ✓ 统一：feather icons初始化架构');
console.log('- ✓ 统一：与drafts-new保持一致的标准');
console.log('AdminDragSort:', typeof AdminDragSort !== 'undefined' ? '✓ 已加载' : '✗ 未加载');
console.log('AdminUtils:', typeof AdminUtils !== 'undefined' ? '✓ 已加载' : '✗ 未加载');
</script>
