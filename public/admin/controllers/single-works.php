<?php
/**
 * Single-Works 管理控制器
 * 负责 single-works 页面分组排序、图片管理及相关 AJAX 操作
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../app/Utils/GalleryManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ajaxAction = $_GET['ajax'] ?? ($_POST['ajax_action'] ?? null);
$configPath = __DIR__ . '/../../../app/Config/single_works_sort.php';
$imagesRoot = __DIR__ . '/../../assets/images/single-works';
$trashRoot = __DIR__ . '/../../assets/images/trash';

$galleryManager = new GalleryManager();

if ($ajaxAction) {
    handleSingleWorksAjax($ajaxAction, $configPath, $imagesRoot, $trashRoot);
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

    $categoryData[] = [
        'id' => $category,
        'display_name' => $currentConfig['display_names'][$category] ?? $category,
        'description' => $currentConfig['descriptions'][$category] ?? '',
        'image_count' => $imageCount,
        'position' => $index + 1
    ];
}

$flashMessage = $_SESSION['single_works_flash'] ?? null;
unset($_SESSION['single_works_flash']);

$page_title = '🛠️ Single-Works 管理';
$page_subtitle = '管理 Single-Works 页面分组与图片';
$_GET['page'] = 'single-works';

require_once __DIR__ . '/../index.php';

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
function handleSingleWorksAjax(string $action, string $configPath, string $imagesRoot, string $trashRoot): void
{
    switch ($action) {
        case 'thumbnails':
            outputThumbnails($imagesRoot);
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

function outputThumbnails(string $imagesRoot): void
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
        $images = [];
        $files = scandir($categoryDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, $supported, true)) {
                continue;
            }

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

        usort($images, static fn($a, $b) => $b['modified'] <=> $a['modified']);

        respondJson([
            'success' => true,
            'category' => $category,
            'images' => $images,
            'count' => count($images),
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
        $input = file_get_contents('php://input');
        $payload = json_decode($input, true) ?? $_POST;

        $category = $payload['category'] ?? '';
        $displayName = $payload['displayName'] ?? $category;
        $description = $payload['description'] ?? '';
        $position = $payload['position'] ?? 'last';

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
            'message' => '分组创建成功',
            'category' => $category,
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
