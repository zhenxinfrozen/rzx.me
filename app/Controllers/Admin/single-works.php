<?php
/**
 * Single-Works 管理控制器
 * 负责 single-works 页面分组排序、图片管理及相关 AJAX 操作
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ajaxAction = $_GET['ajax'] ?? ($_POST['ajax_action'] ?? null);
$configPath = __DIR__ . '/../../../app/Config/single_works_sort.php';
$imagesRoot = __DIR__ . '/../../../public/assets/images/single-works';
$trashRoot = __DIR__ . '/../../../public/assets/images/trash';
$imageOrderPath = __DIR__ . '/../../../app/storage/config/image-orders.json';

$galleryManager = new GalleryManager();

if ($ajaxAction) {
    handleSingleWorksAjax($ajaxAction, $configPath, $imagesRoot, $trashRoot, $imageOrderPath);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleSingleWorksFormSubmission($configPath, $imagesRoot, $trashRoot);
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './single-works.php'));
    exit;
}

$currentConfig = loadSingleWorksConfig($configPath);
$categories = $galleryManager->getGalleryCategories('single-works');
$orderedCategories = reorderCategories($categories, $currentConfig['custom_order'] ?? []);

$categoryData = [];
foreach ($orderedCategories as $index => $category) {
    $dirPath = $imagesRoot . '/' . $category;
    $imageCount = is_dir($dirPath)
        ? count(glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE))
        : 0;

    // 获取缩略图信息
    $thumbnailInfo = getCategoryThumbnailInfo($category, $dirPath);

    $categoryData[] = [
        'id' => $category,
        'display_name' => $currentConfig['display_names'][$category] ?? $category,
        'description' => $currentConfig['descriptions'][$category] ?? '',
        'image_count' => $imageCount,
        'position' => $index + 1,
        'thumbnail' => $thumbnailInfo['custom_thumbnail'],
        'first_image_thumb' => $thumbnailInfo['first_image_thumb']
    ];
}

$flashMessage = $_SESSION['single_works_flash'] ?? null;
unset($_SESSION['single_works_flash']);

$page_title = '🛠️ Single-Works 管理';
$page_subtitle = '管理 Single-Works 页面分组与图片';
$_GET['page'] = 'single-works';

// 控制器逻辑完成，返回给 AdminIndexController 渲染视图

/**
 * 处理表单提交
 */
function handleSingleWorksFormSubmission(string $configPath, string $imagesRoot, string $trashRoot): void
{
    try {
        $deleted = array_filter(array_map('trim', explode(',', $_POST['deleted_categories'] ?? '')));
        foreach ($deleted as $category) {
            moveCategoryToTrash($category, $imagesRoot, $trashRoot);
        }

        $config = [
            'sort_method' => $_POST['sort_method'] ?? 'custom_order',
            'custom_order' => [],
            'prefix_settings' => [
                'remove_prefix' => true,
                'separator' => '-',
            ],
            'display_names' => $_POST['display_names'] ?? [],
            'descriptions' => $_POST['descriptions'] ?? [],
        ];

        if (!empty($_POST['category_order'])) {
            $config['custom_order'] = array_values(array_filter(array_map('trim', explode(',', $_POST['category_order']))));
        }

        if (saveSingleWorksConfig($configPath, $config)) {
            $_SESSION['single_works_flash'] = ['type' => 'success', 'text' => '配置保存成功！'];
        } else {
            throw new RuntimeException('配置保存失败，请检查文件权限');
        }
    } catch (Throwable $e) {
        $_SESSION['single_works_flash'] = ['type' => 'error', 'text' => '保存失败：' . $e->getMessage()];
    }
}

/**
 * 处理 AJAX 请求
 */
