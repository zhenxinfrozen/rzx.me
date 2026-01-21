<?php
/**
 * 整站缩略图管理器
 * 统计和管理整个网站的缩略图，包括生成、清理等批量操作
 */

// 设置页面信息
$page_title = '🖼️ 整站缩略图管理';
$page_subtitle = '统计和管理整个网站的缩略图资源';
$_GET['page'] = 'thumbnail-manager';

require_once __DIR__ . '/../Views/layouts/header.php';

// 处理操作
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'regenerate_thumbs':
            try {
                require_once __DIR__ . '/../../Utils/GalleryManager.php';
                $galleryManager = new GalleryManager();

                $gallery = $_POST['gallery'] ?? 'single-works';
                $category = $_POST['category'] ?? '';

                if ($category) {
                    // 重新生成特定分类的缩略图
                    $sourceDir = __DIR__ . "/../../../public/assets/images/$gallery/$category";
                    $thumbDir = __DIR__ . "/../../../public/assets/images/$gallery/thumbs/$category";

                    if (is_dir($sourceDir)) {
                        $galleryManager->generateThumbnails($sourceDir, $thumbDir);
                        $message = "已成功重新生成 $category 分类的缩略图";
                        $message_type = 'success';
                    } else {
                        $message = "分类目录不存在: $category";
                        $message_type = 'error';
                    }
                } else {
                    $message = "请选择要重新生成缩略图的分类";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "操作失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;

        case 'clean_thumbs':
            try {
                $gallery = $_POST['gallery'] ?? 'single-works';
                $thumbsDir = __DIR__ . "/../../../public/assets/images/$gallery/thumbs";

                if (is_dir($thumbsDir)) {
                    $count = 0;
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($thumbsDir),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );

                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            unlink($file->getPathname());
                            $count++;
                        }
                    }

                    $message = "已清理 $count 个缩略图文件";
                    $message_type = 'success';
                } else {
                    $message = "缩略图目录不存在";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "清理失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
    }
}

// 获取整站画廊统计信息
require_once __DIR__ . '/../../Utils/GalleryManager.php';
$galleryManager = new GalleryManager();

$galleries = ['single-works', 'sketchbook', 'galleries', 'comic', 'videos'];
$gallery_stats = [];

