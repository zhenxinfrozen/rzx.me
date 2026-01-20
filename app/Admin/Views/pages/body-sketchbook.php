<?php
$page_title = $page_title ?? '🛠️ Sketchbook 管理';
$page_subtitle = $page_subtitle ?? '管理 Sketchbook 页面速写本与图片';
$_GET['page'] = $_GET['page'] ?? 'sketchbook';

// 如果控制器没有传入数据，则手动加载数据
if (!isset($categoryData)) {
    // 手动加载必要的依赖和数据
    require_once __DIR__ . '/../../controllers/sketchbook-data.php';
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

<style>
.category-list { max-height: 70vh; overflow-y: auto; padding: 10px; }
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

/* 速写本缩略图样式 */
.category-thumbnail {
    width: 48px;
    height: 48px;
    border-radius: 6px;
    object-fit: cover;
    border: 2px solid #dee2e6;
    margin-right: 12px;
    flex-shrink: 0;
}
.category-thumbnail-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 6px;
    border: 2px dashed #ced4da;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
    color: #6c757d;
    font-size: 20px;
}

.thumbnail-container { min-height: 200px; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #f8f9fa; }

/* 新的图片网格容器 - 居中对齐 */
.thumbnail-grid-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    align-items: flex-start;
    min-height: 200px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

/* 缩略图编辑区域样式 */
.thumbnail-edit-section {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 10px;
}

.thumbnail-upload-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 120px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-direction: column;
}

