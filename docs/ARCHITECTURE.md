# 后台管理系统架构标准

## 📋 文件结构规范

### 目录结构
```
public/admin/
├── index.php                    # 控制台首页（标准结构）
├── controllers/                 # 控制器文件
│   ├── sort-config.php         # 各功能页面控制器
│   ├── gallery-manager.php     
│   ├── tools.php               
│   └── system-info.php         
├── views/                      # 视图文件
│   ├── layouts/                # 布局文件
│   │   ├── header.php          # 通用头部（含导航）
│   │   └── footer.php          # 通用底部（含脚本）
│   └── pages/                  # 页面内容文件
│       └── dashboard-content.php # 控制台页面内容
└── assets/ -> ../assets/       # 静态资源（使用全局assets）
```

## 🏗️ 标准页面结构

### 控制器文件标准格式
```php
<?php
/**
 * 页面标题 - 功能描述
 */

// 设置页面信息
$page_title = '页面标题';
$page_subtitle = '页面描述';
$_GET['page'] = 'page-id';

// 包含头部布局
require_once '../views/layouts/header.php';
?>

<div class="admin-page-content">
    <?php require_once '../views/pages/page-content.php'; ?>
</div>

<?php
// 包含底部布局
require_once '../views/layouts/footer.php';
```

### 页面内容文件标准格式
```php
<?php
/**
 * 页面具体内容
 * 此文件只包含 <body> 内的具体内容，不包含 HTML 结构
 */
?>

<!-- 页面内容开始 -->
<div class="page-header mb-4">
    <h1>页面标题</h1>
    <p class="text-muted">页面描述</p>
</div>

<!-- 具体页面内容 -->
<div class="row">
    <!-- 页面功能区域 -->
</div>

<script>
function pageInit() {
    // 页面专用 JavaScript
    console.log('页面已加载');
}
</script>
```

## 🎨 CSS 类名规范

### 容器类名
- `admin-page-content`: 管理后台页面内容容器（32px padding，响应式）
- ~~`page-content`~~: 已废弃，避免与前台页面冲突
- ~~`main-content`~~: 已废弃，统一使用 admin-page-content

### 响应式设计
```css
.admin-page-content {
    padding: var(--spacing-xl);        /* 32px 桌面端 */
    flex: 1;
    background-color: var(--bg-primary);
    min-height: calc(100vh - 80px);
}

@media (max-width: 768px) {
    .admin-page-content {
        padding: var(--spacing-md) !important;  /* 16px 移动端 */
    }
}
```

## 🔧 资源路径管理

### 统一资源引用
```php
// 在 header.php 中自动处理
$assets_base = $is_in_controllers ? '../../assets' : '../assets';

// CSS引用
<link rel="stylesheet" href="<?= $assets_base ?>/css/admin.css">

// JS引用  
<script src="<?= $assets_base ?>/js/admin.js"></script>
```

## ✅ 标准化检查清单

### 页面结构
- [ ] 使用标准的头部/底部布局引用
- [ ] 页面内容包装在 `admin-page-content` 容器中
- [ ] 设置了正确的 `$page_title` 和 `$_GET['page']`
- [ ] JavaScript 使用 `pageInit()` 函数

### CSS规范
- [ ] 移除 `page-content` 类的使用
- [ ] 统一使用 `admin-page-content` 容器
- [ ] 确保响应式设计正常工作

### 文件组织
- [ ] 控制器文件在 `controllers/` 目录
- [ ] 页面内容文件在 `views/pages/` 目录
- [ ] 布局文件在 `views/layouts/` 目录

## 📱 优势

1. **结构清晰**: MVC 模式，职责分离
2. **易于维护**: 统一的结构标准
3. **响应式设计**: 自动适配桌面和移动端
4. **避免冲突**: 独立的 admin 命名空间
5. **可扩展性**: 标准化的组件系统

## 🔄 迁移指南

### 从旧结构迁移
```php
// 旧结构（不推荐）
<?php include 'old-content.php'; ?>

// 新结构（推荐）
<?php
$page_title = '页面标题';
require_once '../views/layouts/header.php';
?>
<div class="admin-page-content">
    <?php require_once '../views/pages/content.php'; ?>
</div>
<?php require_once '../views/layouts/footer.php'; ?>
```

---
**更新时间**: 2025年9月27日  
**版本**: v0.9.1