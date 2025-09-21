<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="图片画廊 - ray" />
	<link rel="stylesheet" href="/assets/css/pictures.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/assets/css/footer.css" type="text/css" />
	<title>图片 · ray</title>
</head>
<body>
<?php require_once $INCLUDE_HEADER; ?>

<div id="ray-pic-root" class="ray-pic-root">
	<header id="ray-pic-header" class="ray-pic-header">
		<h1 id="ray-pic-title">图片 · 游戏 · 同人 · 插画</h1>
		<p id="ray-pic-sub">Illustrator · game · comic</p>
	</header>

	<main id="ray-pic-main" class="ray-pic-main">
		<!-- 红框：缩略图库 / 列表 -->
		<aside id="ray-pic-sidbarbox" class="ray-pic-sidbarbox">
			<nav id="ray-pic-thumbs" class="ray-pic-thumbs">
				<section class="ray-pic-section">
					<h2 class="ray-pic-section-title">Animals</h2>
					<ul class="ray-pic-list clearfix">
						<li><a href="/assets/images/fullscreen/a_2.jpg" rel="gallery1" title="..."><img src="/assets/images/thumbnails/a_2.gif" width="60" height="60" alt="World of Warcraft" /></a></li>
						<li><a href="/assets/images/fullscreen/a_3.jpg" rel="gallery1"><img src="/assets/images/thumbnails/a_3.gif" width="60" height="60" alt="Nice Night" /></a></li>
						<li><a href="/assets/images/fullscreen/a_4.jpg" rel="gallery1"><img src="/assets/images/thumbnails/a_4.gif" width="60" height="60" alt="Darksiders" /></a></li>
						<li><a href="/assets/images/fullscreen/a_5.jpg" rel="gallery1"><img src="/assets/images/thumbnails/a_5.gif" width="60" height="60" alt="Dante horses" /></a></li>
					</ul>
				</section>

				<section class="ray-pic-section">
					<h2 class="ray-pic-section-title">Games</h2>
					<ul class="ray-pic-list clearfix">
						<li><a href="/assets/images/fullscreen/1.jpg" rel="gallery1" title="都是些老图了"><img src="/assets/images/thumbnails/t_1.jpg" width="60" height="60" alt="World of Warcraft" /></a></li>
						<li><a href="/assets/images/fullscreen/2.jpg" rel="gallery1"><img src="/assets/images/thumbnails/t_2.jpg" width="60" height="60" alt="Nice Night" /></a></li>
						<li><a href="/assets/images/fullscreen/3.jpg" rel="gallery1"><img src="/assets/images/thumbnails/t_3.gif" width="60" height="60" alt="Darksiders" /></a></li>
					</ul>
				</section>

				<section class="ray-pic-section">
					<h2 class="ray-pic-section-title">Gallery comic</h2>
					<ul class="ray-pic-list clearfix">
						<li><a href="/assets/images/fullscreen/c01.jpg" rel="gallery2"><img src="/assets/images/thumbnails/c_1.jpg" width="60" height="60" alt="" /></a></li>
						<li><a href="/assets/images/fullscreen/c02.jpg" rel="gallery2"><img src="/assets/images/thumbnails/c_2.jpg" width="60" height="60" alt="" /></a></li>
					</ul>
				</section>

				<section class="ray-pic-section">
					<h2 class="ray-pic-section-title">Special zone</h2>
					<ul class="ray-pic-list clearfix">
						<li><a href="/assets/images/fullscreen/s02.jpg" rel="gallery-special"><img src="/assets/images/thumbnails/s_2.gif" width="60" height="60" alt="" /></a></li>
					</ul>
				</section>
			</nav>
		</aside>

		<!-- 蓝框：展示区 -->
		<section id="ray-pic-display-area" class="ray-pic-display-area">
			<div id="ray-pic-display-shell" class="ray-pic-display-shell">
				<!-- 绿框：最终图片显示区（用于 JS 控制或无 JS 回退） -->
				<div id="ray-pic-final" class="ray-pic-final" role="region" aria-label="展示区">
					<p class="ray-pic-instruction">点击左侧缩略图以在此处预览大图。若无 JS 支持，将在新标签页打开图片。</p>
				</div>
			</div>
		</section>
	</main>

	<?php require_once $INCLUDE_FOOTER; ?>
</div>

<!-- 加载 modern 无依赖 lightbox（若需要） -->
<script src="/assets/js/simple-lightbox.js"></script>

</body>
</html>
        <div class="width">
