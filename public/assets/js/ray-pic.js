/*
 ray-pic.js
 -------------
 作用说明（中文注释）:
 - 本文件为图片预览的轻量脚本（无外部依赖），用于把缩略图的点击事件委托至
   缩略图容器（`#ray-pic-thumbs`），并把大图异步预加载后在展示区 `#ray-pic-final`
   中显示。
 - 功能要点：
   1. 事件委托：对缩略图容器的 <a> 链接进行 click 拦截，避免页面跳转。
   2. 预加载：使用 Image 对象先行加载目标图片，避免直接插入引起的布局闪烁。
   3. Loader：加载期间显示圆形 spinner；加载成功后淡入图片；加载失败显示错误信息。
   4. 无自动打开：页面加载时不自动展示任何图片，等待用户点击。

 使用说明：
 - HTML: 保持 <nav id="ray-pic-thumbs"> 内为 <ul class="ray-pic-list"><li><a href="..."><img ...></a></li> ...
 - CSS: 图片插入时会被赋予类名 `ray-pic-img`（可配合 `.ray-pic-img.show` 做入场动画）
 - 若要修改入场动画，请编辑 /assets/css/pictures.css 中对应的 .ray-pic-img 规则。

 兼容性与性能建议：
 - 使用 transform/opacity 动画以获得 GPU 加速；避免对大图执行 expensive 的 filter 动画
 - 在移动端如需更节省流量，可改为先加载较小尺寸图，再在需要时替换为高清图。
*/

(function () {
  'use strict';

  // 创建要插入 DOM 的 <img> 元素并统一属性
  function createImageElement(src, alt) {
    var img = document.createElement('img');
    img.src = src;
    if (alt) img.alt = alt;
    img.className = 'ray-pic-img';
    img.loading = 'eager';
    img.style.maxWidth = '100%';
    img.style.maxHeight = '90vh';
    img.style.display = 'block';
    img.style.margin = '0 auto';
    return img;
  }

  // 在展示区显示（带 spinner、预加载、错误处理）
  function showInDisplayArea(href, alt) {
    var finalBox = document.getElementById('ray-pic-final');
    if (!finalBox) return;

    // 清空并显示 loader
    finalBox.innerHTML = '';
    var loader = document.createElement('div');
    loader.className = 'ray-pic-loader';
    finalBox.appendChild(loader);

    // 预加载
    var tmp = new Image();
    tmp.onload = function () {
      var dom = createImageElement(href, alt || '');
      // 用 DOM 元素替换 loader
      finalBox.innerHTML = '';
      finalBox.appendChild(dom);
      // 触发一次重绘后添加 show 类以启动 CSS 过渡（如果存在）
      // eslint-disable-next-line no-unused-expressions
      dom.offsetWidth;
      dom.classList.add('show');
    };
    tmp.onerror = function () {
      finalBox.innerHTML = '<p class="ray-pic-error">无法加载图片。</p>';
    };
    tmp.src = href;
  }

  function init() {
    var container = document.getElementById('ray-pic-thumbs');
    if (!container) return;

    container.addEventListener('click', function (e) {
      var el = e.target;
      while (el && el !== container && el.tagName !== 'A') el = el.parentNode;
      if (!el || el === container) return;
      var href = el.getAttribute('href');
      if (!href) return;
      e.preventDefault();
      e.stopPropagation();
      showInDisplayArea(href, el.getAttribute('title') || (el.querySelector('img') && el.querySelector('img').alt) || '');
    }, false);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
