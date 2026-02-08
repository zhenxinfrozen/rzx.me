# Storage Data 目录

此目录集中存储所有 JSON 数据文件。

## 文件说明

### 排序配置
- `sketchbook-sort.json` - 速写本页面分类排序
- `single-works-sort.json` - 单幅作品页面分类排序
- `video-gallery-sort.json` - 视频画廊分类排序
- `image-orders.json` - 图片在分类内的排序

### 内容数据
- `comics.json` - 漫画作品数据
- `video-gallery.json` - 视频内容数据

### 缩略图配置
- `thumbnail-configs.json` - 自定义缩略图配置
- `thumbnail-presets.json` - 缩略图预设方案

## 重构历史

**v2.5.5 (2026-01-22)**
- 从 `app/storage/config/` 迁移所有 JSON 文件到此目录
- 统一数据存储位置，提高代码可维护性
- 更新所有代码引用，无向后兼容

## 文件权限

确保此目录可写：
```bash
chmod 775 app/storage/data
chmod 664 app/storage/data/*.json
```
