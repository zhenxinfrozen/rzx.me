<?php
// app/Controllers/api_comic_handler.php
// 路径需要相对于调用它的 index.php
require_once __DIR__ . '/../Models/comic_data.php';

function handle_api_request() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // 设置CORS头
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json');

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    try {
        switch ($method) {
            case 'GET':
                handle_get_request();
                break;
            case 'POST':
                handle_post_request();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal server error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

function handle_get_request() {
    $comicId = $_GET['id'] ?? '';
    $action = $_GET['action'] ?? 'get';
    
    switch ($action) {
        case 'get':
            if (empty($comicId)) {
                // 返回所有活跃的漫画列表
                $comics = get_comics_by_status('active');
                echo json_encode([
                    'success' => true,
                    'data' => $comics,
                    'count' => count($comics)
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 获取单个漫画
            $data = get_comic_by_id($comicId);
            if ($data) {
                // 兼容旧格式：如果存在 image 字段但没有 images 数组，转换为数组
                if (isset($data['image']) && !isset($data['images'])) {
                    $data['images'] = [$data['image']];
                }
                
                // 确保返回的数据包含所有必要字段
                $responseData = array_merge([
                    'title' => '',
                    'subtitle' => '',
                    'lines' => '',
                    'images' => [],
                    'alt' => '',
                    'icon_default' => '',
                    'icon_hover' => '',
                    'status' => 'active',
                    'created_at' => '',
                    'updated_at' => ''
                ], $data);
                
                echo json_encode($responseData, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Comic not found'], JSON_UNESCAPED_UNICODE);
            }
            break;
            
        case 'stats':
            $stats = get_comic_stats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'search':
            $keyword = $_GET['q'] ?? '';
            if (empty($keyword)) {
                echo json_encode([
                    'error' => 'Search keyword is required'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $results = search_comics($keyword);
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'keyword' => $keyword
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
    }
}

function handle_post_request() {
    // 这里可以添加POST请求处理，比如添加、更新、删除漫画
    // 目前暂时返回不支持的方法
    http_response_code(501);
    echo json_encode([
        'error' => 'POST operations not implemented yet',
        'message' => 'Use the admin panel for comic management'
    ], JSON_UNESCAPED_UNICODE);
}

// 保持向后兼容的旧API处理函数
function handle_legacy_api() {
    return handle_api_request();
}

function get_comic_list() {
    $comics = get_comics_by_status('active');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $comics,
        'count' => count($comics)
    ], JSON_UNESCAPED_UNICODE);
}
