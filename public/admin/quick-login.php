<?php
// 快速登录设置会话
session_start();
$_SESSION['admin_authenticated'] = true;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card'>
                    <div class='card-body'>
                        <h2 class='text-center mb-4'>管理员登录成功</h2>
                        <p class='text-center'>会话已设置，可以访问后台管理页面。</p>
                        <div class='text-center'>
                            <a href='controllers/comic-manager.php' class='btn btn-primary'>访问 Comic Manager</a>
                            <a href='controllers/tools.php' class='btn btn-secondary'>访问 Tools</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>