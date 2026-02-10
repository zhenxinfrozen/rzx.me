<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>Sketch - Art of Ray| Home</title>
<meta name="keywords" content="漫画，原画,电影分镜头，故事板,Comic,Storyboard,Concept art," />
<meta name="description" content=" This site is a portfolio of Ray art. Hope you enjoy it!" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<meta name="copyright" content="All images copyright Ray zhenxin" />
<meta http-equiv="refresh" content="600" />
<link rel="icon" href="favicon.ico" type="image/png" />
<link rel="shortcut icon" href="favicon.ico" type="image/png" />
<link href="css/home_style.css" rel="stylesheet" type="text/css" />
<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
<link href="testcss/style/csslab.css" rel="stylesheet" type="text/css" />
</head>


<body>
<?php include_once("analytics.php") ?>


<?php include_once("header.php") ?>

<div class="wrap">
<!-- 主体部分   Main -->
	<div id="main">
		<div id="wallpaper">
			<?php
				$imglist=''; 
				$img_folder = "img/sketch/";
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
				echo '<div style="background:url(/'.$img_folder.$image.') no-repeat center top;height:2800px;width:100%;"></div>';?>
		</div>
	</div>
</div>
<div style="height:10px; width:500px;margin:0 auto;" ></div>

<footer class="flooter">
</footer>
</body>
</html>
