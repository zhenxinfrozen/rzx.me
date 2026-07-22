#!/usr/bin/env php
<?php
/**
 * 编码检测和修复工具
 *
 * 功能：
 * 1. 检测项目中所有文件的编码
 * 2. 查找有 BOM 的文件
 * 3. 转换非 UTF-8 文件
 * 4. 移除 PHP 文件的 BOM
 *
 * 使用：php tools/check-encoding.php [--fix]
 */

define('PROJECT_ROOT', __DIR__ . '/..');

class EncodingChecker
{
    private $issues = [];
    private $fixMode = false;
    private $stats = [
        'total' => 0,
        'utf8' => 0,
        'utf8_bom' => 0,
        'other' => 0,
        'fixed' => 0,
        'errors' => 0
    ];

    // 需要检查的文件扩展名
    private $extensions = ['php', 'js', 'html', 'css', 'json', 'md', 'txt', 'xml'];

    // 排除的目录
    private $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/cache', 'public/assets/images'];

    public function __construct($fixMode = false)
    {
        $this->fixMode = $fixMode;
    }

    /**
     * 扫描目录
     */
    public function scan($directory)
    {
        echo "📁 扫描目录: $directory\n";
        echo str_repeat('-', 60) . "\n\n";

        $this->scanDirectory(PROJECT_ROOT . '/' . $directory);

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📊 统计结果:\n";
        echo str_repeat('=', 60) . "\n";
        echo sprintf("总文件数: %d\n", $this->stats['total']);
        echo sprintf("✅ UTF-8 (无 BOM): %d (%.1f%%)\n",
            $this->stats['utf8'],
            $this->stats['total'] > 0 ? ($this->stats['utf8'] / $this->stats['total'] * 100) : 0
        );
        echo sprintf("⚠️  UTF-8 (有 BOM): %d\n", $this->stats['utf8_bom']);
        echo sprintf("❌ 其他编码: %d\n", $this->stats['other']);

        if ($this->fixMode) {
            echo sprintf("🔧 已修复: %d\n", $this->stats['fixed']);
            echo sprintf("⚠️  错误: %d\n", $this->stats['errors']);
        }

        if (!empty($this->issues)) {
            echo "\n" . str_repeat('=', 60) . "\n";
            echo "🔍 发现的问题:\n";
            echo str_repeat('=', 60) . "\n";

            foreach ($this->issues as $issue) {
                echo sprintf("[%s] %s\n", $issue['type'], $issue['file']);
                if (isset($issue['detail'])) {
                    echo "    → {$issue['detail']}\n";
                }
            }

            if (!$this->fixMode) {
                echo "\n💡 运行 'php tools/check-encoding.php --fix' 来自动修复问题\n";
            }
        } else {
            echo "\n✨ 太棒了！所有文件编码都正确！\n";
        }
    }

    /**
     * 递归扫描目录
     */
    private function scanDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            $relativePath = str_replace(PROJECT_ROOT . '/', '', $path);

            // 检查是否是排除的目录
            foreach ($this->excludeDirs as $excludeDir) {
                if (strpos($relativePath, $excludeDir) === 0) {
                    continue 2;
                }
            }

            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (is_file($path)) {
                $this->checkFile($path, $relativePath);
            }
        }
    }

    /**
     * 检查单个文件
     */
    private function checkFile($fullPath, $relativePath)
    {
        // 检查文件扩展名
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->extensions)) {
            return;
        }

        $this->stats['total']++;

        // 读取文件前几个字节
        $handle = fopen($fullPath, 'rb');
        if (!$handle) {
            return;
        }

        $firstBytes = fread($handle, 4);
        fclose($handle);

        // 检查 BOM
        $hasBOM = false;
        $bomType = null;

        if (substr($firstBytes, 0, 3) === "\xEF\xBB\xBF") {
            $hasBOM = true;
            $bomType = 'UTF-8 BOM';
            $this->stats['utf8_bom']++;
        } elseif (substr($firstBytes, 0, 2) === "\xFE\xFF") {
            $hasBOM = true;
            $bomType = 'UTF-16 BE BOM';
            $this->stats['other']++;
        } elseif (substr($firstBytes, 0, 2) === "\xFF\xFE") {
            $hasBOM = true;
            $bomType = 'UTF-16 LE BOM';
            $this->stats['other']++;
        } else {
            // 无 BOM，检查内容编码
            $content = file_get_contents($fullPath);
            if ($this->isUtf8($content)) {
                $this->stats['utf8']++;
                return; // UTF-8 无 BOM，完美！
            } else {
                $this->stats['other']++;
                $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'BIG5', 'ASCII'], true);
                $this->issues[] = [
                    'type' => 'ENCODING',
                    'file' => $relativePath,
                    'detail' => "检测到编码: " . ($encoding ?: 'Unknown')
                ];

                if ($this->fixMode) {
                    $this->fixEncoding($fullPath, $relativePath, $encoding);
                }
                return;
            }
        }

        // 有 BOM
        if ($hasBOM) {
            $this->issues[] = [
                'type' => 'BOM',
                'file' => $relativePath,
                'detail' => "检测到 $bomType" . ($ext === 'php' ? ' (PHP 文件不能有 BOM!)' : '')
            ];

            if ($this->fixMode) {
                $this->removeBOM($fullPath, $relativePath);
            }
        }
    }

    /**
     * 检查是否是有效的 UTF-8
     */
    private function isUtf8($string)
    {
        return mb_check_encoding($string, 'UTF-8');
    }

    /**
     * 修复编码问题
     */
    private function fixEncoding($fullPath, $relativePath, $currentEncoding)
    {
        try {
            $content = file_get_contents($fullPath);

            if ($currentEncoding && $currentEncoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $currentEncoding);
            }

            file_put_contents($fullPath, $content);
            $this->stats['fixed']++;
            echo "✅ 已修复: $relativePath (从 $currentEncoding 转换为 UTF-8)\n";
        } catch (Exception $e) {
            $this->stats['errors']++;
            echo "❌ 修复失败: $relativePath - {$e->getMessage()}\n";
        }
    }

    /**
     * 移除 BOM
     */
    private function removeBOM($fullPath, $relativePath)
    {
        try {
            $content = file_get_contents($fullPath);

            // 移除 UTF-8 BOM
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }
            // 移除 UTF-16 BE BOM
            elseif (substr($content, 0, 2) === "\xFE\xFF") {
                $content = substr($content, 2);
            }
            // 移除 UTF-16 LE BOM
            elseif (substr($content, 0, 2) === "\xFF\xFE") {
                $content = substr($content, 2);
            }

            file_put_contents($fullPath, $content);
            $this->stats['fixed']++;
            echo "✅ 已移除 BOM: $relativePath\n";
        } catch (Exception $e) {
            $this->stats['errors']++;
            echo "❌ 移除 BOM 失败: $relativePath - {$e->getMessage()}\n";
        }
    }
}

// 主程序
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         RZX.ME 项目编码检测和修复工具                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$fixMode = in_array('--fix', $argv);

if ($fixMode) {
    echo "🔧 修复模式已启用\n\n";
    echo "警告：此操作将修改文件！建议先提交 Git 或备份。\n";
    echo "按 Enter 继续，或 Ctrl+C 取消...\n";
    readline();
}

$checker = new EncodingChecker($fixMode);
$checker->scan('app');
$checker->scan('public');
$checker->scan('docs');

echo "\n✨ 扫描完成！\n\n";
