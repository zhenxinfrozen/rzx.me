<?php
$page_title = $page_title ?? '🎬 Video Gallery 管理';
$page_subtitle = $page_subtitle ?? '管理视频分组与文件';
$_GET['page'] = $_GET['page'] ?? 'video-gallery';

// 如果控制器没有传入数据，则手动加载数据
if (!isset($categoryData)) {
    // 手动加载必要的依赖和数据
    require_once __DIR__ . '/../../controllers/video-gallery.php';
}

$categoryData = $categoryData ?? [];
$currentConfig = $currentConfig ?? [
    'sort_method' => 'custom_order',
    'custom_order' => [],
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

/* 视频分组缩略图样式 */
.category-thumbnail {
    width: 85px;
    height: 48px;
    border-radius: 6px;
    object-fit: cover;
    border: 2px solid #dee2e6;
    margin-right: 12px;
    flex-shrink: 0;
}
.category-thumbnail-placeholder {
    width: 48px;
    height: 27px;
    border-radius: 6px;
    border: 2px dashed #ced4da;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
    color: #6c757d;
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
    height: 68px;
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
.thumbnail-upload-btn i {
    font-size: 24px;
    margin-bottom: 8px;
}
.thumbnail-upload-btn span {
    font-size: 12px;
    text-align: center;
}

/* 视频网格容器 */
.video-grid-container {
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

/* 视频项样式 */
.video-item {
    width: 120px;
    height: 67px;
    border-radius: 6px;
    overflow: hidden;
    display: inline-block;
    border: 2px solid #dee2e6;
    position: relative;
    cursor: pointer;
    background: #343a40;
}
.video-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.video-item:hover {
    border-color: #007bff;
}
.video-item.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,.25);
}

/* 视频操作按钮 */
.video-actions {
    position: absolute;
    top: 2px;
    right: 2px;
    display: none;
    flex-direction: column;
    gap: 2px;
}
.video-item:hover .video-actions {
    display: flex;
}
.video-action-btn {
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
.video-action-btn.delete {
    background: #dc3545;
}
.video-action-btn.delete:hover {
    background: #c82333;
}
.video-action-btn.move {
    background: #6c757d;
}
.video-action-btn.move:hover {
    background: #5a6268;
}

.add-video-btn {
    width: 120px;
    height: 67px;
    border-radius: 6px;
    border: 2px dashed #007bff;
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
.add-video-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.05);
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
    width: 48px;
    height: 27px;
    border-radius: 6px;
    object-fit: cover;
}

.icon-row {
    display: flex;
    gap: 12px;
    align-items: center;
}

.icon-box {
    width: 48px;
    height: 27px;
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
    min-height: 80px;
    border: 1px dashed rgba(0,0,0,0.05);
    border-radius: 12px;
    padding: 12px;
    background: #f8f9fb;
}

/* 修正缩略图显示区域 */
#edit-thumbnail-preview {
    display: flex;
    align-items: center;
}
#edit-thumbnail-preview img {
    display: block;
    width: 120px;
    height: 67px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #28a745;
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


