<?php
// public/admin/controllers/comic-manager.php

define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../../app/Models/comic_data.php';

// 处理POST请求
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $comicData = [
                'title' => trim($_POST['title'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'lines' => trim($_POST['lines'] ?? ''),
                'alt' => trim($_POST['alt'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // 处理图片上传
            $uploadDir = __DIR__ . '/../../assets/images/comic/';
            $thumbsDir = $uploadDir . 'thumbs/';
            
            // 确保目录存在
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!is_dir($thumbsDir)) mkdir($thumbsDir, 0755, true);
            
            $images = [];
            $icons = [];
            
            // 处理主图片上传
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        // 使用原文件名
                        $originalName = pathinfo($filename, PATHINFO_FILENAME);
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $newFilename = $originalName . '.' . $ext;
                        
                        // 如果文件已存在，添加数字后缀
                        $counter = 1;
                        while (file_exists($uploadDir . $newFilename)) {
                            $newFilename = $originalName . '_' . $counter . '.' . $ext;
                            $counter++;
                        }
                        
                        $uploadPath = $uploadDir . $newFilename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                            $images[] = '/assets/images/comic/' . $newFilename;
                        }
                    }
                }
            }
            
            // 处理图标上传
            if (!empty($_FILES['icon_default']['name'])) {
                $originalName = pathinfo($_FILES['icon_default']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['icon_default']['name'], PATHINFO_EXTENSION);
                $iconDefault = $originalName . '-icon-default.' . $ext;
                
                // 如果文件已存在，添加数字后缀
                $counter = 1;
                while (file_exists($thumbsDir . $iconDefault)) {
                    $iconDefault = $originalName . '-icon-default_' . $counter . '.' . $ext;
                    $counter++;
                }
                
                if (move_uploaded_file($_FILES['icon_default']['tmp_name'], $thumbsDir . $iconDefault)) {
                    $comicData['icon_default'] = '/assets/images/comic/thumbs/' . $iconDefault;
                }
            }
            
            if (!empty($_FILES['icon_hover']['name'])) {
                $originalName = pathinfo($_FILES['icon_hover']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['icon_hover']['name'], PATHINFO_EXTENSION);
                $iconHover = $originalName . '-icon-hover.' . $ext;
                
                // 如果文件已存在，添加数字后缀
                $counter = 1;
                while (file_exists($thumbsDir . $iconHover)) {
                    $iconHover = $originalName . '-icon-hover_' . $counter . '.' . $ext;
                    $counter++;
                }
                
                if (move_uploaded_file($_FILES['icon_hover']['tmp_name'], $thumbsDir . $iconHover)) {
                    $comicData['icon_hover'] = '/assets/images/comic/thumbs/' . $iconHover;
                }
            }
            
            $comicData['images'] = $images;
            
            if (add_comic($comicData)) {
                $message = '漫画添加成功！';
                $messageType = 'success';
            } else {
                $message = '添加失败，请重试。';
                $messageType = 'danger';
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $comicData = [
                'title' => trim($_POST['title'] ?? ''),
                'subtitle' => trim($_POST['subtitle'] ?? ''),
                'lines' => trim($_POST['lines'] ?? ''),
                'alt' => trim($_POST['alt'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            if (update_comic($id, $comicData)) {
                $message = '漫画更新成功！';
                $messageType = 'success';
            } else {
                $message = '更新失败，请重试。';
                $messageType = 'danger';
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            if (delete_comic($id)) {
                $message = '漫画删除成功！';
                $messageType = 'success';
            } else {
                $message = '删除失败，请重试。';
                $messageType = 'danger';
            }
            break;
    }
}

// 获取所有漫画
$comics = get_all_comics_data();

// 读取并应用自定义顺序（storage/comic_order.json）
$orderFile = __DIR__ . '/../../../storage/comic_order.json';
if (file_exists($orderFile)) {
    $orderJson = file_get_contents($orderFile);
    $orderArr = json_decode($orderJson, true);
    if (is_array($orderArr) && count($orderArr) > 0) {
        $ordered = [];
        foreach ($orderArr as $cid) {
            if (isset($comics[$cid])) $ordered[$cid] = $comics[$cid];
        }
        // append any missing comics
        foreach ($comics as $k=>$v) if (!isset($ordered[$k])) $ordered[$k] = $v;
        $comics = $ordered;
    }
}

// 处理AJAX图片操作（单图上传/删除/排序）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $ajax = $_POST['ajax_action'];
    header('Content-Type: application/json');

    if ($ajax === 'upload_image') {
        $comicId = $_POST['comic_id'] ?? '';
        if (!$comicId || !isset($_FILES['image'])) {
            echo json_encode(['ok' => false, 'error' => '参数缺失']); exit;
        }
        $uploadDir = __DIR__ . '/../../assets/images/comic/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'上传失败']); exit; }
        $orig = pathinfo($file['name'], PATHINFO_FILENAME);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new = $orig . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $new)) {
            $url = '/assets/images/comic/' . $new;
            // 更新数据
            $all = get_all_comics_data();
            if (isset($all[$comicId])) {
                $all[$comicId]['images'][] = $url;
                save_comics_data($all);
            }
            echo json_encode(['ok' => true, 'url' => $url]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'保存失败']); exit;
    }

    if ($ajax === 'delete_image') {
        $comicId = $_POST['comic_id'] ?? '';
        $imageUrl = $_POST['image_url'] ?? '';
        if (!$comicId || !$imageUrl) { echo json_encode(['ok'=>false,'error'=>'参数缺失']); exit; }
        $all = get_all_comics_data();
        if (!isset($all[$comicId])) { echo json_encode(['ok'=>false,'error'=>'未找到漫画']); exit; }
        $idx = array_search($imageUrl, $all[$comicId]['images'] ?? []);
        if ($idx !== false && $idx !== null) {
            array_splice($all[$comicId]['images'], $idx, 1);
            save_comics_data($all);
            // 尝试删除文件
            $localPath = __DIR__ . '/../../' . ltrim($imageUrl, '/');
            if (file_exists($localPath)) @unlink($localPath);
            echo json_encode(['ok'=>true]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'图片未找到']); exit;
    }

    if ($ajax === 'reorder_images') {
        $comicId = $_POST['comic_id'] ?? '';
        $order = $_POST['order'] ?? '';
        if (!$comicId || $order === '') { echo json_encode(['ok'=>false,'error'=>'参数缺失']); exit; }
        $orderArr = json_decode($order, true);
        if (!is_array($orderArr)) { echo json_encode(['ok'=>false,'error'=>'参数格式错误']); exit; }
        $all = get_all_comics_data();
        if (!isset($all[$comicId])) { echo json_encode(['ok'=>false,'error'=>'未找到漫画']); exit; }
        $all[$comicId]['images'] = $orderArr;
        save_comics_data($all);
        echo json_encode(['ok'=>true]); exit;
    }
    if ($ajax === 'add_group') {
        // 创建一个空的分组条目并保存
        $all = get_all_comics_data();
        // 生成唯一ID
        $newId = 'g' . time();
        $title = 'New Group';
        $all[$newId] = [
            'title' => $title,
            'subtitle' => '',
            'lines' => '',
            'alt' => '',
            'status' => 'inactive',
            'images' => []
        ];
        if (save_comics_data($all)) {
            echo json_encode(['ok'=>true, 'id'=>$newId, 'title'=>$title]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'创建失败']); exit;
    }
    if ($ajax === 'reorder_comics') {
        $order = $_POST['order'] ?? '';
        $orderArr = json_decode($order, true);
        if (!is_array($orderArr)) { echo json_encode(['ok'=>false,'error'=>'参数格式错误']); exit; }
        $orderFile = __DIR__ . '/../../../storage/comic_order.json';
        if (!is_dir(dirname($orderFile))) mkdir(dirname($orderFile), 0755, true);
        if (file_put_contents($orderFile, json_encode($orderArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode(['ok'=>true]); exit;
        }
        echo json_encode(['ok'=>false,'error'=>'写入失败']); exit;
    }

    echo json_encode(['ok'=>false,'error'=>'未知操作']); exit;
}

require_once __DIR__ . '/../views/layouts/header.php';
// 引入独立的 comic manager 样式
echo '<link rel="stylesheet" href="/assets/css/comic-manager.css">';
?>

<div class="container-fluid mt-4 admin-page-content">
    <!-- 排序方式卡片 -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="bi bi-gear-fill me-2"></i> 排序方式设置</h5>
        </div>
        <div class="card-body">
            <form method="post" id="configForm">
                <div class="row">
                    <div class="col-md-6">
                        <label for="sort_method" class="form-label">排序方式</label>
                        <select name="sort_method" id="sort_method" class="form-select">
                            <option value="custom_order">自定义排序（拖拽调整）</option>
                            <option value="alphabetical">字母排序（A-Z）</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <!-- 左栏：分组/侧栏 -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white position-relative">
                    <h6 class="card-title mb-0"><i class="bi bi-list me-2"></i> 分组顺序</h6>
                    <button id="addGroupBtn" class="add-category-btn" title="添加新分组">+</button>
                </div>
                <div class="card-body p-0">
                    <ul class="category-list list-unstyled mb-0" id="sideItems">
                        <?php $i = 0; foreach ($comics as $id => $c): $i++; ?>
                        <?php $imageCount = isset($c['images']) ? count($c['images']) : 0; ?>
                        <li class="category-item" data-id="<?= htmlspecialchars($id) ?>">
                            <div class="category-row d-flex align-items-center p-3">
                                <span class="drag-handle" title="拖拽排序">⋮⋮</span>
                                <?php $thumb = isset($c['icon_default']) && $c['icon_default'] ? $c['icon_default'] : '/assets/images/comic/thumbs/placeholder.png'; ?>
                                <img class="side-thumb me-2" src="<?= htmlspecialchars($thumb) ?>" alt="">
                                <div class="category-content d-flex align-items-center flex-grow-1" data-id="<?= htmlspecialchars($id) ?>">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold category-name"><?= htmlspecialchars($c['title'] ?? $id) ?></div>
                                        <small class="text-muted"><?= $imageCount ?> 张图片</small>
                                    </div>
                                    <span class="badge bg-secondary"><?= $i ?></span>
                                </div>
                            </div>
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
                    <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> 编辑分组</h6>
                    <small id="edit-status" class="opacity-75">选择左侧分组进行编辑</small>
                </div>
                <div class="card-body">
                    <!-- 编辑容器：包含占位和表单，表单初始隐藏 -->
                    <div id="edit-panel">
                        <div id="edit-panel-placeholder" class="text-center text-muted py-4">
                            <i class="bi bi-arrow-left" style="width:48px; height:48px; opacity:0.5;"></i>
                            <p class="mt-3">点击左侧分组开始编辑</p>
                        </div>

                        <form method="POST" id="editComicFormInline" style="display:none;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="editComicId">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editTitle" class="form-label">显示名称</label>
                                        <input type="text" class="form-control" name="title" id="editTitle" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editSubtitle" class="form-label">文件夹名</label>
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
                                <div class="mb-3">
                                    <label for="editStatus" class="form-label">状态</label>
                                    <select class="form-select" name="status" id="editStatus">
                                        <option value="active">启用</option>
                                        <option value="inactive">禁用</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">图片管理</label>
                                    <div id="thumbnail-grid" class="thumbnail-area"></div>
                                    <div class="form-text">可上传/删除，并拖拽排序（会保存顺序）</div>
                                </div>
                            </div>
                            <div class="form-actions d-flex gap-2">
                                <button type="submit" class="btn btn-primary">更新漫画</button>
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
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white"><i class="bi bi-eye me-2"></i> 预览</div>
                <div class="card-body">
                    <a href="/" class="d-block mb-2"><i class="bi bi-box-arrow-up-right"></i> 前台页面</a>
                    <a href="#" class="d-block"><i class="bi bi-code"></i> API数据</a>
                    <hr>
                    <div>总分组: <?= count($comics) ?></div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background:#f4c542; color:#222;"><i class="bi bi-gear me-2"></i> 管理</div>
                <div class="card-body">
                    <a href="#" class="d-block"><i class="bi bi-trash"></i> 回收站</a>
                    <a href="#" class="d-block"><i class="bi bi-gear"></i> PHP配置</a>
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
            <form method="POST" enctype="multipart/form-data">
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
    // 隐藏占位，显示表单
    const placeholder = document.getElementById('edit-panel-placeholder');
    const form = document.getElementById('editComicFormInline');
    if (placeholder) placeholder.style.display = 'none';
    if (form) form.style.display = 'block';
    renderThumbnails(id);
}

function deleteComic(id) {
    const comic = comics[id];
    if (!comic) return;
    
    if (confirm(`确定要删除漫画"${comic.title}"吗？此操作无法撤销。`)) {
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
// 初始化 Sortable for side list (绑定到服务器渲染的 #sideItems)
document.addEventListener('DOMContentLoaded', function() {
    const sideItems = document.getElementById('sideItems');
    function initSideSortable() {
        if (typeof Sortable !== 'undefined') {
            Sortable.create(sideItems, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() { saveSidebarOrder(); }
            });
        } else {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
            s.onload = initSideSortable;
            document.head.appendChild(s);
        }
    }
    initSideSortable();

    function saveSidebarOrder() {
        const ids = [...document.querySelectorAll('#sideItems .category-item')].map(el=>el.dataset.id);
        const fd = new FormData();
        fd.append('ajax_action','reorder_comics');
        fd.append('order', JSON.stringify(ids));
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (!res.ok) showToast('错误', res.error || '排序保存失败', 'danger');
        });
    }

    // 点击左侧项打开编辑
    document.querySelectorAll('#sideItems .category-content').forEach(el=>{
        el.addEventListener('click', function(){ openEditFromSidebar(this.dataset.id); });
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

// Add group button
document.addEventListener('click', function(e){
    if (e.target && e.target.id === 'addGroupBtn'){
        const fd = new FormData(); fd.append('ajax_action','add_group');
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (res.ok) {
                // insert new side-item at top
                const sideItems = document.getElementById('sideItems');
                const div = document.createElement('div');
                div.className = 'side-item active';
                div.dataset.id = res.id;
                div.innerHTML = `<span class="drag-handle"><i class="bi bi-list"></i></span><img class="side-thumb" src="/assets/images/comic/thumbs/placeholder.png"><div style="flex:1"><div style="font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${res.title}</div><div style="font-size:12px;color:#666">0 张图片</div></div><div style="margin-left:8px;"><span class="badge bg-light text-muted" style="border-radius:12px; padding:6px 8px; font-size:12px">0</span></div>`;
                sideItems.insertBefore(div, sideItems.firstChild);
                // re-init sortable
                if (typeof Sortable !== 'undefined') try{ Sortable.create(sideItems, { handle: '.drag-handle', animation:150, ghostClass:'sortable-ghost', onEnd:function(){ saveSidebarOrder(); }}); }catch(e){}
                showToast('成功','已创建新分组','success');
            } else showToast('错误', res.error || '创建失败','danger');
        });
    }
});

function openEditFromSidebar(id) {
    // 找到并打开编辑模态
    editComic(id);
    // 高亮选中项
    document.querySelectorAll('#sideItems .side-item').forEach(el=>el.classList.remove('active'));
    const sel = document.querySelector('#sideItems .side-item[data-id="'+id+'"]');
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
    if (confirm('确定删除该漫画吗？')) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(f);
        f.submit();
    }
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
                const thumb = document.querySelector('#sideItems .category-item[data-id="'+comicId+'"] .side-thumb');
                if (thumb) thumb.src = urls[0] || '/assets/images/comic/thumbs/placeholder.png';
                showToast('已保存','缩略图顺序已保存','success');
            }
        });
    }

    // 初始化 Sortable for thumbnail grid（销毁旧实例后重建）
    function initThumbnailSortable() {
        try {
            if (grid._sortable) { grid._sortable.destroy(); grid._sortable = null; }
        } catch (e) { /* ignore */ }
        if (typeof Sortable !== 'undefined') {
            grid._sortable = Sortable.create(grid, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(){ saveThumbnailOrder(id); }
            });
        } else {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
            s.onload = initThumbnailSortable;
            document.head.appendChild(s);
        }
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
                const thumb = document.querySelector('#sideItems .category-item[data-id="'+id+'"] .side-thumb');
                if (thumb) thumb.src = res.url;
                showToast('已上传','图片上传成功','success');
            } else showToast('错误', res.error||'上传失败', 'danger');
        });
    };
    input.click();
}

function ajaxDeleteImage(id, url) {
    if (!confirm('确定删除这张图片吗？')) return;
    const fd = new FormData();
    fd.append('ajax_action','delete_image');
    fd.append('comic_id', id);
    fd.append('image_url', url);
    fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
        if (res.ok) {
            comics[id].images = (comics[id].images||[]).filter(i=>i!==url);
            renderThumbnails(id);
            showToast('已删除','图片已删除','success');
        } else showToast('错误', res.error||'删除失败','danger');
    });
}

</script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>