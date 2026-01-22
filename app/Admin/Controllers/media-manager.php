<?php
/**
 * 通用媒体管理控制器 (重构版)
 *
 * 职责：
 * - AJAX 请求路由
 * - 调用对应的 Service 层
 * - 返回 JSON 响应
 *
 * 支持模块：sketchbook, galleries, drafts, videos, template, comics
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../bootstrap.php';

// 自动加载 Services
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\Admin\\Services\\Media\\') === 0) {
        $classPath = str_replace('App\\Admin\\Services\\Media\\', '', $class);
        $file = __DIR__ . '/../Services/Media/' . $classPath . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Admin\Services\Media\ImageService;
use App\Admin\Services\Media\VideoService;
use App\Admin\Services\Media\ConfigService;
use App\Admin\Services\Media\CategoryService;

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 获取参数
$module = $_GET['module'] ?? '';
$action = $_GET['ajax'] ?? '';

// 验证模块
$allowedModules = ['sketchbook', 'galleries', 'drafts', 'videos', 'template', 'comics'];
if (!in_array($module, $allowedModules)) {
    respondJson(['success' => false, 'error' => '无效的模块名称'], 400);
    exit;
}

// 设置路径（videos 模块使用特殊路径）
if ($module === 'videos') {
    $baseDir = __DIR__ . '/../../../public/assets/videos/video-gallery';
    $configPath = __DIR__ . '/../../storage/data/video-gallery-sort.json';
} else {
    $baseDir = __DIR__ . '/../../../public/assets/images/' . $module;
    $configPath = __DIR__ . '/../../storage/data/' . $module . '-sort.json';
}
$orderPath = __DIR__ . '/../../storage/data/image-orders.json';

// 初始化服务
$imageService = new ImageService();
$videoService = new VideoService();
$configService = new ConfigService();
$categoryService = new CategoryService($imageService, $videoService, $configService);

// 处理 AJAX 请求
if ($action) {
    handleAjaxRequest($action, $module, $baseDir, $configPath, $orderPath);
    exit;
}

/**
 * AJAX 请求路由
 */
