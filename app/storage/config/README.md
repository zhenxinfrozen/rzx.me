# Storage Config Directory

此目录用于存储后台系统动态生成的配置文件。

## 文件说明

- `video-gallery-sort.json` - Video Gallery 分组排序配置
- `single-works-sort.json` - Single Works 分组排序配置
- `sketchbook-sort.json` - Sketchbook 分组排序配置
- `image-orders.json` - 图片排序配置
- `thumbnail-configs.json` - 缩略图配置

## 注意事项

- 这些文件由后台管理系统自动维护
- 已添加到 `.gitignore`，不会提交到 Git
- 首次部署时会自动创建默认配置
