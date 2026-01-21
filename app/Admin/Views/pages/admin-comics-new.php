<?php
/**
 * Comics 管理页面 - 新版本
 * 使用标准化组件和三栏布局
 *
 * @version 1.1.0
 * @date 2026-01-22
 *
 * 功能特性:
 * - 三栏布局（列表 / 编辑 / 预览）
 * - 拖拽排序
 * - 图片管理（主图片 + 图标）
 * - 图标 hover 效果预览
 * - 完整 CRUD 操作
 *
 * v1.1.0 更新:
 * - ✅ 实现创建/编辑/删除漫画
 * - ✅ 实现图标上传（默认 + Hover）
 * - ✅ 实现主图片上传和删除
 * - ✅ 实现拖拽排序保存
 */

$page_title = $page_title ?? '🛠️ Comics 管理 (新版)';
$page_subtitle = $page_subtitle ?? '管理漫画作品与图片 - 使用新组件';
$_GET['page'] = $_GET['page'] ?? 'comics-new';

// 加载数据
if (!isset($comics)) {
    require_once __DIR__ . '/../../../app/Models/comic_data.php';
    $comics = get_all_comics_data();
}

// comics是关联数组，需要保留ID
// 按 order_id 排序但保留关联
uasort($comics, function($a, $b) {
    return ($a['order_id'] ?? 999) - ($b['order_id'] ?? 999);
});

$flashMessage = $_SESSION['comics_flash'] ?? null;
unset($_SESSION['comics_flash']);
$totalComics = count($comics);
?>

<!-- 引入新组件样式 -->
<link rel="stylesheet" href="/assets/admin/css/admin-common.css?v=2.0">
<link rel="stylesheet" href="/assets/admin/css/admin-three-column.css?v=2.0">
<link rel="stylesheet" href="/assets/admin/css/admin-image-manager.css?v=2.0">

<!-- 自定义样式（仅 Comics 特有部分） -->
<style>
/* 添加按钮样式（与 sketchbook 统一） */
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

/* Comics 特有：双图标预览（默认 + Hover） */
.comic-icon-preview {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.comic-icon-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    background: #f8f9fa;
}

.comic-icon-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comic-icon-item .icon-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    font-size: 10px;
    padding: 2px 4px;
    text-align: center;
}

.comic-icon-item .admin-image-action-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 4px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.comic-icon-item:hover .admin-image-action-btn {
    display: flex;
}

