<?php
// 这是一个纯前端演示页面（CSS/JS），有意不通过 includes/bootstrap.php 引导。
// 我们将保留其作为可独立包含的片段，同时现代化结构与内联样式（不改变功能）。
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CSS 动画演示</title>

    <!-- 内联样式（从 style/csslab.css 提取） -->
    <style>
/* inlined from style/csslab.css */
.div {width:100px; height:100px; float:left;}
#header{
	font-size:40px;
	color:#fff;
	font-family:"Courier New", Courier, monospace;
	text-align:center;
}

#wutai{
	height:500px;
	width:1200px;
	margin:0 auto;
	padding:50px;
	-webkit-perspective: 900px;
	-webkit-perspective-origin: 50% 50%;
}
.box{
    /* minimal box defaults for dynamic absolute-positioned boxes */
    display:block;
    box-sizing:border-box;
    border:1px #CCC solid;
    background: red;
    will-change: transform, opacity;
}
@-webkit-keyframes myfirst /* Safari and Chrome */
{
0%   {background:red; left:-120px; top:0px;}
25%  {background:yellow; left:500px; top:0px;}
35%  {-webkit-border-radius:25px;}
50%  {background:blue; left:600px; top:200px;}
75%  {background:green; left:0px; top:200px;}
80%  {-webkit-transform: rotateX(132deg);}
90%  {width:300px;}
100% {-webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px);}
}

@-webkit-keyframes myfirst2 /* Safari and Chrome */
{
0%   {left:-220px; top:-123px;}
25%  {-webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px);}
35%  {-webkit-border-radius:25px;}
50%  { left:600px; top:200px;}
75%  {-webkit-transform: rotateX(132deg);}
80%  {left:0px; top:200px;}
90%  {height:30px;}
100% {left:500px; top:0px;}
}
@-webkit-keyframes myfirst4 /* Safari and Chrome */
{
0%   {left:-140px; top:-180px;}
25%  {-webkit-transform: rotateY(23deg) rotateZ(180deg) translateY(552px) translateZ(-2352px);}
35%  {-webkit-border-radius:25px;}
50%  {left:600px; top:60px;}
75%  {-webkit-transform: rotateX(19deg);}
80%  {left:0px; top:200px;}
90%   {-webkit-transform: rotateY(340deg) rotateZ(10deg) translateY(212px) translateZ(-252px);}
100% {left:500px; top:0px;}
}

@-webkit-keyframes myfirst5 /* Safari and Chrome */
{
0%   {left:-100px; top:-100px;}
25%  {-webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(-2352px);}
35%  {-webkit-border-radius:25px;}
50%  {left:600px; top:200px;}
75%  {-webkit-transform: rotateX(132deg);}
80%  {left:0px; top:200px;}
90%  {height:200px;}
100% {left:500px; top:0px;}
}
#box1{
	z-index: 100;
	opacity: 0.7;
	-webkit-transform: rotateY(56deg); /* Safari and Chrome */
-webkit-animation-name: myfirst4;
-webkit-animation-timing-function: linear;
-webkit-animation-duration: 5s;
-webkit-animation-delay: 0s;
-webkit-animation-iteration-count: infinite;
-webkit-animation-direction: alternate;
-webkit-animation-play-state: running;
}
#box2{
	z-index: 100;
	opacity: 0.7;
	-webkit-transform: rotateX(32deg); 	/* Safari 和 Chrome */
-webkit-animation-name: myfirst2;
-webkit-animation-duration: 5s;
-webkit-animation-timing-function: linear;
-webkit-animation-delay: 0s;
-webkit-animation-iteration-count: infinite;
-webkit-animation-direction: alternate;
-webkit-animation-play-state: running;
}
#box3{
	z-index: 100;
	opacity: 0.7;
	-webkit-transform: rotateY(32deg); 	/* Safari 和 Chrome */
