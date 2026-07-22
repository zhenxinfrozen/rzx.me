# Admin Controllers 功能分析与整合建议

## 📊 现有 Controllers 功能对比

### 1️⃣ **Comics.php** - 漫画管理器
**特殊功能：**
- ✅ 管理单个漫画条目（非文件夹结构）
- ✅ 每个漫画有多张图片（image[]）
- ✅ 独特字段：title, subtitle, lines, alt, status
- ✅ 支持图标对（icon_default + icon_hover）
- ✅ 数据存储在 JSON 文件而非文件系统
- ✅ 排序功能（drag & drop）

**目录结构：**
```
public/assets/images/comic/
  ├── comic-001.jpg          ← 直接存储，无子文件夹
  ├── comic-002.jpg
  └── thumbs/
      ├── xxx-icon-default.jpg
      └── xxx-icon-hover.jpg
```

**结论：❌ 不适合使用 media-manager**  
原因：数据模型完全不同，是单个条目管理而非分类文件夹管理。

---

### 2️⃣ **Videos.php** - 视频管理器
**特殊功能：**
- ✅ 视频文件处理（.mp4, .mov, .avi, .mkv）
- ✅ FFmpeg 集成（自动生成视频缩略图）
- ✅ 视频元数据提取（时长、分辨率、编码器）
- ✅ 支持上传自定义缩略图（替换自动生成的）
- ✅ 视频分组管理（groups）
- ✅ 特殊的 `get_group_videos` API
- ✅ 文件大小限制（视频通常更大）

**AJAX 端点：**
- `scan_directory` - 扫描视频目录
- `get_group_videos` - 获取分组视频
- `videos` - 获取分类视频列表
- `upload_videos` - 上传视频
- `upload_thumbnail` - 上传自定义缩略图
- `delete_thumbnail` - 删除缩略图
- `reorder_videos` - 视频排序
- `php_config` - PHP 配置检查

**结论：⚠️ 部分可以整合**  
- 基础文件管理可以用 media-manager
- 但需要保留视频特有功能（FFmpeg、元数据提取）

---

### 3️⃣ **Sketchbook.php** - 速写本管理器
**特殊功能：**
- ✅ 图片分类管理（标准结构）
- ✅ 自定义缩略图支持
- ✅ 图片排序（order 配置）
- ✅ 显示名称和描述配置
- ✅ 文件夹重命名功能
- ✅ 分类排序（拖拽）
- ✅ 缩略图服务集成

**AJAX 端点：**
- `thumbnails` - 获取图片列表 ✅ 可替换
- `upload_images` - 上传图片 ✅ 可替换
- `create_category` - 创建分类 ✅ 可替换
- `delete_image` - 删除图片 ✅ 可替换
- `delete_category` - 删除分类 ✅ 可替换
- `reorder_images` - 图片排序 ⚠️ 需要配置文件
- `set_thumbnail` - 设置封面 ⚠️ 需要配置文件
- `upload_thumbnail` - 上传自定义缩略图 ⚠️ 特殊功能
- `save_category` - 保存分类信息 ⚠️ 需要配置文件

**结论：✅ 最适合整合到 media-manager**  
这是最标准的文件夹+图片结构，应该作为 media-manager 的蓝本。

---

### 4️⃣ **Drafts.php** - 草稿管理器
**功能分析：** （与 Sketchbook 几乎相同）
- ✅ 图片分类管理
- ✅ 自定义缩略图
- ✅ 排序和配置

**结论：✅ 完全可以整合到 media-manager**  
Drafts 和 Sketchbook 功能完全一致，只是模块名称不同。

---

### 5️⃣ **Galleries.php** - 画廊管理器
**功能分析：** （从 Sketchbook 复制）
- ✅ 与 Sketchbook 完全相同的功能

**结论：✅ 已经在使用 media-manager**  
我们刚刚完成了这个整合。

---

## 🎯 整合建议

### ✅ **可以完全整合到 media-manager 的模块：**
1. **Sketchbook** - 标准图片管理
2. **Drafts** - 标准图片管理
3. **Galleries** - 已完成整合