.comic-icon-item .admin-image-action-btn svg {
    width: 14px;
    height: 14px;
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

    <!-- 左栏：漫画列表 -->
    <div class="admin-left-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white position-relative">
                <h6 class="card-title mb-0">
                    <i data-feather="book-open" class="me-2"></i>
                    漫画列表
                </h6>
                <button type="button" class="add-category-btn" onclick="showAddPanel()" title="添加新漫画">+</button>
            </div>
            <div class="card-body">
                <ul class="admin-category-list list-unstyled mb-0" id="comicList">
                    <?php foreach ($comics as $id => $comic): ?>
                    <li class="admin-list-item" data-comic-id="<?= htmlspecialchars($id) ?>">
                        <div class="d-flex align-items-center p-3">
                            <span class="admin-drag-handle" title="拖拽排序">⋮⋮</span>

                            <!-- 缩略图（图标） -->
                            <?php if (!empty($comic['icon_default'])): ?>
                                <img src="<?= htmlspecialchars($comic['icon_default']) ?>" alt="图标" class="admin-thumbnail me-2">
                            <?php else: ?>
                                <div class="admin-thumbnail-placeholder me-2">
                                    <i data-feather="image"></i>
                                </div>
                            <?php endif; ?>

                            <!-- 信息 -->
                            <div class="flex-grow-1" style="cursor: pointer;" onclick="editComic('<?= htmlspecialchars($id) ?>')">
                                <div class="fw-semibold"><?= htmlspecialchars($comic['title'] ?: $id) ?></div>
                                <small class="admin-text-muted"><?= htmlspecialchars($comic['subtitle'] ?? '') ?></small>
                            </div>

                            <span class="admin-badge admin-badge-primary"><?= (int)($comic['order_id'] ?? 0) ?></span>
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
            <div class="card-header bg-white">
                <h5 class="card-title mb-0" id="edit-title">选择一个漫画开始编辑</h5>
                <small class="admin-text-muted" id="edit-status">从左侧列表选择</small>
            </div>
            <div class="card-body">

                <!-- 占位符 -->
                <div id="edit-placeholder" class="admin-empty-state">
                    <i data-feather="edit-3" class="admin-empty-icon"></i>
                    <p>从左侧列表选择一个漫画开始编辑</p>
                    <p class="admin-text-muted">或点击顶部 + 号添加新漫画</p>
                </div>

                <!-- 添加面板 -->
                <div id="add-panel" style="display: none;">
                    <form id="add-comic-form">
                        <div class="mb-3">
                            <label class="form-label">标题 *</label>
                            <input type="text" id="new-title" class="form-control" placeholder="漫画标题" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">副标题</label>
                            <input type="text" id="new-subtitle" class="form-control" placeholder="副标题">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">描述</label>
                            <textarea id="new-lines" class="form-control" rows="3" placeholder="描述文字，支持HTML"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ALT 文本</label>
                            <input type="text" id="new-alt" class="form-control" placeholder="图片alt属性">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">状态</label>
                            <select id="new-status" class="form-select">
                                <option value="active">启用</option>
                                <option value="inactive">禁用</option>
                            </select>
                        </div>

                        <!-- 图标上传 -->
                        <div class="mb-3">
                            <label class="form-label">默认图标</label>
                            <input type="file" id="new-icon-default" class="form-control" accept="image/*">
                            <small class="admin-text-muted">上传后会自动保存</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hover 图标 (可选)</label>
                            <input type="file" id="new-icon-hover" class="form-control" accept="image/*">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="admin-btn admin-btn-success" onclick="createComic()">
                                <i data-feather="plus"></i>创建漫画
                            </button>
                            <button type="button" class="admin-btn admin-btn-secondary" onclick="cancelAdd()">
                                <i data-feather="x"></i>取消
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 编辑面板 -->
                <div id="edit-panel" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">标题</label>
                        <input type="text" id="edit-title-input" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">副标题</label>
                        <input type="text" id="edit-subtitle" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">描述</label>
                        <textarea id="edit-lines" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ALT 文本</label>
                        <input type="text" id="edit-alt" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <select id="edit-status-select" class="form-select">
                            <option value="active">启用</option>
                            <option value="inactive">禁用</option>
                        </select>
                    </div>

                    <!-- 图标管理 -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i data-feather="star"></i>
                            图标（缩略图）
                        </label>
                        <div class="comic-icon-preview" id="iconPreview">
                            <!-- JS 动态生成 -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="selectIcons()">
                            <i data-feather="upload"></i> 上传图标
                        </button>
                        <small class="admin-text-muted d-block mt-1">默认图标 + Hover图标（可选）</small>
                    </div>

                    <!-- 主图片管理 -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i data-feather="image"></i>
                            漫画图片
                        </label>
                        <div id="imageManager" class="admin-thumbnail-grid-container">
                            <!-- JS 动态生成 -->
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="admin-btn admin-btn-primary" onclick="saveComic()">
                            <i data-feather="save"></i>保存
                        </button>
                        <button type="button" class="admin-btn admin-btn-danger" onclick="deleteComic()">
                            <i data-feather="trash-2"></i>删除
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 右栏：统计和工具 -->
    <div class="admin-right-panel">

        <!-- 统计卡片 -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="bar-chart-2" class="me-2"></i>
                    统计
                </h6>
            </div>
            <div class="card-body">
                <div class="admin-stat-item">
                    <span class="admin-stat-label">漫画总数</span>
                    <span class="admin-stat-value" id="total-comics"><?= $totalComics ?></span>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="zap" class="me-2"></i>
                    快速操作
                </h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <button class="admin-btn admin-btn-outline-primary btn-sm">
                    <i data-feather="refresh-cw"></i> 刷新数据
                </button>
                <button class="admin-btn admin-btn-outline-secondary btn-sm">
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
                    <p><strong>拖拽排序：</strong>按住⋮⋮拖拽列表项</p>
                    <p><strong>图标：</strong>默认图标 + Hover效果图标</p>
                    <p><strong>图片：</strong>可上传多张主要展示图片</p>
                </small>
            </div>
        </div>

    </div>

</div>


<!-- 版本提示 -->
<div class="alert alert-info alert-dismissible fade show">
    <i data-feather="info"></i>
    <strong>新版本 v1.1</strong> - ✓ 三栏布局 ✓ 拖拽排序 ✓ 图标管理 ✓ 完整CRUD。
    <a href="/admin?page=comics" class="alert-link">返回旧版本</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- 引入组件 JS -->
<script src="/assets/admin/js/admin-utils.js?v=1.0"></script>
<script src="/assets/admin/js/admin-drag-sort.js?v=1.0"></script>

<script>
// ==================== 数据初始化 ====================
const controllerUrl = '/admin/ajax?controller=comics';
const comicsData = <?php echo json_encode($comics, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;

// 转换为数组格式（保留ID）
const comicsList = [];
for (const [id, data] of Object.entries(comicsData)) {
    comicsList.push({ ...data, id });
}

console.log('Comics数据加载:', comicsList.length, '条');

// 当前编辑的漫画ID
let currentComicId = null;

// ==================== 页面初始化 ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM加载完成');

    // 初始化拖拽排序
    if (typeof AdminDragSort !== 'undefined') {
        const dragSort = new AdminDragSort('comicList', {
            itemSelector: '.admin-list-item',
            handleSelector: '.admin-drag-handle',
            onReorder: saveComicOrder
        });
        console.log('✓ 拖拽排序已初始化');
    } else {
        console.warn('✗ AdminDragSort未加载');
    }

    // 初始化feather图标
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// ==================== 排序保存 ====================
function saveComicOrder(order) {
    const comicIds = order.map(item => item.id);
    console.log('保存排序:', comicIds);

    const formData = new FormData();
    formData.append('ajax_action', 'reorder_comics');
    formData.append('order', JSON.stringify(comicIds));

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage('排序已保存', 'success');
            }
        } else {
            console.error('排序保存失败:', data.error);
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.error || '保存失败', 'error');
            }
        }
    })
    .catch(err => {
        console.error('排序保存失败:', err);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('保存失败', 'error');
        }
    });
}

