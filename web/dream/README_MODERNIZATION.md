# Dream 画廊 - 现代化改造

## 概述
这个画廊已从基于 Flash 的 AutoViewer 升级为使用现代 Web 技术（HTML5、CSS3、JavaScript）实现。

## 改造内容

### 原有技术栈 (已弃用)
- **Flash SWF**: viewer.swf
- **SWFObject**: 用于嵌入 Flash 内容
- **XML 配置**: gallery.xml（保留使用）

### 新技术栈
- **HTML5**: 语义化结构，响应式设计
- **CSS3**: 现代样式、动画、渐变、毛玻璃效果
- **原生 JavaScript (ES6+)**: 面向对象编程，无需外部库

## 新功能特性

### 核心功能
✅ **自动播放幻灯片** - 默认3秒切换（可配置）
✅ **手动导航** - 前进/后退按钮
✅ **缩略图预览** - 点击快速跳转
✅ **图片计数器** - 显示当前位置
✅ **播放/暂停控制** - 自由控制幻灯片

### 交互增强
🎹 **键盘快捷键**:
- `←` / `→` - 上一张/下一张
- `空格` - 播放/暂停
- `Home` - 第一张
- `End` - 最后一张

📱 **触摸手势**:
- 左右滑动切换图片

### 视觉效果
- 渐变背景（紫色渐变）
- 毛玻璃效果（backdrop-filter）
- 平滑过渡动画
- 加载动画指示器
- 悬停效果增强

### 响应式设计
- 适配桌面、平板、手机
- 自适应图片尺寸
- 触摸友好的控制元素

## 文件结构

```
Dream/
├── index.html              # 主页面（现代版）
├── gallery.css             # 样式文件
├── gallery.js              # 功能脚本
├── gallery.xml             # 图片数据（保留）
├── readme.html             # AutoViewer 原始说明
├── index_flash_backup.html # Flash 版本备份
├── flash_backup/           # Flash 文件备份
│   ├── viewer.swf
│   └── swfobject.js
├── images/
│   └── large/              # 14张图片
└── music/                  # （如有）
```

## 浏览器兼容性

### 完全支持
- Chrome / Edge (Chromium) 90+
- Firefox 88+
- Safari 14+
- Opera 76+

### 功能降级
- 旧版浏览器不支持 backdrop-filter（毛玻璃效果）
- IE11 需替换 ES6 语法（不推荐）

## 使用说明

### 本地预览
1. 直接用浏览器打开 `index.html`
2. 或使用本地服务器（推荐）

### 修改配置
编辑 `gallery.xml` 来管理图片：
```xml
<gallery title="画廊标题" displayTime="3">
    <image>
        <url>images/large/photo.jpg</url>
        <caption>图片说明</caption>
        <width>800</width>
        <height>600</height>
    </image>
</gallery>
```

## 性能优化

- **懒加载**: 图片按需加载
- **预加载**: 下一张图片提前加载（可扩展）
- **CSS 动画**: 使用 GPU 加速
- **事件委托**: 优化事件监听器

## 未来改进

可能的增强方向：
- [ ] 添加全屏模式
- [ ] 图片缩放功能
- [ ] Service Worker（离线支持）
- [ ] 图片预加载策略
- [ ] 转换到 JSON 配置
- [ ] 添加更多过渡效果
- [ ] 分享功能

## 许可证

原 AutoViewer © 2008 Airtight Inc.
现代化改造 © 2026

---

**注意**: 旧的 Flash 文件已备份到 `flash_backup/` 目录，如需恢复可使用 `index_flash_backup.html`。
