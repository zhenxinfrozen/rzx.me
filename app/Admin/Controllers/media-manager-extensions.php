<?php
/**
 * Media Manager 扩展功能
 * 这些函数需要添加到 media-manager.php 中替换 TODO 部分
 */

/**
 * 重新排序媒体文件
 */
function reorderCategoryMedia(string $module, string $imagesRoot, string $imageOrderPath): void
{
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $category = $input['category'] ?? '';
        $order = $input['order'] ?? [];

        if (!$category || empty($order)) {
            throw new InvalidArgumentException('参数不完整');
        }

        // 保存排序
        $orderConfig = loadImageOrderConfig($imageOrderPath);
        $orderConfig['modules'][$module]['categories'][$category] = $order;
        $orderConfig['_generated'] = date('c');

        saveImageOrderConfig($imageOrderPath, $orderConfig);

        respondJson(['success' => true, 'message' => '排序已保存']);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 设置分类封面
 */
function setAsThumbnail(string $module, string $imagesRoot, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $category = $input['category'] ?? '';
        $image = $input['image'] ?? '';
        $thumbName = $input['thumb'] ?? ($input['thumb_name'] ?? $image);

        if (!$category || !$thumbName) {
            throw new InvalidArgumentException('参数不完整');
        }

        $thumbPath = $imagesRoot . '/' . $category . '/thumbs/' . $thumbName;
        if (!file_exists($thumbPath)) {
            $thumbPath = $imagesRoot . '/' . $category . '/' . $thumbName;
            if (!file_exists($thumbPath)) {
                throw new InvalidArgumentException('缩略图文件不存在');
            }
        }

        // 保存到配置
        $config = loadModuleConfig($configPath);
        $config['category_thumbnails'][$category] = "/assets/images/{$module}/{$category}/thumbs/{$thumbName}";
        saveModuleConfig($configPath, $config);

        respondJson([
            'success' => true,
            'thumbnail_url' => $config['category_thumbnails'][$category],
            'message' => '封面已设置'
        ]);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 上传自定义缩略图
 */
function uploadCustomThumbnail(string $module, string $imagesRoot, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        if (empty($_POST['category']) || !isset($_FILES['thumbnail'])) {
            throw new InvalidArgumentException('缺少必要参数');
        }

        $category = trim($_POST['category']);
        $file = $_FILES['thumbnail'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('缩略图上传失败');
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('缩略图不能超过10MB');
        }

        $categoryDir = $imagesRoot . '/' . $category;
        $thumbDir = $categoryDir . '/thumbs';

        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'custom-thumb-' . date('Ymd-His') . '.' . $extension;
        $targetPath = $thumbDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('保存缩略图失败');
        }

        $publicUrl = "/assets/images/{$module}/{$category}/thumbs/{$filename}";

        // 保存到配置
        $config = loadModuleConfig($configPath);
        $config['category_thumbnails'][$category] = $publicUrl;
        saveModuleConfig($configPath, $config);

        respondJson([
            'success' => true,
            'thumbnail_url' => $publicUrl,
            'message' => '缩略图已上传'
        ]);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 删除自定义缩略图
 */
function deleteCustomThumbnail(string $module, string $imagesRoot, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $category = $input['category'] ?? '';

        if (!$category) {
            throw new InvalidArgumentException('分类名称不能为空');
        }

        $config = loadModuleConfig($configPath);
        $thumbnailUrl = $config['category_thumbnails'][$category] ?? null;

        if ($thumbnailUrl) {
            // 删除文件
            $thumbnailPath = __DIR__ . '/../../../public' . $thumbnailUrl;
            if (file_exists($thumbnailPath) && strpos($thumbnailPath, 'custom-thumb-') !== false) {
                @unlink($thumbnailPath);
            }

            // 从配置中删除
            unset($config['category_thumbnails'][$category]);
            saveModuleConfig($configPath, $config);
        }

        // 查找第一张图片作为新缩略图
        $categoryDir = $imagesRoot . '/' . $category;
        $newThumbnail = findCategoryThumbnail($module, $categoryDir, $category, $config);

        respondJson([
            'success' => true,
            'new_thumbnail_url' => $newThumbnail,
            'message' => '缩略图已删除'
        ]);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 保存分类信息（包括重命名）
 */
function saveCategoryUpdate(string $module, string $imagesRoot, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $category = $input['category'] ?? '';
        $displayName = $input['displayName'] ?? '';
        $description = $input['description'] ?? '';
        $newFolderName = $input['newFolderName'] ?? null;

        if (!$category) {
            throw new InvalidArgumentException('分类名称不能为空');
        }

        $config = loadModuleConfig($configPath);
        $renamed = false;
        $actualCategory = $category;

        // 处理文件夹重命名
        if ($newFolderName && $newFolderName !== $category) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newFolderName)) {
                throw new InvalidArgumentException('文件夹名只能包含字母、数字、下划线和连字符');
            }

            $oldDir = $imagesRoot . '/' . $category;
            $newDir = $imagesRoot . '/' . $newFolderName;

            if (file_exists($newDir)) {
                throw new InvalidArgumentException('目标文件夹已存在');
            }

            if (!is_dir($oldDir)) {
                throw new InvalidArgumentException('源文件夹不存在');
            }

            if (!rename($oldDir, $newDir)) {
                throw new RuntimeException('重命名文件夹失败');
            }

            // 更新配置中的所有引用
            $oldOrder = $config['custom_order'] ?? [];
            $config['custom_order'] = array_map(function($item) use ($category, $newFolderName) {
                return $item === $category ? $newFolderName : $item;
            }, $oldOrder);

            if (isset($config['display_names'][$category])) {
                $config['display_names'][$newFolderName] = $config['display_names'][$category];
                unset($config['display_names'][$category]);
            }

            if (isset($config['descriptions'][$category])) {
                $config['descriptions'][$newFolderName] = $config['descriptions'][$category];
                unset($config['descriptions'][$category]);
            }

            if (isset($config['category_thumbnails'][$category])) {
                $oldThumb = $config['category_thumbnails'][$category];
                $newThumb = str_replace("/{$category}/", "/{$newFolderName}/", $oldThumb);
                $config['category_thumbnails'][$newFolderName] = $newThumb;
                unset($config['category_thumbnails'][$category]);
            }

            $renamed = true;
            $actualCategory = $newFolderName;
        }

        // 更新显示名称和描述
        if ($displayName) {
            $config['display_names'][$actualCategory] = $displayName;
        }
        if ($description !== null) {
            $config['descriptions'][$actualCategory] = $description;
        }

        saveModuleConfig($configPath, $config);

        $response = [
            'success' => true,
            'message' => '分类信息已更新'
        ];

        if ($renamed) {
            $response['renamed'] = true;
            $response['newCategory'] = $actualCategory;
        }

        respondJson($response);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 保存分类排序
 */
function saveCategoryOrder(string $module, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? ($input['category_order'] ?? '');

        if (!$order) {
            throw new InvalidArgumentException('排序数据不能为空');
        }

        // 如果是逗号分隔的字符串，转换为数组
        if (is_string($order)) {
            $order = array_filter(array_map('trim', explode(',', $order)));
        }

        $config = loadModuleConfig($configPath);
        $config['custom_order'] = $order;
        saveModuleConfig($configPath, $config);

        respondJson([
            'success' => true,
            'message' => '排序已保存',
            'order' => $order
        ]);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 输出分类缩略图信息
 */
function outputCategoryThumbnail(string $module, string $imagesRoot, string $configPath): void
{
    header('Content-Type: application/json');

    try {
        $category = $_GET['category'] ?? '';
        if (!$category) {
            throw new InvalidArgumentException('分类名称不能为空');
        }

        $config = loadModuleConfig($configPath);
        $categoryDir = $imagesRoot . '/' . $category;

        $customThumbnail = $config['category_thumbnails'][$category] ?? null;
        $firstImageThumb = null;

        if (is_dir($categoryDir)) {
            $firstImageThumb = findCategoryThumbnail($module, $categoryDir, $category, $config);
        }

        respondJson([
            'success' => true,
            'thumbnail' => $customThumbnail,
            'first_image_thumb' => $firstImageThumb
        ]);

    } catch (Throwable $e) {
        respondJson(['success' => false, 'error' => $e->getMessage()], 400);
    }
}

/**
 * 输出 PHP 上传配置
 */
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
        $recommendations[] = '建议设置 post_max_size = 60M';
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

// ==================== 配置管理辅助函数 ====================

/**
 * 加载模块配置
 */
function loadModuleConfig(string $configPath): array
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
        'display_names' => [],
        'descriptions' => [],
        'category_thumbnails' => [],
    ];
}

