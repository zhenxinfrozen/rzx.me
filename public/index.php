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
<link href="/assets/css/footer.css" rel="stylesheet" type="text/css" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3gJwYp8U+Y3f9C5v+XgYh2i1Yl3M7u1l9+8n8F0Y3Yg=" crossorigin="anonymous"></script>
<link href="testcss(1)/style/csslab.css" rel="stylesheet" type="text/css" />
</head>

<body>
<?php require_once $INCLUDE_HEADER; ?>
<div class="wrap">
  
    <div class="main">
        <div id="wallpaper">
            <?php
                $haha = ''; // 先初始化
                $img_folder = "assets/images/home/";
                $imglist = array();

                // 检查目录是否存在
                if (is_dir($img_folder)) {
                    if ($dh = opendir($img_folder)) {
                        while (($file = readdir($dh)) !== false) {
                            if (preg_match('/\.(gif|jpg|png)$/i', $file)) {
                                $imglist[] = $file;
                            }
                        }
                        closedir($dh);
                    }
                }

                $no = count($imglist) - 1;
                if ($no >= 0) {
                    // 为避免从 float 隐式转换到 int 的警告，显式转换为 int
                    // 在 PHP 7+ 中实际上不需要手动 seed，但保留兼容性代码
                    mt_srand((int)(microtime(true) * 1000));
                    $random = mt_rand(0, $no);
                    $image = $imglist[$random];

                    if ($image == "fin12-11-9.jpg") {
                        $haha = '<div style="background:#fff; height:550px;width:100%;"></div>';
                        if (file_exists('testcss(1)/index.php')) {
                            include 'testcss(1)/index.php';
                        }
                    }
                    if ($image == "frog1.jpg") {
                        $haha = '<div style="background:url(' . $img_folder . 'haha/fly.gif) no-repeat 70% 20%;height:550px;width:100%;"></div>';
                    }

                    // 优先使用本地相对路径，如果本地不存在再回退到远程服务器
                    $localPath = $img_folder . $image;
                    if (file_exists($localPath)) {
                        $bgUrl = $localPath;
                    } else {
                        // Prefer local path if image exists in assets/images
                        $localPath = '' . $img_folder . $image;
                        if (file_exists($localPath)) {
                            $bgUrl = $localPath;
                        } else {
                            $bgUrl = '' . $img_folder . $image;
                        }
                    }

                    echo '<div style="background:url('. $bgUrl .') no-repeat center;height:550px;width:100%;">'.$haha.'</div>';
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
        <a href="ray-sites.php" target="_self"><div class="menu_box"><div class="f4"></div><div class="t4">Blog</div></div></a>
        <a href="ray-sketch.php" target="_self"><div class="menu_box"><div class="f5"></div><div class="t5">SketchBooks</div></div></a>
        <a href="ray-pictures.php" target="_self"><div class="menu_box"><div class="f6"></div><div class="t6">Pictures</div></div></a>
        <a href="ray-contact.php" target="_self"><div class="menu_box"><div class="f7"></div><div class="t7">About Me</div></div></a>
    </div>
</div>
<?php require_once $INCLUDE_FOOTER; ?>
</body>
</html>