<?php

namespace App\Admin\Services\Media;

/**
 * 视频处理服务
 * 负责：视频上传、FFmpeg缩略图生成、视频验证
 */
class VideoService
{
    private const ALLOWED_EXTENSIONS = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
    private const MAX_SIZE = 200 * 1024 * 1024; // 200MB

    private string $ffmpegPath;

    public function __construct(string $ffmpegPath = 'ffmpeg')
    {
        $this->ffmpegPath = $ffmpegPath;
    }

    /**
     * 上传视频到指定分类
     */
    public function uploadVideos(string $categoryPath, array $files): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($files['name'] as $index => $filename) {
            try {
                if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('上传失败');
                }

                if ($files['size'][$index] > self::MAX_SIZE) {
                    throw new \RuntimeException('文件大小超过200MB限制');
                }

                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    throw new \RuntimeException('不支持的视频格式');
                }

                $safeName = $this->sanitizeFilename($filename);
                $targetPath = $categoryPath . '/' . $safeName;

                if (!move_uploaded_file($files['tmp_name'][$index], $targetPath)) {
                    throw new \RuntimeException('保存文件失败');
                }

                // 生成视频缩略图
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
     * 生成视频缩略图（使用FFmpeg）
     */
    public function generateThumbnail(string $videoPath, string $categoryPath): bool
    {
        try {
            $thumbDir = $categoryPath . '/thumbs';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            $filename = basename($videoPath);
            $thumbName = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
            $thumbPath = $thumbDir . '/' . $thumbName;

            return $this->extractVideoFrame($videoPath, $thumbPath);

        } catch (\Throwable $e) {
            error_log("视频缩略图生成失败: {$videoPath} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * 使用FFmpeg提取视频帧
     */
    private function extractVideoFrame(string $videoPath, string $thumbPath): bool
    {
        if (!$this->isFfmpegAvailable()) {
            error_log("FFmpeg不可用，无法生成视频缩略图");
            return false;
        }

        $command = sprintf(
            '%s -i %s -ss 00:00:01.000 -vframes 1 -vf "scale=640:-1" -q:v 2 %s 2>&1',
            $this->ffmpegPath,
            escapeshellarg($videoPath),
            escapeshellarg($thumbPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            error_log("FFmpeg执行失败: " . implode("\n", $output));
            return false;
        }

        return file_exists($thumbPath) && filesize($thumbPath) > 0;
    }

    /**
     * 检查FFmpeg是否可用
     */
    public function isFfmpegAvailable(): bool
    {
        $command = $this->ffmpegPath . ' -version 2>&1';
        exec($command, $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * 获取视频信息（时长、分辨率等）
     */
    public function getVideoInfo(string $videoPath): ?array
    {
        if (!$this->isFfmpegAvailable()) {
            return null;
        }

        $command = sprintf(
            '%s -i %s 2>&1',
            $this->ffmpegPath,
            escapeshellarg($videoPath)
        );

        exec($command, $output);
        $outputStr = implode("\n", $output);

        $info = [];

        // 提取时长
        if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})/', $outputStr, $matches)) {
            $info['duration'] = [
                'hours' => (int) $matches[1],
                'minutes' => (int) $matches[2],
                'seconds' => (int) $matches[3],
                'total_seconds' => (int) $matches[1] * 3600 + (int) $matches[2] * 60 + (int) $matches[3]
            ];
        }

        // 提取分辨率
        if (preg_match('/Stream.*Video.*?(\d{3,5})x(\d{3,5})/', $outputStr, $matches)) {
            $info['resolution'] = [
                'width' => (int) $matches[1],
                'height' => (int) $matches[2]
            ];
        }

        // 提取比特率
        if (preg_match('/bitrate: (\d+) kb\/s/', $outputStr, $matches)) {
            $info['bitrate'] = (int) $matches[1];
        }

        return !empty($info) ? $info : null;
    }

    /**
     * 删除视频及其缩略图
     */
    public function deleteVideoWithThumbnail(string $videoPath): bool
    {
        $deleted = false;

        // 删除视频文件
        if (file_exists($videoPath)) {
            $deleted = @unlink($videoPath);
        }

        // 删除对应的缩略图
        $thumbPath = $this->getVideoThumbnailPath($videoPath);
        if (file_exists($thumbPath)) {
            @unlink($thumbPath);
        }

        return $deleted;
    }

    /**
     * 获取视频缩略图路径
     */
    public function getVideoThumbnailPath(string $videoPath): string
    {
        $dir = dirname($videoPath);
        $filename = pathinfo($videoPath, PATHINFO_FILENAME);
        return $dir . '/thumbs/' . $filename . '.jpg';
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
            $name = 'video_' . time();
        }

        return $name . '.' . $info['extension'];
    }

    /**
     * 验证是否为视频文件
     */
    public function isVideoFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::ALLOWED_EXTENSIONS);
    }

    /**
     * 获取视频文件列表
     */
    public function getVideoFiles(string $categoryPath): array
    {
        if (!is_dir($categoryPath)) {
            return [];
        }

        $pattern = $categoryPath . '/*.{' . implode(',', self::ALLOWED_EXTENSIONS) . '}';
        $files = glob($pattern, GLOB_BRACE);

        return array_map('basename', $files ?: []);
    }
}
