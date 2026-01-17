# Videos 页面系统使用说明

## 📋 概述

Videos 页面是一个模板化的视频展示系统，参考了 `comic.php` 和 `single-works.php` 的设计模式，支持通过后台管理或直接编辑JSON文件来管理视频分组和视频内容。

## 🎯 核心功能

### 1. 视频分组管理
- ✅ 支持多个视频分组（默认3个，可扩展）
- ✅ 每个分组有独立的标题、描述和视频列表
- ✅ 分组菜单使用拼接预览图（最多5个视频预览图）
- ✅ 纯CSS实现的overlay hover效果

### 2. 响应式视频网格
- ✅ 根据屏幕宽度自动调整列数：
  - 超大屏（≥1600px）：5列
  - 大屏（1200-1599px）：4列
  - 中屏（900-1199px）：3列
  - 小屏（600-899px）：2列
  - 移动（<600px）：2列（紧凑）

### 3. 交互体验
- ✅ 点击分组菜单切换视频内容
- ✅ 平滑滚动到视频显示区域
- ✅ 视频懒加载（preload="metadata"）
- ✅ 支持MP4和WebM双格式

## 📁 文件结构

```
rzx-me/
├── app/
│   ├── Models/
│   │   └── video_data.php              # 视频数据模型（CRUD操作）
│   ├── Views/
│   │   └── pages/
│   │       └── videos.php              # 视频页面模板
│   ├── Config/
│   │   ├── routes.php                  # 路由配置（已添加/videos路由）
│   │   └── page_config.php             # 页面配置（已添加videos配置）
│   └── storage/
│       └── data/
│           └── video_data.json         # 视频数据JSON文件
└── public/
    └── assets/
        ├── css/
        │   └── videos.css              # 视频页面样式
        └── movie/                      # 视频文件目录
            ├── *.mp4                   # MP4格式视频
            ├── *.webm                  # WebM格式视频（可选）
            └── *.jpg                   # 视频预览图（poster）
```

## 📝 数据结构

### video_data.json 格式

```json
{
  "group-id": {
    "title": "分组标题",
    "description": "分组描述",
    "status": "active",
    "order": 1,
    "videos": [
      {
        "title": "视频标题",
        "description": "视频描述（可选）",
        "poster": "/assets/movie/poster.jpg",
        "sources": {
          "mp4": "/assets/movie/video.mp4",
          "webm": "/assets/movie/video.webm"
        }
      }
    ]
  }
}
```

### 字段说明

#### 分组字段
- `title`: 分组标题（必填）
- `description`: 分组描述（可选）
- `status`: 状态，`active` 或 `inactive`（默认：active）
- `order`: 排序顺序，数字越小越靠前（默认：自动递增）
- `videos`: 该分组下的视频数组（必填）

#### 视频字段
- `title`: 视频标题（可选，显示在视频下方）
- `description`: 视频描述（可选）
- `poster`: 视频预览图路径（必填，显示在播放前）
- `sources`: 视频源文件对象
  - `mp4`: MP4格式视频路径（推荐）
  - `webm`: WebM格式视频路径（可选，用于更好的浏览器兼容性）

## 🔧 使用方法

### 方法一：直接编辑 JSON 文件

1. 编辑 `app/storage/data/video_data.json`
2. 添加或修改视频分组和视频数据
3. 保存文件
4. 刷新页面查看效果

**示例：添加新分组**

```json
{
  "my-new-group": {
    "title": "我的新分组",
    "description": "这是一个新的视频分组",
    "status": "active",
    "order": 4,
    "videos": [
      {
        "title": "测试视频",
        "description": "这是一个测试视频",
        "poster": "/assets/movie/test-poster.jpg",
        "sources": {
          "mp4": "/assets/movie/test-video.mp4"
        }
      }
    ]
  }
}
```

### 方法二：使用 PHP 函数（推荐用于后台管理）

#### 获取所有分组
```php
require_once 'app/Models/video_data.php';
$groups = get_all_video_groups();
```

#### 添加新分组
```php
$newGroup = [
    'title' => '新分组',
    'description' => '分组描述',
    'videos' => []
];
add_video_group('new-group-id', $newGroup);
```

#### 更新分组
```php
$updateData = [
    'title' => '更新后的标题',
    'description' => '更新后的描述'
];
update_video_group('group-id', $updateData);
```

#### 删除分组
```php
delete_video_group('group-id');
```

