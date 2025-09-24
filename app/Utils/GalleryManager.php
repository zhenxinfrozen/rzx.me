<?php
// app/Utils/GalleryManager.php

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
}