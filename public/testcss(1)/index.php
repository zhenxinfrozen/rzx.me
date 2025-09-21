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
    // 动画改进：多组冲击效果（更多方块、更快、更有冲击感）
    (function (){
        var boxes = [];
        var container = document.getElementById('wutai');

        function getMaxHeight() { return Math.max(0, Math.round(window.innerHeight * 0.40)); }
        function applyContainerStyle() { container.style.position = 'relative'; container.style.height = '40vh'; container.style.overflow = 'hidden'; }
        function rand(min, max) { return Math.random() * (max - min) + min; }
        function randInt(max) { return Math.floor(Math.random() * max); }

        function hsl2color(hsl) {
            if (hsl[0] > 360 || hsl[0] < 0 || hsl[1] > 100 || hsl[1] < 0 || hsl[2] > 100 || hsl[2] < 0) return "#000000";
            var rgb = [0,0,0];
            if (hsl[0] <= 60) { rgb[0]=255; rgb[1]=Math.floor(255/60*hsl[0]); }
            else if (hsl[0] <= 120) { rgb[0]=Math.floor(255-(255/60)*(hsl[0]-60)); rgb[1]=255; }
            else if (hsl[0] <= 180) { rgb[1]=255; rgb[2]=Math.floor((255/60)*(hsl[0]-120)); }
            else if (hsl[0] <= 240) { rgb[1]=Math.floor(255-(255/60)*(hsl[0]-180)); rgb[2]=255; }
            else if (hsl[0] <= 300) { rgb[0]=Math.floor((255/60)*(hsl[0]-240)); rgb[2]=255; }
            else { rgb[0]=255; rgb[2]=Math.floor(255-(255/60)*(hsl[0]-300)); }
            var sat = Math.abs((hsl[1]-100)/100);
            rgb[0]=Math.floor(rgb[0]-(rgb[0]-128)*sat);
            rgb[1]=Math.floor(rgb[1]-(rgb[1]-128)*sat);
            rgb[2]=Math.floor(rgb[2]-(rgb[2]-128)*sat);
            var lum=(hsl[2]-50)/50;
            if (lum>0){ rgb[0]=Math.floor(rgb[0]+(255-rgb[0])*lum); rgb[1]=Math.floor(rgb[1]+(255-rgb[1])*lum); rgb[2]=Math.floor(rgb[2]+(255-rgb[2])*lum); }
            else if (lum<0){ rgb[0]=Math.floor(rgb[0]+rgb[0]*lum); rgb[1]=Math.floor(rgb[1]+rgb[1]*lum); rgb[2]=Math.floor(rgb[2]+rgb[2]*lum); }
            return "#" + (0x1000000 + rgb[0]*0x010000 + rgb[1]*0x000100 + rgb[2]).toString(16).substring(1);
        }

        function createBoxElement(size){
            var el = document.createElement('div');
            el.className = 'box';
            el.style.width = size + 'px';
            el.style.height = size + 'px';
            el.style.position = 'absolute';
            el.style.pointerEvents = 'none';
            container.appendChild(el);
            return el;
        }

        function spawnBox(opts){
            var size = Math.round(rand(opts.sizeMin || 30, opts.sizeMax || 120));
            var el = createBoxElement(size);
            var containerW = container.clientWidth || window.innerWidth;
            var maxH = getMaxHeight();

            var spawnEdge = opts.spawnEdge || 'top';
            var x, y, angle, speed, vx, vy;

            // spawn off-screen depending on edge and aim towards inside
            if (spawnEdge === 'top'){
                x = rand(0, Math.max(1, containerW - size));
                y = -size - rand(0, 200);
                angle = rand(Math.PI*0.25, Math.PI*0.75); // mostly downward
            } else if (spawnEdge === 'bottom'){
                x = rand(0, Math.max(1, containerW - size));
                y = maxH + size + rand(0,200);
                angle = rand(-Math.PI*0.75, -Math.PI*0.25); // mostly upward
            } else if (spawnEdge === 'left'){
                x = -size - rand(0,200);
                y = rand(0, Math.max(1, maxH - size));
                angle = rand(-Math.PI*0.25, Math.PI*0.25); // mostly rightward
            } else { // right
                x = (containerW + size + rand(0,200));
                y = rand(0, Math.max(1, maxH - size));
                angle = rand(Math.PI*0.75, Math.PI*1.25); // mostly leftward
            }

            speed = rand(opts.minSpeed || 200, opts.maxSpeed || 600);
            vx = Math.cos(angle) * speed;
            vy = Math.sin(angle) * speed;

            el.style.backgroundColor = hsl2color([randInt(360), 100, 60]);
            el.style.borderRadius = Math.round(rand(0, 40)) + 'px';

            boxes.push({ el: el, x: x, y: y, vx: vx, vy: vy, size: size, hidden: false });
            el.style.transform = 'translate(' + Math.round(x) + 'px, ' + Math.round(y) + 'px)';
        }

        function spawnGroup(group){
            for (var i=0;i<group.count;i++) spawnBox(group);
        }

        // Example groups: more boxes, faster speeds, different edges => 冲击感
        var GROUPS = [
            { count: 10, spawnEdge: 'top', minSpeed: 300, maxSpeed: 700, sizeMin: 30, sizeMax: 80, delay: 0 },
            { count: 8, spawnEdge: 'left', minSpeed: 350, maxSpeed: 800, sizeMin: 30, sizeMax: 100, delay: 400 },
            { count: 12, spawnEdge: 'right', minSpeed: 300, maxSpeed: 700, sizeMin: 40, sizeMax: 120, delay: 900 }
        ];

        var lastTs = null;
        function step(ts){
            if (!lastTs) lastTs = ts;
            var dt = (ts - lastTs) / 1000; lastTs = ts;
            var maxH = getMaxHeight();
            var containerW = container.clientWidth || window.innerWidth;

            for (var i=boxes.length-1;i>=0;i--){
                var b = boxes[i];
                b.x += b.vx * dt;
                b.y += b.vy * dt;

                // horizontal wrap for drama
                if (b.x < -b.size*2) b.x = containerW + b.size;
                if (b.x > containerW + b.size*2) b.x = -b.size;

                // hide if vertical out of allowed area
                if (b.y < 0 || (b.y + b.size) > maxH){
                    if (!b.hidden){ b.el.style.visibility = 'hidden'; b.hidden = true; }
                } else {
                    if (b.hidden){ b.el.style.visibility = 'visible'; b.hidden = false; }
                    b.el.style.transform = 'translate(' + Math.round(b.x) + 'px, ' + Math.round(b.y) + 'px)';
                }

                // optional: cleanup boxes that are far outside both horizontally and vertically to avoid DOM growth
                if (Math.abs(b.x) > containerW*3 || Math.abs(b.y) > window.innerHeight*3){
                    // remove element and compact array
                    try { b.el.parentNode.removeChild(b.el); } catch(e){}
                    boxes.splice(i,1);
                }
            }

            window.requestAnimationFrame(step);
        }

        // periodic bursts (每隔一段时间再制造冲击)
        function scheduleBursts(){
            GROUPS.forEach(function(g){ setTimeout(function(){ spawnGroup(g); }, g.delay || 0); });
            // 每 4 秒重复一次冲击
            setInterval(function(){ GROUPS.forEach(function(g){ spawnGroup(g); }); }, 4000);
        }

        function onResize(){ /* nothing special for now */ }

        document.addEventListener('DOMContentLoaded', function(){
            applyContainerStyle();
            // initial small fallback: create a few boxes so page isn't empty on very small screens
            spawnGroup({ count: 5, spawnEdge: 'top', minSpeed: 150, maxSpeed: 400, sizeMin:40, sizeMax:90, delay:0 });
            scheduleBursts();
            window.addEventListener('resize', onResize);
            window.requestAnimationFrame(step);
        });
    })();
    </script>

</body>
</html>


