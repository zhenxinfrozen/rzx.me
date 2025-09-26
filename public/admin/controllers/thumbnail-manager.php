<?php
// public/admin/controllers/thumbnail-manager.php - 缩略图管理器
require_once '../../../app/bootstrap.php';

// 简单的认证检查
session_start();
if (!isset($_SESSION['admin_authenticated']) && !isset($_GET['dev'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../../../app/Utils/GalleryManager.php';
require_once '../../../app/Services/ThumbnailService.php';
$galleryManager = new GalleryManager();

// 处理AJAX请求
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'scan_galleries':
                $galleries = [];
                $imagesPath = '../../assets/images';
                
                if (is_dir($imagesPath)) {
                    $dirs = scandir($imagesPath);
                    foreach ($dirs as $dir) {
                        if ($dir === '.' || $dir === '..') continue;
                        
                        $fullPath = $imagesPath . '/' . $dir;
                        if (is_dir($fullPath)) {
                            $categories = $galleryManager->getGalleryCategories($dir);
                            $galleries[] = [
                                'name' => $dir,
                                'display_name' => ucfirst(str_replace('-', ' ', $dir)),
                                'categories' => $categories,
                                'category_count' => count($categories)
                            ];
                        }
                    }
                }
                
                echo json_encode(['success' => true, 'galleries' => $galleries]);
                exit;
                
            case 'generate_thumbnails':
                $gallery = $_POST['gallery'] ?? '';
                $category = $_POST['category'] ?? '';
                
                if (empty($gallery)) {
                    throw new Exception('Gallery参数不能为空');
                }
                
                $path = $gallery;
                if (!empty($category)) {
                    $path .= '/' . $category;
                }
                
                $result = $galleryManager->generateThumbnails($path);
                
                echo json_encode([
                    'success' => true, 
                    'message' => '缩略图生成完成',
                    'results' => $result,
                    'count' => count($result)
                ]);
                exit;
                
            case 'clean_thumbnails':
                $gallery = $_POST['gallery'] ?? '';
                $category = $_POST['category'] ?? '';
                
                if (empty($gallery)) {
                    throw new Exception('Gallery参数不能为空');
                }
                
                $thumbsDir = '../../assets/images/' . $gallery;
                if (!empty($category)) {
                    $thumbsDir .= '/' . $category;
                }
                $thumbsDir .= '/thumbs';
                
                $count = 0;
                if (is_dir($thumbsDir)) {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($thumbsDir),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            unlink($file->getPathname());
                            $count++;
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => "已清理 $count 个缩略图文件",
                    'count' => $count
                ]);
                exit;
                
            default:
                throw new Exception('未知操作');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// 设置页面信息
$page_title = '缩略图管理器';
$_GET['page'] = 'thumbnail-manager';

// 包含头部
include '../views/layouts/header.php';
?>

<div class="page-header">
    <h1>🖼️ 缩略图管理器</h1>
    <p>统一管理和维护所有Gallery的缩略图文件</p>
</div>

<div class="alert alert-info" id="statusPanel" style="display: none;">
    <div id="statusMessage"></div>
</div>

<div class="content-card">
    <h3>🎛️ 批量操作</h3>
    <div class="control-actions" style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="generateAllThumbnails()">
            <i data-feather="refresh-cw"></i>
            生成所有缩略图
        </button>
        <button class="btn btn-outline" onclick="cleanAllThumbnails()">
            <i data-feather="trash-2"></i>
            清理所有缩略图
        </button>
        <button class="btn btn-primary" onclick="refreshGalleries()">
            <i data-feather="search"></i>
            重新扫描
        </button>
    </div>
</div>

<div id="galleriesContainer">
    <div class="content-card">
        <div style="text-align: center; padding: 40px;">
            <div class="loading-spinner"></div>
            <div style="margin-top: 15px; color: var(--text-secondary);">正在加载Gallery数据...</div>
        </div>
    </div>
</div>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 20px;
}

.gallery-card {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-medium);
    background: var(--bg-secondary);
    transition: all 0.3s ease;
}

.gallery-card:hover {
    box-shadow: var(--shadow-medium);
    transform: translateY(-1px);
}

.gallery-card.loading {
    opacity: 0.6;
    pointer-events: none;
}

.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
}

.gallery-title {
    font-size: 1.1em;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.gallery-info {
    font-size: 0.9em;
    color: var(--text-secondary);
    background: var(--bg-primary);
    padding: 4px 8px;
    border-radius: var(--radius-small);
}

.category-list {
    max-height: 240px;
    overflow-y: auto;
    margin: var(--spacing-lg);
    margin-bottom: 0;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border-light);
}

.category-item:last-child {
    border-bottom: none;
}

.category-item:hover {
    background: var(--bg-primary);
}

.category-name {
    font-weight: 500;
    color: var(--text-primary);
}

.category-actions {
    display: flex;
    gap: 6px;
}

.btn-small {
    padding: 4px 10px;
    font-size: 0.8em;
    border-radius: var(--radius-small);
}

