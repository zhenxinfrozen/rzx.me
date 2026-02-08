<?php

namespace App\Admin\Services\Media;

/**
 * 配置管理服务
 * 负责：模块配置读写、排序配置、显示名称、描述等
 */
class ConfigService
{
    /**
     * 加载模块配置
     */
    public function loadConfig(string $configPath): array
    {
        if (file_exists($configPath)) {
            $json = file_get_contents($configPath);
            $config = json_decode($json, true);
            if (is_array($config)) {
                return $config;
            }
        }

        return $this->getDefaultConfig();
    }

    /**
     * 保存模块配置
     */
    public function saveConfig(string $configPath, array $config): bool
    {
        $dir = dirname($configPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config['_updated'] = date('c');

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (bool) file_put_contents($configPath, $json, LOCK_EX);
    }

    /**
     * 获取默认配置结构
     */
    public function getDefaultConfig(): array
    {
        return [
            'sort_method' => 'custom_order',
            'custom_order' => [],
            'display_names' => [],
            'descriptions' => [],
            'category_thumbnails' => [],
            '_created' => date('c')
        ];
    }

    /**
     * 加载图片排序配置
     */
    public function loadOrderConfig(string $orderPath): array
    {
        if (!file_exists($orderPath)) {
            return [
                '_comment' => 'Media files order configuration',
                '_generated' => date('c'),
                'modules' => []
            ];
        }

        $content = file_get_contents($orderPath);
        $config = json_decode($content, true);

        return is_array($config) ? $config : [
            '_comment' => 'Media files order configuration',
            '_generated' => date('c'),
            'modules' => []
        ];
    }

    /**
     * 保存图片排序配置
     */
    public function saveOrderConfig(string $orderPath, array $config): bool
    {
        $dir = dirname($orderPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config['_generated'] = date('c');

        $content = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return file_put_contents($orderPath, $content, LOCK_EX) !== false;
    }

    /**
     * 获取分类媒体文件的排序列表
     */
    public function getCategoryMediaOrder(string $module, string $category, string $orderPath, array $allFiles): array
    {
        $orderConfig = $this->loadOrderConfig($orderPath);
        $savedOrder = $orderConfig['modules'][$module]['categories'][$category] ?? [];

        if (empty($savedOrder)) {
            sort($allFiles, SORT_NATURAL | SORT_FLAG_CASE);
            return $allFiles;
        }

        $orderedFiles = [];
        $remainingFiles = $allFiles;

        foreach ($savedOrder as $filename) {
            if (in_array($filename, $allFiles)) {
                $orderedFiles[] = $filename;
                $remainingFiles = array_diff($remainingFiles, [$filename]);
            }
        }

        if (!empty($remainingFiles)) {
            sort($remainingFiles, SORT_NATURAL | SORT_FLAG_CASE);
            $orderedFiles = array_merge($orderedFiles, $remainingFiles);
        }

        return $orderedFiles;
    }

    /**
     * 保存分类媒体排序
     */
    public function saveCategoryMediaOrder(string $module, string $category, array $order, string $orderPath): bool
    {
        $orderConfig = $this->loadOrderConfig($orderPath);
        $orderConfig['modules'][$module]['categories'][$category] = array_values($order);
        return $this->saveOrderConfig($orderPath, $orderConfig);
    }

    /**
     * 从排序配置中移除文件
     */
    public function removeFromMediaOrder(string $module, string $category, string $filename, string $orderPath): void
    {
        $orderConfig = $this->loadOrderConfig($orderPath);

        if (isset($orderConfig['modules'][$module]['categories'][$category])) {
            $orderConfig['modules'][$module]['categories'][$category] = array_values(
                array_filter(
                    $orderConfig['modules'][$module]['categories'][$category],
                    fn($file) => $file !== $filename
                )
            );
            $this->saveOrderConfig($orderPath, $orderConfig);
        }
    }

    /**
     * 设置分类显示名称
     */
    public function setDisplayName(string $configPath, string $category, string $displayName): bool
    {
        $config = $this->loadConfig($configPath);
        $config['display_names'][$category] = $displayName;
        return $this->saveConfig($configPath, $config);
    }

    /**
     * 设置分类描述
     */
    public function setDescription(string $configPath, string $category, string $description): bool
    {
        $config = $this->loadConfig($configPath);
        $config['descriptions'][$category] = $description;
        return $this->saveConfig($configPath, $config);
    }

    /**
     * 设置分类缩略图
     */
    public function setCategoryThumbnail(string $configPath, string $category, string $thumbnailUrl): bool
    {
        $config = $this->loadConfig($configPath);
        $config['category_thumbnails'][$category] = $thumbnailUrl;
        return $this->saveConfig($configPath, $config);
    }

    /**
     * 删除分类缩略图
     */
    public function removeCategoryThumbnail(string $configPath, string $category): bool
    {
        $config = $this->loadConfig($configPath);
        unset($config['category_thumbnails'][$category]);
        return $this->saveConfig($configPath, $config);
    }

    /**
     * 保存分类排序
     */
    public function saveCategoryOrder(string $configPath, array $order): bool
    {
        $config = $this->loadConfig($configPath);
        $config['custom_order'] = array_values($order);
        return $this->saveConfig($configPath, $config);
    }

    /**
     * 重命名分类（更新所有相关配置）
     */
    public function renameCategory(string $configPath, string $oldName, string $newName): bool
    {
        $config = $this->loadConfig($configPath);

        // 更新排序
        if (isset($config['custom_order'])) {
            $config['custom_order'] = array_map(
                fn($item) => $item === $oldName ? $newName : $item,
                $config['custom_order']
            );
        }

        // 更新显示名称
        if (isset($config['display_names'][$oldName])) {
            $config['display_names'][$newName] = $config['display_names'][$oldName];
            unset($config['display_names'][$oldName]);
        }

        // 更新描述
        if (isset($config['descriptions'][$oldName])) {
            $config['descriptions'][$newName] = $config['descriptions'][$oldName];
            unset($config['descriptions'][$oldName]);
        }

        // 更新缩略图
        if (isset($config['category_thumbnails'][$oldName])) {
            $oldThumb = $config['category_thumbnails'][$oldName];
            $newThumb = str_replace("/{$oldName}/", "/{$newName}/", $oldThumb);
            $config['category_thumbnails'][$newName] = $newThumb;
            unset($config['category_thumbnails'][$oldName]);
        }

        return $this->saveConfig($configPath, $config);
    }
}
