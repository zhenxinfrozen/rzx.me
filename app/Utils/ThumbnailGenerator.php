<?php
// app/Utils/ThumbnailGenerator.php
// 缩略图生成工具类

require_once __DIR__ . '/ImageProcessor.php';

class ThumbnailGenerator {
    
    // 默认缩略图配置
    private static $defaultConfig = [
        'width' => 200,
        'height' => 200,
        'quality' => 80,
        'format' => 'jpg',
        'crop' => false,
        'suffix' => '_thumb'
    ];
    
    /**
     * 生成单个缩略图
     * 
     * @param string $sourcePath 源图片路径
     * @param string $thumbnailPath 缩略图输出路径（可选）
     * @param array $config 配置选项
     * @return string|false 缩略图路径或false
     */
    public static function generate($sourcePath, $thumbnailPath = null, $config = []) {
        // 检查GD库
        if (!ImageProcessor::isGdAvailable()) {
            error_log("ThumbnailGenerator: GD library not available");
            return false;
        }
        
        // 检查源文件
        if (!file_exists($sourcePath)) {
            error_log("ThumbnailGenerator: Source file not found: $sourcePath");
            return false;
        }
        
        // 合并配置
        $config = array_merge(self::$defaultConfig, $config);
        
        // 生成缩略图路径（如果未提供）
        if (!$thumbnailPath) {
            $thumbnailPath = self::generateThumbnailPath($sourcePath, $config);
        }
        
        // 如果缩略图已存在且比源文件新，跳过生成
        if (file_exists($thumbnailPath) && filemtime($thumbnailPath) >= filemtime($sourcePath)) {
            return $thumbnailPath;
        }
        
        try {
            // 获取源图片信息
            $imageInfo = ImageProcessor::getImageInfo($sourcePath);
            if (!$imageInfo) {
                error_log("ThumbnailGenerator: Cannot get image info for: $sourcePath");
                return false;
            }
            
            // 创建源图片资源
            $sourceImage = ImageProcessor::createImageResource($sourcePath);
            if (!$sourceImage) {
                error_log("ThumbnailGenerator: Cannot create image resource for: $sourcePath");
                return false;
            }
            
            // 计算缩略图尺寸
            list($thumbWidth, $thumbHeight) = ImageProcessor::calculateDimensions(
                $imageInfo['width'], 
                $imageInfo['height'], 
                $config['width'], 
                $config['height'], 
                $config['crop']
            );
            
            // 创建缩略图画布
            $thumbnailImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // 处理透明度（PNG/GIF）
            if ($imageInfo['type'] == IMAGETYPE_PNG || $imageInfo['type'] == IMAGETYPE_GIF) {
                imagealphablending($thumbnailImage, false);
                imagesavealpha($thumbnailImage, true);
                $transparent = imagecolorallocatealpha($thumbnailImage, 255, 255, 255, 127);
                imagefill($thumbnailImage, 0, 0, $transparent);
            }
            
            // 生成缩略图
            if ($config['crop']) {
                // 裁剪模式
                $sourceRatio = $imageInfo['width'] / $imageInfo['height'];
                $thumbRatio = $config['width'] / $config['height'];
                
                if ($sourceRatio > $thumbRatio) {
                    // 源图更宽，裁剪左右
                    $cropHeight = $imageInfo['height'];
                    $cropWidth = $cropHeight * $thumbRatio;
                    $srcX = ($imageInfo['width'] - $cropWidth) / 2;
                    $srcY = 0;
                } else {
                    // 源图更高，裁剪上下
                    $cropWidth = $imageInfo['width'];
                    $cropHeight = $cropWidth / $thumbRatio;
                    $srcX = 0;
                    $srcY = ($imageInfo['height'] - $cropHeight) / 2;
                }
                
                imagecopyresampled(
                    $thumbnailImage, $sourceImage,
                    0, 0, $srcX, $srcY,
                    $config['width'], $config['height'],
                    $cropWidth, $cropHeight
                );
            } else {
                // 缩放模式
                imagecopyresampled(
                    $thumbnailImage, $sourceImage,
                    0, 0, 0, 0,
                    $thumbWidth, $thumbHeight,
                    $imageInfo['width'], $imageInfo['height']
                );
            }
            
            // 保存缩略图
            $result = ImageProcessor::saveImage($thumbnailImage, $thumbnailPath, $config['format'], $config['quality']);
            
            // 清理资源
            imagedestroy($sourceImage);
            imagedestroy($thumbnailImage);
            
            return $result ? $thumbnailPath : false;
            
        } catch (Exception $e) {
            error_log("ThumbnailGenerator: Error generating thumbnail - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 批量生成缩略图
     * 
     * @param array $sourcePaths 源图片路径数组
     * @param array $config 配置选项
     * @return array 结果数组 [source_path => thumbnail_path_or_false]
     */
    public static function generateBatch($sourcePaths, $config = []) {
        $results = [];
        
        foreach ($sourcePaths as $sourcePath) {
            $results[$sourcePath] = self::generate($sourcePath, null, $config);
        }
        
        return $results;
    }
    
    /**
     * 为目录中的所有图片生成缩略图
     * 
     * @param string $directory 图片目录
     * @param array $config 配置选项
     * @return array 结果数组
     */
    public static function generateForDirectory($directory, $config = []) {
        require_once __DIR__ . '/FileScanner.php';
        
        $images = FileScanner::scanImages($directory);
        $sourcePaths = array_column($images, 'path');
        
        return self::generateBatch($sourcePaths, $config);
    }
    
    /**
     * 生成缩略图路径
     * 
     * @param string $sourcePath 源图片路径
     * @param array $config 配置选项
     * @return string
     */
    private static function generateThumbnailPath($sourcePath, $config) {
        $pathInfo = pathinfo($sourcePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = strtolower($config['format']);
        
        // 创建thumbs子目录
        $thumbsDir = $directory . '/thumbs';
        if (!is_dir($thumbsDir)) {
            mkdir($thumbsDir, 0755, true);
        }
        
        return $thumbsDir . '/' . $filename . $config['suffix'] . '.' . $extension;
    }
    
    /**
     * 获取缩略图路径（如果存在）
     * 
     * @param string $sourcePath 源图片路径
     * @param array $config 配置选项
     * @return string|false 缩略图路径或false
     */
    public static function getThumbnailPath($sourcePath, $config = []) {
        $config = array_merge(self::$defaultConfig, $config);
        $thumbnailPath = self::generateThumbnailPath($sourcePath, $config);
        
        return file_exists($thumbnailPath) ? $thumbnailPath : false;
    }
    
    /**
     * 清理旧的缩略图
     * 
     * @param string $directory 图片目录
     * @return int 删除的文件数量
     */
    public static function cleanOldThumbnails($directory) {
        $thumbsDir = $directory . '/thumbs';
        if (!is_dir($thumbsDir)) {
            return 0;
        }
        
        require_once __DIR__ . '/FileScanner.php';
        
        $sourceImages = FileScanner::scanImages($directory);
        $sourceFiles = array_map('basename', array_column($sourceImages, 'path'));
        
        $thumbnails = FileScanner::scanImages($thumbsDir);
        $deleted = 0;
        
        foreach ($thumbnails as $thumbnail) {
            $originalName = str_replace(self::$defaultConfig['suffix'], '', $thumbnail['basename']);
            $hasSource = false;
            
            foreach ($sourceFiles as $sourceFile) {
                if (pathinfo($sourceFile, PATHINFO_FILENAME) === $originalName) {
                    $hasSource = true;
                    break;
                }
            }
            
            if (!$hasSource) {
                unlink($thumbnail['path']);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}