### ⚠️ **需要扩展 media-manager 才能整合：**
4. **Videos** - 需要添加视频特有功能：
   - FFmpeg 缩略图生成
   - 视频元数据提取
   - 大文件处理
   - 自定义缩略图上传

### ❌ **不应该整合的模块：**
5. **Comics** - 数据模型完全不同，保持独立

---

## 📝 Media-Manager 需要增强的功能

为了支持 Sketchbook、Drafts、Videos 的完整功能，`media-manager.php` 需要添加：

### 1. 配置文件管理
```php
// 读取/保存分类配置（display_names, descriptions, custom_order）
function loadModuleConfig(string $module): array;
function saveModuleConfig(string $module, array $config): bool;
```

### 2. 缩略图服务集成
```php
// 生成缩略图（图片）
function ensureThumbnail(string $imagePath, string $module): ?string;

// 生成缩略图（视频，需要 FFmpeg）
function ensureVideoThumbnail(string $videoPath): ?string;
```

### 3. 自定义缩略图上传
```php
case 'upload_thumbnail':
    uploadCustomThumbnail($module, $imagesRoot, $configPath);
    break;
```

### 4. 文件重命名功能
```php
case 'rename_category':
    renameCategory($module, $imagesRoot, $configPath);
    break;
```

### 5. 排序配置
```php
case 'save_category_order':
    saveCategoryOrder($module, $configPath);
    break;

case 'reorder_images':
    reorderImages($module, $imagesRoot, $imageOrderPath);
    break;
```

---

## 🚀 迁移计划

### Phase 1: 完善 media-manager（1-2小时）
1. 添加配置文件读写功能
2. 添加缩略图服务集成
3. 添加自定义缩略图上传
4. 添加排序功能

### Phase 2: 迁移 Drafts（30分钟）
1. 修改 admin-drafts-new.php 使用 media-manager
2. 测试所有功能
3. 备份并移除 drafts.php

### Phase 3: 迁移 Sketchbook（30分钟）
1. 修改 admin-sketchbook-new.php 使用 media-manager
2. 测试所有功能
3. 保留 sketchbook.php 作为参考

### Phase 4: 扩展 Videos 支持（2-3小时）
1. 在 media-manager 中添加 FFmpeg 支持
2. 添加视频元数据提取
3. 测试视频上传和缩略图生成
4. 逐步迁移 videos.php

---

## 📊 预期效果

**整合前：**
- 5 个独立的 controller 文件
- 重复代码 ~80%
- 难以维护和更新

**整合后：**
- 1 个通用 media-manager.php
- 2 个特殊 controller（comics.php, videos.php 部分功能）
- 代码复用率 90%+
- 统一的 API 接口
- 更容易添加新模块

---

## 🎨 新模块添加流程（整合后）

添加新的媒体管理模块只需要：

1. 创建目录结构：
   ```
   public/assets/images/new-module/
     ├── category1/
     │   ├── file1.jpg
     │   └── thumbs/
     └── category2/
   ```

2. 修改前端页面调用：
   ```javascript
   const controllerUrl = '/admin/ajax?controller=media-manager&module=new-module';
   ```

3. 完成！无需编写新的 controller

---

## ⚠️ 注意事项

1. **向后兼容**：旧的 controller 文件应该保留一段时间
2. **测试覆盖**：每个功能都需要测试
3. **文档更新**：更新开发文档和 API 文档
4. **数据备份**：迁移前备份所有配置文件

---

## 📚 相关文件

- ✅ [media-manager.php](d:\VS CODE\rzx-me\app\Admin\Controllers\media-manager.php) - 通用控制器
- ⚠️ [videos.php](d:\VS CODE\rzx-me\app\Admin\Controllers\videos.php) - 需要保留视频特有功能
- ✅ [sketchbook.php](d:\VS CODE\rzx-me\app\Admin\Controllers\sketchbook.php) - 可以完全整合
- ✅ [drafts.php](d:\VS CODE\rzx-me\app\Admin\Controllers\drafts.php) - 可以完全整合
- ❌ [comics.php](d:\VS CODE\rzx-me\app\Admin\Controllers\comics.php) - 保持独立
- ✅ [galleries.php](d:\VS CODE\rzx-me\app\Admin\Controllers\galleries.php) - 已完成整合

---

生成时间：2026-01-22
