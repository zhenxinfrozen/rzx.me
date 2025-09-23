<?php
// 图片画廊阅读器视图 - Sketch Dreams Gallery
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<style>
  /* 画廊专用样式 */
  html,body{height:100%;margin:0;background:#ffffff;color:#222;overflow-x:hidden}
  .gallery-wrap{height:95vh;display:flex;flex-direction:column;background:#fff;position:relative;overflow:hidden}
  .swiper{flex:1;min-height:0;width:100%;overflow:hidden}
  .main-swiper, .main-swiper .swiper-wrapper, .main-swiper .swiper-slide{height:calc(100vh - 120px)}
  .main-swiper .swiper-wrapper{align-items:center}
  .main-swiper .swiper-slide{width:100%;flex:0 0 100%;box-sizing:border-box}
  .swiper-slide{display:flex;align-items:center;justify-content:center;background:#fff}
  .swiper-slide img{display:block;max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain}
  .controls{display:none}
  .thumbs{height:120px;background:#ffffff;display:flex;align-items:center;padding:8px;overflow-x:auto;overflow-y:hidden;justify-content:center;gap:8px;flex-wrap:nowrap}
  .thumbs img{height:88px;max-width:100px;cursor:pointer;opacity:0.8;flex-shrink:0;object-fit:cover}
  .thumbs img.active{outline:3px solid #8cb5d5;opacity:1}
  /* 导航箭头样式 */
  .swiper-button-next, .swiper-button-prev{ color: #8a8a8a; }
</style>

<div class="gallery-wrap">
  <div class="controls">
    <button id="playPause">Pause</button>
  </div>


  <div class="swiper main-swiper">
    <div class="swiper-wrapper" id="slides">
      <!-- slides injected by JS -->
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>

  <div class="thumbs" id="thumbs"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
  async function loadGallery(){
    try{
function dlog(msg){ try{ console.log(msg); }catch(e){} }
      dlog('loadGallery start');
      const slidesEl = document.getElementById('slides');
      const thumbsEl = document.getElementById('thumbs');
      const slideItems = [];

      // use the known gallery size (14) instead of HEAD probing to avoid 404 noise
      const discovered = [];
      const MAX = 14; // known number of sketches
for(let i=1;i<=MAX;i++){ discovered.push(`/assets/images/sketch-dreams-${String(i).padStart(2,'0')}.jpg`); }
      dlog(`discovered ${discovered.length} images`);

      // Preload first N images (small number) to avoid lazy/clone race conditions
      async function preload(src, timeout = 1500){
        return new Promise(resolve=>{
          try{
            const img = new Image();
            let done = false;
            const t = setTimeout(()=>{ if(!done){ done=true; resolve(false); } }, timeout);
            img.onload = ()=>{ if(!done){ done=true; clearTimeout(t); resolve(true); } };
            img.onerror = ()=>{ if(!done){ done=true; clearTimeout(t); resolve(false); } };
            img.src = src;
          }catch(e){ resolve(false); }
        });
      }
      try{
        const PRIMED = 7; // number of slides at each end to prime (increased for robustness)
        const toPre = Math.min(PRIMED, discovered.length);
        const preList = discovered.slice(0,toPre);
        // also preload last N images to avoid tail race conditions
        const lastPre = discovered.slice(Math.max(0, discovered.length-PRIMED));
        dlog('preloading first '+preList.length+' and last '+lastPre.length+' images');
        const pres1 = await Promise.all(preList.map(p=>preload(p,3000)));
        const pres2 = await Promise.all(lastPre.map(p=>preload(p,3000)));
        dlog('preload results start: '+pres1.map(v=>v? 'ok':'fail').join(','));
        dlog('preload results end: '+pres2.map(v=>v? 'ok':'fail').join(','));
      }catch(e){ dlog('preload error '+e); }

const PRIMED = 7;
discovered.forEach((path,i)=>{
        // slide
        const slide = document.createElement('div');
        slide.className='swiper-slide';
        const imageEl = document.createElement('img');
        imageEl.className = 'swiper-lazy';
        imageEl.setAttribute('data-src', path);
        // direct-src for first/last PRIMED slides to avoid head/tail race with clones
        if(i < PRIMED || i >= (discovered.length - PRIMED)){
          imageEl.src = path; dlog('direct src set for primed slide '+i);
        }
        imageEl.alt = `Sketch ${i+1}`;
        imageEl.style.opacity = '0';
        imageEl.style.transition = 'opacity 240ms ease';
        // on load apply longest-edge sizing and reveal
        imageEl.addEventListener('load', ()=>{
          try{
            const naturalW = imageEl.naturalWidth || 0;
            const naturalH = imageEl.naturalHeight || 0;
            const srcMatch = imageEl.getAttribute('data-src') || imageEl.currentSrc || imageEl.src;
            dlog(`img loaded: ${srcMatch} ${naturalW}x${naturalH}`);
            const peers = document.querySelectorAll(`img[data-src="${srcMatch}"]`);
            peers.forEach(img=>{
              if(naturalW >= naturalH){ img.style.width='100%'; img.style.height='auto'; img.style.maxWidth='none'; img.style.maxHeight='100%'; }
              else { img.style.height='100%'; img.style.width='auto'; img.style.maxHeight='none'; img.style.maxWidth='100%'; }
              img.style.opacity='1';
            });
            try{ if(window.mainSwiper){ mainSwiper.update(); mainSwiper.updateSize(); } }catch(e){}
          }catch(e){}
        });
        imageEl.addEventListener('error', ()=>{ imageEl.src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='; imageEl.style.opacity='1'; });
        imageEl.addEventListener('error', ()=>{ dlog('img ERROR: '+(imageEl.getAttribute('data-src')||imageEl.src)); });
        // preloader element for Swiper lazy
        const pre = document.createElement('div'); pre.className='swiper-lazy-preloader';
        slide.appendChild(imageEl);
        slide.appendChild(pre);
        slidesEl.appendChild(slide);

        // thumb (use generated thumbs if available)
        const timg = document.createElement('img');
        const thumbPath = path.replace('/assets/images/','/assets/images/thumbs/');
        timg.src = thumbPath;
        timg.alt = imageEl.alt;
        timg.loading = 'lazy';
        timg.style.height = '88px';
        timg.style.objectFit = 'cover';
        timg.addEventListener('click', ()=>{ if(window.mainSwiper && typeof mainSwiper.slideToLoop === 'function'){ mainSwiper.slideToLoop(i); } else if(window.mainSwiper){ mainSwiper.slideTo(i); } });
        thumbsEl.appendChild(timg);
        dlog(`created slide/thumb ${i+1} -> ${path}`);
        slideItems.push({imageEl,timg});
      });

      dlog('initializing Swiper');
      // initialize Swiper with native lazy loading and loop support
      window.mainSwiper = new Swiper('.main-swiper',{
        loop:true,
        preloadImages:false,
        observer: true,
        observeParents: true,
        lazy:{ loadPrevNext: true, loadPrevNextAmount: 3 },
        autoplay:{delay:5000,disableOnInteraction:false},
        pagination:{el:'.swiper-pagination',clickable:true},
        navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},
      });

      dlog('Swiper initialized');

      // clone-aware loader: force-load current and nearby slides (assign src on all matching imgs incl. clones)
      function ensureVisibleSlidesLoaded(swiper, amount){
        try{
          const ri = swiper.realIndex || 0;
          dlog('ensureVisibleSlidesLoaded realIndex='+ri+' amount='+amount);
          for(let off=-amount; off<=amount; off++){
            const idx = (ri + off + slideItems.length) % slideItems.length;
            const path = slideItems[idx]?.imageEl?.getAttribute('data-src');
            if(!path) continue;
            // find all imgs that reference this data-src (includes clones)
            // match both data-src and src (some clones may have src already)
            const peers = document.querySelectorAll(`img[data-src="${path}"], img[src="${path}"]`);
            peers.forEach(img=>{
              if(!img.src || img.naturalWidth===0){ img.src = path; dlog('force src -> '+path); }
              // if it already has src but style not applied, trigger load handler via dispatch
              if(img.src && img.naturalWidth>0){ try{ img.dispatchEvent(new Event('load')); }catch(e){} }
            });
          }
        }catch(e){ dlog('ensureVisibleSlidesLoaded error '+e); }
      }

      mainSwiper.on('init', function(){
        try{ dlog('on init realIndex='+this.realIndex); try{ this.slideToLoop(0,0); }catch(e){} ensureVisibleSlidesLoaded(this,2); }
        catch(e){ dlog('init handler error '+e); }
      });
      // also ensure for slide changes (start) we proactively load nearby slides
      mainSwiper.on('slideChangeTransitionStart', function(){ try{ ensureVisibleSlidesLoaded(this,2); }catch(e){} });

      // Watch for clones being added (some Swiper builds modify DOM after init); when clones appear, force-assign src for relevant imgs
      try{
        const wrapper = document.querySelector('.main-swiper .swiper-wrapper');
        if(wrapper && window.MutationObserver){
          const mo = new MutationObserver((mutList)=>{
            mutList.forEach(m=>{
              if(m.addedNodes && m.addedNodes.length){
                m.addedNodes.forEach(n=>{
                  if(n.nodeType===1 && n.classList.contains('swiper-slide')){
                    const imgs = n.querySelectorAll('img[data-src]');
                    imgs.forEach(img=>{ if(!img.src || img.naturalWidth===0){ img.src = img.getAttribute('data-src'); dlog('mutation forced src '+(img.getAttribute('data-src')||img.src)); } });
                  }
                });
              }
            });
          });
          mo.observe(wrapper, { childList:true, subtree:true });
          dlog('mutation observer attached');
        }
      }catch(e){ dlog('mutation observer error '+e); }

      // short retry loop: for the first/last primed logical slides, if still blank within 3s, force src repeatedly
      try{
            const retryStart = Date.now();
        const retryInterval = setInterval(()=>{
          try{
            const len = slideItems.length;
            const PRIMED_CHECK = 7;
            const checkSet = new Set();
            for(let k=0;k<PRIMED_CHECK && k<len;k++){ checkSet.add(k); checkSet.add(len-1-k); }
            let allOk = true;
            Array.from(checkSet).forEach(i=>{
              const slide = slideItems[i];
              if(!slide) return;
              const img = slide.imageEl;
              if(img && (img.naturalWidth===0 || img.complete===false)){
                allOk = false;
                const ds = img.getAttribute('data-src')||img.src;
                img.src = ds; dlog('retry forced src for index '+i+' -> '+ds);
              }
            });
            if(allOk || (Date.now()-retryStart)>5000){ clearInterval(retryInterval); dlog('retry loop ended'); }
          }catch(e){ clearInterval(retryInterval); dlog('retry loop error '+e); }
        }, 200);
      }catch(e){ dlog('retry loop setup error '+e); }

      // make sure initial thumb is highlighted
      if(slideItems.length>0){ slideItems[0].timg.classList.add('active'); }

      // highlight thumb and center on slide change
      mainSwiper.on('slideChange', function(){
        const idx = this.realIndex;
        slideItems.forEach((s,si)=> s.timg.classList.toggle('active', si===idx));
        try{ const active = slideItems[idx]?.timg; if(active){ const parent = thumbsEl; const targetLeft = active.offsetLeft - (parent.clientWidth - active.clientWidth)/2; parent.scrollTo({left:targetLeft,behavior:'smooth'}); } }catch(e){}
      });

// ensure initial lazy load for visible slides (call immediately after init)
try{ mainSwiper.lazy.load(); dlog('called mainSwiper.lazy.load()'); }catch(e){}
      // also attempt to preload neighbors at transition start
      mainSwiper.on('slideNextTransitionStart', function(){ try{ mainSwiper.lazy.loadInSlide((this.realIndex+1)%slideItems.length); }catch(e){} });
      mainSwiper.on('slidePrevTransitionStart', function(){ try{ mainSwiper.lazy.loadInSlide((this.realIndex-1+slideItems.length)%slideItems.length); }catch(e){} });

setTimeout(()=>{ try{ mainSwiper.update(); mainSwiper.updateSize(); mainSwiper.lazy.load(); dlog('post-update lazy.load() + update()'); }catch(e){} },600);

      const btn = document.getElementById('playPause'); btn.addEventListener('click', ()=>{ if(mainSwiper.autoplay.running){ mainSwiper.autoplay.stop(); btn.textContent='Play'; } else{ mainSwiper.autoplay.start(); btn.textContent='Pause'; } });



    }catch(e){ console.error(e); }
  }
  loadGallery();
</script>