<?php
/**
 * galleries 管理控制器
 * 负责 galleries 页面分组排序、图片管理及相关 AJAX 操作
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Utils/GalleryManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ajaxAction = $_GET['ajax'] ?? ($_POST['ajax_action'] ?? null);
$configPath = __DIR__ . '/../../storage/data/galleries-sort.json';
$imagesRoot = __DIR__ . '/../../../public/assets/images/galleries';
$trashRoot = __DIR__ . '/../../../public/assets/images/trash/galleries';
$imageOrderPath = __DIR__ . '/../../storage/data/image-orders.json';

$galleryManager = new GalleryManager();

if ($ajaxAction) {
    handlegalleriesAjax($ajaxAction, $configPath, $imagesRoot, $trashRoot, $imageOrderPath);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlegalleriesFormSubmission($configPath, $imagesRoot, $trashRoot);
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './galleries.php'));
    exit;
}

$currentConfig = loadgalleriesConfig($configPath);
$categories = $galleryManager->getGalleryCategories('galleries');
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

$flashMessage = $_SESSION['galleries_flash'] ?? null;
unset($_SESSION['galleries_flash']);

$page_title = '🛠️ galleries 管理';
$page_subtitle = '管理 galleries 页面分组与图片';
$_GET['page'] = 'galleries';

// 控制器逻辑完成，返回给 AdminIndexController 渲染视图

/**
 * 处理表单提交
 */
function handlegalleriesFormSubmission(string $configPath, string $imagesRoot, string $trashRoot): void
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

        if (savegalleriesConfig($configPath, $config)) {
            $_SESSION['galleries_flash'] = ['type' => 'success', 'text' => '配置保存成功！'];
        } else {
            throw new RuntimeException('配置保存失败，请检查文件权限');
        }
    } catch (Throwable $e) {
        $_SESSION['galleries_flash'] = ['type' => 'error', 'text' => '保存失败：' . $e->getMessage()];
    }
}

/**
 * 处理 AJAX 请求
 */
function handlegalleriesAjax(string $action, string $configPath, string $imagesRoot, string $trashRoot, string $imageOrderPath): void
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

        case 'upload_thumbnail':
            uploadCategoryThumbnail($imagesRoot);
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

        case 'save_category_order':
            saveCategoryOrder($configPath);
            return;

        case 'delete_category':
            deleteCategoryAndFiles($configPath, $imagesRoot, $trashRoot);
            return;

        default:
            respondJson(['success' => false, 'error' => '未知的操作'], 400);
            return;
    }
}

