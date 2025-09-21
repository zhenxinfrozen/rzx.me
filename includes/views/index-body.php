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

                    if ($image === 'fin12-11-9.jpg') {
                        $haha = '<div style="background:#fff; height:550px;width:100%;"></div>';
                        if (file_exists(__DIR__ . '/../../public/testcss(1)/index.php')) {
                            include __DIR__ . '/../../public/testcss(1)/index.php';
                        }
                    }

                    if ($image === 'frog1.jpg') {
                        $haha = '<div style="background:url(' . $img_folder . 'haha/fly.gif) no-repeat 70% 20%;height:550px;width:100%;"></div>';
                    }

                    $localPath = $img_dir . $image;
                    if (is_file($localPath)) {
                        $bgUrl = $img_folder . $image;
                    } else {
                        $bgUrl = $img_folder . $image;
                    }

                    $bgUrlEsc = htmlspecialchars($bgUrl, ENT_QUOTES | ENT_HTML5);
                    echo '<div style="background:url('. $bgUrlEsc .') no-repeat center;height:550px;width:100%;">'.$haha.'</div>';
                } else {
                    echo '<div style="background:#eee; height:550px; width:100%; text-align:center; line-height:550px;">没有找到图片</div>';
                }
            ?>
        </div>
    </div>
    <div class="menu">
        <a href="ray-animation.php" target="_self"><div class="menu_box"><div class="f1"></div><div class="t1">Animation</div></div></a>
        <a href="ray-latest.php" target="_self"><div class="menu_box"><div class="f2"></div><div class="t2">Latest</div></div></a>
        <a href="ray-comic.php" target="_self"><div class="menu_box"><div class="f3"></div><div class="t3">Comic</div></div></a>
        <a href="ray-sites.php" target="_self">
            <div class="menu_box">
                <div class="menu_icon_wrapper">
                    <img src="assets/images/menu-cion-blog-wordpress-logo.png" alt="Blog" class="menu_icon" width="100" height="100">
                </div>
                <div class="t4">Blog</div>
            </div>
        </a>
        <a href="ray-sketch.php" target="_self"><div class="menu_box"><div class="f5"></div><div class="t5">SketchBooks</div></div></a>
        <a href="ray-pictures.php" target="_self"><div class="menu_box"><div class="f6"></div><div class="t6">Pictures</div></div></a>
        <a href="ray-about.php" target="_self"><div class="menu_box"><div class="f7"></div><div class="t7">About Me</div></div></a>
    </div>
</div>
    <script>
    // Small vanilla JS to trigger click feedback for menu boxes (no jQuery)
    (function(){
        document.addEventListener('click', function(ev){
            var el = ev.target;
            // find closest .menu_box
            while(el && el !== document.body){
                if (el.classList && el.classList.contains('menu_box')){
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
