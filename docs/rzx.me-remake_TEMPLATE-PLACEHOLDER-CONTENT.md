# 模板占位内容完整清单

## 📄 文档说明
本文档列出 `admin-template-new.php` 中所有占位内容的位置、用途和替换方法。

**目标受众**: 开发者和 AI Agent  
**更新日期**: 2024  
**模板版本**: v1.1.0

---

## 🎯 占位内容总览

| 类型 | 位置 | 数量 | 用途 |
|------|------|------|------|
| PHP数据数组 | 文件顶部 | 1个 | 演示列表数据结构 |
| SVG占位图 | `/assets/images/template/` | 3个 | 演示缩略图功能 |
| HTML表单 | 中栏面板 | 2组 | 演示添加/编辑界面 |
| 统计卡片 | 右栏 | 2个 | 演示数据统计 |
| 快速操作按钮 | 右栏 | 3个 | 演示常用功能 |
| JavaScript函数 | 底部script | 6个 | 演示CRUD操作 |

---

## 📦 详细清单

### 1. PHP 占位数据

#### `$categoryData` 数组
**文件位置**: admin-template-new.php, 第5-24行

```php
// 🎭 TEMPLATE PLACEHOLDER DATA - 以下数据仅供展示
// 实际使用时应从数据库/JSON/API 加载
$categoryData = [
    [
        'id' => 'example-category-1',
        'display_name' => '示例分类 1 (占位)',
        'description' => '这是模板占位数据',
        'thumbnail' => '/assets/images/template/placeholder-1.svg',
        'image_count' => 5,
        'position' => 1
    ],
    // ... 共3组数据
];
```

**用途**: 
- 演示左栏列表渲染
- 展示数据结构示例
- 测试拖拽排序功能

**替换方法**:
```php
// ❌ 删除占位数据
unset($categoryData);

// ✅ 加载实际数据
require_once __DIR__ . '/../../Controllers/your-data-loader.php';
$categoryData = load_your_module_data();
```

**依赖项**: 
- 左栏的 `foreach` 循环
- 统计卡片的 `count($categoryData)`

---

### 2. SVG 占位图片

#### 占位图片文件
**文件位置**: `/public/assets/images/template/`

| 文件名 | 尺寸 | 颜色 | 用途 |
|--------|------|------|------|
| placeholder-1.svg | 150x150 | 灰色 #e9ecef | 示例缩略图1 |
| placeholder-2.svg | 150x150 | 蓝色 #d1ecf1 | 示例缩略图2 |
| placeholder-3.svg | 150x150 | 绿色 #d4edda | 示例缩略图3 |
| README.md | - | - | 说明文档 |

**SVG 结构**:
```xml
<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
  <rect width="150" height="150" fill="#e9ecef"/>
  <text x="75" y="75" text-anchor="middle" 
        font-size="14" fill="#6c757d">
    TEMPLATE PLACEHOLDER
  </text>
</svg>
```

**用途**:
- 演示左栏列表的缩略图
- 演示中栏的图片管理器
- 提供视觉参考

**替换方法**:
```php
// 在数据加载时修改路径
'thumbnail' => '/assets/images/books/cover-' . $id . '.jpg'

// 或删除整个目录
// rm -rf public/assets/images/template/
```

**引用位置**:
- `$categoryData` 数组的 `thumbnail` 字段
- 中栏编辑面板的 `<img src="...">`

---

### 3. HTML 表单占位

#### 添加面板 `#add-panel`
**文件位置**: admin-template-new.php, 第138-166行

**包含字段**:
```html
- 名称输入框（必填）
- 显示名称输入框
- 排序位置下拉框（第一个/最后一个）
- 描述文本域
- 创建/取消按钮
```

**用途**: 演示添加新项目的表单结构

**替换要点**:
- 根据实际业务修改字段
- 添加表单验证
- 实现 `createItem()` 函数

---

#### 编辑面板 `#edit-panel`
**文件位置**: admin-template-new.php, 第168-215行

**包含内容**:
```html
- 显示名称输入框（预填：示例分类 1 (占位)）
- 描述文本域（预填：这是模板占位数据）
- 图片管理器（显示2个占位图）
- 保存/删除按钮
```

**图片管理器结构**:
```html
<div class="admin-thumbnail-grid-container">
    <div class="admin-image-item">
        <img src="/assets/images/template/placeholder-1.svg">
        <div class="admin-image-actions">
            <button class="star"><i data-feather="star"></i></button>
            <button class="delete"><i data-feather="trash-2"></i></button>
        </div>
    </div>
    <!-- ... 第2个图片 -->
    <div class="admin-image-upload-btn">
        <i data-feather="plus"></i>上传图片
    </div>
</div>
```