.thumbnail-upload-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
}
.thumbnail-item {
    width: 80px;
    height: 80px;
    margin: 5px;
    border-radius: 4px;
    overflow: hidden;
    display: inline-block;
    border: 2px solid #dee2e6;
    position: relative;
    cursor: pointer;
}
.thumbnail-item img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-item:hover { border-color: #007bff; }
.thumbnail-item.selected { border-color: #0d6efd; box-shadow: 0 0 0 2px rgba(13,110,253,.25); }

/* 图片操作按钮 */
.thumbnail-actions {
    position: absolute;
    top: 2px;
    right: 2px;
    display: none;
    flex-direction: column;
    gap: 2px;
}
.thumbnail-item:hover .thumbnail-actions {
    display: flex;
}
.thumbnail-action-btn {
    width: 20px;
    height: 20px;
    border: none;
    border-radius: 3px;
    color: white;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.thumbnail-action-btn.delete { background: #dc3545; }
.thumbnail-action-btn.delete:hover { background: #c82333; }
.thumbnail-action-btn.move { background: #6c757d; }
.thumbnail-action-btn.move:hover { background: #5a6268; }
.thumbnail-action-btn.set-thumb { background: #0d6efd; }
.thumbnail-action-btn.set-thumb:hover { background: #0b5ed7; }

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

#toastContainer {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 2000;
}

.side-thumb {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    object-fit: cover;
}
.icon-row {
    display: flex;
    gap: 12px;
    align-items: center;
}
.icon-box {
    width: 64px;
    height: 64px;
    border: 1px dashed #ced4da;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f8f9fa;
}
.icon-img {
    max-width: 100%;
    max-height: 100%;
    display: none;
}
.thumbnail-area {
    min-height: 120px;
    border: 1px dashed rgba(0,0,0,0.05);
    border-radius: 12px;
    padding: 12px;
    background: #f8f9fb;
}

/* 图片排序样式 */
.thumbnail-sortable {
    transition: all 0.3s ease;
}
.thumbnail-sortable.dragging {
    opacity: 0.5;
    transform: scale(1.05);
    z-index: 1000;
}
.thumbnail-sortable.is-thumbnail {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40,167,69,.25);
}
.thumbnail-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
}

/* 方块上传按钮样式 - 与comics页面统一 */
.add-image-btn {
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
    margin: 5px;
}
.add-image-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.05);
}

/* 上传区域样式 */
.upload-area {
    border: 2px dashed #ced4da;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}
.upload-area.dragover {
    border-color: #007bff;
    background: #e3f2fd;
}
.upload-area:hover {
    border-color: #007bff;
}

/* 缩略图上传按钮样式 */
.thumbnail-upload-btn {
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
.thumbnail-upload-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.02);
}
.thumbnail-upload-btn i {
    font-size: 24px;
}

/* 修正缩略图显示区域 */
#edit-thumbnail-preview {
    display: flex;
    align-items: center;
}
#edit-thumbnail-preview img {
    display: block;
}

/* 自动生成缩略图按钮 */
.auto-thumbnail-btn {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.auto-thumbnail-btn:hover {
    background: #138496;
}
</style>

<?php if ($flashMessage): ?>
<div class="alert alert-<?= htmlspecialchars($flashMessage['type'] === 'success' ? 'success' : 'danger') ?> alert-toast" role="alert">
    <i data-feather="<?= $flashMessage['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="me-2"></i>
    <?= htmlspecialchars($flashMessage['text']) ?>
</div>
<?php endif; ?>

<div class="ray-body-box-useless">
<div class="content-header d-flex justify-content-between align-items-center mb-4">
    <p>管理 Sketchbook 页面的分类排序和显示设置</p>
</div>

<div class="row g-3">
                <div class="col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white position-relative">
                            <h6 class="card-title mb-0">
                                <i data-feather="list" class="me-2"></i>
                                速写本顺序
                            </h6>
                            <button type="button" class="add-category-btn" onclick="showAddCategoryPanel()" title="添加新速写本">+</button>
                        </div>
                        <div class="card-body p-0">
                            <ul class="category-list list-unstyled mb-0" id="categoryOrder">
                                <?php foreach ($categoryData as $category): ?>
                                <li class="category-item" data-category="<?= htmlspecialchars($category['id']) ?>" draggable="true">
                                    <div class="category-row d-flex align-items-center p-3">
                                        <span class="drag-handle" title="拖拽排序">⋮⋮</span>
                                        <!-- 速写本缩略图 -->
                                        <?php
                                        $thumbnailSrc = '';
                                        if (!empty($category['thumbnail'])) {
                                            $thumbnailSrc = $category['thumbnail'];
                                        } elseif (!empty($category['first_image_thumb'])) {
                                            $thumbnailSrc = $category['first_image_thumb'];
                                        }
                                        ?>
                                        <?php if ($thumbnailSrc): ?>
                                            <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="缩略图" class="category-thumbnail">
                                        <?php else: ?>
                                            <div class="category-thumbnail-placeholder">
                                                <i data-feather="image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="category-content d-flex align-items-center flex-grow-1" onclick="editCategory('<?= htmlspecialchars($category['id']) ?>')">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold category-name"><?= htmlspecialchars($category['display_name'] ?: $category['id']) ?></div>
                                                <small class="text-muted"><?= (int) $category['image_count'] ?> 张图片</small>
                                            </div>
                                            <span class="badge bg-secondary"><?= (int) $category['position'] ?></span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="display_names[<?= htmlspecialchars($category['id']) ?>]" value="<?= htmlspecialchars($category['display_name']) ?>" class="display-name-input">
                                    <input type="hidden" name="descriptions[<?= htmlspecialchars($category['id']) ?>]" value="<?= htmlspecialchars($category['description']) ?>" class="description-input">
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i data-feather="edit-3" class="me-2" id="edit-icon"></i>
                                <span id="edit-title">编辑速写本</span>
                            </h6>
                            <small id="edit-status" class="opacity-75">选择左侧速写本进行编辑</small>
                        </div>
                        <div class="card-body">
                            <div id="edit-panel-placeholder" class="text-center text-muted py-4">
                                <i data-feather="arrow-left" style="width: 48px; height: 48px; opacity: 0.5;"></i>
                                <p class="mt-3">点击左侧速写本开始编辑</p>
                            </div>

                            <div id="add-category-panel" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">新速写本名称</label>
                                        <input type="text" id="new-category-name" class="form-control" placeholder="输入速写本名称（英文，不含空格）">
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

                                <!-- 图片管理 -->
                                <div class="mb-3">
                                    <label class="form-label">图片管理</label>
                                    <div id="new-images-preview" class="thumbnail-grid-container" style="min-height: 150px;">
                                        <div class="add-image-btn" onclick="selectImagesFile('new')" title="上传图片">
                                            +
                                        </div>
                                    </div>
                                    <small class="text-muted">可选择多张图片，支持 JPG、PNG、WebP 格式</small>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="createNewCategory()">
                                        <i data-feather="plus" class="me-1"></i>创建速写本
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelAddCategory()">
                                        <i data-feather="x" class="me-1"></i>取消
                                    </button>
                                </div>
                            </div>

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

                                <!-- 缩略图编辑 -->
                                <div class="mb-3">
                                    <label class="form-label">缩略图</label>
                                    <div class="thumbnail-edit-section">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- 当前缩略图显示 -->
                                            <div class="thumbnail-preview" id="edit-thumbnail-preview">
                                                <img id="edit-thumbnail-img" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; border: 2px solid #28a745; display: none;">
                                            </div>
                                            <!-- 上传/更换缩略图按钮 -->
                                            <div class="thumbnail-upload-btn" id="edit-thumbnail-upload" onclick="selectThumbnailFile('edit')" title="上传/更换缩略图">
                                                <i data-feather="upload"></i>
                                                <span>上传缩略图</span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeThumbnail('edit')" id="remove-thumbnail-btn" style="display: none;">
                                                <i data-feather="trash-2" class="me-1"></i>删除缩略图
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <h6><i data-feather="image" class="me-1"></i>图片管理</h6>
                                <div id="thumbnail-grid" class="thumbnail-grid-container"></div>
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

                <div class="col-lg-3">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i data-feather="eye" class="me-2"></i>预览
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="/sketchbook" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i data-feather="external-link" class="me-1"></i>前台页面
                                    </a>
                                    <a href="/api/sketchbook" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i data-feather="code" class="me-1"></i>API数据
                                    </a>
                                </div>
                                <hr>
                                <div class="small text-muted">
                                    <div class="d-flex justify-content-between">
                                        <span>总速写本:</span>
                                        <span><?= (int) $totalCategories ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="card-title mb-0">
                                    <i data-feather="settings" class="me-2"></i>管理
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="controllers/trash.php" class="btn btn-outline-warning btn-sm">
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

        </div>

<!-- 隐藏的文件输入 -->
<input type="file" id="thumbnailFileInput" accept="image/*" style="display: none;" onchange="handleThumbnailUpload(event)">
<input type="file" id="imagesFileInput" accept="image/*" multiple style="display: none;" onchange="handleImagesUpload(event)">

<div id="toastContainer"></div>

<script>
const controllerUrl = '/admin/controllers/sketchbook.php';
const existingMeta = <?= json_encode($categoryData, JSON_UNESCAPED_UNICODE) ?>;

let currentEditingCategory = null;
let deletedCategories = [];
let currentFileInputType = null; // 'thumbnail' | 'images'
let currentFileInputContext = null; // 'new' | 'edit'
let selectedImages = []; // 当前选中的图片用于排序

document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    updateCategoryOrder();
    feather.replace();

    setTimeout(checkPhpConfig, 1000);

    const existingToast = document.querySelector('.alert-toast');
    if (existingToast) {
        setTimeout(() => {
            existingToast.classList.add('fade-out');
            setTimeout(() => existingToast.remove(), 300);
        }, 3000);
    }
});

function showAddCategoryPanel() {
    document.getElementById('edit-panel-placeholder').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('add-category-panel').style.display = 'block';

    document.getElementById('edit-title').textContent = '添加速写本';
    document.getElementById('edit-status').textContent = '创建新的作品速写本';
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';

    // 重置缩略图和图片预览
    const newThumbPreview = document.getElementById('new-thumbnail-preview');
    if (newThumbPreview) {
        newThumbPreview.style.display = 'none';
    }

    const newImagesPreview = document.getElementById('new-images-preview');
    if (newImagesPreview) {
        newImagesPreview.style.display = 'flex';
        newImagesPreview.innerHTML = `
            <div class="add-image-btn" onclick="selectImagesFile('new')" title="上传图片">
                +
            </div>
        `;
    }

    const icon = document.getElementById('edit-icon');
    icon.setAttribute('data-feather', 'plus');
    feather.replace();
}

function cancelAddCategory() {
    document.getElementById('add-category-panel').style.display = 'none';
    document.getElementById('edit-panel-placeholder').style.display = 'block';
    document.getElementById('edit-icon').setAttribute('data-feather', 'edit-3');
    document.getElementById('edit-title').textContent = '编辑速写本';
    document.getElementById('edit-status').textContent = '选择左侧速写本进行编辑';

    // 清空表单
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';

    // 清空图片预览
    const previewContainer = document.getElementById('new-images-preview');
    if (previewContainer) {
        previewContainer.style.display = 'flex';
        previewContainer.innerHTML = `
            <div class="add-image-btn" onclick="selectImagesFile('new')" title="上传图片">
                +
            </div>
        `;
    }

    // 清空文件输入
    document.getElementById('imagesFileInput').value = '';

    feather.replace();
}

function editCategory(categoryName) {
    currentEditingCategory = categoryName;
    document.querySelectorAll('.category-row').forEach(row => row.classList.remove('active'));
    const row = document.querySelector(`[data-category="${categoryName}"] .category-row`);
    if (row) {
        row.classList.add('active');
    }

    const displayInput = document.querySelector(`input[name="display_names[${categoryName}]"]`);
    const descriptionInput = document.querySelector(`input[name="descriptions[${categoryName}]"]`);

    document.getElementById('edit-display-name').value = displayInput ? displayInput.value : categoryName;
    document.getElementById('edit-folder-name').value = categoryName;
    document.getElementById('edit-description').value = descriptionInput ? descriptionInput.value : '';

    document.getElementById('edit-panel-placeholder').style.display = 'none';
    document.getElementById('add-category-panel').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'block';

    document.getElementById('edit-title').textContent = '编辑速写本';
    document.getElementById('edit-status').textContent = `正在编辑: ${categoryName}`;
    document.getElementById('edit-icon').setAttribute('data-feather', 'edit-3');
    feather.replace();

    loadCategoryThumbnails(categoryName);
    loadCategoryThumbnailImage(categoryName);
}

function saveCategory() {
    if (!currentEditingCategory) return;

    const displayName = document.getElementById('edit-display-name').value;
    const description = document.getElementById('edit-description').value;

    // 添加确认对话框
    if (!confirm('确定要保存对速写本 "' + currentEditingCategory + '" 的修改吗？')) {
        return;
    }

    showToast('info', '正在保存...');

    fetch(`${controllerUrl}?ajax=save_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentEditingCategory,
            displayName: displayName,
            description: description,
            category_order: getCurrentCategoryOrder()
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 局部更新界面，不重新加载整个页面
            updateCategoryDisplayInList(currentEditingCategory, displayName, description);

            // 更新缩略图信息
            if (data.thumbnail_info) {
                updateCategoryThumbnailInList(currentEditingCategory,
                    data.thumbnail_info.custom_thumbnail || data.thumbnail_info.first_image_thumb);
            }

            showToast('success', '速写本信息已保存');
        } else {
            showToast('danger', data.message || '保存失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '保存失败，请重试');
    });
}

function deleteCurrentCategory() {
    if (!currentEditingCategory) return;
    if (!confirm(`确定要删除速写本 \"${currentEditingCategory}\" 吗？\n这将删除该速写本下的所有图片文件。`)) return;

    fetch(`${controllerUrl}?ajax=delete_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentEditingCategory
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.category-item[data-category="${currentEditingCategory}"]`);
            if (item) item.remove();

            document.getElementById('edit-panel').style.display = 'none';
            document.getElementById('edit-panel-placeholder').style.display = 'block';
            currentEditingCategory = null;

            updateCategoryOrder();
            showToast('success', '速写本已删除');
        } else {
            showToast('danger', data.message || '删除失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '删除速写本失败');
    });
}

function loadCategoryThumbnails(categoryName) {
    const container = document.getElementById('thumbnail-grid');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> 加载中...</div>';

    fetch(`${controllerUrl}?ajax=thumbnails&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';

            // 添加方块上传按钮
            const addBtn = document.createElement('div');
            addBtn.className = 'add-image-btn';
            addBtn.innerHTML = '+';
            addBtn.title = '上传图片';
            addBtn.onclick = () => selectImagesFile('edit');
            container.appendChild(addBtn);

            if (data.success && data.images.length > 0) {
                data.images.forEach((image, index) => {
                    const thumbName = image.thumb_name || '';
                    const normalizedThumbName = thumbName || image.name;
                    const isThumb = data.current_thumbnail && normalizedThumbName === data.current_thumbnail;
                    const safeName = image.name.replace(/'/g, "\\'");
                    const safeThumbName = normalizedThumbName.replace(/'/g, "\\'");
                    const div = document.createElement('div');
                    div.className = `thumbnail-item thumbnail-sortable ${isThumb ? 'is-thumbnail' : ''}`;
                    div.dataset.image = image.name;
                    if (thumbName) {
                        div.dataset.thumbName = thumbName;
                    }
                    div.dataset.index = index;
                    div.draggable = true;
                    div.innerHTML = `
                        <img src="${image.thumb_path}" alt="${image.name}" loading="lazy">
                        <div class="thumbnail-actions">
                            <button class="thumbnail-action-btn set-thumb" title="设为速写本缩略图" onclick="event.stopPropagation(); setAsThumbnail('${categoryName}', '${safeName}', '${safeThumbName}');">
                                <i data-feather="star" style="width: 10px; height: 10px;"></i>
                            </button>
                            <button class="thumbnail-action-btn move" title="移动" onmousedown="event.stopPropagation();">
                                <i data-feather="move" style="width: 10px; height: 10px;"></i>
                            </button>
                            <button class="thumbnail-action-btn delete" title="删除" onclick="event.stopPropagation(); deleteImage('${categoryName}', '${safeName}');">
                                <i data-feather="trash-2" style="width: 10px; height: 10px;"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                });

                const statusDiv = document.createElement('div');
                statusDiv.className = 'mt-2 small text-muted';
                statusDiv.innerHTML = `共 ${data.images.length} 张图片，可拖拽排序。${data.current_thumbnail ? '绿色边框为当前缩略图' : ''}`;
                container.appendChild(statusDiv);
            } else {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'mt-2 small text-muted';
                emptyDiv.textContent = '暂无图片';
                container.appendChild(emptyDiv);
            }

            feather.replace();
            initializeImageSorting();
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i data-feather="alert-circle" style="width:32px; height:32px; opacity:0.5;"></i>
                    <p class="mt-2 small">加载失败，请重试</p>
                </div>`;
            feather.replace();
        });
}

function loadCategoryThumbnailImage(categoryName) {
    fetch(`${controllerUrl}?ajax=category_thumbnail&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            const img = document.getElementById('edit-thumbnail-img');
            const uploadBtn = document.getElementById('edit-thumbnail-upload');
            const removeBtn = document.getElementById('remove-thumbnail-btn');

            if (data.success && (data.thumbnail || data.first_image_thumb)) {
                // 有缩略图时显示图片，隐藏上传按钮
                const thumbnailUrl = data.thumbnail || data.first_image_thumb;
                img.src = thumbnailUrl;
                img.style.display = 'block';
                uploadBtn.querySelector('span').textContent = '更换缩略图';
                removeBtn.style.display = data.thumbnail ? 'inline-block' : 'none'; // 只有自定义缩略图才显示删除按钮
            } else {
                // 没有缩略图时隐藏图片，显示上传按钮
                img.style.display = 'none';
                uploadBtn.querySelector('span').textContent = '上传缩略图';
                removeBtn.style.display = 'none';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('edit-thumbnail-img').style.display = 'none';
            document.getElementById('edit-thumbnail-upload').querySelector('span').textContent = '上传缩略图';
            document.getElementById('remove-thumbnail-btn').style.display = 'none';
        });
}

