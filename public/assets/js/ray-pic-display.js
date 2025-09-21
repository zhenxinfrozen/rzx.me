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
    // show a simple loader box
    var loader = document.createElement('div');
    loader.className = 'ray-pic-loader';
    loader.textContent = 'Loading...';
    finalBox.appendChild(loader);

    var img = createImage(href, alt || '');
    // when image loads, replace loader
    img.onload = function () {
      finalBox.innerHTML = '';
      finalBox.appendChild(img);
    };
    img.onerror = function () {
      finalBox.innerHTML = '<p class="ray-pic-error">无法加载图片。</p>';
    };
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