.btn-success {
    background: var(--success-color);
    color: white;
    border: none;
}

.btn-success:hover {
    background: #008a20;
}

.btn-danger {
    background: var(--error-color);
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #b32d2e;
}

.gallery-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
}

.loading-spinner {
    display: inline-block;
    width: 32px;
    height: 32px;
    border: 3px solid var(--border-light);
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
let galleries = [];

// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化图标
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    loadGalleries();
});

// 加载所有gallery数据
async function loadGalleries() {
    try {
        showStatus('正在扫描Gallery...', 'info');
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=scan_galleries'
        });
        
        const data = await response.json();
        
        if (data.success) {
            galleries = data.galleries;
            renderGalleries();
            hideStatus();
        } else {
            showStatus('扫描失败: ' + data.message, 'error');
        }
    } catch (error) {
        showStatus('扫描出错: ' + error.message, 'error');
    }
}

// 渲染gallery列表
function renderGalleries() {
    const container = document.getElementById('galleriesContainer');
    
    if (galleries.length === 0) {
        container.innerHTML = `
            <div class="content-card">
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    未找到任何Gallery
                </div>
            </div>
        `;
        return;
    }

    const html = `
        <div class="gallery-grid">
            ${galleries.map(gallery => `
                <div class="content-card gallery-card" data-gallery="${gallery.name}">
                    <div class="gallery-header">
                        <h3 class="gallery-title">${gallery.display_name}</h3>
                        <div class="gallery-info">${gallery.category_count} 个分类</div>
                    </div>
                    
                    <div class="category-list">
                        ${gallery.categories.map(category => `
                            <div class="category-item">
                                <span class="category-name">${category}</span>
                                <div class="category-actions">
                                    <button class="btn btn-small btn-success" 
                                        onclick="generateThumbnails('${gallery.name}', '${category}')">
                                        生成
                                    </button>
                                    <button class="btn btn-small btn-danger" 
                                        onclick="cleanThumbnails('${gallery.name}', '${category}')">
                                        清理
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="gallery-actions">
                        <button class="btn btn-primary" 
                            onclick="generateThumbnails('${gallery.name}')">
                            <i data-feather="refresh-cw"></i>
                            生成全部
                        </button>
                        <button class="btn btn-outline" 
                            onclick="cleanThumbnails('${gallery.name}')">
                            <i data-feather="trash-2"></i>
                            清理全部
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
    
    // 重新初始化图标
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// 生成缩略图
async function generateThumbnails(gallery, category = '') {
    try {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.classList.add('loading');

        const displayName = category ? `${gallery}/${category}` : gallery;
        showStatus(`正在生成 ${displayName} 的缩略图...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'generate_thumbnails');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showStatus(`✅ ${displayName}: ${data.message} (${data.count} 个文件)`, 'success');
        } else {
            showStatus(`❌ ${displayName}: ${data.message}`, 'error');
        }
    } catch (error) {
        showStatus(`生成出错: ${error.message}`, 'error');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.classList.remove('loading');
    }
}

// 清理缩略图
async function cleanThumbnails(gallery, category = '') {
    const displayName = category ? `${gallery}/${category}` : gallery;
    
    if (!confirm(`确定要清理 ${displayName} 的所有缩略图吗？`)) {
        return;
    }

    try {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.classList.add('loading');

        showStatus(`正在清理 ${displayName} 的缩略图...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'clean_thumbnails');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showStatus(`🗑️ ${displayName}: ${data.message}`, 'success');
        } else {
            showStatus(`❌ ${displayName}: ${data.message}`, 'error');
        }
    } catch (error) {
        showStatus(`清理出错: ${error.message}`, 'error');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.classList.remove('loading');
    }
}

// 批量生成所有缩略图
async function generateAllThumbnails() {
    if (!confirm('确定要为所有Gallery生成缩略图吗？这可能需要较长时间。')) {
        return;
    }

    for (const gallery of galleries) {
        await generateThumbnails(gallery.name);
        // 短暂延迟避免过载
        await new Promise(resolve => setTimeout(resolve, 500));
    }
}

// 批量清理所有缩略图
async function cleanAllThumbnails() {
    if (!confirm('确定要清理所有Gallery的缩略图吗？此操作不可逆！')) {
        return;
    }

    for (const gallery of galleries) {
        await cleanThumbnails(gallery.name);
        // 短暂延迟避免过载
        await new Promise(resolve => setTimeout(resolve, 300));
    }
}

// 刷新gallery列表
function refreshGalleries() {
    loadGalleries();
}

// 显示状态信息
function showStatus(message, type = 'info') {
    const panel = document.getElementById('statusPanel');
    const messageEl = document.getElementById('statusMessage');
    
    panel.className = `alert alert-${type}`;
    panel.style.display = 'block';
    messageEl.textContent = message;
}

// 隐藏状态信息
function hideStatus() {
    const panel = document.getElementById('statusPanel');
    panel.style.display = 'none';
}
</script>

<?php include '../views/layouts/footer.php'; ?>