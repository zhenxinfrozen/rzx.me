<?php
/**
 * 缩略图中心 - 批量管理部分 (partial)
 *
 * @var string $dev_query
 */
?>
<div class="content-card">
    <h3><i data-feather="play-circle" class="me-2"></i>批量操作</h3>
    <div class="d-flex gap-2 mt-3 flex-wrap">
        <button class="btn btn-primary" onclick="generateAllThumbnails()">
            <i data-feather="refresh-cw" class="me-1"></i>
            生成所有缩略图
        </button>
        <button class="btn btn-outline-danger" onclick="cleanAllThumbnails()">
            <i data-feather="trash-2" class="me-1"></i>
            清理所有缩略图
        </button>
        <button class="btn btn-outline-secondary" onclick="refreshGalleries()">
            <i data-feather="search" class="me-1"></i>
            重新扫描
        </button>
    </div>
</div>

<div id="galleriesContainer" class="mt-4">
    <div class="content-card">
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">正在加载Gallery数据...</div>
        </div>
    </div>
</div>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1rem;
}
.category-list {
    max-height: 220px;
    overflow-y: auto;
}
</style>

<script>
let galleriesData = [];

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('galleriesContainer')) {
        loadGalleries();
    }
});

async function loadGalleries() {
    try {
        showStatus('正在扫描Gallery...', 'info');
        const formData = new FormData();
        formData.append('action', 'scan_galleries');
        formData.append('ajax', '1');

        const response = await fetch('/admin/ajax.php?controller=thumbnail-center', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            galleriesData = data.galleries;
            renderGalleries();
            hideStatus();
        } else {
            showStatus('扫描失败: ' + data.message, 'danger');
        }
    } catch (error) {
        showStatus('扫描出错: ' + error.message, 'danger');
    }
}

function renderGalleries() {
    const container = document.getElementById('galleriesContainer');
    if (!container) return;
    
    if (galleriesData.length === 0) {
        container.innerHTML = `<div class="content-card text-center p-5 text-muted">未找到任何Gallery</div>`;
        return;
    }

    const html = `
        <div class="gallery-grid">
            ${galleriesData.map(gallery => `
                <div class="card" data-gallery="${gallery.name}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">${gallery.display_name}</h5>
                        <span class="badge bg-secondary">${gallery.category_count} 个分类</span>
                    </div>
                    
                    <div class="list-group list-group-flush category-list">
                        ${gallery.categories.map(category => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="category-name">${category}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success" onclick="generateThumbnails('${gallery.name}', '${category}')">生成</button>
                                    <button class="btn btn-outline-danger" onclick="cleanThumbnails('${gallery.name}', '${category}')">清理</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="card-footer d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-primary" onclick="generateThumbnails('${gallery.name}')">
                            <i data-feather="refresh-cw" class="me-1"></i>生成全部
                        </button>
                        <button class="btn btn-outline-danger" onclick="cleanThumbnails('${gallery.name}')">
                            <i data-feather="trash-2" class="me-1"></i>清理全部
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = html;
    feather.replace();
}

async function generateThumbnails(gallery, category = '') {
    try {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '0.6';

        const displayName = category ? `${gallery}/${category}` : gallery;
        showStatus(`正在生成 ${displayName} 的缩略图...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'generate_thumbnails');
        formData.append('ajax', '1');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('/admin/ajax.php?controller=thumbnail-center', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showStatus(`✅ ${displayName}: ${data.message} (${data.count} 个文件)`, 'success');
        } else {
            showStatus(`❌ ${displayName}: ${data.message}`, 'danger');
        }
    } catch (error) {
        showStatus(`生成出错: ${error.message}`, 'danger');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '1';
    }
}

async function cleanThumbnails(gallery, category = '') {
    const displayName = category ? `${gallery}/${category}` : gallery;
    if (!confirm(`确定要清理 ${displayName} 的所有缩略图吗？`)) return;

    try {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '0.6';

        showStatus(`正在清理 ${displayName} 的缩略图...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'clean_thumbnails');
        formData.append('ajax', '1');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('/admin/ajax.php?controller=thumbnail-center', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showStatus(`🗑️ ${displayName}: ${data.message}`, 'success');
        } else {
            showStatus(`❌ ${displayName}: ${data.message}`, 'danger');
        }
    } catch (error) {
        showStatus(`清理出错: ${error.message}`, 'danger');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '1';
    }
}

async function generateAllThumbnails() {
    if (!confirm('确定要为所有Gallery生成缩略图吗？这可能需要较长时间。')) return;
    for (const gallery of galleriesData) {
        await generateThumbnails(gallery.name);
        await new Promise(resolve => setTimeout(resolve, 500));
    }
}

async function cleanAllThumbnails() {
    if (!confirm('确定要清理所有Gallery的缩略图吗？此操作不可逆！')) return;
    for (const gallery of galleriesData) {
        await cleanThumbnails(gallery.name);
        await new Promise(resolve => setTimeout(resolve, 300));
    }
}

function refreshGalleries() {
    const container = document.getElementById('galleriesContainer');
    container.innerHTML = `<div class="content-card text-center p-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">正在重新扫描...</div></div>`;
    loadGalleries();
}

function showStatus(message, type = 'info') {
    const panel = document.getElementById('statusPanel');
    const messageEl = document.getElementById('statusMessage');
    if (!panel || !messageEl) return;
    
    panel.className = `alert alert-${type}`;
    panel.style.display = 'block';
    messageEl.textContent = message;
}

function hideStatus() {
    const panel = document.getElementById('statusPanel');
    if (panel) panel.style.display = 'none';
}
</script>
