<?php
// app/Services/ThumbnailService.php
// 合并配置管理和缩略图服务

require_once __DIR__ . '/../Utils/ThumbnailGenerator.php';

class ThumbnailService {
    private static $configFile = __DIR__ . '/../Data/thumbnail_configs.json';
    private static $builtinConfigs = [
        'gallery' => [
            'name' => 'Gallery页面',
            'width' => 300,
            'height' => 300,
            'quality' => 85,
            'format' => 'jpg',
            'crop' => false,
            'suffix' => '_gallery',
            'directory' => 'gallery-thumbs',
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
            'directory' => 'works-thumbs',
            'builtin' => true
        ],
        'sketch' => [
            'name' => 'Sketch页面',
            'width' => 150,
            'height' => 150,
            'quality' => 75,
            'format' => 'jpg',
            'crop' => true,
            'suffix' => '_sketch',
            'directory' => 'sketch-thumbs',
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
            // ThumbnailGenerator 仍然会在文件名上使用 suffix，我们这里传递，便于其行为一致
            'suffix' => $config['suffix'] ?? '_thumb',
        ];

        $resultPath = ThumbnailGenerator::generate($sourcePath, $outputPath, $genConfig);

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
}