function handleSingleWorksAjax(string $action, string $configPath, string $imagesRoot, string $trashRoot, string $imageOrderPath): void
{
    switch ($action) {
        case 'thumbnails':
            outputThumbnails($imagesRoot, $imageOrderPath);
            return;

        case 'upload_images':
            uploadCategoryImages($imagesRoot);
            return;

        case 'create_category':
            createCategory($configPath, $imagesRoot);
            return;

        case 'php_config':
            outputPhpUploadConfig();
            return;

        case 'category_thumbnail':
            outputCategoryThumbnail($imagesRoot);
            return;

        case 'set_thumbnail':
            setAsThumbnail($imagesRoot, $configPath);
            return;

        case 'delete_thumbnail':
            deleteThumbnail($imagesRoot, $configPath);
            return;

        case 'reorder_images':
            reorderCategoryImages($imagesRoot, $imageOrderPath);
            return;

        case 'delete_image':
            deleteCategoryImage($imagesRoot, $trashRoot, $imageOrderPath);
            return;

        case 'save_category':
            saveCategoryUpdate($configPath, $imagesRoot);
            return;

        case 'delete_category':
            deleteCategoryAndFiles($configPath, $imagesRoot, $trashRoot);
            return;

        default:
            respondJson(['success' => false, 'error' => '未知的操作'], 400);
            return;
    }
}

function loadSingleWorksConfig(string $configPath): array
{
    if (file_exists($configPath)) {
        $config = include $configPath;
        if (is_array($config)) {
            return $config;
        }
    }

    return [
        'sort_method' => 'custom_order',
        'custom_order' => [],
        'prefix_settings' => ['remove_prefix' => true, 'separator' => '-'],
        'display_names' => [],
        'descriptions' => [],
    ];
}

function saveSingleWorksConfig(string $configPath, array $config): bool
{
    $content = "<?php\n/**\n * Single-Works 分组排序配置\n * 自动生成于: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($config, true) . ";\n";
    return (bool) file_put_contents($configPath, $content, LOCK_EX);
}

function reorderCategories(array $categories, array $customOrder): array
{
    if (empty($customOrder)) {
        sort($categories);
        return $categories;
    }

    $ordered = [];
    foreach ($customOrder as $category) {
        if (in_array($category, $categories, true)) {
            $ordered[] = $category;
        }
    }

    foreach ($categories as $category) {
        if (!in_array($category, $ordered, true)) {
            $ordered[] = $category;
        }
    }

    return $ordered;
}

function moveCategoryToTrash(string $category, string $imagesRoot, string $trashRoot): void
{
    if ($category === '') {
        return;
    }

    $sourceDir = $imagesRoot . '/' . $category;
    if (!is_dir($sourceDir)) {
        return;
    }

    if (!is_dir($trashRoot)) {
        mkdir($trashRoot, 0755, true);
    }

    $targetDir = $trashRoot . '/' . $category . '-' . date('Y-m-d-H-i-s');
    if (!rename($sourceDir, $targetDir)) {
        throw new RuntimeException("无法移动分组 {$category} 至回收站");
    }
}

