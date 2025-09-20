# ray-comic.php 现代化升级

迁移步骤（0-6）：

0. 规划与备份（必做）
- 在 git 上创建新分支（例如 v0.7.4-modernize-ray-comic）
- 备份当前文件（commit 当前状态）

1. 引入小型视图渲染器（低风险）
- 新增 includes/view_renderer.php，提供 render_template($file, $vars) 方法
- 目的：减少全局依赖，局部传入模板变量

2. 转换页面为“控制器 + 视图”模式（中等风险）
- 将 public/ray-comic.php 改为控制器，仅负责数据准备与调用渲染
- 将主体 HTML 抽取到 includes/views/ray-comic-body.php

3. 提取模板片段到 includes/views（低风险）
- 把页面片段拆分为独立视图（menu、comic_show 等），便于复用

4. 组件化（可选）
- 将复用 UI 抽取到 includes/components 下，使用 render_template 引入

5. 统一静态资源与 BASE_URL（中等）
- 在 includes/config.php 添加 BASE_URL 常量
- 统一资源引用为 <?= BASE_URL ?>/assets/...

6. 自动化与构建（长期）
- 引入 npm + 构建工具（vite/webpack/gulp），实现 CSS/JS 合并、压缩、指纹化

每步均需提交与回归测试，确保可回滚。
