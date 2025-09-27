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
?>

<div class="container mt-4">
    <!-- 侧边栏：可拖拽的漫画条目列表（缩略图在前） -->
    <style>
    /* 轻量化侧边栏样式，最小侵入性 */
    #comic-side-list {
        position: absolute;
        left: 20px;
        top: 100px;
        width: 260px;
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        padding: 10px;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        z-index: 20;
    }
    #comic-side-list .side-item { display:flex; align-items:center; gap:10px; padding:8px; border-radius:6px; margin-bottom:8px; cursor:grab; }
    #comic-side-list .side-item.dragging { opacity:0.7; transform:scale(1.01); box-shadow:0 8px 20px rgba(0,0,0,0.08); }
    #comic-side-list .side-thumb{ width:56px; height:56px; border-radius:6px; object-fit:cover; border:1px solid #dee2e6 }
    #main-content { margin-left: 300px; }
    @media (max-width: 991px) { #comic-side-list{ display:none } #main-content{ margin-left:0 } }
    /* 缩略图网格样式 */
    #thumbnail-grid { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-start; }
    #thumbnail-grid .thumbnail-item { width:84px; padding:6px; border:1px solid #e9ecef; border-radius:6px; text-align:center; background:#fff; cursor:grab; }
    #thumbnail-grid .thumbnail-item.dragging { opacity:0.6; transform:scale(1.02); box-shadow:0 8px 20px rgba(0,0,0,0.06); }
    #thumbnail-grid .thumbnail-item img { width:72px; height:72px; object-fit:cover; border-radius:4px; display:block; margin:0 auto; }
    #thumbnail-grid .add-image-btn { width:84px; height:84px; display:flex; align-items:center; justify-content:center; border:1px dashed #ced4da; border-radius:6px; font-size:28px; color:#6c757d; background:#fafafa; cursor:pointer; }
    </style>

    <div id="comic-side-list" aria-label="漫画列表">
        <div style="font-weight:600; margin-bottom:8px;">漫画列表</div>
        <div id="sideItems"></div>
    </div>

    <div id="main-content">
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book"></i> Comic Manager - 漫画管理</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addComicModal">
            <i class="bi bi-plus-lg"></i> 添加漫画
        </button>
    </div>

    <!-- 漫画列表 -->
    <div class="row">
        <?php foreach ($comics as $id => $comic): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <?php if (!empty($comic['images'][0])): ?>
                <img src="<?= htmlspecialchars($comic['images'][0]) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($comic['alt']) ?>">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($comic['title']) ?></h5>
                    <p class="card-text text-muted"><?= htmlspecialchars($comic['subtitle']) ?></p>
                    <div class="mt-auto">
                        <span class="badge bg-<?= $comic['status'] === 'active' ? 'success' : 'secondary' ?> mb-2">
                            <?= $comic['status'] === 'active' ? '启用' : '禁用' ?>
                        </span>
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-primary btn-sm" onclick="editComic('<?= $id ?>')">
                                <i class="bi bi-pencil"></i> 编辑
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteComic('<?= $id ?>')">
                                <i class="bi bi-trash"></i> 删除
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($comics)): ?>
    <div class="text-center py-5">
        <i class="bi bi-book display-1 text-muted"></i>
        <h3 class="text-muted mt-3">还没有漫画</h3>
        <p class="text-muted">点击上方的"添加漫画"按钮开始创建你的第一个漫画。</p>
    </div>
    <?php endif; ?>
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

<!-- 编辑漫画模态框 -->
<div class="modal fade" id="editComicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">编辑漫画</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editComicForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editComicId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editTitle" class="form-label">标题 *</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editSubtitle" class="form-label">副标题</label>
                            <input type="text" class="form-control" name="subtitle" id="editSubtitle">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editLines" class="form-label">描述内容</label>
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
                    <!-- 缩略图编辑网格（由 JS 渲染） -->
                    <div class="mb-3">
                        <label class="form-label">图片管理</label>
                        <div id="thumbnail-grid" style="min-height:100px; padding:6px; background:#f8f9fa; border-radius:6px;"></div>
                        <div class="form-text">可上传/删除，并拖拽排序（会保存顺序）</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">更新漫画</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const comics = <?= json_encode($comics) ?>;

function editComic(id) {
    const comic = comics[id];
    if (!comic) return;
    
    document.getElementById('editComicId').value = id;
    document.getElementById('editTitle').value = comic.title || '';
    document.getElementById('editSubtitle').value = comic.subtitle || '';
    document.getElementById('editLines').value = comic.lines || '';
    document.getElementById('editAlt').value = comic.alt || '';
    document.getElementById('editStatus').value = comic.status || 'active';
    
    new bootstrap.Modal(document.getElementById('editComicModal')).show();
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
// 填充侧边栏（使用 Sortable.js）
document.addEventListener('DOMContentLoaded', function() {
    const sideItems = document.getElementById('sideItems');
    const comicKeys = Object.keys(comics);
    comicKeys.forEach((key) => {
        const c = comics[key];
        const div = document.createElement('div');
        div.className = 'side-item';
        div.dataset.id = key;
        div.innerHTML = `
            <span class="drag-handle" title="拖动排序">⋮</span>
            <img class="side-thumb" src="${c.images && c.images[0] ? c.images[0] : '/assets/images/comic/thumbs/placeholder.png'}" alt="">
            <div style="flex:1; min-width:0">
                <div style="font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${c.title || key}</div>
                <div style="font-size:12px; color:#666">${(c.images||[]).length} 张图片</div>
            </div>
        `;
        div.addEventListener('click', function(e){ if (e.target.closest('.drag-handle')) return; openEditFromSidebar(this.dataset.id); });
        sideItems.appendChild(div);
    });

    // 初始化 Sortable for side list
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
        const ids = [...document.querySelectorAll('#sideItems .side-item')].map(el=>el.dataset.id);
        const fd = new FormData();
        fd.append('ajax_action','reorder_comics');
        fd.append('order', JSON.stringify(ids));
        fetch(location.href, { method:'POST', body: fd }).then(r=>r.json()).then(res=>{
            if (!res.ok) alert(res.error || '排序保存失败');
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
    setTimeout(()=> renderThumbnails(id), 200);
}

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
        el.innerHTML = `<img src="${url}" /><div style="text-align:center; margin-top:4px;"><button class='btn btn-sm btn-outline-danger' onclick="ajaxDeleteImage('${id}','${url.replace(/'/g,"\\'")}')">删除</button></div>`;
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
            if (!res.ok) alert(res.error || '缩略图排序保存失败');
            else {
                // update local comics variable to reflect saved order
                comics[comicId].images = urls;
                // update side thumbnail if first image changed
                const thumb = document.querySelector('#sideItems .side-item[data-id="'+comicId+'"] .side-thumb');
                if (thumb) thumb.src = urls[0] || '/assets/images/comic/thumbs/placeholder.png';
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
                // 更新本地 comics 变量 并重新渲染
                comics[id].images = comics[id].images || [];
                comics[id].images.push(res.url);
                renderThumbnails(id);
                // 更新侧栏缩略图
                const thumb = document.querySelector('#sideItems .side-item[data-id="'+id+'"] .side-thumb');
                if (thumb) thumb.src = res.url;
            } else alert(res.error||'上传失败');
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
            // 更新本地并重新渲染
            comics[id].images = (comics[id].images||[]).filter(i=>i!==url);
            renderThumbnails(id);
        } else alert(res.error||'删除失败');
    });
}

</script>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>