# 中文编码问题完全解决方案

## 🎯 问题根源

### 为什么会出现中文乱码？

1. **Agent/Copilot 创建文件时编码不一致**
   - 某些情况下可能使用系统默认编码（Windows 上是 GBK）
   - 移动文件时可能触发"读取-删除-创建"流程，导致编码转换错误

2. **BOM (Byte Order Mark) 问题**
   - UTF-8 with BOM 会导致 PHP 输出问题（headers already sent）
   - 不同编辑器对 BOM 的处理不一致

3. **Git 配置问题**
   - 没有配置 UTF-8 支持
   - 路径名编码错误

## ✅ 已实施的解决方案

### 1. EditorConfig 配置（`.editorconfig`）

这是**跨编辑器**的统一配置文件，确保所有编辑器（VS Code, PHPStorm, Sublime Text等）都使用相同的编码设置。

**关键设置：**
```ini
[*]
charset = utf-8              # 所有文件使用 UTF-8 无 BOM
end_of_line = lf             # 统一使用 Unix 换行符
insert_final_newline = true  # 文件末尾插入新行
trim_trailing_whitespace = true
```

**PHP 特殊保护：**
```ini
[*.php]
charset = utf-8              # 绝对不能有 BOM！
```

### 2. VS Code 工作区配置（`.vscode/settings.json`）

专门针对 VS Code 和 AI Agent 的配置。

**核心设置：**
```json
{
  "files.encoding": "utf8",           // 默认 UTF-8
  "files.autoGuessEncoding": true,    // 自动检测现有文件编码
  "files.eol": "\n",                  // LF 换行符
  "files.trimTrailingWhitespace": true,
  "files.insertFinalNewline": true,
  "editor.renderWhitespace": "boundary" // 显示空白字符，便于发现问题
}
```

**语言特定配置：**
```json
{
  "[php]": {
    "files.encoding": "utf8"  // PHP 必须 UTF-8
  },
  "[markdown]": {
    "files.encoding": "utf8"
  }
}
```

### 3. Git 配置

已配置本地 Git 仓库支持 UTF-8：

```bash
git config --local core.quotepath false          # 中文路径不转义
git config --local i18n.commitencoding utf-8     # 提交信息使用 UTF-8
git config --local i18n.logoutputencoding utf-8  # 日志输出使用 UTF-8
git config --local gui.encoding utf-8            # GUI 使用 UTF-8
```

### 4. 编码检测工具（`tools/check-encoding.php`）

自动检测和修复编码问题的 CLI 工具。

## 📋 使用指南

### 日常开发

**现在你只需：**
1. ✅ 正常使用 VS Code 和 AI Agent - 配置会自动生效
2. ✅ 创建/编辑文件 - EditorConfig 确保编码正确
3. ✅ Git 提交 - 中文路径和信息正常显示

**不需要：**
- ❌ 手动设置文件编码
- ❌ 担心 Agent 创建的文件编码
- ❌ 每次检查 BOM

### 定期检查

**检查项目编码状态：**
```bash
php tools/check-encoding.php
```

**自动修复问题：**
```bash
php tools/check-encoding.php --fix
```

### 修复现有文件

如果发现某个文件有中文乱码：

**方法 1：使用工具自动修复**
```bash
php tools/check-encoding.php --fix
```

**方法 2：VS Code 手动修复**
1. 打开文件
2. 点击右下角编码显示（如 "GBK"）
3. 选择 "Reopen with Encoding" → "GBK"
4. 确认内容正确后，点击编码 → "Save with Encoding" → "UTF-8"

**方法 3：批量转换（PowerShell）**
```powershell
# 转换单个文件
$content = Get-Content -Path "文件.php" -Encoding GBK
Set-Content -Path "文件.php" -Value $content -Encoding UTF8NoBOM

# 批量转换（谨慎使用！）
Get-ChildItem -Recurse -Include *.php | ForEach-Object {
    $content = Get-Content $_.FullName -Encoding GBK
    Set-Content $_.FullName -Value $content -Encoding UTF8NoBOM
}
```

## 🛡️ 防护层级

你现在有**三层防护**：

1. **EditorConfig** - 基础层，跨编辑器通用
2. **VS Code Settings** - VS Code 和 AI Agent 专用
3. **Git Config** - 版本控制层面保护

## ⚠️ 特别注意

### PHP 文件的 BOM 问题

**为什么 PHP 不能有 BOM？**
```php
<?php
header('Content-Type: application/json');  // ❌ 如果有 BOM，这行会失败！
```

PHP 的 BOM 会被当作输出，导致：
- `headers already sent` 错误
- JSON 解析失败
- Session 无法启动

**检查方法：**
```bash
# 检查文件开头是否有 BOM（应该是 3C 3F 70，即 <?p）
Get-Content -Encoding Byte app/bootstrap.php | Select-Object -First 3 | Format-Hex
```

### Agent 操作最佳实践

**当 AI Agent 要移动/重构大量文件时：**

1. **先提交当前更改**
   ```bash
   git add .
   git commit -m "保存更改"
   ```

2. **让 Agent 执行操作**

3. **立即检查编码**
   ```bash
   php tools/check-encoding.php
   ```

4. **发现问题立即修复**
   ```bash
   php tools/check-encoding.php --fix
   ```

5. **如果问题严重，可以回滚**
   ```bash
   git diff  # 查看更改
   git reset --hard HEAD  # 撤销所有更改
   ```

## 🔍 常见问题

### Q: 为什么有时 VS Code 右下角显示 "GBK"？

**A:** 这是打开了旧文件。解决方法：
1. "Reopen with Encoding" → GBK
2. 确认内容正确
3. "Save with Encoding" → UTF-8

### Q: Git 提交信息中文显示乱码？

**A:** 已通过 Git 配置解决。如果还有问题：
```bash
git config --global i18n.commitencoding utf-8
git config --global i18n.logoutputencoding utf-8
```

### Q: 终端中文显示乱码？

**A:** PowerShell 设置：
```powershell
# 临时设置
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# 永久设置（添加到 PowerShell 配置文件）
notepad $PROFILE
# 添加：[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
```

### Q: Copilot 生成的代码有乱码？

**A:** 现在不会了！`.vscode/settings.json` 已配置：
```json
{
  "github.copilot.editor.enableAutoCompletions": true,
  "files.encoding": "utf8"
}
```

## 📊 验证配置

**检查配置是否生效：**

```bash
# 1. 检查 Git 配置
git config --local --list | Select-String "i18n|encoding|quotepath"

# 2. 检查 EditorConfig
Get-Content .editorconfig

# 3. 检查 VS Code 配置
Get-Content .vscode/settings.json

# 4. 扫描项目编码
php tools/check-encoding.php
```

## ✨ 总结

现在你的项目有了**完整的 UTF-8 编码保护体系**：

✅ **预防**：EditorConfig + VS Code Settings 确保新文件正确编码  
✅ **检测**：自动化工具检测现有问题  
✅ **修复**：一键自动修复编码问题  
✅ **版本控制**：Git 正确处理中文路径和内容  

**从此告别中文乱码！** 🎉
