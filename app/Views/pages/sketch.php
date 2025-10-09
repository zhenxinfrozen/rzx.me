<?php
require_once __DIR__ . '/../../Utils/GalleryManager.php';
require_once __DIR__ . '/../../Services/ThumbnailService.php';

/**
 * 根据后台配置对速写本目录进行排序
 */
function reorderSketchbookAlbums(array $albumNames): array {
    $configPath = __DIR__ . '/../../Config/sketchbook_sort.php';
    if (!file_exists($configPath)) {
        natcasesort($albumNames);
        return array_values($albumNames);
    }

    $config = include $configPath;
    $method = $config['sort_method'] ?? 'alphabetical';
    $customOrder = $config['custom_order'] ?? [];

    switch ($method) {
        case 'custom_order':
            $ordered = [];
            foreach ($customOrder as $name) {
                if (in_array($name, $albumNames, true)) {
                    $ordered[] = $name;
                }
            }
            foreach ($albumNames as $name) {
                if (!in_array($name, $ordered, true)) {
                    $ordered[] = $name;
                }
            }
            return $ordered;

        case 'date_modified':
            $basePath = __DIR__ . '/../../../public/assets/images/sketchbook/';
            usort($albumNames, function ($a, $b) use ($basePath) {
                $pathA = $basePath . $a;
                $pathB = $basePath . $b;
                $timeA = is_dir($pathA) ? filemtime($pathA) : 0;
                $timeB = is_dir($pathB) ? filemtime($pathB) : 0;
                return $timeB <=> $timeA;
            });
            return $albumNames;

        case 'prefix_sort':
            $settings = $config['prefix_settings'] ?? ['remove_prefix' => false, 'separator' => '-'];
            $separator = $settings['separator'] ?? '-';
            usort($albumNames, function ($a, $b) use ($separator) {
                $pa = explode($separator, $a, 2);
                $pb = explode($separator, $b, 2);
                $prefixA = $pa[0];
                $prefixB = $pb[0];
                if ($prefixA === $prefixB) {
                    return strcmp($a, $b);
                }
                return strcmp($prefixA, $prefixB);
            });
            return $albumNames;

        case 'alphabetical':
        default:
            natcasesort($albumNames);
            return array_values($albumNames);
    }
}

/**
 * 根据后台记录的排序调整图片顺序
 */
function reorderSketchbookImages(string $albumName, array $images): array {
    $orderFile = __DIR__ . '/../../Data/sketchbook_image_order.json';
    if (!file_exists($orderFile)) {
        return $images;
    }

    $orders = json_decode(file_get_contents($orderFile), true);
    if (!is_array($orders) || empty($orders[$albumName]) || !is_array($orders[$albumName])) {
        return $images;
    }

    $order = array_values(array_filter($orders[$albumName], 'is_string'));
    if (empty($order)) {
        return $images;
    }

    $position = array_flip($order);
    usort($images, function ($a, $b) use ($position) {
        $nameA = $a['name'] ?? ($a['filename'] ?? null);
        $nameB = $b['name'] ?? ($b['filename'] ?? null);
        $indexA = $nameA !== null && isset($position[$nameA]) ? $position[$nameA] : PHP_INT_MAX;
        $indexB = $nameB !== null && isset($position[$nameB]) ? $position[$nameB] : PHP_INT_MAX;
        if ($indexA === $indexB) {
            return strcmp((string)$nameA, (string)$nameB);
        }
        return $indexA <=> $indexB;
    });

    return $images;
}

$sketchDir = 'sketchbook';
$galleryManager = new GalleryManager();
$albumNames = $galleryManager->getGalleryCategories($sketchDir);
$albumNames = reorderSketchbookAlbums($albumNames);

$albumsData = [];
foreach ($albumNames as $albumName) {
    $albumPath = __DIR__ . '/../../../public/assets/images/' . $sketchDir . '/' . $albumName;
    if (!is_dir($albumPath)) {
        continue;
    }

    // 确保生成标准缩略图（保存在 thumbs 目录）
    ThumbnailService::generateBatchForPage($albumPath, 'sketchbook-thumb');

    $images = $galleryManager->getCategoryImages($sketchDir, $albumName);
    $images = reorderSketchbookImages($albumName, $images);
    if (empty($images)) {
        continue;
    }

    $formattedImages = [];
    foreach ($images as $image) {
        $thumbUrl = $image['thumb_url'] ?? '';
        $thumbPath = $thumbUrl ? __DIR__ . '/../../../public' . $thumbUrl : '';
        if (!$thumbUrl || !file_exists($thumbPath)) {
            $thumbUrl = $image['url'];
        }

        $formattedImages[] = [
            'full_url' => $image['url'],
            'thumb_url' => $thumbUrl,
            'filename' => $image['name'],
            'label' => pathinfo($image['name'], PATHINFO_FILENAME) ?: $image['name']
        ];
    }

    $albumsData[] = [
        'name' => $albumName,
        'images' => $formattedImages
    ];
}
?>

