# rzxme-skeuo-menu —— rzx.me 拟物菜单组件（老站动效复刻版）

WordPress 自定义动态区块 `rzxme/skeuo-menu`，把 old.rzx.me 首页经典拟物化菜单（7 项）复刻迁移到 rzx.me 生产站首页。

## 服务器部署位置（rzx.me / dev.rzx.me 22333）

- 区块定义：`/www/wwwroot/rzx.me/wp-content/themes/rzxme-2025/blocks/skeuo-menu/block.json`
- 渲染函数：`/www/wwwroot/rzx.me/wp-content/themes/rzxme-2025/blocks/skeuo-menu/rzx-skeuo-menu-block.php`
- 样式：`/www/wwwroot/rzx.me/wp-content/themes/rzxme-2025/assets/skeuo-menu/rzx-skeuo-menu.css`
- 脚本：`/www/wwwroot/rzx.me/wp-content/themes/rzxme-2025/assets/skeuo-menu/rzx-skeuo-menu.js`
- functions.php 末尾 `require_once` 注册（备份：`functions.php.bak-skeuo-20261023`）
- 首页 post 6574 内容已插入 `<!-- wp:rzxme/skeuo-menu /-->`（位于静态菜单 group 锚点 `ray-home-menu-anim-alpha` 之后）
- 图片：`/wp-content/uploads/2026/10/ray-home-menu-sketchbook-{01,02}.png`

## 菜单项与动效（2026-10-23 最终态）

| 菜单项 | 动效 |
|---|---|
| Animation | hover 两层波浪上涌淹没图标；点击按下 scale(0.94) |
| Latest | hover 红点扩散淹没 + 图标上浮放大；点击按下 scale(0.95) |
| Comic | hover 与 click 一致：随机上浮 30–50px + 随机旋转 6–14°（方向 ±），600ms 弹簧回弹 |
| Blog | 默认倾斜 20°，hover 弹性回摆（-60° 回摆→震荡停在 12 点，750ms）；点击随机方向摆到 ±50° 纯旋转回弹 12 点（500ms，无缩放） |
| SketchBooks | 3D 翻书：静止封面 01（z2），hover 封面绕左缘 rotateY(-130°) 翻开露出内页 02 |
| Works | hover 水平动感模糊渐隐；点击随机强度 6–14 的水平模糊脉冲 340ms 衰减 |
| About | hover 中心放大 0.8→1.3 + 圆角 6→14px；点击按下 scale(0.94)（同 Animation/Latest） |

动效基调：克制含蓄。全部选择器以 `.rzx-skeuo` 前缀隔离，不影响 WP 全局样式。

## 设计与实现说明

- 不用古腾堡区块堆叠还原（哈希类名易变、figure/p 层级挂不了 ::before/::after），改为主题自定义动态区块，可拖入古腾堡编辑器。
- 点击 href="#" 不导航，仅保留交互动效。
- 动画随机值由 JS 写入 CSS 变量（`--rzx-comic-rise` / `--rzx-comic-rot` / `--rzx-shock-rot`）。
- Comic / Blog 均带 DOM-ready 守卫（脚本可能先于容器渲染，勿回退）。

## 已知注意事项

- WPO 页面缓存会挡住更新：改动后需删除 `/wp-content/cache/wpo-cache` 与 `/wp-content/wpo-cache`（插件 purge API 与 wp-cli 'wpo' 均不可用）。
- WPO WebP 转换已关闭（2026-10-23），页面直接引用 png。
- Sketch 封面 01 当前为原始单图（7811 字节）；若替换 png，注意附件 11849 映射无需改（同名覆盖即可）。
