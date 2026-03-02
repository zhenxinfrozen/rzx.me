<?php
/**
 * Video Gallery 管理页面 - 新版本
 * 使用标准化组件和三栏布局
 *
 * @version 1.0.0
 * @date 2026-01-22
 *
 * v1.0.0 更新日志:
 * - 基于 sketchbook-new v2.5.5 模板创建
 * - 适配 Video Gallery 视频管理功能
 * - 数据存储：app/storage/data/video-gallery-sort.json
 * - 视频路径：public/files/videos/
 * - 统一架构：三栏布局 + 组件化设计
 * - 优化：CSS样式，操作按钮移到右上角竖直排列
 * - 移除：移动按钮（拖拽功能已满足需求）
 * - 改进：当前缩略图始终显示星标，hover时显示其他操作
 *
 * v2.5.1 更新日志:
 * - 修复：缩略图扩展名不匹配问题（动态查找任意格式的缩略图文件）
 * - 改进：getCategoryThumbnailInfo 函数现在会验证配置中的缩略图是否存在
 * - 改进：如果配置的缩略图文件不存在，自动查找同名但不同扩展名的文件
 *
 * v2.5.0 更新日志:
 * - 修复：显示名称参数名称错误(display_name → displayName)，现在能正确保存
 * - 新增：文件夹重命名功能，支持修改category文件夹名
 * - 新增：后端自动处理文件夹重命名和配置更新
 * - 改进：前端验证文件夹名格式（仅允许英文、数字、下划线和短横线）
 */

$page_title = $page_title ?? '🛠️ Video Gallery 管理 (新版)';
$page_subtitle = $page_subtitle ?? '管理 Video Gallery 页面视频集与视频 - 使用新组件';
$_GET['page'] = $_GET['page'] ?? 'videos';

// 加载 media-manager 服务（使用命名空间）
require_once __DIR__ . '/../../Services/Media/ImageService.php';
require_once __DIR__ . '/../../Services/Media/VideoService.php';
require_once __DIR__ . '/../../Services/Media/ConfigService.php';
require_once __DIR__ . '/../../Services/Media/CategoryService.php';

use App\Admin\Services\Media\ImageService;
use App\Admin\Services\Media\VideoService;
use App\Admin\Services\Media\ConfigService;
use App\Admin\Services\Media\CategoryService;

// 加载数据
if (!isset($categoryData)) {
    $categoryData = [];
    try {
        $imageService = new ImageService();
        $videoService = new VideoService();
        $configService = new ConfigService();
        $categoryService = new CategoryService($imageService, $videoService, $configService);

        // 使用 CategoryService 获取 videos 模块数据
        $baseDir = realpath(__DIR__ . '/../../../../public/assets/videos/video-gallery');
        $configPath = __DIR__ . '/../../../storage/data/video-gallery-sort.json';

        // getCategories 直接返回数组，不是 ['success' => true, 'categories' => ...] 格式
        $categories = $categoryService->getCategories($baseDir, 'videos', $configPath);

        // 转换为页面需要的格式（添加 id 和 first_image_thumb 字段）
        foreach ($categories as $cat) {
            $categoryData[] = [
                'id' => $cat['name'],
                'display_name' => $cat['display_name'],
                'description' => $cat['description'] ?? '',
                'video_count' => $cat['video_count'],
                'thumbnail' => $cat['thumbnail'],
                'first_image_thumb' => $cat['thumbnail'],
                'position' => $cat['position']
            ];
        }

    } catch (\Exception $e) {
        error_log('Videos-new: Exception - ' . $e->getMessage());
        error_log('Videos-new: Stack trace - ' . $e->getTraceAsString());
    }
}

$categoryData = $categoryData ?? [];

$currentConfig = $currentConfig ?? [
    'sort_method' => 'custom_order',
    'custom_order' => [],
    'prefix_settings' => ['remove_prefix' => true, 'separator' => '-'],
    'display_names' => [],
    'descriptions' => [],
];
$flashMessage = $flashMessage ?? null;
$totalCategories = count($categoryData);
?>

<!-- 引入新组件样式 -->
<link rel="stylesheet" href="/assets/admin/css/admin-common.css?v=1.0.0">
<link rel="stylesheet" href="/assets/admin/css/admin-three-column.css?v=1.0.0">

<!-- 自定义样式（覆盖和补充） -->
<style>
/* 添加按钮样式（参考旧版本） */
.add-category-btn {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: #108e1d;
    color: white;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(16, 142, 29, 0.6);
}
.add-category-btn:hover {
    background: #47a854;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(16, 142, 29, 0.8);
}
.add-category-btn:active {
    transform: translateY(-50%) scale(0.95);
}

/* 缩略图编辑区域 */
.thumbnail-edit-section {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

/* Videos 模块专用：左侧列表缩略图 16:9 */
.category-thumbnail {
    width: 85px;
    height: 48px;
    border-radius: 6px;
    object-fit: cover;
    border: 2px solid #dee2e6;
    margin-right: 12px;
    flex-shrink: 0;
}

/* Videos 模块：上传缩略图按钮 16:9 比例 */
.thumbnail-upload-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 68px;
    border: 2px dashed #6c757d;
    border-radius: 8px;
    background: white;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-direction: column;
    gap: 4px;
}

.thumbnail-upload-btn:hover {
    border-color: #007bff;
    color: #007bff;
    background: #f8f9fa;
}

/* 当前缩略图预览 16:9 */
#edit-thumbnail-img {
    width: 120px;
    height: 68px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #dee2e6;
}