function initializeDragAndDrop() {
    const categoryList = document.getElementById('categoryOrder');
    let draggedItem = null;

    document.querySelectorAll('.category-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
            const handle = this.querySelector('.drag-handle');
            if (handle) handle.classList.add('grabbing');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', '');
        });

        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            const handle = this.querySelector('.drag-handle');
            if (handle) handle.classList.remove('grabbing');
            document.querySelectorAll('.category-item').forEach(el => el.classList.remove('drag-over'));
            draggedItem = null;
            updateCategoryOrder();
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedItem || draggedItem === this) return;
            const rect = this.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            document.querySelectorAll('.category-item').forEach(el => el.classList.remove('drag-over'));
            if (e.clientY < midY) {
                categoryList.insertBefore(draggedItem, this);
            } else {
                categoryList.insertBefore(draggedItem, this.nextSibling);
            }
            this.classList.add('drag-over');
        });

        item.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
    });
}

function initializeImageSorting() {
    const images = document.querySelectorAll('.thumbnail-sortable');
    let draggedImage = null;

    images.forEach(image => {
        image.addEventListener('dragstart', function(e) {
            draggedImage = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        image.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedImage = null;
            updateImageOrder();
        });

        image.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedImage || draggedImage === this) return;

            const rect = this.getBoundingClientRect();
            const midX = rect.left + rect.width / 2;

            if (e.clientX < midX) {
                this.parentNode.insertBefore(draggedImage, this);
            } else {
                this.parentNode.insertBefore(draggedImage, this.nextSibling);
            }
        });

        image.addEventListener('drop', function(e) {
            e.preventDefault();
        });
    });
}

