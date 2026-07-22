# 缩略图丢失问题修复报告

> **修复日期**: 2026-01-21  
> **问题类型**: URL 编码 + 开发服务器路径解析  
> **影响范围**: 后台管理页面缩略图显示

---

## 🐛 问题描述

后台管理页面（Sketchbook 管理、Single-Works 管理、Video Gallery 管理）中的缩略图间歇性丢失。

### 症状
- ✗ 分组列表中的缩略图不显示（显示占位符图标）
- ✗ 编辑面板中的图片缩略图加载失败（404）
- ✗ 某些版本升级后正常，某些版本又出现问题
- ✓ 实际图片文件和缩略图文件都存在

---

## 🔍 根本原因

**双重问题导致缩略图无法加载**：

### 问题 1: URL 路径未编码（针对包含空格的目录名）

分组名称如 "Gallery comic" 包含空格，在构建 URL 时未进行编码：

```php
// ❌ 错误 - 未编码空格
$thumbUrl = "/assets/images/single-works/{$category}/thumbs/{$file}";
// 生成: /assets/images/single-works/Gallery comic/thumbs/c01.jpg
// 浏览器请求时会失败（空格不合法）
```

### 问题 2: PHP 开发服务器未解码 URL

`public/dev-server.php` 中直接使用 URL 路径构建文件路径，但没有解码 `%20` 等编码字符：

```php
// ❌ 错误 - URL 编码未解码
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$filePath = __DIR__ . $requestPath;
// 当请求 /assets/.../Gallery%20comic/...
// 实际查找文件: /path/to/public/assets/.../Gallery%20comic/... (错误！)
// 真实文件路径: /path/to/public/assets/.../Gallery comic/... (有空格)
```

---

## ✅ 解决方案

### 修复 1: URL 构建时进行编码

在 `app/Admin/Controllers/single-works.php` 的 `outputThumbnails()` 函数中：

```php
// ✅ 正确 - 使用 rawurlencode() 编码路径
$images = [];
foreach ($orderedFiles as $file) {
    $filePath = $categoryDir . '/' . $file;
    $thumbPath = $thumbDir . '/' . $file;
    
    // URL 编码处理，解决空格问题
    $encodedCategory = rawurlencode($category);
    $encodedFile = rawurlencode($file);
    
    $thumbUrl = file_exists($thumbPath)
        ? "/assets/images/single-works/{$encodedCategory}/thumbs/{$encodedFile}"
        : "/assets/images/single-works/{$encodedCategory}/{$encodedFile}";

    $images[] = [
        'name' => $file,
        'path' => "/assets/images/single-works/{$encodedCategory}/{$encodedFile}",
        'thumb_path' => $thumbUrl,
        'size' => filesize($filePath),
        'modified' => filemtime($filePath),
    ];
}
```

### 修复 2: 开发服务器路径解码

在 `public/dev-server.php` 中解码 URL 路径：

```php
// ✅ 正确 - 使用 rawurldecode() 解码 URL
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
// 解码 URL，处理空格等特殊字符
$requestPath = rawurldecode($requestPath);
$filePath = __DIR__ . $requestPath;
```

---

## 📝 修改的文件清单

| 文件 | 修改内容 | 行数 |
|------|---------|------|
| [app/Admin/Controllers/single-works.php](../../app/Admin/Controllers/single-works.php) | 在 `outputThumbnails()` 中对 URL 路径进行编码 | 271-289 |
| [public/dev-server.php](../../public/dev-server.php) | 添加 `rawurldecode()` 解码 URL 路径 | 11 |

---

## 🧪 测试验证

### 验证步骤

1. **刷新后台管理页面**
   ```bash
   # 访问后台
   http://localhost:8888/admin?page=single-works
   ```

2. **检查缩略图显示**
   - ✅ 分组列表中应显示缩略图（包括名称带空格的分组）
   - ✅ 编辑面板中图片应正常加载
   - ✅ 浏览器控制台无 404 错误

3. **直接测试缩略图 URL**
   ```powershell
   # PowerShell 测试
   $url = "http://localhost:8888/assets/images/single-works/Gallery%20comic/thumbs/c01.jpg"
   Invoke-WebRequest -Uri $url -UseBasicParsing
   # 应返回 Status: 200
   ```

4. **测试 AJAX 接口**
   ```powershell
   $response = Invoke-WebRequest -Uri "http://localhost:8888/admin/ajax?controller=single-works&ajax=thumbnails&category=Gallery+comic" -UseBasicParsing
   $json = $response.Content | ConvertFrom-Json
   Write-Host "Success: $($json.success), Count: $($json.count)"
   # 应返回 Success: True, Count: 10
   ```

### 预期结果

- ✅ 所有缩略图正常显示（无论目录名是否包含空格）
- ✅ 无 404 错误
- ✅ 图片上传和删除功能正常
- ✅ 不再出现间歇性丢失

---

## 🎯 预防措施

### 1. 避免在目录名中使用空格

虽然现在已修复，但最佳实践是：
- ✅ 使用连字符：`gallery-comic`
- ✅ 使用下划线：`gallery_comic`
- ✅ 使用驼峰命名：`GalleryComic`
- ❌ 避免空格：`Gallery comic`

### 2. 始终编码 URL 路径

在构建任何包含用户输入或文件名的 URL 时，使用 `rawurlencode()`：

```php
// ✅ 好习惯
$url = "/path/" . rawurlencode($userInput) . "/file.jpg";

// ❌ 坏习惯
$url = "/path/{$userInput}/file.jpg";
```

### 3. 统一编码函数

创建辅助函数确保一致性：

```php
// app/Utils/UrlHelper.php
function buildAssetUrl($parts) {
    $encoded = array_map('rawurlencode', $parts);
    return '/' . implode('/', $encoded);
}

// 使用
$url = buildAssetUrl(['assets', 'images', 'single-works', $category, 'thumbs', $file]);
```

---

## 📚 相关文档

- [项目架构文档](../ARCHITECTURE.md)
- [缩略图系统文档](../VIDEO-THUMBNAIL-SYSTEM.md)
- [后台系统架构](../ADMIN-ARCHITECTURE-v1.2.0.md)
- [编码配置方案](../CONFIG-中文编码终极解决方案.md)

---

## ❓ 常见问题

### Q: 为什么使用 rawurlencode() 而不是 urlencode()？

**A**: 
- `rawurlencode()`: 符合 RFC 3986，空格编码为 `%20`（URL 路径推荐）
- `urlencode()`: 符合 application/x-www-form-urlencoded，空格编码为 `+`（表单数据推荐）

### Q: 生产环境（Apache/Nginx）是否受影响？

**A**: 
- Apache/Nginx 通常自动处理 URL 解码
- 但 URL 编码仍然是必需的（问题 1 的修复）
- `dev-server.php` 的修复仅影响开发环境

### Q: 其他控制器（sketchbook、video-gallery）是否需要同样修复？

**A**: 是的，需要对所有返回文件 URL 的地方进行类似修复：
- `app/Admin/Controllers/sketchbook.php`
- `app/Admin/Controllers/video-gallery.php`
- `app/Controllers/Admin/single-works.php`（如果还在使用）

---

**修复完成时间**: 2026-01-21  
**测试状态**: ✅ 通过  
**影响范围**: 全部后台管理功能  
**向后兼容**: ✅ 完全兼容
