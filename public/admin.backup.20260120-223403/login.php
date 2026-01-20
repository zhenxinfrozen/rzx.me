<?php
/**
 * RZX.ME 后台管理系统 - 登录页面
 * 简单的认证界面，支持开发模式
 */

// 设置正确的字符编码
header('Content-Type: text/html; charset=UTF-8');

session_start();

// 处理登录
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 简单的硬编码认证（生产环境需要改进）
    if ($username === 'admin' && $password === 'rzx2024') {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_user'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}

// 如果已登录，跳转到后台
if (isset($_SESSION['admin_authenticated'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZX.ME 后台登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #23282d;
            margin-bottom: 8px;
        }
        
        .subtitle {
            color: #646970;
            margin-bottom: 32px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #23282d;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #c3c4c7;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0073aa;
            box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #0073aa;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .btn:hover {
            background-color: #005a87;
        }
        
        .error {
            background-color: #fef2f2;
            color: #d63638;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        
        .dev-note {
            margin-top: 24px;
            padding: 16px;
            background-color: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 13px;
            color: #1e40af;
        }
        
        .dev-note a {
            color: #1e40af;
            font-weight: 600;
        }
        
        .footer {
            margin-top: 24px;
            text-align: center;
            color: #8c8f94;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">RZX.ME</div>
        <div class="subtitle">后台管理系统</div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    required
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                >
            </div>
            
            <button type="submit" class="btn">登录</button>
        </form>
        
        <div class="dev-note">
            <strong>开发提示:</strong><br>
            默认用户名: <code>admin</code>, 密码: <code>rzx2024</code><br>
            或者使用 <a href="index.php?dev">开发模式</a> 跳过登录
        </div>
        
        <div class="footer">
            © 2024 RZX.ME - 个人作品展示网站
        </div>
    </div>
</body>
</html>