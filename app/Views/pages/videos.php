<?php
// app/Views/pages/videos.php
// Videos展示页面 - 模板化版本

// 自动加载必要的类
require_once __DIR__ . '/../../Models/video_data.php';

// 获取所有视频分组数据（自动从目录扫描并应用配置）
$videoGroups = get_videos_for_display();
$activeGroups = array_filter($videoGroups, function($group) {
    return ($group['status'] ?? 'active') === 'active';
});
?>

<div class="title">
视频 Videos
</div>

<!-- 视频分组菜单 -->
<div id="ray-video-wapper">
	<div id="ray-video-showbox" aria-label="Video Categories Showbox">
		<?php 
		$groupIndex = 0;
		foreach ($activeGroups as $groupId => $group): 
			$groupIndex++;
			// 获取该分组的前5个视频预览图作为拼接背景
			$previewImages = array_slice($group['videos'] ?? [], 0, 5);
		?>
		<div class="video-group-item" id="video-group-<?= $groupIndex ?>" data-group-id="<?= htmlspecialchars($groupId) ?>">
			<div class="video-group-previews">
				<?php if (!empty($previewImages)): ?>
					<?php foreach ($previewImages as $video): ?>
						<div class="preview-thumb">
							<img src="<?= htmlspecialchars($video['poster'] ?? '') ?>" alt="<?= htmlspecialchars($video['title'] ?? '') ?>">
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="preview-placeholder">
						<span><?= htmlspecialchars($groupId) ?></span>
					</div>
				<?php endif; ?>
			</div>
			<div class="video-group-title">
				<h3><?= htmlspecialchars($group['title'] ?? $groupId) ?></h3>
				<p class="video-count"><?= count($group['videos'] ?? []) ?> 个视频</p>
			</div>
		</div>
		<?php endforeach; ?>
		
		<div id="video-overlay"></div>
	</div>
</div>

