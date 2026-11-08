# shared-header

可挂载的站点顶部菜单栏组件（logo + 站点名 + 菜单栏），与 `shared-admin` 同一思路：
样式与结构由本组件统一维护，各项目自行 require 并传入配置即可接入。

## 结构

```
shared-header/
├── src/SiteHeader.php        # 组件类 \SharedHeader\SiteHeader
└── assets/css/header.css     # 样式 (通过 public/shared-header junction 暴露)
```

## 接入方式

```php
require_once <root>/shared-header/src/SiteHeader.php;

echo \SharedHeader\SiteHeader::render([
    'brand'   => 'RZX.ME',
    'tagline' => '颓废动画人',
    'nav'     => [
        'Home' => '/',
        'LAB'  => '/sites',
        // 下拉子菜单:
        'LAB 项目' => [
            'href'     => '/lab/',
            'children' => ['girl' => '/lab/girl/'],
        ],
    ],
]);
```

常用配置项：`brand` / `tagline` / `home_url` / `avatar` / `avatar_file` /
`nav` / `active_map` / `current_path` / `url_rewriter` / `assets_url` /
`assets_path` / `fixed`。详见 `SiteHeader::render()` 注释。

## 静态资源

主仓库通过 junction 暴露样式文件：

- `public/shared-header` → `<root>/shared-header`
- `.htaccess` 放行规则: `RewriteRule ^shared-header/ - [L]`

## 现有接入方

- 主站: `app/Views/layouts/header.php`（传 `url_rewriter => 'url'` 以支持非伪静态模式）
- RayLab: `lab/lab-plugin/raylab-header.php`（同一菜单 + `LAB 项目` 下拉子菜单）