function outputThumbnails(string $imagesRoot, string $imageOrderPath): void
{
    header('Content-Type: application/json');

    try {
        $category = $_GET['category'] ?? '';
        if ($category === '') {
            throw new InvalidArgumentException('分组名称不能为空');
        }

        $category = basename($category);
        $categoryDir = $imagesRoot . '/' . $category;
        $thumbDir = $categoryDir . '/thumbs';

        if (!is_dir($categoryDir)) {
            throw new InvalidArgumentException('分组目录不存在');
        }

        $supported = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allImageFiles = [];
        $files = scandir($categoryDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, $supported, true)) {
                continue;
            }

            $allImageFiles[] = $file;
        }

        // 使用新的排序逻辑获取有序的图片列表
        $orderedFiles = getCategoryImageOrder($category, $imageOrderPath, $allImageFiles);

        $images = [];
        foreach ($orderedFiles as $file) {
            $filePath = $categoryDir . '/' . $file;
            $thumbPath = $thumbDir . '/' . $file;
            $thumbUrl = file_exists($thumbPath)
                ? "/assets/images/single-works/{$category}/thumbs/{$file}"
                : "/assets/images/single-works/{$category}/{$file}";

            $images[] = [
                'name' => $file,
                'path' => "/assets/images/single-works/{$category}/{$file}",
                'thumb_path' => $thumbUrl,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
            ];
        }

        // 获取当前缩略图信息
        $thumbnailInfo = getCategoryThumbnailInfo($category, $categoryDir);
        $currentThumbnail = null;
        if ($thumbnailInfo['custom_thumbnail']) {
            $currentThumbnail = basename($thumbnailInfo['custom_thumbnail']);
        }

        respondJson([
            'success' => true,
            'category' => $category,
            'images' => $images,
            'count' => count($images),
            'current_thumbnail' => $currentThumbnail,
        ]);
    } catch (Throwable $e) {
        respondJson([
            'success' => false,
            'error' => $e->getMessage(),
            'images' => [],
            'count' => 0,
        ], 400);
    }
}

function uploadCategoryImages(string $imagesRoot): void
{
    header('Content-Type: application/json');

    try {
        if (!isset($_POST['category'], $_FILES['images'])) {
            throw new InvalidArgumentException('缺少必要参数');
        }

        $category = $_POST['category'];
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
            throw new InvalidArgumentException('无效的分组名称');
        }

        $categoryDir = $imagesRoot . '/' . $category;
        $thumbDir = $categoryDir . '/thumbs';

        if (!is_dir($categoryDir)) {
            throw new InvalidArgumentException('分组目录不存在');
        }
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $files = $_FILES['images'];
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        $uploaded = 0;
        $errors = [];

        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];

            if ($fileError !== UPLOAD_ERR_OK) {
                $errors[] = "文件 {$fileName} 上传失败";
                continue;
            }

            if ($fileSize > 50 * 1024 * 1024) {
                $errors[] = "文件 {$fileName} 太大（超过50MB）";
                continue;
            }

            $imageInfo = @getimagesize($tmpName);
            if (!$imageInfo) {
                $errors[] = "文件 {$fileName} 不是有效的图片";
                continue;
            }

            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!in_array($imageInfo[2], $allowedTypes, true)) {
                $errors[] = "文件 {$fileName} 格式不支持";
                continue;
            }

            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $finalName = $safeName . '.' . $extension;
            $counter = 1;
            while (file_exists($categoryDir . '/' . $finalName)) {
                $finalName = $safeName . '_' . $counter . '.' . $extension;
                $counter++;
            }

            $targetPath = $categoryDir . '/' . $finalName;
            if (!move_uploaded_file($tmpName, $targetPath)) {
                $errors[] = "文件 {$fileName} 保存失败";
                continue;
            }

            if (!generateThumbnail($targetPath, $thumbDir . '/' . $finalName, 200, 200, $imageInfo[2])) {
                $errors[] = "文件 {$fileName} 缩略图生成失败";
                continue;
            }

            $uploaded++;
        }

        respondJson([
            'success' => $uploaded > 0,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'message' => $uploaded > 0 ? "成功上传 {$uploaded} 张图片" : '上传失败'
        ]);
    } catch (Throwable $e) {
        respondJson([
            'success' => false,
            'uploaded' => 0,
            'message' => $e->getMessage(),
        ], 400);
    }
}