<div id="pp_gallery" class="pp_gallery">
    <div id="pp_loading" class="pp_loading"></div>
    <div id="pp_next" class="pp_next"></div>
    <div id="pp_prev" class="pp_prev"></div>
    <div id="pp_thumbContainer">
        <?php if (empty($albumsData)): ?>
            <div class="album empty-album">
                <div class="descr">暂无素描册内容</div>
            </div>
        <?php else: ?>
            <?php foreach ($albumsData as $albumData): ?>
                <div class="album" data-album="<?= htmlspecialchars($albumData['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($albumData['images'] as $image): ?>
                        <div class="content">
                            <img src="<?= htmlspecialchars($image['thumb_url'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($image['full_url'], ENT_QUOTES, 'UTF-8') ?>" />
                            <span><?= htmlspecialchars($image['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="descr"><?= htmlspecialchars($albumData['name'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div id="pp_back" class="pp_back">Albums</div>
    </div>
    <!-- scripts: keep view self-contained (header/footer load CSS/JS separately) -->
</div>

<!-- 纯内联 JS（无 jQuery/Migrate/transform） -->
<script type="text/javascript" src="/assets/js/cufon-yui.js"></script>
<script>
(function(){
    var ie = !!document.documentMode;
    var enableshow  = true;   // 是否允许显示下一张
    var current     = -1;     // 当前图片索引（content 的 index）
    var album       = -1;     // 当前相册索引

    function bySelAll(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
    function byId(id){ return document.getElementById(id); }

    var gallery = byId('pp_gallery');
    var albums  = bySelAll('#pp_thumbContainer div.album');
    var loader  = byId('pp_loading');
    var nextBtn = byId('pp_next');
    var prevBtn = byId('pp_prev');
    var backBtn = byId('pp_back');
    var thumbs  = bySelAll('#pp_thumbContainer div.content img');

    // 预加载所有缩略图，等加载完再布局
    (function preloadThumbs(){
        var loaded = 0, total = thumbs.length;
        thumbs.forEach(function(img){
            var pre = new Image();
            pre.onload = function(){ if(++loaded === total) layoutAlbums(); };
            pre.src = img.getAttribute('src');
        });
    })();

    function layoutAlbums(){
        var w = (window.innerWidth||document.documentElement.clientWidth);
        var spaces = w/(albums.length+1);
        var cnt = 0;
        albums.forEach(function(a){
            cnt++;
            var left = spaces*cnt - (a.offsetWidth/2);
            a.style.left = left + 'px';
            // 添加初始相册从底部滑入动效
            animateElement(a, 'bottom', '0px', 500);
            a.addEventListener('click', spreadPictures);
        });
        // 初始随机旋转缩略图
        thumbs.forEach(function(img){
            var r = Math.floor(Math.random()*41)-20;
            img.style.transform = 'rotate('+r+'deg)';
        });
    }

    function spreadPictures(){
        var albumEl = this;
        album = albums.indexOf(albumEl);
        // 其他相册下沉，添加动效
        albums.forEach(function(a){ 
            if(a!==albumEl){ 
                animateElement(a, 'bottom', '-150px', 300);
            } 
        });
        // 当前相册移到左边，隐藏描述
        albumEl.removeEventListener('click', spreadPictures);
        albumEl.dataset.left = albumEl.style.left || '0px';
        animateElement(albumEl, 'left', '0px', 500);
        var descr = albumEl.querySelector('.descr'); 
        if(descr) animateElement(descr, 'bottom', '-30px', 200);
        
        // 保持缩略图在当前相册内，通过窗口宽度均分定位（与旧版一致）
        var contents = bySelAll('.content', albumEl);
        var total = contents.length;
        var cnt = 0;
        contents.forEach(function(content){
            cnt++;
            var w = (window.innerWidth||document.documentElement.clientWidth);
            var spaces = w/(total+1);
            var left = (spaces*cnt) - (140/2);
            var r = Math.floor(Math.random()*41)-20;
            // 添加缩略图展开动效
            animateElement(content, 'left', left + 'px', 500, function(){
                content.addEventListener('click', showImage);
                content.addEventListener('mouseenter', upImage);
                content.addEventListener('mouseleave', downImage);
            });
            // 图片旋转动效
            var img = content.querySelector('img'); 
            if(img) animateElement(img, 'transform', 'rotate('+r+'deg)', 300);
        });
        if(backBtn) animateElement(backBtn, 'left', '0px', 300);
    }

    if(backBtn) backBtn.addEventListener('click', function(){
        animateElement(backBtn, 'left', '-100px', 300);
        hideNavigation();
        
        // 清理当前预览
        hideCurrentPicture();
        
        var currentAlbum = albums[album];
        if(currentAlbum){
            // 先重置当前相册内缩略图位置 - 完全移除left样式恢复初始状态
            bySelAll('.content', currentAlbum).forEach(function(c){
                // 移除left定位，让缩略图回到相册内的自然文档流位置
                c.style.removeProperty('left');
                c.style.marginTop='0px';
                // 重置图片旋转
                var img = c.querySelector('img');
                if(img) {
                    var r = Math.floor(Math.random()*41)-20;
                    img.style.transform = 'rotate('+r+'deg)';
                }
                // 移除事件监听：通过替换节点实现
                var n = c.cloneNode(true); currentAlbum.replaceChild(n, c);
            });
            
            // 然后移动当前相册回原位置
            animateElement(currentAlbum, 'left', currentAlbum.dataset.left||'0px', 500, function(){
                // 动画完成后重新绑定点击事件
                currentAlbum.addEventListener('click', spreadPictures);
            });
            
            var descr = currentAlbum.querySelector('.descr'); 
            if(descr) animateElement(descr, 'bottom', '0px', 500);
        }
        
        // 其他相册从底部滑入
        albums.forEach(function(a){ 
            if(a!==currentAlbum){ 
                animateElement(a, 'bottom', '0px', 500);
            } 
        });
        
        // 重置状态
        album = -1;
        current = -1;
    });

    function showImage(nav){
        if(!enableshow) return; enableshow=false;
        var contentEl;
        if(nav===1){
            // next按钮：current应该已经被设置
            var albumEl = albums[album]; var list = bySelAll('.content', albumEl);
            contentEl = list[current];
            if(!contentEl){ enableshow=true; return; }
        } else if(nav===-1){
            // prev按钮：current应该已经被设置
            var albumEl = albums[album]; var list = bySelAll('.content', albumEl);
            contentEl = list[current];
            if(!contentEl){ enableshow=true; return; }
        } else {
            contentEl = this;
        }

        if(loader) loader.style.display='block';
        // 当前 index - 排除非content元素
        if(nav !== 1 && nav !== -1) {
            var albumEl = albums[album]; 
            var list = bySelAll('.content', albumEl);
            current = Array.prototype.indexOf.call(list, contentEl);
        }
        var imgThumb = contentEl.querySelector('img');
        var src = imgThumb ? imgThumb.getAttribute('alt') : '';
        var captionNode = contentEl.querySelector('span');
        var caption = captionNode ? captionNode.textContent : '';

        var large = new Image();
        large.onload = function(){
            resizeNative(large);
            function doCreate(){
                // 清理旧预览
                var exist = document.querySelector('#pp_gallery .pp_preview');
                if(exist) exist.remove();
                var preview = document.createElement('div');
                preview.id='pp_preview'; preview.className='pp_preview';
                var descr = document.createElement('div'); descr.className='pp_descr'; descr.innerHTML = '<span>'+caption+'</span>';
                preview.appendChild(large); preview.appendChild(descr);
                var oldById = document.getElementById('pp_preview'); if(oldById) oldById.remove();
                gallery.prepend(preview);
                var largeW = large.width + 20; var largeH = large.height + 10 + 45;
                preview.style.width=largeW+'px'; preview.style.height=largeH+'px'; preview.style.visibility='visible';
                if(typeof Cufon!=='undefined'){ try{ Cufon.replace('.pp_descr'); }catch(e){} }
                showNavigation(); if(loader) loader.style.display='none';
                var r = Math.floor(Math.random()*41)-20; preview.style.setProperty('--pp-rot', r+'deg');
                requestAnimationFrame(function(){ void preview.offsetWidth; requestAnimationFrame(function(){ preview.classList.add('enter'); enableshow=true; }); });
            }
            if(document.getElementById('pp_preview')) hideCurrentPicture(doCreate); else doCreate();
        };
        large.onerror = function(){};
        large.src = src;
    }

    if(nextBtn) nextBtn.addEventListener('click', function(){ 
        var albumEl = albums[album]; 
        var list = bySelAll('.content', albumEl);
        current++;
        if(current >= list.length) current = 0; // 循环到第一张
        showImage.call(null, 1); 
    });
    if(prevBtn) prevBtn.addEventListener('click', function(){ 
        var albumEl = albums[album]; 
        var list = bySelAll('.content', albumEl);
        current--;
        if(current < 0) current = list.length - 1; // 循环到最后一张
        showImage.call(null, -1); 
    });

    function hideCurrentPicture(done){
        var pp = document.getElementById('pp_preview');
        if(!pp){ if(typeof done==='function') done(); return; }
        pp.classList.remove('enter'); void pp.offsetWidth; pp.classList.add('exit');
        var handled=false; var onEnd=function(){ if(handled) return; handled=true; pp.removeEventListener('transitionend', onEnd); if(pp.parentNode) pp.parentNode.removeChild(pp); if(typeof done==='function') done(); };
        pp.addEventListener('transitionend', onEnd); setTimeout(onEnd,700);
    }

    function showNavigation(){ 
        if(nextBtn) animateElement(nextBtn, 'right', '0px', 100);
        if(prevBtn) animateElement(prevBtn, 'left', '0px', 100);
    }
    function hideNavigation(){ 
        if(nextBtn) animateElement(nextBtn, 'right', '-40px', 300);
        if(prevBtn) animateElement(prevBtn, 'left', '-40px', 300);
    }
    function upImage(){ 
        var img = this.querySelector('img'); 
        // 添加平滑上升动效
        animateElement(this, 'marginTop', '-70px', 400);
        if(img){ 
            img.style.transform='rotate(0deg)'; 
            img.style.webkitTransform='rotate(0deg)'; 
        } 
    }
    function downImage(){ 
        var img = this.querySelector('img'); 
        var r = Math.floor(Math.random()*41)-20; 
        // 添加平滑下降动效
        animateElement(this, 'marginTop', '0px', 400);
        if(img){ 
            img.style.transform='rotate('+r+'deg)'; 
            img.style.webkitTransform='rotate('+r+'deg)'; 
        } 
    }

    function resizeNative(imageEl){
        var widthMargin=50, heightMargin=200;
        var windowH=(window.innerHeight||document.documentElement.clientHeight)-heightMargin;
        var windowW=(window.innerWidth||document.documentElement.clientWidth)-widthMargin;
        var theImage=new Image(); theImage.src=imageEl.src;
        var imgwidth=theImage.width, imgheight=theImage.height;
        if((imgwidth>windowW)||(imgheight>windowH)){
            if(imgwidth>imgheight){
                var newwidth=windowW; var ratio=imgwidth/windowW; var newheight=imgheight/ratio; theImage.height=newheight; theImage.width=newwidth;
                if(newheight>windowH){ var newnewheight=windowH; var newratio=newheight/windowH; var newnewwidth=newwidth/newratio; theImage.width=newnewwidth; theImage.height=newnewheight; }
            } else {
                var newheight=windowH; var ratio2=imgheight/windowH; var newwidth2=imgwidth/ratio2; theImage.height=newheight; theImage.width=newwidth2;
                if(newwidth2>windowW){ var newnewwidth2=windowW; var newratio2=newwidth2/windowW; var newnewheight2=newheight/newratio2; theImage.height=newnewheight2; theImage.width=newnewwidth2; }
            }
        }
        imageEl.style.width=theImage.width+'px'; imageEl.style.height=theImage.height+'px';
    }

    // 简单的动画函数，模拟jQuery的animate
    function animateElement(element, property, targetValue, duration, callback) {
        if (!element) return;
        
        var startTime = performance.now();
        var startValue = getComputedStyle(element)[property];
        
        // 解析数值和单位
        var parseValue = function(val) {
            if (property === 'transform') return val;
            var match = val.match(/(-?\d*\.?\d+)(.*)/);
            return match ? { value: parseFloat(match[1]), unit: match[2] || 'px' } : { value: 0, unit: 'px' };
        };
        
        var start = parseValue(startValue);
        var target = parseValue(targetValue);
        
        if (property === 'transform') {
            element.style[property] = targetValue;
            if (callback) setTimeout(callback, duration);
            return;
        }
        
        function animate() {
            var elapsed = performance.now() - startTime;
            var progress = Math.min(elapsed / duration, 1);
            
            // 缓动函数 (easeOutQuart)
            var eased = 1 - Math.pow(1 - progress, 4);
            
            var currentValue = start.value + (target.value - start.value) * eased;
            element.style[property] = currentValue + target.unit;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else if (callback) {
                callback();
            }
        }
        
        requestAnimationFrame(animate);
    }

    // 启动
    // 不依赖 jQuery，直接执行布局预加载逻辑
    // 预加载完成后会调用 layoutAlbums()
})();
</script>
