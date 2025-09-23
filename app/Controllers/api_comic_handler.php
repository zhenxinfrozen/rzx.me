<?php
// app/Controllers/api_comic_handler.php
// 路径需要相对于调用它的 index.php
require_once __DIR__ . '/../Models/comic_data.php';

function handle_api_request() {
    $comicId = $_GET['id'] ?? '';
    $data = get_comic_by_id($comicId);

    header('Content-Type: application/json');

    if ($data) {
        echo json_encode($data);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Comic not found']);
    }
}
