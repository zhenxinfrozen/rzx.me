<?php
/**
 * 缂╃暐鍥句腑蹇?- 鎵归噺绠＄悊閮ㄥ垎 (partial)
 *
 * @var string $dev_query
 */
?>
<div class="content-card">
    <h3><i data-feather="play-circle" class="me-2"></i>鎵归噺鎿嶄綔</h3>
    <div class="d-flex gap-2 mt-3 flex-wrap">
        <button class="btn btn-primary" onclick="generateAllThumbnails()">
            <i data-feather="refresh-cw" class="me-1"></i>
            鐢熸垚鎵€鏈夌缉鐣ュ浘
        </button>
        <button class="btn btn-outline-danger" onclick="cleanAllThumbnails()">
            <i data-feather="trash-2" class="me-1"></i>
            娓呯悊鎵€鏈夌缉鐣ュ浘
        </button>
        <button class="btn btn-outline-secondary" onclick="refreshGalleries()">
            <i data-feather="search" class="me-1"></i>
            閲嶆柊鎵弿
        </button>
    </div>
</div>

<div id="galleriesContainer" class="mt-4">
    <div class="content-card">
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">姝ｅ湪鍔犺浇Gallery鏁版嵁...</div>
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
        showStatus('姝ｅ湪鎵弿Gallery...', 'info');
        const formData = new FormData();
        formData.append('action', 'scan_galleries');
        formData.append('ajax', '1');

        const response = await fetch('/admin/ajax?controller=thumbnail-center', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            galleriesData = data.galleries;
            renderGalleries();
            hideStatus();
        } else {
            showStatus('鎵弿澶辫触: ' + data.message, 'danger');
        }
    } catch (error) {
        showStatus('鎵弿鍑洪敊: ' + error.message, 'danger');
    }
}

function renderGalleries() {
    const container = document.getElementById('galleriesContainer');
    if (!container) return;
    
    if (galleriesData.length === 0) {
        container.innerHTML = `<div class="content-card text-center p-5 text-muted">鏈壘鍒颁换浣旼allery</div>`;
        return;
    }

    const html = `
        <div class="gallery-grid">
            ${galleriesData.map(gallery => `
                <div class="card" data-gallery="${gallery.name}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">${gallery.display_name}</h5>
                        <span class="badge bg-secondary">${gallery.category_count} 涓垎绫?/span>
                    </div>
                    
                    <div class="list-group list-group-flush category-list">
                        ${gallery.categories.map(category => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="category-name">${category}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success" onclick="generateThumbnails('${gallery.name}', '${category}')">鐢熸垚</button>
                                    <button class="btn btn-outline-danger" onclick="cleanThumbnails('${gallery.name}', '${category}')">娓呯悊</button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="card-footer d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-primary" onclick="generateThumbnails('${gallery.name}')">
                            <i data-feather="refresh-cw" class="me-1"></i>鐢熸垚鍏ㄩ儴
                        </button>
                        <button class="btn btn-outline-danger" onclick="cleanThumbnails('${gallery.name}')">
                            <i data-feather="trash-2" class="me-1"></i>娓呯悊鍏ㄩ儴
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
        showStatus(`姝ｅ湪鐢熸垚 ${displayName} 鐨勭缉鐣ュ浘...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'generate_thumbnails');
        formData.append('ajax', '1');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('/admin/ajax?controller=thumbnail-center', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showStatus(`鉁?${displayName}: ${data.message} (${data.count} 涓枃浠?`, 'success');
        } else {
            showStatus(`鉂?${displayName}: ${data.message}`, 'danger');
        }
    } catch (error) {
        showStatus(`鐢熸垚鍑洪敊: ${error.message}`, 'danger');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '1';
    }
}

async function cleanThumbnails(gallery, category = '') {
    const displayName = category ? `${gallery}/${category}` : gallery;
    if (!confirm(`纭畾瑕佹竻鐞?${displayName} 鐨勬墍鏈夌缉鐣ュ浘鍚楋紵`)) return;

    try {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '0.6';

        showStatus(`姝ｅ湪娓呯悊 ${displayName} 鐨勭缉鐣ュ浘...`, 'info');
        
        const formData = new FormData();
        formData.append('action', 'clean_thumbnails');
        formData.append('ajax', '1');
        formData.append('gallery', gallery);
        if (category) formData.append('category', category);

        const response = await fetch('/admin/ajax?controller=thumbnail-center', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            showStatus(`馃棏锔?${displayName}: ${data.message}`, 'success');
        } else {
            showStatus(`鉂?${displayName}: ${data.message}`, 'danger');
        }
    } catch (error) {
        showStatus(`娓呯悊鍑洪敊: ${error.message}`, 'danger');
    } finally {
        const card = document.querySelector(`[data-gallery="${gallery}"]`);
        if (card) card.style.opacity = '1';
    }
}

async function generateAllThumbnails() {
    if (!confirm('纭畾瑕佷负鎵€鏈塆allery鐢熸垚缂╃暐鍥惧悧锛熻繖鍙兘闇€瑕佽緝闀挎椂闂淬€?)) return;
    for (const gallery of galleriesData) {
        await generateThumbnails(gallery.name);
        await new Promise(resolve => setTimeout(resolve, 500));
    }
}

async function cleanAllThumbnails() {
    if (!confirm('纭畾瑕佹竻鐞嗘墍鏈塆allery鐨勭缉鐣ュ浘鍚楋紵姝ゆ搷浣滀笉鍙€嗭紒')) return;
    for (const gallery of galleriesData) {
        await cleanThumbnails(gallery.name);
        await new Promise(resolve => setTimeout(resolve, 300));
    }
}

function refreshGalleries() {
    const container = document.getElementById('galleriesContainer');
    container.innerHTML = `<div class="content-card text-center p-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">姝ｅ湪閲嶆柊鎵弿...</div></div>`;
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
