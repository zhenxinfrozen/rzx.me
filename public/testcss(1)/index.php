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
	width:100px;
	height:100px;
	margin:50px auto;
	float:left;
	border:1px #CCC solid;
	background: red;
	padding:30px;
	position:relative;
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
    // 动画改进：使用 requestAnimationFrame 进行更平滑的运动，盒子初始位置/速度/尺寸随机化
    (function (){
        var BOX_COUNT = 5;
        var boxes = [];
        var container = document.getElementById('wutai');

        // 限制高度为视口的 40vh
        function getMaxHeight() {
            return Math.max(0, Math.round(window.innerHeight * 0.40));
        }

        // 初始化样式：容器限制高度并隐藏溢出
        function applyContainerStyle() {
            container.style.position = 'relative';
            container.style.height = '40vh';
            container.style.overflow = 'hidden';
        }

        function rand(min, max) { return Math.random() * (max - min) + min; }

        function randInt(max) { return Math.floor(Math.random() * max); }

        function hsl2color(hsl) {
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

        function createBoxes() {
            for (var i = 1; i <= BOX_COUNT; i++) {
                var el = document.getElementById('box' + i);
                // 随机尺寸，宽高在 40..120px
                var size = Math.round(rand(40, 120));
                el.style.width = size + 'px';
                el.style.height = size + 'px';
                el.style.position = 'absolute';

                // 初始随机位置：水平在容器宽的 0..100%，垂直在 0..maxHeight
                var containerW = container.clientWidth || window.innerWidth;
                var maxH = getMaxHeight();
                var x = rand(0, Math.max(1, containerW - size));
                var y = rand(0, Math.max(1, maxH - size));

                // 随机速度：每秒 px/s，水平和垂直速度 -range..range（更随机）
                var speedRange = rand(30, 180); // px per second
                var angle = rand(0, Math.PI * 2);
                var vx = Math.cos(angle) * speedRange;
                var vy = Math.sin(angle) * speedRange;

                // 颜色随机化
                el.style.backgroundColor = hsl2color([randInt(360), 100, 70]);
                el.style.borderRadius = Math.round(rand(0, 30)) + 'px';

                // 保存状态
                boxes.push({ el: el, x: x, y: y, vx: vx, vy: vy, size: size, hidden: false });
                // 立刻设置位置
                el.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
            }
        }

        var lastTs = null;
        function step(ts) {
            if (!lastTs) lastTs = ts;
            var dt = (ts - lastTs) / 1000; // seconds
            lastTs = ts;
            var maxH = getMaxHeight();
            var containerW = container.clientWidth || window.innerWidth;

            boxes.forEach(function (b) {
                b.x += b.vx * dt;
                b.y += b.vy * dt;

                // 边界反弹（水平），使飞行更有戏剧性
                if (b.x < -b.size) b.x = containerW; // wrap-around from left
                if (b.x > containerW) b.x = -b.size; // wrap-around from right

                // 垂直超出限制时隐藏（并停止渲染位置）
                if (b.y < 0 || (b.y + b.size) > maxH) {
                    if (!b.hidden) {
                        b.el.style.visibility = 'hidden';
                        b.hidden = true;
                    }
                } else {
                    if (b.hidden) {
                        b.el.style.visibility = 'visible';
                        b.hidden = false;
                    }
                    b.el.style.transform = 'translate(' + Math.round(b.x) + 'px, ' + Math.round(b.y) + 'px)';
                }
            });
            window.requestAnimationFrame(step);
        }

        function onResize() {
            // 当容器大小变化时，确保盒子 x,y 不会永久不可见
            boxes.forEach(function (b) {
                var containerW = container.clientWidth || window.innerWidth;
                if (b.x > containerW) b.x = containerW / 2;
            });
        }

        // 初始化
        document.addEventListener('DOMContentLoaded', function (){
            applyContainerStyle();
            createBoxes();
            window.addEventListener('resize', onResize);
            window.requestAnimationFrame(step);
        });
    })();
    </script>

</body>
</html>