-webkit-animation-name: myfirst;
-webkit-animation-duration: 5s;
-webkit-animation-timing-function: linear;
-webkit-animation-delay: 2s;
-webkit-animation-iteration-count: infinite;
-webkit-animation-direction: alternate;
-webkit-animation-play-state: running;
}
#box4{
	z-index: 100;
	opacity: 0.7;
	-webkit-perspective-style:preserve-3d;
	-webkit-transform: rotateY(123deg) rotateZ(34deg) translateY(552px) translateZ(552px); 	/* Safari 和 Chrome */
-webkit-animation-name: myfirst5;
-webkit-animation-duration: 5s;
-webkit-animation-timing-function: linear;
-webkit-animation-delay: 2s;
-webkit-animation-iteration-count: infinite;
-webkit-animation-direction: alternate;
-webkit-animation-play-state: running;
}
}
#box5{
	z-index: 100;
	opacity: 0.7;
	-webkit-transform: rotateY(120deg); 	/* Safari 和 Chrome */
}

#box1，#box2 li{
	list-style-type:none;
}

</style>
</head>

<body>
    <main>
        <section id="wutai" aria-label="演示舞台">
            <div id="box1" class="box" role="presentation"></div>
            <div id="box2" class="box" role="presentation"></div>
            <div id="box3" class="box" role="presentation"></div>
            <div id="box4" class="box" role="presentation"></div>
            <div id="box5" class="box" role="presentation"></div>
        </section>
    </main>

    <script>
    // 恢复并增强“初始方块”方式：周期性正弦运动（X/Y/Z），容器宽度为 100vw，BOX_COUNT 可调整
    (function(){
        var BOX_COUNT = 12; // 初始方块数量，可调整
        var boxes = [];
        var container = document.getElementById('wutai');

        function getMaxHeight(){ return Math.max(0, Math.round(window.innerHeight * 0.40)); }
        function applyContainerStyle(){
            container.style.position = 'relative';
            container.style.height = '40vh';
            container.style.width = '100vw'; // 不限制宽度，使用视口宽度
            container.style.overflow = 'hidden';
            container.style.perspective = '900px';
            container.style.perspectiveOrigin = '50% 50%';
        }

        function rand(min,max){ return Math.random()*(max-min)+min; }
        function randInt(max){ return Math.floor(Math.random()*max); }

        function hsl2color(hsl){
            if (hsl[0] > 360 || hsl[0] < 0 || hsl[1] > 100 || hsl[1] < 0 || hsl[2] > 100 || hsl[2] < 0) return '#000000';
            var rgb=[0,0,0];
            if(hsl[0]<=60){rgb[0]=255;rgb[1]=Math.floor(255/60*hsl[0]);}
            else if(hsl[0]<=120){rgb[0]=Math.floor(255-(255/60)*(hsl[0]-60));rgb[1]=255;}
            else if(hsl[0]<=180){rgb[1]=255;rgb[2]=Math.floor((255/60)*(hsl[0]-120));}
            else if(hsl[0]<=240){rgb[1]=Math.floor(255-(255/60)*(hsl[0]-180));rgb[2]=255;}
            else if(hsl[0]<=300){rgb[0]=Math.floor((255/60)*(hsl[0]-240));rgb[2]=255;}else{rgb[0]=255;rgb[2]=Math.floor(255-(255/60)*(hsl[0]-300));}
            var sat=Math.abs((hsl[1]-100)/100);
            rgb[0]=Math.floor(rgb[0]-(rgb[0]-128)*sat);rgb[1]=Math.floor(rgb[1]-(rgb[1]-128)*sat);rgb[2]=Math.floor(rgb[2]-(rgb[2]-128)*sat);
            var lum=(hsl[2]-50)/50;
            if(lum>0){rgb[0]=Math.floor(rgb[0]+(255-rgb[0])*lum);rgb[1]=Math.floor(rgb[1]+(255-rgb[1])*lum);rgb[2]=Math.floor(rgb[2]+(255-rgb[2])*lum);}else if(lum<0){rgb[0]=Math.floor(rgb[0]+rgb[0]*lum);rgb[1]=Math.floor(rgb[1]+rgb[1]*lum);rgb[2]=Math.floor(rgb[2]+rgb[2]*lum);}            
            return '#'+(0x1000000+rgb[0]*0x010000+rgb[1]*0x000100+rgb[2]).toString(16).substring(1);
        }

        function createInitialBoxes(){
            var containerW = container.clientWidth || window.innerWidth;
            var maxH = getMaxHeight();
            var period = 16000; // 全局周期 16s，开始和结束同状态
            var omega = 2 * Math.PI / period;

            for(var i=0;i<BOX_COUNT;i++){
                var el = document.createElement('div');
                el.className = 'box';
                var size = Math.round(rand(40,120));
                el.style.width = size + 'px';
                el.style.height = size + 'px';
                el.style.position = 'absolute';
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.92'; // 透明度不高
                el.style.willChange = 'transform, opacity';

                // 基准位置
                var baseX = rand(0, Math.max(1, containerW - size));
                var baseY = rand(0, Math.max(1, maxH - size));

                // 振幅：X/Y 取容器一部分，Z 取更大范围以增强 3D 感
                var ampX = rand(20, Math.max(40, containerW * 0.15));
                var ampY = rand(10, Math.max(20, maxH * 0.15));
                var ampZ = rand(60, 360); // px in Z axis

                // 相位随机化以增加差异但全局周期一致
                var phase = rand(0, Math.PI * 2);

                // 存储状态
                boxes.push({ el: el, baseX: baseX, baseY: baseY, ampX: ampX, ampY: ampY, ampZ: ampZ, phase: phase, size: size, period: period, omega: omega });
                container.appendChild(el);

                // 立即设置位置
                var x = baseX + ampX * Math.sin(phase + 0);
                var y = baseY + ampY * Math.sin(phase + 0);
                var z = ampZ * Math.sin(phase + 0);
                el.style.transform = 'translate3d(' + Math.round(x) + 'px, ' + Math.round(y) + 'px, ' + Math.round(z) + 'px)';
                el.style.backgroundColor = hsl2color([randInt(360), 100, 70]);
                el.style.borderRadius = Math.round(rand(0,30)) + 'px';
            }
        }

        var startTs = null;
        function step(ts){
            if (!startTs) startTs = ts;
            var t = ts - startTs; // ms since start
            var maxH = getMaxHeight();
            var containerW = container.clientWidth || window.innerWidth;

            boxes.forEach(function(b){
                var omega = b.omega;
                var phase = b.phase;
                var x = b.baseX + b.ampX * Math.sin(phase + omega * t);
                var y = b.baseY + b.ampY * Math.sin(phase + omega * t + Math.PI/6);
                var z = b.ampZ * Math.sin(phase + omega * t + Math.PI/3);

                // 如果竖直位置超出限制则隐藏
                if (y < 0 || (y + b.size) > maxH){
                    b.el.style.visibility = 'hidden';
                } else {
                    b.el.style.visibility = 'visible';
                }

                b.el.style.transform = 'translate3d(' + Math.round(x) + 'px, ' + Math.round(y) + 'px, ' + Math.round(z) + 'px)';
            });

            window.requestAnimationFrame(step);
        }

        document.addEventListener('DOMContentLoaded', function(){
            applyContainerStyle();
            createInitialBoxes();
            window.requestAnimationFrame(step);
            window.addEventListener('resize', function(){
                // 重新布局基础位置以响应宽度变化
                boxes.forEach(function(b){
                    var containerW = container.clientWidth || window.innerWidth;
                    b.baseX = Math.min(b.baseX, Math.max(1, containerW - b.size));
                });
            });
        });
    })();
    </script>

</body>
</html>


