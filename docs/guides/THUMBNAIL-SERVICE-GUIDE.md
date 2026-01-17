# 🎯 缩略图服务统一化文档

## 📋 概述

新的缩略图服务系统实现了**页面自定义尺寸**和**存放目录自定义**，完全满足您的需求：

### ✅ 核心特性
- **🎨 页面自定义配置**：每个页面可以有不同的缩略图尺寸、质量、格式
- **📁 目录自定义**：支持自定义缩略图存放目录，避免冲突
- **🔧 统一API**：所有页面使用同一套服务，代码更简洁
- **⚡ 性能优化**：自动跳过已存在且较新的缩略图
- **🧹 自动清理**：支持清理孤立的缩略图文件

---

## 🚀 使用方法

### 1. 基本使用 - 预设配置

```php
// 引入服务
require_once __DIR__ . '/../../Services/ThumbnailService.php';

// Gallery页面 - 300x300px，存放在 thumbs/
ThumbnailService::generateForPage($imagePath, 'gallery');

// Single-Works页面 - 200x200px，存放在 works-thumbs/
ThumbnailService::generateForPage($imagePath, 'single-works');

// Sketch页面 - 150x150px，裁剪模式，存放在 sketch-thumbs/
ThumbnailService::generateForPage($imagePath, 'sketch');

// 图标用途 - 100x100px，PNG格式，存放在 icons/
ThumbnailService::generateForPage($imagePath, 'icon');
```

### 2. 自定义配置

```php
// 完全自定义配置
$customConfig = [
    'width' => 250,              // 自定义宽度
    'height' => 250,             // 自定义高度
    'quality' => 90,             // 图片质量 (1-100)
    'format' => 'webp',          // 输出格式 (jpg/png/webp)
    'crop' => true,              // 是否裁剪 (true=裁剪, false=缩放)
    'suffix' => '_my_thumb',     // 文件名后缀
    'directory' => 'my-thumbs'   // 存放目录名
];

ThumbnailService::generateForPage($imagePath, 'gallery', $customConfig);
```

### 3. 批量生成

```php
// 批量生成整个目录的缩略图
$results = ThumbnailService::generateBatchForPage($directoryPath, 'single-works');

// 自定义批量生成
$results = ThumbnailService::generateBatchForPage($directoryPath, 'gallery', $customConfig);
```

### 4. 获取缩略图路径

```php
// 获取已存在的缩略图路径
$thumbPath = ThumbnailService::getThumbnailPath($imagePath, 'gallery');

if ($thumbPath) {
    echo "缩略图存在: " . $thumbPath;
} else {
    echo "缩略图不存在，需要生成";
}
```

---

## 🎨 预设配置说明

| 页面类型 | 尺寸 | 质量 | 格式 | 存放目录 | 文件后缀 | 裁剪模式 |
|---------|------|------|------|----------|----------|----------|
| `gallery` | 300×300px | 85% | JPG | `thumbs/` | `_gallery` | 缩放 |
| `single-works` | 200×200px | 80% | JPG | `works-thumbs/` | `_works` | 缩放 |
| `sketch` | 150×150px | 75% | JPG | `sketch-thumbs/` | `_sketch` | 裁剪 |
| `icon` | 100×100px | 80% | PNG | `icons/` | `_icon` | 裁剪 |
| `large` | 800×600px | 90% | JPG | `large-previews/` | `_large` | 缩放 |
| `mobile` | 120×120px | 70% | WebP | `mobile-thumbs/` | `_mobile` | 裁剪 |
| `carousel` | 400×225px | 85% | JPG | `carousel-thumbs/` | `_carousel` | 裁剪 |
| `list` | 80×80px | 75% | JPG | `list-thumbs/` | `_list` | 裁剪 |

---

## 📂 目录结构示例

```
public/assets/images/
├── galleries/
│   └── AAA/
│       ├── image1.jpg              # 原图
│       ├── image2.jpg              # 原图
│       ├── thumbs/         # Gallery页面缩略图
│       │   ├── image1_gallery.jpg
│       │   └── image2_gallery.jpg
│       ├── sketch-thumbs/          # Sketch页面缩略图
│       │   ├── image1_sketch.jpg
│       │   └── image2_sketch.jpg
│       └── icons/                  # 图标缩略图
│           ├── image1_icon.png
│           └── image2_icon.png
└── single-works/
    └── Animals/
        ├── photo1.jpg              # 原图
        ├── photo2.jpg              # 原图
        └── works-thumbs/           # Single-Works页面缩略图
            ├── photo1_works.jpg
            └── photo2_works.jpg
```

---

## 🔧 高级功能

### 响应式配置

```php
require_once __DIR__ . '/../../Config/ThumbnailConfig.php';

// 根据设备类型获取配置
$mobileConfig = ThumbnailConfig::getResponsiveConfig('mobile');    // 120×120px
$tabletConfig = ThumbnailConfig::getResponsiveConfig('tablet');    // 200×200px  
$desktopConfig = ThumbnailConfig::getResponsiveConfig('desktop');  // 300×300px

// 使用响应式配置
ThumbnailService::generateForPage($imagePath, 'gallery', $mobileConfig);
```

### 自定义配置模板

```php
// 添加新的页面配置
ThumbnailService::setPageConfig('my-page', [
    'width' => 400,
    'height' => 300,
    'quality' => 85,
    'format' => 'webp',
    'crop' => false,
    'suffix' => '_mypage',
    'directory' => 'mypage-thumbs'
]);

// 使用新配置
ThumbnailService::generateForPage($imagePath, 'my-page');
```

### 清理孤立文件

```php
// 清理指定目录的孤立缩略图
$deletedCount = ThumbnailService::cleanOldThumbnails($directoryPath, 'gallery');
echo "删除了 {$deletedCount} 个孤立的缩略图文件";
```

---

## 🔄 页面集成示例

### Gallery页面集成

```php
// app/Views/pages/gallery.php
require_once __DIR__ . '/../../Services/ThumbnailService.php';

$galleryPath = __DIR__ . '/../../../public/assets/images/galleries/' . $galleryName;
ThumbnailService::generateBatchForPage($galleryPath, 'gallery');

// 在模板中使用
$thumbPath = ThumbnailService::getThumbnailPath($imagePath, 'gallery');
if ($thumbPath) {
    echo '<img src="' . $thumbPath . '" alt="缩略图">';
}
```

### Single-Works页面集成

```php  
// app/Views/pages/single-works.php
require_once __DIR__ . '/../../Services/ThumbnailService.php';

foreach ($categoriesData as $categoryData) {
    $categoryPath = __DIR__ . '/../../../public/assets/images/single-works/' . $categoryData['name'];
    ThumbnailService::generateBatchForPage($categoryPath, 'single-works');
}
```

---

## 🎯 优势总结

### ✅ 解决的问题
1. **尺寸统一性** - 不同页面有不同的缩略图需求，现在可以独立配置
2. **目录冲突** - 每种用途的缩略图存放在独立目录，避免覆盖
3. **代码重复** - 统一的API，避免每个页面重复实现缩略图逻辑
4. **维护困难** - 集中管理配置，便于后续调整和维护

### 🚀 新增能力
1. **灵活配置** - 支持任意尺寸、质量、格式、目录的组合配置
2. **响应式支持** - 可根据设备类型生成不同规格的缩略图
3. **批量处理** - 支持整个目录的批量生成和管理
4. **智能优化** - 自动跳过已存在的缩略图，提高性能
5. **自动清理** - 支持清理孤立文件，保持存储整洁

完美实现了您提出的**"各自页面的缩略图的大小自定义，缩略图存放目录自定义"**需求！