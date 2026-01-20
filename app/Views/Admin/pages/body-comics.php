<?php
// 页面配置（供 header.php 使用）
$page_title = $page_title ?? '🛠️ 漫画配置';
$page_subtitle = $page_subtitle ?? '管理漫画分组和图片';
$_GET['page'] = $_GET['page'] ?? 'comics';

// 如果控制器没有传入 $comics，则尝试加载
if (!isset($comics)) {
    require_once __DIR__ . '/../../../Models/comic_data.php';
    $comics = get_all_comics_data();
}

// 页面消息提示
$message = $message ?? '';
$messageType = $messageType ?? '';
?>

<?php if (!empty($message)): ?>
<div class="container-fluid pt-3">
    <div class="alert alert-<?= htmlspecialchars($messageType ?: 'info') ?>">
        <?= htmlspecialchars($message) ?>
    </div>
</div>
<?php endif; ?>

<!-- 引入独立的 comic manager 样式 -->
<link rel="stylesheet" href="/assets/css/comic-manager.css">

<!-- Single-Works 风格的拖拽动效样式 -->
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
.category-row {
    transition: all 0.2s ease;
}
.category-item:hover .category-row {
    background: rgba(0,123,255,0.05);
}

/* 缩略图拖拽样式 */
.thumbnail-sortable {
    transition: all 0.3s ease;
    cursor: move;
}
.thumbnail-sortable.dragging {
    opacity: 0.5;
    transform: scale(1.05) rotate(3deg);
    z-index: 1000;
    box-shadow: 0 8px 25px rgba(0,0,0,0.4) !important;
}
.thumbnail-item {
    position: relative;
}

/* 漫画分组缩略图样式 */
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
}

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
</style>

