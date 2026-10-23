/* RZX 拟物菜单动效 JS —— 原生 JS，无 jQuery，rzx-skeuo 命名空间隔离 */
(function () {
	'use strict';

	function init() {
		var root = document.querySelector('.rzx-skeuo');
		if (!root) {
			return;
		}

	var items = root.querySelectorAll('.rzx-skeuo__item');
	Array.prototype.forEach.call(items, function (item) {
		// 点击不导航（href="#"），仅保留交互动效
		item.addEventListener('click', function (e) {
			e.preventDefault();
		});
	});

	/* ---------- Comic：hover 与 click 一致的极限弹跳（随机上浮 + 随机旋转） ---------- */
	var comicItem = root.querySelector('.rzx-skeuo__item--comic');
	var comicImg  = comicItem ? comicItem.querySelector('.rzx-skeuo__icon img') : null;
	if (comicImg) {
		function doBounce() {
			// 随机上浮 30–50px（40±10）；随机旋转：方向 ±，幅度 6°–14°
			var rise = -(30 + Math.random() * 20);
			comicImg.style.setProperty('--rzx-comic-rise', rise + 'px');
			var dir = Math.random() < 0.5 ? -1 : 1;
			var mag = 6 + Math.random() * 8;
			comicImg.style.setProperty('--rzx-comic-rot', (dir * mag) + 'deg');
			comicImg.classList.remove('rzx-skeuo-bounce');
			void comicImg.offsetWidth; // 强制 reflow 以重置动画
			comicImg.classList.add('rzx-skeuo-bounce');
		}
		comicItem.addEventListener('mouseenter', doBounce);
		comicItem.addEventListener('focusin', doBounce);
		comicItem.addEventListener('click', doBounce);
	}

	/* ---------- Blog：点击随机旋转一圈 + 弹簧收尾 ----------
	   点击态保持到鼠标移出，避免 hover 弹性动画在旋转结束后被重新触发 */
	var blogItem = root.querySelector('.rzx-skeuo__item--blog');
	if (blogItem) {
		blogItem.addEventListener('click', function () {
			var img = blogItem.querySelector('.rzx-skeuo__icon img');
			if (img) {
				// 随机方向：顺时针 +50° 或逆时针 -50°
				var dir = Math.random() < 0.5 ? -1 : 1;
				img.style.setProperty('--rzx-shock-rot', (dir * 50) + 'deg');
			}
			// 重新触发动画（先移除 class 强制 reflow）
			blogItem.classList.remove('rzx-skeuo-clicked');
			void blogItem.offsetWidth;
			blogItem.classList.add('rzx-skeuo-clicked');
		});
		// 重新进入时清掉残留点击态（恢复 hover 弹性）；移出时移除点击态
		blogItem.addEventListener('mouseenter', function () {
			blogItem.classList.remove('rzx-skeuo-clicked');
		});
		blogItem.addEventListener('mouseleave', function () {
			blogItem.classList.remove('rzx-skeuo-clicked');
		});
	}

	/* ---------- Works：横向动感模糊（SVG filter 渐变） ---------- */
	var worksItem = root.querySelector('.rzx-skeuo__item--works');
	var worksImg  = worksItem ? worksItem.querySelector('.rzx-skeuo__icon img') : null;
	if (worksImg) {
		var svgns  = 'http://www.w3.org/2000/svg';
		var svg    = document.createElementNS(svgns, 'svg');
		var defs   = document.createElementNS(svgns, 'defs');
		var filter = document.createElementNS(svgns, 'filter');
		var gblur  = document.createElementNS(svgns, 'feGaussianBlur');

		svg.setAttribute('width', '0');
		svg.setAttribute('height', '0');
		svg.style.position = 'absolute';

		filter.setAttribute('id', 'rzx-skeuo-blur');
		filter.setAttribute('x', '-50%');
		filter.setAttribute('y', '-50%');
		filter.setAttribute('width', '200%');
		filter.setAttribute('height', '200%');

		gblur.setAttribute('in', 'SourceGraphic');
		gblur.setAttribute('stdDeviation', '8 0');

		filter.appendChild(gblur);
		defs.appendChild(filter);
		svg.appendChild(defs);
		document.body.appendChild(svg);

		worksImg.style.filter = 'url(#rzx-skeuo-blur)';
		worksImg.style.willChange = 'filter';

		var raf = null, startTs = null, fromVal = 8, toVal = 0, dur = 200;

		function ease(t) {
			return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
		}

		function step(ts) {
			if (!startTs) { startTs = ts; }
			var elapsed = ts - startTs;
			var p = ease(Math.min(1, elapsed / dur));
			var cur = fromVal + (toVal - fromVal) * p;
			gblur.setAttribute('stdDeviation', cur + ' 0');
			if (elapsed < dur) {
				raf = requestAnimationFrame(step);
			} else {
				startTs = null;
				raf = null;
			}
		}

		function startAnim(f, t, d) {
			if (raf) { cancelAnimationFrame(raf); }
			fromVal = f;
			toVal = t;
			dur = d || 200;
			startTs = null;
			raf = requestAnimationFrame(step);
		}

		function readCur() {
			var v = parseFloat(gblur.getAttribute('stdDeviation'));
			return isNaN(v) ? 8 : v;
		}

		worksItem.addEventListener('mouseenter', function () { startAnim(readCur(), 0); });
		worksItem.addEventListener('mouseleave', function () { startAnim(readCur(), 8); });
		worksItem.addEventListener('focusin', function () { startAnim(readCur(), 0); });
		worksItem.addEventListener('focusout', function () { startAnim(readCur(), 8); });
		// 点击：随机强度（6–14）的水平模糊脉冲，340ms 衰减回 0
		worksItem.addEventListener('click', function () {
			var peak = 6 + Math.random() * 8;
			startAnim(peak, 0, 340);
		});
	}
	}

	// 脚本可能先于 .rzx-skeuo 容器渲染，等 DOM 就绪再绑定
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
