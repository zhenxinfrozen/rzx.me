# 视频格式支持说明

## 🎯 快速答案

### 1. 后端工具支持哪些视频格式？

**配置文件：** `app/Config/video_formats.php`

**支持格式：**
```
mp4, mkv, webm, avi, mov, flv, m4v, wmv, 
mpg, mpeg, ogv, 3gp, ts, mts, vob
```

**功能覆盖：**
- ✅ 目录扫描识别（`ScanVideos.php`）
- ✅ FFmpeg 缩略图生成（所有格式）
- ✅ 数据库记录（`video_data.json`）

---

### 2. 前台为何 MKV 无法播放？

**原因：浏览器 HTML5 `<video>` 标签限制**

#### 浏览器兼容性

| 格式 | Chrome | Firefox | Safari | Edge | 可播放 |
|------|--------|---------|--------|------|--------|
| MP4  | ✅ | ✅ | ✅ | ✅ | **是** |
| WebM | ✅ | ✅ | ⚠️ | ✅ | 是 |
| MKV  | ❌ | ❌ | ❌ | ❌ | **否** |
| AVI  | ❌ | ❌ | ❌ | ❌ | 否 |

**结论：MKV/AVI 等格式浏览器原生不支持**

---

## 📋 当前系统状态

### 已实现功能

✅ **后端扫描：** 20+ 种视频格式识别  
✅ **缩略图生成：** FFmpeg 支持所有格式截图  
✅ **数据存储：** JSON 正确记录 MKV/AVI 路径  
✅ **Windows 路径：** 中文/特殊字符文件名支持  

### 限制

❌ **前台播放：** 仅 MP4/WebM（HTML5 标准限制）  
⚠️ **MKV 显示：** 有缩略图但点击无法播放  

---

## 🛠️ 解决方案（未来后台升级）

### 方案 1：服务器端转码（推荐）

**工具：FFmpeg 批量转换**

```bash
# 高质量转码
ffmpeg -i input.mkv -c:v libx264 -crf 23 -c:a aac output.mp4

# 批量转换（PowerShell）
Get-ChildItem *.mkv | ForEach-Object {
    $out = $_.Name -replace '\.mkv$', '.mp4'
    ffmpeg -i $_.Name -c:v libx264 -crf 23 -c:a aac $out
}
```

### 方案 2：前端下载链接

**为不支持格式提供下载按钮**

修改 `app/Views/pages/videos.php`：
```javascript
// 检测不可播放格式
if (video.sources?.mkv || video.sources?.avi) {
    html += `<a href="${mkvSource}" download>下载 MKV</a>`;
}
```

---

## 📚 参考资料

### 相关配置文件

- `app/Config/video_formats.php` - 格式配置
- `app/Utils/VideoThumbnailGenerator.php` - 缩略图引擎
- `app/Views/pages/videos.php` - 前台播放页面

### 常用工具

```bash
# 扫描视频并生成缩略图
php app/Console/ScanVideos.php -f

# 批量重新生成缩略图
php regenerate-all-thumbnails.php

# 单目录重新生成
php regenerate-thumbnails.php test-bl
```

### HTML5 视频支持文档

- [MDN - Video Element](https://developer.mozilla.org/zh-CN/docs/Web/HTML/Element/video)
- [Can I Use - Video Format](https://caniuse.com/?search=video)

---

## ✅ 最佳实践

### 推荐工作流程

1. **上传视频：** 支持 MP4/MKV/AVI 等所有格式
2. **自动处理：** 系统自动扫描并生成缩略图
3. **前台显示：** 
   - MP4/WebM → 直接播放
   - MKV/AVI → 显示缩略图（待后台添加转码功能）

### 当前最优方案

**对于 MKV 文件：**
- 保留原文件（高质量存档）
- 手动转码为 MP4（Web 播放）
- 两个文件放同一目录，系统自动识别

**示例：**
```
test-bl/
  ├── 言叶之庭.mkv        (2.2GB, 原始文件)
  ├── 言叶之庭.mp4        (800MB, Web 播放)
  └── 言叶之庭.jpg        (17KB, 缩略图)
```

系统会优先使用 MP4 播放，MKV 作为备份。

---

## 📅 更新日志

- **2025-10-12**：MKV 格式支持，Windows 路径修复
