<?php
require_once __DIR__ . '/../../Models/comic_data.php';
$allComics = get_all_comics_data();
$activeComics = array_filter($allComics, function($comic) {
    return ($comic['status'] ?? 'active') === 'active';
});
$comicStats = get_comic_stats();
?>

<div class="comic_title">
    <span class="comic-title-text"><b>漫画 Comic</b></span>
</div>
<a href="#"><div class="comic_dog">
</div></a>
<p align="center"><span style="font-family: 'Comic Sans MS', cursive; color:#999999; font-size:1rem;"> 
    Interactive Comics Gallery <span style="font-size:0.8rem">（<b>交互式漫画</b> - 共<?= $comicStats['active'] ?>个作品）</span>
</span></p>

<div id="ray-comic-menu">
    <!-- 动态生成的漫画图标 -->
    <?php if (empty($activeComics)): ?>
        <div class="no-comics-message">
            <p style="text-align: center; color: #999; margin: 2rem 0;">
                <i class="bi bi-book" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                还没有漫画作品，敬请期待...
            </p>
        </div>
    <?php else: ?>
        <?php $index = 1; ?>
        <?php foreach ($activeComics as $comicId => $comic): ?>
        <a href="#" data-comic-id="<?= htmlspecialchars($comicId) ?>" class="comic-link" title="<?= htmlspecialchars($comic['title']) ?>">
            <div class="ray-comic-menu-icon" id="comic-<?= $index ?>" data-title="<?= htmlspecialchars($comic['title']) ?>">
                <?php if (!empty($comic['icon_default']) && !empty($comic['icon_hover'])): ?>
                    <img class="default-img" src="<?= htmlspecialchars($comic['icon_default']) ?>" 
                         alt="<?= htmlspecialchars($comic['alt'] ?? $comic['title']) ?>">
                    <img class="hover-img" src="<?= htmlspecialchars($comic['icon_hover']) ?>" 
                         alt="<?= htmlspecialchars($comic['alt'] ?? $comic['title']) ?> hover">
                <?php elseif (!empty($comic['icon_default'])): ?>
                    <!-- 只有默认图标 -->
                    <img class="default-img" src="<?= htmlspecialchars($comic['icon_default']) ?>" 
                         alt="<?= htmlspecialchars($comic['alt'] ?? $comic['title']) ?>">
                <?php elseif (!empty($comic['images'][0])): ?>
                    <!-- 使用第一张图片作为图标 -->
                    <img class="default-img" src="<?= htmlspecialchars($comic['images'][0]) ?>" 
                         alt="<?= htmlspecialchars($comic['alt'] ?? $comic['title']) ?>"
                         style="object-fit: cover;">
                <?php else: ?>
                    <!-- 备用文字图标 -->
                    <div class="icon-text-wrapper">
                        <span class="icon-text"><?= mb_substr($comic['title'], 0, 2) ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- 图标标题提示 -->
                <div class="icon-tooltip">
                    <?= htmlspecialchars($comic['title']) ?>
                </div>
            </div>
        </a>
        <?php $index++; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="comic_show">
    <div class="comic-placeholder">
        <div class="placeholder-content">
            <i class="bi bi-mouse-fill" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
            <p>点击上方图标查看漫画内容</p>
            <small class="text-muted">Comic Gallery - 交互式漫画展示</small>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const comicLinks = document.querySelectorAll('#ray-comic-menu a');
    const comicShowContainer = document.getElementById('comic_show');

    comicLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const comicId = this.dataset.comicId;
            if (!comicId) {
                // 如果没有 comic-id，说明是外部链接（如 sketch-dream），允许正常跳转
                return;
            }
            
            // 只有有 comic-id 的链接才阻止默认行为并进行AJAX处理
            event.preventDefault();

            // 使用新的统一API路径
            fetch(`api?id=${comicId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // 生成多图片展示的HTML
                    let imagesHtml = '';
                    if (data.images && Array.isArray(data.images)) {
                        imagesHtml = data.images.map(image => 
                            `<div class="comic-item">
                                <img src="${image}" alt="${data.alt}" />
                            </div>`
                        ).join('');
                    } else if (data.image) {
                        // 兼容旧格式
                        imagesHtml = `<div class="comic-item">
                            <img src="${data.image}" alt="${data.alt}" />
                        </div>`;
                    }

                    const htmlContent = `
                        <div id="comic-wrapper" class="comic-content-wrapper">
                            <div id="comic-titlebox" class="comic-info">
                                <h1 class="comic-title">${data.title}</h1>
                                ${data.subtitle ? `<h2 class="comic-subtitle">${data.subtitle}</h2>` : ''}
                                ${data.lines ? `<div class="comic-description">${data.lines}</div>` : ''}
                                <div class="comic-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-images"></i> ${data.images ? data.images.length : 1} 张图片
                                        ${data.created_at ? ` | <i class="bi bi-calendar"></i> ${data.created_at}` : ''}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="comic-gallery">
                            ${imagesHtml}
                        </div>
                    `;
                    comicShowContainer.innerHTML = htmlContent;
                })
                .catch(error => {
                    console.error('Error fetching or processing comic data:', error);
                    comicShowContainer.innerHTML = `
                        <div class="error-message text-center py-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                            <p class="text-danger mt-2">内容加载失败，请稍后再试。</p>
                            <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> 重新加载
                            </button>
                        </div>
                    `;
                });
        });
    });
});
</script>

<style>
/* 增强样式 */
.comic-link {
    text-decoration: none;
    transition: transform 0.2s ease;
}

.comic-link:hover {
    transform: translateY(-2px);
}

.ray-comic-menu-icon {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.ray-comic-menu-icon:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.icon-tooltip {
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 100;
}

.ray-comic-menu-icon:hover .icon-tooltip {
    opacity: 1;
}

.icon-text-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}

.comic-content-wrapper {
    margin-bottom: 2rem;
}

.comic-info {
    text-align: center;
    padding: 2rem 1rem;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 10px;
    margin-bottom: 1rem;
}

.comic-title {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.comic-subtitle {
    color: #34495e;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.comic-description {
    color: #7f8c8d;
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.comic-gallery {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin: 2rem 0;
}

.comic-item {
    display: flex;
    justify-content: center;
}

.comic-item img {
    max-width: 100%;
    height: auto;
    display: block;
    cursor: default;
    border-radius: 0;
    box-shadow: none;
    transition: none;
}

.comic-item img:hover {
    transform: none;
    box-shadow: none;
}

.gallery-controls {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
}

.placeholder-content {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.no-comics-message {
    text-align: center;
    padding: 2rem;
}

/* 图片模态框样式 */
.image-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
}

.modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    z-index: 10000;
}

.modal-image {
    max-width: 100%;
    max-height: 90vh;
    border-radius: 8px;
}

.close-btn {
    position: absolute;
    top: -40px;
    right: 0;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
}

.error-message {
    background: #f8f9fa;
    border-radius: 8px;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .comic-gallery {
        grid-template-columns: 1fr;
    }
    
    .gallery-controls {
        flex-direction: column;
        align-items: center;
    }
    
    .comic-info {
        padding: 1rem;
    }
}
</style>