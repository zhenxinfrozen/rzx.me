<?php require_once __DIR__ . '/../includes/bootstrap.php'; ?>
<!--
	说明：根据用户要求，本仓库已移除本地的 prettyPhoto 插件 JS 文件。
	该页面之前通过动态方式加载该插件；如果需要恢复这些文件，可以从删除它们的前一个提交中恢复。

	恢复示例（从仓库根目录执行）：
		git checkout d3b15fe^ -- public/assets/js/jquery.prettyPhoto.js
		git checkout d3b15fe^ -- "public/dev/demo/js/新建文件夹/jquery.prettyPhoto.js"
		git add public/assets/js/jquery.prettyPhoto.js "public/dev/demo/js/新建文件夹/jquery.prettyPhoto.js"
		git commit -m "chore(pictures): restore prettyPhoto plugin"

	恢复文件后，如果需要可以在此文件中重新启用插件的加载与初始化代码。
-->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="我不太喜欢也不太擅长的色彩作品" />
<meta name="author" content="ray,ruizhenxin,rzx.me">
<link rel="stylesheet" href="/assets/css/pictures.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" />
<link href="/assets/css/footer.css" rel="stylesheet" type="text/css" />
<!-- Load a compatible jQuery core for prettyPhoto (local copy 1.9.1 available in Scripts/) -->
	<style type="text/css" media="screen">
			* { margin: 0; padding: 0;border:none; }
			
			
			
			h4 { margin: 15px 0 5px 0; }
			
			h4, p { font-size: 1.2em; }
			
			ul li { display: inline;}
			
			.wide {
				border-bottom: 1px #000 solid;
				width: 4000px;
			}
		</style>
</head>


<body>
<?php require_once $INCLUDE_HEADER; ?>
<h2>&nbsp;</h2>
<h2>&nbsp;</h2>
<h2>图片,游戏,同人,插画</h2>
<p>Illustrator game comic.....</p>
		
		<h4>Animals</h4>
<div class="width">
		<ul class="gallery clearfix">
			<li><a href="/assets/images/fullscreen/a_2.jpg" rel="prettyPhoto[gallery1]" title="..."><img src="/assets/images/thumbnails/a_2.gif" width="60" height="60" alt="World of Warcraft" /></a></li>
			<li><a href="/assets/images/fullscreen/a_3.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_3.gif" width="60" height="60" alt="Nice Night" /></a></li>
			<li><a href="/assets/images/fullscreen/a_4.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_4.gif" width="60" height="60" alt="Darksiders" /></a></li>
			<li><a href="/assets/images/fullscreen/a_5.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_5.gif" width="60" height="60" alt="Dante horses" /></a></li>
			<li><a href="/assets/images/fullscreen/a_6.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_6.gif" width="60" height="60" alt="Footman Human" /></a></li>
			<li><a href="/assets/images/fullscreen/a_7.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_7.gif" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/a_8.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_8.gif" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/a_9.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/a_9.gif" width="60" height="60" alt="" /></a></li>
		</ul>
        </div>
		
		<h4>Games</h4>