// ==================== 显示添加面板 ====================
function showAddPanel() {
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('add-panel').style.display = 'block';

    document.getElementById('edit-title').textContent = '添加新漫画';
    document.getElementById('edit-status').textContent = '创建新的漫画作品';

    // 清空表单
    document.getElementById('new-title').value = '';
    document.getElementById('new-subtitle').value = '';
    document.getElementById('new-lines').value = '';
    document.getElementById('new-alt').value = '';
    document.getElementById('new-status').value = 'active';
    if (document.getElementById('new-icon-default')) {
        document.getElementById('new-icon-default').value = '';
    }
    if (document.getElementById('new-icon-hover')) {
        document.getElementById('new-icon-hover').value = '';
    }
}

// ==================== 取消添加 ====================
function cancelAdd() {
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-placeholder').style.display = 'block';
    document.getElementById('edit-title').textContent = '选择一个漫画开始编辑';
    document.getElementById('edit-status').textContent = '从左侧列表选择';
}

// ==================== 编辑漫画 ====================
function editComic(comicId) {
    console.log('编辑漫画:', comicId);

    const comic = comicsList.find(c => c.id === comicId);
    if (!comic) {
        console.error('未找到漫画:', comicId);
        return;
    }

    currentComicId = comicId;

    // 更新左侧激活状态
    document.querySelectorAll('.admin-list-item').forEach(item => {
        item.classList.toggle('active', item.dataset.comicId === comicId);
    });

    // 显示编辑面板
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'block';

    // 更新标题
    document.getElementById('edit-title').textContent = comic.title || comicId;
    document.getElementById('edit-status').textContent = '编辑漫画信息';

    // 填充表单
    document.getElementById('edit-title-input').value = comic.title || '';
    document.getElementById('edit-subtitle').value = comic.subtitle || '';
    document.getElementById('edit-lines').value = comic.lines || '';
    document.getElementById('edit-alt').value = comic.alt || '';
    document.getElementById('edit-status-select').value = comic.status || 'active';

    // 加载图标
    loadIcons(comic);

    // 加载主图片
    loadImages(comic);

    // 刷新图标
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    console.log('✓ 编辑面板已显示');
}

