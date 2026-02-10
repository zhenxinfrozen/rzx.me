<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>斑斓色彩 | COLORFUL</title>
<meta name="copyright" content="All images copyright Ray.© Ray" />
<meta name="keywords" content="动画，漫画，图片，电影，动漫，芮真心，分镜头台本，颓废的动画人" />
<meta name="description" content="我不太喜欢也不太擅长的色彩作品" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<link href="css/home_style.css" rel="stylesheet" type="text/css" />
<script src="js/main.js" language="javascript"></script>
</head>


<body>
<?php include_once("analytics.php") ?>

<?php include_once("header.php") ?>

<div class="wrap">
	<div id="main">
		<div id="wallpaper">
			<?php
				$imglist=''; 
				$img_folder = "img/illustration/";
				mt_srand((double)microtime()*1000);
				$imgs = dir($img_folder);
				while ($file = $imgs->read()) { 
				if (eregi("gif", $file) || eregi("jpg", $file) || eregi("png", $file))
				$imglist .= "$file ";
				} closedir($imgs->handle);
				$imglist = explode(" ", $imglist); 
				$no = sizeof($imglist)-2;
				$random = mt_rand(0, $no);
				$image = $imglist[$random];
				echo '<div id="roll-illustration" style="background:url(/'.$img_folder.$image.') no-repeat center top;height:1800px;width:100%;"></div>';?>
		</div>
	</div>
</div>
</body>
</html>