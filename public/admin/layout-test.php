<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>布局测试</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .test-card { min-height: 200px; }
        .bg-test-1 { background: #e3f2fd; }
        .bg-test-2 { background: #f3e5f5; }
        .bg-test-3 { background: #e8f5e8; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mb-4">三栏布局测试</h1>
        
        <div class="row g-3">
            <!-- 左栏 -->
            <div class="col-lg-3">
                <div class="card test-card bg-test-1">
                    <div class="card-header">左栏 (col-lg-3)</div>
                    <div class="card-body">
                        <p>这里应该是分组列表</p>
                        <ul class="list-unstyled">
                            <li class="p-2 border-bottom">Animals</li>
                            <li class="p-2 border-bottom">Gallery comic</li>
                            <li class="p-2 border-bottom">Game+color</li>
                            <li class="p-2 border-bottom">Special-Zone</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- 中栏 -->
            <div class="col-lg-6">
                <div class="card test-card bg-test-2">
                    <div class="card-header">中栏 (col-lg-6)</div>
                    <div class="card-body">
                        <p>这里应该是编辑区域</p>
                        <div class="mb-3">
                            <label class="form-label">显示名称</label>
                            <input type="text" class="form-control" value="Animals">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">描述</label>
                            <textarea class="form-control" rows="2">动物主题作品</textarea>
                        </div>
                        <button class="btn btn-primary btn-sm me-2">更新</button>
                        <button class="btn btn-danger btn-sm">删除</button>
                    </div>
                </div>
            </div>
            
            <!-- 右栏 -->
            <div class="col-lg-3">
                <div class="card test-card bg-test-3">
                    <div class="card-header">右栏 (col-lg-3)</div>
                    <div class="card-body">
                        <p>这里应该是预览和管理</p>
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm">前台预览</a>
                            <a href="#" class="btn btn-outline-warning btn-sm">回收站</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>