function createCategory(string $configPath, string $imagesRoot): void
{
    header('Content-Type: application/json');

    try {
        $category = $_POST['category'] ?? '';
        $displayName = $_POST['displayName'] ?? $category;
        $description = $_POST['description'] ?? '';
        $position = $_POST['position'] ?? 'last';

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
            throw new InvalidArgumentException('分组名称只能包含字母、数字、下划线和连字符');
        }

        $categoryDir = $imagesRoot . '/' . $category;
        $thumbDir = $categoryDir . '/thumbs';

        if (is_dir($categoryDir)) {
            throw new InvalidArgumentException('分组已存在');
        }

        if (!mkdir($categoryDir, 0755, true)) {
            throw new RuntimeException('创建分组目录失败');
        }
        if (!mkdir($thumbDir, 0755, true)) {
            throw new RuntimeException('创建缩略图目录失败');
        }

        // 处理图片上传
        $uploadedFiles = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $originalName = $_FILES['images']['name'][$i];
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    // 验证文件类型
                    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        continue; // 跳过不支持的文件类型
                    }

                    // 生成安全的文件名
                    $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                    $fileName = $safeFileName . '.' . $extension;

                    // 确保文件名唯一
                    $counter = 1;
                    $finalFileName = $fileName;
                    while (file_exists($categoryDir . '/' . $finalFileName)) {
                        $finalFileName = $safeFileName . '_' . $counter . '.' . $extension;
                        $counter++;
                    }

                    $targetPath = $categoryDir . '/' . $finalFileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $uploadedFiles[] = $finalFileName;

                        // 生成缩略图
                        $imageInfo = getimagesize($targetPath);
                        if ($imageInfo && !generateThumbnail($targetPath, $thumbDir . '/' . $finalFileName, 200, 200, $imageInfo[2])) {
                            // 缩略图生成失败，但不影响主流程
                            error_log("Failed to generate thumbnail for: " . $finalFileName);
                        }
                    }
                }
            }
        }

        $config = loadSingleWorksConfig($configPath);
        if ($position === 'first') {
            array_unshift($config['custom_order'], $category);
        } else {
            $config['custom_order'][] = $category;
        }

        $config['display_names'][$category] = $displayName ?: $category;
        if ($description) {
            $config['descriptions'][$category] = $description;
        }

        if (!saveSingleWorksConfig($configPath, $config)) {
            throw new RuntimeException('保存配置失败');
        }

        respondJson([
            'success' => true,
            'message' => '分组创建成功' . (!empty($uploadedFiles) ? '，已上传 ' . count($uploadedFiles) . ' 张图片' : ''),
            'category' => $category,
            'uploaded_files' => $uploadedFiles,
            'thumbnail_info' => getCategoryThumbnailInfo($category, $categoryDir)
        ]);
    } catch (Throwable $e) {
        respondJson([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

function outputPhpUploadConfig(): void
{
    header('Content-Type: application/json');

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
    $recommendations = [];

    if ($uploadMaxBytes < 50 * 1024 * 1024) {
        $issues[] = "upload_max_filesize ({$config['upload_max_filesize']}) 小于推荐的50MB";
        $recommendations[] = '建议设置 upload_max_filesize = 50M';
    }

    if ($postMaxBytes < 60 * 1024 * 1024) {
        $issues[] = "post_max_size ({$config['post_max_size']}) 小于推荐的60MB";
        $recommendations[] = '建议设置 post_max_size = 60M (应大于upload_max_filesize)';
    }

    if ((int) $config['max_file_uploads'] < 20) {
        $issues[] = "max_file_uploads ({$config['max_file_uploads']}) 可能限制批量上传";
        $recommendations[] = '建议设置 max_file_uploads = 20';
    }

    if ((int) $config['max_execution_time'] < 300) {
        $issues[] = "max_execution_time ({$config['max_execution_time']}秒) 可能不足以处理大文件";
        $recommendations[] = '建议设置 max_execution_time = 300';
    }

    if ($config['file_uploads'] === 'Off') {
        $issues[] = '文件上传功能已禁用';
        $recommendations[] = '必须设置 file_uploads = On';
    }

    respondJson([
        'config' => $config,
        'limits' => [
            'upload_max_bytes' => $uploadMaxBytes,
            'post_max_bytes' => $postMaxBytes,
            'upload_max_mb' => round($uploadMaxBytes / 1024 / 1024, 1),
            'post_max_mb' => round($postMaxBytes / 1024 / 1024, 1),
        ],
        'issues' => $issues,
        'recommendations' => $recommendations,
        'status' => empty($issues) ? 'ok' : 'warning',
    ]);
}

function parseSizeToBytes(string $value): int
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $value);
    $number = (float) preg_replace('/[^0-9\.]/', '', $value);

    if ($unit) {
        return (int) round($number * pow(1024, stripos('bkmgtpezy', $unit[0])));
    }

    return (int) round($number);
}

function generateThumbnail(string $sourcePath, string $destPath, int $maxWidth, int $maxHeight, int $imageType): bool
{
    try {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (!function_exists('imagecreatefromwebp')) {
                    return false;
                }
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);
        $thumbWidth = (int) round($sourceWidth * $ratio);
        $thumbHeight = (int) round($sourceHeight * $ratio);

        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        if (in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbImage, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbImage, $destPath, 6);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbImage, $destPath);
                break;
            case IMAGETYPE_WEBP:
                if (!function_exists('imagewebp')) {
                    $result = false;
                } else {
                    $result = imagewebp($thumbImage, $destPath, 85);
                }
                break;
            default:
                $result = false;
        }

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return (bool) $result;
    } catch (Throwable $e) {
        return false;
    }
}