function updateCategoryOrder() {
    const order = getCurrentCategoryOrder();

    // 实时保存速写本顺序
    if (order) {
        fetch(`${controllerUrl}?ajax=save_order`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.warn('速写本顺序保存失败:', data.message);
            }
        })
        .catch(err => {
            console.warn('速写本顺序保存错误:', err);
        });
    }
}

function getCurrentCategoryOrder() {
    const order = [];
    document.querySelectorAll('#categoryOrder .category-item').forEach(item => {
        order.push(item.dataset.category);
    });
    return order.join(',');
}

function setAsThumbnail(categoryName, imageName, thumbName) {
    fetch(`${controllerUrl}?ajax=set_thumbnail`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: categoryName,
            image: imageName,
            thumb: thumbName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 更新编辑区域的缩略图显示
            const editImg = document.getElementById('edit-thumbnail-img');
            const uploadBtn = document.getElementById('edit-thumbnail-upload');
            const removeBtn = document.getElementById('remove-thumbnail-btn');

            if (editImg && data.thumbnail_url) {
                editImg.src = data.thumbnail_url;
                editImg.style.display = 'block';
                uploadBtn.querySelector('span').textContent = '更换缩略图';
                removeBtn.style.display = 'inline-block';
            }

            // 更新左侧速写本列表的缩略图
            updateCategoryThumbnailInList(categoryName, data.thumbnail_url);

            // 重新加载图片网格以更新视觉标识
            loadCategoryThumbnails(categoryName);

            showToast('success', '缩略图已设置');
        } else {
            showToast('danger', data.message || '设置失败');
        }
    })
    .catch(err => {
        console.error('设置缩略图失败:', err);
        showToast('danger', '设置缩略图失败');
    });
}