#### 添加视频到分组
```php
$newVideo = [
    'title' => '新视频',
    'poster' => '/assets/movie/poster.jpg',
    'sources' => [
        'mp4' => '/assets/movie/video.mp4'
    ]
];
add_video_to_group('group-id', $newVideo);
```

### 方法三：从目录扫描视频文件

如果你的视频文件已经按目录分组存放，可以使用扫描功能自动生成配置：

```php
require_once 'app/Models/video_data.php';

// 扫描 public/assets/movie 目录
$scannedGroups = scan_videos_from_directory(__DIR__ . '/public/assets/movie');

// 保存扫描结果
save_video_groups($scannedGroups);
```

**目录结构示例：**
```
public/assets/movie/
├── action-scenes/
│   ├── video1.mp4
│   ├── video1.jpg        # 预览图
│   ├── video2.mp4
│   └── video2.jpg
├── nature-effects/
│   ├── flower.mp4
│   ├── flower.jpg
│   └── ...
└── story-clips/
    └── ...
```

## 🎨 样式定制

### 修改分组菜单尺寸

编辑 `public/assets/css/videos.css`：

```css
#ray-video-showbox {
    --item-width: 250px;  /* 默认200px */
    --gap: 15px;          /* 默认10px */
}
```

### 修改视频网格列数

```css
.video-grid {
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    /* 调整 minmax 的第一个参数可以改变最小列宽 */
}
```

### 自定义overlay颜色

```css
#video-group-1:hover ~ #video-overlay {
    background-color: rgba(255, 0, 242, 0.3);  /* 粉红色 */
}
#video-group-2:hover ~ #video-overlay {
    background-color: rgba(0, 166, 255, 0.3);  /* 蓝色 */
}
/* 继续添加更多分组的颜色... */
```

## 📊 可用的辅助函数

### video_data.php 提供的函数

| 函数名 | 功能 | 参数 | 返回值 |
|--------|------|------|--------|
| `get_all_video_groups()` | 获取所有视频分组 | 无 | array |
| `get_video_group($groupId)` | 获取单个分组 | string | array/null |
| `add_video_group($groupId, $data)` | 添加分组 | string, array | bool |
| `update_video_group($groupId, $data)` | 更新分组 | string, array | bool |
| `delete_video_group($groupId)` | 删除分组 | string | bool |
| `add_video_to_group($groupId, $video)` | 添加视频到分组 | string, array | bool |
| `save_video_groups($groups)` | 保存所有分组 | array | bool |
| `scan_videos_from_directory($dir)` | 扫描目录 | string | array |
| `get_video_stats()` | 获取统计信息 | 无 | array |

## 🔍 调试技巧

### 查看当前视频数据
```php
require_once 'app/Models/video_data.php';
$groups = get_all_video_groups();
echo '<pre>' . print_r($groups, true) . '</pre>';
```

### 查看统计信息
```php
$stats = get_video_stats();
// 输出：['total_groups' => 3, 'active_groups' => 3, 'total_videos' => 6]
```

### 检查JSON文件是否有效
```bash
# 在命令行中运行
php -r "echo json_encode(json_decode(file_get_contents('app/storage/data/video_data.json')), JSON_PRETTY_PRINT);"
```

## ⚠️ 注意事项

1. **视频文件大小**：建议视频文件不超过50MB，以确保加载速度
2. **预览图必需**：每个视频都应该有对应的预览图（poster）
3. **文件路径**：所有路径都应该是相对于网站根目录的绝对路径（以`/`开头）
4. **浏览器兼容**：建议同时提供MP4和WebM格式以获得最佳兼容性
5. **分组数量**：虽然支持扩展，但建议分组数量不超过8个，以保持页面简洁
6. **JSON格式**：编辑JSON文件时注意保持格式正确，避免语法错误

## 🚀 未来扩展建议

1. **后台管理界面**：创建admin页面用于可视化管理视频
2. **视频上传功能**：集成文件上传功能，直接在后台上传视频
3. **缩略图自动生成**：使用FFmpeg自动从视频提取预览帧
4. **播放统计**：记录视频播放次数和时长
5. **标签系统**：为视频添加标签，支持按标签筛选
6. **搜索功能**：添加视频搜索和过滤功能

## 📞 技术支持

如有问题，请查看：
- 项目文档：`README.md`
- 路由配置：`app/Config/routes.php`
- 视频模型：`app/Models/video_data.php`
- 页面模板：`app/Views/pages/videos.php`

---

**版本：** v1.0.0  
**创建日期：** 2025年10月11日  
**作者：** GitHub Copilot
