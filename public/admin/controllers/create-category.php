<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['category'])) {
        throw new Exception('缺少必要参数');
    }
    
    $category = $input['category'];
    $displayName = $input['displayName'] ?? $category;
    $description = $input['description'] ?? '';
    $position = $input['position'] ?? 'last';
    
    // 验证分组名称
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $category)) {
        throw new Exception('分组名称只能包含字母、数字、下划线和连字符');
    }
    
    // 创建目录
    $categoryDir = "../../assets/images/single-works/$category";
    $thumbsDir = "$categoryDir/thumbs";
    
    if (is_dir($categoryDir)) {
        throw new Exception('分组已存在');
    }
    
    if (!mkdir($categoryDir, 0755, true)) {
        throw new Exception('创建目录失败');
    }
    
    if (!mkdir($thumbsDir, 0755, true)) {
        throw new Exception('创建缩略图目录失败');
    }
    
    // 更新配置文件
    $configPath = '../../../app/Config/single_works_sort.php';
    $config = file_exists($configPath) ? include($configPath) : [
        'sort_method' => 'custom_order',
        'custom_order' => [],
        'prefix_settings' => ['remove_prefix' => true, 'separator' => '-'],
        'display_names' => [],
        'descriptions' => []
    ];
    
    // 添加到自定义顺序
    if ($position === 'first') {
        array_unshift($config['custom_order'], $category);
    } else {
        $config['custom_order'][] = $category;
    }
    
    // 添加显示名称和描述
    $config['display_names'][$category] = $displayName;
    if (!empty($description)) {
        $config['descriptions'][$category] = $description;
    }
    
    // 保存配置
    $configContent = "<?php\n/**\n * Single-Works 分组排序配置\n * 自动生成于: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($config, true) . ";";
    
    if (!file_put_contents($configPath, $configContent)) {
        throw new Exception('保存配置失败');
    }
    
    echo json_encode([
        'success' => true,
        'message' => '分组创建成功',
        'category' => $category
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>