<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Comic</title>
<link href="assets/css/comic.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/footer.css" rel="stylesheet" type="text/css" />
</head>


<body>
<?php require_once $INCLUDE_HEADER; ?>
<div class="comic_title">
	<font face="Tahoma, Geneva, sans-serif" color="#999999" size="7"><b>漫画Comic</b></font>
</div>
<a href="#"><div class="comic_dog">
</div></a>
<p align="center"><font face="Comic Sans MS, cursive" color="#999999" size="3"> Test page<font size="-1">（<b>测试</b>）</font> </font></p>
<div id="ray-comic-menu">
	<a href="/sketch-dream.html"><div id="c1" class="ray-comic-menu-icon"></div></a>
    <a href="/gzjy.html"><div id="c2" class="ray-comic-menu-icon"></div></a>
	<a href="/Wine.html"><div id="c3" class="ray-comic-menu-icon"></div></a>
    <a href="/MagicUbuntu.html"><div id="c4" class="ray-comic-menu-icon"></div></a>
    <a href="/ice-fire.html">
    <div id="c5" class="ray-comic-menu-icon">
        <img class="default-img" src="/assets/images/ray-comic-icefire-01.png" alt="comic 5">
        <img class="hover-img" src="/assets/images/ray-comic-icefire-02.png" alt="comic 5 hover">
    </div>
    </a>
</div>
<div class="comic_show">
</div>
<?php require_once $INCLUDE_FOOTER; ?>
</body>
</html>