**用途**: 
- 演示编辑表单结构
- 展示图片管理组件用法
- 提供完整的交互示例

---

### 4. 统计卡片占位

#### 卡片1: 分类总数
**文件位置**: admin-template-new.php, 第231-234行

```html
<div class="admin-stat-card mb-3">
    <h3><?= $totalCategories ?></h3>
    <p>[MODULE]总数 (占位)</p>
</div>
```

**用途**: 演示基础统计卡片

---

#### 卡片2: 总项目数
**文件位置**: admin-template-new.php, 第235-239行

```html
<div class="admin-stat-card" 
     style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
    <h3>99</h3>
    <p>总项目数 (占位)</p>
</div>
```

**用途**: 演示带渐变背景的统计卡片

**替换方法**:
```php
// 计算实际统计值
$totalItems = array_sum(array_column($categoryData, 'image_count'));
```

---

### 5. 快速操作按钮占位

**文件位置**: admin-template-new.php, 第247-255行

| 按钮 | 图标 | 颜色 | 功能 |
|------|------|------|------|
| 刷新数据 | refresh-cw | btn-outline-primary | `location.reload()` |
| API测试 | server | btn-outline-success | `alert('API测试功能 - 占位')` |
| 导出配置 | download | btn-outline-info | `alert('导出功能 - 占位')` |

**用途**: 演示常用工具按钮

**替换方法**:
```javascript
// 实现实际功能
onclick="exportConfig()"   // 替代 alert()
onclick="testApi()"        // 替代 alert()
```

---

### 6. JavaScript 函数占位

**文件位置**: admin-template-new.php, 第280-360行

#### 函数清单

| 函数名 | 参数 | 当前行为 | 实际需求 |
|--------|------|----------|----------|
| `showAddPanel()` | 无 | 显示添加面板 | ✅ 已完成（无需修改） |
| `cancelAdd()` | 无 | 隐藏添加面板 | ✅ 已完成（无需修改） |
| `editCategory(id)` | id | 显示编辑面板 + console.log | ⚠️ 需要加载实际数据 |
| `createItem()` | 无 | alert + console.log | ❌ 需要实现 AJAX 创建 |
| `saveItem()` | 无 | alert + console.log | ❌ 需要实现 AJAX 保存 |
| `deleteItem()` | 无 | alert + confirm | ❌ 需要实现 AJAX 删除 |
| `saveSort()` | 无 | alert + console.log | ❌ 需要实现 AJAX 排序 |

---

#### 函数详情

