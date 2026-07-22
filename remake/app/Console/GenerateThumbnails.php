<?php
/**
 * 统一缩略图生成命令行工具
 * 整合原有的 regenerate-thumbs.php 和 tools/generate_thumbs.php 功能
 * 支持所有Gallery类型，不仅限于single-works
 */

class GenerateThumbnails
{
    private $galleryManager;
    
    public function __construct()
    {
        require_once __DIR__ . '/../Utils/GalleryManager.php';
        $this->galleryManager = new GalleryManager();
    }
    
    /**
     * 主执行方法
     * @param array $args 命令行参数
     */
    public function run($args = [])
    {
        $this->printHeader();
        
        // 解析参数
        $options = $this->parseArguments($args);
        
        if (isset($options['help']) || empty($options)) {
            $this->showHelp();
            return;
        }
        
        if (isset($options['gallery'])) {
            // 处理指定的gallery
            $this->processGallery($options['gallery'], $options);
        } else {
            // 处理所有gallery
            $this->processAllGalleries($options);
        }
        
        echo "\n🎉 缩略图生成任务完成！\n";
    }
    
    /**
     * 处理指定的gallery
     */
    private function processGallery($gallery, $options)
    {
        echo "📁 处理Gallery: $gallery\n";
        echo str_repeat('─', 50) . "\n";
        
        if (strpos($gallery, '/') !== false) {
            // 处理特定分类 (如: single-works/Animals)
            $result = $this->galleryManager->generateThumbnails($gallery);
            $this->reportResults($gallery, $result);
        } else {
            // 处理整个gallery的所有分类
            $categories = $this->galleryManager->getGalleryCategories($gallery);
            
            if (empty($categories)) {
                echo "⚠️  Gallery '$gallery' 不存在或没有分类\n";
                return;
            }
            
            foreach ($categories as $category) {
                $fullPath = $gallery . '/' . $category;
                echo "\n🔸 处理分类: $category\n";
                
                $result = $this->galleryManager->generateThumbnails($fullPath);
                $this->reportResults($fullPath, $result);
            }
        }
    }
    
    /**
     * 处理所有gallery
     */
    private function processAllGalleries($options)
    {
        echo "🌟 处理所有Gallery...\n";
        echo str_repeat('─', 50) . "\n";
        
        // 扫描所有gallery
        $galleries = $this->scanAllGalleries();
        
        if (empty($galleries)) {
            echo "⚠️  未找到任何Gallery目录\n";
            return;
        }
        
        foreach ($galleries as $gallery) {
            $this->processGallery($gallery, $options);
            echo "\n";
        }
    }
    
    /**
     * 扫描所有可用的gallery
     */
    private function scanAllGalleries()
    {
        $galleries = [];
        $imagesPath = __DIR__ . '/../../public/assets/images';
        
        if (!is_dir($imagesPath)) {
            return $galleries;
        }
        
        $dirs = scandir($imagesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $fullPath = $imagesPath . '/' . $dir;
            if (is_dir($fullPath)) {
                $galleries[] = $dir;
            }
        }
        
        return $galleries;
    }
    
    /**
     * 报告生成结果
     */
    private function reportResults($path, $results)
    {
        if (empty($results)) {
            echo "   ✅ 所有缩略图已存在，无需生成\n";
            return;
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($results as $result) {
            if ($result['success']) {
                $successCount++;
                echo "   ✅ {$result['image']}\n";
            } else {
                $failCount++;
                echo "   ❌ {$result['image']} (生成失败)\n";
            }
        }
        
        echo "   📊 成功: $successCount, 失败: $failCount\n";
    }
    
    /**
     * 解析命令行参数
     */
    private function parseArguments($args)
    {
        $options = [];
        
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            
            if ($arg === '--help' || $arg === '-h') {
                $options['help'] = true;
            } elseif ($arg === '--gallery' || $arg === '-g') {
                if (isset($args[$i + 1])) {
                    $options['gallery'] = $args[$i + 1];
                    $i++; // 跳过下一个参数
                }
            } elseif ($arg === '--force' || $arg === '-f') {
                $options['force'] = true;
            }
        }
        
        return $options;
    }
    
    /**
     * 显示帮助信息
     */
    private function showHelp()
    {
        echo "使用方法:\n";
        echo "  php app/Console/GenerateThumbnails.php [选项]\n\n";
        echo "选项:\n";
        echo "  -g, --gallery <name>    指定要处理的gallery名称\n";
        echo "                          支持格式: gallery名 或 gallery名/分类名\n";
        echo "  -f, --force            强制重新生成所有缩略图\n";
        echo "  -h, --help             显示此帮助信息\n\n";
        echo "示例:\n";
        echo "  php app/Console/GenerateThumbnails.php                    # 处理所有gallery\n";
        echo "  php app/Console/GenerateThumbnails.php -g single-works    # 处理single-works\n";
        echo "  php app/Console/GenerateThumbnails.php -g single-works/Animals  # 处理特定分类\n";
    }
    
    /**
     * 打印程序头部信息
     */
    private function printHeader()
    {
        echo "\n";
        echo "🖼️  统一缩略图生成工具 v2.0\n";
        echo "════════════════════════════════════════\n";
        echo "整合优化版 - 支持所有Gallery类型\n";
        echo "基于GalleryManager和ThumbnailGenerator\n\n";
    }
}

// 如果直接运行此文件
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $generator = new GenerateThumbnails();
    $generator->run(array_slice($argv, 1));
}