function respondJson(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

/**
 * 获取分组的缩略图信息
 */
function getCategoryThumbnailInfo($category, $dirPath)
{
    $result = [
        'custom_thumbnail' => null,
        'first_image_thumb' => null
    ];

    // 检查是否有自定义缩略图（在config中保存的）
    $configPath = __DIR__ . '/../../app/Config/single_works_config.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
        if (isset($config['category_thumbnails'][$category])) {
            $result['custom_thumbnail'] = $config['category_thumbnails'][$category];
        }
    }

    // 获取第一张图片的缩略图路径
    if (is_dir($dirPath)) {
        $images = glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($images)) {
            // 按文件名排序，确保一致性
            sort($images);
            $firstImage = basename($images[0]);
            $thumbPath = $dirPath . '/thumbs/' . $firstImage;

            // 如果缩略图不存在，尝试生成
            if (!file_exists($thumbPath)) {
                // 调用ThumbnailService生成缩略图
                try {
                    $thumbnailServicePath = __DIR__ . '/../../app/Services/ThumbnailService.php';
                    if (file_exists($thumbnailServicePath)) {
                        require_once $thumbnailServicePath;
                        if (class_exists('ThumbnailService')) {
                            ThumbnailService::generate($images[0], 'single-works');
                        }
                    }
                } catch (Throwable $e) {
                    error_log('缩略图生成失败: ' . $e->getMessage());
                }
            }

            if (file_exists($thumbPath)) {
                $result['first_image_thumb'] = '/assets/images/single-works/' . $category . '/thumbs/' . $firstImage;
            } else {
                // 如果缩略图依然不存在，使用原图
                $result['first_image_thumb'] = '/assets/images/single-works/' . $category . '/' . $firstImage;
            }
        }
    }

    return $result;
}

/**
 * 输出分组缩略图信息
 */
function outputCategoryThumbnail($imagesRoot)
{
    $category = $_GET['category'] ?? '';
    if (!$category) {
        respondJson(['success' => false, 'error' => '分组名称不能为空'], 400);
        return;
    }

    $dirPath = $imagesRoot . '/' . $category;
    $thumbnailInfo = getCategoryThumbnailInfo($category, $dirPath);

    respondJson([
        'success' => true,
        'thumbnail' => $thumbnailInfo['custom_thumbnail'],
        'first_image_thumb' => $thumbnailInfo['first_image_thumb']
    ]);
}

/**
 * 设置分组缩略图
 */