function updateCategoryThumbnailInList(categoryName, thumbnailUrl) {
    const categoryThumb = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail`);
    const categoryPlaceholder = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail-placeholder`);

    if (categoryThumb && thumbnailUrl) {
        categoryThumb.src = thumbnailUrl;
    } else if (categoryPlaceholder && thumbnailUrl) {
        categoryPlaceholder.outerHTML = `<img src="${thumbnailUrl}" alt="缩略图" class="category-thumbnail">`;
    }
}

/**
 * 更新速写本在列表中的显示信息
 */
function updateCategoryDisplayInList(categoryName, displayName, description) {
    // 更新隐藏的input值
    const displayInput = document.querySelector(`input[name="display_names[${categoryName}]"]`);
    const descriptionInput = document.querySelector(`input[name="descriptions[${categoryName}]"]`);

    if (displayInput) displayInput.value = displayName;
    if (descriptionInput) descriptionInput.value = description;

    // 更新显示的名称
    const nameEl = document.querySelector(`[data-category="${categoryName}"] .category-name`);
    if (nameEl) nameEl.textContent = displayName || categoryName;
}

/**
 * 动态添加新速写本到列表中
 */
function addNewCategoryToList(categoryName, displayName, description, thumbnailInfo) {
    const categoryList = document.getElementById('categoryOrder');
    const position = document.getElementById('new-category-position').value;

    // 创建新的速写本元素
    const newCategoryItem = document.createElement('li');
    newCategoryItem.className = 'category-item';
    newCategoryItem.setAttribute('data-category', categoryName);
    newCategoryItem.setAttribute('draggable', 'true');

    // 确定缩略图显示
    const thumbnailUrl = thumbnailInfo?.custom_thumbnail || thumbnailInfo?.first_image_thumb;
    const thumbnailHtml = thumbnailUrl
        ? `<img src="${thumbnailUrl}" alt="缩略图" class="category-thumbnail">`
        : `<div class="category-thumbnail-placeholder"><i data-feather="image"></i></div>`;

    newCategoryItem.innerHTML = `
        <div class="category-row d-flex align-items-center p-3">
            <span class="drag-handle" title="拖拽排序">⋮⋮</span>
            ${thumbnailHtml}
            <div class="category-content d-flex align-items-center flex-grow-1" onclick="editCategory('${categoryName}')">
                <div class="flex-grow-1">
                    <div class="fw-semibold category-name">${displayName || categoryName}</div>
                    <small class="text-muted">0 张图片</small>
                </div>
                <span class="badge bg-secondary">${categoryList.children.length + 1}</span>
            </div>
        </div>
        <input type="hidden" name="display_names[${categoryName}]" value="${displayName}" class="display-name-input">
        <input type="hidden" name="descriptions[${categoryName}]" value="${description}" class="description-input">
    `;

    // 根据位置插入
    if (position === 'first') {
        categoryList.insertBefore(newCategoryItem, categoryList.firstChild);
    } else {
        categoryList.appendChild(newCategoryItem);
    }

    // 重新初始化拖拽和feather图标
    initializeDragAndDrop();
    feather.replace();

    // 更新速写本顺序
    updateCategoryOrder();
}