// ==================== 加载图标预览 ====================
function loadIcons(comic) {
    const container = document.getElementById('iconPreview');
    if (!container) return;

    container.innerHTML = '';

    if (comic.icon_default) {
        container.innerHTML += `
            <div class="comic-icon-item">
                <img src="${comic.icon_default}" alt="默认图标">
                <div class="icon-label">默认</div>
                <button class="admin-image-action-btn delete" onclick="deleteIcon('icon_default')" title="删除">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `;
    }

    if (comic.icon_hover) {
        container.innerHTML += `
            <div class="comic-icon-item">
                <img src="${comic.icon_hover}" alt="Hover图标">
                <div class="icon-label">Hover</div>
                <button class="admin-image-action-btn delete" onclick="deleteIcon('icon_hover')" title="删除">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
        `;
    }

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// ==================== 加载主图片 ====================
function loadImages(comic) {
    const container = document.getElementById('imageManager');
    if (!container) return;

    container.innerHTML = '';

    const grid = document.createElement('div');
    grid.className = 'admin-image-grid';

    // 添加按钮
    grid.innerHTML = `
        <div class="admin-add-image-btn" onclick="selectMainImages()" title="上传图片">+</div>
    `;

    // 渲染图片
    (comic.images || []).forEach((imgPath, index) => {
        const item = document.createElement('div');
        item.className = 'admin-image-item';
        item.innerHTML = `
            <img src="${imgPath}" alt="图片 ${index + 1}" loading="lazy">
            <button class="admin-image-action-btn delete" onclick="deleteMainImage('${imgPath}')" title="删除">
                <i data-feather="trash-2"></i>
            </button>
        `;
        grid.appendChild(item);
    });

    container.appendChild(grid);

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// ==================== 图标上传 ====================
function selectIcons() {
    if (!currentComicId) {
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('请先选择一个漫画', 'warning');
        }
        return;
    }

    const html = `
        <div class="modal fade" id="iconModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">上传图标</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">默认图标 *</label>
                            <input type="file" id="iconDefaultFile" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hover 图标 (可选)</label>
                            <input type="file" id="iconHoverFile" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" onclick="uploadIcons()">上传</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
    const modal = new bootstrap.Modal(document.getElementById('iconModal'));
    modal.show();

    document.getElementById('iconModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

async function uploadIcons() {
    const defaultFile = document.getElementById('iconDefaultFile').files[0];
    const hoverFile = document.getElementById('iconHoverFile').files[0];

    if (!defaultFile) {
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('请选择默认图标', 'warning');
        }
        return;
    }

    // 上传默认图标
    await uploadSingleIcon(defaultFile, 'icon_default');

    // 上传Hover图标
    if (hoverFile) {
        await uploadSingleIcon(hoverFile, 'icon_hover');
    }

    // 关闭模态框并刷新
    bootstrap.Modal.getInstance(document.getElementById('iconModal')).hide();
    setTimeout(() => location.reload(), 500);
}

async function uploadSingleIcon(file, field) {
    const formData = new FormData();
    formData.append('ajax_action', 'upload_image');
    formData.append('comic_id', currentComicId);
    formData.append('field', field);
    formData.append('image', file);

    try {
        const res = await fetch(controllerUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.ok) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.message || '上传成功', 'success');
            }
            return true;
        } else {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.error || '上传失败', 'error');
            }
            return false;
        }
    } catch (err) {
        console.error('上传失败:', err);
        return false;
    }
}

// ==================== 删除图标 ====================
async function deleteIcon(field) {
    if (!currentComicId || !confirm('确定要删除这个图标吗？')) return;

    const comic = comicsList.find(c => c.id === currentComicId);
    if (!comic || !comic[field]) return;

    const formData = new FormData();
    formData.append('ajax_action', 'delete_image');
    formData.append('comic_id', currentComicId);
    formData.append('image_url', comic[field]);
    formData.append('field', field);

    try {
        const res = await fetch(controllerUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.ok) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage('删除成功', 'success');
            }
            setTimeout(() => location.reload(), 500);
        } else {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.error || '删除失败', 'error');
            }
        }
    } catch (err) {
        console.error('删除失败:', err);
    }
}

// ==================== 主图片上传 ====================
function selectMainImages() {
    if (!currentComicId) {
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('请先选择一个漫画', 'warning');
        }
        return;
    }

    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.accept = 'image/*';
    input.onchange = async (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage(`正在上传 ${files.length} 张图片...`, 'info');
        }

        for (const file of files) {
            await uploadMainImage(file);
        }

        setTimeout(() => location.reload(), 500);
    };
    input.click();
}

async function uploadMainImage(file) {
    const formData = new FormData();
    formData.append('ajax_action', 'upload_image');
    formData.append('comic_id', currentComicId);
    formData.append('field', 'images');
    formData.append('image', file);

    try {
        const res = await fetch(controllerUrl, { method: 'POST', body: formData });
        const data = await res.json();
        return data.ok;
    } catch (err) {
        console.error('上传失败:', err);
        return false;
    }
}

// ==================== 删除主图片 ====================
async function deleteMainImage(imgPath) {
    if (!currentComicId || !confirm('确定要删除这张图片吗？')) return;

    const formData = new FormData();
    formData.append('ajax_action', 'delete_image');
    formData.append('comic_id', currentComicId);
    formData.append('image_url', imgPath);
    formData.append('field', 'images');

    try {
        const res = await fetch(controllerUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.ok) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage('删除成功', 'success');
            }
            setTimeout(() => location.reload(), 500);
        } else {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.error || '删除失败', 'error');
            }
        }
    } catch (err) {
        console.error('删除失败:', err);
    }
}

// 保存排序
function saveOrder(order) {
    const comicOrder = order.map(item => item.id);

    console.log('保存漫画排序:', comicOrder);

    const formData = new FormData();
    formData.append('ajax_action', 'reorder_comics');
    formData.append('order', JSON.stringify(comicOrder));

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            AdminUtils.showMessage('排序已保存', 'success');
        } else {
            AdminUtils.showMessage(data.error || '保存失败', 'error');
        }
    })
    .catch(err => {
        console.error('保存排序失败:', err);
        AdminUtils.showMessage('保存失败', 'error');
    });
}

// ==================== 创建漫画 ====================
async function createComic() {
    const title = document.getElementById('new-title').value.trim();
    const subtitle = document.getElementById('new-subtitle').value.trim();
    const lines = document.getElementById('new-lines').value.trim();
    const alt = document.getElementById('new-alt').value.trim();
    const status = document.getElementById('new-status').value;

    if (!title) {
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('请输入标题', 'warning');
        }
        return;
    }

    try {
        // 创建空条目
        const formData = new FormData();
        formData.append('ajax_action', 'add_group');

        const res = await fetch(controllerUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.ok) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showMessage(data.error || '创建失败', 'error');
            }
            return;
        }

        const newId = data.id;

        // 上传图标（如果有）
        const iconDefaultFile = document.getElementById('new-icon-default')?.files[0];
        const iconHoverFile = document.getElementById('new-icon-hover')?.files[0];

        if (iconDefaultFile) {
            const fd = new FormData();
            fd.append('ajax_action', 'upload_image');
            fd.append('comic_id', newId);
            fd.append('field', 'icon_default');
            fd.append('image', iconDefaultFile);
            await fetch(controllerUrl, { method: 'POST', body: fd });
        }

        if (iconHoverFile) {
            const fd = new FormData();
            fd.append('ajax_action', 'upload_image');
            fd.append('comic_id', newId);
            fd.append('field', 'icon_hover');
            fd.append('image', iconHoverFile);
            await fetch(controllerUrl, { method: 'POST', body: fd });
        }

        // 更新基本信息
        const updateData = new FormData();
        updateData.append('action', 'update');
        updateData.append('id', newId);
        updateData.append('title', title);
        updateData.append('subtitle', subtitle);
        updateData.append('lines', lines);
        updateData.append('alt', alt);
        updateData.append('status', status);

        await fetch(controllerUrl, { method: 'POST', body: updateData });

        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('创建成功！', 'success');
        }
        setTimeout(() => location.reload(), 500);

    } catch (err) {
        console.error('创建失败:', err);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('创建失败', 'error');
        }
    }
}

// ==================== 保存漫画 ====================
async function saveComic() {
    if (!currentComicId) return;

    const title = document.getElementById('edit-title-input').value.trim();
    const subtitle = document.getElementById('edit-subtitle').value.trim();
    const lines = document.getElementById('edit-lines').value.trim();
    const alt = document.getElementById('edit-alt').value.trim();
    const status = document.getElementById('edit-status-select').value;

    if (!title) {
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('请输入标题', 'warning');
        }
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', currentComicId);
    formData.append('title', title);
    formData.append('subtitle', subtitle);
    formData.append('lines', lines);
    formData.append('alt', alt);
    formData.append('status', status);

    try {
        await fetch(controllerUrl, { method: 'POST', body: formData });

        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('保存成功！', 'success');
        }
        setTimeout(() => location.reload(), 500);
    } catch (err) {
        console.error('保存失败:', err);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('保存失败', 'error');
        }
    }
}

// ==================== 删除漫画 ====================
async function deleteComic() {
    if (!currentComicId || !confirm('确定要删除这个漫画吗？此操作不可恢复！')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', currentComicId);

    try {
        await fetch(controllerUrl, { method: 'POST', body: formData });

        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('删除成功！', 'success');
        }
        setTimeout(() => location.reload(), 500);
    } catch (err) {
        console.error('删除失败:', err);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showMessage('删除失败', 'error');
        }
    }
}

// ==================== 页面加载完成 ====================
console.log('Comics 管理页面 v2.0 已加载');
console.log('- ✓ 统一数据处理模式');
console.log('- ✓ 标准化CRUD操作');
console.log('- ✓ 简化代码结构');
console.log('Comics数据:', comicsList.length, '条');
console.log('AdminDragSort:', typeof AdminDragSort !== 'undefined' ? '✓' : '✗');
console.log('AdminUtils:', typeof AdminUtils !== 'undefined' ? '✓' : '✗');
</script>

<?php
// 返回视图给 AdminIndexController
?>
