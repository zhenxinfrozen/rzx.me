<?php
// app/Utils/ImageProcessor.php
// 图片处理工具类 - 用于图片的基本处理操作

class ImageProcessor {
    
    /**
     * 获取图片信息
     * 
     * @param string $imagePath 图片路径
     * @return array|false 图片信息数组或false
     */
    public static function getImageInfo($imagePath) {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageInfo[2],
            'mime' => $imageInfo['mime'],
            'ratio' => $imageInfo[0] / $imageInfo[1],
            'orientation' => $imageInfo[0] > $imageInfo[1] ? 'landscape' : ($imageInfo[0] < $imageInfo[1] ? 'portrait' : 'square'),
            'file_size' => filesize($imagePath)
        ];
    }
    
    /**
     * 创建图片资源
     * 
     * @param string $imagePath 图片路径
     * @return \GdImage|resource|false
     */
    public static function createImageResource($imagePath) {
        $imageInfo = self::getImageInfo($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        switch ($imageInfo['type']) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($imagePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($imagePath);
            case IMAGETYPE_BMP:
                return imagecreatefrombmp($imagePath);
            default:
                return false;
        }
    }
    
    /**
     * 保存图片
     * 
     * @param \GdImage|resource $image 图片资源
     * @param string $outputPath 输出路径
     * @param string $format 输出格式 (jpg, png, webp)
     * @param int $quality 质量 (1-100)
     * @return bool
     */
    public static function saveImage($image, $outputPath, $format = 'jpg', $quality = 85) {
        // 创建输出目录（如果不存在）
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        switch (strtolower($format)) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $outputPath, $quality);
            case 'png':
                // PNG质量范围是0-9，需要转换
                $pngQuality = 9 - round(($quality / 100) * 9);
                return imagepng($image, $outputPath, $pngQuality);
            case 'webp':
                return imagewebp($image, $outputPath, $quality);
            default:
                return imagejpeg($image, $outputPath, $quality);
        }
    }
    
    /**
     * 计算缩放尺寸
     * 
     * @param int $originalWidth 原始宽度
     * @param int $originalHeight 原始高度  
     * @param int $maxWidth 最大宽度
     * @param int $maxHeight 最大高度
     * @param bool $crop 是否裁剪
     * @return array 新的尺寸 [width, height]
     */
    public static function calculateDimensions($originalWidth, $originalHeight, $maxWidth, $maxHeight, $crop = false, array $options = []) {
        $ratio = $originalWidth / $originalHeight;
        $mode = $options['mode'] ?? 'fit';

        if (!$crop && $mode === 'min_edge') {
            $targetMin = (int)($options['min_edge'] ?? min($maxWidth, $maxHeight));
            if ($targetMin <= 0) {
                $targetMin = min($maxWidth, $maxHeight);
            }

            $originalMin = min($originalWidth, $originalHeight);
            // 若原图较小则不放大
            if ($originalMin <= $targetMin) {
                return [$originalWidth, $originalHeight];
            }

            $scale = $targetMin / $originalMin;
            return [
                (int) round($originalWidth * $scale),
                (int) round($originalHeight * $scale)
            ];
        }
        
        if ($crop) {
            // 裁剪模式：填满目标尺寸
            $targetRatio = $maxWidth / $maxHeight;
            
            if ($ratio > $targetRatio) {
                // 原图更宽，按高度缩放
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            } else {
                // 原图更高，按宽度缩放
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            }
        } else {
            // 缩放模式：保持比例，完全显示
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                // 图片小于目标尺寸，不放大
                return [$originalWidth, $originalHeight];
            }
            
            if ($ratio > ($maxWidth / $maxHeight)) {
                // 按宽度缩放
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            } else {
                // 按高度缩放
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            }
        }
        
        return [round($newWidth), round($newHeight)];
    }
    
    /**
     * 检查GD库是否可用
     * 
     * @return bool
     */
    public static function isGdAvailable() {
        return extension_loaded('gd');
    }
    
    /**
     * 获取支持的图片格式
     * 
     * @return array
     */
    public static function getSupportedFormats() {
        $formats = [];
        
        if (function_exists('imagecreatefromjpeg')) {
            $formats[] = 'jpeg';
            $formats[] = 'jpg';
        }
        if (function_exists('imagecreatefrompng')) {
            $formats[] = 'png';
        }
        if (function_exists('imagecreatefromgif')) {
            $formats[] = 'gif';
        }
        if (function_exists('imagecreatefromwebp')) {
            $formats[] = 'webp';
        }
        if (function_exists('imagecreatefrombmp')) {
            $formats[] = 'bmp';
        }
        
        return $formats;
    }
}