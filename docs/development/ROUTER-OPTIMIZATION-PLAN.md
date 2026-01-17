# 🚀 路由系统架构文档

## 📁 文件结构说明

### 🎯 核心路由文件
```
app/
├── Config/
│   └── routes.php          # 🔧 路由配置中心 (统一定义所有路由)
└── Router.php             # ⚡ 路由处理引擎 (解析和匹配逻辑)

public/
├── index.php              # 🚪 前端控制器 (统一入口点)  
├── .htaccess              # 🌐 Apache重写规则 (生产环境)
└── dev-server.php         # 🛠️ 开发服务器 (仅开发环境)

# 启动脚本
├── start-dev-server.bat   # 🖥️ Windows启动脚本
└── start-dev-server.sh    # 🐧 Linux/Mac启动脚本
```

## 🔄 请求处理流程

### 生产环境 (Apache/Nginx)
```
HTTP请求 → .htaccess → 静态文件直接返回 OR 转发到index.php
         → Router.php (加载routes.php) → 匹配路由 → 处理器执行
```

### 开发环境 (PHP内置服务器)  
```  
HTTP请求 → dev-server.php → 静态文件直接返回 OR 转发到index.php
         → Router.php (加载routes.php) → 匹配路由 → 处理器执行
```

## 📋 文件职责说明

| 文件 | 职责 | 修改场景 |
|------|------|----------|
| `routes.php` | 路由规则配置 | 添加新页面/API |
| `Router.php` | 路由匹配逻辑 | 扩展路由功能 |
| `index.php` | 请求分发处理 | 修改渲染流程 |
| `.htaccess` | Apache重写规则 | 生产环境配置 |
| `dev-server.php` | 开发环境路由 | 开发调试需求 |

## 🚀 使用方法

### 开发环境启动
```bash
# Windows
./start-dev-server.bat

# Linux/Mac  
./start-dev-server.sh

# 手动启动
php -S localhost:8000 -t public dev-server.php
```

### 添加新页面路由
在 `app/Config/routes.php` 中添加：
```php
'/new-page' => [
    'view' => 'pages/new-page.php',
    'title' => '新页面 - RZX.ME', 
    'handler' => 'get_page_data'
],
```

## ✅ 优化成果

✅ **命名清晰化**: router.php → dev-server.php  
✅ **职责分离**: 配置 vs 逻辑 vs 处理  
✅ **环境区分**: 开发 vs 生产环境分离  
✅ **文档完善**: 详细说明每个文件用途  
✅ **启动便利**: 一键启动开发服务器