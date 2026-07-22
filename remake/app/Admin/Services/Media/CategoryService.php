<?php

namespace App\Admin\Services\Media;

/**
 * 分类管理服务
 * 负责：分类创建、删除、重命名、查找缩略图等
 */
class CategoryService
{
    private ImageService $imageService;
    private VideoService $videoService;
    private ConfigService $configService;

    public function __construct(
        ImageService $imageService,
        VideoService $videoService,
        ConfigService $configService
    ) {
        $this->imageService = $imageService;
        $this->videoService = $videoService;
        $this->configService = $configService;
    }

    /**
     * 创建新分类
     */
    public function createCategory(string $baseDir, string $categoryName): array
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $categoryName)) {
            throw new \InvalidArgumentException('分类名只能包含字母、数字、下划线和连字符');
        }

        $categoryDir = $baseDir . '/' . $categoryName;

        if (file_exists($categoryDir)) {
            throw new \RuntimeException('分类已存在');
        }

        if (!mkdir($categoryDir, 0755, true)) {
            throw new \RuntimeException('创建分类目录失败');
        }

        $thumbDir = $categoryDir . '/thumbs';
        if (!mkdir($thumbDir, 0755, true)) {
            throw new \RuntimeException('创建缩略图目录失败');
        }

        return [
            'category' => $categoryName,
            'path' => $categoryDir,
            'created' => true
        ];
    }

    /**
     * 删除分类及其所有文件
     */
    public function deleteCategory(string $baseDir, string $categoryName): bool
    {
        $categoryDir = $baseDir . '/' . $categoryName;

        if (!is_dir($categoryDir)) {
            throw new \InvalidArgumentException('分类不存在');
        }

        $trashDir = dirname($baseDir) . '/trash/' . basename($baseDir);
        if (!is_dir($trashDir)) {
            mkdir($trashDir, 0755, true);
        }

        $targetPath = $trashDir . '/' . $categoryName . '_deleted_' . time();

        return rename($categoryDir, $targetPath);
    }

    /**
     * 重命名分类
     */
    public function renameCategory(
        string $baseDir,
        string $oldName,
        string $newName,
        string $configPath
    ): bool {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newName)) {
            throw new \InvalidArgumentException('分类名只能包含字母、数字、下划线和连字符');
        }

        $oldDir = $baseDir . '/' . $oldName;
        $newDir = $baseDir . '/' . $newName;

        if (!is_dir($oldDir)) {
            throw new \InvalidArgumentException('源分类不存在');
        }

        if (file_exists($newDir)) {
            throw new \RuntimeException('目标分类已存在');
        }

        if (!rename($oldDir, $newDir)) {
            throw new \RuntimeException('重命名分类失败');
        }

        // 更新配置
        $this->configService->renameCategory($configPath, $oldName, $newName);

        return true;
    }

    /**
     * 获取所有分类列表
     */
    public function getCategories(string $baseDir, string $module, string $configPath): array
    {
        if (!is_dir($baseDir)) {
            return [];
        }

        $config = $this->configService->loadConfig($configPath);
        $dirs = array_filter(glob($baseDir . '/*'), 'is_dir');
        $categories = [];

        foreach ($dirs as $dir) {
            $categoryName = basename($dir);

            // 跳过 trash 目录
            if ($categoryName === 'trash') {
                continue;
            }

            // Videos 模块特殊处理：统计 thumbs 目录中的缩略图数量
            if ($module === 'videos') {
                $thumbDir = $dir . '/thumbs';
                $thumbCount = is_dir($thumbDir) ? count($this->imageService->getImageFiles($thumbDir)) : 0;

                $categories[] = [
                    'name' => $categoryName,
                    'folder' => $categoryName,
                    'display_name' => $config['display_names'][$categoryName] ?? $this->formatDisplayName($categoryName),
                    'description' => $config['descriptions'][$categoryName] ?? '',
                    'image_count' => 0,
                    'video_count' => $thumbCount,
                    'media_count' => $thumbCount,
                    'count' => $thumbCount,
                    'thumbnail' => $this->findCategoryThumbnail($dir, $module, $categoryName, $config),
                    'position' => $this->getCategoryPosition($categoryName, $config['custom_order'] ?? [])
                ];
            } else {
                $imageFiles = $this->imageService->getImageFiles($dir);
                $videoFiles = $this->videoService->getVideoFiles($dir);
                $mediaCount = count($imageFiles) + count($videoFiles);

                $categories[] = [
                    'name' => $categoryName,
                    'folder' => $categoryName,
                    'display_name' => $config['display_names'][$categoryName] ?? $this->formatDisplayName($categoryName),
                    'description' => $config['descriptions'][$categoryName] ?? '',
                    'image_count' => count($imageFiles),
                    'video_count' => count($videoFiles),
                    'media_count' => $mediaCount,
                    'count' => $mediaCount, // 兼容字段
                    'thumbnail' => $this->findCategoryThumbnail($dir, $module, $categoryName, $config),
                    'position' => $this->getCategoryPosition($categoryName, $config['custom_order'] ?? [])
                ];
            }
        }

        // 排序
        usort($categories, function($a, $b) {
            if ($a['position'] === $b['position']) {
                return strnatcasecmp($a['name'], $b['name']);
            }
            return $a['position'] - $b['position'];
        });

        return $categories;
    }

    /**
     * 获取分类的所有媒体文件
     */
    public function getCategoryMedia(
        string $baseDir,
        string $module,
        string $category,
        string $orderPath,
        string $configPath
    ): array {
        $categoryDir = $baseDir . '/' . $category;

        if (!is_dir($categoryDir)) {
            return [];
        }

        $config = $this->configService->loadConfig($configPath);

        // Videos 模块特殊处理：只返回缩略图，不返回视频文件
        if ($module === 'videos') {
            return $this->getVideoThumbnails($categoryDir, $module, $category, $orderPath, $config);
        }

        $imageFiles = $this->imageService->getImageFiles($categoryDir);
        $videoFiles = $this->videoService->getVideoFiles($categoryDir);
        $allFiles = array_merge($imageFiles, $videoFiles);

        // 应用自定义排序
        $orderedFiles = $this->configService->getCategoryMediaOrder($module, $category, $orderPath, $allFiles);

        $currentThumbnail = $config['category_thumbnails'][$category] ?? null;
        $media = [];

        foreach ($orderedFiles as $filename) {
            $filePath = $categoryDir . '/' . $filename;
            $isVideo = $this->videoService->isVideoFile($filename);
            if ($isVideo) {
                $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
            } elseif ($module === 'galleries') {
                $thumbFilename = $this->detectGalleryThumbnailName($categoryDir, $filename);
            } else {
                $thumbFilename = $filename;
            }
            $thumbPath = $categoryDir . '/thumbs/' . $thumbFilename;

            // 根据模块类型构建URL前缀
            $urlBase = ($module === 'videos')
                ? "/assets/videos/video-gallery"
                : "/assets/images/{$module}";

            $thumbUrl = file_exists($thumbPath)
                ? "{$urlBase}/{$category}/thumbs/{$thumbFilename}"
                : "{$urlBase}/{$category}/{$filename}";

            // 判断是否是当前封面
            $isThumbnail = false;
            if ($currentThumbnail) {
                $isThumbnail = ($filename === basename($currentThumbnail)) ||
                              (strpos($currentThumbnail, $thumbFilename) !== false);
            }

            $media[] = [
                'id' => $filename,
                'name' => $filename,
                'path' => "{$urlBase}/{$category}/{$filename}",
                'thumb_path' => $thumbUrl,
                'thumb_name' => $thumbFilename,
                'size' => file_exists($filePath) ? filesize($filePath) : 0,
                'modified' => file_exists($filePath) ? filemtime($filePath) : 0,
                'type' => $isVideo ? 'video' : 'image',
                'is_thumbnail' => $isThumbnail,
                'is_video' => $isVideo,
                'is_image' => !$isVideo,
            ];
        }

        return $media;
    }

    /**
     * 查找分类缩略图
     */
    public function findCategoryThumbnail(string $categoryDir, string $module, string $categoryName, array $config): ?string
    {
        // 优先使用配置中的自定义缩略图
        if (!empty($config['category_thumbnails'][$categoryName])) {
            return $config['category_thumbnails'][$categoryName];
        }

        $thumbDir = $categoryDir . '/thumbs';

        // 根据模块类型构建 URL 前缀
        $urlBase = ($module === 'videos')
            ? "/assets/videos/video-gallery"
            : "/assets/images/{$module}";

        // 查找 icon-01.* 文件
        if (is_dir($thumbDir)) {
            $icons = glob($thumbDir . '/icon-01.*');
            if (!empty($icons)) {
                return "{$urlBase}/{$categoryName}/thumbs/" . basename($icons[0]);
            }
        }

        // 查找第一个媒体文件
        $imageFiles = $this->imageService->getImageFiles($categoryDir);
        $videoFiles = $this->videoService->getVideoFiles($categoryDir);
        $allFiles = array_merge($imageFiles, $videoFiles);

        if (!empty($allFiles)) {
            sort($allFiles);
            $firstFile = $allFiles[0];
            $isVideo = $this->videoService->isVideoFile($firstFile);

            if ($isVideo) {
                $thumbName = pathinfo($firstFile, PATHINFO_FILENAME) . '.jpg';
                $thumbPath = $thumbDir . '/' . $thumbName;
                if (file_exists($thumbPath)) {
                    return "/assets/images/{$module}/{$categoryName}/thumbs/{$thumbName}";
                }
            } else {
                $thumbPath = $thumbDir . '/' . $firstFile;
                if (file_exists($thumbPath)) {
                    return "{$urlBase}/{$categoryName}/thumbs/{$firstFile}";
                }
            }

            return "{$urlBase}/{$categoryName}/{$firstFile}";
        }

        return null;
    }

    private function detectGalleryThumbnailName(string $categoryDir, string $originalFile): string
    {
        $thumbsPath = $categoryDir . '/thumbs';
        $baseName = pathinfo($originalFile, PATHINFO_FILENAME);
        $originalExt = strtolower(pathinfo($originalFile, PATHINFO_EXTENSION));

        $galleryThumbName = $baseName . '_gallery.' . $originalExt;
        if (file_exists($thumbsPath . '/' . $galleryThumbName)) {
            return $galleryThumbName;
        }

        if (file_exists($thumbsPath . '/' . $originalFile)) {
            return $originalFile;
        }

        $galleryCandidates = glob($thumbsPath . '/' . $baseName . '_gallery.*');
        if (!empty($galleryCandidates)) {
            return basename($galleryCandidates[0]);
        }

        $directCandidates = glob($thumbsPath . '/' . $baseName . '.*');
        if (!empty($directCandidates)) {
            return basename($directCandidates[0]);
        }

        return $originalFile;
    }

    /**
     * 获取videos模块的缩略图列表（只返回缩略图，不返回视频文件）
     */
    private function getVideoThumbnails(
        string $categoryDir,
        string $module,
        string $category,
        string $orderPath,
        array $config
    ): array {
        $thumbDir = $categoryDir . '/thumbs';
        if (!is_dir($thumbDir)) {
            return [];
        }

        // 只扫描 thumbs 目录中的缩略图
        $thumbFiles = $this->imageService->getImageFiles($thumbDir);

        // 应用自定义排序
        $orderedFiles = $this->configService->getCategoryMediaOrder($module, $category, $orderPath, $thumbFiles);

        $urlBase = "/assets/videos/video-gallery";
        $currentThumbnail = $config['category_thumbnails'][$category] ?? null;
        $media = [];

        foreach ($orderedFiles as $filename) {
            $thumbPath = $thumbDir . '/' . $filename;

            // 判断是否是当前封面
            $isThumbnail = false;
            if ($currentThumbnail) {
                $isThumbnail = ($filename === basename($currentThumbnail)) ||
                              (strpos($currentThumbnail, $filename) !== false);
            }

            $media[] = [
                'id' => $filename,
                'name' => $filename,
                'path' => "{$urlBase}/{$category}/thumbs/{$filename}",
                'thumb_path' => "{$urlBase}/{$category}/thumbs/{$filename}",
                'thumb_name' => $filename,
                'size' => file_exists($thumbPath) ? filesize($thumbPath) : 0,
                'modified' => file_exists($thumbPath) ? filemtime($thumbPath) : 0,
                'type' => 'image',
                'is_thumbnail' => $isThumbnail,
                'is_video' => false,
                'is_image' => true,
            ];
        }

        return $media;
    }

    /**
     * 删除媒体文件
     */
    public function deleteMedia(
        string $baseDir,
        string $module,
        string $category,
        string $filename,
        string $orderPath
    ): bool {
        $categoryDir = $baseDir . '/' . $category;
        $filePath = $categoryDir . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('文件不存在');
        }

        // 移动到回收站
        $trashDir = dirname($baseDir) . '/trash/' . basename($baseDir) . '/' . $category;
        if (!is_dir($trashDir)) {
            mkdir($trashDir, 0755, true);
        }

        $trashPath = $trashDir . '/' . $filename;

        if (!rename($filePath, $trashPath)) {
            throw new \RuntimeException('删除文件失败');
        }

        // 删除缩略图
        $isVideo = $this->videoService->isVideoFile($filename);
        $thumbName = $isVideo ? pathinfo($filename, PATHINFO_FILENAME) . '.jpg' : $filename;
        $thumbPath = $categoryDir . '/thumbs/' . $thumbName;

        if (file_exists($thumbPath)) {
            $thumbTrashDir = $trashDir . '/thumbs';
            if (!is_dir($thumbTrashDir)) {
                mkdir($thumbTrashDir, 0755, true);
            }
            @rename($thumbPath, $thumbTrashDir . '/' . $thumbName);
        }

        // 从排序配置中移除
        $this->configService->removeFromMediaOrder($module, $category, $filename, $orderPath);

        return true;
    }

    /**
     * 格式化显示名称
     */
    private function formatDisplayName(string $folderName): string
    {
        $name = str_replace(['_', '-'], ' ', $folderName);
        return ucwords($name);
    }

    /**
     * 获取分类在自定义排序中的位置
     */
    private function getCategoryPosition(string $categoryName, array $customOrder): int
    {
        $pos = array_search($categoryName, $customOrder);
        return $pos !== false ? $pos : 9999;
    }
}
