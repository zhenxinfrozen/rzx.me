<?php
// app/Services/ThumbnailService.php
// 合并配置管理和缩略图服务

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
}
