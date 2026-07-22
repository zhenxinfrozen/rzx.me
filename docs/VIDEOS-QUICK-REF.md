# Videos 页面快速参考

## 🎯 核心特性

### 视觉效果
- **默认：** 黑白缩略图 + 底部标题
- **Hover：** 彩色缩略图 + 移动蒙版 + 阴影增强

### 技术实现
- **黑白滤镜：** `filter: grayscale(100%)`
- **模糊蒙版：** `backdrop-filter: blur(2px)`
- **移动动画：** `transform: translate3d(...)`
- **兄弟选择器：** `.item:hover ~ #overlay`

## 📊 当前数据

**总计：** 11个视频，4个分组

```
animation-clips: 4个视频
comic-anim:      5个视频
storyboard:      1个视频
test-ex:         1个视频
```

## 🔧 常用命令

### 扫描新视频
```powershell
php app/Console/ScanVideos.php --force
```

### 查看现有数据
```powershell
php app/Console/ScanVideos.php
```

## 🎨 蒙版颜色

| ID | 颜色 |
|----|------|
| #1 | 粉红 rgba(255, 0, 242, 0.3) |
| #2 | 蓝色 rgba(0, 166, 255, 0.3) |
| #3 | 绿色 rgba(0, 255, 0, 0.3) |
| #4 | 橙色 rgba(255, 153, 0, 0.3) |
| #5 | 粉紫 rgba(255, 0, 100, 0.3) |
| #6 | 黄绿 rgba(100, 255, 0, 0.3) |

## 📁 文件位置

- **页面：** `app/Views/pages/videos.php`
- **样式：** `public/assets/css/videos.css`
- **数据：** `app/storage/data/video_data.json`
- **视频：** `public/assets/videos/video-gallery/`
- **扫描工具：** `app/Console/ScanVideos.php`

## 🔄 添加视频步骤

1. 放置文件
```
public/assets/videos/video-gallery/
└── your-group/
    ├── video.mp4
    └── video.jpg
```

2. 扫描更新
```powershell
php app/Console/ScanVideos.php --force
```

3. 刷新页面 ✅

## 💡 自定义提示

### 修改分组标题
编辑 `video_data.json`：
```json
"comic-anim": {
  "title": "漫画动画"  // 自定义中文标题
}
```

### 添加新蒙版颜色
在 `videos.css` 末尾添加：
```css
#video-group-7:hover ~ #video-overlay {
    background-color: rgba(R, G, B, 0.3);
    transform: translate3d(calc(6 * (var(--item-width) + var(--gap))), 0, 0);
}
```

---

**访问页面：** http://localhost/videos  
**文档：** VIDEOS-FINAL-OPTIMIZATION.md
