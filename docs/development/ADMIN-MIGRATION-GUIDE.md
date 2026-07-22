# Admin 迁移实战指南

## 🎯 快速开始

### 一键执行
双击运行项目根目录的 `migrate-admin.bat`，按提示选择操作。

### 推荐流程

```bash
# 1. 先验证当前文件编码
migrate-admin.bat → 选择 1

# 2. 模拟运行看看效果
migrate-admin.bat → 选择 2

# 3. 确认无误后执行实际迁移
migrate-admin.bat → 选择 3 → 输入 yes 确认

# 4. 如果出问题立即回滚
migrate-admin.bat → 选择 4 → 输入 yes 确认
```

## 📋 详细步骤

### Phase 1: 准备和验证 (5分钟)

#### 1.1 验证当前编码
```powershell
# 检查所有 PHP 文件的编码状态
.\tools\migrate-admin-safe.ps1 -Verify
```

**预期输出**：
```
✅ 所有文件都是 UTF-8 无BOM 编码
  ✓ cache-manager.php - UTF-8 无BOM, 包含中文
  ✓ comics.php - UTF-8 无BOM, 包含中文
  ...
```

#### 1.2 提交当前状态
```bash
# 确保工作区干净
git status

# 如果有未提交的改动，先提交
git add .
git commit -m "chore: prepare for admin migration"
```

### Phase 2: 模拟运行 (2分钟)

#### 2.1 执行模拟
```powershell
.\tools\migrate-admin-safe.ps1 -DryRun
```

**预期输出**：
```
ℹ️  步骤 1/7: 检查前置条件...
✅ 前置条件检查通过
ℹ️  步骤 2/7: 创建备份...
[DRY-RUN] 将创建备份: D:\VS CODE\rzx-me\public\admin.backup.20260120-143052
...
⚠️  这是一次模拟运行，没有实际复制文件
```

#### 2.2 检查计划
查看输出，确认：
- ✅ 文件数量正确
- ✅ 目录结构合理
- ✅ 备份路径正确

### Phase 3: 执行迁移 (5分钟)

#### 3.1 执行实际迁移
```powershell
.\tools\migrate-admin-safe.ps1
```

**监控输出**：
```
✅ 已复制 14 个 Controller 文件
  ✓ cache-manager.php - 中文正常
  ✓ comics.php - 中文正常
  ...
✅ 已复制 12 个 View 文件
✅ 文件完整性验证通过
  ✓ cache-manager.php - 中文验证通过
    预览: <?php /** * 缓存管理器 * 管理网站的各种缓存...
```

#### 3.2 立即验证
```powershell
# 检查迁移后的文件
Get-ChildItem -Path "app\Controllers\Admin" -Recurse

# 随机抽查一个文件的内容
Get-Content "app\Controllers\Admin\cache-manager.php" -Encoding UTF8 | Select-Object -First 20
```

**关键检查**：看到中文注释 "缓存管理器" 应该**完全正常**，不能有乱码！

### Phase 4: 验证和测试 (10分钟)

#### 4.1 Git 状态检查
```bash
# 查看 Git 识别的变化
git status

# 查看一个文件的 diff
git diff app/Controllers/Admin/cache-manager.php
```

**重要**：Git diff 应该显示**新增文件**，不应该有编码转换标记。

#### 4.2 内容抽查
```powershell
# 检查几个关键文件
$files = @(
    "app\Controllers\Admin\cache-manager.php",
    "app\Controllers\Admin\comics.php",
    "app\Views\Admin\layouts\header.php"
)

foreach ($file in $files) {
    Write-Host "`n=== $file ===" -ForegroundColor Cyan
    $content = Get-Content $file -Encoding UTF8 -Raw
    $preview = $content.Substring(0, [Math]::Min(200, $content.Length))
    Write-Host $preview
    
    if ($content -notmatch '[\u4e00-\u9fa5]') {
        Write-Host "⚠️  警告: 未检测到中文字符！" -ForegroundColor Yellow
    } else {
        Write-Host "✅ 中文字符正常" -ForegroundColor Green
    }
}
```

#### 4.3 暂存文件
如果所有检查都通过：
```bash
git add app/Controllers/Admin/
git add app/Views/Admin/
git status
```

### Phase 5: 清理和提交 (5分钟)

#### 5.1 提交迁移
```bash
git commit -m "feat(v1.2.0): migrate admin system to app directory

