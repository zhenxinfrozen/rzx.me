<?php
/**
 * 文档查看器控制器
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../app/Services/DocumentManager.php';

use App\Services\DocumentManager;

$docManager = new DocumentManager();
$files = $docManager->getDocuments();

$selectedFile = $_GET['file'] ?? null;
$searchQuery = $_GET['q'] ?? null;

$document = null;
$searchResults = null;

if ($searchQuery) {
    $searchResults = $docManager->search($searchQuery);
} elseif ($selectedFile) {
    $document = $docManager->getDocument($selectedFile);
    if ($document) {
        $document['html'] = $docManager->render($document['content']);
    }
}

// 支持 AJAX 请求返回 JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    if ($searchResults !== null) {
        echo json_encode([
            'status' => 'success',
            'type' => 'search',
            'results' => $searchResults
        ]);
    } elseif ($document) {
        echo json_encode([
            'status' => 'success',
            'type' => 'document',
            'title' => $document['title'],
            'html' => $document['html'],
            'mtime' => date('Y-m-d H:i', $document['mtime']),
            'file' => $document['file']
        ]);
    } else {
        // 如果没有选择文件也没有搜索，返回首页内容结构
        echo json_encode([
            'status' => 'success',
            'type' => 'index',
            'title' => '文档中心'
        ]);
    }
    exit;
}