<div class="row g-3">
            <div class="col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white position-relative">
                        <h6 class="card-title mb-0">
                            <i data-feather="list" class="me-2"></i>
                            分组顺序
                        </h6>
                        <button type="button" class="add-category-btn" onclick="showAddCategoryPanel()" title="添加新分组">+</button>
                    </div>
                    <div class="card-body p-0">
                        <ul class="category-list list-unstyled mb-0" id="categoryOrder">
                            <?php foreach ($categoryData as $category): ?>
                            <li class="category-item" data-category="<?= htmlspecialchars($category['id']) ?>" draggable="true">
                                <div class="category-row d-flex align-items-center p-3">
                                    <span class="drag-handle" title="拖拽排序">⋮⋮</span>
                                    <!-- 分组缩略图 -->
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
                                            <i data-feather="video"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="category-content d-flex align-items-center flex-grow-1" onclick="editCategory('<?= htmlspecialchars($category['id']) ?>')">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold category-name"><?= htmlspecialchars($category['display_name'] ?: $category['id']) ?></div>
                                            <small class="text-muted"><?= (int) $category['video_count'] ?> 个视频</small>
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
                            <span id="edit-title">编辑分组</span>
                        </h6>
                        <small id="edit-status" class="opacity-75">选择左侧分组进行编辑</small>
                    </div>
                    <div class="card-body">
                        <div id="edit-panel-placeholder" class="text-center text-muted py-4">
                            <i data-feather="arrow-left" style="width: 48px; height: 48px; opacity: 0.5;"></i>
                            <p class="mt-3">点击左侧分组开始编辑</p>
                        </div>

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

                            <!-- 视频管理 -->
                            <div class="mb-3">
                                <label class="form-label">视频管理</label>
                                <div id="new-videos-preview" class="video-grid-container" style="min-height: 150px;">
                                    <div class="add-video-btn" onclick="selectVideosFile('new')" title="上传视频">
                                        +
                                    </div>
                                </div>
                                <small class="text-muted">可选择多个视频文件，支持 MP4、WebM 格式</small>
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
                                            <img id="edit-thumbnail-img" style="width: 120px; height: 68px; border-radius: 8px; object-fit: cover; border: 2px solid #28a745; display: none;">
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
                                        <small class="text-muted ms-2">默认使用分组内第一张视频缩略图</small>
                                    </div>
                                </div>
                            </div>

                            <h6><i data-feather="video" class="me-1"></i>视频管理</h6>
                            <div id="video-grid" class="video-grid-container"></div>
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
                                <a href="/videos" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i data-feather="external-link" class="me-1"></i>前台页面
                                </a>
                                <a href="/api/videos" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i data-feather="code" class="me-1"></i>API数据
                                </a>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between">
                                    <span>总分组:</span>
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
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="scanVideoDirectory()">
                                    <i data-feather="refresh-cw" class="me-1"></i>扫描目录
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showPhpConfigInfo()">
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
<input type="file" id="videosFileInput" accept="video/*" multiple style="display: none;" onchange="handleVideosUpload(event)">

<div id="toastContainer"></div>

<script>
const controllerUrl = '/admin/ajax?controller=video-gallery';
const existingMeta = <?= json_encode($categoryData, JSON_UNESCAPED_UNICODE) ?>;