function handleAjaxRequest(string $action, string $module, string $baseDir, string $configPath, string $orderPath): void
{
    global $imageService, $videoService, $configService, $categoryService;

    header('Content-Type: application/json; charset=utf-8');

    try {
        switch ($action) {
            // ========== 分类操作 ==========
            case 'categories':
                $categories = $categoryService->getCategories($baseDir, $module, $configPath);
                respondJson([
                    'success' => true,
                    'categories' => $categories,
                    'groups' => $categories, // 兼容 videos 模块
                    'data' => $categories    // 兼容字段
                ]);
                break;

            case 'create_category':
                $input = json_decode(file_get_contents('php://input'), true);
                $categoryName = $input['category'] ?? $input['name'] ?? '';

                $result = $categoryService->createCategory($baseDir, $categoryName);
                respondJson(['success' => true, 'message' => '分类创建成功', 'category' => $result]);
                break;

            case 'delete_category':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';

                $categoryService->deleteCategory($baseDir, $category);
                respondJson(['success' => true, 'message' => '分类已删除']);
                break;

            case 'save_category':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';
                $displayName = $input['displayName'] ?? '';
                $description = $input['description'] ?? '';
                $newFolderName = $input['newFolderName'] ?? null;

                $renamed = false;
                $actualCategory = $category;

                // 处理重命名
                if ($newFolderName && $newFolderName !== $category) {
                    $categoryService->renameCategory($baseDir, $category, $newFolderName, $configPath);
                    $renamed = true;
                    $actualCategory = $newFolderName;
                }

                // 更新显示名称和描述
                if ($displayName) {
                    $configService->setDisplayName($configPath, $actualCategory, $displayName);
                }
                if ($description !== null) {
                    $configService->setDescription($configPath, $actualCategory, $description);
                }

                $response = ['success' => true, 'message' => '分类信息已更新'];
                if ($renamed) {
                    $response['renamed'] = true;
                    $response['newCategory'] = $actualCategory;
                }

                respondJson($response);
                break;

            case 'save_category_order':
            case 'save_order':
                $input = json_decode(file_get_contents('php://input'), true);
                $order = $input['order'] ?? $input['category_order'] ?? '';

                if (is_string($order)) {
                    $order = array_filter(array_map('trim', explode(',', $order)));
                }

                $configService->saveCategoryOrder($configPath, $order);
                respondJson(['success' => true, 'message' => '排序已保存', 'order' => $order]);
                break;

            // ========== 媒体文件操作 ==========
            case 'thumbnails':
            case 'videos':
                $category = $_GET['category'] ?? '';
                $media = $categoryService->getCategoryMedia($baseDir, $module, $category, $orderPath, $configPath);

                respondJson([
                    'success' => true,
                    'images' => $media,
                    'videos' => $media,  // 兼容字段
                    'category' => $category
                ]);
                break;

            case 'upload_images':
            case 'upload_videos':
                $category = $_POST['category'] ?? '';
                $categoryDir = $baseDir . '/' . $category;

                if (!is_dir($categoryDir)) {
                    throw new InvalidArgumentException('分类不存在');
                }

                // 判断上传类型
                $isVideo = ($action === 'upload_videos' || !empty($_FILES['videos']));
                $files = $_FILES[$isVideo ? 'videos' : 'images'] ?? [];

                if (empty($files['name'])) {
                    throw new InvalidArgumentException('没有上传文件');
                }

                $results = $isVideo
                    ? $videoService->uploadVideos($categoryDir, $files)
                    : $imageService->uploadImages($categoryDir, $files);

                respondJson([
                    'success' => true,
                    'results' => $results,
                    'message' => '上传完成'
                ]);
                break;

            case 'delete_image':
            case 'delete_video':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';
                $filename = $input['image'] ?? $input['video'] ?? '';

                $categoryService->deleteMedia($baseDir, $module, $category, $filename, $orderPath);
                respondJson(['success' => true, 'message' => '文件已删除']);
                break;

            case 'reorder_images':
            case 'reorder_videos':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';
                $order = $input['order'] ?? [];

                $configService->saveCategoryMediaOrder($module, $category, $order, $orderPath);
                respondJson(['success' => true, 'message' => '排序已保存']);
                break;

            // ========== 缩略图操作 ==========
            case 'set_thumbnail':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';
                $thumbName = $input['thumb'] ?? $input['thumb_name'] ?? '';

                $thumbnailUrl = "/assets/images/{$module}/{$category}/thumbs/{$thumbName}";
                $configService->setCategoryThumbnail($configPath, $category, $thumbnailUrl);

                respondJson([
                    'success' => true,
                    'thumbnail_url' => $thumbnailUrl,
                    'message' => '封面已设置'
                ]);
                break;

            case 'upload_thumbnail':
                $category = $_POST['category'] ?? '';
                $categoryDir = $baseDir . '/' . $category;

                $result = $imageService->uploadCustomThumbnail($categoryDir, $_FILES['thumbnail']);
                $thumbnailUrl = "/assets/images/{$module}/{$category}/thumbs/{$result['filename']}";

                $configService->setCategoryThumbnail($configPath, $category, $thumbnailUrl);

                respondJson([
                    'success' => true,
                    'thumbnail_url' => $thumbnailUrl,
                    'message' => '缩略图已上传'
                ]);
                break;

            case 'delete_thumbnail':
                $input = json_decode(file_get_contents('php://input'), true);
                $category = $input['category'] ?? '';

                $config = $configService->loadConfig($configPath);
                $thumbnailUrl = $config['category_thumbnails'][$category] ?? null;

                if ($thumbnailUrl) {
                    $thumbnailPath = __DIR__ . '/../../../public' . $thumbnailUrl;
                    $imageService->deleteCustomThumbnail($thumbnailPath);
                    $configService->removeCategoryThumbnail($configPath, $category);
                }

                $categoryDir = $baseDir . '/' . $category;
                $newThumbnail = $categoryService->findCategoryThumbnail($categoryDir, $module, $category, $config);

                respondJson([
                    'success' => true,
                    'new_thumbnail_url' => $newThumbnail,
                    'message' => '缩略图已删除'
                ]);
                break;

            case 'category_thumbnail':
                $category = $_GET['category'] ?? '';
                $categoryDir = $baseDir . '/' . $category;
                $config = $configService->loadConfig($configPath);

                $customThumbnail = $config['category_thumbnails'][$category] ?? null;
                $firstImageThumb = null;

                if (is_dir($categoryDir)) {
                    $firstImageThumb = $categoryService->findCategoryThumbnail($categoryDir, $module, $category, $config);
                }

                respondJson([
                    'success' => true,
                    'thumbnail' => $customThumbnail,
                    'first_image_thumb' => $firstImageThumb
                ]);
                break;

            // ========== 系统信息 ==========
            case 'php_config':
                outputPhpUploadConfig();
                break;

            // ========== 兼容旧接口 ==========
            case 'scan_directory':
            case 'get_group_videos':
                // Videos 模块的遗留接口，重定向到 thumbnails
                $_GET['ajax'] = 'thumbnails';
                handleAjaxRequest('thumbnails', $module, $baseDir, $configPath, $orderPath);
                break;

            default:
                respondJson(['success' => false, 'error' => '未知的操作: ' . $action], 400);
        }

    } catch (Throwable $e) {
        respondJson([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => defined('DEBUG') && DEBUG ? $e->getTraceAsString() : null
        ], 400);
    }
}

/**
 * 输出 PHP 上传配置
 */
function outputPhpUploadConfig(): void
{
    $config = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'file_uploads' => ini_get('file_uploads') ? 'On' : 'Off',
    ];

    $uploadMaxBytes = parseSizeToBytes($config['upload_max_filesize']);
    $postMaxBytes = parseSizeToBytes($config['post_max_size']);

    $issues = [];
    if ($uploadMaxBytes < 50 * 1024 * 1024) {
        $issues[] = "upload_max_filesize ({$config['upload_max_filesize']}) 小于推荐的50MB";
    }
    if ($postMaxBytes < 60 * 1024 * 1024) {
        $issues[] = "post_max_size ({$config['post_max_size']}) 小于推荐的60MB";
    }

    respondJson([
        'config' => $config,
        'limits' => [
            'upload_max_bytes' => $uploadMaxBytes,
            'post_max_bytes' => $postMaxBytes,
        ],
        'issues' => $issues,
        'status' => empty($issues) ? 'ok' : 'warning',
    ]);
}

/**
 * 解析大小字符串为字节
 */
function parseSizeToBytes(string $value): int
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $value);
    $number = (float) preg_replace('/[^0-9\.]/', '', $value);

    if ($unit) {
        return (int) round($number * pow(1024, stripos('bkmgtpezy', $unit[0])));
    }

    return (int) round($number);
}

/**
 * 输出 JSON 响应
 */
function respondJson(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