<!-- 视频显示区域 -->
<div id="ray-video-displaybox" aria-label="Video Display Area">
	<div class="video-grid" id="video-grid-container">
		<!-- 视频将通过JavaScript动态加载 -->
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoGroupItems = document.querySelectorAll('.video-group-item');
    const videoGridContainer = document.getElementById('video-grid-container');
    const overlay = document.getElementById('video-overlay');
    
    // 视频数据（从PHP传递到JavaScript）
    const videoGroupsData = <?= json_encode($activeGroups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    // 动态计算overlay位置
    function updateOverlayPosition() {
        videoGroupItems.forEach((item, index) => {
            item.addEventListener('mouseenter', function() {
                const rect = this.getBoundingClientRect();
                const menuRect = this.parentElement.getBoundingClientRect();
                const offsetX = rect.left - menuRect.left;
                
                overlay.style.transform = `translate3d(${offsetX}px, 0, 0)`;
            });
        });
    }
    
    // 初始化overlay位置计算
    updateOverlayPosition();
    
    // 窗口resize时重新计算
    window.addEventListener('resize', updateOverlayPosition);

    videoGroupItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            
            const groupId = this.dataset.groupId;
            if (!groupId || !videoGroupsData[groupId]) return;
            
            const groupData = videoGroupsData[groupId];
            
            // 生成视频HTML
            let videosHtml = '';
            if (groupData.videos && Array.isArray(groupData.videos)) {
                videosHtml = groupData.videos.map((video, index) => {
                    const mp4Source = video.sources?.mp4 || video.mp4 || '';
                    const webmSource = video.sources?.webm || video.webm || '';
                    const poster = video.poster || '';
                    const title = video.title || '';
                    const description = video.description || '';
                    
                    return `
                        <div class="video-item" data-video-index="${index}">
                            <div class="video-thumbnail" style="background-image: url('${poster}')">
                                <div class="play-overlay">
                                    <svg class="play-icon" viewBox="0 0 24 24" width="64" height="64">
                                        <path fill="currentColor" d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <video class="movie" 
                                   poster="${poster}" 
                                   preload="none" 
                                   controls 
                                   style="display: none;"
                                   data-mp4="${mp4Source}"
                                   data-webm="${webmSource}">
                                您的浏览器不支持HTML5视频播放。
                            </video>
                            ${title ? `<p class="video-title">${title}</p>` : ''}
                            ${description ? `<p class="video-description">${description}</p>` : ''}
                        </div>
                    `;
                }).join('');
                
                // 在当前分组视频末尾添加HTML5提示（模仿视频项样式）
                videosHtml += `
                    <div class="html5-tip">
                        <div class="html5-poster">
                            <img src="/assets/movie/html5-150.png" alt="HTML5" />
                        </div>
                        <p class="html5-title">本页面视频采用HTML5标准</p>
                        <p class="html5-description">请使用<a href="http://www.google.cn/chrome/intl/zh-CN/landing_chrome.html?hl=zh" target="_blank">Chrome</a>, Firefox, 或者 Safari 浏览该网页.</p>
                    </div>
                `;
            }
            
            videoGridContainer.innerHTML = videosHtml || '<p style="text-align:center; color:#999;">该分组暂无视频</p>';
            
            // 为每个视频项添加交互事件
            initVideoInteractions();
            
            // 移除自动滚动，让用户手动查看
            // videoGridContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // 初始化视频交互（hover 加载，click 播放）
    function initVideoInteractions() {
        const videoItems = document.querySelectorAll('.video-item');
        
        videoItems.forEach(item => {
            const thumbnail = item.querySelector('.video-thumbnail');
            const video = item.querySelector('video');
            let isLoaded = false;
            let hoverTimer = null;
            
            // 鼠标悬停 - 延迟加载视频
            item.addEventListener('mouseenter', function() {
                // 延迟 300ms 加载，避免快速划过时加载
                hoverTimer = setTimeout(() => {
                    if (!isLoaded) {
                        loadVideo(video);
                        isLoaded = true;
                    }
                }, 300);
            });
            
            item.addEventListener('mouseleave', function() {
                // 取消延迟加载
                if (hoverTimer) {
                    clearTimeout(hoverTimer);
                    hoverTimer = null;
                }
            });
            
            // 点击缩略图 - 显示视频并播放
            if (thumbnail) {
                thumbnail.addEventListener('click', function() {
                    // 确保视频已加载
                    if (!isLoaded) {
                        loadVideo(video);
                        isLoaded = true;
                    }
                    
                    // 隐藏缩略图，显示视频
                    thumbnail.style.display = 'none';
                    video.style.display = 'block';
                    
                    // 播放视频
                    video.play().catch(err => {
                        console.log('视频播放失败:', err);
                    });
                });
            }
            
            // 视频暂停时，可选择显示回缩略图
            video.addEventListener('pause', function() {
                // 如果视频播放时间很短（<2秒），显示回缩略图
                if (video.currentTime < 2) {
                    setTimeout(() => {
                        if (video.paused) {
                            video.style.display = 'none';
                            if (thumbnail) thumbnail.style.display = 'block';
                        }
                    }, 1000);
                }
            });
        });
    }
    
    // 加载视频源
    function loadVideo(videoElement) {
        const mp4Src = videoElement.dataset.mp4;
        const webmSrc = videoElement.dataset.webm;
        
        // 创建 source 元素
        if (mp4Src) {
            const sourceMP4 = document.createElement('source');
            sourceMP4.src = mp4Src;
            sourceMP4.type = 'video/mp4';
            videoElement.appendChild(sourceMP4);
        }
        
        if (webmSrc) {
            const sourceWebM = document.createElement('source');
            sourceWebM.src = webmSrc;
            sourceWebM.type = 'video/webm';
            videoElement.appendChild(sourceWebM);
        }
        
        // 触发加载
        videoElement.load();
    }
});
</script>
