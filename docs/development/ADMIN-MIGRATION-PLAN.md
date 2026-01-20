# Admin 后台系统迁移方案 v1.2.0

## 🎯 迁移目标

将 `public/admin` 下的后台管理系统迁移到 `app` 目录，实现前后端分离：

```
迁移前:
public/admin/
├── controllers/    → app/Controllers/Admin/
├── views/         → app/Views/Admin/
├── index.php      → 需要重构为路由
├── router.php     → 整合到 app/Router.php
└── login.php      → 移到 app/Controllers/Admin/

迁移后:
app/Controllers/Admin/    # 后台控制器
app/Views/Admin/          # 后台视图
app/Config/routes.php     # 添加 admin 路由
public/index.php          # 统一入口
```

## ⚠️ 已知风险

### 编码问题
- **问题**: 上次迁移时使用 VSCode 移动文件导致中文乱码
- **原因**: Git/VSCode 可能自动转换文件编码（UTF-8 ↔ UTF-8 with BOM）
- **影响**: PHP 文件中的中文注释和字符串全部损坏

### 解决方案
使用 **PowerShell 命令** 而非 VSCode/Git 移动文件，保持原始编码

## 📝 迁移步骤 (安全版本)

### 准备阶段

#### Step 1: 创建备份
```powershell
# 1.1 创建完整备份
Copy-Item -Path "public\admin" -Destination "public\admin.backup" -Recurse

# 1.2 验证备份
Get-ChildItem -Path "public\admin.backup" -Recurse
```

#### Step 2: 创建目标目录
```powershell
# 2.1 创建 app 下的目标目录
New-Item -ItemType Directory -Force -Path "app\Controllers\Admin"
New-Item -ItemType Directory -Force -Path "app\Views\Admin"
```

#### Step 3: 验证文件编码
```powershell
# 3.1 检查所有 PHP 文件的编码
Get-ChildItem -Path "public\admin" -Recurse -Include "*.php" | ForEach-Object {
    $bytes = [System.IO.File]::ReadAllBytes($_.FullName)[0..2]
    $hasBOM = ($bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF)
    [PSCustomObject]@{
        File = $_.FullName
        HasBOM = $hasBOM
    }
} | Format-Table -AutoSize
```

### 迁移阶段 (使用安全的二进制复制)

#### Step 4: 移动 Controllers
```powershell
# 4.1 使用 Copy-Item 保持编码 (二进制复制)
Get-ChildItem -Path "public\admin\controllers" -Filter "*.php" | ForEach-Object {
    $dest = "app\Controllers\Admin\$($_.Name)"
    Copy-Item -Path $_.FullName -Destination $dest -Force
    Write-Host "✅ 已复制: $($_.Name)" -ForegroundColor Green
}

# 4.2 验证复制结果
Get-ChildItem -Path "app\Controllers\Admin" -Filter "*.php" | ForEach-Object {
    $content = Get-Content $_.FullName -Encoding UTF8 -Raw
    if ($content -match '[^\x00-\x7F]') {
        Write-Host "✅ $($_.Name) 包含中文字符正常" -ForegroundColor Green
    }
}
```

#### Step 5: 移动 Views
```powershell
# 5.1 复制 Views 目录结构
Copy-Item -Path "public\admin\views\*" -Destination "app\Views\Admin\" -Recurse -Force

# 5.2 验证
Get-ChildItem -Path "app\Views\Admin" -Recurse
```

#### Step 6: 处理入口文件
```powershell
# 6.1 复制入口文件到临时位置（需要重构）
Copy-Item -Path "public\admin\index.php" -Destination "app\Controllers\Admin\index.php.backup"
Copy-Item -Path "public\admin\router.php" -Destination "app\Controllers\Admin\router.php.backup"
Copy-Item -Path "public\admin\login.php" -Destination "app\Controllers\Admin\AuthController.php.template"
```

### 验证阶段

