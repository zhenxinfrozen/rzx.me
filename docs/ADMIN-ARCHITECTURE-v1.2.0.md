# Admin 后台架构说明 v1.2.0

## 🎯 架构概览

Admin 后台已完全迁移到 `app/Admin/` 目录，采用清晰的 MVC 架构。

```
rzx-me/
├── public/
│   └── admin/                    # 入口层 (只保留路由器)
│       ├── index.php            # 主入口 → AdminIndexController::handle()
│       ├── ajax.php             # AJAX 统一路由器
│       ├── login.php            # 认证页面
│       └── .htaccess            # Web服务器配置
│
└── app/
    └── Admin/                    # 业务逻辑层 (独立模块)
        ├── Controllers/          # 控制器 (15个)
        │   ├── AdminIndexController.php      # 主控制器
        │   ├── sketchbook.php               # 速写本管理
        │   ├── single-works.php             # 作品分组管理
        │   ├── video-gallery.php            # 视频管理
        │   ├── comics.php                   # 漫画管理
        │   ├── thumbnail-center.php         # 缩略图中心
        │   ├── gallery-manager.php          # 图库管理
        │   ├── cache-manager.php            # 缓存管理
        │   ├── site-config.php              # 站点配置
        │   ├── system-info.php              # 系统信息
        │   ├── tools.php                    # 开发工具
        │   ├── trash.php                    # 回收站
        │   └── docs-handler.php             # 文档处理
        │
        ├── Views/                # 视图 (13个)
        │   ├── layouts/         # 布局组件
        │   │   ├── header.php
        │   │   └── footer.php
        │   └── pages/           # 页面视图
        │       ├── body-dashboard.php
        │       ├── body-single-works.php
        │       ├── body-sketchbook.php
        │       ├── body-comics.php
        │       ├── body-video-gallery.php
        │       ├── thumbnail-center.php
        │       └── tools.php
        │
        └── Helpers/              # 辅助类
            └── ResponseHelper.php  # JSON 响应助手

```

## 📐 请求流程

### 1. 页面请求流程
```
浏览器请求: /admin?page=single-works
    ↓
public/admin/index.php (入口路由器)
    ↓
app/Admin/Controllers/AdminIndexController.php
    ↓ handle() 方法
    ├─ 验证page参数
    ├─ 加载对应控制器 (single-works.php)
    ├─ 执行控制器逻辑
    └─ 渲染视图 (body-single-works.php)
    ↓
返回完整HTML页面
```

### 2. AJAX 请求流程
```
JavaScript: fetch('/admin/ajax.php?controller=single-works&ajax=upload_thumbnail')
    ↓
public/admin/ajax.php (AJAX路由器)
    ↓ 验证参数
    ├─ controller: single-works
    └─ ajax: upload_thumbnail
    ↓
require app/Admin/Controllers/single-works.php
    ↓
handleSingleWorksAjax('upload_thumbnail', ...)
    ↓
uploadCategoryThumbnail() 函数
    ↓
返回 JSON 响应
```

## 🔑 关键设计原则

### 1. 单一入口
- **页面请求**: 统一通过 `public/admin/index.php`
- **AJAX请求**: 统一通过 `public/admin/ajax.php`
- **好处**: 集中验证、日志、错误处理

### 2. 控制器分离
- 每个功能模块一个独立控制器
- 控制器只负责业务逻辑，不处理路由
- 使用函数式编程，便于测试

### 3. 视图与逻辑分离
- 控制器不输出HTML
- 视图文件只负责展示
- 数据通过变量传递给视图

### 4. 配置集中管理
- 配置文件统一在 `app/Config/`
- 控制器读取配置，不硬编码路径
- 便于维护和部署

## 🛠️ 开发指南

### 添加新页面

1. **创建控制器**: `app/Admin/Controllers/my-feature.php`
```php
<?php
define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../../bootstrap.php';

function handleMyFeatureAjax($action) {
    switch ($action) {
        case 'my_action':
            // 处理逻辑
            respondJson(['success' => true]);
            break;
    }
}

// 页面逻辑
$page_title = '我的功能';
$page_subtitle = '功能描述';
$_GET['page'] = 'my-feature';

// 控制器逻辑完成，返回给 AdminIndexController 渲染视图
```

