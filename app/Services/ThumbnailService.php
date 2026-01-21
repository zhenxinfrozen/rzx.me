<?php
// app/Services/ThumbnailService.php
// 合并配置管理和缩略图服务

require_once __DIR__ . '/../Utils/ImageProcessor.php';

class ThumbnailService {
    private static $configFile = __DIR__ . '/../storage/data/thumbnail-configs.json';
    private static $builtinConfigs = [
        'gallery' => [
            'name' => 'Gallery页面',
            'width' => 300,
            'height' => 300,
            'quality' => 85,
            'format' => 'jpg',
            'crop' => false,
            'suffix' => '_gallery',
            'directory' => 'thumbs',
            'builtin' => true
        ],
        'gallery-standard' => [
            'name' => 'Gallery标准缩略图',
            'width' => 300,
            'height' => 300,
            'quality' => 85,
            'format' => 'jpg',
            'crop' => false,
            'suffix' => '',  // 无后缀，兼容原有逻辑
            'directory' => 'thumbs',
            'builtin' => true
        ],
        'gallery-icon' => [
            'name' => 'Gallery图标',
            'width' => 100,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'crop' => true,  // 图标通常需要裁剪成正方形
            'suffix' => '',  // 图标使用特殊命名规则
            'directory' => 'thumbs',
            'builtin' => true
        ],
        'single-works' => [
            'name' => 'Single-Works页面',
            'width' => 200,
            'height' => 200,
            'quality' => 80,
            'format' => 'jpg',
            'crop' => false,
            'suffix' => '_works',
            'directory' => 'thumbs',
            'builtin' => true
        ],
        'sketchbook-thumb' => [
            'name' => 'Sketchbook缩略图',
            'width' => 150,
            'height' => 150,
            'quality' => 85,
            'format' => 'webp',
            'crop' => false,
            'mode' => 'fit',
            'suffix' => '',
            'directory' => 'thumbs',
            'builtin' => true
        ],
        // 向后兼容旧的ID，行为与新的 sketchbook-thumb 保持一致
        'sketch' => [
            'name' => 'Sketch（兼容）',
            'width' => 150,
            'height' => 150,
            'quality' => 85,
            'format' => 'webp',
            'crop' => false,
            'mode' => 'fit',
            'suffix' => '',
            'directory' => 'thumbs',
            'builtin' => true
        ]
    ];

