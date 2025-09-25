<?php
// app/Utils/GalleryManager.php

require_once __DIR__ . '/ThumbnailGenerator.php';

class GalleryManager
{
    private $galleriesPath;
    private $galleriesUrl;
    private $maxFileSize;
    
    public function __construct()
    {
        $this->galleriesPath = __DIR__ . '/../../public/assets/images/galleries';
        $this->galleriesUrl = '/assets/images/galleries';
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB 限制
    }
    
    /**
     * 获取文件大小限制（MB）
     */
    public function getMaxFileSizeMB()
    {
        return round($this->maxFileSize / 1024 / 1024, 1);
    }
    
    /**
     * 检查画廊中被跳过的大文件
     */
    public function getSkippedFiles($galleryName)
    {
        $galleryPath = $this->galleriesPath . '/' . $galleryName;
        $skippedFiles = [];
        
        if (!is_dir($galleryPath)) {
            return $skippedFiles;
        }
        
        $files = scandir($galleryPath);
        $supportedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }
            
            $filePath = $galleryPath . '/' . $file;
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $fileSize = filesize($filePath);
                
                if (in_array($extension, $supportedTypes) && $fileSize > $this->maxFileSize) {
                    $skippedFiles[] = [
                        'name' => $file,
                        'size' => $fileSize,
                        'size_mb' => round($fileSize / 1024 / 1024, 2)
                    ];
                }
            }
        }
        
        return $skippedFiles;
    }
    
    /**
     * 扫描galleries目录，获取所有子目录
     */
    public function scanGalleries()
    {
        $galleries = [];
        
        if (!is_dir($this->galleriesPath)) {
            return $galleries;
        }
        
        $dirs = scandir($this->galleriesPath);
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            
            $fullPath = $this->galleriesPath . '/' . $dir;
            if (is_dir($fullPath)) {
                $galleries[] = [
                    'name' => $dir,
                    'path' => $fullPath,
                    'url' => $this->galleriesUrl . '/' . $dir,
                    'route' => '/gallery-' . $dir
                ];
            }
        }
        
        return $galleries;
    }
    
    /**
     * 获取指定目录下的所有分类（子目录）
     * @param string $baseDir 基础目录名（相对于images目录）
     * @return array 分类名称数组
     */
    public function getGalleryCategories($baseDir)
    {
        $categories = [];
        $basePath = __DIR__ . '/../../public/assets/images/' . $baseDir;
        
        if (!is_dir($basePath)) {
            return $categories;
        }
        
        $dirs = scandir($basePath);
        
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($basePath . '/' . $dir)) {
                continue;
            }
            
            $categories[] = $dir;
        }
        
        return $categories;
    }
    
    /**
     * 获取指定目录下指定分类的所有图片
     * @param string $baseDir 基础目录名
     * @param string $category 分类名
     * @return array 图片信息数组
     */
    public function getCategoryImages($baseDir, $category)
    {
        $images = [];
        $categoryPath = __DIR__ . '/../../public/assets/images/' . $baseDir . '/' . $category;
        
        if (!is_dir($categoryPath)) {
            return $images;
        }
        
        $files = scandir($categoryPath);
        $supportedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }
            
            $filePath = $categoryPath . '/' . $file;
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $fileSize = filesize($filePath);
                
                // 跳过过大的文件
                if (in_array($extension, $supportedTypes) && $fileSize <= $this->maxFileSize) {
                    $images[] = [
                        'name' => $file,
                        'path' => $filePath,
                        'url' => '/assets/images/' . $baseDir . '/' . $category . '/' . $file,
                        'thumb_url' => '/assets/images/' . $baseDir . '/' . $category . '/thumbs/' . $file,
                        'size' => $fileSize,
                        'category' => $category
                    ];
                }
            }
        }
        
        // 按文件名排序
        usort($images, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        return $images;
    }
    
    /**
     * 获取指定gallery的所有图片
     */
    public function getGalleryImages($galleryName)
    {
        $galleryPath = $this->galleriesPath . '/' . $galleryName;
        $images = [];
        
        if (!is_dir($galleryPath)) {
            return $images;
        }
        
        $files = scandir($galleryPath);
        $supportedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB 限制
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }
            
            $filePath = $galleryPath . '/' . $file;
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $fileSize = filesize($filePath);
                
                // 检查文件类型和大小
                if (in_array($extension, $supportedTypes) && $fileSize <= $maxFileSize) {
                    $images[] = [
                        'name' => $file,
                        'path' => $filePath,
                        'url' => $this->galleriesUrl . '/' . $galleryName . '/' . $file,
                        'thumb_url' => $this->galleriesUrl . '/' . $galleryName . '/thumbs/' . $file,
                        'size' => $fileSize
                    ];
                } elseif (in_array($extension, $supportedTypes) && $fileSize > $maxFileSize) {
                    // 记录被跳过的大文件
                    error_log("Gallery: 跳过大文件 {$file} (". round($fileSize/1024/1024, 2) ."MB > 10MB)");
                }
            }
        }
        
        // 按文件名排序
        usort($images, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $images;
    }
    
    /**
     * 为gallery生成所有缩略图
     */
    public function generateThumbnails($galleryName)
    {
        // 检查是否是特殊路径（包含斜杠）
        if (strpos($galleryName, '/') !== false) {
            return $this->generateThumbnailsForPath($galleryName);
        }
        
        $images = $this->getGalleryImages($galleryName);
        $thumbsPath = $this->galleriesPath . '/' . $galleryName . '/thumbs';
        
        // 创建thumbs目录
        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath, 0755, true);
        }
        
        $thumbnailGenerator = new ThumbnailGenerator();
        $results = [];
        
        foreach ($images as $image) {
            $thumbPath = $thumbsPath . '/' . $image['name'];
            
            // 如果缩略图不存在，则生成
            if (!file_exists($thumbPath)) {
                $success = $thumbnailGenerator->generate($image['path'], $thumbPath, ['width' => 300, 'height' => 300]);
                $results[] = [
                    'image' => $image['name'],
                    'success' => $success,
                    'thumb_path' => $thumbPath
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * 为任意路径生成缩略图
     * @param string $relativePath 相对于 images 目录的路径，如 'Single-Works/Animals'
     */
    public function generateThumbnailsForPath($relativePath)
    {
        $fullPath = __DIR__ . '/../../public/assets/images/' . $relativePath;
        $thumbsPath = $fullPath . '/thumbs';
        
        if (!is_dir($fullPath)) {
            return [];
        }
        
        // 创建thumbs目录
        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath, 0755, true);
        }
        
        $thumbnailGenerator = new ThumbnailGenerator();
        $results = [];
        $supportedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        $files = scandir($fullPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'thumbs') {
                continue;
            }
            
            $filePath = $fullPath . '/' . $file;
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $fileSize = filesize($filePath);
                
                if (in_array($extension, $supportedTypes) && $fileSize <= $this->maxFileSize) {
                    $thumbPath = $thumbsPath . '/' . $file;
                    
                    // 如果缩略图不存在，则生成 (200px max)
                    if (!file_exists($thumbPath)) {
                        $success = $thumbnailGenerator->generate($filePath, $thumbPath, ['width' => 200, 'height' => 200]);
                        $results[] = [
                            'image' => $file,
                            'success' => $success,
                            'thumb_path' => $thumbPath
                        ];
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * 为gallery生成图标缩略图（100x100px, 两张：默认和悬停）
     */
    public function generateGalleryIcon($galleryName)
    {
        $images = $this->getGalleryImages($galleryName);
        
        if (empty($images)) {
            return false;
        }
        
        $thumbsPath = $this->galleriesPath . '/' . $galleryName . '/thumbs';
        
        // 创建thumbs目录
        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath, 0755, true);
        }
        
        $thumbnailGenerator = new ThumbnailGenerator();
        $results = [];
        
        // 生成两个图标：默认和悬停
        for ($i = 0; $i < min(2, count($images)); $i++) {
            $sourceImage = $images[$i];
            $extension = pathinfo($sourceImage['name'], PATHINFO_EXTENSION);
            $iconNumber = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $iconName = "00-{$galleryName}-icon-{$iconNumber}.{$extension}";
            $iconPath = $thumbsPath . '/' . $iconName;
            
            // 如果图标不存在，则生成
            if (!file_exists($iconPath)) {
                $success = $thumbnailGenerator->generate($sourceImage['path'], $iconPath, ['width' => 100, 'height' => 100]);
                $results[$i == 0 ? 'default' : 'hover'] = [
                    'success' => $success,
                    'icon_name' => $iconName,
                    'icon_path' => $iconPath,
                    'icon_url' => $this->galleriesUrl . '/' . $galleryName . '/thumbs/' . $iconName
                ];
            } else {
                $results[$i == 0 ? 'default' : 'hover'] = [
                    'success' => true,
                    'icon_name' => $iconName,
                    'icon_path' => $iconPath,
                    'icon_url' => $this->galleriesUrl . '/' . $galleryName . '/thumbs/' . $iconName
                ];
            }
        }
        
        // 如果只有一张图片，复制为悬停图标
        if (count($images) == 1 && isset($results['default'])) {
            $results['hover'] = $results['default'];
        }
        
        return $results;
    }
    
    /**
     * 为所有galleries生成图标
     */
    public function generateAllIcons()
    {
        $galleries = $this->scanGalleries();
        $results = [];
        
        foreach ($galleries as $gallery) {
            $result = $this->generateGalleryIcon($gallery['name']);
            $results[$gallery['name']] = $result;
        }
        
        return $results;
    }
    
    /**
     * 获取gallery的图标URLs（默认和悬停）
     */
    public function getGalleryIconUrls($galleryName)
    {
        $images = $this->getGalleryImages($galleryName);
        
        if (empty($images)) {
            return null;
        }
        
        $extension = pathinfo($images[0]['name'], PATHINFO_EXTENSION);
        $baseUrl = $this->galleriesUrl . '/' . $galleryName . '/thumbs/';
        
        return [
            'default' => $baseUrl . "00-{$galleryName}-icon-01.{$extension}",
            'hover' => $baseUrl . "00-{$galleryName}-icon-02.{$extension}"
        ];
    }
    
    /**
     * 获取排序后的分组列表
     * @param string $baseDir 基础目录名
     * @return array 排序后的分组数据
     */
    public function getSortedCategories($baseDir)
    {
        $categories = $this->getGalleryCategories($baseDir);
        $configPath = __DIR__ . '/../Config/single_works_sort.php';
        
        // 如果配置文件不存在，返回原始顺序
        if (!file_exists($configPath)) {
            return $this->formatCategoriesData($categories);
        }
        
        $config = require $configPath;
        $sortMethod = $config['sort_method'] ?? 'alphabetical';
        
        switch ($sortMethod) {
            case 'custom_order':
                $categories = $this->sortByCustomOrder($categories, $config['custom_order'] ?? []);
                break;
                
            case 'prefix_sort':
                $categories = $this->sortByPrefix($categories, $config['prefix_settings'] ?? []);
                break;
                
            case 'date_modified':
                $categories = $this->sortByDateModified($categories, $baseDir);
                break;
                
            case 'alphabetical':
            default:
                sort($categories);
                break;
        }
        
        return $this->formatCategoriesData($categories, $config);
    }
    
    /**
     * 按自定义顺序排序
     */
    private function sortByCustomOrder($categories, $customOrder)
    {
        if (empty($customOrder)) {
            return $categories;
        }
        
        $sorted = [];
        
        // 先添加自定义顺序中的分组
        foreach ($customOrder as $orderCategory) {
            if (in_array($orderCategory, $categories)) {
                $sorted[] = $orderCategory;
            }
        }
        
        // 添加未在自定义顺序中的分组（按字母顺序）
        $remaining = array_diff($categories, $sorted);
        sort($remaining);
        
        return array_merge($sorted, $remaining);
    }
    
    /**
     * 按前缀排序
     */
    private function sortByPrefix($categories, $prefixSettings)
    {
        $separator = $prefixSettings['separator'] ?? '-';
        
        // 提取前缀并排序
        $categoriesWithPrefix = [];
        foreach ($categories as $category) {
            $parts = explode($separator, $category, 2);
            $prefix = is_numeric($parts[0]) ? intval($parts[0]) : 999;
            $categoriesWithPrefix[] = [
                'name' => $category,
                'prefix' => $prefix,
                'display_name' => ($prefixSettings['remove_prefix'] ?? false) && count($parts) > 1 ? $parts[1] : $category
            ];
        }
        
        // 按前缀排序
        usort($categoriesWithPrefix, function($a, $b) {
            return $a['prefix'] <=> $b['prefix'];
        });
        
        return array_column($categoriesWithPrefix, 'name');
    }
    
    /**
     * 按修改时间排序
     */
    private function sortByDateModified($categories, $baseDir)
    {
        $basePath = __DIR__ . '/../../public/assets/images/' . $baseDir;
        $categoriesWithTime = [];
        
        foreach ($categories as $category) {
            $dirPath = $basePath . '/' . $category;
            $categoriesWithTime[] = [
                'name' => $category,
                'time' => is_dir($dirPath) ? filemtime($dirPath) : 0
            ];
        }
        
        // 按时间倒序排列（最新的在前）
        usort($categoriesWithTime, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        
        return array_column($categoriesWithTime, 'name');
    }
    
    /**
     * 格式化分组数据，添加显示名称等信息
     */
    private function formatCategoriesData($categories, $config = [])
    {
        $formatted = [];
        $displayNames = $config['display_names'] ?? [];
        $descriptions = $config['descriptions'] ?? [];
        
        foreach ($categories as $category) {
            $formatted[] = [
                'name' => $category,
                'display_name' => $displayNames[$category] ?? $category,
                'description' => $descriptions[$category] ?? '',
                'url' => '/assets/images/single-works/' . $category
            ];
        }
        
        return $formatted;
    }
}