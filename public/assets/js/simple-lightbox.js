// Minimal, dependency-free lightbox for the gallery
// Features: group by rel attribute (e.g. rel="prettyPhoto[gallery1]"), prev/next, keyboard, caption
(function(){
  'use strict';

  function $(sel, ctx){ return (ctx||document).querySelector(sel); }
  function $all(sel, ctx){ return Array.prototype.slice.call((ctx||document).querySelectorAll(sel)); }

  var Lightbox = function(){
    this.build();
    this.bind();
    this.current = -1;
    this.items = [];
  };

  Lightbox.prototype.build = function(){
    var html = document.createElement('div');
    html.className = 'slb-overlay';
    html.innerHTML = '\n      <div class="slb-backdrop"></div>\n      <div class="slb-pane" role="dialog" aria-modal="true">\n        <button class="slb-close" aria-label="关闭">×</button>\n        <div class="slb-inner">\n          <button class="slb-prev" aria-label="上一张">‹</button>\n          <div class="slb-content">\n            <img class="slb-img" src="" alt="" />\n            <div class="slb-caption"></div>\n          </div>\n          <button class="slb-next" aria-label="下一张">›</button>\n        </div>\n      </div>';
    document.body.appendChild(html);
    this.el = html;
    this.backdrop = $('.slb-backdrop', html);
    this.pane = $('.slb-pane', html);
    this.img = $('.slb-img', html);
    this.caption = $('.slb-caption', html);
    this.btnClose = $('.slb-close', html);
    this.btnPrev = $('.slb-prev', html);
    this.btnNext = $('.slb-next', html);
  };

  Lightbox.prototype.bind = function(){
    var self = this;
    this.btnClose.addEventListener('click', function(){ self.close(); });
    this.backdrop.addEventListener('click', function(){ self.close(); });
    this.btnPrev.addEventListener('click', function(){ self.prev(); });
    this.btnNext.addEventListener('click', function(){ self.next(); });
    document.addEventListener('keydown', function(e){
      if (self.el.classList.contains('slb-open')){
        if (e.key === 'Escape') self.close();
        if (e.key === 'ArrowLeft') self.prev();
        if (e.key === 'ArrowRight') self.next();
      }
    });
  };

  Lightbox.prototype.open = function(items, index){
    this.items = items;
    this.current = index;
    this.showCurrent();
    this.el.classList.add('slb-open');
    // focus for accessibility
    this.pane.setAttribute('tabindex', '-1');
    this.pane.focus();
  };

  Lightbox.prototype.close = function(){
    this.el.classList.remove('slb-open');
    this.img.src = '';
    this.caption.textContent = '';
  };

  Lightbox.prototype.showCurrent = function(){
    var item = this.items[this.current];
    if (!item) return;
    this.img.src = item.href;
    this.img.alt = item.title || '';
    // caption could be html; insert as text for safety
    this.caption.textContent = item.title || '';
    // update prev/next disabled
    if (this.items.length <= 1){
      this.btnPrev.style.display = 'none';
      this.btnNext.style.display = 'none';
    } else {
      this.btnPrev.style.display = '';
      this.btnNext.style.display = '';
    }
  };

  Lightbox.prototype.next = function(){
    if (this.items.length <= 1) return;
    this.current = (this.current + 1) % this.items.length;
    this.showCurrent();
  };

  Lightbox.prototype.prev = function(){
    if (this.items.length <= 1) return;
    this.current = (this.current - 1 + this.items.length) % this.items.length;
    this.showCurrent();
  };

  // init: find gallery links and attach handlers
  function initLightbox(){
    var lb = new Lightbox();
    // group anchors by rel attribute (e.g. prettyPhoto[gallery1]) or data-group
    var anchors = $all('.gallery a');
    var groups = {};
    anchors.forEach(function(a, idx){
      var rel = a.getAttribute('rel') || a.getAttribute('data-group') || '';
      // normalize rel like prettyPhoto[gallery1] -> gallery1
      var m = rel.match(/\[(.*?)\]/);
      var key = m ? m[1] : (rel || 'default');
      if (!groups[key]) groups[key] = [];
      groups[key].push({href: a.href, title: a.getAttribute('title') || a.getAttribute('data-title') || '', el: a});

      a.addEventListener('click', function(e){
        e.preventDefault();
        var items = groups[key];
        var index = items.findIndex(function(it){ return it.el === a; });
        lb.open(items, index);
      });
    });
  }

  // auto-init on DOMContentLoaded
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initLightbox);
  } else {
    initLightbox();
  }

})();