<div class="width">
		<ul class="gallery clearfix">
			<li><a href="/assets/images/fullscreen/1.jpg" rel="prettyPhoto[gallery1]" title="都是些老图了,彩图数量有限(一半都是鼠绘)充数."><img src="/assets/images/thumbnails/t_1.jpg" width="60" height="60" alt="World of Warcraft" /></a></li>
			<li><a href="/assets/images/fullscreen/2.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_2.jpg" width="60" height="60" alt="Nice Night" /></a></li>
			<li><a href="/assets/images/fullscreen/3.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_3.gif" width="60" height="60" alt="Darksiders" /></a></li>
			<li><a href="/assets/images/fullscreen/4.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_4.jpg" width="60" height="60" alt="Sylvanas Windrunner" /></a></li>
			<li><a href="/assets/images/fullscreen/5.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_5.jpg" width="60" height="60" alt="Dante horses" /></a></li>
			<li><a href="/assets/images/fullscreen/6.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_6.jpg" width="60" height="60" alt="Footman Human" /></a></li>
			<li><a href="/assets/images/fullscreen/7.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_7.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/p01.jpg" rel="prettyPhoto"><img src="/assets/images/thumbnails/p_1.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/8.jpg" rel="prettyPhoto[gallery1]"><img src="/assets/images/thumbnails/t_8.jpg" width="60" height="60" alt="" /></a></li>
		</ul>
        </div>	

		<h4>Gallery comic</h4>
        <div class="width">
		<ul class="gallery clearfix">
			<li><a href="/assets/images/fullscreen/c01.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_1.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c02.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_2.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/d01.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/d_1.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c03.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_3.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c04.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_4.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c05.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_5.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c06.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_6.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c07.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_7.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/c08.jpg" rel="prettyPhoto[gallery2]"><img src="/assets/images/thumbnails/c_8.jpg" width="60" height="60" alt="" /></a></li>
			<li><a href="/assets/images/fullscreen/s01.jpg" rel="prettyPhoto" title="&lt;a href=&#x27;http://www.ruizhenxin.com/blog&#x27; target=&#x27;_blank&#x27; &gt;This will open [My Blog] in a new window&lt;/a&gt;"><img src="/assets/images/thumbnails/s_1.jpg" width="60" height="60" alt="" /></a></li>
		</ul>
</div>


		<h4>Special zone</h4>
        <div class="width">
		<ul class="gallery clearfix">
			<li><a href="/assets/images/fullscreen/s02.jpg" rel="prettyPhoto" title="&lt;a href=&#x27;http://www.ruizhenxin.com/blog&#x27; target=&#x27;_blank&#x27; &gt;This will open [My Blog] in a new window&lt;/a&gt;"><img src="/assets/images/thumbnails/s_2.gif" width="60" height="60" alt="" /></a></li>
		</ul>
</div>

		
<!-- 在 body 末尾加载脚本以避免阻塞渲染 -->
<script>
/*
 说明：此脚本负责在站点页脚（footer.php）提供现代 jQuery 之后，延迟加载旧的 prettyPhoto 插件并初始化。
 目的：
   - 避免同时加载多个 jQuery 版本导致冲突。
   - 只在 jQuery 可用时动态插入插件脚本并初始化画廊（lightbox）。

 如果你已经从仓库中删除了 `/assets/js/jquery.prettyPhoto.js`，该脚本将尝试加载但会在控制台记录错误（不会中断页面）。
 恢复插件后可取消注释此段或保留以实现非阻塞的动态加载。
*/
(function waitForjQueryAndLoad(){
	function loadPrettyPhoto(){
		// 检查 jQuery 是否已加载
		if (window.jQuery) {
			// 为 old prettyPhoto 插件提供必要的 $.browser shim（旧插件依赖此值）
			if (typeof jQuery.browser === 'undefined') {
				jQuery.browser = { msie: false, version: '0' };
			}
			// 动态插入插件脚本
			var s = document.createElement('script');
			s.src = '/assets/js/jquery.prettyPhoto.js';
			s.onload = function(){
				// 插件加载完成后初始化画廊
				jQuery(function($){
					$(".gallery a[rel^='prettyPhoto']").prettyPhoto({theme: 'dark_square'});
				});
			};
			s.onerror = function(){
				// 无法加载插件时在控制台输出警告（沉默失败，避免影响用户）
				console.warn('无法加载 prettyPhoto 插件');
			};
			document.body.appendChild(s);
		} else {
			// 如果 jQuery 还未出现，短暂等待后重试；页脚可能有延迟加载逻辑
			setTimeout(loadPrettyPhoto, 200);
		}
	}
	loadPrettyPhoto();
})();
</script>
<?php require_once $INCLUDE_FOOTER; ?>
</body>
</html>