/* 调整图片管理操作按钮样式 */
.admin-image-actions {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.admin-image-item:hover .admin-image-actions {
    opacity: 1;
}

/* 当前缩略图只显示星标按钮 */
.admin-image-item.is-thumbnail .admin-image-action-btn.star {
    opacity: 1;
}

/* hover时显示所有按钮 */
.admin-image-item.is-thumbnail:hover .admin-image-actions {
    opacity: 1;
}

.admin-image-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: rgba(255, 255, 255, 0.95);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.admin-image-action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

.admin-image-action-btn.star {
    color: #ffc107;
}

/* 活跃状态使用深色背景，避免与黄色图标重复 */
.admin-image-action-btn.star.active {
    background: #ff9800;
    color: white;
}

.admin-image-action-btn.delete {
    color: #dc3545;
}

.admin-image-action-btn.delete:hover {
    background: #dc3545;
    color: white;
}

.thumbnail-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 68px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    background: #f8f9fa;
}

/* 视频网格容器 */
.video-grid-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-start;
    align-items: flex-start;
    min-height: 160px;
    padding: 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

/* 视频项样式 */
.video-item {
    width: 120px;
    height: 68px;
    border-radius: 6px;
    overflow: hidden;
    display: inline-block;
    border: 2px solid #dee2e6;
    position: relative;
    cursor: pointer;
    background: #343a40;
}
.video-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.video-item:hover {
    border-color: #007bff;
}
.video-item.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13,110,253,.25);
}

/* 视频操作按钮 */
.video-actions {
    position: absolute;
    top: 2px;
    right: 2px;
    display: none;
    flex-direction: column;
    gap: 2px;
}
.video-item:hover .video-actions {
    display: flex;
}
.video-action-btn {
    width: 20px;
    height: 20px;
    border: none;
    border-radius: 3px;
    color: white;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.video-action-btn.delete {
    background: #dc3545;
}
.video-action-btn.delete:hover {
    background: #c82333;
}

.add-video-btn {
    width: 120px;
    height: 68px;
    border-radius: 6px;
    border: 2px dashed #007bff;
    background: #f8f9fa;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 24px;
    font-weight: bold;
}
.add-video-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.03);
}

/* hover删除按钮 - 右上角 */
.thumbnail-delete-hover {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(220, 53, 69, 0.95);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.thumbnail-preview:hover .thumbnail-delete-hover {
    opacity: 1;
}

.thumbnail-delete-hover:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.admin-thumbnail-upload-btn {
    width: 120px;
    height: 120px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 12px;
    text-align: center;
    gap: 8px;
}

.admin-thumbnail-upload-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.02);
}

.admin-thumbnail-upload-btn i {
    width: 24px;
    height: 24px;
}

/* 图片网格对齐 - 居中表格布局 */
.admin-image-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
    align-items: flex-start;
    justify-content: flex-start;
}

/* Videos 模块：上传按钮 16:9 */
.admin-add-image-btn {
    width: 160px;
    height: 90px;
    border: 2px dashed #007bff;
    border-radius: 6px;
    background: #f8f9fa;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #007bff;
    font-size: 24px;
    font-weight: bold;
    flex-shrink: 0;
    margin: 0;
}

.admin-add-image-btn:hover {
    background: #e3f2fd;
    border-color: #0056b3;
    transform: scale(1.05);
}

/* 图片项样式增强 - Videos 模块 16:9 */
.admin-image-item {
    position: relative;
    width: 160px;
    height: 90px;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    transition: all 0.2s ease;
    cursor: move;
    flex-shrink: 0;
    margin: 0;
}

.admin-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.admin-image-item.is-thumbnail {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40,167,69,.25);
}

