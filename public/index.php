<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>Art of Ray | Home</title>
<meta name="keywords" content="漫画，原画,电影分镜头，故事板,Comic,Storyboard,Concept art," />
<meta name="description" content=" This site is a portfolio of Ray art. Hope you enjoy it!" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<meta name="copyright" content="All images copyright Ray zhenxin" />
<meta http-equiv="refresh" content="600" />
<link rel="icon" href="favicon.ico" type="image/png" />
<link rel="shortcut icon" href="favicon.ico" type="image/png" />
<link href="assets/css/home_style.css" rel="stylesheet" type="text/css" />
<link href="testcss(1)/style/csslab.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php require_once $INCLUDE_HEADER; ?>
<div class="wrap">
  
    <div class="main">
        <div id="wallpaper">
            <?php
                $haha = '';
                $img_folder = 'assets/images/home/'; // web-relative URL prefix
                $img_dir = __DIR__ . '/' . $img_folder; // filesystem path
                $imglist = [];

                // 使用 DirectoryIterator 更可靠地列出图片文件
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
                        // 如果出现异常，保留 $imglist 为空并在下面显示占位
                    }
                }

                $count = count($imglist);
                if ($count > 0) {
                    // 使用更现代的 random_int，如果不可用则回退到 mt_rand
                    if (function_exists('random_int')) {
                        $random = random_int(0, $count - 1);
                    } else {
                        mt_srand((int)(microtime(true) * 1000));
                        $random = mt_rand(0, $count - 1);
                    }
                    $image = $imglist[$random];

                    if ($image === 'fin12-11-9.jpg') {
                        $haha = '<div style="background:#fff; height:550px;width:100%;"></div>';
                        if (file_exists(__DIR__ . '/testcss(1)/index.php')) {
                            include __DIR__ . '/testcss(1)/index.php';
                        }
                    }

                    if ($image === 'frog1.jpg') {
                        $haha = '<div style="background:url(' . $img_folder . 'haha/fly.gif) no-repeat 70% 20%;height:550px;width:100%;"></div>';
                    }

                    // 以 web 相对路径作为背景 URL（但通过文件系统检查本地文件是否存在）
                    $localPath = $img_dir . $image;
                    if (is_file($localPath)) {
                        $bgUrl = $img_folder . $image;
                    } else {
                        // 回退到相同的相对 URL（保持原有行为）
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
        <a href="ray-contact.php" target="_self"><div class="menu_box"><div class="f7"></div><div class="t7">About Me</div></div></a>
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
<?php require_once $INCLUDE_FOOTER; ?>
</body>
</html>