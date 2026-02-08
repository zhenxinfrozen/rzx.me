# 中文编码终极解决方案 - 一劳永逸完全指南

> **最后更新**: 2026-01-21  
> **适用于**: Windows 10/11 + VSCode + PowerShell + Git  
> **目标**: 彻底解决中文乱码问题，包括 AI Agent 操作

---

## 📌 问题根源

### 为什么会出现中文乱码？

1. **Windows 系统默认编码是 GB2312/GBK (代码页 936)**
2. **PowerShell 默认使用系统编码，不是 UTF-8**
3. **VSCode 终端继承 PowerShell 的编码设置**
4. **AI Agent 使用 PowerShell 命令时，如果不显式指定编码会使用默认的 GB2312**

### 乱码发生场景

- ✗ AI Agent 使用 `Get-Content`/`Set-Content` 时
- ✗ 创建新文件时没有指定 UTF-8
- ✗ PowerShell 输出中文到文件
- ✗ Git 提交信息包含中文
- ✗ 文件路径包含中文

---

## ✅ 解决方案架构

### 三层防护体系

```
┌─────────────────────────────────────────┐
│  Layer 1: Windows 系统级配置 (可选)      │
│  - 系统默认 UTF-8                        │
│  - Windows Terminal UTF-8               │
└─────────────────────────────────────────┘
            ↓
┌─────────────────────────────────────────┐
│  Layer 2: PowerShell 全局配置 (必需)    │
│  - PowerShell Profile 自动设置 UTF-8    │
│  - 终端编码强制 UTF-8                   │
└─────────────────────────────────────────┘
            ↓
┌─────────────────────────────────────────┐
│  Layer 3: VSCode 项目配置 (必需)        │
│  - 文件编码 UTF-8                       │
│  - 终端启动参数强制 UTF-8               │
│  - EditorConfig 跨编辑器统一            │
└─────────────────────────────────────────┘
```

---

## 🔧 配置步骤

### Step 1: PowerShell 全局配置 ⭐ 最重要

这是**核心配置**，确保所有 PowerShell 会话都使用 UTF-8。

#### 1.1 创建 PowerShell 配置文件

```powershell
# 检查配置文件路径
$PROFILE.CurrentUserAllHosts
```

输出类似：`D:\OneDrive\文档\WindowsPowerShell\profile.ps1`

#### 1.2 添加 UTF-8 配置

```powershell
# 方法 A: 自动添加配置（推荐）
$utf8Config = @'
# ========================================
# UTF-8 编码配置 - 解决中文乱码
# ========================================
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::InputEncoding = [System.Text.Encoding]::UTF8

# 设置默认参数
$PSDefaultParameterValues['Out-File:Encoding'] = 'utf8'
$PSDefaultParameterValues['*:Encoding'] = 'utf8'

# 设置代码页为 UTF-8
chcp 65001 | Out-Null

Write-Host "✓ PowerShell UTF-8 编码已启用" -ForegroundColor Green
'@

# 如果文件不存在，创建它
if (!(Test-Path $PROFILE.CurrentUserAllHosts)) {
    New-Item -Path $PROFILE.CurrentUserAllHosts -ItemType File -Force
}

# 添加配置
Add-Content -Path $PROFILE.CurrentUserAllHosts -Value $utf8Config -Encoding UTF8
```

#### 1.3 验证配置

关闭并重新打开 PowerShell，应该看到：
```
✓ PowerShell UTF-8 编码已启用
```

检查编码：
```powershell
[Console]::OutputEncoding.CodePage  # 应该显示: 65001
chcp  # 应该显示: 活动代码页: 65001
```

---

### Step 2: VSCode 项目配置 ⭐ 必需

#### 2.1 项目配置文件 `.vscode/settings.json`

已自动配置，包含：