<div class="container-fluid mt-4 admin-page-content">
    <!-- 顶部排序卡片已移除，排序 ID 已集成到编辑分组面板 -->
    <div class="row g-3">
        <!-- 左栏：分组/侧栏 -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white position-relative">
                    <h6 class="card-title mb-0">
                        <i data-feather="list" class="me-2"></i>
                        分组顺序
                    </h6>
                    <button type="button" class="add-category-btn" onclick="showAddComicModal()" title="添加新漫画">+</button>
                </div>
                <div class="card-body p-0">
                    <ul class="category-list list-unstyled mb-0" id="categoryOrder">
                        <?php $i = 0; foreach ($comics as $id => $c): $i++; ?>
                        <?php $imageCount = isset($c['images']) ? count($c['images']) : 0; ?>
                        <li class="category-item" data-category="<?= htmlspecialchars($id) ?>" data-id="<?= htmlspecialchars($id) ?>" draggable="true">
                            <div class="category-row d-flex align-items-center p-3">
                                <span class="drag-handle" title="拖拽排序">⋮⋮</span>
                                <!-- 漫画缩略图 -->
                                <?php 
                                $thumbnailSrc = '';
                                if (!empty($c['icon_default'])) {
                                    $thumbnailSrc = $c['icon_default'];
                                } elseif (!empty($c['images']) && count($c['images']) > 0) {
                                    $thumbnailSrc = $c['images'][0];
                                }
                                ?>
                                <?php if ($thumbnailSrc): ?>
                                    <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="缩略图" class="category-thumbnail">
                                <?php else: ?>
                                    <div class="category-thumbnail-placeholder">
                                        <i data-feather="image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="category-content d-flex align-items-center flex-grow-1" onclick="editComic('<?= htmlspecialchars($id) ?>')">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold category-name"><?= htmlspecialchars($c['title'] ?? $id) ?></div>
                                        <small class="text-muted"><?= $imageCount ?> 张图片</small>
                                    </div>
                                    <span class="badge bg-secondary"><?= isset($c['order_id']) ? (int)$c['order_id'] : $i ?></span>
                                </div>
                            </div>
                            <input type="hidden" name="display_names[<?= htmlspecialchars($id) ?>]" value="<?= htmlspecialchars($c['title']) ?>" class="display-name-input">
                            <input type="hidden" name="descriptions[<?= htmlspecialchars($id) ?>]" value="<?= htmlspecialchars($c['lines']) ?>" class="description-input">
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 中栏：编辑与卡片网格 -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i data-feather="edit-3" class="me-2" id="edit-icon"></i>
                        <span id="edit-title">编辑漫画</span>
                    </h6>
                    <small id="edit-status" class="opacity-75">选择左侧漫画进行编辑</small>
                </div>
                <div class="card-body">
                    <!-- 编辑容器：包含占位和表单，表单初始隐藏 -->
                    <div id="edit-panel">
                        <div id="edit-panel-placeholder" class="text-center text-muted py-4">
                            <i class="bi bi-arrow-left" style="width:48px; height:48px; opacity:0.5;"></i>
                            <p class="mt-3">点击左侧条目开始编辑</p>
                        </div>

                        <form method="POST" id="editComicFormInline" style="display:none;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="editComicId">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editTitle" class="form-label">标题</label>
                                        <input type="text" class="form-control" name="title" id="editTitle" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editSubtitle" class="form-label">副标题</label>
                                        <input type="text" class="form-control" name="subtitle" id="editSubtitle">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="editLines" class="form-label">描述信息</label>
                                    <textarea class="form-control" name="lines" id="editLines" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="editAlt" class="form-label">Alt文本</label>
                                    <input type="text" class="form-control" name="alt" id="editAlt">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editStatus" class="form-label">状态</label>
                                        <select class="form-select" name="status" id="editStatus">
                                            <option value="active">启用</option>
                                            <option value="inactive">禁用</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editOrderId" class="form-label">排序 ID</label>
                                        <div class="d-flex">
                                            <input type="number" class="form-control" name="order_id" id="editOrderId" min="0" step="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">图标 (默认 / 悬停)</label>
                                    <div class="icon-row">
                                        <div class="icon-box" id="icon-default-box">
                                            <img class="icon-img" id="icon-default-img" src="" alt="默认图标" />
                                        </div>
                                        <div class="icon-box" id="icon-hover-box">
                                            <img class="icon-img" id="icon-hover-img" src="" alt="悬停图标" />
                                        </div>
                                        <div class="icon-actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showIconUploadDialog('icon_default')">上传默认图标</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showIconUploadDialog('icon_hover')">上传悬停图标</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">图片管理</label>
                                    <div id="thumbnail-grid" class="thumbnail-area"></div>
                                    <div class="form-text">可上传/删除，并拖拽排序（会保存顺序）</div>
                                </div>
                                
                            </div>
                            <div class="form-actions d-flex gap-2">
                                <button type="submit" class="btn btn-primary">更新/添加漫画</button>
                                <button type="button" class="btn btn-danger" id="deleteComicBtn">删除</button>
                                <button type="button" class="btn btn-secondary ms-auto" id="closeEditPanel">关闭</button>
                            </div>
                        </form>
                    </div>

                    <div class="row mt-3" id="comicCards">
                        <!-- 保持空白：旧的卡片列表已移除。只在点击左侧分组时由 editComic(id) 填充并显示编辑面板 -->
                    </div>
                </div>
            </div>
        </div>

        <!-- 右栏：预览与管理 -->
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
                            <a href="/" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i data-feather="external-link" class="me-1"></i>前台页面
                            </a>
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i data-feather="code" class="me-1"></i>API数据
                            </a>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <div class="d-flex justify-content-between">
                                <span>总分组:</span>
                                <span><?= count($comics) ?></span>
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
                            <a href="#" class="btn btn-outline-warning btn-sm">
                                <i data-feather="trash-2" class="me-1"></i>回收站
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm">
                                <i data-feather="info" class="me-1"></i>PHP配置
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加漫画模态框 -->
<div class="modal fade" id="addComicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">添加新漫画</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addComicForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">标题 *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subtitle" class="form-label">副标题</label>
                            <input type="text" class="form-control" name="subtitle">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lines" class="form-label">描述内容</label>
                        <textarea class="form-control" name="lines" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alt" class="form-label">Alt文本</label>
                        <input type="text" class="form-control" name="alt">
                    </div>
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">漫画图片 *</label>
                        <input type="file" class="form-control" name="images[]" multiple accept="image/*" required>
                        <div class="form-text">支持上传多张图片，将存储在 public/assets/images/comic/ 目录</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="icon_default" class="form-label">默认图标</label>
                            <input type="file" class="form-control" name="icon_default" accept="image/*">
                            <div class="form-text">将存储在缩略图目录</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon_hover" class="form-label">悬停图标</label>
                            <input type="file" class="form-control" name="icon_hover" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">状态</label>
                        <select class="form-select" name="status">
                            <option value="active">启用</option>
                            <option value="inactive">禁用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">添加漫画</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 使用外部样式文件 comic-manager.css 管理样式 -->