function setAsThumbnail($imagesRoot, $configPath)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';
    $image = $input['image'] ?? '';

    if (!$category || !$image) {
        respondJson(['success' => false, 'error' => '参数不完整'], 400);
        return;
    }

    // 保存到配置文件
    $config = [];
    $configFile = __DIR__ . '/../../app/Config/single_works_config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    }

    if (!isset($config['category_thumbnails'])) {
        $config['category_thumbnails'] = [];
    }

    $thumbnailUrl = '/assets/images/single-works/' . $category . '/thumbs/' . $image;
    $config['category_thumbnails'][$category] = $thumbnailUrl;

    // 保存配置
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($configFile, $configContent)) {
        respondJson([
            'success' => true,
            'thumbnail_url' => $thumbnailUrl
        ]);
    } else {
        respondJson(['success' => false, 'error' => '保存配置失败'], 500);
    }
}

/**
 * 删除分组缩略图
 */
function deleteThumbnail($imagesRoot, $configPath)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';

    if (!$category) {
        respondJson(['success' => false, 'error' => '分组名称不能为空'], 400);
        return;
    }

    // 从配置文件中删除
    $configFile = __DIR__ . '/../../app/Config/single_works_config.php';
    $config = [];
    if (file_exists($configFile)) {
        $config = require $configFile;
    }

    if (isset($config['category_thumbnails'][$category])) {
        unset($config['category_thumbnails'][$category]);
    }

    // 保存配置
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    file_put_contents($configFile, $configContent);

    // 获取第一张图片作为新的缩略图
    $dirPath = $imagesRoot . '/' . $category;
    $newThumbnailUrl = null;
    if (is_dir($dirPath)) {
        $images = glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($images)) {
            $firstImage = basename($images[0]);
            $thumbPath = $dirPath . '/thumbs/' . $firstImage;
            if (file_exists($thumbPath)) {
                $newThumbnailUrl = '/assets/images/single-works/' . $category . '/thumbs/' . $firstImage;
            }
        }
    }

    respondJson([
        'success' => true,
        'new_thumbnail_url' => $newThumbnailUrl
    ]);
}

/**
 * 重新排序分组图片 - 基于数据记录的优雅排序
 */
function reorderCategoryImages($imagesRoot, $imageOrderPath)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';
    $order = $input['order'] ?? [];

    if (!$category || empty($order)) {
        respondJson(['success' => false, 'error' => '参数不完整'], 400);
        return;
    }

    $dirPath = $imagesRoot . '/' . $category;
    if (!is_dir($dirPath)) {
        respondJson(['success' => false, 'error' => '分组不存在'], 404);
        return;
    }

    try {
        // 读取或创建图片排序配置
        $orderConfig = loadImageOrderConfig($imageOrderPath);

        // 验证所有文件都存在
        foreach ($order as $filename) {
            if (!file_exists($dirPath . '/' . $filename)) {
                respondJson(['success' => false, 'error' => "文件不存在: {$filename}"], 404);
                return;
            }
        }

        // 保存新的排序
        $orderConfig['categories'][$category] = $order;
        saveImageOrderConfig($imageOrderPath, $orderConfig);

        respondJson(['success' => true, 'message' => '排序保存成功']);

    } catch (Exception $e) {
        respondJson(['success' => false, 'error' => '排序失败: ' . $e->getMessage()], 500);
    }
}

/**
 * 删除分组图片
 */