    public static function getAllConfigs() {
        $customConfigs = self::loadCustomConfigs();
        return array_merge(self::$builtinConfigs, $customConfigs);
    }
    public static function getConfig($configId) {
        $allConfigs = self::getAllConfigs();
        return $allConfigs[$configId] ?? null;
    }
    public static function getBuiltinConfigs() {
        return self::$builtinConfigs;
    }
    public static function getCustomConfigs() {
        return self::loadCustomConfigs();
    }
    public static function addCustomConfig($configId, $config) {
        $requiredFields = ['name', 'width', 'height', 'quality', 'format', 'suffix', 'directory'];
        foreach ($requiredFields as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                throw new Exception("配置字段 '{$field}' 是必需的");
            }
        }
        if (isset(self::$builtinConfigs[$configId])) {
            throw new Exception("配置ID '{$configId}' 与内置配置冲突");
        }
        $customConfigs = self::loadCustomConfigs();
        $config['builtin'] = false;
        $config['created_at'] = date('Y-m-d H:i:s');
        $customConfigs[$configId] = $config;
        return self::saveCustomConfigs($customConfigs);
    }
    public static function updateCustomConfig($configId, $config) {
        $customConfigs = self::loadCustomConfigs();
        if (!isset($customConfigs[$configId])) {
            throw new Exception("自定义配置 '{$configId}' 不存在");
        }
        $config['builtin'] = false;
        $config['created_at'] = $customConfigs[$configId]['created_at'] ?? date('Y-m-d H:i:s');
        $config['updated_at'] = date('Y-m-d H:i:s');
        $customConfigs[$configId] = $config;
        return self::saveCustomConfigs($customConfigs);
    }
    public static function deleteCustomConfig($configId) {
        if (isset(self::$builtinConfigs[$configId])) {
            throw new Exception("不能删除内置配置");
        }
        $customConfigs = self::loadCustomConfigs();
        if (!isset($customConfigs[$configId])) {
            throw new Exception("自定义配置 '{$configId}' 不存在");
        }
        unset($customConfigs[$configId]);
        return self::saveCustomConfigs($customConfigs);
    }
    private static function loadCustomConfigs() {
        if (!file_exists(self::$configFile)) {
            return [];
        }
        $content = file_get_contents(self::$configFile);
        $configs = json_decode($content, true);
        return is_array($configs) ? $configs : [];
    }
    private static function saveCustomConfigs($configs) {
        $configDir = dirname(self::$configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        $content = json_encode($configs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents(self::$configFile, $content) !== false;
    }

    // 兼容旧接口（demo页面用到了）
    public static function getAllPageConfigs() {
        return self::getAllConfigs();
    }

    // ================= 生成相关（用于测试与批量任务） =================

    /**
     * 生成缩略图（根据配置ID或直接传配置数组）
     * 返回统一结果结构：
     * - success: bool
     * - original_path: string
     * - output_path: string|null
     * - config_used: array
     * - error: string|null
     */
    public static function generate($sourcePath, $configIdOrArray) {
        $config = is_array($configIdOrArray)
            ? $configIdOrArray
            : self::getConfig($configIdOrArray);

        if (!$config) {
            return [
                'success' => false,
                'original_path' => $sourcePath,
                'output_path' => null,
                'config_used' => null,
                'error' => '配置不存在'
            ];
        }

        if (!file_exists($sourcePath)) {
            return [
                'success' => false,
                'original_path' => $sourcePath,
                'output_path' => null,
                'config_used' => $config,
                'error' => '源文件不存在'
            ];
        }

        // 计算输出路径（使用配置中的目录与后缀、格式）
        $outputPath = self::buildOutputPath($sourcePath, $config);

        // 确保目录
        $outDir = dirname($outputPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $genConfig = [
            'width' => (int)($config['width'] ?? 200),
            'height' => (int)($config['height'] ?? 200),
            'quality' => (int)($config['quality'] ?? 80),
            'format' => strtolower($config['format'] ?? 'jpg'),
            'crop' => (bool)($config['crop'] ?? false),
            'mode' => $config['mode'] ?? 'fit',
            'min_edge' => $config['min_edge'] ?? null,
            // ThumbnailGenerator 仍然会在文件名上使用 suffix，我们这里传递，便于其行为一致
            'suffix' => $config['suffix'] ?? '_thumb',
        ];

        $resultPath = self::generateThumbnailInternal($sourcePath, $outputPath, $genConfig);

        if ($resultPath === false) {
            return [
                'success' => false,
                'original_path' => $sourcePath,
                'output_path' => $outputPath,
                'config_used' => $genConfig,
                'error' => '生成失败（请检查GD库或图片格式）'
            ];
        }

        return [
            'success' => true,
            'original_path' => $sourcePath,
            'output_path' => $resultPath,
            'config_used' => array_merge($config, [
                'format' => $genConfig['format'],
                'suffix' => $genConfig['suffix']
            ]),
            'file_size' => @filesize($resultPath) ?: null,
        ];
    }

    /**
     * 仅计算输出路径（不生成）
     */
    public static function getOutputPath($sourcePath, $configIdOrArray) {
        $config = is_array($configIdOrArray)
            ? $configIdOrArray
            : self::getConfig($configIdOrArray);
        if (!$config) return null;
        return self::buildOutputPath($sourcePath, $config);
    }

    /**
     * 构建输出路径（基于配置的目录/后缀/格式）
     */
    private static function buildOutputPath($sourcePath, $config) {
        $info = pathinfo($sourcePath);
        $dir = $info['dirname'];
        $filename = $info['filename'];
        $targetDir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ($config['directory'] ?? 'thumbs');
        $ext = strtolower($config['format'] ?? 'jpg');
        $suffix = $config['suffix'] ?? '_thumb';
        return $targetDir . DIRECTORY_SEPARATOR . $filename . $suffix . '.' . $ext;
    }

    /**
     * 生成单个缩略图文件
     * @param string $sourcePath 源文件路径
     * @param string $targetPath 目标文件路径
     * @param string $configId 配置ID
     * @return bool 生成是否成功
     */
    public static function generateSingle($sourcePath, $targetPath, $configId) {
        $config = self::getConfig($configId);
        if (!$config) {
            throw new Exception("缩略图配置 '{$configId}' 不存在");
        }

        $options = [
            'width' => $config['width'],
            'height' => $config['height'],
            'quality' => $config['quality'],
            'crop' => $config['crop'] ?? false,
            'format' => $config['format']
        ];

        return self::generateThumbnailInternal($sourcePath, $targetPath, $options) !== false;
    }

    /**
     * 批量为页面生成缩略图（向后兼容方法）
     * @param string $directoryPath 目录路径
     * @param string $pageType 页面类型 (single-works, gallery, etc.)
     */
    public static function generateBatchForPage($directoryPath, $pageType = 'single-works') {
        // 获取页面对应的配置
        $pageConfigs = self::getAllPageConfigs();
        $configId = isset($pageConfigs[$pageType]) ? $pageType : 'single-works';

        // 扫描目录中的图片文件
        if (!is_dir($directoryPath)) {
            return;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($directoryPath);

        foreach ($files as $file) {
            $filePath = $directoryPath . '/' . $file;
            if (is_file($filePath)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExtensions)) {
                    try {
                        self::generate($filePath, $configId);
                    } catch (Exception $e) {
                        // 忽略单个文件的错误，继续处理其他文件
                        error_log("Thumbnail generation failed for {$filePath}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * 内部缩略图生成方法（从ThumbnailGenerator集成）
     */
    private static function generateThumbnailInternal($sourcePath, $thumbnailPath, $config = []) {
        // 默认配置
        $defaultConfig = [
            'width' => 200,
            'height' => 200,
            'quality' => 80,
            'format' => 'jpg',
            'crop' => false,
            'mode' => 'fit',
            'min_edge' => null,
        ];

        $config = array_merge($defaultConfig, $config);

        // 检查GD库
        if (!ImageProcessor::isGdAvailable()) {
            error_log("ThumbnailService: GD library not available");
            return false;
        }

        // 检查源文件
        if (!file_exists($sourcePath)) {
            error_log("ThumbnailService: Source file not found: $sourcePath");
            return false;
        }

        // 如果缩略图已存在且比源文件新，跳过生成
        if (file_exists($thumbnailPath) && filemtime($thumbnailPath) >= filemtime($sourcePath)) {
            return $thumbnailPath;
        }

        try {
            // 获取源图片信息
            $imageInfo = ImageProcessor::getImageInfo($sourcePath);
            if (!$imageInfo) {
                error_log("ThumbnailService: Cannot get image info for: $sourcePath");
                return false;
            }

            // 创建源图片资源
            $sourceImage = ImageProcessor::createImageResource($sourcePath);
            if (!$sourceImage) {
                error_log("ThumbnailService: Cannot create image resource for: $sourcePath");
                return false;
            }

            // 计算缩略图尺寸
            list($thumbWidth, $thumbHeight) = ImageProcessor::calculateDimensions(
                $imageInfo['width'],
                $imageInfo['height'],
                $config['width'],
                $config['height'],
                $config['crop'],
                [
                    'mode' => $config['mode'] ?? 'fit',
                    'min_edge' => $config['min_edge'] ?? null,
                ]
            );

            // 裁剪模式需要使用目标尺寸创建画布
            if ($config['crop']) {
                $thumbnailImage = imagecreatetruecolor($config['width'], $config['height']);
            } else {
                $thumbnailImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
            }

            // 处理背景色和透明度
            if ($imageInfo['type'] == IMAGETYPE_PNG || $imageInfo['type'] == IMAGETYPE_GIF) {
                // PNG/GIF 保持透明度
                imagealphablending($thumbnailImage, false);
                imagesavealpha($thumbnailImage, true);
                $transparent = imagecolorallocatealpha($thumbnailImage, 255, 255, 255, 127);
                imagefill($thumbnailImage, 0, 0, $transparent);
            } else {
                // JPG 使用白色背景
                imagealphablending($thumbnailImage, true);
                $white = imagecolorallocate($thumbnailImage, 255, 255, 255);
                imagefill($thumbnailImage, 0, 0, $white);
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
            error_log("ThumbnailService: Error generating thumbnail - " . $e->getMessage());
            return false;
        }
    }
}
