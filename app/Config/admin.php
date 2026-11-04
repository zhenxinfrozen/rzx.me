<?php
/**
 * Admin 后台管理系统配置 - 驱动 SharedAdmin 通用套件
 */

return [
    'site_title'     => 'RZX.ME',
    'site_subtitle'  => '后台管理',
    'assets_url'     => '/shared-admin/assets',
    'home_url'       => '/',
    'logout_url'     => '/admin?page=logout',
    'current_user'   => [
        'name'  => 'RZX',
        'email' => 'admin@rzx.me',
        'role'  => 'Administrator',
    ],
    'menu' => [
        [
            'type'  => 'item',
            'key'   => 'dashboard',
            'title' => '控制台',
            'url'   => '/admin?page=dashboard',
            'icon'  => 'home',
        ],
        [
            'type'  => 'section',
            'title' => '内容管理',
        ],
        [
            'type'  => 'item',
            'key'   => 'sketchbook',
            'title' => 'Sketchbook管理',
            'url'   => '/admin?page=sketchbook',
            'icon'  => 'book-open',
        ],
        [
            'type'  => 'item',
            'key'   => 'comics',
            'title' => 'Comics管理',
            'url'   => '/admin?page=comics',
            'icon'  => 'layers',
        ],
        [
            'type'  => 'item',
            'key'   => 'videos',
            'title' => 'Videos管理',
            'url'   => '/admin?page=videos',
            'icon'  => 'film',
        ],
        [
            'type'  => 'item',
            'key'   => 'galleries',
            'title' => 'Galleries画廊管理',
            'url'   => '/admin?page=galleries',
            'icon'  => 'folder',
        ],
        [
            'type'  => 'item',
            'key'   => 'drafts',
            'title' => 'Drafts 草稿管理',
            'url'   => '/admin?page=drafts',
            'icon'  => 'edit-3',
        ],
        [
            'type'  => 'section',
            'title' => '工具',
        ],
        [
            'type'  => 'item',
            'key'   => 'tools',
            'title' => '管理工具',
            'url'   => '/admin?page=tools',
            'icon'  => 'tool',
        ],
        [
            'type'  => 'item',
            'key'   => 'thumbnail-center',
            'title' => '缩略图中心',
            'url'   => '/admin?page=thumbnail-center',
            'icon'  => 'grid',
        ],
        [
            'type'  => 'item',
            'key'   => 'thumbnail-manager',
            'title' => '整站缩略图管理',
            'url'   => '/admin?page=thumbnail-manager',
            'icon'  => 'image',
        ],
        [
            'type'  => 'item',
            'key'   => 'system-info',
            'title' => '系统信息',
            'url'   => '/admin?page=system-info',
            'icon'  => 'info',
        ],
        [
            'type'  => 'item',
            'key'   => 'docs',
            'title' => '项目文档',
            'url'   => '/admin?page=docs',
            'icon'  => 'book',
        ],
        [
            'type'  => 'item',
            'key'   => 'template-new',
            'title' => '📐 标准模板',
            'url'   => '/admin?page=template-new',
            'icon'  => 'file-text',
        ],
        [
            'type'  => 'section',
            'title' => '系统设置',
        ],
        [
            'type'  => 'item',
            'key'   => 'site-config',
            'title' => '网站配置',
            'url'   => '/admin?page=site-config',
            'icon'  => 'settings',
        ],
        [
            'type'  => 'item',
            'key'   => 'cache-manager',
            'title' => '缓存管理',
            'url'   => '/admin?page=cache-manager',
            'icon'  => 'database',
        ],
        [
            'type'  => 'item',
            'key'   => 'trash',
            'title' => '回收站',
            'url'   => '/admin?page=trash',
            'icon'  => 'trash-2',
        ],
    ]
];