2. **创建视图**: `app/Admin/Views/pages/body-my-feature.php`
```php
<div class="container">
    <h1><?= $page_title ?></h1>
    <!-- 页面内容 -->
</div>
```

3. **注册到 AdminIndexController**
```php
$allowedPages = [
    'my-feature' => __DIR__ . '/my-feature.php',
    // ...
];
```

4. **添加AJAX路由** (如需要)
```php
// 在 public/admin/ajax.php
$allowedControllers = [
    'my-feature' => __DIR__ . '/../../app/Admin/Controllers/my-feature.php',
    // ...
];
```

### AJAX 请求规范

**前端调用**:
```javascript
const controllerUrl = '/admin/ajax.php?controller=my-feature';

fetch(`${controllerUrl}&ajax=my_action`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ data: 'value' })
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        // 成功处理
    }
});
```

**后端处理**:
```php
function handleMyFeatureAjax($action) {
    switch ($action) {
        case 'my_action':
            $input = json_decode(file_get_contents('php://input'), true);
            // 处理逻辑
            respondJson(['success' => true, 'message' => '操作成功']);
            break;
    }
}
```

## 📁 目录职责

| 目录 | 职责 | 示例 |
|-----|------|------|
| `public/admin/` | HTTP入口、路由分发 | index.php, ajax.php |
| `app/Admin/Controllers/` | 业务逻辑、数据处理 | single-works.php |
| `app/Admin/Views/` | HTML渲染、页面展示 | body-single-works.php |
| `app/Admin/Helpers/` | 通用辅助功能 | ResponseHelper.php |
| `app/Config/` | 配置文件 | single_works_config.php |
| `app/Models/` | 数据模型、持久化 | comic_data.php |
| `app/Services/` | 服务类、业务封装 | ThumbnailService.php |

## 🔄 迁移历史

### v1.2.0 重构 (2026-01-20/21)

**Before**:
```
public/admin/
├── controllers/     ← 业务逻辑混在public下
│   ├── single-works.php
│   ├── sketchbook.php
│   └── ...
└── views/          ← 视图也在public下
    ├── layouts/
    └── pages/
```

**After**:
```
public/admin/        ← 只保留入口
├── index.php       ← 路由器
├── ajax.php        ← AJAX路由器
└── login.php

app/Admin/          ← 业务逻辑独立
├── Controllers/    ← 控制器在app下
├── Views/          ← 视图在app下
└── Helpers/        ← 辅助类在app下
```

**Benefits**:
- ✅ 清晰的业务逻辑与入口分离
- ✅ public 目录不再包含业务代码
- ✅ 便于单元测试和代码复用
- ✅ 符合现代 PHP 框架最佳实践
- ✅ 更好的安全性（app目录可以放在web根目录外）

## 🧹 已删除内容

迁移过程中已删除：
- `public/admin/controllers/` - 15个旧控制器
- `public/admin/views/` - 13个旧视图
- `public/admin/router.php` - 旧路由器
- `public/admin/diagnose-paths.php` - 诊断工具
- `public/admin/test-*.php` - 测试文件

备份位置: `public/admin.old-files.20260121-002232/`

## 📚 相关文档

- [后台系统总结](./ADMIN-SYSTEM-SUMMARY.md)
- [API参考](./api-reference.md)
- [缩略图系统](./THUMBNAIL-SYSTEM.md)
- [视频管理指南](./VIDEO-GALLERY-ADMIN-GUIDE.md)

## 🎓 最佳实践

1. **路径使用**: 总是使用 `__DIR__` 构建绝对路径
2. **错误处理**: 使用 try-catch 包装危险操作
3. **输入验证**: 验证所有用户输入
4. **输出转义**: 使用 `escapeHtml()` 防止XSS
5. **配置管理**: 使用 `ConfigManager` 读写配置
6. **JSON响应**: 统一使用 `respondJson()` 或 `ResponseHelper`
7. **缓存破坏**: 图片URL添加时间戳参数
8. **调试信息**: 开发环境包含debug对象
