<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CSS 动画演示</title>

  <style>
  /* inlined & cleaned from style/csslab.css */
  .div { width:100px; height:100px; float:left; }
  #header{ font-size:40px; color:#fff; font-family:"Courier New", Courier, monospace; text-align:center; }

  #wutai{
    height:40vh; /* 限制为视口 40vh */
    width:100vw; /* 视口宽度 */
    margin:0 auto;
    padding:20px;
    perspective:900px;
    perspective-origin:50% 50%;
    -webkit-perspective:900px;
    -webkit-perspective-origin:50% 50%;
    position:relative;
    /* overflow:hidden; */
  }

  .box{
    width:100px;
    height:100px;
    margin:0;
    float:left;
    border:1px #CCC solid;
    background: red;
    padding:0;
    position:relative;
    box-sizing:border-box;
    opacity:0.92; /* 稍微降低透明度但不高 */
    will-change: transform, opacity;
    /* start paused; JS will resume after a short delay */
    animation-play-state: paused;
    -webkit-animation-play-state: paused;
  }

  @keyframes myfirst {
    0%   { background:red; left:-120px; top:0px; }
    25%  { background:yellow; left:500px; top:0px; }
    35%  { border-radius:25px; }
    50%  { background:blue; left:600px; top:200px; }
    75%  { background:green; left:0px; top:200px; }
    80%  { transform: rotateX(132deg); }
    90%  { width:300px; }
    100% { transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px); }
  }
  @-webkit-keyframes myfirst { /* Safari/Chrome legacy */
    0%   { background:red; left:-120px; top:0px; }
    25%  { background:yellow; left:500px; top:0px; }
    35%  { -webkit-border-radius:25px; }
    50%  { background:blue; left:600px; top:200px; }
    75%  { background:green; left:0px; top:200px; }
    80%  { -webkit-transform: rotateX(132deg); }
    90%  { width:300px; }
    100% { -webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px); }
  }

  @keyframes myfirst2 {
    0%   { left:-220px; top:-123px; }
    25%  { transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px); }
    35%  { border-radius:25px; }
    50%  { left:600px; top:200px; }
    75%  { transform: rotateX(132deg); }
    80%  { left:0px; top:200px; }
    90%  { height:30px; }
    100% { left:500px; top:0px; }
  }
  @-webkit-keyframes myfirst2 { /* Safari/Chrome */
    0%   { left:-220px; top:-123px; }
    25%  { -webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(2352px); }
    35%  { -webkit-border-radius:25px; }
    50%  { left:600px; top:200px; }
    75%  { -webkit-transform: rotateX(132deg); }
    80%  { left:0px; top:200px; }
    90%  { height:30px; }
    100% { left:500px; top:0px; }
  }

  @keyframes myfirst4 {
    0%   { left:-140px; top:-180px; }
    25%  { transform: rotateY(23deg) rotateZ(180deg) translateY(552px) translateZ(-2352px); }
    35%  { border-radius:25px; }
    50%  { left:600px; top:60px; }
    75%  { transform: rotateX(19deg); }
    80%  { left:0px; top:200px; }
    90%  { transform: rotateY(340deg) rotateZ(10deg) translateY(212px) translateZ(-252px); }
    100% { left:500px; top:0px; }
  }
  @-webkit-keyframes myfirst4 { /* Safari/Chrome */
    0%   { left:-140px; top:-180px; }
    25%  { -webkit-transform: rotateY(23deg) rotateZ(180deg) translateY(552px) translateZ(-2352px); }
    35%  { -webkit-border-radius:25px; }
    50%  { left:600px; top:60px; }
    75%  { -webkit-transform: rotateX(19deg); }
    80%  { left:0px; top:200px; }
    90%  { -webkit-transform: rotateY(340deg) rotateZ(10deg) translateY(212px) translateZ(-252px); }
    100% { left:500px; top:0px; }
  }

  @keyframes myfirst5 {
    0%   { left:-100px; top:-100px; }
    25%  { transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(-2352px); }
    35%  { border-radius:25px; }
    50%  { left:600px; top:200px; }
    75%  { transform: rotateX(132deg); }
    80%  { left:0px; top:200px; }
    90%  { height:200px; }
    100% { left:500px; top:0px; }
  }
  @-webkit-keyframes myfirst5 { /* Safari/Chrome */
    0%   { left:-100px; top:-100px; }
    25%  { -webkit-transform: rotateY(23deg) rotateZ(34deg) translateY(552px) translateZ(-2352px); }
    35%  { -webkit-border-radius:25px; }
    50%  { left:600px; top:200px; }
    75%  { -webkit-transform: rotateX(132deg); }
    80%  { left:0px; top:200px; }
    90%  { height:200px; }
    100% { left:500px; top:0px; }
  }

  /* per-box animation setup (standard + webkit), start paused */
  #box1{ z-index:100; opacity:0.92; transform: rotateY(0deg); -webkit-transform: rotateY(0deg); animation-name: myfirst4; animation-timing-function: linear; animation-duration: 5s; animation-delay: 0s; animation-iteration-count: infinite; animation-direction: alternate; animation-play-state: paused; -webkit-animation-name: myfirst4; -webkit-animation-timing-function: linear; -webkit-animation-duration: 5s; -webkit-animation-delay:0s; -webkit-animation-iteration-count: infinite; -webkit-animation-direction: alternate; -webkit-animation-play-state: paused; }
  #box2{ z-index:100; opacity:0.92; transform: rotateX(0eg); -webkit-transform: rotateX(0deg); animation-name: myfirst2; animation-duration:5s; animation-timing-function:linear; animation-delay:0s; animation-iteration-count: infinite; animation-direction: alternate; animation-play-state: paused; -webkit-animation-name: myfirst2; -webkit-animation-duration:5s; -webkit-animation-timing-function:linear; -webkit-animation-delay:0s; -webkit-animation-iteration-count: infinite; -webkit-animation-direction: alternate; -webkit-animation-play-state: paused; }
  #box3{ z-index:100; opacity:0.92; transform: rotateY(0deg); -webkit-transform: rotateY(0deg); animation-name: myfirst; animation-duration:5s; animation-timing-function:linear; animation-delay:2s; animation-iteration-count: infinite; animation-direction: alternate; animation-play-state: paused; -webkit-animation-name: myfirst; -webkit-animation-duration:5s; -webkit-animation-timing-function:linear; -webkit-animation-delay:2s; -webkit-animation-iteration-count: infinite; -webkit-animation-direction: alternate; -webkit-animation-play-state: paused; }
  #box4{ z-index:100; opacity:0.92; transform: rotateY(0deg) rotateZ(0deg) translateY(0px) translateZ(0px); -webkit-transform: rotateY(0eg) rotateZ(0deg) translateY(0px) translateZ(00px); animation-name: myfirst5; animation-duration:5s; animation-timing-function:linear; animation-delay:2s; animation-iteration-count: infinite; animation-direction: alternate; animation-play-state: paused; -webkit-animation-name: myfirst5; -webkit-animation-duration:5s; -webkit-animation-timing-function:linear; -webkit-animation-delay:2s; -webkit-animation-iteration-count: infinite; -webkit-animation-direction: alternate; -webkit-animation-play-state: paused; }
  #box5{ z-index:100; opacity:0.92; transform: rotateY(0deg); -webkit-transform: rotateY(0deg); }

  #box1, #box2 li{ list-style-type:none; }

  </style>