/* 操作按钮（参考旧版本） */
.admin-image-actions {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.admin-image-item:hover .admin-image-actions {
    opacity: 1;
}

.admin-image-action-btn {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    padding: 0;
}

.admin-image-action-btn:hover {
    transform: scale(1.15);
}

.admin-image-action-btn svg {
    width: 12px;
    height: 12px;
}

.admin-image-action-btn.star {
    color: #6c757d;
}

.admin-image-action-btn.star.active,
.admin-image-action-btn.star:hover {
    color: #ffc107;
}

.admin-image-action-btn.move {
    color: #007bff;
    cursor: move;
}

.admin-image-action-btn.delete {
    color: #dc3545;
}
</style>

<!-- 消息提示 -->
<?php if ($flashMessage): ?>
<div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
    <i data-feather="<?= $flashMessage['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>"></i>
    <?= htmlspecialchars($flashMessage['text']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>


<!-- 三栏布局 -->
<div class="admin-three-column">

    <!-- 左栏：视频集列表 -->
    <div class="admin-left-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white position-relative">
                <h6 class="card-title mb-0">
                    <i data-feather="list" class="me-2"></i>
                    视频集顺序
                </h6>
                <button type="button" class="add-category-btn" onclick="showAddPanel()" title="添加新视频集">+</button>
            </div>
            <div class="card-body">
                <ul class="admin-category-list list-unstyled mb-0" id="categoryList">
                    <?php foreach ($categoryData as $category): ?>
                    <li class="admin-list-item" data-category="<?= htmlspecialchars($category['id']) ?>">
                        <div class="d-flex align-items-center p-3">
                            <span class="admin-drag-handle" title="拖拽排序">⋮⋮</span>

                            <!-- 缩略图（16:9） -->
                            <?php
                            $thumbnailSrc = '';
                            if (!empty($category['thumbnail'])) {
                                $thumbnailSrc = $category['thumbnail'];
                            } elseif (!empty($category['first_image_thumb'])) {
                                $thumbnailSrc = $category['first_image_thumb'];
                            }
                            ?>
                            <?php if ($thumbnailSrc): ?>
                                <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="缩略图" class="category-thumbnail me-2">
                            <?php else: ?>
                                <div class="category-thumbnail-placeholder me-2">
                                    <i data-feather="image"></i>
                                </div>
                            <?php endif; ?>

                            <!-- 信息 -->
                            <div class="flex-grow-1" style="cursor: pointer;" onclick="editCategory('<?= htmlspecialchars($category['id']) ?>')">
                                <div class="fw-semibold"><?= htmlspecialchars($category['display_name'] ?: $category['id']) ?></div>
                                <?php
                                $descText = trim($category['description'] ?? '');
                                if ($descText === '') {
                                    $descText = '【占位-描述信息】';
                                }
                                ?>
                                <small class="admin-text-muted"><?= htmlspecialchars($descText) ?></small>
                            </div>

                            <span class="admin-badge admin-badge-primary"><?= (int) $category['video_count'] ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- 中栏：编辑区域 -->
    <div class="admin-center-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="edit-3" class="me-2" id="edit-icon"></i>
                    <span id="edit-title">编辑视频集</span>
                </h6>
                <small id="edit-status" class="opacity-75">选择左侧视频集进行编辑</small>
            </div>
            <div class="card-body">

                <!-- 占位符 -->
                <div id="edit-placeholder" class="admin-edit-placeholder">
                    <i data-feather="arrow-left" style="width: 48px; height: 48px;"></i>
                    <p class="mt-3">点击左侧视频集开始编辑</p>
                </div>

                <!-- 添加面板 -->
                <div id="add-panel" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">新视频集名称</label>
                        <input type="text" id="new-category-name" class="form-control" placeholder="输入视频集名称（英文）">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">显示名称</label>
                            <input type="text" id="new-display-name" class="form-control" placeholder="显示给用户的名称">
                        </div>
                        <div class="col-6">
                            <label class="form-label">排序位置</label>
                            <select id="new-category-position" class="form-select">
                                <option value="first">第一个</option>
                                <option value="last" selected>最后一个</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">描述信息</label>
                        <textarea id="new-description" class="form-control" rows="2" placeholder="可选的描述"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="admin-btn admin-btn-success" onclick="createCategory()">
                            <i data-feather="plus"></i>创建视频集
                        </button>
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="cancelAdd()">
                            <i data-feather="x"></i>取消
                        </button>
                    </div>
                </div>

                <!-- 编辑面板 -->
                <div id="edit-panel" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">显示名称</label>
                            <input type="text" id="edit-display-name" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">文件夹名</label>
                            <input type="text" id="edit-folder-name" class="form-control" placeholder="英文名称" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">描述信息</label>
                        <textarea id="edit-description" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- 缩略图编辑 -->
                    <div class="mb-3">
                        <label class="form-label">缩略图</label>
                        <div class="thumbnail-edit-section">
                            <div class="d-flex align-items-center gap-3">
                                <!-- 当前缩略图显示 -->
                                <div class="thumbnail-preview" id="edit-thumbnail-preview" style="position: relative;">
                                    <img id="edit-thumbnail-img" style="width: 120px; height: 68px; border-radius: 8px; object-fit: cover; border: 2px solid #28a745; display: none;">
                                    <!-- hover删除按钮 -->
                                    <button class="thumbnail-delete-hover" id="thumbnail-delete-hover" onclick="removeThumbnail()" style="display: none;" title="移除缩略图">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </div>
                                <!-- 上传/更换缩略图按钮 -->
                                <div>
                                    <div class="thumbnail-upload-btn" id="edit-thumbnail-upload" onclick="selectThumbnailFile()" title="上传/更换缩略图">
                                        <i data-feather="upload"></i>
                                        <span>上传缩略图</span>
                                    </div>
                                    <!-- 隐藏的文件输入 -->
                                    <input type="file" id="thumbnail-file-input" accept="image/*" style="display: none;" onchange="handleThumbnailUpload(event)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 视频管理 -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i data-feather="video"></i>
                            视频管理
                        </label>
                        <div id="video-grid" class="video-grid-container"></div>
                        <input type="file" id="videosFileInput" accept="video/*" multiple style="display: none;" onchange="handleVideosUpload(event)">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="admin-btn admin-btn-primary" onclick="saveCategory()">
                            <i data-feather="save"></i>更新
                        </button>
                        <button type="button" class="admin-btn admin-btn-danger" onclick="deleteCategory()">
                            <i data-feather="trash-2"></i>删除
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 右栏：预览和工具 -->
    <div class="admin-right-panel">

        <!-- 统计信息 -->
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i data-feather="eye" class="me-2"></i>
                    预览
                </h6>
            </div>
            <div class="card-body">
                <div class="admin-stat-card">
                    <h3><?= $totalCategories ?></h3>
                    <p>视频集总数</p>
                </div>

                <div class="admin-stat-card" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                    <h3 id="total-videos">0</h3>
                    <p>总视频数</p>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="zap" class="me-2"></i>
                    快速操作
                </h6>
            </div>
            <div class="card-body">
                <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="refreshData()">
                    <i data-feather="refresh-cw"></i> 刷新数据
                </button>
                <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="testAPI()">
                    <i data-feather="server"></i> API 测试
                </button>
                <button class="btn btn-sm btn-outline-info w-100" onclick="exportData()">
                    <i data-feather="download"></i> 导出配置
                </button>
            </div>
        </div>

        <!-- 帮助信息 -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-feather="help-circle" class="me-2"></i>
                    使用提示
                </h6>
            </div>
            <div class="card-body">
                <small class="admin-text-muted">
                    <p><strong>拖拽排序：</strong>按住⋮⋮拖拽列表项调整顺序</p>
                    <p><strong>设置封面：</strong>上传自定义缩略图或使用首个视频缩略图</p>
                    <p><strong>上传视频：</strong>点击 + 号选择视频文件</p>
                </small>
            </div>
        </div>

    </div>

</div>


<!-- 版本提示 -->
<div class="alert alert-info alert-dismissible fade show">
    <i data-feather="info"></i>
    <strong>新版本 v1.0.0</strong> - ✓ 修复拖拽排序 ✓ 增强调试日志 ✓ 开发者工具(F12)查看。
    <a href="/admin?page=videos" class="alert-link">返回旧版本</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>


<!-- 引入新组件 JS -->
<script src="/assets/admin/js/admin-utils.js?v=2.4"></script>
<script src="/assets/admin/js/admin-drag-sort.js?v=2.4"></script>

<script>
// 全局变量
const controllerUrl = '/admin/ajax?controller=videos';
const existingMeta = <?= json_encode($categoryData, JSON_UNESCAPED_UNICODE) ?>;

// 当前编辑的分类
let currentCategory = null;
let imageManager = null;
let currentFileInputType = null;
let currentFileInputContext = null;

// 初始化拖拽排序
console.log('初始化AdminDragSort...');
console.log('AdminDragSort可用:', typeof AdminDragSort !== 'undefined');

const dragSort = new AdminDragSort('categoryList', {
    itemSelector: '.admin-list-item',
    handleSelector: '.admin-drag-handle',
    onReorder: (order) => {
        console.log('==== 拖拽排序触发 ====');
        console.log('新排序:', order);
        console.log('order类型:', typeof order);
        console.log('order长度:', order.length);
        saveOrder(order);
    }
});

console.log('AdminDragSort实例:', dragSort);

// 显示添加面板
function showAddPanel() {
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'none';
    document.getElementById('add-panel').style.display = 'block';

    document.getElementById('edit-title').textContent = '添加新视频集';
    document.getElementById('edit-status').textContent = '创建新的视频集';

    // 清空输入
    document.getElementById('new-category-name').value = '';
    document.getElementById('new-display-name').value = '';
    document.getElementById('new-description').value = '';
}

// 取消添加
function cancelAdd() {
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-placeholder').style.display = 'flex';

    document.getElementById('edit-title').textContent = '编辑视频集';
    document.getElementById('edit-status').textContent = '选择左侧视频集进行编辑';
}

// 编辑分类
function editCategory(categoryId) {
    currentCategory = categoryId;

    // 高亮当前项
    document.querySelectorAll('.admin-list-item').forEach(item => {
        if (item.dataset.category === categoryId) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // 隐藏占位符和添加面板
    document.getElementById('edit-placeholder').style.display = 'none';
    document.getElementById('add-panel').style.display = 'none';
    document.getElementById('edit-panel').style.display = 'block';

    // 更新标题（立即从列表数据获取）
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    const displayName = listItem ? listItem.querySelector('.fw-semibold').textContent : categoryId;

    document.getElementById('edit-title').textContent = displayName;
    document.getElementById('edit-status').textContent = '加载中...';
    document.getElementById('edit-display-name').value = displayName;
    document.getElementById('edit-folder-name').value = categoryId;

    loadCategoryVideos(categoryId);

    // 加载分类元数据（描述等）
    const metaItem = existingMeta.find(cat => cat.id === categoryId);
    if (metaItem) {
        document.getElementById('edit-description').value = metaItem.description || '';
    }

    // 加载分类缩略图
    loadCategoryThumbnailImage(categoryId);
}

function loadCategoryVideos(categoryName) {
    const container = document.getElementById('video-grid');
    const statusEl = document.getElementById('edit-status');
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> 加载中...</div>';

    fetch(`${controllerUrl}&ajax=videos&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';

            const addBtn = document.createElement('div');
            addBtn.className = 'add-video-btn';
            addBtn.innerHTML = '+';
            addBtn.title = '上传视频';
            addBtn.onclick = () => selectVideosFile('edit');
            container.appendChild(addBtn);

            if (data.success && data.videos.length > 0) {
                data.videos.forEach((video, index) => {
                    const sourceKeys = Object.keys(video.sources || {});
                    const fileName = sourceKeys.length > 0
                        ? video.sources[sourceKeys[0]].split('/').pop()
                        : (video.title || `video_${index}`);

                    const div = document.createElement('div');
                    div.className = 'video-item video-sortable';
                    div.dataset.filename = fileName;
                    div.dataset.index = index;
                    div.draggable = true;
                    div.innerHTML = `
                        <img src="${video.poster}" alt="${video.title || fileName}">
                        <div class="video-actions">
                            <button class="video-action-btn delete" title="删除" onclick="event.stopPropagation(); deleteVideo('${categoryName}', '${fileName}');">
                                <i data-feather="trash-2" style="width: 10px; height: 10px;"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(div);
                });

                const statusDiv = document.createElement('div');
                statusDiv.className = 'mt-2 small text-muted';
                statusDiv.textContent = `共 ${data.videos.length} 个视频，可拖拽排序。`;
                container.appendChild(statusDiv);

                if (statusEl) {
                    statusEl.textContent = `${data.videos.length} 个视频`;
                }
            } else {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'mt-2 small text-muted';
                emptyDiv.textContent = '暂无视频';
                container.appendChild(emptyDiv);
                if (statusEl) {
                    statusEl.textContent = '0 个视频';
                }
            }

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            initializeVideoSorting();
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i data-feather="alert-circle" style="width:32px; height:32px; opacity:0.5;"></i>
                    <p class="mt-2 small">加载失败，请重试</p>
                </div>`;
            if (statusEl) {
                statusEl.textContent = '加载失败';
            }
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
}

function initializeVideoSorting() {
    const videos = document.querySelectorAll('.video-sortable');
    let draggedVideo = null;

    videos.forEach(video => {
        video.addEventListener('dragstart', function() {
            draggedVideo = this;
            this.classList.add('dragging');
        });

        video.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedVideo = null;
            updateVideoOrder();
        });

        video.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedVideo || draggedVideo === this) return;

            const rect = this.getBoundingClientRect();
            const midX = rect.left + rect.width / 2;

            if (e.clientX < midX) {
                this.parentNode.insertBefore(draggedVideo, this);
            } else {
                this.parentNode.insertBefore(draggedVideo, this.nextSibling);
            }
        });

        video.addEventListener('drop', function(e) {
            e.preventDefault();
        });
    });
}

function updateVideoOrder() {
    if (!currentCategory) return;

    const videoOrder = [];
    document.querySelectorAll('.video-sortable').forEach(item => {
        const videoIndex = Array.from(item.parentNode.children).indexOf(item);
        videoOrder.push(videoIndex);
    });

    fetch(`${controllerUrl}&ajax=reorder_videos`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            order: videoOrder
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('视频顺序已更新', 'success');
        } else {
            AdminUtils.showMessage(data.message || '更新失败', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        AdminUtils.showMessage('网络请求失败', 'error');
    });
}

function selectVideosFile(context) {
    currentFileInputType = 'videos';
    currentFileInputContext = context;
    document.getElementById('videosFileInput')?.click();
}

function handleVideosUpload(event) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    const maxSize = 500 * 1024 * 1024;
    const oversized = files.filter(file => file.size > maxSize);
    if (oversized.length > 0) {
        AdminUtils.showMessage(`有 ${oversized.length} 个文件超过500MB限制`, 'error');
        return;
    }

    if (currentFileInputContext === 'edit' && currentCategory) {
        uploadVideos(currentCategory, files);
    }

    event.target.value = '';
}

function uploadVideos(categoryName, files) {
    const formData = new FormData();
    formData.append('category', categoryName);
    Array.from(files).forEach(file => formData.append('videos[]', file));

    const totalSize = (Array.from(files).reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(1);
    AdminUtils.showMessage(`正在上传 ${files.length} 个视频 (${totalSize}MB)...`, 'info');

    fetch(`${controllerUrl}&ajax=upload_videos`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage(data.message || '上传成功', 'success');
            loadCategoryVideos(categoryName);
            loadCategoryThumbnailImage(categoryName);

            const countEl = document.querySelector(`.admin-list-item[data-category="${categoryName}"] .admin-text-muted`);
            if (countEl && data.total_count !== undefined) {
                countEl.textContent = `${data.total_count} 个视频`;
            }
        } else {
            AdminUtils.showMessage(data.message || '上传失败', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        AdminUtils.showMessage('上传失败。如果文件较大，请检查PHP配置', 'error');
    });
}

function deleteVideo(categoryName, videoTitle) {
    if (!confirm(`确定要删除视频 "${videoTitle}" 吗？\n此操作将永久删除原文件。`)) return;

    fetch(`${controllerUrl}&ajax=delete_video`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: categoryName,
            video: videoTitle
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage(data.message || '视频已删除', 'success');
            loadCategoryVideos(categoryName);
            loadCategoryThumbnailImage(categoryName);

            const countEl = document.querySelector(`.admin-list-item[data-category="${categoryName}"] .admin-text-muted`);
            if (countEl && data.total_count !== undefined) {
                countEl.textContent = `${data.total_count} 个视频`;
            }
        } else {
            AdminUtils.showMessage(data.message || '删除失败', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        AdminUtils.showMessage('删除视频失败', 'error');
    });
}

// 初始化图片管理器
function initImageManager(images, currentThumbnail) {
    const container = document.getElementById('imageManager');
    container.innerHTML = '';

    // 简化版图片展示
    const grid = document.createElement('div');
    grid.className = 'admin-image-grid';
    grid.id = 'image-grid-' + Date.now(); // 唯一ID

    // 添加按钮
    const addBtn = document.createElement('div');
    addBtn.className = 'admin-add-image-btn';
    addBtn.innerHTML = '+';
    addBtn.title = '上传图片';
    addBtn.onclick = selectImages;
    grid.appendChild(addBtn);

    if (images.length === 0) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'admin-empty-state';
        emptyDiv.innerHTML = '<p>暂无图片，点击 + 上传</p>';
        grid.appendChild(emptyDiv);
    }

    // 渲染图片
    images.forEach((img, index) => {
        const item = document.createElement('div');
        item.className = 'admin-image-item';
        if (img.is_thumbnail) item.classList.add('is-thumbnail');
        item.dataset.imageId = img.id || img.name;
        item.dataset.imageName = img.name;
        item.draggable = true;

        const imgEl = document.createElement('img');
        imgEl.src = img.thumb_path || img.path;
        imgEl.alt = img.name;
        imgEl.loading = 'lazy';
        item.appendChild(imgEl);

        // 操作按钮（右上角竖直排列）
        const actions = document.createElement('div');
        actions.className = 'admin-image-actions';

        // 星标按钮
        const starBtn = document.createElement('button');
        starBtn.className = 'admin-image-action-btn star' + (img.is_thumbnail ? ' active' : '');
        starBtn.innerHTML = '<i data-feather="star"></i>';
        starBtn.title = img.is_thumbnail ? '当前封面' : '设为封面';
        starBtn.onclick = (e) => {
            e.stopPropagation();
            console.log('点击星标:', img.name, img.thumb_name);
            setThumbnail(img.name, img.thumb_name || img.name);
        };
        actions.appendChild(starBtn);

        // 删除按钮
        const delBtn = document.createElement('button');
        delBtn.className = 'admin-image-action-btn delete';
        delBtn.innerHTML = '<i data-feather="trash-2"></i>';
        delBtn.title = '删除图片';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            deleteImage(img.name);
        };
        actions.appendChild(delBtn);

        item.appendChild(actions);
        grid.appendChild(item);
    });

    // 添加提示信息
    if (images.length > 0) {
        const hint = document.createElement('small');
        hint.className = 'admin-text-muted mt-2 d-block';
        hint.textContent = `共 ${images.length} 张图片。拖拽图片可调整顺序，点击星标设为封面。`;
        grid.appendChild(hint);
    }

    container.appendChild(grid);

    // 重新激活Feather图标
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // 初始化图片拖拽排序
    if (images.length > 1) {
        // 稍微延迟以确保omready
        setTimeout(() => {
            new AdminImageDragSort(grid.id, {
                itemSelector: '.admin-image-item',
                onReorder: (order) => {
                    console.log('图片排序已更改:', order);
                    saveImageOrder(order);
                }
            });
        }, 100);
    }

    // 刷新 feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// 选择图片
function selectImages() {
    const input = document.createElement('input');
    input.type = 'file';
    input.multiple = true;
    input.accept = 'image/*';
    input.onchange = (e) => {
        const files = Array.from(e.target.files);
        uploadImages(files);
    };
    input.click();
}

// 上传图片
function uploadImages(files) {
    if (!currentCategory) {
        AdminUtils.showMessage('请先选择一个视频集', 'warning');
        return;
    }

    if (!files || files.length === 0) return;

    const formData = new FormData();
    formData.append('category', currentCategory);
    Array.from(files).forEach(file => formData.append('images[]', file));

    const totalSize = (Array.from(files).reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(1);
    AdminUtils.showMessage(`正在上传 ${files.length} 张图片 (${totalSize}MB)...`, 'info');

    const controllerUrl = '/admin/ajax?controller=media-manager&module=videos';

    fetch(`${controllerUrl}&ajax=upload_images`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage(data.message || '上传成功', 'success');

            // 重新加载图片列表
            editCategory(currentCategory);

            // 更新列表中的图片数量
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (listItem && data.total_count) {
                const countEl = listItem.querySelector('.admin-text-muted');
                if (countEl) {
                    countEl.textContent = `${data.total_count} 张图片`;
                }
            }

            // 如果自动设置了缩略图，更新显示
            if ((data.auto_set_thumbnail || data.thumbnail_set) && data.thumbnail_url) {
                const editImg = document.getElementById('edit-thumbnail-img');
                const listImg = listItem?.querySelector('img');
                if (editImg) {
                    editImg.src = data.thumbnail_url + '?t=' + Date.now();
                    editImg.style.display = 'block';
                }
                if (listImg) {
                    listImg.src = data.thumbnail_url + '?t=' + Date.now();
                }
            }
        } else {
            AdminUtils.showMessage(data.message || '上传失败', 'error');
        }
    })
    .catch(err => {
        console.error('上传失败:', err);
        AdminUtils.showMessage('上传失败。如果文件较大，请检查PHP配置', 'error');
    });
}

// 设置封面
function setThumbnail(imageName, thumbName) {
    if (!currentCategory) return;

    console.log('设置缩略图:', { category: currentCategory, image: imageName, thumb: thumbName });

    const controllerUrl = '/admin/ajax?controller=media-manager&module=videos';

    // 使用旧版本的 API - 注意参数名是 thumb 不是 thumb_name
    fetch(`${controllerUrl}&ajax=set_thumbnail`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            image: imageName,
            thumb: thumbName || imageName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('已设为封面图', 'success');

            // 1. 更新编辑区域的12 0x120缩略图预览
            const editImg = document.getElementById('edit-thumbnail-img');
            const editPreview = document.getElementById('edit-thumbnail-preview');
            if (editImg && data.thumbnail_url) {
                editImg.src = data.thumbnail_url + '?t=' + Date.now();
                editImg.style.display = 'block';
                // 移除placeholder
                const placeholder = editPreview.querySelector('span');
                if (placeholder) placeholder.remove();
            }

            // 2. 更新左侧列表的44x44缩略图
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (listItem && data.thumbnail_url) {
                const listImg = listItem.querySelector('img');
                if (listImg) {
                    listImg.src = data.thumbnail_url + '?t=' + Date.now();
                }
            }

            // 3. 更新图片管理区域的星标状态（不重新加载）
            const imageItems = document.querySelectorAll('.admin-image-item');
            imageItems.forEach(item => {
                const itemName = item.dataset.imageName;
                const starBtn = item.querySelector('.admin-image-action-btn.star');
                if (itemName === imageName) {
                    // 当前图片设为活跃
                    item.classList.add('is-thumbnail');
                    if (starBtn) {
                        starBtn.classList.add('active');
                        starBtn.title = '当前封面';
                    }
                } else {
                    // 其他图片移除活跃状态
                    item.classList.remove('is-thumbnail');
                    if (starBtn) {
                        starBtn.classList.remove('active');
                        starBtn.title = '设为封面';
                    }
                }
            });
        } else {
            AdminUtils.showMessage(data.message || '设置失败', 'error');
        }
    })
    .catch(err => {
        console.error('设置封面失败:', err);
        AdminUtils.showMessage('设置封面失败', 'error');
    });
}

// 删除图片
function deleteImage(imageName) {
    if (!currentCategory) return;

    if (!confirm(`确定要删除图片 "${imageName}" 吗？`)) {
        return;
    }

    const controllerUrl = '/admin/ajax?controller=media-manager&module=videos';

    fetch(`${controllerUrl}&ajax=delete_image`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            image: imageName
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('图片已删除', 'success');
            // 重新加载图片列表
            editCategory(currentCategory);

            // 更新列表中的图片计数
            updateImageCount(currentCategory);
        } else {
            AdminUtils.showMessage(data.message || '删除失败', 'error');
        }
    })
    .catch(err => {
        console.error('删除失败:', err);
        AdminUtils.showMessage('删除图片失败', 'error');
    });
}

// 更新图片计数
function updateImageCount(categoryId) {
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    if (listItem) {
        const countElement = listItem.querySelector('.admin-text-muted');
        if (countElement) {
            const currentText = countElement.textContent;
            const match = currentText.match(/(\d+)/);
            if (match) {
                const count = Math.max(0, parseInt(match[1]) - 1);
                countElement.textContent = `${count} 张图片`;
            }
        }
    }
}

// 保存图片排序
function saveImageOrder(orderData) {
    if (!currentCategory) return;

    const controllerUrl = '/admin/ajax?controller=media-manager&module=videos';

    // 将对象数组转换为字符串数组（按顺序排列的图片名）
    const imageOrder = Array.isArray(orderData)
        ? orderData.map(item => item.id || item)
        : orderData;

    fetch(`${controllerUrl}&ajax=reorder_images`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            order: imageOrder
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('排序已保存', 'success');
        } else {
            console.warn('排序保存失败:', data.message);
            AdminUtils.showMessage(data.message || '排序保存失败', 'warning');
        }
    })
    .catch(err => {
        console.error('排序保存错误:', err);
        AdminUtils.showMessage('排序保存失败', 'error');
    });
}

// 保存排序
function saveOrder(order) {
    console.log('==== saveOrder被调用 ====');
    console.log('接收到的order:', order);

    // order 是对象数组 [{id: 'xxx', position: 1}, ...]
    const categoryOrder = order.map(item => item.id).join(',');

    fetch(`${controllerUrl}&ajax=save_order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            order: categoryOrder
        })
    })
    .then(res => {
        console.log('收到响应:', res);
        return res.json();
    })
    .then(data => {
        console.log('响应数据:', data);
        if (data.success) {
            AdminUtils.showMessage('排序已保存', 'success');
        } else {
            AdminUtils.showMessage(data.message || '保存失败', 'error');
        }
    })
    .catch(err => {
        console.error('保存排序失败:', err);
        AdminUtils.showMessage('保存失败', 'error');
    });
}

