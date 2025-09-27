<?php
// app/Views/pages/single-works.php
// Single Works展示页面 - 升级版

// 自动加载必要的类
require_once __DIR__ . '/../../Utils/FileScanner.php';
require_once __DIR__ . '/../../Utils/ImageProcessor.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';
require_once __DIR__ . '/../../Services/ThumbnailService.php';

// 配置Single Works目录（小写）
$singleWorksDir = 'single-works';
$galleryManager = new GalleryManager();

// 获取排序后的分组数据
$categoriesData = $galleryManager->getSortedCategories($singleWorksDir);

// 为每个分组生成缩略图（single-works页面专用配置：200x200px）
foreach ($categoriesData as $categoryData) {
    $categoryPath = __DIR__ . '/../../../public/assets/images/' . $singleWorksDir . '/' . $categoryData['name'];
    ThumbnailService::generateBatchForPage($categoryPath, 'single-works');
}
?>

<div id="ray-pic-root" class="ray-pic-root">

	<div id="ray-pic-sidebar">
		<header id="ray-pic-header" class="ray-pic-header">
			<h1 id="ray-pic-title">图片 · 游戏 · 同人 · 插画</h1>
			<p id="ray-pic-sub">Illustrator · game · comic</p>
		</header>

		<main id="ray-pic-main" class="ray-pic-main">
			<!-- 缩略图库 / 列表 -->
			<aside id="ray-pic-sidbarbox" class="ray-pic-sidbarbox">
				<nav id="ray-pic-thumbs" class="ray-pic-thumbs">
					<?php 
					// 动态生成所有分组的图片展示
					foreach ($categoriesData as $index => $categoryData): 
						$category = $categoryData['name'];
						$displayName = $categoryData['display_name'];
						$description = $categoryData['description'];
						
						$images = $galleryManager->getCategoryImages($singleWorksDir, $category);
						
						if (empty($images)) continue;
						
						// 为不同分组设置不同的 rel 属性
						$galleryRel = 'gallery' . ($index + 1);
					?>
					<section class="ray-pic-section" data-category="<?= htmlspecialchars($category) ?>">
						<h2 class="ray-pic-section-title" title="<?= htmlspecialchars($description) ?>">
							<?= htmlspecialchars($displayName) ?>
						</h2>
						<ul class="ray-pic-list clearfix">
							<?php foreach ($images as $image): 
								// 构建缩略图路径 (使用thumbs目录)
								$thumbUrl = '/assets/images/' . $singleWorksDir . '/' . $category . '/thumbs/' . $image['name'];
								$thumbPath = __DIR__ . '/../../../public' . $thumbUrl;
								
								// 如果缩略图不存在，使用原图
								if (!file_exists($thumbPath)) {
									$thumbUrl = $image['url']; // 降级使用原图
								}
								
								// 提取文件名作为alt属性
								$altText = pathinfo($image['name'], PATHINFO_FILENAME);
							?>
							<li>
								<a href="<?= htmlspecialchars($image['url']) ?>" rel="<?= $galleryRel ?>" title="<?= htmlspecialchars($altText) ?>">
									<img loading="lazy" 
										 src="<?= htmlspecialchars($thumbUrl) ?>" 
										 width="60" 
										 height="60" 
										 alt="<?= htmlspecialchars($altText) ?>" />
								</a>
							</li>
							<?php endforeach; ?>
						</ul>
					</section>
					<?php endforeach; ?>
				</nav>
			</aside>

		</main>

	</div>

	<!-- 展示区 -->
	<section id="ray-pic-display-area" class="ray-pic-display-area">
		<div id="ray-pic-display-shell" class="ray-pic-display-shell">
			<div id="ray-pic-final" class="ray-pic-final" role="region" aria-label="展示区">
				<p class="ray-pic-instruction"></p>
			</div>
		</div>
	</section>
</div>

<script src="/assets/js/ray-pic.js"></script>
