<?php
/**
 * ray-pictures.php — 清理版
 * 说明：本文件为图片画廊页面的主模板（已清理历史残留），包含：
 *  - 缩略图侧栏（#ray-pic-thumbs）
 *  - 中央展示区（#ray-pic-final），由 ray-pic.js 控制图片预览
 *  - 使用简单语义结构（section/aside/nav/ul/li），便于无 JS 回退和可访问性
 *
 * 注意：页面引用的 JS 文件为 /assets/js/ray-pic.js（轻量预览脚本），如需更改请同时更新此处。
 */
require_once __DIR__ . '/../includes/bootstrap.php';
?>
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

	<div id="ray-pic-sidebar">
		<header id="ray-pic-header" class="ray-pic-header">
			<h1 id="ray-pic-title">图片 · 游戏 · 同人 · 插画</h1>
			<p id="ray-pic-sub">Illustrator · game · comic</p>
		</header>

		<main id="ray-pic-main" class="ray-pic-main">
			<!-- 缩略图库 / 列表 -->
			<aside id="ray-pic-sidbarbox" class="ray-pic-sidbarbox">
				<nav id="ray-pic-thumbs" class="ray-pic-thumbs">
					<!-- 内容保持不变（缩略图列表由编辑器维护） -->
					<?php /* 缩略图列表在此插入（保留原有 HTML） */ ?>
					<section class="ray-pic-section">
						<h2 class="ray-pic-section-title">Animals</h2>
						<ul class="ray-pic-list clearfix">
							<li><a href="/assets/images/fullscreen/a_2.jpg" rel="gallery1" title="..."><img loading="lazy" src="/assets/images/thumbnails/a_2.gif" width="60" height="60" alt="World of Warcraft" /></a></li>
							<li><a href="/assets/images/fullscreen/a_3.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_3.gif" width="60" height="60" alt="Nice Night" /></a></li>
							<li><a href="/assets/images/fullscreen/a_4.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_4.gif" width="60" height="60" alt="Darksiders" /></a></li>
							<li><a href="/assets/images/fullscreen/a_5.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_5.gif" width="60" height="60" alt="Dante horses" /></a></li>
							<li><a href="/assets/images/fullscreen/a_6.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_6.gif" width="60" height="60" alt="Footman Human" /></a></li>
							<li><a href="/assets/images/fullscreen/a_7.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_7.gif" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/a_8.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_8.gif" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/a_9.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/a_9.gif" width="60" height="60" alt="" /></a></li>
						</ul>
					</section>

					<section class="ray-pic-section">
						<h2 class="ray-pic-section-title">Games</h2>
						<ul class="ray-pic-list clearfix">
							<li><a href="/assets/images/fullscreen/1.jpg" rel="gallery1" title="都是些老图了,彩图数量有限(一半都是鼠绘)充数."><img loading="lazy" src="/assets/images/thumbnails/t_1.jpg" width="60" height="60" alt="World of Warcraft" /></a></li>
							<li><a href="/assets/images/fullscreen/2.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_2.jpg" width="60" height="60" alt="Nice Night" /></a></li>
							<li><a href="/assets/images/fullscreen/3.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_3.gif" width="60" height="60" alt="Darksiders" /></a></li>
							<li><a href="/assets/images/fullscreen/4.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_4.jpg" width="60" height="60" alt="Sylvanas Windrunner" /></a></li>
							<li><a href="/assets/images/fullscreen/5.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_5.jpg" width="60" height="60" alt="Dante horses" /></a></li>
							<li><a href="/assets/images/fullscreen/6.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_6.jpg" width="60" height="60" alt="Footman Human" /></a></li>
							<li><a href="/assets/images/fullscreen/7.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_7.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/p01.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/p_1.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/8.jpg" rel="gallery1"><img loading="lazy" src="/assets/images/thumbnails/t_8.jpg" width="60" height="60" alt="" /></a></li>
						</ul>
					</section>

					<section class="ray-pic-section">
						<h2 class="ray-pic-section-title">Gallery comic</h2>
						<ul class="ray-pic-list clearfix">
							<li><a href="/assets/images/fullscreen/c01.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_1.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c02.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_2.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/d01.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/d_1.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c03.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_3.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c04.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_4.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c05.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_5.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c06.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_6.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c07.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_7.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/c08.jpg" rel="gallery2"><img loading="lazy" src="/assets/images/thumbnails/c_8.jpg" width="60" height="60" alt="" /></a></li>
							<li><a href="/assets/images/fullscreen/s01.jpg" rel="gallery2" title="&lt;a href=&#x27;http://www.ruizhenxin.com/blog&#x27; target=&#x27;_blank&#x27; &gt;This will open [My Blog] in a new window&lt;/a&gt;"><img loading="lazy" src="/assets/images/thumbnails/s_1.jpg" width="60" height="60" alt="" /></a></li>
						</ul>
					</section>

					<section class="ray-pic-section">
						<h2 class="ray-pic-section-title">Special zone</h2>
						<ul class="ray-pic-list clearfix">
							<li><a href="/assets/images/fullscreen/s02.jpg" rel="gallery-special" title="&lt;a href=&#x27;http://www.ruizhenxin.com/blog&#x27; target=&#x27;_blank&#x27; &gt;This will open [My Blog] in a new window&lt;/a&gt;"><img loading="lazy" src="/assets/images/thumbnails/s_2.gif" width="60" height="60" alt="" /></a></li>
						</ul>
					</section>
				</nav>
			</aside>

		</main>

	</div>

	<!-- 展示区 -->
	<section id="ray-pic-display-area" class="ray-pic-display-area">
		<div id="ray-pic-display-shell" class="ray-pic-display-shell">
			<div id="ray-pic-final" class="ray-pic-final" role="region" aria-label="展示区">
				<p class="ray-pic-instruction">请选择左侧缩略图以在此处预览。</p>
			</div>
		</div>
	</section>
</div>

<?php require_once $INCLUDE_FOOTER; ?>
<!-- 页面脚本：simple-lightbox 为可选 overlay，ray-pic.js 为本页预览脚本（已清理并格式化） -->
<script src="/assets/js/simple-lightbox.js"></script>
<script src="/assets/js/ray-pic.js"></script>

</body>
</html>
