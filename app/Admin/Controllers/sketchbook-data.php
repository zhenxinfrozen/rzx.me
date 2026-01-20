<?php
/**
 * Sketchbook 数据加载器
 * 提供视图所需的数据，不处理 AJAX 请求
 */

require_once __DIR__ . '/../../Utils/GalleryManager.php';

$galleryManager = new GalleryManager();
$configPath = __DIR__ . '/../../Config/sketchbook_sort.php';
$imagesRoot = __DIR__ . '/../../../public/assets/images/sketchbook';
$trashRoot = __DIR__ . '/../../../storage/trash/sketchbook';

$currentConfig = loadSketchbookConfig($configPath);
$categories = $galleryManager->getGalleryCategories('sketchbook');
$orderedCategories = reorderCategories($categories, $currentConfig['custom_order'] ?? []);

$categoryData = [];
foreach ($orderedCategories as $index => $category) {
    $dirPath = $imagesRoot . '/' . $category;
    $imageCount = is_dir($dirPath)
        ? count(glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE))
        : 0;

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

$flashMessage = $_SESSION['sketchbook_flash'] ?? null;
unset($_SESSION['sketchbook_flash']);

function sketchbook_find_existing_thumbnail(string $categoryDir, string $originalFile): ?string
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

function sketchbook_ensure_thumbnail(string $categoryDir, string $originalFile): ?string
{
    $existing = sketchbook_find_existing_thumbnail($categoryDir, $originalFile);
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
                $result = ThumbnailService::generate($sourcePath, 'sketchbook-thumb');
                if (is_array($result) && !empty($result['success']) && !empty($result['output_path'])) {
                    return basename($result['output_path']);
                }

                $expected = ThumbnailService::getOutputPath($sourcePath, 'sketchbook-thumb');
                if ($expected && file_exists($expected)) {
                    return basename($expected);
                }
            } catch (Throwable $e) {
                error_log('Sketchbook thumbnail generation failed: ' . $e->getMessage());
            }
        }
    }

    return sketchbook_find_existing_thumbnail($categoryDir, $originalFile);
}

function loadSketchbookConfig(string $configPath): array
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

function reorderCategories(array $categories, array $customOrder): array
{
    if (empty($customOrder)) {
        return $categories;
    }

    $ordered = [];
    foreach ($customOrder as $category) {
        if (in_array($category, $categories, true)) {
            $ordered[] = $category;
        }
    }

    $remaining = array_diff($categories, $ordered);
    return array_merge($ordered, $remaining);
}

function getCategoryThumbnailInfo($category, $dirPath)
{
    $result = [
        'custom_thumbnail' => null,
        'first_image_thumb' => null,
    ];

    $configPath = __DIR__ . '/../../Config/sketchbook_config.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
        if (isset($config['category_thumbnails'][$category])) {
            $result['custom_thumbnail'] = $config['category_thumbnails'][$category];
        }
    }

    if (is_dir($dirPath)) {
        $images = glob($dirPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($images)) {
            sort($images);
            $firstImage = basename($images[0]);
            $thumbName = sketchbook_ensure_thumbnail($dirPath, $firstImage);

            if ($thumbName) {
                $result['first_image_thumb'] = '/assets/images/sketchbook/' . $category . '/thumbs/' . $thumbName;
            } else {
                $result['first_image_thumb'] = '/assets/images/sketchbook/' . $category . '/' . $firstImage;
            }
        }
    }

    return $result;
}
