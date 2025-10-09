<?php
// app/Config/ThumbnailConfig.php
// 缩略图配置管理 - 支持页面自定义

class ThumbnailConfig {
    
    /**
     * 获取页面自定义配置示例
     * 演示如何为不同页面配置不同的缩略图参数
     */
    public static function getCustomConfigs() {
        return [
            // Gallery页面 - 大尺寸高质量缩略图
            'gallery' => [
                'width' => 300,
                'height' => 300,
                'quality' => 85,
                'format' => 'jpg',
                'crop' => false,
                'suffix' => '_gallery',
                'directory' => 'thumbs'  // 标准目录
            ],
            
            // Single-Works页面 - 中等尺寸
            'single-works' => [
                'width' => 200,
                'height' => 200,
                'quality' => 80,
                'format' => 'jpg',
                'crop' => false,
                'suffix' => '_works',
                'directory' => 'works-thumbs'
            ],
            
            // Sketch 页面 - 统一使用新的非裁剪缩略图
            'sketchbook-thumb' => [
                'width' => 150,
                'height' => 150,
                'quality' => 85,
                'format' => 'webp',
                'crop' => false,
                'mode' => 'fit',
                'suffix' => '',
                'directory' => 'thumbs'
            ],
            
            // 图标用途 - 最小尺寸
            'icon' => [
                'width' => 100,
                'height' => 100,
                'quality' => 80,
                'format' => 'png',  // PNG保持透明度
                'crop' => true,
                'suffix' => '_icon',
                'directory' => 'icons'
            ],
            
            // 大预览图 - 高分辨率
            'large' => [
                'width' => 800,
                'height' => 600,
                'quality' => 90,
                'format' => 'jpg',
                'crop' => false,
                'suffix' => '_large',
                'directory' => 'large-previews'
            ],
            
            // 移动端专用 - 压缩优化
            'mobile' => [
                'width' => 120,
                'height' => 120,
                'quality' => 70,
                'format' => 'webp',  // 使用WebP格式减小文件大小
                'crop' => true,
                'suffix' => '_mobile',
                'directory' => 'mobile-thumbs'
            ],
            
            // 轮播图专用 - 16:9比例
            'carousel' => [
                'width' => 400,
                'height' => 225,  // 16:9 比例
                'quality' => 85,
                'format' => 'jpg',
                'crop' => true,
                'suffix' => '_carousel',
                'directory' => 'carousel-thumbs'
            ],
            
            // 列表视图 - 统一小图标
            'list' => [
                'width' => 80,
                'height' => 80,
                'quality' => 75,
                'format' => 'jpg',
                'crop' => true,
                'suffix' => '_list',
                'directory' => 'list-thumbs'
            ]
        ];
    }
    
    /**
     * 应用自定义配置到ThumbnailService
     */
    public static function applyCustomConfigs() {
        require_once __DIR__ . '/../Services/ThumbnailService.php';
        
        $configs = self::getCustomConfigs();
        
        foreach ($configs as $pageType => $config) {
            try {
                $existing = ThumbnailService::getConfig($pageType);

                // 内置配置不允许直接覆盖
                if ($existing && ($existing['builtin'] ?? false)) {
                    continue;
                }

                if ($existing) {
                    ThumbnailService::updateCustomConfig($pageType, $config);
                } else {
                    ThumbnailService::addCustomConfig($pageType, $config);
                }
            } catch (Throwable $e) {
                error_log('ThumbnailConfig::applyCustomConfigs error: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * 获取响应式配置 - 根据屏幕大小选择不同尺寸
     */
    public static function getResponsiveConfig($deviceType = 'desktop') {
        $responsiveConfigs = [
            'mobile' => [
                'width' => 120,
                'height' => 120,
                'quality' => 70,
                'directory' => 'mobile-thumbs'
            ],
            'tablet' => [
                'width' => 200,
                'height' => 200,
                'quality' => 75,
                'directory' => 'tablet-thumbs'
            ],
            'desktop' => [
                'width' => 300,
                'height' => 300,
                'quality' => 85,
                'directory' => 'desktop-thumbs'
            ]
        ];
        
        return $responsiveConfigs[$deviceType] ?? $responsiveConfigs['desktop'];
    }
}

// 使用示例：
/*

// 1. 基本使用 - 页面预设配置
ThumbnailService::generateForPage($imagePath, 'gallery');
ThumbnailService::generateForPage($imagePath, 'sketchbook-thumb');

// 2. 自定义配置
$customConfig = [
    'width' => 250,
    'height' => 250,
    'quality' => 90,
    'directory' => 'custom-thumbs'
];
ThumbnailService::generateForPage($imagePath, 'gallery', $customConfig);

// 3. 批量生成
ThumbnailService::generateBatchForPage($directoryPath, 'single-works');

// 4. 响应式配置
$mobileConfig = ThumbnailConfig::getResponsiveConfig('mobile');
ThumbnailService::generateForPage($imagePath, 'gallery', $mobileConfig);

// 5. 应用全局自定义配置
ThumbnailConfig::applyCustomConfigs();

*/