- Move controllers from public/admin/controllers to app/Controllers/Admin
- Move views from public/admin/views to app/Views/Admin
- Preserve file encoding (UTF-8 without BOM)
- Create backup at public/admin.backup.*

Related: #v1.2.0-admin-refactor"
```

#### 5.2 保留备份（暂时）
```powershell
# 不要立即删除备份，保留一段时间
# public\admin.backup.* 会在确认无误后手动删除
```

## 🔙 失败回滚

### 如果发现乱码或问题

#### 方法1: 使用脚本回滚
```powershell
.\tools\migrate-admin-safe.ps1 -Rollback
```

#### 方法2: Git 回滚
```bash
# 撤销所有改动
git checkout .
git clean -fd

# 或者回退到上一个提交
git reset --hard HEAD~1
```

#### 方法3: 手动清理
```powershell
# 删除迁移的文件
Remove-Item -Path "app\Controllers\Admin" -Recurse -Force
Remove-Item -Path "app\Views\Admin" -Recurse -Force
```

## ⚠️ 常见问题

### Q1: 运行脚本提示权限错误
**A**: 以管理员身份运行 PowerShell，或执行：
```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```

### Q2: Git diff 显示大量改动
**A**: 可能是换行符或编码问题，检查：
```bash
git config core.autocrlf
git diff --word-diff app/Controllers/Admin/cache-manager.php
```

### Q3: 看到中文乱码
**A**: 立即停止！执行回滚：
```powershell
.\tools\migrate-admin-safe.ps1 -Rollback
```

### Q4: 脚本执行很慢
**A**: 正常现象，因为要逐个验证文件。总共约 5-10 分钟。

## ✅ 成功标志

迁移成功的标志：
- ✅ `app/Controllers/Admin/` 有 14 个 PHP 文件
- ✅ `app/Views/Admin/` 有完整的视图文件
- ✅ 随机抽查文件中文正常显示
- ✅ Git diff 显示合理的新增内容
- ✅ 没有编码错误或乱码

## 📊 迁移后的目录结构

```
rzx-me/
├── app/
│   ├── Controllers/
│   │   └── Admin/                    ← 新增
│   │       ├── cache-manager.php
│   │       ├── comics.php
│   │       ├── gallery-manager.php
│   │       ├── single-works.php
│   │       ├── sketchbook.php
│   │       ├── system-info.php
│   │       ├── thumbnail-center.php
│   │       ├── tools.php
│   │       ├── trash.php
│   │       ├── video-gallery.php
│   │       ├── site-config.php
│   │       ├── docs-handler.php
│   │       ├── single-works-data.php
│   │       └── sketchbook-data.php
│   └── Views/
│       └── Admin/                    ← 新增
│           ├── layouts/
│           │   ├── header.php
│           │   └── footer.php
│           └── pages/
│               ├── body-dashboard.php
│               ├── body-comics.php
│               ├── body-docs.php
│               ├── body-single-works.php
│               ├── body-sketchbook.php
│               └── body-video-gallery.php
└── public/
    └── admin/                         ← 保留（需重构）
        ├── index.php
        ├── router.php
        └── login.php
```

## 🎯 下一步

迁移成功后，还需要：
1. ✅ 更新所有文件中的路径引用
2. ✅ 重构 `public/admin/index.php` 使用新路径
3. ✅ 配置路由系统支持 admin
4. ✅ 测试后台所有功能
5. ✅ 更新文档

详见：`docs/development/ADMIN-REFACTOR-ROADMAP.md`