function deleteCategoryImage($imagesRoot, $trashRoot, $imageOrderPath)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';
    $image = $input['image'] ?? '';

    if (!$category || !$image) {
        respondJson(['success' => false, 'error' => '参数不完整'], 400);
        return;
    }

    $imagePath = $imagesRoot . '/' . $category . '/' . $image;
    $thumbPath = $imagesRoot . '/' . $category . '/thumbs/' . $image;

    if (!file_exists($imagePath)) {
        respondJson(['success' => false, 'error' => '图片不存在'], 404);
        return;
    }

    // 移动到回收站
    $trashCategoryDir = $trashRoot . '/' . $category;
    if (!is_dir($trashCategoryDir)) {
        mkdir($trashCategoryDir, 0755, true);
    }
    if (!is_dir($trashCategoryDir . '/thumbs')) {
        mkdir($trashCategoryDir . '/thumbs', 0755, true);
    }

    $success = rename($imagePath, $trashCategoryDir . '/' . $image);
    if (file_exists($thumbPath)) {
        rename($thumbPath, $trashCategoryDir . '/thumbs/' . $image);
    }

    if ($success) {
        // 从排序配置中移除已删除的图片
        $orderConfig = loadImageOrderConfig($imageOrderPath);
        if (isset($orderConfig['categories'][$category])) {
            $orderConfig['categories'][$category] = array_values(
                array_filter(
                    $orderConfig['categories'][$category],
                    fn($filename) => $filename !== $image
                )
            );
            saveImageOrderConfig($imageOrderPath, $orderConfig);
        }

        respondJson(['success' => true]);
    } else {
        respondJson(['success' => false, 'error' => '删除失败'], 500);
    }
}

/**
 * 保存分组更新
 */
function saveCategoryUpdate($configPath, $imagesRoot)
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        parse_str(file_get_contents('php://input'), $input);
    }

    $category = $input['category'] ?? '';
    $displayName = $input['displayName'] ?? '';
    $description = $input['description'] ?? '';
    $categoryOrder = $input['category_order'] ?? '';

    if (!$category) {
        respondJson(['success' => false, 'error' => '分组名称不能为空'], 400);
        return;
    }

    try {
        $config = loadSingleWorksConfig($configPath);

        // 更新显示名称和描述
        if ($displayName) {
            $config['display_names'][$category] = $displayName;
        }
        if ($description) {
            $config['descriptions'][$category] = $description;
        }

        // 更新分组顺序
        if ($categoryOrder) {
            $config['custom_order'] = explode(',', $categoryOrder);
        }

        if (saveSingleWorksConfig($configPath, $config)) {
            // 获取更新后的缩略图信息
            $dirPath = $imagesRoot . '/' . $category;
            $thumbnailInfo = getCategoryThumbnailInfo($category, $dirPath);

            respondJson([
                'success' => true,
                'message' => '分组信息已更新',
                'thumbnail_info' => $thumbnailInfo
            ]);
        } else {
            respondJson(['success' => false, 'error' => '保存配置失败'], 500);
        }
    } catch (Exception $e) {
        respondJson(['success' => false, 'error' => '更新失败: ' . $e->getMessage()], 500);
    }
}

/**
 * 删除分组和文件
 */
function deleteCategoryAndFiles($configPath, $imagesRoot, $trashRoot)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';

    if (!$category) {
        respondJson(['success' => false, 'error' => '分组名称不能为空'], 400);
        return;
    }

    try {
        $categoryDir = $imagesRoot . '/' . $category;

        if (!is_dir($categoryDir)) {
            respondJson(['success' => false, 'error' => '分组不存在'], 404);
            return;
        }

        // 移动到回收站
        if (!is_dir($trashRoot)) {
            mkdir($trashRoot, 0755, true);
        }

        $trashTarget = $trashRoot . '/' . $category . '_deleted_' . date('Y-m-d_H-i-s');
        if (rename($categoryDir, $trashTarget)) {
            // 从配置中删除
            $config = loadSingleWorksConfig($configPath);

            // 从custom_order中删除
            if (isset($config['custom_order'])) {
                $config['custom_order'] = array_values(array_filter($config['custom_order'], function($item) use ($category) {
                    return $item !== $category;
                }));
            }

            // 删除显示名称和描述
            if (isset($config['display_names'][$category])) {
                unset($config['display_names'][$category]);
            }
            if (isset($config['descriptions'][$category])) {
                unset($config['descriptions'][$category]);
            }

            // 删除缩略图配置
            $thumbnailConfigFile = __DIR__ . '/../../app/Config/single_works_config.php';
            if (file_exists($thumbnailConfigFile)) {
                $thumbnailConfig = require $thumbnailConfigFile;
                if (isset($thumbnailConfig['category_thumbnails'][$category])) {
                    unset($thumbnailConfig['category_thumbnails'][$category]);
                    $configContent = "<?php\nreturn " . var_export($thumbnailConfig, true) . ";\n";
                    file_put_contents($thumbnailConfigFile, $configContent);
                }
            }

            if (saveSingleWorksConfig($configPath, $config)) {
                respondJson([
                    'success' => true,
                    'message' => '分组已删除并移至回收站'
                ]);
            } else {
                respondJson(['success' => false, 'error' => '删除成功但配置更新失败'], 500);
            }
        } else {
            respondJson(['success' => false, 'error' => '无法移动分组到回收站'], 500);
        }

    } catch (Exception $e) {
        respondJson(['success' => false, 'error' => '删除失败: ' . $e->getMessage()], 500);
    }
}