##### `editCategory(id)` - 需要补充数据加载
```javascript
function editCategory(id) {
    currentEditId = id;
    
    // ✅ 已完成：UI切换逻辑
    // ...
    
    // ❌ TODO: 加载实际数据
    fetch(`/admin/ajax?controller=[module]&ajax=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit-name').value = data.display_name;
            // ... 填充其他字段
        });
}
```

##### `createItem()` - 需要实现创建
```javascript
function createItem() {
    const name = document.getElementById('new-name').value;
    if (!name) {
        alert('请输入名称');
        return;
    }
    
    // ❌ 当前：占位代码
    alert('创建功能 - 占位\n名称: ' + name);
    
    // ✅ 应该：AJAX 请求
    const formData = new FormData();
    formData.append('name', name);
    formData.append('display_name', document.getElementById('new-display-name').value);
    // ... 其他字段
    
    fetch('/admin/ajax?controller=[module]&ajax=create', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('创建失败: ' + data.error);
        }
    });
}
```

##### `saveItem()` - 需要实现保存
```javascript
function saveItem() {
    // ❌ 当前：占位代码
    alert('保存功能 - 占位\nID: ' + currentEditId);
    
    // ✅ 应该：AJAX 请求
    const formData = new FormData();
    formData.append('id', currentEditId);
    formData.append('display_name', document.getElementById('edit-name').value);
    // ... 其他字段
    
    fetch('/admin/ajax?controller=[module]&ajax=update', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
```

##### `deleteItem()` - 需要实现删除
```javascript
function deleteItem() {
    if (!confirm('确定要删除这个项目吗？')) {
        return;
    }
    
    // ❌ 当前：占位代码
    alert('删除功能 - 占位\nID: ' + currentEditId);
    
    // ✅ 应该：AJAX 请求
    fetch('/admin/ajax?controller=[module]&ajax=delete', {
        method: 'POST',
        body: JSON.stringify({ id: currentEditId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
```

##### `saveSort()` - 需要实现排序
```javascript
function saveSort() {
    const items = document.querySelectorAll('.admin-sortable-item');
    const order = Array.from(items).map((item, index) => ({
        id: item.dataset.id,
        position: index + 1
    }));
    
    // ❌ 当前：占位代码
    alert('排序功能 - 占位\n共 ' + order.length + ' 项');
    
    // ✅ 应该：AJAX 请求
    fetch('/admin/ajax?controller=[module]&ajax=save-sort', {
        method: 'POST',
        body: JSON.stringify({ order }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 显示成功提示（不刷新页面）
            showToast('排序已保存');
        }
    });
}
```

---

## 🔍 如何识别占位内容

### 视觉标记
- 🎭 表情符号：`🎭 TEMPLATE PLACEHOLDER`
- 文本后缀：`(占位)` 或 `占位示例`
- 特定路径：`/assets/images/template/`

### 代码标记
- 注释：`<!-- TEMPLATE PLACEHOLDER: ... -->`
- 注释：`// TEMPLATE: ...` 或 `// TODO: ...`
- 函数调用：`alert('XXX - 占位')`
- 日志输出：`console.log('占位', ...)`

### 数据标记
- ID 前缀：`example-category-1`
- 显示名称：包含 `示例` 或 `(占位)`
- 固定数字：`99`（右栏统计）

---

## ✅ 替换检查清单

### 准备阶段
- [ ] 阅读 TEMPLATE-USAGE-GUIDE.md
- [ ] 理解模板三栏布局结构
- [ ] 准备实际数据源（数据库表/JSON文件/API端点）

### 数据层替换
- [ ] 删除 `$categoryData` 占位数组
- [ ] 实现实际数据加载函数
- [ ] 更新统计逻辑（`$totalCategories`, 总项目数）

### 视图层替换
- [ ] 全局替换 `[MODULE]` 和 `[module]`
- [ ] 修改表单字段为实际业务字段
- [ ] 更新图片路径（不使用 `/template/`）
- [ ] 移除 `(占位)` 文本标记

### 逻辑层替换
- [ ] 实现 `editCategory()` 的数据加载
- [ ] 实现 `createItem()` 的 AJAX 创建
- [ ] 实现 `saveItem()` 的 AJAX 保存
- [ ] 实现 `deleteItem()` 的 AJAX 删除
- [ ] 实现 `saveSort()` 的 AJAX 排序
- [ ] 删除所有 `alert()` 占位代码

### 清理工作
- [ ] 删除所有占位注释（`🎭 TEMPLATE PLACEHOLDER`）
- [ ] 删除 `/assets/images/template/` 目录
- [ ] 运行页面测试所有功能
- [ ] 验证拖拽排序功能
- [ ] 验证图片上传/删除功能

### 文档更新
- [ ] 更新路由注释（AdminIndexController.php）
- [ ] 添加模块说明文档（如需要）
- [ ] 更新 README（如涉及新功能）

---

## 🤖 给 AI Agent 的执行建议

### 识别模式
当用户说"基于模板创建XX管理页面"时：
1. 复制 `admin-template-new.php` → `admin-XX-new.php`
2. 全局替换占位符（[MODULE]/[module]）
3. 询问用户数据结构（字段、类型、来源）
4. 实现数据加载逻辑
5. 实现 AJAX CRUD 端点
6. 删除所有占位标记

### 关键问题清单
- ✅ 数据从哪里来？（JSON文件 / 数据库表 / API）
- ✅ 需要哪些字段？（名称、描述、图片、排序...）
- ✅ 是否需要图片管理？（如不需要，删除图片管理器代码）
- ✅ 统计信息显示什么？（总数、项目数、其他指标）
- ✅ 需要哪些快速操作？（刷新、导出、批量删除...）

### 实现优先级
1. **P0 - 必须实现**: 数据加载、列表显示、编辑保存
2. **P1 - 重要功能**: 创建新项、删除项、拖拽排序
3. **P2 - 增强功能**: 图片管理、批量操作、统计卡片
4. **P3 - 可选功能**: 快速操作按钮、高级搜索、导出功能

### 测试检查点
- ✅ 页面能正常打开（无PHP错误）
- ✅ 左栏列表能正常显示
- ✅ 点击列表项能切换到编辑模式
- ✅ 添加按钮能显示添加面板
- ✅ 保存操作能成功（检查数据库/JSON）
- ✅ 删除操作能成功且有确认
- ✅ 拖拽排序能保存新顺序
- ✅ Feather Icons 能正常渲染

---

## 📚 相关文档
- [TEMPLATE-USAGE-GUIDE.md](./TEMPLATE-USAGE-GUIDE.md) - 使用指南
- [NEW-PAGES-ARCHITECTURE-COMPLIANCE.md](./NEW-PAGES-ARCHITECTURE-COMPLIANCE.md) - 架构规范
- [public/assets/images/template/README.md](../public/assets/images/template/README.md) - 占位图说明

---

**文档版本**: v1.0.0  
**模板版本**: v1.1.0  
**最后更新**: 2024  
**维护者**: 开发团队