let currentEditingCategory = null;
let deletedCategories = [];
let currentFileInputType = null; // 'thumbnail' | 'videos'
let currentFileInputContext = null; // 'new' | 'edit'
let selectedVideos = []; // 当前选中的视频用于排序

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

    document.getElementById('edit-title').textContent = '添加分组';
    document.getElementById('edit-status').textContent = '创建新的视频分组';
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';

    // 重置视频预览
    const newVideosPreview = document.getElementById('new-videos-preview');
    if (newVideosPreview) {
        newVideosPreview.style.display = 'flex';
        newVideosPreview.innerHTML = `
            <div class="add-video-btn" onclick="selectVideosFile('new')" title="上传视频">
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
    document.getElementById('edit-title').textContent = '编辑分组';
    document.getElementById('edit-status').textContent = '选择左侧分组进行编辑';

    // 清空表单
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';

    // 清空视频预览
    const previewContainer = document.getElementById('new-videos-preview');
    if (previewContainer) {
        previewContainer.style.display = 'flex';
        previewContainer.innerHTML = `
            <div class="add-video-btn" onclick="selectVideosFile('new')" title="上传视频">
                +
            </div>
        `;
    }

    // 清空文件输入
    document.getElementById('videosFileInput').value = '';

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

    document.getElementById('edit-title').textContent = '编辑分组';
    document.getElementById('edit-status').textContent = `正在编辑: ${categoryName}`;
    document.getElementById('edit-icon').setAttribute('data-feather', 'edit-3');
    feather.replace();

    loadCategoryVideos(categoryName);
    loadCategoryThumbnailImage(categoryName);
}

function saveCategory() {
    if (!currentEditingCategory) return;

    const displayName = document.getElementById('edit-display-name').value;
    const description = document.getElementById('edit-description').value;

    // 添加确认对话框
    if (!confirm('确定要保存对分组 "' + currentEditingCategory + '" 的修改吗？')) {
        return;
    }

    showToast('info', '正在保存...');

    fetch(`${controllerUrl}&ajax=save_category`, {
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

            showToast('success', '分组信息已保存');
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
    if (!confirm(`确定要删除分组 \"${currentEditingCategory}\" 吗？\n这将删除该分组下的所有视频文件。`)) return;

    fetch(`${controllerUrl}&ajax=delete_category`, {
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
            showToast('success', '分组已删除');
        } else {
            showToast('danger', data.message || '删除失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '删除分组失败');
    });
}

function loadCategoryVideos(categoryName) {
    const container = document.getElementById('video-grid');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> 加载中...</div>';

    fetch(`${controllerUrl}&ajax=videos&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';

            // 添加方块上传按钮
            const addBtn = document.createElement('div');
            addBtn.className = 'add-video-btn';
            addBtn.innerHTML = '+';
            addBtn.title = '上传视频';
            addBtn.onclick = () => selectVideosFile('edit');
            container.appendChild(addBtn);

            if (data.success && data.videos.length > 0) {
                data.videos.forEach((video, index) => {
                    const isThumb = data.current_thumbnail && video.poster === data.current_thumbnail;
                    // 从sources中获取实际文件名
                    const sourceKeys = Object.keys(video.sources || {});
                    const fileName = sourceKeys.length > 0 ? video.sources[sourceKeys[0]].split('/').pop() : video.title;

                    const div = document.createElement('div');
                    div.className = `video-item video-sortable ${isThumb ? 'is-thumbnail' : ''}`;
                    div.dataset.video = video.title;
                    div.dataset.filename = fileName;
                    div.dataset.index = index;
                    div.draggable = true;
                    div.innerHTML = `
                        <img src="${video.poster}" alt="${video.title}">
                        <div class="video-actions">
                            <button class="video-action-btn delete" title="删除" onclick="event.stopPropagation(); deleteVideo('${categoryName}', '${fileName}');">
                                <i data-feather="trash-2" style="width: 10px; height: 10px;"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                });

                const statusDiv = document.createElement('div');
                statusDiv.className = 'mt-2 small text-muted';
                statusDiv.innerHTML = `共 ${data.videos.length} 个视频，可拖拽排序。`;
                container.appendChild(statusDiv);
            } else {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'mt-2 small text-muted';
                emptyDiv.textContent = '暂无视频';
                container.appendChild(emptyDiv);
            }

            feather.replace();
            initializeVideoSorting();
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
    fetch(`${controllerUrl}&ajax=category_thumbnail&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            const img = document.getElementById('edit-thumbnail-img');
            const uploadBtn = document.getElementById('edit-thumbnail-upload');
            const removeBtn = document.getElementById('remove-thumbnail-btn');

            if (data.success && (data.thumbnail || data.first_video_thumb)) {
                // 有缩略图时显示图片，隐藏上传按钮
                const thumbnailUrl = data.thumbnail || data.first_video_thumb;
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

function initializeVideoSorting() {
    const videos = document.querySelectorAll('.video-sortable');
    let draggedVideo = null;

    videos.forEach(video => {
        video.addEventListener('dragstart', function(e) {
            draggedVideo = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        video.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedVideo = null;
            updateVideoOrder();
        });

        video.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedVideo || draggedVideo === this) return;

            const rect = this.getBoundingClientRect();
            const midX = rect.left + rect.width / 2;

            if (e.clientX < midX) {
                this.parentNode.insertBefore(draggedVideo, this);
            } else {
                this.parentNode.insertBefore(draggedVideo, this.nextSibling);
            }
        });

        video.addEventListener('drop', function(e) {
            e.preventDefault();
        });
    });
}

function updateCategoryOrder() {
    const order = getCurrentCategoryOrder();

    // 实时保存分组顺序
    if (order) {
        fetch(`${controllerUrl}&ajax=save_order`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order: order })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.warn('分组顺序保存失败:', data.message);
            }
        })
        .catch(err => {
            console.warn('分组顺序保存错误:', err);
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
 * 更新分组在列表中的显示信息
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
 * 动态添加新分组到列表中
 */
function addNewCategoryToList(categoryName, displayName, description, thumbnailInfo) {
    const categoryList = document.getElementById('categoryOrder');
    const position = document.getElementById('new-category-position').value;

    // 创建新的分组元素
    const newCategoryItem = document.createElement('li');
    newCategoryItem.className = 'category-item';
    newCategoryItem.setAttribute('data-category', categoryName);
    newCategoryItem.setAttribute('draggable', 'true');

    // 确定缩略图显示
    const thumbnailUrl = thumbnailInfo?.custom_thumbnail || thumbnailInfo?.first_video_thumb;
    const thumbnailHtml = thumbnailUrl
        ? `<img src="${thumbnailUrl}" alt="缩略图" class="category-thumbnail">`
        : `<div class="category-thumbnail-placeholder">
            <i data-feather="video"></i>
        </div>`;

    newCategoryItem.innerHTML = `
        <div class="category-row d-flex align-items-center p-3">
            <span class="drag-handle" title="拖拽排序">⋮⋮</span>
            ${thumbnailHtml}
            <div class="category-content d-flex align-items-center flex-grow-1" onclick="editCategory('${categoryName}')">
                <div class="flex-grow-1">
                    <div class="fw-semibold category-name">${displayName || categoryName}</div>
                    <small class="text-muted">0 个视频</small>
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

    // 更新分组顺序
    updateCategoryOrder();
}

