<?php
/**
 * Galleries 画廊管理器
 * 管理前台 /galleries 页面显示的画廊集合
 */

// 设置页面信息
$page_title = '📁 Galleries 画廊管理';
$page_subtitle = '管理前台画廊页面 (/galleries) 显示的作品集';
$_GET['page'] = 'galleries-manager';

require_once __DIR__ . '/../Views/layouts/header.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';

$galleryManager = new GalleryManager();

// 处理操作
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'regenerate_icons':
            try {
                $result = $galleryManager->generateAllIcons();
                $message = "成功重新生成所有画廊图标";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "操作失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;

        case 'scan_galleries':
            try {
                $galleries = $galleryManager->scanGalleries();
                $message = "扫描完成，找到 " . count($galleries) . " 个画廊";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "扫描失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
    }
}

// 获取画廊列表
$galleries = $galleryManager->scanGalleries();
$galleriesPath = __DIR__ . '/../../../public/assets/images/galleries';
?>

<div class="page-header">
    <h1>📁 Galleries 画廊管理</h1>
    <p>管理前台 <code>/galleries</code> 页面显示的画廊集合</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- 画廊概览 -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3>📊 画廊概览</h3>
            <p style="color: var(--text-secondary); margin: 5px 0;">
                当前共有 <strong style="color: var(--primary-color);"><?= count($galleries) ?></strong> 个画廊集合
            </p>
        </div>
        <div>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="scan_galleries">
                <button type="submit" class="btn btn-outline" style="margin-right: 10px;">
                    <i data-feather="refresh-cw"></i>
                    重新扫描
                </button>
            </form>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="regenerate_icons">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="image"></i>
                    重新生成图标
                </button>
            </form>
        </div>
    </div>

    <!-- 整体统计 -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <?php
        $totalImages = 0;
        $totalCategories = 0;
        foreach ($galleries as $gallery) {
            $totalImages += $gallery['image_count'];
            $totalCategories++;
        }
        ?>
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <h2 style="margin: 0 0 5px 0; font-size: 32px;"><?= count($galleries) ?></h2>
            <p style="margin: 0; opacity: 0.9;">画廊总数</p>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <h2 style="margin: 0 0 5px 0; font-size: 32px;"><?= $totalImages ?></h2>
            <p style="margin: 0; opacity: 0.9;">图片总数</p>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
            <h2 style="margin: 0 0 5px 0; font-size: 32px;"><?= round($totalImages / max(count($galleries), 1)) ?></h2>
            <p style="margin: 0; opacity: 0.9;">平均图片数</p>
        </div>
    </div>
</div>