function updateImageOrder() {
    if (!currentEditingCategory) return;

    const imageOrder = [];
    document.querySelectorAll('.thumbnail-sortable').forEach(item => {
        imageOrder.push(item.dataset.image);
    });

    fetch(`${controllerUrl}?ajax=reorder_images`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentEditingCategory,
            order: imageOrder
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '图片顺序已更新');
        } else {
            showToast('danger', data.message || '更新失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '更新图片顺序失败');
    });
}

function selectThumbnailFile(context) {
    currentFileInputType = 'thumbnail';
    currentFileInputContext = context;
    document.getElementById('thumbnailFileInput').click();
}

function selectImagesFile(context) {
    currentFileInputType = 'images';
    currentFileInputContext = context;
    document.getElementById('imagesFileInput').click();
}

function handleThumbnailUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        showToast('danger', '请选择图片文件');
        return;
    }

    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        showToast('danger', '缩略图文件不能超过10MB');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const imgId = `${currentFileInputContext}-thumbnail-img`;
        const previewId = `${currentFileInputContext}-thumbnail-preview`;
        const uploadId = `${currentFileInputContext}-thumbnail-upload`;

        document.getElementById(imgId).src = e.target.result;
        document.getElementById(previewId).style.display = 'block';
        if (document.getElementById(uploadId)) {
            document.getElementById(uploadId).style.display = 'none';
        }
    };
    reader.readAsDataURL(file);

    // 如果是编辑模式，立即上传缩略图
    if (currentFileInputContext === 'edit' && currentEditingCategory) {
        uploadThumbnail(currentEditingCategory, file);
    }
}