<!-- edit panel moved into middle column -->

<script>
const comics = <?= json_encode($comics) ?>;

function showAddComicModal() {
    // 重置表单
    document.getElementById('addComicForm').reset();
    // 显示模态框
    const modal = new bootstrap.Modal(document.getElementById('addComicModal'));
    modal.show();
}

function editComic(id) {
    const comic = comics[id];
    if (!comic) return;
    // 填充内联编辑面板并显示
    document.getElementById('editComicId').value = id;
    document.getElementById('editTitle').value = comic.title || '';
    document.getElementById('editSubtitle').value = comic.subtitle || '';
    document.getElementById('editLines').value = comic.lines || '';
    document.getElementById('editAlt').value = comic.alt || '';
    document.getElementById('editStatus').value = comic.status || 'active';
    // 填充并显示可编辑的 order_id（与状态同一行）
    const orderEl = document.getElementById('editOrderId');
    if (orderEl) orderEl.value = comic.order_id !== undefined ? comic.order_id : '';
    // 隐藏占位，显示表单
    const placeholder = document.getElementById('edit-panel-placeholder');
    const form = document.getElementById('editComicFormInline');
    if (placeholder) placeholder.style.display = 'none';
    if (form) form.style.display = 'block';
    // 填充图标预览（删除按钮已移除）
    const def = document.getElementById('icon-default-img');
    const hov = document.getElementById('icon-hover-img');
    if (def) { 
        def.src = comic.icon_default || ''; 
        def.style.display = comic.icon_default ? 'block' : 'none'; 
    }
    if (hov) { 
        hov.src = comic.icon_hover || ''; 
        hov.style.display = comic.icon_hover ? 'block' : 'none'; 
    }
    renderThumbnails(id);
}