function updateVideoOrder() {
    if (!currentEditingCategory) return;

    const videoOrder = [];
    document.querySelectorAll('.video-sortable').forEach((item, index) => {
        // 传递视频在数组中的索引
        const videoIndex = Array.from(item.parentNode.children).indexOf(item);
        videoOrder.push(videoIndex);
    });

    fetch(`${controllerUrl}&ajax=reorder_videos`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentEditingCategory,
            order: videoOrder
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '视频顺序已更新');
        } else {
            showToast('danger', data.message || '更新失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '网络请求失败');
    });
}

function selectThumbnailFile(context) {
    currentFileInputType = 'thumbnail';
    currentFileInputContext = context;
    document.getElementById('thumbnailFileInput').click();
}

function selectVideosFile(context) {
    currentFileInputType = 'videos';
    currentFileInputContext = context;
    document.getElementById('videosFileInput').click();
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

function handleVideosUpload(event) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    const maxSize = 500 * 1024 * 1024; // 500MB per video
    const oversized = files.filter(file => file.size > maxSize);
    if (oversized.length > 0) {
        showToast('danger', `有 ${oversized.length} 个文件超过500MB限制`);
        return;
    }

    if (currentFileInputContext === 'edit' && currentEditingCategory) {
        // 编辑模式直接上传
        uploadVideos(currentEditingCategory, files);
    } else if (currentFileInputContext === 'new') {
        // 新建模式预览
        const previewContainer = document.getElementById('new-videos-preview');
        if (!previewContainer) {
            return;
        }

        previewContainer.style.display = 'flex';

        files.forEach((file, index) => {
            // 为视频文件创建预览项
            const div = document.createElement('div');
            div.className = 'video-item';
            div.innerHTML = `
                <div style="width: 100%; height: 100%; background: #343a40; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                    ${file.name}
                </div>
                <div class="video-actions">
                    <button class="video-action-btn delete" onclick="removePreviewVideo(this)">
                        <i data-feather="x" style="width: 10px; height: 10px;"></i>
                    </button>
                </div>
            `;

            // 检查是否已存在上传按钮，如果没有则添加
            let addBtn = previewContainer.querySelector('.add-video-btn');
            if (!addBtn) {
                addBtn = document.createElement('div');
                addBtn.className = 'add-video-btn';
                addBtn.innerHTML = '+';
                addBtn.title = '上传视频';
                addBtn.onclick = () => selectVideosFile('new');
                previewContainer.appendChild(addBtn);
            }

            // 在上传按钮之前插入
            previewContainer.insertBefore(div, addBtn);
            feather.replace();
        });
    }
}