function handleImagesUpload(event) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    const maxSize = 50 * 1024 * 1024; // 50MB
    const oversized = files.filter(file => file.size > maxSize);
    if (oversized.length > 0) {
        showToast('danger', `有 ${oversized.length} 个文件超过50MB限制`);
        return;
    }

    if (currentFileInputContext === 'edit' && currentEditingCategory) {
        // 编辑模式直接上传
        uploadImages(currentEditingCategory, files);
    } else if (currentFileInputContext === 'new') {
        // 新建模式预览
        const previewContainer = document.getElementById('new-images-preview');
        if (!previewContainer) {
            return;
        }

        previewContainer.style.display = 'flex';

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                // 检查是否已存在上传按钮，如果没有则添加
                let addBtn = previewContainer.querySelector('.add-image-btn');
                if (!addBtn) {
                    addBtn = document.createElement('div');
                    addBtn.className = 'add-image-btn';
                    addBtn.innerHTML = '+';
                    addBtn.title = '上传图片';
                    addBtn.onclick = () => selectImagesFile('new');
                    previewContainer.appendChild(addBtn);
                }

                // 创建图片预览项
                const div = document.createElement('div');
                div.className = 'thumbnail-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="${file.name}">
                    <div class="thumbnail-actions">
                        <button class="thumbnail-action-btn delete" onclick="removePreviewImage(this)">
                            <i data-feather="x" style="width: 10px; height: 10px;"></i>
                        </button>
                    </div>
                `;
                // 在上传按钮之前插入
                previewContainer.insertBefore(div, addBtn);
                feather.replace();
            };
            reader.readAsDataURL(file);
        });
    }
}

function uploadThumbnail(categoryName, file) {
    const formData = new FormData();
    formData.append('category', categoryName);
    formData.append('thumbnail', file);

    showToast('info', '正在上传缩略图...');

    fetch(`${controllerUrl}?ajax=upload_thumbnail`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '缩略图上传成功');

            // 更新编辑区域的缩略图显示
            const editImg = document.getElementById('edit-thumbnail-img');
            const previewDiv = document.getElementById('edit-thumbnail-preview');
            const uploadDiv = document.getElementById('edit-thumbnail-upload');
            const removeBtn = document.getElementById('remove-thumbnail-btn');

            if (editImg && data.thumbnail_url) {
                editImg.src = data.thumbnail_url;
                editImg.style.display = 'block';
                previewDiv.style.display = 'flex';
                uploadDiv.style.display = 'none';
                if (removeBtn) {
                    removeBtn.style.display = 'inline-block';
                }
            }

            // 更新左侧速写本列表的缩略图
            const categoryThumb = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail`);
            const categoryPlaceholder = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail-placeholder`);
            if (categoryThumb && data.thumbnail_url) {
                categoryThumb.src = data.thumbnail_url;
            } else if (categoryPlaceholder && data.thumbnail_url) {
                categoryPlaceholder.outerHTML = `<img src="${data.thumbnail_url}" alt="缩略图" class="category-thumbnail">`;
            }

            // 重新加载图片列表与缩略图信息
            loadCategoryThumbnails(categoryName);
            loadCategoryThumbnailImage(categoryName);
        } else {
            showToast('danger', data.message || '缩略图上传失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '缩略图上传失败');
    });
}

function removeThumbnail(context) {
    if (context === 'edit' && currentEditingCategory) {
        if (!confirm('确定要删除缩略图吗？将自动使用第一张图片作为缩略图。')) return;

        fetch(`${controllerUrl}?ajax=delete_thumbnail`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ category: currentEditingCategory })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 更新编辑区域显示
                const editImg = document.getElementById('edit-thumbnail-img');
                const uploadBtn = document.getElementById('edit-thumbnail-upload');
                const removeBtn = document.getElementById('remove-thumbnail-btn');

                if (data.new_thumbnail_url) {
                    // 有新的缩略图（第一张图片）
                    editImg.src = data.new_thumbnail_url;
                    editImg.style.display = 'block';
                    uploadBtn.querySelector('span').textContent = '更换缩略图';
                    removeBtn.style.display = 'inline-block';
                } else {
                    // 完全没有图片
                    editImg.style.display = 'none';
                    uploadBtn.querySelector('span').textContent = '上传缩略图';
                    removeBtn.style.display = 'none';
                }

                showToast('success', '缩略图已删除，已自动使用第一张图片');
                loadCategoryThumbnails(currentEditingCategory);

                // 更新左侧速写本列表
                const categoryThumb = document.querySelector(`[data-category="${currentEditingCategory}"] .category-thumbnail`);
                if (categoryThumb) {
                    if (data.new_thumbnail_url) {
                        categoryThumb.src = data.new_thumbnail_url;
                    } else {
                        categoryThumb.outerHTML = `
                            <div class="category-thumbnail-placeholder">
                                <i data-feather="image"></i>
                            </div>`;
                        feather.replace();
                    }
                }
            } else {
                showToast('danger', data.message || '删除失败');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('danger', '删除缩略图失败');
        });
    } else if (context === 'new') {
        const newThumbPreview = document.getElementById('new-thumbnail-preview');
        const newThumbUpload = document.getElementById('new-thumbnail-upload');
        if (newThumbPreview) {
            newThumbPreview.style.display = 'none';
        }
        if (newThumbUpload) {
            newThumbUpload.style.display = 'block';
        }
    }
}

function removePreviewImage(button) {
    button.closest('.thumbnail-item').remove();
    const container = document.getElementById('new-images-preview');
    // 检查是否还有图片项（除了上传按钮）
    const imageItems = container.querySelectorAll('.thumbnail-item');
    if (imageItems.length === 0) {
        // 确保上传按钮仍然存在
        let addBtn = container.querySelector('.add-image-btn');
        if (!addBtn) {
            addBtn = document.createElement('div');
            addBtn.className = 'add-image-btn';
            addBtn.innerHTML = '+';
            addBtn.title = '选择图片';
            addBtn.onclick = () => selectImagesFile('new');
            container.appendChild(addBtn);
        }
    }
}

function uploadImages(categoryName, files) {
    const formData = new FormData();
    formData.append('category', categoryName);
    Array.from(files).forEach(file => formData.append('images[]', file));

    const totalSize = (Array.from(files).reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(1);
    showToast('info', `正在上传 ${files.length} 张图片 (${totalSize}MB)...`);

    fetch(`${controllerUrl}?ajax=upload_images`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || '上传成功');
            loadCategoryThumbnails(categoryName);

            // 更新图片数量
            const countEl = document.querySelector(`[data-category="${categoryName}"] .category-item small`);
            if (countEl && data.total_count) {
                countEl.textContent = `${data.total_count} 张图片`;
            }

            // 如果是第一张图片且没有缩略图，自动设置为缩略图
            if (data.auto_set_thumbnail || data.thumbnail_set) {
                showToast('info', '已自动设置第一张图片为缩略图');
                loadCategoryThumbnailImage(categoryName);

                // 更新左侧速写本列表的缩略图
                const categoryPlaceholder = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail-placeholder`);
                if (categoryPlaceholder && data.thumbnail_url) {
                    categoryPlaceholder.outerHTML = `<img src="${data.thumbnail_url}" alt="缩略图" class="category-thumbnail">`;
                }
            }
        } else {
            showToast('danger', data.message || '上传失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '上传失败。如果文件较大，请检查PHP配置');
    });
}

