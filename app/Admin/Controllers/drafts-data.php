<?php
/**
 * Drafts 数据加载器
 * 提供视图所需的数据，不处理AJAX请求
 */

require_once __DIR__ . '/../../Utils/GalleryManager.php';

$galleryManager = new GalleryManager();
$configPath = __DIR__ . '/../../storage/data/drafts-sort.json';
$imagesRoot = __DIR__ . '/../../../public/assets/images/drafts';
$trashRoot = __DIR__ . '/../../../storage/trash/drafts';

// 加载配置和数据
$currentConfig = loadDraftsConfig($configPath);
$categories = $galleryManager->getGalleryCategories('drafts');
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

$flashMessage = $_SESSION['drafts_flash'] ?? null;
unset($_SESSION['drafts_flash']);

// 包含必要的函数（从原控制器文件复制）
function loadDraftsConfig(string $configPath): array
{
    if (file_exists($configPath)) {
        $content = file_get_contents($configPath);
        $config = json_decode($content, true);
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
        'first_image_thumb' => null
    ];

    // 检查是否有自定义缩略图（在config中保存的）
    $configPath = __DIR__ . '/../../Config/drafts_config.php';
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

            if (file_exists($thumbPath)) {
                $result['first_image_thumb'] = '/assets/images/drafts/' . $category . '/thumbs/' . $firstImage;
            } else {
                // 如果缩略图依然不存在，使用原图
                $result['first_image_thumb'] = '/assets/images/drafts/' . $category . '/' . $firstImage;
            }
        }
    }

    return $result;
}
