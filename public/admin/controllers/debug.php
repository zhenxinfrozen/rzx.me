<?php
// 设置必要变量避免header.php中的错误
$_GET['dev'] = true;
$page_title = 'Debug Test';
$page_subtitle = 'CSS Debug';
$_GET['page'] = 'debug';

// 显示调试信息
echo "<!-- Debug Info -->\n";
echo "<!-- REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . " -->\n";
echo "<!-- Current directory: " . __DIR__ . " -->\n";

// 手动设置路径变量
$request_uri = $_SERVER['REQUEST_URI'] ?? '/admin/controllers/debug.php';
$is_in_controllers = strpos($request_uri, '/admin/controllers/') !== false;

if ($is_in_controllers) {
    $assets_base = '../../assets';
} else {
    $assets_base = '../assets';
}

echo "<!-- Is in controllers: " . ($is_in_controllers ? 'true' : 'false') . " -->\n";
echo "<!-- Assets base: " . $assets_base . " -->\n";
echo "<!-- Admin.css should be at: " . $assets_base . "/css/admin.css -->\n";

// 检查文件是否存在
$admin_css_path = __DIR__ . '/../../assets/css/admin.css';
echo "<!-- Admin CSS file exists: " . (file_exists($admin_css_path) ? 'true' : 'false') . " -->\n";
echo "<!-- Admin CSS path: " . $admin_css_path . " -->\n";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Test</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= $assets_base ?>/css/admin.css">
    <style>
        .debug-info {
            background: #f8f9fa;
            border: 2px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="debug-info">
            <h2>CSS调试信息</h2>
            <p><strong>请求URI:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'not set') ?></p>
            <p><strong>是否在controllers目录:</strong> <?= $is_in_controllers ? '是' : '否' ?></p>
            <p><strong>Assets基础路径:</strong> <?= htmlspecialchars($assets_base) ?></p>
            <p><strong>Admin CSS完整路径:</strong> <?= htmlspecialchars($admin_css_path) ?></p>
            <p><strong>Admin CSS文件存在:</strong> <?= file_exists($admin_css_path) ? '是' : '否' ?></p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">测试Bootstrap样式</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">如果你能看到这个卡片有样式，说明Bootstrap CSS加载正常。</p>
                        <button class="btn btn-primary">Primary Button</button>
                        <button class="btn btn-success">Success Button</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>