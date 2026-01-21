# UTF-8 编码配置完全指南

**问题**: VSCode AI Agent 操作文件时出现中文乱码

**根本原因**: Windows PowerShell 默认使用 GB2312 (代码页 936) 而不是 UTF-8

## 解决方案

### 1. PowerShell 全局配置

已自动添加到 PowerShell 配置文件：
```
%USERPROFILE%\Documents\PowerShell\profile.ps1
```

配置内容：
```powershell
# UTF-8 编码配置
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::InputEncoding = [System.Text.Encoding]::UTF8

# 设置默认参数
$PSDefaultParameterValues['Out-File:Encoding'] = 'utf8'
$PSDefaultParameterValues['*:Encoding'] = 'utf8'
```

### 2. VSCode 项目配置

已创建 `.vscode/settings.json`，包含：
- 文件编码强制使用 UTF-8
- 终端启动时自动设置 UTF-8
- 所有文件类型默认 UTF-8

### 3. Windows 系统设置（可选但推荐）

#### 方法 A: 通过 Windows 设置
1. 打开 **设置** → **时间和语言** → **语言和区域**
2. 点击 **管理语言设置**
3. 点击 **更改系统区域设置**
4. 勾选 **Beta: 使用 Unicode UTF-8 提供全球语言支持**
5. 重启电脑

#### 方法 B: 通过注册表（高级用户）
```powershell
# 设置系统默认代码页为 UTF-8
Set-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\Control\Nls\CodePage" -Name "ACP" -Value "65001"
Set-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\Control\Nls\CodePage" -Name "OEMCP" -Value "65001"
```

### 4. Git 配置（如果使用 Git）

```bash
git config --global core.quotepath false
git config --global gui.encoding utf-8
git config --global i18n.commit.encoding utf-8
git config --global i18n.logoutputencoding utf-8
```

## 验证配置

### 1. 重启 VSCode 终端
关闭所有终端窗口，重新打开一个新终端。

### 2. 检查 PowerShell 编码
```powershell
[Console]::OutputEncoding
[Console]::InputEncoding
$OutputEncoding
```

应该显示：
```
CodePage: 65001
EncodingName: Unicode (UTF-8)
```

### 3. 检查当前代码页
```powershell
chcp
```

应该显示：
```
活动代码页: 65001
```

### 4. 测试中文输出
```powershell
Write-Output "测试中文: 你好世界"
echo "测试中文"
```

应该正常显示中文，无乱码。

## AI Agent 使用注意事项

### PowerShell 命令
当 AI Agent 需要读写文件时，确保使用：

```powershell
# 读取文件
[System.IO.File]::ReadAllText($path, [System.Text.Encoding]::UTF8)

# 写入文件
[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)
```

**避免使用**：
```powershell
# ❌ 不要用 Get-Content/Set-Content 的默认行为
Get-Content $path  # 可能使用错误编码
Set-Content $path $content  # 可能使用错误编码

# ✅ 如果必须使用，显式指定编码
Get-Content $path -Encoding UTF8
Set-Content $path $content -Encoding UTF8
```

### Git 操作
```bash
# ✅ 推荐：让 Git 自动检测
git add .
git commit -m "message"

# ✅ 或者在 .gitattributes 中配置
* text=auto eol=lf
*.php text eol=lf
```

## 常见问题

### Q: 为什么修改后还是出现乱码？
A: 需要完全重启 VSCode，不是重新加载窗口。关闭 VSCode 应用程序后重新打开。

### Q: 只有特定文件有问题？
A: 检查文件本身的编码：
```powershell
# 检查文件编码
Get-Content $file -Encoding Byte | Select-Object -First 3
```

如果前3个字节是 `239, 187, 191`，文件有 UTF-8 BOM。
如果文件显示乱码，可能需要重新保存为 UTF-8：
```powershell
$content = Get-Content $file -Raw -Encoding UTF8
[System.IO.File]::WriteAllText($file, $content, (New-Object System.Text.UTF8Encoding $false))
```

### Q: AI Agent 的 replace_string_in_file 工具会有问题吗？
A: 不会。`replace_string_in_file` 工具本身会正确处理 UTF-8，问题主要出在 PowerShell 的 `Get-Content/Set-Content` 命令。

### Q: 需要每次手动设置吗？
A: 不需要。配置文件会在每次 PowerShell 启动时自动加载。

## 文件清单

完成配置后，应该有以下文件：

1. **PowerShell 配置文件**
   - 位置: `%USERPROFILE%\Documents\PowerShell\profile.ps1`
   - 作用: PowerShell 启动时自动设置 UTF-8

2. **VSCode 项目配置**
   - 位置: `.vscode/settings.json`
   - 作用: VSCode 和终端默认使用 UTF-8

3. **Git 配置**（如果使用）
   - 位置: `.gitattributes`
   - 作用: 统一行尾符和文本编码

## 最终检查清单

- [ ] PowerShell 配置文件已创建并包含 UTF-8 设置
- [ ] VSCode 项目 settings.json 已配置
- [ ] 重启 VSCode
- [ ] 新终端显示 UTF-8 编码
- [ ] 测试中文输出正常
- [ ] AI Agent 操作文件无乱码

---

**配置完成时间**: 2026-01-21  
**影响范围**: 项目内所有文件操作  
**推荐**: 同时配置 Windows 系统 UTF-8 支持（需重启电脑）
