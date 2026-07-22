<?php
/**
 * 视频缩略图生成工具
 * 使用FFmpeg从视频中提取缩略图
 */

class VideoThumbnailGenerator {
    
    /**
     * 获取支持的视频格式列表
     * 
     * @return array
     */
    public static function getSupportedFormats() {
        static $formats = null;
        
        if ($formats === null) {
            $configFile = __DIR__ . '/../Config/video_formats.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                $formats = $config['extensions'] ?? [];
            } else {
                // 默认格式列表
                $formats = ['mp4', 'mkv', 'webm', 'avi', 'mov', 'flv', 'm4v', 'wmv', 'mpg', 'mpeg', 'ogv',
                           'MP4', 'MKV', 'WEBM', 'AVI', 'MOV', 'FLV', 'M4V', 'WMV', 'MPG', 'MPEG', 'OGV'];
            }
        }
        
        return $formats;
    }
    
    /**
     * 检查FFmpeg是否可用
     * 
     * @return bool
     */
    public static function isFFmpegAvailable() {
        // Windows系统
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where ffmpeg 2>nul', $output, $returnCode);
        } else {
            // Linux/Mac系统
            exec('which ffmpeg 2>/dev/null', $output, $returnCode);
        }
        
        return $returnCode === 0;
    }
    
    /**
     * 获取视频时长（秒）
     * 
     * @param string $videoPath 视频文件路径
     * @return float|false 视频时长（秒），失败返回false
     */
    public static function getVideoDuration($videoPath) {
        if (!self::isFFmpegAvailable() || !file_exists($videoPath)) {
            return false;
        }
        
        // 使用ffprobe获取视频时长
        $command = sprintf(
            'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($videoPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output[0])) {
            return (float)$output[0];
        }
        
        return false;
    }
    
    /**
     * 获取视频的自定义缩略图提取位置
     * 
     * @param string $videoBaseName 视频文件名（不含扩展名）
     * @return int|null 自定义位置（秒），如果没有自定义配置返回null
     */
    private static function getCustomThumbnailPosition($videoBaseName) {
        $configFile = __DIR__ . '/../Config/video_thumbnail_overrides.php';
        
        if (!file_exists($configFile)) {
            return null;
        }
        
        $overrides = require $configFile;
        
        if (!isset($overrides[$videoBaseName])) {
            return null;
        }
        
        $config = $overrides[$videoBaseName];
        
        // 如果配置是秒数类型，直接返回
        if ($config['type'] === 'seconds') {
            return (int)$config['position'];
        }
        
        // 如果是百分比类型，需要先获取视频时长
        if ($config['type'] === 'percentage') {
            // 这里暂时返回null，让调用者自己计算
            // 因为这个方法不知道视频路径
            return null;
        }
        
        return null;
    }
    
    /**
     * 智能提取缩略图（自动跳过黑屏/淡入帧）
     * 
     * @param string $videoPath 视频文件的绝对路径
     * @param string $outputPath 输出缩略图的绝对路径
     * @param int $width 缩略图宽度（默认320）
     * @return bool 是否成功
     */
    public static function generateSmartThumbnail($videoPath, $outputPath, $width = 320) {
        if (!self::isFFmpegAvailable()) {
            error_log('FFmpeg未安装或不可用');
            return false;
        }
        
        if (!file_exists($videoPath)) {
            error_log('视频文件不存在: ' . $videoPath);
            return false;
        }
        
        // 检查是否有自定义配置
        $baseName = pathinfo($videoPath, PATHINFO_FILENAME);
        $customPosition = self::getCustomThumbnailPosition($baseName);
        
        if ($customPosition !== null) {
            // 使用自定义配置
            return self::generateThumbnail($videoPath, $outputPath, $customPosition, $width);
        }
        
        // 获取视频时长
        $duration = self::getVideoDuration($videoPath);
        
        // 根据视频时长智能选择提取时间点
        // 策略：避开开头的黑屏/淡入，选择视频的前1/4位置
        if ($duration !== false && $duration > 0) {
            // 如果视频超过4秒，在25%位置提取（跳过开头）
            if ($duration > 4) {
                $timeInSeconds = (int)($duration * 0.25);
            }
            // 如果视频2-4秒，在第2秒提取
            elseif ($duration > 2) {
                $timeInSeconds = 2;
            }
            // 如果视频很短，在一半位置提取
            else {
                $timeInSeconds = (int)($duration * 0.5);
            }
        } else {
            // 无法获取时长，默认从第3秒提取（通常能跳过开头黑屏）
            $timeInSeconds = 3;
        }
        
        // 确保至少是1秒
        $timeInSeconds = max(1, $timeInSeconds);
        
        return self::generateThumbnail($videoPath, $outputPath, $timeInSeconds, $width);
    }
    
    /**
     * 从视频中提取缩略图
     * 
     * @param string $videoPath 视频文件的绝对路径
     * @param string $outputPath 输出缩略图的绝对路径
     * @param int $timeInSeconds 在第几秒截取（默认第3秒，跳过开头黑屏）
     * @param int $width 缩略图宽度（默认320）
     * @return bool 是否成功
     */
    public static function generateThumbnail($videoPath, $outputPath, $timeInSeconds = 3, $width = 320) {
        if (!self::isFFmpegAvailable()) {
            error_log('FFmpeg未安装或不可用');
            return false;
        }
        
        if (!file_exists($videoPath)) {
            error_log('视频文件不存在: ' . $videoPath);
            return false;
        }
        
        // 确保输出目录存在
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // 构建FFmpeg命令
        // -ss: 指定时间点
        // -i: 输入文件
        // -vframes 1: 只提取一帧
        // -vf scale: 缩放到指定宽度，高度自动计算保持比例
        // -q:v 2: 质量设置（1-31，数字越小质量越高）
        
        // Windows下处理特殊字符路径：使用双引号而不是escapeshellarg
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = sprintf(
                'ffmpeg -ss %d -i "%s" -vframes 1 -vf "scale=%d:-1" -q:v 2 "%s" 2>&1',
                $timeInSeconds,
                $videoPath,
                $width,
                $outputPath
            );
        } else {
            // Linux/Mac使用escapeshellarg
            $command = sprintf(
                'ffmpeg -ss %d -i %s -vframes 1 -vf "scale=%d:-1" -q:v 2 %s 2>&1',
                $timeInSeconds,
                escapeshellarg($videoPath),
                $width,
                escapeshellarg($outputPath)
            );
        }
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            error_log('FFmpeg执行失败: ' . implode("\n", $output));
            return false;
        }
        
        return file_exists($outputPath);
    }
    
    /**
     * 复制 FFmpeg 缺失提示占位图
     * 
     * @param string $outputPath 输出路径
     * @return bool
     */
    public static function copyPlaceholderThumbnail($outputPath) {
        $placeholderPath = __DIR__ . '/../../public/assets/images/ffmpeg-required-placeholder.jpg';
        
        // 如果占位图不存在，记录错误并返回失败
        if (!file_exists($placeholderPath)) {
            error_log("缺少 FFmpeg 占位图: {$placeholderPath}");
            return false;
        }
        
        // 复制占位图到目标位置
        return copy($placeholderPath, $outputPath);
    }
    
    /**
     * 为视频生成缩略图（仅使用 FFmpeg，不降级为默认图）
     * 
     * @param string $videoPath 视频文件绝对路径
     * @param string $outputPath 输出缩略图绝对路径
     * @param string $videoTitle 视频标题（用于日志）
     * @param bool $useSmartExtraction 是否使用智能提取（默认true，自动跳过黑屏）
     * @return bool
     */
    public static function generateOrDefault($videoPath, $outputPath, $videoTitle = 'Video', $useSmartExtraction = true) {
        // 如果缩略图已存在，不重复生成
        if (file_exists($outputPath)) {
            return true;
        }
        
        // 检查 FFmpeg 是否可用
        if (!self::isFFmpegAvailable()) {
            error_log("[VideoThumbnail] FFmpeg 不可用，使用占位图: {$videoTitle}");
            return self::copyPlaceholderThumbnail($outputPath);
        }
        
        // 尝试使用 FFmpeg 提取
        $success = false;
        if ($useSmartExtraction) {
            $success = self::generateSmartThumbnail($videoPath, $outputPath);
        } else {
            $success = self::generateThumbnail($videoPath, $outputPath);
        }
        
        // 如果 FFmpeg 提取失败，使用占位图
        if (!$success) {
            error_log("[VideoThumbnail] FFmpeg 提取失败，使用占位图: {$videoTitle}");
            return self::copyPlaceholderThumbnail($outputPath);
        }
        
        return true;
    }
}