foreach ($galleries as $gallery) {
    $galleryPath = __DIR__ . "/../../../public/assets/images/$gallery";

    // 检查画廊目录是否存在
    if (!is_dir($galleryPath)) {
        continue;
    }

    $categories = $galleryManager->getGalleryCategories($gallery);
    $total_images = 0;
    $total_thumbs = 0;

    foreach ($categories as $category) {
        $images = glob(__DIR__ . "/../../../public/assets/images/$gallery/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
        $thumbs = glob(__DIR__ . "/../../../public/assets/images/$gallery/thumbs/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

        $total_images += count($images);
        $total_thumbs += count($thumbs);
    }

    $gallery_stats[$gallery] = [
        'categories' => count($categories),
        'images' => $total_images,
        'thumbs' => $total_thumbs,
        'categories_list' => $categories
    ];
}
?>

<div class="page-header">
    <h1>🖼️ 整站缩略图管理</h1>
    <p>统计和管理整个网站所有画廊的缩略图资源</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 整站统计概览 -->
<div class="content-card">
    <h3>📊 整站缩略图统计</h3>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php
        $total_galleries = count($gallery_stats);
        $total_categories = array_sum(array_column($gallery_stats, 'categories'));
        $total_images = array_sum(array_column($gallery_stats, 'images'));
        $total_thumbs = array_sum(array_column($gallery_stats, 'thumbs'));
        ?>

        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px;">
            <div class="stat-icon">
                <i data-feather="folder" style="width: 40px; height: 40px;"></i>
            </div>
            <div class="stat-info">
                <h2 style="margin: 10px 0 5px 0;"><?= $total_galleries ?></h2>
                <p style="margin: 0; opacity: 0.9;">画廊总数</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px;">
            <div class="stat-icon">
                <i data-feather="layers" style="width: 40px; height: 40px;"></i>
            </div>
            <div class="stat-info">
                <h2 style="margin: 10px 0 5px 0;"><?= $total_categories ?></h2>
                <p style="margin: 0; opacity: 0.9;">分类总数</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 12px;">
            <div class="stat-icon">
                <i data-feather="image" style="width: 40px; height: 40px;"></i>
            </div>
            <div class="stat-info">
                <h2 style="margin: 10px 0 5px 0;"><?= $total_images ?></h2>
                <p style="margin: 0; opacity: 0.9;">图片总数</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 12px;">
            <div class="stat-icon">
                <i data-feather="grid" style="width: 40px; height: 40px;"></i>
            </div>
            <div class="stat-info">
                <h2 style="margin: 10px 0 5px 0;"><?= $total_thumbs ?></h2>
                <p style="margin: 0; opacity: 0.9;">缩略图总数</p>
                <?php if ($total_images > $total_thumbs): ?>
                    <small style="opacity: 0.9;">⚠️ 缺少 <?= $total_images - $total_thumbs ?> 个</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 各画廊详细统计 -->
<div class="content-card">
    <h3>📁 各画廊详细统计</h3>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php foreach ($gallery_stats as $gallery => $stats): ?>
            <div class="stat-card" style="border: 2px solid var(--border-color); border-radius: 12px; padding: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: var(--primary-color);">
                        <i data-feather="folder"></i>
                        <?= ucfirst($gallery) ?>
                    </h4>
                    <?php if ($stats['images'] > $stats['thumbs']): ?>
                        <span style="background: var(--warning-color); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            需要处理
                        </span>
                    <?php else: ?>
                        <span style="background: var(--success-color); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                            ✓ 完整
                        </span>
                    <?php endif; ?>
                </div>
                <div style="color: var(--text-secondary); font-size: 14px;">
                    <p style="margin: 8px 0;">📂 分类: <?= $stats['categories'] ?> 个</p>
                    <p style="margin: 8px 0;">📷 图片: <?= $stats['images'] ?> 张</p>
                    <p style="margin: 8px 0;">🖼️ 缩略图: <?= $stats['thumbs'] ?> 个</p>
                    <?php if ($stats['images'] > $stats['thumbs']): ?>
                        <p style="color: var(--warning-color); margin: 8px 0; font-weight: bold;">
                            ⚠️ 缺少 <?= $stats['images'] - $stats['thumbs'] ?> 个缩略图
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 批量操作工具 -->
<div class="content-card">
    <h3>🛠️ 批量缩略图操作</h3>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <!-- 重新生成缩略图 -->
        <div class="form-section" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
            <h4>🔄 重新生成缩略图</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">为指定画廊的分类重新生成所有缩略图</p>

            <form method="POST">
                <input type="hidden" name="action" value="regenerate_thumbs">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="gallery" style="display: block; margin-bottom: 5px; font-weight: bold;">选择画廊</label>
                    <select name="gallery" id="gallery" class="form-control" onchange="updateCategories()" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <?php foreach ($gallery_stats as $gallery => $stats): ?>
                            <option value="<?= $gallery ?>"><?= ucfirst($gallery) ?> (<?= $stats['categories'] ?> 个分类)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="category" style="display: block; margin-bottom: 5px; font-weight: bold;">选择分类</label>
                    <select name="category" id="category" class="form-control" required style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <option value="">请选择分类</option>
                        <?php
                        $firstGallery = array_key_first($gallery_stats);
                        if ($firstGallery && isset($gallery_stats[$firstGallery]['categories_list'])):
                            foreach ($gallery_stats[$firstGallery]['categories_list'] as $category):
                        ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; background: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer;">
                    <i data-feather="refresh-cw"></i>
                    重新生成缩略图
                </button>
            </form>
        </div>

        <!-- 清理缩略图 -->
        <div class="form-section" style="border: 1px solid var(--error-color); border-radius: 8px; padding: 20px;">
            <h4 style="color: var(--error-color);">🗑️ 清理缩略图</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">清理指定画廊的所有缩略图文件（谨慎操作）</p>

            <form method="POST" onsubmit="return confirm('确定要清理所有缩略图吗？此操作不可恢复！\n\n清理后需要重新生成缩略图才能正常显示。')">
                <input type="hidden" name="action" value="clean_thumbs">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="clean_gallery" style="display: block; margin-bottom: 5px; font-weight: bold;">选择画廊</label>
                    <select name="gallery" id="clean_gallery" class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <?php foreach ($gallery_stats as $gallery => $stats): ?>
                            <option value="<?= $gallery ?>"><?= ucfirst($gallery) ?> (<?= $stats['thumbs'] ?> 个缩略图)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline" style="width: 100%; padding: 10px; color: var(--error-color); border: 2px solid var(--error-color); background: white; border-radius: 4px; cursor: pointer;">
                    <i data-feather="trash-2"></i>
                    清理所有缩略图
                </button>
            </form>
        </div>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: var(--info-bg, #e3f2fd); border-left: 4px solid var(--info-color, #2196f3); border-radius: 4px;">
        <p style="margin: 0; color: var(--text-primary);"><strong>💡 提示：</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px; color: var(--text-secondary);">
            <li>重新生成缩略图会覆盖现有的缩略图文件</li>
            <li>清理操作会删除所有缩略图，但不会影响原图</li>
            <li>建议在清理后立即重新生成缩略图</li>
            <li>大量图片的生成操作可能需要较长时间，请耐心等待</li>
        </ul>
    </div>
</div>

<!-- 分类详细信息 -->
<div class="content-card">
    <h3>📋 分类详细信息</h3>

    <div style="margin-top: 20px;">
        <?php foreach ($gallery_stats as $gallery => $stats): ?>
            <h4 style="color: var(--primary-color); margin-top: 30px;">
                <i data-feather="folder"></i>
                <?= ucfirst($gallery) ?> 画廊
            </h4>

            <?php if (empty($stats['categories_list'])): ?>
                <p style="color: var(--text-muted); padding: 20px; background: var(--bg-secondary); border-radius: 8px;">
                    暂无分类数据
                </p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <?php foreach ($stats['categories_list'] as $category): ?>
                        <?php
                        $images = glob(__DIR__ . "/../../../public/assets/images/$gallery/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
                        $thumbs = glob(__DIR__ . "/../../../public/assets/images/$gallery/thumbs/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
                        $image_count = count($images);
                        $thumb_count = count($thumbs);
                        $completion = $image_count > 0 ? round(($thumb_count / $image_count) * 100) : 0;
                        ?>
                        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px; transition: all 0.3s;">
                            <h5 style="margin: 0 0 10px 0; color: var(--primary-color); font-size: 15px;">
                                📁 <?= htmlspecialchars($category) ?>
                            </h5>
                            <p style="margin: 5px 0; font-size: 13px; color: var(--text-secondary);">
                                📷 图片: <strong><?= $image_count ?></strong> 张
                            </p>
                            <p style="margin: 5px 0; font-size: 13px; color: var(--text-secondary);">
                                🖼️ 缩略图: <strong><?= $thumb_count ?></strong> 个
                            </p>

                            <!-- 进度条 -->
                            <div style="margin: 10px 0;">
                                <div style="background: var(--bg-secondary); border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="background: <?= $completion >= 100 ? 'var(--success-color)' : 'var(--warning-color)' ?>; width: <?= $completion ?>%; height: 100%; transition: width 0.3s;"></div>
                                </div>
                                <small style="color: var(--text-muted); font-size: 11px;">完成度: <?= $completion ?>%</small>
                            </div>

                            <?php if ($image_count > $thumb_count): ?>
                                <p style="color: var(--warning-color); font-size: 12px; margin: 5px 0; font-weight: bold;">
                                    ⚠️ 缺少 <?= $image_count - $thumb_count ?> 个缩略图
                                </p>
                            <?php elseif ($image_count === $thumb_count && $image_count > 0): ?>
                                <p style="color: var(--success-color); font-size: 12px; margin: 5px 0; font-weight: bold;">
                                    ✅ 缩略图完整
                                </p>
                            <?php elseif ($image_count === 0): ?>
                                <p style="color: var(--text-muted); font-size: 12px; margin: 5px 0;">
                                    📭 分类为空
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<script>
// 页面特定的JavaScript
function pageInit() {
    // 更新分类列表
    window.updateCategories = function() {
        const gallery = document.getElementById('gallery').value;
        const categorySelect = document.getElementById('category');

        // 清空选项
        categorySelect.innerHTML = '<option value="">请选择分类</option>';

        // 根据选择的画廊添加分类选项
        const categories = <?= json_encode($gallery_stats) ?>;

        if (categories[gallery] && categories[gallery].categories_list) {
            categories[gallery].categories_list.forEach(function(category) {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
        }
    };
}
</script>

<?php require_once __DIR__ . '/../Views/layouts/footer.php'; ?>
