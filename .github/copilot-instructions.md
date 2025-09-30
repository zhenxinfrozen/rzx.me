## 快速上手 — rzx-me 代码库（给 AI 代理的实用要点）

以下说明基于代码可观察到的实现，目标是让自动化编码/修改任务可以安全、准确地完成。

### 1) 程序入口与“为什么”
- 单一前端入口：`public/index.php` 负责引导（require app/bootstrap.php、view_renderer、Router 等），所有 HTTP 请求通过此入口分发（静态/页面/API/admin）。
- 路由配置集中在 `app/Config/routes.php`（Router 类在 `app/Router.php`），Router 支持三类路由：pages、api、static；也支持正则（以 ~...~ 包裹）。

### 2) 配置与约定（务必用现有 API）
- 配置管理器：使用 `ConfigManager`（`app/Config/ConfigManager.php`），全局快捷函数 `config('app.timezone')` 可读取配置。不要直接包含配置文件，优先用 `config()`。
- 主要配置文件：`app/Config/app.php`（应用/路径/视图/安全/性能）。

### 3) 视图与页面数据
- 视图目录：`app/Views/`，模板渲染使用 `render_template($file, $vars)`（`app/view_renderer.php`）。
- 页面数据由 `app/Controllers/page_data_handler.php` 提供（`get_page_specific_data($viewFile)`），路由的 `handler` 字段常指向此类函数。

### 4) API 与后端逻辑
- API 通过 Router 标记为 `type: 'api'`，默认处理器在 `app/Controllers/api_comic_handler.php`，注意它设置了 CORS header 并返回 JSON。
- 业务逻辑以函数集合形式组织（非 class-heavy）：例如漫画数据相关函数在 `app/Models/comic_data.php`（读写 `storage/comic_data.json`）。若要改数据，优先调用这些函数（`add_comic`, `update_comic`, `delete_comic` 等）。

### 5) 静态资源与缩略图系统
- 缩略图服务核心：`app/Services/ThumbnailService.php` + `app/Utils/ImageProcessor.php`（依赖 PHP GD 扩展）。
- 自定义配置保存在 `app/Data/thumbnail_configs.json`，内置配置在 `ThumbnailService::$builtinConfigs`。生成缩略图的 CLI：`php app/Console/GenerateThumbnails.php`（支持 -g/--gallery 和 -f/--force）。

### 6) 管理后台与静态转发
- Admin 页面位于 `public/admin/`，`public/index.php` 会对 admin 路由做特殊转发（直接交给 admin 目录的 .htaccess / index）。修改 admin 页面时请在 `public/admin/views/` 内寻找对应模板。

### 7) 常用开发 / 调试命令（可直接运行）
- 在 Windows 开发机（仓库 root）：双击或运行 `start-dev-server.bat` 启动内置开发配置；Unix 下用 `start-dev-server.sh`。
- 直接触发缩略图：
  - 处理所有 gallery: `php app/Console/GenerateThumbnails.php`
  - 指定 gallery: `php app/Console/GenerateThumbnails.php -g single-works/Animals`
- 若使用 composer 自动加载（可选）：项目会在 `app/bootstrap.php` 检查 `../vendor/autoload.php`。若新增第三方包，请更新 composer 并确保 `bootstrap.php` 可加载。

### 8) 项目特定约定与注意点（切记）
- 单入口架构：避免直接创建新的独立入口文件；若要新增路由，优先修改 `app/Config/routes.php` 并添加 handler。
- 配置读写：使用 `ConfigManager` 的 `get/set`，不要随意 require 配置文件到其他命名空间。运行时可以用 `config()->set()`。
- 数据持久：漫画数据与缩略图配置以 JSON 存储在 `storage/` 或 `app/Data/`，直接编辑这些文件须谨慎；推荐通过现有函数（例如 `save_comics_data()`、`ThumbnailService::addCustomConfig()`）操作。
- 缩略图要求：服务器必须开启 GD 扩展（ImageProcessor::isGdAvailable()），否则生成会失败。
- 兼容与遗留：仓库保留大量历史/备份文件（`*-copy.php` 等），编辑时注意不要删除这些文件，除非确认不再需要（README 中也有说明）。

### 9) 代码修改示例（如何实现小改动）
- 读取配置：`$tz = config('app.timezone');`
- 渲染视图并传参：`echo render_template(__DIR__ . '/../app/Views/layouts/header.php', ['title' => $title]);`
- 在路由中新增 API：向 `app/Config/routes.php` 的 `api` 段添加 `/api/comic/new`，并在 `app/Controllers/` 增加对应 handler（返回 JSON，设置 header 'Content-Type: application/json'）。

### 10) 当心的常见误区
- 不要在 public 下直接硬编码后端逻辑（后端逻辑集中在 app/）。
- 不要绕过 Router 修改 URL 分发：Router 处理静态、admin、api 的细微差异（例如 allow_direct 标志、~regex~ 模式）。

如果你希望我把上面的说明再精简为 10 条快速规则或把某一部分展开为「如何添加新 API / 新缩略图配置」的具体补丁示例，我可以立刻生成并提交到 `.github/copilot-instructions.md` 的更新版。请告诉我你想优先细化哪部分。 
