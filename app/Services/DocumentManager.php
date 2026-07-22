<?php
/**
 * 文档管理器 - 专门为 rzx-me 设计
 */

namespace App\Services;

class DocumentManager {
    private $docsPath;

    public function __construct() {
        $this->docsPath = realpath(__DIR__ . '/../../docs');
    }

    /**
     * 获取所有文档列表（按目录分组）
     */
    public function getDocuments() {
        $sections = [];
        
        // 1. 先处理根目录下的文档
        $rootFiles = glob($this->docsPath . '/*.md');
        if (!empty($rootFiles)) {
            $sections[] = [
                'id' => 'root',
                'name' => basename($this->docsPath), // 直接用 docs 目录名
                'icon' => 'bi-folder',
                'files' => $this->processFileList($rootFiles)
            ];
        }

        // 2. 扫描子目录
        $dirs = glob($this->docsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            if (substr($dirName, 0, 1) === '.') continue;

            $dirFiles = glob($dir . '/*.md');
            if (empty($dirFiles)) continue;

            $sections[] = [
                'id' => $dirName,
                'name' => $dirName, // 直接用物理文件夹名
                'icon' => 'bi-folder',
                'files' => $this->processFileList($dirFiles, $dirName)
            ];
        }

        return $sections;
    }

    /**
     * 处理文件列表并提取元数据
     */
    private function processFileList($files, $subDir = '') {
        $result = [];
        foreach ($files as $file) {
            $fileName = basename($file);
            $relativePath = $subDir ? $subDir . '/' . $fileName : $fileName;
            
            // 直接使用文件名（去掉 .md 扩展名）作为标题，不再从内容中提取 # 标题
            $title = pathinfo($fileName, PATHINFO_FILENAME);

            $result[] = [
                'file' => $relativePath,
                'title' => $title,
                'path' => $file,
                'size' => filesize($file),
                'mtime' => filemtime($file)
            ];
        }

        usort($result, function($a, $b) {
            return strcasecmp($a['file'], $b['file']);
        });

        return $result;
    }

    /**
     * 获取单个文档内容
     */
    public function getDocument($relativePath) {
        $filePath = $this->docsPath . '/' . $relativePath;
        $realDocsPath = realpath($this->docsPath);
        $realFilePath = realpath($filePath);

        if (!$realFilePath || strpos($realFilePath, $realDocsPath) !== 0) {
            return null;
        }

        $content = file_get_contents($realFilePath);
        
        // 直接使用物理文件名作为显示标题
        $title = pathinfo($relativePath, PATHINFO_FILENAME);

        return [
            'file' => $relativePath,
            'title' => $title,
            'content' => $content,
            'mtime' => filemtime($realFilePath)
        ];
    }

    /**
     * 渲染 Markdown
     */
    public function render($markdown) {
        require_once __DIR__ . '/../Utils/Parsedown.php';
        $parsedown = new \Parsedown();
        return $parsedown->text($markdown);
    }
    
    /**
     * 搜索文档
     */
    public function search($query) {
        $sections = $this->getDocuments();
        $results = [];
        $query = mb_strtolower($query);

        foreach ($sections as $section) {
            foreach ($section['files'] as $doc) {
                $content = mb_strtolower(file_get_contents($doc['path']));
                if (strpos(mb_strtolower($doc['title']), $query) !== false || strpos($content, $query) !== false) {
                    // 生成预览
                    $contentRaw = file_get_contents($doc['path']);
                    $pos = mb_stripos($contentRaw, $query);
                    $start = max(0, $pos - 50);
                    $preview = mb_substr($contentRaw, $start, 150);
                    
                    $results[] = [
                        'file' => $doc['file'],
                        'title' => $doc['title'],
                        'preview' => '...' . htmlspecialchars($preview) . '...'
                    ];
                }
            }
        }
        return $results;
    }
}
