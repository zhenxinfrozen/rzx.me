<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>Art of Ray 4.0 | Home</title>
<meta name="keywords" content="漫画，原画,电影分镜头，故事板,Comic,Storyboard,Concept art," />
<meta name="description" content=" This site is a portfolio of Ray art. Hope you enjoy it!" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<meta name="copyright" content="All images copyright Ray zhenxin" />
<meta http-equiv="refresh" content="600" />
<link rel="icon" href="favicon.ico" type="image/png" />
<link rel="shortcut icon" href="favicon.ico" type="image/png" />
<link href="css/home_style.css" rel="stylesheet" type="text/css" />
<script src="js/jquery-1.9.1.min.js" type="text/javascript"></script>
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
				$img_folder = "img/home/";
				mt_srand((double)microtime()*1000);
				$imgs = dir($img_folder);
				while ($file = $imgs->read()) {
				if (preg_match("/gif/", $file) || preg_match("/jpg/", $file) || preg_match("/png/", $file) || preg_match("/webm/", $file))
				$imglist .= "$file ";
				} closedir($imgs->handle);
				$imglist = explode(" ", $imglist);
				$no = sizeof($imglist)-2;
				$random = mt_rand(0, $no);
				$image = $imglist[$random];
				if (($image=="webm01.webm") || ($image=="webm02.webm") || ($image=="webm03.webm") || ($image=="webm04.webm")){
					echo '  <video width="100%"  loop="loop" autoplay="autoplay" top="0">
    <source src="/'.$img_folder.$image.'" type="video/mp4" />
    <source src="/example/html5/mov_bbb.ogg" type="video/ogg" />
    Your browser does not support HTML5 video.
  </video> ';
				}
		// 		if (($image=="webm01.mp4") || ($image=="webm02.mp4") || ($image=="webm03.mp4") || ($image=="webm04.mp4")){
		// 			echo '  <video width="100%"  loop="loop" autoplay="autoplay">
  //   <source src="/'.$img_folder.$image.'" type="video/mp4" />
  //   Your browser does not support HTML5 video.
  // </video> ';
		// 		}
				else{
					$IMGheight = getimagesize("$img_folder$image");//获取图片的高度，然后用$IMGheight[1]调取高度
					echo '<div style="background:url(/'.$img_folder.$image.') no-repeat center top;height:'.$IMGheight[1].'px;width:100%;"></div>';}
				?>
		</div>
	</div>
</div>
<div style="height:10px; width:50px;margin:0 auto;" ></div>

<?php include_once("footer.php") ?>
</body>
</html>
