# Videos 视频系统快速使用指南

## 📂 目录结构

```
public/assets/videos/video-gallery/
├── animation-clips/      # 动画片段
│   ├── video1.mp4
│   ├── video1.jpg       # 预览图（与视频同名）
│   ├── video2.mp4
│   └── video2.jpg
├── comic-anim/          # 漫画动画
├── storyboard/          # 故事板
└── test-ex/             # 测试练习
```

## 🚀 使用流程

### 1. 添加视频文件

将视频文件和预览图放入对应的分组目录：

```
public/assets/videos/video-gallery/animation-clips/
├── my-animation.mp4      # 视频文件
└── my-animation.jpg      # 预览图（必需，与视频同名）
```

### 2. 扫描视频目录

运行扫描命令自动生成视频数据：

```powershell
# Windows PowerShell
php app/Console/ScanVideos.php

# 强制重新扫描（覆盖现有数据）
php app/Console/ScanVideos.php --force
```

### 3. 查看效果

访问页面：`http://localhost/videos`

## 📋 重要说明

### 预览图要求
- ✅ 必须与视频文件同名（例如：video.mp4 对应 video.jpg）
- ✅ 支持格式：jpg, jpeg, png, gif, webp
- ✅ 如果没有同名预览图，会使用目录中的第一张图片

### 视频格式支持
- ✅ MP4（推荐，最佳兼容性）
- ✅ WebM（可选，更小的文件大小）
- ✅ MOV, AVI, OGV（其他格式）

### 目录命名规则
- 目录名会自动转换为分组标题
- 例如：`animation-clips` → "Animation clips"
- 使用 `-` 或 `_` 分隔单词

## 🎨 页面特性

### 1. 分组菜单
- 缩略图垂直排列（100%宽度）
- 显示前5个视频的预览图
- 纯CSS overlay hover效果

### 2. 视频展示
- 响应式网格布局（2-5列）
- 简洁样式，无圆角
- 标题和描述居中显示

### 3. 响应式列数
| 屏幕宽度 | 列数 |
|---------|------|
| ≥1600px | 5列 |
| 1200-1599px | 4列 |
| 900-1199px | 3列 |
| 600-899px | 2列 |
| <600px | 2列（紧凑） |

## 🛠️ 手动编辑数据

如果需要手动编辑视频信息，编辑：`app/storage/data/video_data.json`

```json
{
  "my-group": {
    "title": "我的分组",
    "description": "分组描述",
    "status": "active",
    "order": 1,
    "videos": [
      {
        "title": "视频标题",
        "description": "视频描述（可选）",
        "poster": "/assets/videos/video-gallery/my-group/poster.jpg",
        "sources": {
          "mp4": "/assets/videos/video-gallery/my-group/video.mp4"
        }
      }
    ]
  }
}
```

## ⚡ 自动扫描功能

页面会在首次访问时自动扫描视频目录（如果JSON文件为空）。

要强制重新扫描，使用CLI工具：
```powershell
php app/Console/ScanVideos.php --force
```

## 📝 添加新分组

1. 在 `public/assets/videos/video-gallery/` 创建新目录
2. 添加视频文件和预览图
3. 运行扫描工具：`php app/Console/ScanVideos.php --force`
4. 刷新页面查看效果

---

**提示：** 确保视频文件和预览图的文件名正确对应，这样预览图才能正确显示！
