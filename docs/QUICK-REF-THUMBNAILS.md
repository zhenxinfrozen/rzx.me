# 🚀 视频缩略图系统 - 快速参考

## 添加新视频（3步完成）

1. **放入视频文件**
   ```
   public/assets/videos/video-gallery/分组名/视频.mp4
   ```

2. **刷新浏览器页面**
   ```
   Ctrl + F5 (强制刷新)
   ```

3. **完成！** ✅
   - 缩略图自动生成
   - 数据库自动更新
   - 无需任何命令

---

## 修复黑色缩略图

```bash
php fix-low-quality-thumbnails.php
```

然后刷新浏览器（Ctrl+F5）

---

## 当前状态

| 项目 | 状态 |
|------|------|
| 总视频数 | 36 ✅ |
| 分组数 | 5 ✅ |
| FFmpeg | ✅ 已安装 |
| 自动化 | ✅ 已启用 |
| 缩略图质量 | 100% 高质量 ✅ |

---

## 质量标准

- **高质量**: > 5KB，FFmpeg真实截图 ✅
- **低质量**: < 5KB，GD灰色图标 ❌（自动替换）

---

## 常用命令（可选）

```bash
# 修复低质量缩略图
php fix-low-quality-thumbnails.php

# 刷新数据库
php app/Console/ScanVideos.php -f

# 测试系统
php test-auto-thumbnail.php
```

---

## 完整文档

详见：`AUTO-THUMBNAIL-GUIDE.md`
