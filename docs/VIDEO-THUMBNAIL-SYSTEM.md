# 视频缩略图系统 - 架构说明

## 🎯 设计原则

### 前台自动化（用户无感知）
- **职责**: 检测新增视频，生成缺失的缩略图
- **触发**: 用户访问视频页面时自动运行
- **策略**: 只处理没有缩略图的视频
- **原则**: 不管已有缩略图质量，不做全局刷新

### 后台手动工具（管理员控制）
- **职责**: 批量重新生成、质量优化、全局刷新
- **触发**: 管理员手动运行命令
- **策略**: 可以删除并重建所有缩略图
- **原则**: 完全控制，按需执行

---

## 🔄 前台自动化流程

### 触发条件
```
用户访问 /videos
    ↓
调用 get_all_video_groups()
    ↓
自动运行 smart_thumbnail_check()
```

### 检测逻辑
```php
1. 读取缓存文件（已处理的视频列表）
2. 扫描当前视频目录
3. 对比发现新增视频
4. 只为新增视频生成缩略图（如果缺失）
5. 更新缓存
```

### 关键代码
`app/Models/video_data.php`:
```php
function get_all_video_groups($autoCheckThumbnails = true) {
    if ($autoCheckThumbnails) {
        $stats = smart_thumbnail_check();
        if ($stats['generated'] > 0) {
            // 重新扫描并更新数据库
            $groups = scan_videos_from_directory(VIDEO_GALLERY_DIR, false);
            save_video_groups($groups);
        }
    }
    // ...
}
```

`app/Utils/AutoThumbnailGenerator.php`:
```php
function auto_generate_missing_thumbnails($videoGalleryDir) {
    foreach ($videos as $video) {
        // 关键：如果缩略图已存在，直接跳过
        if (file_exists($thumbnailPath)) {
            continue;  // 不检查质量，不重新生成
        }
        
        // 只为缺失的生成
        VideoThumbnailGenerator::generateOrDefault(...);
    }
}
```

---

## 🛠️ 后台手动工具

### 1. 强制重新生成所有缩略图
```bash
php regenerate-all-thumbnails.php
```

**功能**:
- 删除所有现有缩略图
- 为每个视频重新生成
- 使用最新的智能提取策略

**使用场景**:
- 更新了提取算法
- 发现大量缩略图质量问题
- FFmpeg版本升级后

### 2. 扫描并更新数据库
```bash
php app/Console/ScanVideos.php -f
```

**功能**:
- 扫描视频目录
- 更新video_data.json
- 同步新增/删除的视频

### 3. 批量重新生成（带扫描）
```bash
php regenerate-thumbnails.php
```

**功能**:
- 扫描所有视频
- 删除旧缩略图
- 重新生成
- 显示进度和统计

---

## 📁 文件结构

### 核心文件

#### 前台自动化
```
app/Utils/AutoThumbnailGenerator.php
  ├── auto_generate_missing_thumbnails()  # 生成缺失的
  ├── smart_thumbnail_check()             # 智能检测
  └── get_video_directory_state()         # 获取目录状态

app/Models/video_data.php
  └── get_all_video_groups()              # 集成自动检查

app/storage/cache/thumbnail_check.json    # 缓存文件
```

#### 后台工具
```
regenerate-all-thumbnails.php             # 强制全部重建
regenerate-thumbnails.php                 # 批量重建（旧）
app/Console/ScanVideos.php                # 扫描工具
```

#### 缩略图生成引擎
```
app/Utils/VideoThumbnailGenerator.php
  ├── generateSmartThumbnail()            # 智能提取
  ├── generateThumbnail()                 # 基础提取
  ├── generateDefaultThumbnail()          # GD默认图
  └── generateOrDefault()                 # 自动fallback
```

---

## 🎨 缩略图生成策略

### 智能提取位置
```php
视频时长 > 4秒   → 25% 位置
视频时长 2-4秒   → 2秒 位置
视频时长 < 2秒   → 50% 位置
```

### 特殊视频配置
`app/Config/video_thumbnail_overrides.php`:
```php
return [
    'Gunman' => [
        'position' => 10,
        'type' => 'seconds',
        'reason' => '视频整体较暗，10秒位置画面最清晰'
    ],
];
```

### Fallback机制
```
尝试FFmpeg生成
    ↓ 失败
使用GD生成默认图（灰色背景+播放图标）
```

---

## 📊 使用场景

### 场景1: 添加新视频（最常见）
```
1. 用户上传视频到 public/assets/videos/video-gallery/分组/
2. 用户刷新页面
3. 系统自动检测新视频
4. 自动生成缩略图
5. 自动更新数据库
```
**无需任何手动操作！**

### 场景2: 发现缩略图质量问题（管理员）
```bash
# 重新生成所有缩略图
php regenerate-all-thumbnails.php

# 更新数据库
php app/Console/ScanVideos.php -f
```

### 场景3: 更新提取策略后
```bash
# 修改 app/Utils/VideoThumbnailGenerator.php
# 然后重新生成所有缩略图
php regenerate-all-thumbnails.php
```

---

## ⚙️ 配置选项

### 禁用前台自动检查
`app/Models/video_data.php`:
```php
// 禁用自动检查
$groups = get_all_video_groups(false);
```

### 调整智能提取位置
`app/Utils/VideoThumbnailGenerator.php`:
```php
// 修改百分比
$timeInSeconds = (int)($duration * 0.30);  // 30%而不是25%
```

### 调整缩略图尺寸
```php
VideoThumbnailGenerator::generateSmartThumbnail(
    $videoPath,
    $outputPath,
    480  // 改为480宽度
);
```

---

## 🔍 故障排查

### 问题: 新视频没有自动生成缩略图

**检查步骤**:
```bash
# 1. 确认FFmpeg可用
ffmpeg -version

# 2. 查看日志（检查错误）
# Windows: 查看 PHP 错误日志
# 或在代码中添加 error_log() 输出

# 3. 手动测试
php test-auto-thumbnail.php

# 4. 清除缓存重试
Remove-Item app\storage\cache\thumbnail_check.json
```

### 问题: 缩略图质量不好

**解决方案**:
```bash
# 不要用前台自动修复，使用后台工具
php regenerate-all-thumbnails.php
```

### 问题: 某个特定视频总是黑色

**解决方案**:
1. 添加特殊配置到 `app/Config/video_thumbnail_overrides.php`
2. 手动测试多个位置找到最佳点
3. 重新生成该视频缩略图

---

## ✅ 最佳实践

### DO（推荐）
✅ 让前台自动处理新视频  
✅ 使用后台工具做全局优化  
✅ 为特殊视频添加自定义配置  
✅ 定期运行后台工具维护质量  

### DON'T（避免）
❌ 在前台检查已有缩略图质量  
❌ 前台自动重新生成所有缩略图  
❌ 混淆前台和后台的职责  
❌ 删除缓存文件作为常规操作  

---

## 📈 性能考虑

### 前台性能
- 使用缓存避免重复扫描
- 只处理新增视频
- 异步生成不阻塞页面（可选优化）

### 后台性能
- 批量处理可能耗时较长
- 建议在低峰期运行
- 可以添加进度显示

---

## 🎉 总结

| 功能 | 位置 | 触发 | 职责 |
|------|------|------|------|
| 自动检测新视频 | 前台 | 页面加载 | 只处理缺失的缩略图 |
| 全局重新生成 | 后台 | 手动命令 | 重建所有缩略图 |
| 质量优化 | 后台 | 手动命令 | 调整参数重建 |
| 数据库同步 | 后台 | 手动命令 | 扫描并更新 |

**前台专注用户体验，后台专注质量控制。**
