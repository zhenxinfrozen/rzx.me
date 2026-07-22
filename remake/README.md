# Rzx.me (2008-2014 Archive Edition)

> 一个关于 2008-2014 年时期个人网站的数字化存档与现代化重构项目。

## 📖 项目背景

这是我个人网站（Rzx.me）的早期版本存档，主要涵盖了 2008 年至 2014 年期间的内容与设计风格。
那个时代的代码大多是“手搓”的原生 PHP 和早期的 jQuery 交互。随着时间的推移，旧版代码在现代 PHP 环境（PHP 8+）下出现了大量兼容性问题，且原有的架构难以维护。

**本项目的目标是：**
在**表面上完整保留**当年的视觉创意、动画效果和交互逻辑（作为怀旧存档），但在**底层逻辑上**进行彻底的现代化重构，使其能够稳定运行在现代服务器环境中。

## 🛠️ 现代化改造 (Modernization)

为了适应现代 Web 标准，本项目在保留前端“原汁原味”的同时，对后端进行了全面的重写：

*   **架构升级**：从杂乱的单文件 PHP 脚本重构为基于 **单一入口 (Single Entry Point)** 和 **自定义路由 (Custom Router)** 的 MVC-lite 架构。
*   **PHP 8 兼容**：修复了所有过时的语法，全面支持 PHP 8.0+。
*   **路由系统**：
    *   实现了灵活的路由分发机制。
    *   支持 **伪静态 (Pretty URL)** 和 **查询参数 (Query Param)** 双模式，方便在不支持 URL Rewrite 的环境（如部分宝塔面板配置）中部署。
*   **媒体管理**：
    *   重构了 **Sketchbook** 和 **Single Works** 画廊系统。
    *   重写了视频画廊 (Video Gallery) 的数据加载逻辑。
    *   引入了基于 PHP GD 的**智能缩略图生成服务**，自动处理旧资源的预览图。
    *   **Git 索引优化 (v1.5.0)**：彻底清理了历史遗留的已追踪媒体文件索引。现代图片与视频资产通过 `.gitignore` 完全脱离 Git 追踪，实现“代码与资产物理分离”，极大提升了版本控制的响应速度和 VPS 部署的稳定性。
*   **工程化**：引入了统一的配置管理 (`ConfigManager`) 和视图渲染器，从混乱的全局变量中解脱出来。

## ✨ 主要特性

*   **怀旧 UI**: 完美的 2010 年代 Web 设计风格，保留了经典的导航动画和页面布局。
*   **双模路由**: 通过配置文件轻松切换 `example.com/about` 或 `example.com/?route=/about`。
*   **画廊系统**: 支持按文件夹自动扫描、分页和排序的图片展示系统。
*   **轻量级**: 不依赖笨重的现代框架（如 Laravel/Symfony），保持了当年网站“打开即看”的轻快感。

## 🚀 快速开始

### 环境要求
*   PHP 8.0 或更高版本
*   Web 服务器 (Nginx/Apache)
*   PHP GD 扩展 (用于缩略图生成)

### 安装步骤

1.  **克隆代码**:
    ```bash
    git clone https://github.com/zhenxinfrozen/rzx-me.git
    cd rzx-me
    ```

2.  **配置环境**:
    复制或修改 `app/Config/app.php`。
    如果你的服务器配置了 URL Rewrite (伪静态)，请开启：
    ```php
    'use_pretty_urls' => true,
    ```
    如果在宝塔等不支持 Rewrite 的简单环境，请关闭（默认）：
    ```php
    'use_pretty_urls' => false,
    ```

3.  **恢复资源 (重要)**:
    由于体积原因，原始的媒体资源（图片、视频、Sketchbook原稿）**并未包含**在 Git 仓库中。（它们在 `.gitignore` 中被排除）。
    请将备份的媒体资源放入以下目录：
    *   `public/assets/images/`
    *   `public/assets/videos/`
    *   `public/assets/videos/`

4.  **运行**:
    将 Web 服务器根目录指向 `public/` 文件夹。

## 📂 目录结构

```
rzx-me/
├── app/                  # 核心应用代码
│   ├── Config/           # 配置文件 (路由, 应用配置, 画廊排序)
│   ├── Controllers/      # 业务逻辑控制器
│   ├── Services/         # 核心服务 (如缩略图生成)
│   ├── Utils/            # 工具类
│   ├── Views/            # 页面模板 (PHP混编)
│   └── Router.php        # 路由分发器
├── public/               # Web 入口
│   ├── assets/           # 静态资源 (CSS, JS, 媒体文件)
│   ├── admin/            # 旧版后台入口 (部分保留)
│   └── index.php         # 统一入口文件
└── docs/                 # 开发与维护文档
```

## 📝 许可证

Private / Personal Archive.
仅供学习与存档使用。
