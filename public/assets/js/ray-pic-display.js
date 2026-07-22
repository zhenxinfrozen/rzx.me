// Minimal dependency-free preview: when a thumbnail is clicked, show the full image
// in #ray-pic-final (centered). Does not prevent overlay lightbox.
(function () {
  'use strict';

  function createImage(src, alt) {
    var img = document.createElement('img');
    img.src = src;
    if (alt) img.alt = alt;
    img.style.maxWidth = '100%';
    img.style.maxHeight = '90vh';
    img.style.display = 'block';
    img.style.margin = '0 auto';
    img.loading = 'eager';
    return img;
  }

  function showInDisplayArea(href, alt) {
    var finalBox = document.getElementById('ray-pic-final');
    if (!finalBox) return;
    // clear existing
    finalBox.innerHTML = '';
    // show spinner (loader)
    var loader = document.createElement('div');
    loader.className = 'ray-pic-loader';
    finalBox.appendChild(loader);

    // preload using Image object then insert with a fade-in class
    var tmp = new Image();
    tmp.onload = function () {
      // create the image element for DOM and add animation class
      var dom = createImage(href, alt || '');
      dom.className = 'ray-pic-img';
      // replace loader with image
      finalBox.innerHTML = '';
      finalBox.appendChild(dom);
      // force reflow then add show class to trigger transition
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
    // delegate clicks on anchors inside the thumb nav
    container.addEventListener('click', function (e) {
      var el = e.target;
      // find the nearest anchor
      while (el && el !== container && el.tagName !== 'A') el = el.parentNode;
      if (!el || el === container) return;
      var href = el.getAttribute('href');
      if (!href) return;
      // Prevent default navigation so page doesn't jump
      e.preventDefault();
      e.stopPropagation();
      // show in display area
      showInDisplayArea(href, el.getAttribute('title') || el.querySelector('img')?.alt || '');
    }, false);

    // Do NOT auto-load any thumbnail on page open.
    // The display area should show the instruction until the user clicks a thumbnail.
  }

  // init on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