</head>
<body>
  <main>
    <section id="wutai" aria-label="演示舞台">
      <div id="box1" class="box"></div>
      <div id="box2" class="box"></div>
      <div id="box3" class="box"></div>
      <div id="box4" class="box"></div>
      <div id="box5" class="box"></div>
    </section>
  </main>

  <script>
  // 现代化 JS：DOMContentLoaded + 随机颜色 + 延迟 2s 启动 CSS 动画
  document.addEventListener('DOMContentLoaded', function(){
    var boxes = document.querySelectorAll('.box');
    boxes.forEach(function(el){
      el.style.backgroundColor = hsl2color([radomFuc(360), 100, 70]);
    });

    // 等待 2 秒保持初始状态，再启动动画
    setTimeout(function(){
      boxes.forEach(function(el){
        el.style.animationPlayState = 'running';
        el.style.webkitAnimationPlayState = 'running';
      });
    }, 2000);
  });

  function radomFuc(value){ return Math.floor(value * Math.random()); }

  function hsl2color(hsl){
    if (hsl[0] > 360 || hsl[0] < 0 || hsl[1] > 100 || hsl[1] < 0 || hsl[2] > 100 || hsl[2] < 0) return '#000000';
    var rgb=[0,0,0];
    if (hsl[0] <= 60){ rgb[0]=255; rgb[1]=Math.floor(255/60*hsl[0]); }
    else if (hsl[0] <= 120){ rgb[0]=Math.floor(255-(255/60)*(hsl[0]-60)); rgb[1]=255; }
    else if (hsl[0] <= 180){ rgb[1]=255; rgb[2]=Math.floor((255/60)*(hsl[0]-120)); }
    else if (hsl[0] <= 240){ rgb[1]=Math.floor(255-(255/60)*(hsl[0]-180)); rgb[2]=255; }
    else if (hsl[0] <= 300){ rgb[0]=Math.floor((255/60)*(hsl[0]-240)); rgb[2]=255; }
    else { rgb[0]=255; rgb[2]=Math.floor(255-(255/60)*(hsl[0]-300)); }
    var sat = Math.abs((hsl[1]-100)/100);
    rgb[0]=Math.floor(rgb[0]-(rgb[0]-128)*sat);
    rgb[1]=Math.floor(rgb[1]-(rgb[1]-128)*sat);
    rgb[2]=Math.floor(rgb[2]-(rgb[2]-128)*sat);
    var lum=(hsl[2]-50)/50;
    if (lum>0){ rgb[0]=Math.floor(rgb[0]+(255-rgb[0])*lum); rgb[1]=Math.floor(rgb[1]+(255-rgb[1])*lum); rgb[2]=Math.floor(rgb[2]+(255-rgb[2])*lum); }
    else if (lum<0){ rgb[0]=Math.floor(rgb[0]+rgb[0]*lum); rgb[1]=Math.floor(rgb[1]+rgb[1]*lum); rgb[2]=Math.floor(rgb[2]+rgb[2]*lum); }
    return '#'+(0x1000000+rgb[0]*0x010000+rgb[1]*0x000100+rgb[2]).toString(16).substring(1);
  }
  </script>
</body>
</html>