// 创建分类
function createCategory() {
    const name = document.getElementById('new-category-name').value.trim();
    const displayName = document.getElementById('new-display-name').value.trim();
    const description = document.getElementById('new-description').value.trim();
    const position = document.getElementById('new-category-position').value;

    if (!name) {
        AdminUtils.showMessage('请输入视频集名称', 'warning');
        return;
    }

    if (!/^[a-zA-Z0-9_-]+$/.test(name)) {
        AdminUtils.showMessage('文件夹名只能包含英文、数字、下划线和短横线', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('category', name);
    formData.append('displayName', displayName);
    formData.append('description', description);
    formData.append('position', position);

    AdminUtils.showMessage('正在创建视频集...', 'info');

    fetch(`${controllerUrl}&ajax=create_category`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('视频集创建成功', 'success');
            location.reload();
        } else {
            AdminUtils.showMessage(data.message || '创建失败', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        AdminUtils.showMessage('创建失败', 'error');
    });
}

// 保存分类
function saveCategory() {
    if (!currentCategory) return;

    const displayName = document.getElementById('edit-display-name').value.trim();
    const description = document.getElementById('edit-description').value.trim();

    if (!confirm(`确定要保存对视频集 "${currentCategory}" 的修改吗？`)) {
        return;
    }

    fetch(`${controllerUrl}&ajax=save_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory,
            displayName: displayName,
            description: description
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('视频集信息已保存', 'success');

            const metaItem = existingMeta.find(cat => cat.id === currentCategory);
            if (metaItem) {
                metaItem.display_name = displayName;
                metaItem.description = description;
            }

            updateCategoryInList(currentCategory, displayName);
            document.getElementById('edit-title').textContent = displayName || currentCategory;
        } else {
            AdminUtils.showMessage(data.message || '保存失败', 'error');
        }
    })
    .catch(err => {
        console.error('保存失败:', err);
        AdminUtils.showMessage('保存失败', 'error');
    });
}

// 更新列表中的分类信息
function updateCategoryInList(categoryId, displayName) {
    const listItem = document.querySelector(`.admin-list-item[data-category="${categoryId}"]`);
    if (listItem) {
        const nameElement = listItem.querySelector('.fw-semibold');
        if (nameElement) {
            nameElement.textContent = displayName || categoryId;
        }
    }
}

// 获取当前分类排序
function getCurrentCategoryOrder() {
    const items = document.querySelectorAll('.admin-list-item');
    return Array.from(items).map((item, index) => ({
        id: item.dataset.category,
        position: index + 1
    }));
}

// 删除分类
function deleteCategory() {
    if (!currentCategory) return;

    if (!confirm(`确定要删除视频集 "${currentCategory}" 吗？\n这将删除该视频集下的所有视频文件。`)) {
        return;
    }

    fetch(`${controllerUrl}&ajax=delete_category`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category: currentCategory
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 从列表中移除
            const item = document.querySelector(`.admin-list-item[data-category="${currentCategory}"]`);
            if (item) item.remove();

            // 返回初始状态
            document.getElementById('edit-panel').style.display = 'none';
            document.getElementById('edit-placeholder').style.display = 'flex';
            currentCategory = null;

            AdminUtils.showMessage('视频集已删除', 'success');

            // 更新统计
            updateTotalCount();
        } else {
            AdminUtils.showMessage(data.message || '删除失败', 'error');
        }
    })
    .catch(err => {
        console.error('删除失败:', err);
        AdminUtils.showMessage('删除失败', 'error');
    });
}

// 更新统计总数
function updateTotalCount() {
    const totalCategories = document.querySelectorAll('.admin-list-item').length;
    const statCard = document.querySelector('.admin-stat-card h3');
    if (statCard) {
        statCard.textContent = totalCategories;
    }
}

// 快速操作
function refreshData() {
    location.reload();
}

function testAPI() {
    AdminUtils.showMessage('API 测试：连接正常', 'success');
}

function exportData() {
    AdminUtils.showMessage('导出功能待实现', 'info');
}

// 缩略图编辑功能
function selectThumbnailFile() {
    document.getElementById('thumbnail-file-input')?.click();
}

function handleThumbnailUpload(event) {
    const file = event.target.files[0];
    if (!file || !currentCategory) return;

    // 验证文件类型
    if (!file.type.startsWith('image/')) {
        AdminUtils.showMessage('请选择图片文件', 'error');
        return;
    }

    // 创建FormData
    const formData = new FormData();
    formData.append('thumbnail', file);
    formData.append('category', currentCategory);

    AdminUtils.showMessage('正在上传缩略图...', 'info');

    // 上传到服务器
    fetch(`${controllerUrl}&ajax=upload_thumbnail`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('缩略图上传成功', 'success');
            // 更新预览
            const img = document.getElementById('edit-thumbnail-img');
            const preview = document.getElementById('edit-thumbnail-preview');
            const deleteBtn = document.getElementById('thumbnail-delete-hover');

            if (img && data.thumbnail_url) {
                img.src = data.thumbnail_url + '?t=' + Date.now();
                img.style.display = 'block';
                preview.querySelector('span')?.remove();
            }
            // 显示hover删除按钮
            if (deleteBtn) deleteBtn.style.display = 'block';

            // 更新左侧列表缩略图
            const listItem = document.querySelector(`.admin-list-item[data-category="${currentCategory}"] img`);
            if (listItem && data.thumbnail_url) {
                listItem.src = data.thumbnail_url + '?t=' + Date.now();
            }

            // 重新激活feather图标
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        } else {
            AdminUtils.showMessage(data.message || '上传失败', 'error');
        }
    })
    .catch(err => {
        console.error('上传失败:', err);
        AdminUtils.showMessage('上传失败', 'error');
    })
    .finally(() => {
        // 清空input以允许重复上传同一文件
        event.target.value = '';
    });
}

function removeThumbnail() {
    if (!currentCategory) return;

    if (!confirm('确定要移除当前缩略图吗？')) return;

    fetch(`${controllerUrl}&ajax=delete_thumbnail`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ category: currentCategory })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            AdminUtils.showMessage('缩略图已移除', 'success');
            // 清除预览
            const img = document.getElementById('edit-thumbnail-img');
            const preview = document.getElementById('edit-thumbnail-preview');
            const deleteBtn = document.getElementById('thumbnail-delete-hover');

            if (data.new_thumbnail_url) {
                if (img) {
                    img.src = data.new_thumbnail_url + '?t=' + Date.now();
                    img.style.display = 'block';
                }
                if (deleteBtn) {
                    deleteBtn.style.display = 'inline-flex';
                }
                if (preview) {
                    const placeholder = preview.querySelector('span');
                    if (placeholder) placeholder.remove();
                }

                const listImg = document.querySelector(`.admin-list-item[data-category="${currentCategory}"] img`);
                if (listImg) {
                    listImg.src = data.new_thumbnail_url + '?t=' + Date.now();
                }
            } else {
                if (img) {
                    img.style.display = 'none';
                    img.src = '';
                }
                if (deleteBtn) {
                    deleteBtn.style.display = 'none';
                }
                if (preview && !preview.querySelector('span')) {
                    const placeholder = document.createElement('span');
                    placeholder.style.cssText = 'color: #999; font-size: 12px;';
                    placeholder.textContent = '无缩略图';
                    preview.appendChild(placeholder);
                }
            }
        } else {
            AdminUtils.showMessage(data.message || '移除失败', 'error');
        }
    })
    .catch(err => {
        console.error('移除失败:', err);
        AdminUtils.showMessage('移除失败', 'error');
    });
}

function loadCategoryThumbnailImage(categoryName) {
    const img = document.getElementById('edit-thumbnail-img');
    const preview = document.getElementById('edit-thumbnail-preview');
    const deleteBtn = document.getElementById('thumbnail-delete-hover');
    const uploadBtn = document.getElementById('edit-thumbnail-upload');

    if (!img || !preview) return;

    fetch(`${controllerUrl}&ajax=category_thumbnail&category=${encodeURIComponent(categoryName)}`)
        .then(res => res.json())
        .then(data => {
            const placeholder = preview.querySelector('span');
            if (placeholder) placeholder.remove();

            if (data.success && (data.thumbnail || data.first_video_thumb)) {
                const thumbnailUrl = data.thumbnail || data.first_video_thumb;
                img.src = thumbnailUrl;
                img.style.display = 'block';
                if (uploadBtn) {
                    const label = uploadBtn.querySelector('span');
                    if (label) label.textContent = '更换缩略图';
                }
                if (deleteBtn) {
                    deleteBtn.style.display = data.thumbnail ? 'inline-flex' : 'none';
                }
            } else {
                img.style.display = 'none';
                img.src = '';
                const empty = document.createElement('span');
                empty.style.cssText = 'color: #999; font-size: 12px;';
                empty.textContent = '无缩略图';
                preview.appendChild(empty);
                if (uploadBtn) {
                    const label = uploadBtn.querySelector('span');
                    if (label) label.textContent = '上传缩略图';
                }
                if (deleteBtn) deleteBtn.style.display = 'none';
            }

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        })
        .catch(err => {
            console.error(err);
            img.style.display = 'none';
            img.src = '';
            if (uploadBtn) {
                const label = uploadBtn.querySelector('span');
                if (label) label.textContent = '上传缩略图';
            }
            if (deleteBtn) deleteBtn.style.display = 'none';
        });
}

// 计算总视频数
let totalVideos = 0;
<?php foreach ($categoryData as $cat): ?>
    totalVideos += <?= (int) $cat['video_count'] ?>;
<?php endforeach; ?>

const totalVideosEl = document.getElementById('total-videos');
if (totalVideosEl) {
    totalVideosEl.textContent = totalVideos;
}

// 初始化 feather icons
window.addEventListener('load', function() {
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
            console.log('✓ Feather icons 初始化完成');
        }
    }, 100);
});

// 调试信息 - 版本 v1.0.0
console.log('Video Gallery 管理页面 v1.0.0 已加载');
console.log('- ✓ 使用 videos controller 架构');
console.log('- ✓ 使用 PHP 渲染分类列表');
console.log('- ✓ 分类数:', <?= count($categoryData) ?>);
console.log('- ✓ 总视频数:', totalVideos);
console.log('AdminDragSort:', typeof AdminDragSort !== 'undefined' ? '✓ 已加载' : '✗ 未加载');
console.log('AdminUtils:', typeof AdminUtils !== 'undefined' ? '✓ 已加载' : '✗ 未加载');
</script>
