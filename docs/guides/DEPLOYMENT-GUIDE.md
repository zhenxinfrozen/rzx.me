# RZX.ME 部署指南

## 概述
本项目支持多种Web服务器环境部署，提供了灵活的路由解决方案。

## 支持的Web服务器

### 1. Nginx (推荐生产环境)
- **配置文件**: `nginx.conf.example`
- **优势**: 高性能、更好的静态文件处理、SSL支持
- **适用场景**: 生产环境、高并发访问

#### 部署步骤:
```bash
# 1. 复制项目文件到Web目录
cp -r rzx-me/* /var/www/rzx.me/

# 2. 复制nginx配置
cp nginx.conf.example /etc/nginx/sites-available/rzx.me
ln -s /etc/nginx/sites-available/rzx.me /etc/nginx/sites-enabled/

# 3. 配置PHP-FPM
# 确保PHP-FPM运行，调整nginx.conf中的fastcgi_pass路径

# 4. 重启服务
nginx -t && systemctl restart nginx
systemctl restart php8.2-fpm
```

### 2. Apache (兼容环境)
- **配置文件**: `.htaccess` (已包含)
- **优势**: 配置简单、广泛支持
- **适用场景**: 共享主机、快速部署

#### 部署要求:
- 启用 `mod_rewrite` 模块
- 允许 `.htaccess` 文件覆盖配置
- PHP模块或CGI支持

### 3. PHP内置服务器 (开发环境)
- **启动脚本**: `start-dev-server.bat` / `start-dev-server.sh`
- **路由文件**: `public/dev-server.php`
- **适用场景**: 本地开发、快速测试

## 路由架构说明

### 主应用路由
- **配置文件**: `app/Config/routes.php`
- **处理引擎**: `app/Router.php`  
- **入口点**: `public/index.php`

### Admin后台路由 (独立子系统)
- **Apache**: `public/admin/.htaccess`
- **Nginx**: `nginx.conf.example` 中的 `/admin/` location块
- **PHP Fallback**: `public/admin/router.php` (兼容性)

## Admin后台访问

### URL模式:
- 后台首页: `/admin/`
- 控制器页面: `/admin/{controller-name}`
- 静态资源: `/admin/{assets|css|js|images}/...`

### 可用控制器:
- `comic-manager` - 漫画管理
- `gallery-manager` - 画廊管理  
- `sort-config` - 排序配置
- `thumbnail-manager` - 缩略图管理
- `site-config` - 站点配置
- `system-info` - 系统信息
- `tools` - 工具集合

## 环境配置检查

### PHP要求:
- PHP 7.4+ (推荐 8.1+)
- 扩展: `json`, `gd`, `fileinfo`
- 权限: Web服务器对 `storage/` 和 `public/assets/` 有写权限

### 文件权限:
```bash
# 设置正确的文件权限
chown -R www-data:www-data /var/www/rzx.me/
chmod -R 755 /var/www/rzx.me/
chmod -R 775 /var/www/rzx.me/storage/
chmod -R 775 /var/www/rzx.me/public/assets/
```

### 目录结构验证:
```
public/
├── admin/           # 后台系统
│   ├── .htaccess   # Apache路由配置
│   ├── router.php  # PHP fallback路由
│   ├── index.php   # 后台首页
│   └── controllers/ # 后台控制器
├── assets/         # 静态资源
├── .htaccess      # 主应用Apache配置
└── index.php      # 主应用入口
```

## 故障排除

### 1. Admin页面404错误
**症状**: 访问 `/admin/comic-manager` 返回404

**解决方案**:
- **Nginx**: 检查 `location /admin/` 配置是否正确
- **Apache**: 确认 `mod_rewrite` 已启用，`.htaccess` 生效
- **通用**: 访问 `/admin/router.php` 测试PHP fallback

### 2. 静态资源加载失败
**症状**: CSS/JS文件无法加载

**解决方案**:
- 检查文件权限: `chmod 644 public/admin/css/*`
- 验证路径: 确认资源文件存在于正确位置
- 浏览器调试: 查看Network面板的具体错误

### 3. 路由配置冲突
**症状**: 某些页面工作但其他页面不工作

**解决方案**:
- 查看Web服务器错误日志
- 确认nginx.conf和.htaccess配置一致
- 测试PHP路由fallback: `/admin/router.php`

## 性能优化建议

### 生产环境:
1. 使用Nginx + PHP-FPM
2. 启用OPcache
3. 配置静态资源缓存
4. 启用gzip压缩
5. 使用CDN服务

### 开发环境:
1. 启用错误报告
2. 使用PHP内置服务器快速测试
3. 定期清理临时文件

## 安全考虑

1. **隐藏敏感文件**: nginx.conf已配置拒绝访问 `.ht*`, `.git*`, `*.md` 文件
2. **Admin访问控制**: 建议添加IP白名单或HTTP认证
3. **文件上传安全**: 确保上传目录不可执行PHP
4. **SSL证书**: 生产环境使用HTTPS

## 更新日志

### v0.9.3 - Admin路由优化
- 添加nginx配置示例
- 创建PHP路由fallback
- 优化.htaccess配置
- 提供多环境部署方案