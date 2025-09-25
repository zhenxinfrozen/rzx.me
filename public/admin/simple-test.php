<?php
$page_title = '简单测试页面';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - RZX.ME 后台管理</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .test-card { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-primary">Bootstrap测试页面</h1>
        
        <div class="card test-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">测试卡片</h5>
            </div>
            <div class="card-body">
                <p>这是一个测试页面，用于验证Bootstrap样式是否正常工作。</p>
                <button class="btn btn-primary me-2">主要按钮</button>
                <button class="btn btn-secondary">次要按钮</button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6>左侧卡片</h6>
                        <p>测试两栏布局</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6>右侧卡片</h6>
                        <p>测试两栏布局</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('页面已加载，Bootstrap应该正常工作');
    </script>
</body>
</html>