function uploadThumbnail(categoryName, file) {
    const formData = new FormData();
    formData.append('category', categoryName);
    formData.append('thumbnail', file);

    showToast('info', '正在上传缩略图...');

    fetch(`${controllerUrl}&ajax=upload_thumbnail`, {
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

            if (editImg && data.thumbnail_url) {
                editImg.src = data.thumbnail_url;
                editImg.style.display = 'block';
                previewDiv.style.display = 'flex';
                uploadDiv.style.display = 'none';
            }

            // 更新左侧分组列表的缩略图
            const categoryThumb = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail`);
            const categoryPlaceholder = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail-placeholder`);
            if (categoryThumb && data.thumbnail_url) {
                categoryThumb.src = data.thumbnail_url;
            } else if (categoryPlaceholder && data.thumbnail_url) {
                categoryPlaceholder.outerHTML = `<img src="${data.thumbnail_url}" alt="缩略图" class="category-thumbnail">`;
            }
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
        console.log('删除缩略图：', currentEditingCategory);
        if (!confirm('确定要删除缩略图吗？将自动使用第一张视频缩略图作为分组缩略图。')) return;

        fetch(`${controllerUrl}&ajax=delete_thumbnail`, {
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
                    // 有新的缩略图（第一张视频缩略图）
                    editImg.src = data.new_thumbnail_url;
                    editImg.style.display = 'block';
                    uploadBtn.querySelector('span').textContent = '更换缩略图';
                    removeBtn.style.display = 'inline-block';
                } else {
                    // 完全没有视频
                    editImg.style.display = 'none';
                    uploadBtn.querySelector('span').textContent = '上传缩略图';
                    removeBtn.style.display = 'none';
                }

                // 显示详细的删除结果消息
                let message = '缩略图已删除';
                if (data.file_deleted) {
                    message += '，自定义缩略图文件已从服务器删除';
                } else {
                    message += '，缩略图文件不存在';
                }
                if (data.new_thumbnail_url) {
                    message += '，已自动使用第一张视频缩略图';
                } else {
                    message += '，分组无视频内容';
                }
                showToast('success', message);

                loadCategoryVideos(currentEditingCategory);

                // 更新左侧分组列表 - 修复占位符处理逻辑
                const categoryItem = document.querySelector(`[data-category="${currentEditingCategory}"]`);
                if (categoryItem) {
                    const existingThumb = categoryItem.querySelector('.category-thumbnail');
                    const existingPlaceholder = categoryItem.querySelector('.category-thumbnail-placeholder');

                    console.log('更新缩略图显示:', {
                        category: currentEditingCategory,
                        newThumbnailUrl: data.new_thumbnail_url,
                        hasExistingThumb: !!existingThumb,
                        hasExistingPlaceholder: !!existingPlaceholder
                    });

                    if (data.new_thumbnail_url) {
                        // 有新的缩略图（第一张视频缩略图）
                        const newThumbHtml = `<img src="${data.new_thumbnail_url}" alt="缩略图" class="category-thumbnail">`;
                        if (existingThumb) {
                            existingThumb.outerHTML = newThumbHtml;
                        } else if (existingPlaceholder) {
                            existingPlaceholder.outerHTML = newThumbHtml;
                        } else {
                            // 如果都没有，添加到category-item的开头
                            const categoryContent = categoryItem.querySelector('.category-content');
                            if (categoryContent) {
                                categoryContent.insertAdjacentHTML('beforebegin', newThumbHtml);
                            }
                        }
                    } else {
                        // 完全没有视频，显示占位符
                        const placeholderHtml = `
                            <div class="category-thumbnail-placeholder">
                                <i data-feather="video"></i>
                            </div>`;
                        if (existingThumb) {
                            existingThumb.outerHTML = placeholderHtml;
                        } else if (existingPlaceholder) {
                            existingPlaceholder.outerHTML = placeholderHtml;
                        } else {
                            // 如果都没有，添加到category-item的开头
                            const categoryContent = categoryItem.querySelector('.category-content');
                            if (categoryContent) {
                                categoryContent.insertAdjacentHTML('beforebegin', placeholderHtml);
                            }
                        }
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

function removePreviewVideo(button) {
    button.closest('.video-item').remove();
    const container = document.getElementById('new-videos-preview');
    // 检查是否还有视频项（除了上传按钮）
    const videoItems = container.querySelectorAll('.video-item');
    if (videoItems.length === 0) {
        // 确保上传按钮仍然存在
        let addBtn = container.querySelector('.add-video-btn');
        if (!addBtn) {
            addBtn = document.createElement('div');
            addBtn.className = 'add-video-btn';
            addBtn.innerHTML = '+';
            addBtn.title = '选择视频';
            addBtn.onclick = () => selectVideosFile('new');
            container.appendChild(addBtn);
        }
    }
}

function uploadVideos(categoryName, files) {
    const formData = new FormData();
    formData.append('category', categoryName);
    Array.from(files).forEach(file => formData.append('videos[]', file));

    const totalSize = (Array.from(files).reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(1);
    showToast('info', `正在上传 ${files.length} 个视频 (${totalSize}MB)...`);

    fetch(`${controllerUrl}&ajax=upload_videos`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message || '上传成功');
            loadCategoryVideos(categoryName);

            // 更新视频数量
            const countEl = document.querySelector(`[data-category="${categoryName}"] .category-item small`);
            if (countEl && data.total_count) {
                countEl.textContent = `${data.total_count} 个视频`;
            }

            // 如果是第一个视频且没有缩略图，自动设置为缩略图
            if (data.auto_set_thumbnail || data.thumbnail_set) {
                showToast('info', '已自动设置第一张视频缩略图为分组缩略图');
                loadCategoryThumbnailImage(categoryName);

                // 更新左侧分组列表的缩略图
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

function deleteVideo(categoryName, videoTitle) {
    if (!confirm(`确定要删除视频 "${videoTitle}" 吗？\n此操作将永久删除原文件。`)) return;

    fetch(`${controllerUrl}&ajax=delete_video`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: categoryName,
            video: videoTitle
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log('删除视频响应:', data);
        if (data.success) {
            // 显示详细的删除结果消息
            let message = '视频已删除';
            if (data.video_deleted) {
                message += '，原视频文件已删除';
            } else {
                message += '，但原视频文件删除失败';
            }
            if (data.thumbnails_deleted > 0) {
                message += `，删除了 ${data.thumbnails_deleted} 个缩略图文件`;
            }
            showToast('success', message);

            // 强制重新加载视频列表
            loadCategoryVideos(categoryName).then(() => {
                console.log('视频列表重新加载完成');
            });

            // 更新视频数量
            const countEl = document.querySelector(`[data-category="${categoryName}"] .category-item small`);
            if (countEl && data.total_count !== undefined) {
                countEl.textContent = `${data.total_count} 个视频`;
                console.log('更新视频数量为:', data.total_count);
            }

            // 如果删除了视频，检查是否需要更新分组缩略图
            if (data.total_count === 0) {
                // 分组没有视频了，显示占位符
                const categoryThumb = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail`);
                const categoryPlaceholder = document.querySelector(`[data-category="${categoryName}"] .category-thumbnail-placeholder`);
                if (categoryThumb) {
                    categoryThumb.outerHTML = `
                        <div class="category-thumbnail-placeholder">
                            <i data-feather="video"></i>
                        </div>`;
                    feather.replace();
                    console.log('分组缩略图更新为占位符');
                } else if (categoryPlaceholder) {
                    // 已经是占位符了，确保图标正确
                    feather.replace();
                }
            }
            // 其他情况由loadCategoryVideos处理缩略图更新
        } else {
            showToast('danger', data.message || '删除失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '删除视频失败');
    });
}

function createNewCategory() {
    const categoryName = document.getElementById('new-category-name').value.trim();
    const displayName = document.getElementById('new-display-name').value.trim();
    const description = document.getElementById('new-description').value.trim();
    const position = document.getElementById('new-category-position').value;

    if (!categoryName) {
        showToast('danger', '请输入分组名称');
        return;
    }
    if (!/^[a-zA-Z0-9_-]+$/.test(categoryName)) {
        showToast('danger', '分组名称只能包含字母、数字、下划线和连字符');
        return;
    }

    const existing = Array.from(document.querySelectorAll('.category-item')).map(item => item.dataset.category);
    if (existing.includes(categoryName)) {
        showToast('danger', '分组名称已存在');
        return;
    }

    if (!confirm(`确定要创建分组 "${displayName || categoryName}" 吗？`)) {
        return;
    }

    const videoFiles = document.getElementById('videosFileInput').files;

    const formData = new FormData();
    formData.append('category', categoryName);
    formData.append('displayName', displayName);
    formData.append('description', description);
    formData.append('position', position);

    if (videoFiles.length > 0) {
        Array.from(videoFiles).forEach(file => formData.append('videos[]', file));
    }

    showToast('info', '正在创建分组...');

    fetch(`${controllerUrl}&ajax=create_category`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', '分组创建成功，正在更新列表...');

            // 动态添加新分组到列表中，而不是刷新整个页面
            addNewCategoryToList(data.category, displayName, description, data.thumbnail_info);

            // 清空表单
            document.getElementById('new-category-name').value = '';
            document.getElementById('new-display-name').value = '';
            document.getElementById('new-description').value = '';

            // 清空视频预览
            const previewContainer = document.getElementById('new-videos-preview');
            previewContainer.innerHTML = `
                <div class="add-video-btn" onclick="selectVideosFile('new')" title="上传视频">
                    +
                </div>
            `;

            // 清空文件输入
            document.getElementById('videosFileInput').value = '';

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

function scanVideoDirectory() {
    if (!confirm('确定要扫描视频目录吗？这将重新检测所有视频文件。')) return;

    showToast('info', '正在扫描视频目录...');

    fetch(`${controllerUrl}&ajax=scan_directory`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', `扫描完成，发现 ${data.groups} 个分组`);
            // 重新加载页面以显示最新数据
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('danger', data.message || '扫描失败');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('danger', '扫描失败');
    });
}

function showPhpConfigInfo() {
    fetch(`${controllerUrl}&ajax=php_config`)
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
    fetch(`${controllerUrl}&ajax=php_config`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'warning' && data.issues.length > 0) {
                showToast('warning', `PHP配置可能影响文件上传：${data.issues.slice(0, 2).join('；')}`);
            }
        })
        .catch(() => {});
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
