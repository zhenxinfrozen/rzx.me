<?php
// app/Views/pages/gallery.php
// 动态Gallery展示页面

// 自动加载必要的类
require_once __DIR__ . '/../../Utils/FileScanner.php';
require_once __DIR__ . '/../../Utils/ImageProcessor.php';
require_once __DIR__ . '/../../Utils/ThumbnailGenerator.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';

// 从URL获取gallery名称 (例如: /gallery-AAA -> AAA)
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$galleryName = '';

if (preg_match('/\/gallery-([^\/\?]+)/', $currentPath, $matches)) {
    $galleryName = $matches[1];
}

if (empty($galleryName)) {
    // 如果没有指定gallery，重定向到galleries页面
    header('Location: /galleries');
    exit;
}

$galleryManager = new GalleryManager();
$images = $galleryManager->getGalleryImages($galleryName);

// 生成缩略图（如果不存在）
$galleryManager->generateThumbnails($galleryName);

// 检查被跳过的大文件
$skippedFiles = $galleryManager->getSkippedFiles($galleryName);

if (empty($images)) {
    // 如果gallery不存在或无图片，显示错误信息
    $maxSizeMB = $galleryManager->getMaxFileSizeMB();
    $skippedInfo = !empty($skippedFiles) ? '<p style="color:#e74c3c;">发现 ' . count($skippedFiles) . ' 个文件超过 ' . $maxSizeMB . 'MB 限制被跳过</p>' : '';
    
    echo '<div style="text-align:center; margin:50px;">
            <h2>Gallery "' . htmlspecialchars($galleryName) . '" 不存在或无图片</h2>
            ' . $skippedInfo . '
            <p><a href="/galleries">返回画廊列表</a></p>
          </div>';
    return;
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<style>
  /* Gallery专用样式 */
  html,body{height:100%;margin:0;background:#ffffff;color:#222;overflow-x:hidden}
  .gallery-wrap{
    height:95vh;display:flex;
    flex-direction:column;
    background:#fff;
    position:relative;
    overflow:hidden
}
.gallery-header{
display: flex;
align-items: center; /* 垂直居中所有子项 */
justify-content: center; /* 水平居中flex子项（现在主要是title-container）*/
background: #fff;
padding: 5px;
border-bottom: 0px solid #ddd; /* 建议保留一个细的边框，视觉效果更好 */
position: relative; /* 关键：为 back-link 的绝对定位提供锚点 */
text-align: center; /* 为不支持flex的旧浏览器提供一个回退 */
}
.gallery-title-container{  
display: flex;
flex-direction: column; /* 让内部的标题和副标题垂直排列 */
align-items: center;
}
/* 标题 */
.gallery-title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #666;
  margin: 0;
}
/* 副标题 */
.gallery-subtitle {
  font-size: 0.6rem;
  color: #777;
  margin: 5px 0 0 0;
}
/* 返回链接 - 修改后的样式 */
.back-link {
  /* 使用绝对定位将其脱离文档流，放到左边 */
  position: absolute;
  left: 1%; /* 与父容器的padding保持一致 */
  top: 50%; /* 垂直居中的关键 */
  transform: translateY(-50%); /* 精确垂直居中，无论元素多高 */

  /* 以下是您原有的样式，可以保留 */
  border-radius: 2px;
  padding: 5px;
  color: #999;
  box-shadow: #cdcdcd 1px 1px 12px;
  text-decoration: none;
  font-weight: bold;
  font-size: 0.9rem;

  /* 不再需要 margin-right: auto */
} 
 .back-link:hover{color:#1795ff}
  
  .swiper{flex:1;min-height:0;width:100%;overflow:hidden}
  .main-swiper, .main-swiper .swiper-wrapper, .main-swiper .swiper-slide{height:calc(100vh - 240px)}
  .main-swiper .swiper-wrapper{align-items:center}
  .main-swiper .swiper-slide{width:100%;flex:0 0 100%;box-sizing:border-box}
  .swiper-slide{display:flex;align-items:center;justify-content:center;background:#fff}
  .swiper-slide img{display:block;max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain}
  .thumbs{height:120px;background:#ffffff;display:flex;align-items:center;padding:8px;overflow-x:auto;overflow-y:hidden;justify-content:center;gap:8px;flex-wrap:nowrap;scroll-behavior:smooth}
  .thumbs img{height:88px;max-width:100px;cursor:pointer;opacity:0.8;flex-shrink:0;object-fit:cover;border-radius:4px;transition:opacity 0.3s ease, border-color 0.3s ease;border:2px solid transparent;position:relative;pointer-events:auto}
  .thumbs img:hover{opacity:1;box-shadow:0 2px 8px rgba(0,0,0,0.1);border-color:#ddd}
  .thumbs img.active{border:2px solid #8cb5d5;opacity:1;box-shadow:0 2px 8px rgba(140,181,213,0.3)}
  .swiper-button-next, .swiper-button-prev{color:#8a8a8a}
  
  /* 响应式调整 */
  @media (max-width: 768px) {
    .gallery-header{padding:10px}
    .gallery-title{font-size:1.2rem}
    .back-link{font-size:0.8rem}
    .thumbs{height:80px;padding:5px}
    .thumbs img{height:60px;max-width:80px}
  }
</style>

<div class="gallery-wrap">
  <div class="gallery-header">
    <a href="/galleries" class="back-link">← BACK</a>
        <div class="gallery-title-container">
      <h1 class="gallery-title"><?php echo htmlspecialchars($galleryName); ?></h1>
      <p class="gallery-subtitle">
        <?php echo count($images); ?> Images
        <?php if (!empty($skippedFiles)): ?>
          <span style="color:#e74c3c; font-size:0.8em; display:block;">
            <?php echo count($skippedFiles); ?> 个大文件(>10MB)被跳过
          </span>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <div class="swiper main-swiper">
    <div class="swiper-wrapper" id="slides">
      <!-- slides will be injected by JS -->
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>

  <div class="thumbs" id="thumbs"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
  async function loadGallery(){
    try{
      console.log('Gallery: <?php echo $galleryName; ?> - Loading <?php echo count($images); ?> images');
      
      const slidesEl = document.getElementById('slides');
      const thumbsEl = document.getElementById('thumbs');
      const slideItems = [];

      // 图片数据来自PHP
      const images = <?php echo json_encode($images); ?>;
      
      // 创建slides和thumbnails
      images.forEach((image, index) => {
        // 创建主图slide
        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        
        const img = document.createElement('img');
        img.src = image.url;
        img.alt = `Gallery image ${index + 1}`;
        img.loading = 'lazy';
        
        slide.appendChild(img);
        slidesEl.appendChild(slide);
        
        // 创建缩略图
        const thumb = document.createElement('img');
        thumb.src = image.thumb_url;
        thumb.alt = `Thumbnail ${index + 1}`;
        thumb.style.cursor = 'pointer';
        thumb.dataset.slideIndex = index;
        
        thumbsEl.appendChild(thumb);
        
        slideItems.push({
          imageEl: img,
          timg: thumb,
          index: index
        });
      });

      // 初始化Swiper
      const mainSwiper = new Swiper('.main-swiper', {
        loop: images.length > 1,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        keyboard: {
          enabled: true,
        },
        mousewheel: {
          enabled: true,
        },
        lazy: {
          loadPrevNext: true,
          loadPrevNextAmount: 2,
        },
      });

      // 绑定缩略图点击事件
      slideItems.forEach((slideItem, index) => {
        slideItem.timg.addEventListener('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          console.log('Thumbnail clicked:', index, 'Current realIndex:', mainSwiper.realIndex);
          
          // 在loop模式下，使用slideToLoop来确保正确跳转
          if (mainSwiper) {
            if (mainSwiper.params.loop) {
              mainSwiper.slideToLoop(index);
            } else {
              mainSwiper.slideTo(index);
            }
          }
        });
      });

      // 高亮当前缩略图
      if(slideItems.length > 0) { 
        slideItems[0].timg.classList.add('active'); 
      }

      // 监听slide变化，更新缩略图高亮
      mainSwiper.on('slideChange', function(){
        const idx = this.realIndex;
        console.log('Slide changed to realIndex:', idx, 'activeIndex:', this.activeIndex);
        
        slideItems.forEach((s,si) => {
          s.timg.classList.toggle('active', si === idx);
        });
        
        // 滚动缩略图到当前位置
        try { 
          const active = slideItems[idx]?.timg; 
          if(active) { 
            const parent = thumbsEl; 
            const targetLeft = active.offsetLeft - (parent.clientWidth - active.clientWidth)/2; 
            parent.scrollTo({left: targetLeft, behavior: 'smooth'}); 
          } 
        } catch(e) {}
      });

      console.log('Gallery loaded successfully');

    } catch(e) { 
      console.error('Gallery loading error:', e); 
    }
  }
  
  loadGallery();
</script>