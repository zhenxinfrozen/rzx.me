<?php

namespace App\Admin\Services\Media;

/**
 * 图片处理服务
 * 负责：图片上传、缩略图生成、图片验证
 */
class ImageService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const MAX_SIZE = 50 * 1024 * 1024; // 50MB

    /**
     * 上传图片到指定分类
     */
    public function uploadImages(string $categoryPath, array $files): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($files['name'] as $index => $filename) {
            try {
                if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('上传失败');
                }

                if ($files['size'][$index] > self::MAX_SIZE) {
                    throw new \RuntimeException('文件大小超过50MB限制');
                }

                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    throw new \RuntimeException('不支持的图片格式');
                }

                $safeName = $this->sanitizeFilename($filename);
                $targetPath = $categoryPath . '/' . $safeName;

                if (!move_uploaded_file($files['tmp_name'][$index], $targetPath)) {
                    throw new \RuntimeException('保存文件失败');
                }

                // 生成缩略图
                $this->generateThumbnail($targetPath, $categoryPath);

                $results['success'][] = [
                    'original' => $filename,
                    'saved' => $safeName,
                    'size' => $files['size'][$index]
                ];

            } catch (\Throwable $e) {
                $results['failed'][] = [
                    'filename' => $filename,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * 生成图片缩略图
     */
    public function generateThumbnail(string $sourcePath, string $categoryPath): bool
    {
        try {
            $thumbDir = $categoryPath . '/thumbs';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            $filename = basename($sourcePath);
            $destPath = $thumbDir . '/' . $filename;

            return $this->createThumbnailImage($sourcePath, $destPath, 400, 400);

        } catch (\Throwable $e) {
            error_log("缩略图生成失败: {$sourcePath} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * 使用GD库创建缩略图
     */
    private function createThumbnailImage(string $source, string $dest, int $maxW, int $maxH): bool
    {
        $imageInfo = @getimagesize($source);
        if (!$imageInfo) {
            return false;
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_GIF => @imagecreatefromgif($source),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default => false
        };

        if (!$sourceImage) {
            return false;
        }

        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);

        $ratio = min($maxW / $srcW, $maxH / $srcH, 1);
        $thumbW = (int) round($srcW * $ratio);
        $thumbH = (int) round($srcH * $ratio);

        $thumbImage = imagecreatetruecolor($thumbW, $thumbH);

        // 处理透明背景
        if (in_array($imageInfo[2], [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbW, $thumbH, $transparent);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbW, $thumbH, $srcW, $srcH);

        $result = imagejpeg($thumbImage, $dest, 85);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        return (bool) $result;
    }

    /**
     * 上传自定义缩略图
     */
    public function uploadCustomThumbnail(string $categoryPath, array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('缩略图上传失败');
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            throw new \RuntimeException('缩略图不能超过10MB');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \RuntimeException('不支持的图片格式');
        }

        $thumbDir = $categoryPath . '/thumbs';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $filename = 'custom-thumb-' . date('Ymd-His') . '.' . $extension;
        $targetPath = $thumbDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('保存缩略图失败');
        }

        return [
            'filename' => $filename,
            'path' => $targetPath
        ];
    }

    /**
     * 删除自定义缩略图
     */
    public function deleteCustomThumbnail(string $thumbnailPath): bool
    {
        if (file_exists($thumbnailPath) && strpos($thumbnailPath, 'custom-thumb-') !== false) {
            return @unlink($thumbnailPath);
        }
        return false;
    }

    /**
     * 清理文件名
     */
    private function sanitizeFilename(string $filename): string
    {
        $info = pathinfo($filename);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['filename']);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');

        if (empty($name)) {
            $name = 'image_' . time();
        }

        return $name . '.' . $info['extension'];
    }

    /**
     * 验证是否为图片文件
     */
    public function isImageFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::ALLOWED_EXTENSIONS);
    }

    /**
     * 获取图片文件列表
     */
    public function getImageFiles(string $categoryPath): array
    {
        if (!is_dir($categoryPath)) {
            return [];
        }

        $pattern = $categoryPath . '/*.{' . implode(',', self::ALLOWED_EXTENSIONS) . '}';
        $files = glob($pattern, GLOB_BRACE);

        return array_map('basename', $files ?: []);
    }
}