function deleteComic(id) {
    const comic = comics[id];
    if (!comic) return;
    
    const imageCount = (comic.images || []).length;
    const hasIcons = (comic.icon_default || comic.icon_hover) ? true : false;
    let warningMsg = `确定要删除漫画"${comic.title}"吗？\n\n警告：此操作将删除：\n- 数据记录\n- 所有主图片文件 (${imageCount}个)`;
    if (hasIcons) {
        warningMsg += '\n- 图标文件';
    }
    warningMsg += '\n\n此操作无法撤销！';
    
    if (confirm(warningMsg)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<script>
// 初始化拖拽排序 - 使用single-works风格的丰富动效
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();

    function initializeDragAndDrop() {
        const sideItems = document.getElementById('categoryOrder');
        let draggedItem = null;

        document.querySelectorAll('#categoryOrder .category-item').forEach(item => {
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
                document.querySelectorAll('#categoryOrder .category-item').forEach(el => el.classList.remove('drag-over'));
                draggedItem = null;
                saveSidebarOrder();
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!draggedItem || draggedItem === this) return;
                const rect = this.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                document.querySelectorAll('#categoryOrder .category-item').forEach(el => el.classList.remove('drag-over'));
                if (e.clientY < midY) {
                    sideItems.insertBefore(draggedItem, this);
                } else {
                    sideItems.insertBefore(draggedItem, this.nextSibling);
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

    // 重新初始化拖拽的函数（在加载新数据后调用）
    window.reinitializeDragAndDrop = initializeDragAndDrop;

    function saveSidebarOrder() {
        const ids = [...document.querySelectorAll('#categoryOrder .category-item')].map(el=>el.dataset.id);
        const fd = new FormData();
        fd.append('ajax_action','reorder_comics');
        fd.append('order', JSON.stringify(ids));
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (!res.ok) showToast('错误', res.error || '排序保存失败', 'danger');
        });
    }

    // 使用事件委托：点击左侧项打开编辑（适用于动态插入的项）
    sideItems.addEventListener('click', function(e){
        const cc = e.target.closest('.category-content');
        if (cc && cc.dataset && cc.dataset.id) {
            openEditFromSidebar(cc.dataset.id);
        }
    });
});

// Toast container
const toastContainer = document.createElement('div');
toastContainer.id = 'toastContainer';
toastContainer.style.position = 'fixed';
toastContainer.style.right = '20px';
toastContainer.style.bottom = '20px';
toastContainer.style.zIndex = '2000';
document.body.appendChild(toastContainer);

function showToast(title, message, type='info'){
    const colors = { info: 'bg-primary text-white', success: 'bg-success text-white', danger: 'bg-danger text-white' };
    const t = document.createElement('div');
    t.className = `toast ${colors[type] || colors.info}`;
    t.style.minWidth = '240px';
    t.style.marginTop = '8px';
    t.style.padding = '10px';
    t.innerHTML = `<strong>${title}</strong><div style="font-size:13px;">${message}</div>`;
    toastContainer.appendChild(t);
    setTimeout(()=>{ t.style.opacity = '0'; setTimeout(()=>t.remove(),300); }, 3000);
}

function openEditFromSidebar(id) {
    // 找到并打开编辑模态
    editComic(id);
    // 高亮选中项
    document.querySelectorAll('#categoryOrder .category-item').forEach(el=>el.classList.remove('active'));
    const sel = document.querySelector('#categoryOrder .category-item[data-id="'+id+'"]');
    if (sel) sel.classList.add('active');
    // 在编辑模态内渲染缩略图网格
    // renderThumbnails 已在 editComic 中调用
}

// 关闭编辑面板
document.getElementById('closeEditPanel').addEventListener('click', function(){
    const placeholder = document.getElementById('edit-panel-placeholder');
    const form = document.getElementById('editComicFormInline');
    if (form) form.style.display = 'none';
    if (placeholder) placeholder.style.display = 'block';
});

// 删除按钮（内联）
document.getElementById('deleteComicBtn').addEventListener('click', function(){
    const id = document.getElementById('editComicId').value;
    if (!id) return;
    deleteComic(id); // 使用优化后的函数
});

// 编辑表单提交（保留原有 POST 提交逻辑）
document.getElementById('editComicFormInline').addEventListener('submit', function(e){
    // allow normal POST submit to update
});

function renderThumbnails(id) {
    const comic = comics[id];
    if (!comic) return;
    // 如果编辑模态未打开，先打开
    const grid = document.getElementById('thumbnail-grid');
    if (!grid) return;
    grid.innerHTML = '';
    (comic.images || []).forEach(url=>{
        const el = document.createElement('div');
        el.className = 'thumbnail-item';
        el.dataset.url = url;
        el.innerHTML = `<img src="${url}" />`;
        const del = document.createElement('button');
        del.className = 'btn btn-sm btn-danger del-btn';
        del.innerHTML = '&times;';
        del.title = '删除';
        del.addEventListener('click', function(e){ e.stopPropagation(); ajaxDeleteImage(id, url); });
        el.appendChild(del);
        grid.appendChild(el);
    });
    // 添加上传按钮
    const add = document.createElement('div');
    add.className = 'add-image-btn';
    add.innerHTML = '+';
    add.onclick = function(){ showUploadDialog(id); };
    grid.insertBefore(add, grid.firstChild);

    // save order after uploads/deletes as well
    function saveThumbnailOrder(comicId) {
        const urls = [...grid.querySelectorAll('.thumbnail-item')].map(el=>el.dataset.url);
        const fd = new FormData();
        fd.append('ajax_action','reorder_images');
        fd.append('comic_id', comicId);
        fd.append('order', JSON.stringify(urls));
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (!res.ok) showToast('错误', res.error || '缩略图排序保存失败', 'danger');
            else {
                comics[comicId].images = urls;
                const thumb = document.querySelector('#categoryOrder .category-item[data-id="'+comicId+'"] .side-thumb');
                if (thumb) thumb.src = urls[0] || '/assets/images/comic/thumbs/placeholder.png';
                showToast('已保存','缩略图顺序已保存','success');
            }
        });
    }

    // 初始化原生拖拽排序 - Single-Works 风格
    function initThumbnailSortable() {
        const thumbnails = grid.querySelectorAll('.thumbnail-item');
        let draggedThumbnail = null;

        thumbnails.forEach(thumbnail => {
            thumbnail.draggable = true;
            thumbnail.classList.add('thumbnail-sortable');

            thumbnail.addEventListener('dragstart', function(e) {
                draggedThumbnail = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', '');
            });

            thumbnail.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedThumbnail = null;
                saveThumbnailOrder(id);
            });

            thumbnail.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!draggedThumbnail || draggedThumbnail === this) return;
                const rect = this.getBoundingClientRect();
                const midX = rect.left + rect.width / 2;
                if (e.clientX < midX) {
                    grid.insertBefore(draggedThumbnail, this);
                } else {
                    grid.insertBefore(draggedThumbnail, this.nextSibling);
                }
            });

            thumbnail.addEventListener('drop', function(e) {
                e.preventDefault();
            });
        });
    }

    initThumbnailSortable();
}