/**
 * 加载图片排序配置
 */
function loadImageOrderConfig(string $imageOrderPath): array
{
    if (!file_exists($imageOrderPath)) {
        return [
            '_comment' => 'Single-Works 图片排序配置文件',
            '_generated' => date('c'),
            'categories' => []
        ];
    }

    $content = file_get_contents($imageOrderPath);
    $config = json_decode($content, true);

    if (!is_array($config)) {
        return [
            '_comment' => 'Single-Works 图片排序配置文件',
            '_generated' => date('c'),
            'categories' => []
        ];
    }

    // 从新的合并结构中提取single-works模块的数据
    if (isset($config['modules']['single-works'])) {
        return $config['modules']['single-works'];
    }

    // 向后兼容：如果没有modules结构，直接返回旧格式
    return $config;
}

/**
 * 保存图片排序配置
 */
function saveImageOrderConfig(string $imageOrderPath, array $config): bool
{
    $config['_generated'] = date('c');

    $dir = dirname($imageOrderPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // 读取现有的合并配置文件
    $fullConfig = [];
    if (file_exists($imageOrderPath)) {
        $content = file_get_contents($imageOrderPath);
        $fullConfig = json_decode($content, true) ?? [];
    }

    // 确保modules结构存在
    if (!isset($fullConfig['modules'])) {
        $fullConfig['modules'] = [];
    }

    // 更新single-works模块的配置
    $fullConfig['modules']['single-works'] = $config;
    $fullConfig['_generated'] = date('c');

    $content = json_encode($fullConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($imageOrderPath, $content) !== false;
}

/**
 * 获取分组图片的排序列表
 */
function getCategoryImageOrder(string $category, string $imageOrderPath, array $allImages): array
{
    $orderConfig = loadImageOrderConfig($imageOrderPath);
    $savedOrder = $orderConfig['categories'][$category] ?? [];

    if (empty($savedOrder)) {
        // 如果没有保存的排序，按文件名自然排序
        sort($allImages, SORT_NATURAL | SORT_FLAG_CASE);
        return $allImages;
    }

    // 合并保存的排序和新文件
    $orderedImages = [];
    $remainingImages = $allImages;

    // 首先添加已排序的文件（如果它们仍然存在）
    foreach ($savedOrder as $filename) {
        if (in_array($filename, $allImages)) {
            $orderedImages[] = $filename;
            $remainingImages = array_diff($remainingImages, [$filename]);
        }
    }

    // 然后添加新的文件（按自然排序）
    if (!empty($remainingImages)) {
        sort($remainingImages, SORT_NATURAL | SORT_FLAG_CASE);
        $orderedImages = array_merge($orderedImages, $remainingImages);
    }

    return $orderedImages;
}