```json
{
  "files.encoding": "utf8",
  "files.autoGuessEncoding": true,
  "files.eol": "\n",
  
  "terminal.integrated.defaultProfile.windows": "PowerShell",
  "terminal.integrated.profiles.windows": {
    "PowerShell": {
      "source": "PowerShell",
      "args": [
        "-NoLogo",
        "-NoExit",
        "-ExecutionPolicy", "Bypass",
        "-Command",
        "[Console]::OutputEncoding=[System.Text.Encoding]::UTF8; [Console]::InputEncoding=[System.Text.Encoding]::UTF8; $OutputEncoding=[System.Text.Encoding]::UTF8; chcp 65001 | Out-Null"
      ],
      "env": {
        "LANG": "zh_CN.UTF-8",
        "LC_ALL": "zh_CN.UTF-8"
      }
    }
  }
}
```

**关键点**：
- ✅ 终端启动时强制设置 UTF-8
- ✅ 文件默认 UTF-8 编码
- ✅ 环境变量设置语言为中文 UTF-8

#### 2.2 EditorConfig 配置 `.editorconfig`

跨编辑器统一配置：

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true

[*.php]
charset = utf-8
indent_style = space
indent_size = 4

[*.{js,json,md}]
charset = utf-8
indent_style = space
indent_size = 2
```

---

### Step 3: Git 配置 (如果使用 Git)

#### 3.1 Git 全局配置

```bash
# 中文路径不转义
git config --global core.quotepath false

# 编码配置
git config --global gui.encoding utf-8
git config --global i18n.commit.encoding utf-8
git config --global i18n.logoutputencoding utf-8

# 换行符配置
git config --global core.autocrlf false
git config --global core.eol lf
```

#### 3.2 项目级 `.gitattributes` 配置 ⭐ 强烈推荐

在项目根目录创建 `.gitattributes` 文件，确保所有协作者的文本文件处理一致：

```gitattributes
# Git 属性配置 - 确保文本文件正确处理
* text=auto

# 所有文本文件强制使用 LF 换行符
*.php text eol=lf
*.js text eol=lf
*.json text eol=lf
*.md text eol=lf
*.html text eol=lf
*.css text eol=lf

# Windows 脚本保持 CRLF
*.bat text eol=crlf
*.cmd text eol=crlf
*.ps1 text eol=crlf

# 二进制文件
*.png binary
*.jpg binary
*.mp4 binary
```

**关键作用**：
- ✅ 确保跨平台协作时换行符统一（LF）
- ✅ 防止 Windows 用户意外提交 CRLF
- ✅ 二进制文件不会被当作文本处理
- ✅ 所有克隆仓库的人都自动应用相同规则

---

### Step 4: Windows 系统级配置 (可选但推荐)

#### 方法 A: Windows 设置界面

1. 打开 **Windows 设置**
2. **时间和语言** → **语言和区域**
3. 点击 **管理语言设置**
4. **更改系统区域设置**
5. 勾选 ✅ **Beta: 使用 Unicode UTF-8 提供全球语言支持**
6. **重启电脑** 🔄

**优点**: 系统全局支持 UTF-8，所有程序都受益
**缺点**: 需要重启电脑，可能影响老旧程序

#### 方法 B: Windows Terminal 配置

打开 Windows Terminal 设置 (`settings.json`)，添加：

```json
{
  "profiles": {
    "defaults": {
      "encoding": "utf-8"
    }
  }
}
```

---

## 🎯 AI Agent 使用最佳实践

### PowerShell 文件操作

```powershell
# ✅ 推荐：显式指定 UTF-8 编码
[System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)
[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)

# ✅ 如果必须用 Get-Content/Set-Content
Get-Content $path -Encoding UTF8
Set-Content $path -Value $content -Encoding UTF8

# ❌ 避免：使用默认编码（会用 GB2312）
Get-Content $path
Set-Content $path $content
```

### 批量替换时的正确方式

```powershell
# ✅ 正确：保持 UTF-8 编码
$content = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)
$content = $content -replace 'old', 'new'
[System.IO.File]::WriteAllText($file, $content, [System.Text.Encoding]::UTF8)

