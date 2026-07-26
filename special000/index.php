<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>Speical 特别企划 | Home</title>

  <link rel="stylesheet" href="jquery.mobile-1.3.1.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>

  <link href="../css/home_style.css" rel="stylesheet" type="text/css" />
  <script src="../js/jquery-1.9.1.min.js" type="text/javascript"></script>
  <link href="../testcss/style/csslab.css" rel="stylesheet" type="text/css" />
</head> 
<body> 

<?php include_once("../analytics.php") ?>



    <div data-role="content">
        <div class="article">
<!--             <p><img src="http://pic.rzx.me/rzxme/web/images/thumbnails/a_2.gif" alt="Fixed Gear bike"></p> -->
            <div style="width:100%;height:100px;"></div>
            <h2></h2>
            <!-- <p>杂物堆放处！</p> -->
            <!-- <p><a href="#right-panel" data-role="button" data-theme="b" data-inline="true" data-mini="true" data-shadow="false">目录</a></p> -->
        </div><!-- /article -->


<div style ="width:80%;  margin:0 auto;">
<?php
 
$imgtype=array('bmp','jpg','PNG','JPG','jpeg','png','gif');   //初始化图片文件扩展名
 
$imgtype_count=count($imgtype);     //计算共有多少图片扩展名
 
$path="pic"; //设定目录
 
$handle=opendir($path);     //打开目录
 
while ($file = readdir($handle))    //取得目录中的文件名
{
  if (is_dir($file)) {continue;}      //如果$file为目录，则不做操作
  $type = explode(".",$file);         //分割字符串
  $type=$type[1];     //得到文件扩展名

  //得到文件名（去掉扩展名）
$filename=$file; 
$filename=str_replace(strrchr($filename, "."),"",$filename); 

  for($i=0;$i<$imgtype_count;$i++)
  {
  if($type==$imgtype[$i])     //判断文件扩展名是否为图片文件的扩展名,若是则做下列输出
      {
      //echo"<a href=".$path."/".$file." target=\"_blank\" alt=\"点击打开新窗口浏览\"><img src=".$path."/".$file." border=\"0\" onload=\"if(this.height>150) {this.height=300;this.width=300*this.width/this.height;}\"></a>\n";
      echo'<a href="#'.$filename.'" data-rel="popup" data-position-to="window" data-transition="fade"><img class="popphoto" src="'.$path.'/'.$file.'" alt="Paris, France"'."border=\"0\" onload=\"if(this.height>150) {this.height=250;this.width=250*this.width/this.height;}\"></a>\n";
      echo'<div data-role="popup" id="'.$filename.'" data-overlay-theme="c" data-theme="c" data-corners="false">
    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right" style="background:#ABCD14; color:#FFF text-decoration:none"></a><img class="popphoto" src="'.$path."/big/B".$file.'" style="max-height:800px;" alt="Paris, France">
</div>';
      }
  }
}
closedir($handle);      //关闭目录
 
?>
</div>

    </div><!-- /content -->




</body>
</html>