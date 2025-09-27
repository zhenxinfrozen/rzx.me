<?php
// app/Views/pages/galleries.php

// 自动加载必要的类
require_once __DIR__ . '/../../Utils/FileScanner.php';
require_once __DIR__ . '/../../Utils/ImageProcessor.php';  

require_once __DIR__ . '/../../Utils/GalleryManager.php';

$galleryManager = new GalleryManager();
$galleries = $galleryManager->scanGalleries();

// 为所有galleries生成图标（如果不存在的话）
$galleryManager->generateAllIcons();
?>

<p align="center"><span style="font-family: 'Comic Sans MS', cursive; color:#999999; font-size:2rem;"> 个人作品集<span style="font-size:0.8rem">（<b>Gallery Collections</b>）</span> </span></p>

<div id="ray-galleries-menu">
    <?php 
    $counter = 1;
    foreach ($galleries as $gallery): 
    ?>
        <a href="<?php echo $gallery['route']; ?>">
            <div class="ray-comic-menu-icon">
                <?php 
                // 生成双图标
                $iconResult = $galleryManager->generateGalleryIcon($gallery['name']);
                
                $defaultIcon = ($iconResult && isset($iconResult['default'])) ? $iconResult['default']['icon_url'] : '/assets/images/404.jpg';
                $hoverIcon = ($iconResult && isset($iconResult['hover'])) ? $iconResult['hover']['icon_url'] : $defaultIcon;
                ?>
                <img class="default-img" src="<?php echo htmlspecialchars($defaultIcon); ?>" alt="gallery <?php echo htmlspecialchars($gallery['name']); ?>">
                <img class="hover-img" src="<?php echo htmlspecialchars($hoverIcon); ?>" alt="gallery <?php echo htmlspecialchars($gallery['name']); ?> hover">
            </div>
        </a>
    <?php 
    $counter++;
    endforeach; 
    ?>
    
    <?php if (empty($galleries)): ?>
        <div class="no-galleries">
            <p>暂无画廊内容</p>
            <p style="font-size:0.8rem; color:#999;">请在 assets/images/galleries/ 目录下添加图片文件夹</p>
        </div>
    <?php endif; ?>
</div>

<div id="galleries_info">
    <div id="gallery-description">
        <p>.</p>
    </div>
</div>

<style>
/* 复用comic页面的样式，但调整为galleries */
#ray-galleries-menu {
    /* 使用与comic相同的布局样式 */
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin: 20px 0;
}

.ray-comic-menu-icon {
    /* 基于comic页面的图标样式，调整为60x60px */
    width: 60px;
    height: 60px;
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
    display: inline-block;
}
.ray-comic-menu-icon:hover{
	border: 1px solid #ccc;
    box-shadow: #929292 0 0 5px;
}

.ray-comic-menu-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
    transition: opacity 0.3s ease;
}

.ray-comic-menu-icon .hover-img {
    opacity: 0;
}

.ray-comic-menu-icon:hover .default-img {
    opacity: 0;
}

.ray-comic-menu-icon:hover .hover-img {
    opacity: 1;
}

.ray-comic-menu-icon:hover {
    transform: scale(1.05);
}

.ray-galleries-menu-icon:hover {
    transform: scale(1.05);
}

.ray-galleries-menu-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.ray-galleries-menu-icon .hover-img {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
}

.ray-galleries-menu-icon:hover .default-img {
    opacity: 0.8;
}

.ray-galleries-menu-icon:hover .hover-img {
    opacity: 1;
}

.no-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
    color: #666;
    font-size: 0.8rem;
    text-align: center;
}

.no-galleries {
    text-align: center;
    color: #999;
    margin: 40px 0;
}

#galleries_info {
    margin-top: 30px;
    text-align: center;
}

#gallery-description {
    color: #666;
    font-style: italic;
}
</style>