#### Step 7: 检查文件完整性
```powershell
# 7.1 对比文件数量
$original = (Get-ChildItem -Path "public\admin" -Recurse -File).Count
$migrated = (Get-ChildItem -Path "app\Controllers\Admin", "app\Views\Admin" -Recurse -File).Count
Write-Host "原始文件: $original | 已迁移: $migrated"

# 7.2 抽查几个文件的中文内容
$testFiles = @(
    "app\Controllers\Admin\cache-manager.php",
    "app\Controllers\Admin\comics.php"
)
foreach ($file in $testFiles) {
    $content = Get-Content $file -Encoding UTF8 -Raw
    Write-Host "`n文件: $file"
    Write-Host "前100字符: $($content.Substring(0, [Math]::Min(100, $content.Length)))"
}
```

#### Step 8: Git 提交测试
```powershell
# 8.1 查看 Git 状态
git status

# 8.2 查看一个文件的 diff (检查是否有编码问题)
git diff app/Controllers/Admin/cache-manager.php

# 8.3 如果 diff 正常，添加到暂存
git add app/Controllers/Admin/
git add app/Views/Admin/
```

### 重构阶段

#### Step 9: 更新路径引用
在所有迁移后的文件中，需要更新：
```php
// 旧路径
require_once '../views/layouts/header.php';
require_once '../../app/bootstrap.php';

// 新路径
require_once __DIR__ . '/../../Views/Admin/layouts/header.php';
require_once __DIR__ . '/../../bootstrap.php';
```

#### Step 10: 配置路由
在 `app/Config/routes.php` 添加：
```php
[
    'path' => '/admin',
    'type' => 'admin',
    'handler' => function() {
        // 加载 admin 入口逻辑
    }
],
```

## 🔙 回滚方案

如果迁移失败：
```powershell
# 删除迁移的文件
Remove-Item -Path "app\Controllers\Admin" -Recurse -Force
Remove-Item -Path "app\Views\Admin" -Recurse -Force

# 从备份恢复（如果需要）
Copy-Item -Path "public\admin.backup\*" -Destination "public\admin\" -Recurse -Force
```

或者使用 Git 回退：
```bash
git checkout .
git clean -fd
```

## ✅ 迁移检查清单

- [ ] 创建完整备份
- [ ] 验证原始文件编码（无 BOM）
- [ ] 创建目标目录
- [ ] 使用二进制复制移动 Controllers
- [ ] 验证 Controllers 中文内容正常
- [ ] 使用二进制复制移动 Views
- [ ] 验证 Views 中文内容正常
- [ ] 复制入口文件备份
- [ ] 检查文件完整性
- [ ] 测试 Git diff (确认无乱码)
- [ ] 更新所有路径引用
- [ ] 配置新路由
- [ ] 测试后台访问
- [ ] 提交到 Git

## 📌 关键原则

1. **永远不要直接移动原文件**，先复制后删除
2. **使用 PowerShell Copy-Item** 而非 Git mv 或 VSCode 移动
3. **每一步都要验证**中文内容是否正常
4. **出现问题立即回滚**，不要继续下去
5. **分步提交 Git**，便于定位问题

## 🔧 备用方案：渐进式迁移

如果一次性迁移风险太大，可以：
1. 先创建新结构，保留旧的
2. 逐个模块迁移和测试
3. 两个系统并行运行
4. 确认无误后删除旧的

## 📊 迁移时间估算

- 准备和备份: 5分钟
- 文件复制: 5分钟
- 验证测试: 10分钟
- 路径重构: 30分钟
- 路由配置: 20分钟
- 整体测试: 20分钟
- **总计: ~1.5小时**

## 🎓 经验教训

**上次失败的原因**：
- 使用了 VSCode agent 的自动文件移动
- 没有验证编码保持
- 没有分步骤验证
- 没有及时回滚

**本次改进**：
- 使用 PowerShell 原生命令
- 每步都验证编码
- 保留完整备份
- 随时可以回滚
