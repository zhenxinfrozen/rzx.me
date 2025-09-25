<?php
// 调试页面
$page_title = '调试测试页面';
require_once '../views/layouts/header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h1>CSS和JS调试测试</h1>
        
        <div class="card">
            <div class="card-header">测试Bootstrap样式</div>
            <div class="card-body">
                <button class="btn btn-primary">Primary按钮</button>
                <button class="btn btn-success">Success按钮</button>
            </div>
        </div>
        
        <div class="mt-3">
            <i data-feather="check-circle"></i> Feather图标测试
        </div>
    </div>
</div>

<script>
console.log('JavaScript正在运行');
if (typeof feather !== 'undefined') {
    console.log('Feather图标库已加载');
    feather.replace();
} else {
    console.error('Feather图标库未加载');
}
</script>

<?php require_once '../views/layouts/footer.php'; ?>