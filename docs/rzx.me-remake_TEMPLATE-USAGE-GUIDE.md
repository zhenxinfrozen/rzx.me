# 后台管理页面标准模板使用指南

## 📋 快速开始

### 1. 复制模板
```bash
cp app/Admin/Views/pages/admin-template-new.php app/Admin/Views/pages/admin-mymodule-new.php
```

### 2. 全局替换（3处）
- `[MODULE]` → 显示名称（如：`Sketchbook`、`Comics`）
- `[module]` → 小写名称（如：`sketchbook`、`comics`）

### 3. 添加路由
在 `AdminIndexController.php` 中添加：
```php
'mymodule-new' => [
    'view' => 'admin-mymodule-new',
    'controller' => 'mymodule',
    'title' => 'MyModule 管理 (新版)',
    'subtitle' => '...'
],
```

## 🎨 架构规范（必须遵守）

### 颜色方案
```
左栏：bg-success (绿色 #28a745)
中栏：bg-primary (蓝色 #007bff)
右栏：bg-info    (青色 #17a2b8)
```

### 标准图标
```
左栏标题：list
中栏标题：edit-3
右栏标题：eye, zap, help-circle
```

### CSS/JS 版本
```html
<!-- CSS 必须使用 v=1.0.0 -->
<link href="...admin-common.css?v=1.0.0">
<link href="...admin-three-column.css?v=1.0.0">
<link href="...admin-image-manager.css?v=1.0.0">

<!-- JS 必须使用 v=2.4 -->
<script src="...admin-utils.js?v=2.4"></script>
<script src="...admin-drag-sort.js?v=2.4"></script>
<script src="...admin-image-manager.js?v=2.4"></script>
```

## 📝 开发检查清单

- [ ] 三栏布局颜色正确（绿/蓝/青）
- [ ] 标题图标正确（list/edit-3/eye）
- [ ] 标题文字格式：`XX顺序`、`编辑XX`
- [ ] CSS 版本号：v=1.0.0
- [ ] JS 版本号：v=2.4
- [ ] class 命名：`admin-left-panel`、`admin-center-panel`、`admin-right-panel`
- [ ] 添加按钮：绿色圆形，右上角
- [ ] 占位符图标：`arrow-left`

## 🚀 示例：创建 Books 管理页面

1. **复制模板**
```bash
cp admin-template-new.php admin-books-new.php
```

2. **替换占位符**
- `[MODULE]` → `Books`
- `[module]` → `books`

3. **添加业务逻辑**
```php
// 加载数据
require_once __DIR__ . '/../../Controllers/books-data.php';

// 在列表中显示
<?php foreach ($booksData as $book): ?>
  <!-- 列表项 -->
<?php endforeach; ?>
```

4. **实现 AJAX 操作**
```javascript
fetch('/admin/ajax?controller=books&ajax=create', {
    method: 'POST',
    body: formData
})
```

就这么简单！🎉
---

## 🎭 模板占位内容说明

### 模板包含的占位数据
模板包含完整的示例数据，方便快速预览效果。**在实际使用时必须替换！**

#### 1. 占位数据数组（PHP）
**位置**: 文件顶部 `$categoryData` 变量
```php
// 🎭 TEMPLATE PLACEHOLDER DATA - 以下数据仅供展示
$categoryData = [
    ['id' => 'example-category-1', 'display_name' => '示例分类 1 (占位)', ...]
    // ... 共3个示例
];
```

**替换方法**:
```php
// ❌ 删除占位数据
// $categoryData = [...];

// ✅ 加载实际数据
$categoryData = load_your_actual_data();
```

#### 2. 占位图片（SVG）
**位置**: `/public/assets/images/template/placeholder-*.svg`
- placeholder-1.svg（灰色）
- placeholder-2.svg（蓝色）
- placeholder-3.svg（绿色）

**说明**: 3个150x150的彩色占位图，用于演示缩略图功能。

**替换方法**:
```php
// ❌ 模板路径
'thumbnail' => '/assets/images/template/placeholder-1.svg'

// ✅ 实际路径
'thumbnail' => '/assets/images/books/cover-' . $item['id'] . '.jpg'
```

**清理命令**:
```bash
# 如果不需要模板占位图，可以删除整个目录
rm -rf public/assets/images/template/
```

#### 3. 占位JavaScript函数
**位置**: 底部 `<script>` 标签内
```javascript
function createItem() {
    // TEMPLATE: 发送 AJAX 请求
    console.log('创建新项目:', name);
    alert('创建功能 - 占位\n名称: ' + name);
    
    // ✅ 替换为实际 AJAX 请求
    // fetch('/admin/ajax?controller=books&ajax=create', {...})
}
```

**需要实现的函数**:
- `createItem()` - 创建新项目
- `saveItem()` - 保存编辑
- `deleteItem()` - 删除项目
- `saveSort()` - 保存排序

#### 4. 占位表单内容
**位置**: `#add-panel` 和 `#edit-panel`
- 示例输入框已填充占位值
- 示例下拉框有默认选项
- 图片管理区显示占位图片

**替换方法**: 根据实际字段修改表单结构，删除不需要的字段。

---

## ⚠️ 给 AI Agent 的特别说明

### 如何识别占位内容
1. 查找注释标记 `🎭 TEMPLATE PLACEHOLDER`
2. 查找文本 `(占位)` 或 `占位示例`
3. 查找路径 `/assets/images/template/`
4. 查找函数中的 `alert()` 和 `console.log()`

### 必须替换的内容清单
- ✅ `$categoryData` 数组 → 实际数据源
- ✅ `/assets/images/template/` → 实际图片路径
- ✅ `alert('XXX - 占位')` → 实际AJAX请求
- ✅ 示例表单字段 → 业务需要的字段
- ✅ 示例统计数字99 → 实际统计逻辑

### 保持不变的内容
- ❌ 三栏颜色方案（bg-success/primary/info）
- ❌ 标题图标（list/edit-3/eye）
- ❌ CSS/JS 版本号（v=1.0.0 / v=2.4）
- ❌ class 命名规范（admin-*）
- ❌ 页面整体布局结构

---

## 📋 快速替换检查清单

使用模板创建新页面时，按此清单逐项检查：

**数据层（PHP）**:
- [ ] 删除 `$categoryData` 占位数组
- [ ] 加载实际数据源（数据库/JSON/API）
- [ ] 更新 `$totalCategories` 统计逻辑

**视图层（HTML）**:
- [ ] 替换所有 `[MODULE]` 和 `[module]` 占位符
- [ ] 修改表单字段为实际业务字段
- [ ] 更新图片路径（不使用 `/template/`）
- [ ] 调整统计卡片内容

**逻辑层（JavaScript）**:
- [ ] 实现 `createItem()` 的 AJAX 请求
- [ ] 实现 `saveItem()` 的 AJAX 请求
- [ ] 实现 `deleteItem()` 的 AJAX 请求
- [ ] 实现 `saveSort()` 的 AJAX 请求
- [ ] 删除所有 `alert()` 占位代码

**清理工作**:
- [ ] 删除所有 `🎭 TEMPLATE PLACEHOLDER` 注释
- [ ] 删除所有 `(占位)` 标记文本
- [ ] 删除 `/assets/images/template/` 目录（如不需要）
- [ ] 更新 README 或注释说明实际功能

---

**文档更新**: 2024 | **模板版本**: v1.1.0 (包含完整占位内容)
