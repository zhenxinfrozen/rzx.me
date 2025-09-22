<?php
// includes/views/index-body.php
// Main body content for public/index.php — kept as a view for easier edits.
?>
<div class="wrap">
  
    <div class="main">
        <div id="wallpaper">
            <?php
                $haha = '';
                $img_folder = 'assets/images/home/'; // web-relative URL prefix
                $img_dir = __DIR__ . '/../../public/' . $img_folder; // filesystem path
                $imglist = [];

                if (is_dir($img_dir)) {
                    try {
                        foreach (new DirectoryIterator($img_dir) as $fileinfo) {
                            if ($fileinfo->isFile()) {
                                $ext = strtolower($fileinfo->getExtension());
                                if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png'], true)) {
                                    $imglist[] = $fileinfo->getFilename();
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // leave $imglist empty on error
                    }
                }

                $count = count($imglist);
                if ($count > 0) {
                    if (function_exists('random_int')) {
                        $random = random_int(0, $count - 1);
                    } else {
                        mt_srand((int)(microtime(true) * 1000));
                        $random = mt_rand(0, $count - 1);
                    }
                    $image = $imglist[$random];

                    if ($image === 'empty-img-opacity0.png') {
                        $haha = '<div id="ray-home-wallpaper-item"></div>';
                        if (file_exists(__DIR__ . '/../../public/dev/css-test-colorcube.php')) {
                            include __DIR__ . '/../../public/dev/css-test-colorcube.php';
                        }
                    }

                    if ($image === 'frog1.jpg') {
                        $haha = '<div style="background:url(' . $img_folder . '/haha/fly.gif) no-repeat 0px 0px;z-index: 200;position: fixed;width: 100px;height: 100px;"></div>';
                    }

                    $localPath = $img_dir . $image;
                    if (is_file($localPath)) {
                        $bgUrl = $img_folder . $image;
                    } else {
                        $bgUrl = $img_folder . $image;
                    }

                    $bgUrlEsc = htmlspecialchars($bgUrl, ENT_QUOTES | ENT_HTML5);
                    echo '<div id="ray-home-wallpaper-item"><img src="'. $bgUrlEsc .'" alt="Blog"  >'.$haha.'</div>';
                } else {
                    echo '<div style="background:#eee; height:550px; width:100%; text-align:center; line-height:550px;">没有找到图片</div>';
                }
            ?>
        </div>
    </div>
</div>
<div class="menu">
    <a href="ray-animation.php" target="_self"><div class="ray-home-menu-icon"><div class="f1"></div><div class="menu-text">Animation</div></div></a>
    <a href="ray-latest.php" target="_self"><div class="ray-home-menu-icon"><div class="f2"></div><div class="menu-text">Latest</div></div></a>
    
    <a href="ray-comic.php" target="_self">
        <div class="ray-home-menu-icon">
            <div class="f3">
            </div>
            <div class="menu-text">Comic</div>
        </div>
    </a>

    <a href="ray-sites.php" target="_self">
        <div id="ray-home-menu-blog" class="ray-home-menu-icon">
            <div id="menu_icon_wrapper">
                <img src="assets/images/ray-home-menu-wordpress-logo.png" alt="Blog" id="wordpress-logo" width="100" height="100">
            </div>
            <div class="menu-text">Blog</div>
        </div>
    </a>
    <a href="ray-sketch.php" target="_self"><div class="ray-home-menu-icon"><div class="f5"></div><div class="menu-text">SketchBooks</div></div></a>
    <a href="ray-pictures.php" target="_self">
        <div id="ray-home-menu-pictures" class="ray-home-menu-icon">
            <div class="menu_icon_wrapper">
                <img src="assets/images/ray-home-menu-pictures.png" alt="Blog" class="menu_icon" width="100" height="100">
            </div>
            <div class="menu-text">Pictures</div>
        </div>
    </a>
    <a href="ray-about.php" target="_self">
        <div class="ray-home-menu-icon">
            <div class="f7"></div>
            <div class="menu-text">About Me</div>
        </div>
    </a>
</div>
    <script>
    // Small vanilla JS to trigger click feedback for menu boxes (no jQuery)
    (function(){
        document.addEventListener('click', function(ev){
            var el = ev.target;
            // find closest .ray-home-menu-blog
            while(el && el !== document.body){
                if (el.classList && el.classList.contains('ray-home-menu-blog')){
                    el.classList.add('clicked');
                    // remove after 600ms
                    setTimeout(function(){ el.classList.remove('clicked'); }, 600);
                    return;
                }
                el = el.parentNode;
            }
        }, false);
    })();
    </script>

    <!-- Inject SVG filter and bind blur animation to menu images -->
    <script>
    (function(){
        var START_X = 8, START_Y = 0, DURATION = 200;
        var svgns = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgns, 'svg');
        svg.setAttribute('width','0'); svg.setAttribute('height','0'); svg.style.position='absolute';
        var defs = document.createElementNS(svgns,'defs');
        var filter = document.createElementNS(svgns,'filter');
        filter.setAttribute('id','motion-blur-horizontal');
        filter.setAttribute('x','-50%'); filter.setAttribute('y','-50%'); filter.setAttribute('width','200%'); filter.setAttribute('height','200%');
        var gblur = document.createElementNS(svgns,'feGaussianBlur');
        gblur.setAttribute('in','SourceGraphic'); gblur.setAttribute('stdDeviation', START_X + ' ' + START_Y);
        filter.appendChild(gblur); defs.appendChild(filter); svg.appendChild(defs); document.body.appendChild(svg);

        function ease(t){ return t<.5?2*t*t:-1+(4-2*t)*t; }

        function bindAnim(img){
            if (!img) return;
            // Ensure this image uses our SVG filter so changing stdDeviation is visible
            try {
                img.style.filter = 'url(#motion-blur-horizontal)';
                img.style.willChange = 'filter';
            } catch (e) {
                // ignore if style assignment fails in some contexts
            }
            var raf=null, startTs=null, fromVal=START_X, toVal=0;
            function step(ts){ if (!startTs) startTs=ts; var elapsed = ts - startTs; var p = Math.min(1, elapsed / DURATION); p = ease(p); var cur = fromVal + (toVal - fromVal) * p; gblur.setAttribute('stdDeviation', cur + ' ' + START_Y); if (elapsed < DURATION) raf = requestAnimationFrame(step); else { startTs=null; raf=null; } }
            function startAnim(f,t){ if (raf) cancelAnimationFrame(raf); fromVal = f; toVal = t; startTs=null; raf = requestAnimationFrame(step); }
            var readCur = function(){ var v = parseFloat(gblur.getAttribute('stdDeviation')); return isNaN(v)?START_X:v; };
            img.addEventListener('mouseenter', function(){ startAnim(readCur(), 0); });
            img.addEventListener('mouseleave', function(){ startAnim(readCur(), START_X); });
            img.addEventListener('focus', function(){ startAnim(readCur(), 0); });
            img.addEventListener('blur', function(){ startAnim(readCur(), START_X); });
            img.addEventListener('touchstart', function(e){ e.preventDefault(); startAnim(readCur(), 0); }, {passive:false});
            img.addEventListener('touchend', function(){ startAnim(readCur(), START_X); });
        }

        // Strict: only bind to images that are descendants of #ray-home-menu-pictures.
        var targets = [];
        var container = document.getElementById('ray-home-menu-pictures');
        if (container) {
            var imgs = container.querySelectorAll('img');
            Array.prototype.forEach.call(imgs, function(n){ targets.push(n); });
            if (targets.length === 0) {
                console.log('[menu-blur] found #ray-home-menu-pictures but no <img> inside it');
            } else {
                console.log('[menu-blur] binding to images under #ray-home-menu-pictures', targets.length);
            }
        } else {
            console.log('[menu-blur] #ray-home-menu-pictures not found; no bindings applied');
        }

        targets.forEach(function(img){ bindAnim(img); });
    })();
    </script>
