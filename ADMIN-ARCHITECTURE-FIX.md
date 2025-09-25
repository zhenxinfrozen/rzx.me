# RZX.ME 后台管理系统 - 架构调整说明

## 🔧 架构调整

### 问题原因
原始设计将 `admin/` 目录放在项目根目录，但PHP开发服务器只能访问 `public/` 目录下的文件，导致404错误。

### 解决方案
将 `admin/` 目录移动到 `public/admin/`，并调整所有相对路径引用。

## 📁 新的目录结构

```
public/
├── admin/                      # 后台管理系统
│   ├── index.php              # 主控制台
│   ├── login.php              # 登录页面
│   ├── controllers/           # 控制器
│   │   ├── sort-config.php    # 分类管理
│   │   └── trash.php          # 回收站
│   ├── views/                 # 视图模板
│   └── assets/                # 静态资源
│       ├── css/admin.css      # 样式文件
│       └── js/admin.js        # 脚本文件
├── assets/                    # 网站资源
├── index.php                  # 网站首页
└── admin.php                  # 后台重定向入口
```

## 🔄 路径调整对照表

| 功能 | 原路径 | 新路径 |
|------|--------|--------|
| app/bootstrap.php | `../app/bootstrap.php` | `../../app/bootstrap.php` |
| 配置文件 | `../../app/Config/` | `../../../app/Config/` |
| GalleryManager | `../../app/Utils/` | `../../../app/Utils/` |
| 图片资源 | `../../public/assets/` | `../assets/` |
| 垃圾桶目录 | `../../public/assets/images/trash` | `../assets/images/trash` |

## 🚀 访问方式

1. **主控制台**: `http://localhost:8080/admin/`
2. **登录页面**: `http://localhost:8080/admin/login.php`
3. **分类管理**: `http://localhost:8080/admin/controllers/sort-config.php`
4. **回收站**: `http://localhost:8080/admin/controllers/trash.php`
5. **快捷入口**: `http://localhost:8080/admin.php` (重定向)

## 💻 开发模式

使用 `?dev` 参数跳过登录认证：
- `http://localhost:8080/admin/?dev`

## ✅ 功能验证

- [x] 主控制台可正常访问
- [x] 登录系统工作正常
- [x] 分类管理功能完整
- [x] 回收站操作正常
- [x] 静态资源加载正确
- [x] 路径引用全部修复

## 🔐 默认登录信息

- **用户名**: `admin`
- **密码**: `rzx2024`

---

**注意**: 所有相对路径已经调整完成，后台管理系统现在可以正常使用。