# Single-Works → Drafts 重命名完成报告

## ✅ 已完成的修改

### 1. 文件创建
- ✅ `app/Admin/Controllers/drafts.php`（复制自 single-works.php）
- ✅ `app/Admin/Controllers/drafts-data.php`（复制自 single-works-data.php）
- ✅ `app/Admin/Views/pages/admin-drafts-new.php`（复制自 admin-galleries-new.php）

### 2. 文件内容批量替换
已在以下文件中完成替换：
- `drafts.php`
- `drafts-data.php`
- `admin-drafts-new.php`

**替换规则**：
```
Single-Works → Drafts
single-works → drafts
Single Works → Drafts
单图作品 → 草稿
作品分类 → 草稿分类
Galleries → Drafts（仅在新文件中）
galleries → drafts（仅在新文件中）
```

### 3. 路由配置更新
**文件**: `app/Admin/Controllers/AdminIndexController.php`

添加了两个新路由：
```php
'drafts' => [
    'view' => 'admin-drafts-new',
    'controller' => 'drafts',
    'title' => 'Drafts 草稿管理',
    'subtitle' => '管理草稿分类与图片'
],
'drafts-new' => [
    'view' => 'admin-drafts-new',
    'controller' => 'drafts',
    'title' => 'Drafts 草稿管理 (新版)',
    'subtitle' => '使用新组件的测试版本'
]
```

并在 bootstrap 加载列表中添加：
```php
if (in_array($page, [..., 'drafts', 'drafts-new'])) {
    require_once __DIR__ . '/../../bootstrap.php';
}
```

### 4. 前台菜单更新
**文件**: `app/Views/layouts/header.php`

```php
// 修改前
'Single-Works' => '/single-works',

// 修改后
'Drafts' => '/drafts',
```

### 5. 后台菜单更新
**文件**: `app/Admin/Views/layouts/admin-header.php`

1. 页面标题映射添加：
```php
'drafts' => 'Drafts 草稿管理',
'drafts-new' => 'Drafts 草稿管理 (新版)',
```

2. 左侧菜单添加新项：
```html
<li class="menu-item <?= in_array($current_page, ['drafts', 'drafts-new']) ? 'active' : '' ?>">
    <a href="/admin?page=drafts">
        <i data-feather="edit-3"></i>
        <span>Drafts 草稿管理</span>
    </a>
</li>
```

---

## 📋 访问路径

### 后台管理
- **新版本**: http://localhost:8888/admin?page=drafts-new
- **旧版本（如有）**: http://localhost:8888/admin?page=drafts

### 前台展示
- http://localhost:8888/drafts

---

## ⚠️ 注意事项

### 1. 保留的 Single-Works
以下文件**未修改**，仍然使用 single-works：
- `app/Admin/Controllers/single-works.php`（原文件）
- `app/Admin/Controllers/single-works-data.php`（原文件）
- `app/Admin/Views/pages/admin-galleries.php`（旧版视图）
- `app/Admin/Views/pages/admin-galleries-new.php`（galleries 新版）

**原因**：这些是原始的 Single-Works 系统，保留作为备份或并行运行。

### 2. 数据文件路径
Drafts 系统使用的数据文件路径（在 `drafts.php` 中）：
```php
$configPath = __DIR__ . '/../../storage/data/drafts-sort.json';
$imagesRoot = __DIR__ . '/../../../public/assets/images/drafts';
```

**需要手动创建**：
- `app/storage/data/drafts-sort.json`
- `public/assets/images/drafts/`（图片目录）

### 3. 前台展示页面
如果前台需要展示 Drafts，还需要：
- 创建 `app/Views/pages/drafts.php`（前台展示页面）
- 创建 `app/Controllers/drafts.php`（前台控制器）
- 配置路由 `app/Config/routes.php`

---

## 🔧 待处理任务

### 必需操作
1. ✅ 创建数据目录和文件：
   ```bash
   mkdir public/assets/images/drafts
   echo {} > app/storage/data/drafts-sort.json
   ```

2. ⏳ 如果需要前台展示，创建前台相关文件

3. ⏳ 测试后台管理功能：
   - 访问 `/admin?page=drafts-new`
   - 测试添加/编辑/删除草稿分类
   - 测试图片上传

### 可选操作
- 如果确定不再需要 single-works，可以考虑删除或重命名原文件
- 更新文档和注释中的相关说明

---

## 📊 文件对比

| 功能 | Single-Works（原） | Drafts（新） | 状态 |
|-----|------------------|------------|------|
| 后台控制器 | single-works.php | drafts.php | ✅ 已创建 |
| 数据加载器 | single-works-data.php | drafts-data.php | ✅ 已创建 |
| 新版视图 | admin-galleries-new.php | admin-drafts-new.php | ✅ 已创建 |
| 路由配置 | single-works, galleries-new | drafts, drafts-new | ✅ 已配置 |
| 后台菜单 | Single-Works分类管理 | Drafts 草稿管理 | ✅ 已添加 |
| 前台菜单 | Single-Works | Drafts | ✅ 已修改 |
| 数据文件 | single-works-sort.json | drafts-sort.json | ⏳ 需创建 |
| 图片目录 | public/assets/images/single-works | public/assets/images/drafts | ⏳ 需创建 |

---

**创建日期**: 2026年1月22日  
**状态**: 文件和配置已完成，等待测试