# ❌ 错误：会导致乱码
$content = Get-Content $file -Raw
$content = $content -replace 'old', 'new'
Set-Content $file -Value $content
```

---

## 📋 验证清单

### 完整检查脚本

运行自动检查脚本：
```powershell
.\tools\check-encoding.ps1
```

### 手动验证步骤

```powershell
# 1. 检查 PowerShell 编码
[Console]::OutputEncoding.CodePage    # 应该: 65001
[Console]::InputEncoding.CodePage     # 应该: 65001
$OutputEncoding.CodePage              # 应该: 65001

# 2. 检查代码页
chcp                                   # 应该: 活动代码页: 65001

# 3. 测试中文输出
Write-Output "测试中文: 你好世界 ABC 123"  # 应该正常显示

# 4. 测试文件读写
"测试UTF-8" | Out-File test.txt -Encoding UTF8
Get-Content test.txt -Encoding UTF8   # 应该正常显示
Remove-Item test.txt
```

---

## ⚠️ 常见问题与解决

### Q1: 配置后还是乱码？

**A**: 需要完全重启 VSCode
1. 关闭所有终端窗口
2. 完全退出 VSCode 应用程序（不是重新加载窗口）
3. 重新打开 VSCode

### Q2: 只有特定文件乱码？

**A**: 文件本身编码可能不是 UTF-8

```powershell
# 检查文件编码（前3个字节）
Get-Content $file -Encoding Byte -TotalCount 3

# 如果是 239, 187, 191 = UTF-8 with BOM
# 如果不是，需要转换编码：
$content = Get-Content $file -Raw -Encoding UTF8
[System.IO.File]::WriteAllText($file, $content, (New-Object System.Text.UTF8Encoding $false))
```

### Q3: Git 提交信息乱码？

**A**: 检查 Git 配置
```bash
git config --global --get i18n.commit.encoding  # 应该: utf-8
git config --global --get i18n.logoutputencoding  # 应该: utf-8
```

### Q4: 老项目中文文件名显示乱码？

**A**: 
```bash
git config --global core.quotepath false
```

---

## 📁 文件清单

配置完成后应该有以下文件：

| 文件路径 | 作用 | 必需性 |
|---------|------|--------|
| `$PROFILE.CurrentUserAllHosts` | PowerShell 自动 UTF-8 | ⭐ 必需 |
| `.vscode/settings.json` | VSCode 项目配置 | ⭐ 必需 |
| `.editorconfig` | 跨编辑器配置 | ⭐ 强烈推荐 |
| `.gitattributes` | Git 仓库文本文件统一 | ⭐ 强烈推荐 |
| `tools/check-encoding.ps1` | 编码检查工具 | ✅ 推荐 |

---

## 🎉 最终确认

### 重启后检查

1. ✅ 打开新的 PowerShell 终端，看到 "✓ PowerShell UTF-8 编码已启用"
2. ✅ 运行 `chcp` 显示 65001
3. ✅ 运行 `[Console]::OutputEncoding.CodePage` 显示 65001
4. ✅ 中文输出正常显示
5. ✅ AI Agent 操作文件无乱码
6. ✅ Git 中文路径和提交信息正常

### 完成标志

```
┌────────────────────────────────────────┐
│  ✓ PowerShell 全局 UTF-8 配置         │
│  ✓ VSCode 终端 UTF-8 强制启动         │
│  ✓ 项目文件默认 UTF-8                 │
│  ✓ Git 中文支持 + .gitattributes      │
│  ✓ EditorConfig 跨编辑器统一          │
│  ✓ 自动检查工具                       │
└────────────────────────────────────────┘
     🎊 配置完成，永久告别乱码！
```

---

## 📚 相关文档

- `ENCODING-SOLUTION.md` - 详细的编码问题分析
- `VSCODE-GLOBAL-ENCODING.md` - VSCode 全局配置指南
- `tools/check-encoding.ps1` - 编码检查脚本

---

**配置完成时间**: 2026-01-21  
**维护者**: GitHub Copilot  
**测试环境**: Windows 11 + VSCode + PowerShell 7 + Git
