<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>动画段 | ANIMATION</title>
<meta name="copyright" content="站点所有原创图片版权归作者所有. 转载注明出处....." />
<meta name="keywords" content="动画，漫画，图片，电影，动漫，芮真心，分镜头台本，颓废的动画人" />
<meta name="description" content="作为动画专业的身份,还是有动画的练习和认真作品的.虽然不是真心感兴趣" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<link href="assets/css/animation.css" rel="stylesheet" type="text/css" />
<link href="/assets/css/footer.css" rel="stylesheet" type="text/css" />
</head>


<body>
<?php require_once $INCLUDE_HEADER; ?>
<div class="title">
动画Animation
</div>

<div id="ray-anim-wapper">
	<div id="ray-anim-showbox" aria-label="Ray Comic Showbox">
		<div class="anim-item" id="anim-1">
			<img src="../../assets/images/anim-001.png" alt="comic 1">
		</div>
		<div class="anim-item" id="anim-2">
			<img src="../../assets/images/anim-002.png" alt="comic 2">
		</div>
		<div class="anim-item" id="anim-3">
			<img src="../../assets/images/anim-003.png" alt="comic 3">
		</div>
		<div class="anim-item" id="anim-4">
			<img src="../../assets/images/anim-004.png" alt="comic 4">
		</div>
	</div>
</div>

<div id="ray-anim-videobox" aria-label="Ray Animation Video Showbox">
	<div class="title">
	视频Video
	</div>

	<div class="video-row">
		<div class="video-x">
				<video class="movie" poster="/assets/movie/shooting-01.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/shooting-01.mp4" type="video/mp4" />
					<source src="/assets/movie/shooting-01.webm" type="video/webm" />
				</video>
				<p>biu biu biu</p>
		</div>

		<div class="video-x">
				<video class="movie" poster="/assets/movie/flower.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/flower.mp4" type="video/mp4" />
					<source src="/assets/movie/flower.webm" type="video/webm" />
				</video>
				<p>flower</p>
		</div>

		<div class="video-x">
				<video class="movie" poster="/assets/movie/dragonfire.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/dragonfire.mp4" type="video/mp4" />
					<source src="/assets/movie/dragonfire.webm" type="video/webm" />
				</video>
				<p>dragonfire</p>
		</div>

		<div class="video-x">
				<video class="movie" poster="/assets/movie/begin-01.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/begin-01.mp4" type="video/mp4" />
					<source src="/assets/movie/begin-01.webm" type="video/webm" />
				</video>
				<p>动画片段 1</p>
		</div>

		<div class="video-x">
				<video class="movie" poster="/assets/movie/Gunman.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/Gunman.mp4" type="video/mp4" />
					<source src="/assets/movie/Gunman.webm" type="video/webm" />
				</video>
				<p>dragonfire</p>
		</div>

		<div class="video-x">
				<video class="movie" poster="/assets/movie/gun-shooting.jpg" preload controls width="355" height="200">
					<source src="/assets/movie/gun-shooting.mp4" type="video/mp4" />
					<source src="/assets/movie/gun-shooting.webm" type="video/webm" />
				</video>
				<p>dragonfire</p>
		</div>
			<div class="html5-tip">
		<p><img src="/assets/movie/html5-150.png" alt="HTML5" /></p>
		<p>本页面视频采用HTML5标准</p>
		<p>请使用<a href="http://www.google.cn/chrome/intl/zh-CN/landing_chrome.html?hl=zh">Chrome</a>, Firefox, 或者 Safari 浏览该网页.</p>
	</div>
	</div>
	

</div>
<?php require_once $INCLUDE_FOOTER; ?>
</body>
</html>