function deleteImage(categoryName, imageName) {
    if (!confirm(`确定要删除图片 "${imageName}" 吗？\n此操作将永久删除原文件。`)) return;

    fetch(`${controllerUrl}?ajax=delete_image`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: categoryName,
            image: imageName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '图片已删除');
            loadCategoryThumbnails(categoryName);

            // 更新图片数量
            const countEl = document.querySelector(`[data-category="${categoryName}"] .category-item small`);
            if (countEl && data.total_count !== undefined) {
                countEl.textContent = `${data.total_count} 张图片`;
            }
        } else {
            showToast('danger', data.message || '删除失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '删除图片失败');
    });
}

function createNewCategory() {
    const categoryName = document.getElementById('new-category-name').value.trim();
    const displayName = document.getElementById('new-display-name').value.trim();
    const description = document.getElementById('new-description').value.trim();
    const position = document.getElementById('new-category-position').value;

    if (!categoryName) {
        showToast('danger', '请输入速写本名称');
        return;
    }
    if (!/^[a-zA-Z0-9_-]+$/.test(categoryName)) {
        showToast('danger', '速写本名称只能包含字母、数字、下划线和连字符');
        return;
    }

    const existing = Array.from(document.querySelectorAll('.category-item')).map(item => item.dataset.category);
    if (existing.includes(categoryName)) {
        showToast('danger', '速写本名称已存在');
        return;
    }

    if (!confirm(`确定要创建速写本 "${displayName || categoryName}" 吗？`)) {
        return;
    }

    const imageFiles = document.getElementById('imagesFileInput').files;

    const formData = new FormData();
    formData.append('category', categoryName);
    formData.append('displayName', displayName);
    formData.append('description', description);
    formData.append('position', position);

    if (imageFiles.length > 0) {
        Array.from(imageFiles).forEach(file => formData.append('images[]', file));
    }

    showToast('info', '正在创建速写本...');

    fetch(`${controllerUrl}?ajax=create_category`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '速写本创建成功，正在更新列表...');

            // 动态添加新速写本到列表中，而不是刷新整个页面
            addNewCategoryToList(data.category, displayName, description, data.thumbnail_info);

            // 清空表单
            document.getElementById('new-category-name').value = '';
            document.getElementById('new-display-name').value = '';
            document.getElementById('new-description').value = '';

            // 清空图片预览
            const previewContainer = document.getElementById('new-images-preview');
            previewContainer.innerHTML = `
                <div class="add-image-btn" onclick="selectImagesFile('new')" title="上传图片">
                    +
                </div>
            `;

            // 清空文件输入
            document.getElementById('imagesFileInput').value = '';

            // 关闭添加面板
            cancelAddCategory();
        } else {
            showToast('danger', data.message || '创建失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '创建失败，请重试');
    });
}

function showPhpConfigInfo() {
    fetch(`${controllerUrl}?ajax=php_config`)
        .then(res => res.json())
        .then(data => {
            let message = `PHP上传配置：\n`;
            message += `• 单文件大小限制: ${data.config.upload_max_filesize}\n`;
            message += `• POST数据限制: ${data.config.post_max_size}\n`;
            message += `• 最大文件数: ${data.config.max_file_uploads}\n`;
            message += `• 执行时间限制: ${data.config.max_execution_time}秒\n`;

            if (data.issues.length > 0) {
                message += `\n问题：\n`;
                data.issues.forEach(issue => message += `• ${issue}\n`);
                message += `\n建议：\n`;
                data.recommendations.forEach(rec => message += `• ${rec}\n`);
            } else {
                message += `\n✅ 配置良好，支持大文件上传`;
            }

            alert(message);
        })
        .catch(err => {
            console.error(err);
            showToast('danger', '获取PHP配置失败');
        });
}

function resetToDefault() {
    if (confirm('确定要重置为默认配置吗？')) {
        window.location.reload();
    }
}

function checkPhpConfig() {
    fetch(`${controllerUrl}?ajax=php_config`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'warning' && data.issues.length > 0) {
                showToast('warning', `PHP配置可能影响文件上传：${data.issues.slice(0, 2).join('；')}`);
            }
        })
        .catch(() => {});
}

function selectImage(imageName) {
    showToast('info', `选择了图片: ${imageName}`);
}

function showToast(type, message) {
    const colors = {
        success: 'bg-success text-white',
        danger: 'bg-danger text-white',
        warning: 'bg-warning text-dark',
        info: 'bg-primary text-white'
    };
    const toast = document.createElement('div');
    toast.className = `toast ${colors[type] || colors.info}`;
    toast.style.minWidth = '240px';
    toast.style.marginTop = '8px';
    toast.style.padding = '10px';
    toast.innerHTML = `<strong>${message}</strong>`;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
</div>