/**
 * 保存模块配置
 */
function saveModuleConfig(string $configPath, array $config): bool
{
    $dir = dirname($configPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return (bool) file_put_contents($configPath, $json, LOCK_EX);
}

/**
 * 加载图片排序配置
 */
function loadImageOrderConfig(string $imageOrderPath): array
{
    if (!file_exists($imageOrderPath)) {
        return [
            '_comment' => 'Media files order configuration',
            '_generated' => date('c'),
            'modules' => []
        ];
    }

    $content = file_get_contents($imageOrderPath);
    $config = json_decode($content, true);

    if (!is_array($config)) {
        return [
            '_comment' => 'Media files order configuration',
            '_generated' => date('c'),
            'modules' => []
        ];
    }

    return $config;
}

/**
 * 保存图片排序配置
 */
function saveImageOrderConfig(string $imageOrderPath, array $config): bool
{
    $dir = dirname($imageOrderPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $content = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($imageOrderPath, $content) !== false;
}

/**
 * 获取分类媒体文件的排序列表
 */
function getCategoryMediaOrder(string $module, string $category, string $imageOrderPath, array $allFiles): array
{
    $orderConfig = loadImageOrderConfig($imageOrderPath);
    $savedOrder = $orderConfig['modules'][$module]['categories'][$category] ?? [];

    if (empty($savedOrder)) {
        sort($allFiles, SORT_NATURAL | SORT_FLAG_CASE);
        return $allFiles;
    }

    $orderedFiles = [];
    $remainingFiles = $allFiles;

    foreach ($savedOrder as $filename) {
        if (in_array($filename, $allFiles)) {
            $orderedFiles[] = $filename;
            $remainingFiles = array_diff($remainingFiles, [$filename]);
        }
    }

    if (!empty($remainingFiles)) {
        sort($remainingFiles, SORT_NATURAL | SORT_FLAG_CASE);
        $orderedFiles = array_merge($orderedFiles, $remainingFiles);
    }

    return $orderedFiles;
}

/**
 * 从排序配置中移除文件
 */
function removeFromMediaOrder(string $module, string $category, string $filename, string $imageOrderPath): void
{
    $orderConfig = loadImageOrderConfig($imageOrderPath);

    if (isset($orderConfig['modules'][$module]['categories'][$category])) {
        $orderConfig['modules'][$module]['categories'][$category] = array_values(
            array_filter(
                $orderConfig['modules'][$module]['categories'][$category],
                fn($file) => $file !== $filename
            )
        );
        saveImageOrderConfig($imageOrderPath, $orderConfig);
    }
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

// ==================== 缩略图生成辅助函数 ====================

/**
 * 生成图片缩略图
 */
function generateImageThumbnail(string $sourcePath, string $destPath, int $maxWidth, int $maxHeight): bool
{
    try {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        switch ($imageInfo[2]) {
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

        if (in_array($imageInfo[2], [IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        $result = imagejpeg($thumbImage, $destPath, 85);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return (bool) $result;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * 生成视频缩略图（需要 FFmpeg）
 */
function generateVideoThumbnail(string $videoPath, string $destPath): bool
{
    try {
        // 检查 FFmpeg 是否可用
        $ffmpegPath = 'ffmpeg'; // 或配置中的路径

        $command = sprintf(
            '%s -i %s -ss 00:00:01.000 -vframes 1 -vf scale=640:-1 %s 2>&1',
            $ffmpegPath,
            escapeshellarg($videoPath),
            escapeshellarg($destPath)
        );

        exec($command, $output, $returnCode);

        return $returnCode === 0 && file_exists($destPath);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * 更新 findCategoryThumbnail 以支持配置
 */
function findCategoryThumbnail(string $module, string $categoryDir, string $categoryName, array $config = []): ?string
{
    // 优先使用配置中的自定义缩略图
    if (!empty($config['category_thumbnails'][$categoryName])) {
        return $config['category_thumbnails'][$categoryName];
    }

    $thumbDir = $categoryDir . '/thumbs';

    // 优先查找 icon-01.* 文件
    if (is_dir($thumbDir)) {
        $icons = glob($thumbDir . '/icon-01.*');
        if (!empty($icons)) {
            return "/assets/images/{$module}/{$categoryName}/thumbs/" . basename($icons[0]);
        }
    }

    // 查找第一张图片/视频
    $mediaFiles = glob($categoryDir . '/*.{jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv,webm}', GLOB_BRACE);
    if (!empty($mediaFiles)) {
        sort($mediaFiles);
        $firstFile = basename($mediaFiles[0]);
        $extension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));

        // 检查是否有缩略图
        if (is_dir($thumbDir)) {
            // 对于视频，查找对应的 .jpg 缩略图
            if (in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm'])) {
                $videoThumb = $thumbDir . '/' . pathinfo($firstFile, PATHINFO_FILENAME) . '.jpg';
                if (file_exists($videoThumb)) {
                    return "/assets/images/{$module}/{$categoryName}/thumbs/" . pathinfo($firstFile, PATHINFO_FILENAME) . '.jpg';
                }
            }

            // 对于图片，查找对应的缩略图
            if (file_exists($thumbDir . '/' . $firstFile)) {
                return "/assets/images/{$module}/{$categoryName}/thumbs/{$firstFile}";
            }
        }

        return "/assets/images/{$module}/{$categoryName}/{$firstFile}";
    }

    return null;
}