function loadgalleriesConfig(string $configPath): array
{
    if (file_exists($configPath)) {
        $json = file_get_contents($configPath);
        $config = json_decode($json, true);
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

function savegalleriesConfig(string $configPath, array $config): bool
{
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return (bool) file_put_contents($configPath, $json, LOCK_EX);
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
            $thumbName = ensuregalleriesThumbnail($categoryDir, $file);
            $thumbUrl = $thumbName
                ? "/assets/images/galleries/{$category}/thumbs/{$thumbName}"
                : "/assets/images/galleries/{$category}/{$file}";

            $images[] = [
                'name' => $file,
                'path' => "/assets/images/galleries/{$category}/{$file}",
                'thumb_path' => $thumbUrl,
                'thumb_name' => $thumbName,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
            ];
        }

        // 获取当前缩略图信息
        $thumbnailInfo = getCategoryThumbnailInfo($category, $categoryDir);
        $currentThumbnail = null;
        if (!empty($thumbnailInfo['custom_thumbnail'])) {
            $currentThumbnail = basename($thumbnailInfo['custom_thumbnail']);
        } elseif (!empty($thumbnailInfo['first_image_thumb'])) {
            $currentThumbnail = basename($thumbnailInfo['first_image_thumb']);
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

        $config = loadgalleriesConfig($configPath);
        if ($position === 'first') {
            array_unshift($config['custom_order'], $category);
        } else {
            $config['custom_order'][] = $category;
        }

        $config['display_names'][$category] = $displayName ?: $category;
        if ($description) {
            $config['descriptions'][$category] = $description;
        }

        if (!savegalleriesConfig($configPath, $config)) {
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function findExistingThumbnail(string $categoryDir, string $originalFile): ?string
{
    $thumbDir = rtrim($categoryDir, '/\\') . '/thumbs';
    if (!is_dir($thumbDir)) {
        return null;
    }

    $baseName = pathinfo($originalFile, PATHINFO_FILENAME);
    $originalExt = strtolower(pathinfo($originalFile, PATHINFO_EXTENSION));

    $candidates = [];
    if ($originalExt !== '') {
        $candidates[] = $thumbDir . '/' . $baseName . '_gallery.' . $originalExt;
        $candidates[] = $thumbDir . '/' . $baseName . '.' . $originalExt;
    }

    $galleryGlob = glob($thumbDir . '/' . $baseName . '_gallery.*') ?: [];
    $directGlob = glob($thumbDir . '/' . $baseName . '.*') ?: [];

    $candidates = array_merge($candidates, $galleryGlob, $directGlob);

    foreach ($candidates as $candidate) {
        if ($candidate && file_exists($candidate)) {
            return basename($candidate);
        }
    }

    return null;
}

function ensuregalleriesThumbnail(string $categoryDir, string $originalFile): ?string
{
    $existing = findExistingThumbnail($categoryDir, $originalFile);
    if ($existing) {
        return $existing;
    }

    $sourcePath = rtrim($categoryDir, '/\\') . '/' . $originalFile;
    if (!file_exists($sourcePath)) {
        return null;
    }

    $servicePath = __DIR__ . '/../../Services/ThumbnailService.php';
    if (file_exists($servicePath)) {
        require_once $servicePath;
        if (class_exists('ThumbnailService')) {
            try {
                $result = ThumbnailService::generate($sourcePath, 'galleries-thumb');
                if (is_array($result) && !empty($result['success']) && !empty($result['output_path'])) {
                    return basename($result['output_path']);
                }

                $expected = ThumbnailService::getOutputPath($sourcePath, 'galleries-thumb');
                if ($expected && file_exists($expected)) {
                    return basename($expected);
                }
            } catch (Throwable $e) {
                error_log('galleries thumbnail generation failed: ' . $e->getMessage());
            }
        }
    }

    return findExistingThumbnail($categoryDir, $originalFile);
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
    $configPath = __DIR__ . '/../../Config/galleries_config.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
        if (isset($config['category_thumbnails'][$category])) {
            $configThumbnail = $config['category_thumbnails'][$category];

            // 验证配置中的缩略图文件是否真实存在
            // 如果文件不存在，尝试查找同名但不同扩展名的文件
            $thumbnailFullPath = __DIR__ . '/../../../public' . $configThumbnail;
            if (file_exists($thumbnailFullPath)) {
                $result['custom_thumbnail'] = $configThumbnail;
            } else {
                // 尝试动态查找：从配置路径中提取文件名（不含扩展名）
                $baseName = pathinfo($configThumbnail, PATHINFO_FILENAME);
                $dirName = dirname($thumbnailFullPath);

                // 使用 glob 查找任意扩展名的同名文件
                $matches = glob($dirName . '/' . $baseName . '.*');
                if (!empty($matches) && file_exists($matches[0])) {
                    // 转换为相对URL路径
                    $relativePath = str_replace(__DIR__ . '/../../../public', '', $matches[0]);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $result['custom_thumbnail'] = $relativePath;
                }
            }
        }
    }

    // 获取第一张图片的缩略图路径
    if (is_dir($dirPath)) {
        $images = glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($images)) {
            // 按文件名排序，确保一致性
            sort($images);
            $firstImage = basename($images[0]);
            $thumbName = ensuregalleriesThumbnail($dirPath, $firstImage);

            if ($thumbName) {
                $result['first_image_thumb'] = '/assets/images/galleries/' . $category . '/thumbs/' . $thumbName;
            } else {
                // 如果缩略图依然不存在，使用原图
                $result['first_image_thumb'] = '/assets/images/galleries/' . $category . '/' . $firstImage;
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
    $thumbName = $input['thumb'] ?? ($input['thumb_name'] ?? '');

    // 添加调试日志
    error_log('setAsThumbnail called: ' . json_encode(['category' => $category, 'image' => $image, 'thumb' => $thumbName]));

    if (!$category || ($image === '' && $thumbName === '')) {
        respondJson(['success' => false, 'error' => '参数不完整'], 400);
        return;
    }

    $categoryDir = rtrim($imagesRoot, '/\\') . '/' . $category;

    // 如果没有提供thumbName，尝试查找
    if ($thumbName === '' && $image !== '') {
        $thumbName = ensuregalleriesThumbnail($categoryDir, $image) ?? $image;
    }

    if ($thumbName === '') {
        respondJson(['success' => false, 'error' => '未找到对应的缩略图文件'], 400);
        return;
    }

    // 检查缩略图文件是否存在
    $thumbPath = $categoryDir . '/thumbs/' . $thumbName;

    // 如果原始thumbName不存在，尝试使用findExistingThumbnail查找
    if (!file_exists($thumbPath)) {
        $foundThumb = findExistingThumbnail($categoryDir, $image);
        if ($foundThumb) {
            $thumbName = $foundThumb;
            $thumbPath = $categoryDir . '/thumbs/' . $thumbName;
        }
    }

    if (!file_exists($thumbPath)) {
        error_log("Thumbnail not found: $thumbPath");
        respondJson(['success' => false, 'error' => "缩略图文件不存在: $thumbName，请先生成"], 400);
        return;
    }

    // 保存到配置文件
    $config = [];
    $configFile = __DIR__ . '/../../Config/galleries_config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    }

    if (!isset($config['category_thumbnails'])) {
        $config['category_thumbnails'] = [];
    }

    $thumbnailUrl = '/assets/images/galleries/' . $category . '/thumbs/' . $thumbName;
    $config['category_thumbnails'][$category] = $thumbnailUrl;

    // 保存配置
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($configFile, $configContent)) {
        error_log("Thumbnail set successfully: $thumbnailUrl");
        respondJson([
            'success' => true,
            'thumbnail_url' => $thumbnailUrl,
            'thumbnail_file' => $thumbName,
        ]);
    } else {
        respondJson(['success' => false, 'error' => '保存配置失败'], 500);
    }
}

function uploadCategoryThumbnail(string $imagesRoot): void
{
    header('Content-Type: application/json');

    try {
        if (empty($_POST['category']) || !isset($_FILES['thumbnail'])) {
            throw new InvalidArgumentException('缺少必要参数');
        }

        $category = trim((string) $_POST['category']);
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
            throw new InvalidArgumentException('无效的分组名称');
        }

        $file = $_FILES['thumbnail'];
        if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            throw new RuntimeException('缩略图上传失败');
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('缩略图不能超过10MB');
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            throw new RuntimeException('文件不是有效的图片');
        }

        $allowedTypes = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];

        if (!isset($allowedTypes[$imageInfo[2]])) {
            throw new RuntimeException('不支持的图片格式');
        }

        $categoryDir = $imagesRoot . '/' . $category;
        if (!is_dir($categoryDir)) {
            throw new InvalidArgumentException('分组目录不存在');
        }

        $thumbDir = $categoryDir . '/thumbs';
        if (!is_dir($thumbDir) && !mkdir($thumbDir, 0755, true)) {
            throw new RuntimeException('无法创建缩略图目录');
        }

        $extension = $allowedTypes[$imageInfo[2]];
        $filename = 'custom-thumb-' . date('Ymd-His') . '.' . $extension;
        $targetPath = $thumbDir . '/' . $filename;

        if (!generateThumbnail($file['tmp_name'], $targetPath, 400, 400, $imageInfo[2])) {
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('保存缩略图失败');
            }
        }

        $publicUrl = '/assets/images/galleries/' . $category . '/thumbs/' . $filename;

        $configFile = __DIR__ . '/../../Config/galleries_config.php';
        $config = [];
        if (file_exists($configFile)) {
            $config = require $configFile;
            if (!is_array($config)) {
                $config = [];
            }
        }

        if (!isset($config['category_thumbnails'])) {
            $config['category_thumbnails'] = [];
        }

        $previous = $config['category_thumbnails'][$category] ?? null;
        if ($previous) {
            $previousPath = realpath(__DIR__ . '/../../../public' . $previous);
            $thumbRealDir = realpath($thumbDir);
            if ($previousPath && $thumbRealDir && strpos($previousPath, $thumbRealDir) === 0 && is_file($previousPath)) {
                @unlink($previousPath);
            }
        }

        $config['category_thumbnails'][$category] = $publicUrl;

        $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
        if (!file_put_contents($configFile, $configContent, LOCK_EX)) {
            throw new RuntimeException('保存缩略图配置失败');
        }

        respondJson([
            'success' => true,
            'thumbnail_url' => $publicUrl,
            'message' => '缩略图已上传',
        ]);
    } catch (Throwable $e) {
        respondJson([
            'success' => false,
            'error' => $e->getMessage(),
        ], 400);
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
    $configFile = __DIR__ . '/../../Config/galleries_config.php';
    $config = [];
    if (file_exists($configFile)) {
        $config = require $configFile;
    }

    $removedThumbnail = null;
    if (isset($config['category_thumbnails'][$category])) {
        $removedThumbnail = $config['category_thumbnails'][$category];
        unset($config['category_thumbnails'][$category]);
    }

    // 保存配置
    $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
    file_put_contents($configFile, $configContent);

    if ($removedThumbnail) {
        $fullPath = realpath(__DIR__ . '/../../../public' . $removedThumbnail);
        $thumbRealDir = realpath($imagesRoot . '/' . $category . '/thumbs');
        if ($fullPath && $thumbRealDir && strpos($fullPath, $thumbRealDir) === 0 && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    // 获取第一张图片作为新的缩略图
    $dirPath = $imagesRoot . '/' . $category;
    $newThumbnailUrl = null;
    if (is_dir($dirPath)) {
        $images = glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($images)) {
            $firstImage = basename($images[0]);
            $thumbPath = $dirPath . '/thumbs/' . $firstImage;
            if (file_exists($thumbPath)) {
                $newThumbnailUrl = '/assets/images/galleries/' . $category . '/thumbs/' . $firstImage;
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
    $newFolderName = $input['newFolderName'] ?? null;

    if (!$category) {
        respondJson(['success' => false, 'error' => '分组名称不能为空'], 400);
        return;
    }

    try {
        $config = loadgalleriesConfig($configPath);
        $renamed = false;
        $actualCategory = $category;

        // 处理文件夹重命名
        if ($newFolderName && $newFolderName !== $category) {
            // 验证新文件夹名
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newFolderName)) {
                respondJson(['success' => false, 'error' => '文件夹名只能包含英文、数字、下划线和短横线'], 400);
                return;
            }

            $oldDir = $imagesRoot . '/' . $category;
            $newDir = $imagesRoot . '/' . $newFolderName;

            // 检查目标文件夹是否已存在
            if (file_exists($newDir)) {
                respondJson(['success' => false, 'error' => '目标文件夹已存在'], 400);
                return;
            }

            // 检查源文件夹是否存在
            if (!is_dir($oldDir)) {
                respondJson(['success' => false, 'error' => '源文件夹不存在'], 404);
                return;
            }

            // 重命名文件夹
            if (!rename($oldDir, $newDir)) {
                respondJson(['success' => false, 'error' => '重命名文件夹失败'], 500);
                return;
            }

            // 更新配置中的所有引用
            // 1. 更新custom_order数组
            if (isset($config['custom_order'])) {
                $key = array_search($category, $config['custom_order']);
                if ($key !== false) {
                    $config['custom_order'][$key] = $newFolderName;
                }
            }

            // 2. 移动display_names
            if (isset($config['display_names'][$category])) {
                $config['display_names'][$newFolderName] = $config['display_names'][$category];
                unset($config['display_names'][$category]);
            }

            // 3. 移动descriptions
            if (isset($config['descriptions'][$category])) {
                $config['descriptions'][$newFolderName] = $config['descriptions'][$category];
                unset($config['descriptions'][$category]);
            }

            // 4. 移动image_orders配置
            $imageOrdersFile = $imagesRoot . '/image-orders.json';
            if (file_exists($imageOrdersFile)) {
                $imageOrders = json_decode(file_get_contents($imageOrdersFile), true) ?? [];
                if (isset($imageOrders[$category])) {
                    $imageOrders[$newFolderName] = $imageOrders[$category];
                    unset($imageOrders[$category]);
                    file_put_contents($imageOrdersFile, json_encode($imageOrders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }

            $renamed = true;
            $actualCategory = $newFolderName;
        }

        // 更新显示名称和描述（使用实际的分类名）
        if ($displayName) {
            $config['display_names'][$actualCategory] = $displayName;
        }
        if ($description) {
            $config['descriptions'][$actualCategory] = $description;
        }

        // 更新分组顺序
        if ($categoryOrder) {
            $config['custom_order'] = explode(',', $categoryOrder);
        }

        if (savegalleriesConfig($configPath, $config)) {
            // 获取更新后的缩略图信息
            $dirPath = $imagesRoot . '/' . $actualCategory;
            $thumbnailInfo = getCategoryThumbnailInfo($actualCategory, $dirPath);

            $response = [
                'success' => true,
                'message' => '分组信息已更新',
                'thumbnail_info' => $thumbnailInfo
            ];

            if ($renamed) {
                $response['renamed'] = true;
                $response['newCategory'] = $actualCategory;
            }

            respondJson($response);
        } else {
            respondJson(['success' => false, 'error' => '保存配置失败'], 500);
        }
    } catch (Exception $e) {
        respondJson(['success' => false, 'error' => '更新失败: ' . $e->getMessage()], 500);
    }
}

/**
 * 保存分类排序
 */
function saveCategoryOrder($configPath)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $categoryOrder = $input['category_order'] ?? '';

    if (!$categoryOrder) {
        respondJson(['success' => false, 'error' => '分类排序不能为空'], 400);
        return;
    }

    try {
        $config = loadgalleriesConfig($configPath);

        // 更新分组顺序
        $config['custom_order'] = array_filter(array_map('trim', explode(',', $categoryOrder)));

        if (savegalleriesConfig($configPath, $config)) {
            respondJson([
                'success' => true,
                'message' => '排序已保存',
                'order' => $config['custom_order']
            ]);
        } else {
            respondJson(['success' => false, 'error' => '保存配置失败'], 500);
        }
    } catch (Exception $e) {
        respondJson(['success' => false, 'error' => '保存失败: ' . $e->getMessage()], 500);
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
            $config = loadgalleriesConfig($configPath);

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
            $thumbnailConfigFile = __DIR__ . '/../../Config/galleries_config.php';
            if (file_exists($thumbnailConfigFile)) {
                $thumbnailConfig = require $thumbnailConfigFile;
                if (isset($thumbnailConfig['category_thumbnails'][$category])) {
                    unset($thumbnailConfig['category_thumbnails'][$category]);
                    $configContent = "<?php\nreturn " . var_export($thumbnailConfig, true) . ";\n";
                    file_put_contents($thumbnailConfigFile, $configContent);
                }
            }

            if (savegalleriesConfig($configPath, $config)) {
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
            '_comment' => 'galleries 图片排序配置文件',
            '_generated' => date('c'),
            'categories' => []
        ];
    }

    $content = file_get_contents($imageOrderPath);
    $config = json_decode($content, true);

    if (!is_array($config)) {
        return [
            '_comment' => 'galleries 图片排序配置文件',
            '_generated' => date('c'),
            'categories' => []
        ];
    }

    // 从新的合并结构中提取galleries模块的数据
    if (isset($config['modules']['galleries'])) {
        return $config['modules']['galleries'];
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

    // 更新galleries模块的配置
    $fullConfig['modules']['galleries'] = $config;
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
