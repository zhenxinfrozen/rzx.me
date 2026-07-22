# Admin 组件目录说明

## 目录结构

```
app/Admin/Views/components/        # 服务端PHP组件
├── README.md                      # 本文件
├── category-list.php              # 分类列表组件（左栏）
├── image-editor.php               # 图片编辑器组件（中栏）
├── preview-panel.php              # 预览面板组件（右栏）
└── form-elements.php              # 通用表单元素

public/assets/admin/               # 浏览器静态资源
├── css/
│   ├── admin-common.css           # 通用样式
│   ├── admin-three-column.css     # 三栏布局
│   └── admin-image-manager.css    # 图片管理器
└── js/
    ├── admin-drag-sort.js         # 拖拽排序
    ├── admin-image-manager.js     # 图片管理器
    └── admin-utils.js             # 通用工具函数
```

## 使用方法

### PHP组件（服务端include）

```php
<?php
// 在页面中引入组件
require_once __DIR__ . '/components/category-list.php';

// 传递参数
renderCategoryList($categories, [
    'thumbnail_size' => '44x44',
    'draggable' => true,
    'show_count' => true
]);
?>
```

### CSS/JS资源（浏览器加载）

```php
<!-- 在 header 中引入 -->
<link rel="stylesheet" href="/assets/admin/css/admin-common.css">
<link rel="stylesheet" href="/assets/admin/css/admin-three-column.css">

<!-- 在 footer 中引入 -->
<script src="/assets/admin/js/admin-utils.js"></script>
<script src="/assets/admin/js/admin-drag-sort.js"></script>
```

## 设计原则

1. **分离关注点**
   - PHP组件处理服务端逻辑和HTML结构
   - CSS处理样式和布局
   - JS处理交互和动态行为

2. **可复用性**
   - 每个组件独立，可在多个页面中使用
   - 参数化配置，适应不同需求

3. **安全性**
   - PHP组件在 app/ 目录，不可直接访问
   - 静态资源在 public/ 目录，只包含前端代码

4. **维护性**
   - 组件化降低耦合度
   - 统一命名规范（admin-* 前缀）
   - 清晰的文件组织

## 命名规范

### PHP组件
- 使用 kebab-case: `category-list.php`
- 主函数使用 camelCase: `renderCategoryList()`

### CSS类名
- 使用 BEM 命名: `.admin-category-list__item--active`
- 组件前缀: `admin-*`

### JS函数/类
- 类使用 PascalCase: `class AdminImageManager`
- 函数使用 camelCase: `function initDragSort()`
- 全局对象: `window.AdminUtils`

---

**创建时间**: 2026-01-21
**维护者**: Development Team