function showUploadDialog(id) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(){
        const file = input.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('ajax_action','upload_image');
        fd.append('comic_id', id);
        fd.append('image', file);
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (res.ok) {
                comics[id].images = comics[id].images || [];
                comics[id].images.push(res.url);
                renderThumbnails(id);
                const thumb = document.querySelector('#categoryOrder .category-item[data-id="'+id+'"] .side-thumb');
                if (thumb) thumb.src = res.url;
                showToast('已上传','图片上传成功','success');
            } else showToast('错误', res.error||'上传失败', 'danger');
        });
    };
    input.click();
}

function ajaxDeleteImage(id, url) {
    if (!confirm('确定删除这张图片吗？\n\n注意：此操作将同时删除数据记录和源文件，无法撤销！')) return;
    const fd = new FormData();
    fd.append('ajax_action','delete_image');
    fd.append('comic_id', id);
    fd.append('image_url', url);
    fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
        if (res.ok) {
            comics[id].images = (comics[id].images||[]).filter(i=>i!==url);
            renderThumbnails(id);
            const message = res.message || '图片已删除';
            showToast('已删除', message, 'success');
        } else showToast('错误', res.error||'删除失败','danger');
    }).catch(err => {
        console.error('删除图片请求失败:', err);
        showToast('错误', '网络请求失败', 'danger');
    });
}

function showIconUploadDialog(field) {
    const id = document.getElementById('editComicId').value;
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(){
        const file = input.files[0]; if (!file) return;
        const fd = new FormData(); fd.append('ajax_action','upload_image'); fd.append('comic_id', id); fd.append('field', field); fd.append('image', file);
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (res.ok) {
                // update local data and preview
                comics[id][field] = res.url;
                if (field === 'icon_default') {
                    const el = document.getElementById('icon-default-img'); el.src = res.url; el.style.display='block';
                } else {
                    const el = document.getElementById('icon-hover-img'); el.src = res.url; el.style.display='block';
                }
                const message = res.message || '图标上传成功';
                showToast('已上传', message, 'success');
            } else showToast('错误', res.error||'上传失败','danger');
        });
    };
    input.click();
}

// ajaxDeleteIcon函数已移除 - 图标删除功能已禁用


// 重建 order_id 按侧栏当前顺序
document.addEventListener('click', function(e){
    const btn = e.target && e.target.closest ? e.target.closest('#reindexBtn') : null;
    if (btn) {
        const ids = [...document.querySelectorAll('#categoryOrder .category-item')].map(el=>el.dataset.id);
        console.log('reindex requested, ids=', ids);
        if (!ids || ids.length === 0) { showToast('错误', '未找到侧栏分组，请确认侧栏 DOM 是否存在', 'danger'); return; }
        // disable button during request
        btn.disabled = true; btn.classList.add('disabled');
        const fd = new FormData(); fd.append('ajax_action','reindex_order_id'); fd.append('order', JSON.stringify(ids));
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            btn.disabled = false; btn.classList.remove('disabled');
            if (res.ok) {
                showToast('已完成','排序ID 已根据侧栏重新编号','success');
                // 简单刷新页面以更新侧栏Badge和本地数据
                setTimeout(()=>location.reload(), 600);
            } else {
                console.error('reindex failed', res);
                showToast('错误', res.error||'重建失败','danger');
            }
        }).catch(err=>{
            btn.disabled = false; btn.classList.remove('disabled');
            console.error('reindex request error', err);
            showToast('错误', '网络请求失败', 'danger');
        });
    }
});


</script>
