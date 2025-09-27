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
    <?php foreach ($activeComics as $comicId => $comic): ?>
        <a href="#" data-comic-id="<?= htmlspecialchars($comicId) ?>">
            <div class="ray-comic-menu-icon">
                <?php if (!empty($comic['icon_default']) && !empty($comic['icon_hover'])): ?>
                    <img class="default-img" src="<?= htmlspecialchars($comic['icon_default']) ?>" alt="<?= htmlspecialchars($comic['title']) ?>">
                    <img class="hover-img" src="<?= htmlspecialchars($comic['icon_hover']) ?>" alt="<?= htmlspecialchars($comic['title']) ?> hover">
                <?php elseif (!empty($comic['icon_default'])): ?>
                    <img class="default-img" src="<?= htmlspecialchars($comic['icon_default']) ?>" alt="<?= htmlspecialchars($comic['title']) ?>">
                <?php elseif (!empty($comic['images']) && !empty($comic['images'][0])): ?>
                    <img class="default-img" src="<?= htmlspecialchars($comic['images'][0]) ?>" alt="<?= htmlspecialchars($comic['title']) ?>">
                <?php else: ?>
                    <span class="icon-text"><?= mb_substr($comic['title'], 0, 2) ?></span>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div id="comic_show">
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const comicLinks = document.querySelectorAll('#ray-comic-menu a');
    const comicShowContainer = document.getElementById('comic_show');

    comicLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const comicId = this.dataset.comicId;
            if (!comicId) return;
            
            event.preventDefault();

            fetch(`api?id=${comicId}`)
                .then(response => response.json())
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
                        imagesHtml = `<div class="comic-item">
                            <img src="${data.image}" alt="${data.alt}" />
                        </div>`;
                    }

                    const htmlContent = `
                        <div id="comic-wrapper">
                            <div id="comic-titlebox">
                                <h1>${data.title}</h1>
                                <p><strong>${data.subtitle}<br />
                                ${data.lines}</strong></p>
                            </div>
                            ${imagesHtml}
                        </div>
                    `;
                    comicShowContainer.innerHTML = htmlContent;
                })
                .catch(error => {
                    console.error('Error:', error);
                    comicShowContainer.innerHTML = '<p style="text-align:center; color:red;">内容加载失败，请稍后再试。</p>';
                });
        });
    });
});
</script>