<!-- 画廊列表 -->
<div class="content-card">
    <h3>📂 画廊列表</h3>
    <p style="color: var(--text-secondary); margin-bottom: 20px;">
        画廊目录位置: <code><?= htmlspecialchars($galleriesPath) ?></code>
    </p>

    <?php if (empty($galleries)): ?>
        <div style="text-align: center; padding: 60px 20px; background: var(--bg-secondary); border-radius: 12px;">
            <i data-feather="inbox" style="width: 64px; height: 64px; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h4 style="color: var(--text-muted);">暂无画廊</h4>
            <p style="color: var(--text-secondary);">
                请在 <code>public/assets/images/galleries/</code> 目录下创建文件夹并添加图片
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($galleries as $index => $gallery): ?>
                <?php
                $iconResult = $galleryManager->generateGalleryIcon($gallery['name']);
                $defaultIcon = ($iconResult && isset($iconResult['default'])) ? $iconResult['default']['icon_url'] : '/assets/images/404.jpg';
                $hoverIcon = ($iconResult && isset($iconResult['hover'])) ? $iconResult['hover']['icon_url'] : $defaultIcon;
                ?>
                <div class="gallery-card" style="border: 2px solid var(--border-color); border-radius: 12px; overflow: hidden; transition: all 0.3s;">
                    <!-- 画廊预览图 -->
                    <div style="position: relative; width: 100%; height: 200px; overflow: hidden; background: var(--bg-secondary);">
                        <img src="<?= htmlspecialchars($defaultIcon) ?>"
                             alt="<?= htmlspecialchars($gallery['name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                             onmouseover="this.src='<?= htmlspecialchars($hoverIcon) ?>'"
                             onmouseout="this.src='<?= htmlspecialchars($defaultIcon) ?>'">
                        <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;">
                            <?= $gallery['image_count'] ?> 张图片
                        </div>
                    </div>

                    <!-- 画廊信息 -->
                    <div style="padding: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: var(--primary-color); font-size: 18px;">
                            <?= htmlspecialchars($gallery['name']) ?>
                        </h4>

                        <div style="margin-bottom: 15px;">
                            <p style="margin: 5px 0; font-size: 13px; color: var(--text-secondary);">
                                <i data-feather="folder" style="width: 14px; height: 14px;"></i>
                                路径: <code style="font-size: 11px;"><?= htmlspecialchars($gallery['path']) ?></code>
                            </p>
                            <p style="margin: 5px 0; font-size: 13px; color: var(--text-secondary);">
                                <i data-feather="link" style="width: 14px; height: 14px;"></i>
                                路由: <code><?= htmlspecialchars($gallery['route']) ?></code>
                            </p>
                            <p style="margin: 5px 0; font-size: 13px; color: var(--text-secondary);">
                                <i data-feather="image" style="width: 14px; height: 14px;"></i>
                                图片数量: <strong><?= $gallery['image_count'] ?></strong> 张
                            </p>
                        </div>

                        <!-- 操作按钮 -->
                        <div style="display: flex; gap: 10px;">
                            <a href="<?= htmlspecialchars($gallery['route']) ?>"
                               target="_blank"
                               class="btn btn-primary"
                               style="flex: 1; text-align: center; text-decoration: none;">
                                <i data-feather="eye"></i>
                                前台预览
                            </a>
                            <a href="<?= htmlspecialchars($galleriesPath . '/' . $gallery['name']) ?>"
                               class="btn btn-outline"
                               style="flex: 1; text-align: center; text-decoration: none;"
                               title="打开文件夹">
                                <i data-feather="folder-open"></i>
                                打开目录
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 使用说明 -->
<div class="content-card">
    <h3>📖 使用说明</h3>
    <div style="background: var(--info-bg, #e3f2fd); border-left: 4px solid var(--info-color, #2196f3); padding: 20px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: var(--primary-color);">如何添加新画廊？</h4>
        <ol style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
            <li>在 <code>public/assets/images/galleries/</code> 目录下创建新文件夹</li>
            <li>将图片文件（jpg, jpeg, png, gif, webp）放入该文件夹</li>
            <li>回到本页面点击"重新扫描"按钮</li>
            <li>系统会自动为画廊生成图标和路由</li>
            <li>前台 <code>/galleries</code> 页面将自动显示新画廊</li>
        </ol>

        <h4 style="margin-top: 20px; color: var(--primary-color);">图标生成规则</h4>
        <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
            <li><strong>默认图标</strong>: 使用画廊中第 1 张图片</li>
            <li><strong>悬停图标</strong>: 使用画廊中第 2 张图片（如果存在）</li>
            <li>图标会自动生成并缓存到 <code>galleries/icons/</code> 目录</li>
            <li>如需更新图标，可点击"重新生成图标"按钮</li>
        </ul>

        <h4 style="margin-top: 20px; color: var(--primary-color);">注意事项</h4>
        <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
            <li>文件夹名称将作为画廊的标识符和 URL 路径</li>
            <li>建议使用英文或拼音命名文件夹，避免使用特殊字符</li>
            <li>每个画廊至少需要 1 张图片才能正常显示</li>
            <li>图片格式支持: jpg, jpeg, png, gif, webp</li>
        </ul>
    </div>
</div>

<style>
.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-color: var(--primary-color);
}

.gallery-card img:hover {
    transform: scale(1.05);
}
</style>

<script>
function pageInit() {
    console.log('Galleries Manager initialized');
}
</script>

<?php require_once __DIR__ . '/../Views/layouts/footer.php'; ?>
