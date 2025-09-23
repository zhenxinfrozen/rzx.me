# rzx-me v0.8.1 工程结构现代化重构计划

## 📊 项目工程结构分析报告

### 🎯 当前项目状态评估

**优点 ✅**：
- 基本的MVC架构已建立
- 前端控制器模式实现
- 视图模板系统规范化
- 静态资源组织良好

**需要改进的结构问题 ❌**：
- 目录命名不够标准化
- 配置文件分散
- 存在冗余和临时文件
- 视图文件命名规范需要统一

### 🔧 现代化改进建议

#### **第一阶段：目录结构标准化**

##### 1. 重命名和重组核心目录
```
当前结构 → 建议结构
app/Handlers/ → app/Controllers/          (更标准的MVC命名)
app/Data/ → app/Models/                   (符合MVC模式)
app/views/ → app/Views/                   (首字母大写标准)
```

##### 2. 配置文件集中化
```
建议新增：
app/Config/
├── app.php          (应用基础配置)
├── database.php     (数据库配置-未来用)
├── routes.php       (路由配置)
└── view.php         (视图配置)
```

##### 3. 清理冗余和临时文件
```
需要清理：
❌ app/legacy/                    (备份文件可移除)
❌ public/dev/                    (开发测试文件)
❌ public/generate-thumbs.php     (临时脚本)
❌ app/views/ray-comic-body copy.php  (重复文件)
❌ tools/ (空目录)
❌ .vscode/ (IDE配置不应提交)
```

#### **第二阶段：文件命名规范化**

##### 4. 视图文件重命名
```
当前命名 → 标准命名
ray-about-body.php → about.php
ray-animation-body.php → animation.php
ray-comic-body.php → comic.php
ray-comic-reader-body.php → gallery.php
ray-latest-body.php → latest.php
ray-pictures-body.php → pictures.php
ray-sites-body.php → sites.php
ray-sketch-body.php → sketch.php
index-body.php → home.php
```

##### 5. 控制器文件重命名
```
api_comic_handler.php → ComicController.php
page_data_handler.php → PageController.php
```

##### 6. 模型文件重命名
```
comic_data.php → Comic.php
```

#### **第三阶段：现代化架构优化**

##### 7. 建议的最终目录结构
```
rzx-me/
├── app/
│   ├── Config/              # 配置文件
│   │   ├── app.php
│   │   ├── routes.php
│   │   └── view.php
│   ├── Controllers/         # 控制器
│   │   ├── PageController.php
│   │   └── ComicController.php
│   ├── Models/              # 数据模型
│   │   └── Comic.php
│   ├── Views/               # 视图模板
│   │   ├── layouts/         # 布局模板
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── pages/           # 页面模板
│   │   │   ├── home.php
│   │   │   ├── about.php
│   │   │   ├── comic.php
│   │   │   ├── gallery.php
│   │   │   └── ...
│   │   └── partials/        # 组件模板
│   ├── Core/                # 核心系统文件
│   │   ├── Bootstrap.php
│   │   ├── Router.php
│   │   ├── View.php
│   │   └── helpers.php
│   └── Services/            # 服务类(未来扩展)
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   ├── media/           # 重命名 movie + music
│   │   └── fonts/           # 新增字体目录
│   └── index.php
├── storage/                 # 新增存储目录
│   ├── cache/
│   ├── logs/
│   └── uploads/
├── tests/                   # 新增测试目录
├── .env                     # 环境配置
├── .gitignore
├── README.md
└── composer.json            # 依赖管理
```

## 🚀 分步实施计划

### **阶段一：基础结构调整**

#### 步骤1：目录重命名 (执行中)
- [x] 分析当前结构
- [ ] app/Handlers/ → app/Controllers/
- [ ] app/Data/ → app/Models/
- [ ] app/views/ → app/Views/

#### 步骤2：配置文件集中化 (计划中)
- [ ] 创建 app/Config/ 目录
- [ ] 提取配置到独立文件
- [ ] 重构 bootstrap.php

#### 步骤3：清理冗余文件 (计划中)
- [ ] 删除 app/legacy/
- [ ] 删除 public/dev/
- [ ] 删除临时文件
- [ ] 清理空目录

### **阶段二：文件标准化**

#### 步骤4：视图文件重命名 (计划中)
- [ ] 创建新的视图目录结构
- [ ] 重命名所有视图文件
- [ ] 更新路由引用

#### 步骤5：控制器重构 (计划中)
- [ ] 重命名控制器文件
- [ ] 标准化类命名
- [ ] 更新命名空间

#### 步骤6：模型重构 (计划中)
- [ ] 重命名模型文件
- [ ] 优化数据处理结构

### **阶段三：架构优化**

#### 步骤7：核心文件重组 (计划中)
- [ ] 创建 app/Core/ 目录
- [ ] 重构核心系统文件
- [ ] 优化自动加载

#### 步骤8：现代化增强 (计划中)
- [ ] 添加 storage/ 目录
- [ ] 创建 .env 配置
- [ ] 添加 composer.json

## 📋 执行检查清单

### 当前进度
- [x] 项目结构分析完成
- [x] 重构计划制定完成
- [ ] 开始执行阶段一

### 风险控制
- ✅ 所有操作前进行git提交
- ✅ 分步骤小幅度修改
- ✅ 每个步骤后测试功能完整性
- ✅ 保持向后兼容性

## 🎯 预期成果

**代码质量提升**：
- 标准化的MVC架构
- 清晰的目录组织
- 统一的命名规范
- 现代化的项目结构

**维护性增强**：
- 配置集中管理
- 模块化的文件组织
- 更好的可扩展性
- 开发效率提升

---

*该文档将在重构过程中持续更新，记录每个步骤的执行状态和结果。*