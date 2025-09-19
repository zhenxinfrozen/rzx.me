
<head>
<link href="style/csslab.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="wutai">
    <div id="box1" class="box">
    </div>
    <div id="box2" class="box">
    </div>
    <div id="box3" class="box">
    </div>
    <div id="box4" class="box">
    </div>
    <div id="box5" class="box">
    </div>
</div>


         
<script type="text/javascript">
window.onload=function(){
    var obj=document.getElementsByClassName('box');
    for(var i=0;i<obj.length;i++){
        obj[i].style.backgroundColor=hsl2color([radomFuc(360), 100, 70])
    }
}
         
function radomFuc($value) { //取随机数
    return parseInt($value * Math.random());
}
         
function hsl2color(hsl) { //HSL颜色算法
    if (hsl[0] > 360 || hsl[0] < 0 || hsl[1] > 100 || hsl[1] < 0 || hsl[2] > 100 || hsl[2] < 0) return "#000000";
    var rgb = [0, 0, 0];
    if (hsl[0] <= 60) {
        rgb[0] = 255;
        rgb[1] = Math.floor(255 / 60 * hsl[0]);
    } else if (hsl[0] <= 120) {
        rgb[0] = Math.floor(255 - (255 / 60) * (hsl[0] - 60));
        rgb[1] = 255;
    } else if (hsl[0] <= 180) {
        rgb[1] = 255;
        rgb[2] = Math.floor((255 / 60) * (hsl[0] - 120));
    } else if (hsl[0] <= 240) {
        rgb[1] = Math.floor(255 - (255 / 60) * (hsl[0] - 180));
        rgb[2] = 255;
    } else if (hsl[0] <= 300) {
        rgb[0] = Math.floor((255 / 60) * (hsl[0] - 240));
        rgb[2] = 255;
    } else if (hsl[0] <= 360) {
        rgb[0] = 255;
        rgb[2] = Math.floor(255 - (255 / 60) * (hsl[0] - 300));
    }
    var sat = Math.abs((hsl[1] - 100) / 100);
    rgb[0] = Math.floor(rgb[0] - (rgb[0] - 128) * sat);
    rgb[1] = Math.floor(rgb[1] - (rgb[1] - 128) * sat);
    rgb[2] = Math.floor(rgb[2] - (rgb[2] - 128) * sat);
    var lum = (hsl[2] - 50) / 50;
    if (lum > 0) {
        rgb[0] = Math.floor(rgb[0] + (255 - rgb[0]) * lum);
        rgb[1] = Math.floor(rgb[1] + (255 - rgb[1]) * lum);
        rgb[2] = Math.floor(rgb[2] + (255 - rgb[2]) * lum);
    } else if (lum < 0) {
        rgb[0] = Math.floor(rgb[0] + rgb[0] * lum);
        rgb[1] = Math.floor(rgb[1] + rgb[1] * lum);
        rgb[2] = Math.floor(rgb[2] + rgb[2] * lum);
    }
    return "#" + (0x1000000 + rgb[0] * 0x010000 + rgb[1] * 0x000100 + rgb[2]).toString(16).substring(1);
}
</script>


