<?php
/**
 * 画廊管理器
 * 管理网站的图片画廊，包括上传、编辑、删除等功能
 */

// 设置页面信息
$page_title = '📁 画廊管理';
$page_subtitle = '管理网站的图片画廊，包括上传、编辑、删除等功能';
$_GET['page'] = 'gallery-manager';

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

// 获取画廊统计信息
require_once __DIR__ . '/../../Utils/GalleryManager.php';
$galleryManager = new GalleryManager();

$galleries = ['single-works', 'galleries'];
$gallery_stats = [];

foreach ($galleries as $gallery) {
    $categories = $galleryManager->getGalleryCategories($gallery);
    $total_images = 0;
    $total_thumbs = 0;

    foreach ($categories as $category) {
        $images = glob("../assets/images/$gallery/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
        $thumbs = glob("../assets/images/$gallery/thumbs/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

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
    <h1>画廊管理</h1>
    <p>管理网站图片画廊，包括缩略图生成、清理等操作</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 画廊统计 -->
<div class="content-card">
    <h3>📊 画廊统计概览</h3>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php foreach ($gallery_stats as $gallery => $stats): ?>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-feather="folder"></i>
                </div>
                <div class="stat-info">
                    <h3><?= ucfirst($gallery) ?></h3>
                    <p><?= $stats['categories'] ?> 个分类</p>
                    <p><?= $stats['images'] ?> 张图片</p>
                    <p><?= $stats['thumbs'] ?> 个缩略图</p>
                    <?php if ($stats['images'] > $stats['thumbs']): ?>
                        <small style="color: var(--warning-color);">⚠️ 部分图片缺少缩略图</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 缩略图管理 -->
<div class="content-card">
    <h3>🖼️ 缩略图管理</h3>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <!-- 重新生成缩略图 -->
        <div class="form-section">
            <h4>重新生成缩略图</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">为指定分类重新生成所有缩略图</p>

            <form method="POST">
                <input type="hidden" name="action" value="regenerate_thumbs">

                <div class="form-group">
                    <label for="gallery">选择画廊</label>
                    <select name="gallery" id="gallery" class="form-control" onchange="updateCategories()">
                        <option value="single-works">Single-Works</option>
                        <option value="galleries">Galleries</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category">选择分类</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="">请选择分类</option>
                        <?php foreach ($gallery_stats['single-works']['categories_list'] as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-feather="refresh-cw"></i>
                    重新生成缩略图
                </button>
            </form>
        </div>

        <!-- 清理缩略图 -->
        <div class="form-section">
            <h4>清理缩略图</h4>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">清理所有缩略图文件（谨慎操作）</p>

            <form method="POST" onsubmit="return confirm('确定要清理所有缩略图吗？此操作不可恢复！')">
                <input type="hidden" name="action" value="clean_thumbs">

                <div class="form-group">
                    <label for="clean_gallery">选择画廊</label>
                    <select name="gallery" id="clean_gallery" class="form-control">
                        <option value="single-works">Single-Works</option>
                        <option value="galleries">Galleries</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline" style="color: var(--error-color); border-color: var(--error-color);">
                    <i data-feather="trash-2"></i>
                    清理所有缩略图
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 分类详情 -->
<div class="content-card">
    <h3>📁 分类详细信息</h3>

    <div style="margin-top: 20px;">
        <?php foreach ($gallery_stats as $gallery => $stats): ?>
            <h4><?= ucfirst($gallery) ?> 画廊</h4>

            <?php if (empty($stats['categories_list'])): ?>
                <p style="color: var(--text-muted);">暂无分类</p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <?php foreach ($stats['categories_list'] as $category): ?>
                        <?php
                        $images = glob(__DIR__ . "/../../../public/assets/images/$gallery/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
                        $thumbs = glob(__DIR__ . "/../../../public/assets/images/$gallery/thumbs/$category/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
                        $image_count = count($images);
                        $thumb_count = count($thumbs);
                        ?>
                        <div style="border: 1px solid var(--border-light); border-radius: 8px; padding: 15px;">
                            <h5 style="margin: 0 0 10px 0; color: var(--primary-color);"><?= htmlspecialchars($category) ?></h5>
                            <p style="margin: 5px 0; font-size: 13px;">📷 图片: <?= $image_count ?> 张</p>
                            <p style="margin: 5px 0; font-size: 13px;">🖼️ 缩略图: <?= $thumb_count ?> 个</p>

                            <?php if ($image_count > $thumb_count): ?>
                                <p style="color: var(--warning-color); font-size: 12px; margin: 5px 0;">
                                    ⚠️ 缺少 <?= $image_count - $thumb_count ?> 个缩略图
                                </p>
                            <?php elseif ($image_count === $thumb_count && $image_count > 0): ?>
                                <p style="color: var(--success-color); font-size: 12px; margin: 5px 0;">
                                    ✅ 缩略图完整
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
