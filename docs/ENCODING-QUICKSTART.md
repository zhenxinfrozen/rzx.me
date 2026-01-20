# 🚀 中文编码问题 - 快速开始

## 已完成的配置

✅ **EditorConfig** - 所有编辑器通用的编码规范  
✅ **Git 配置** - UTF-8 支持  
✅ **编码检测工具** - 自动检测和修复  
✅ **VS Code 配置示例** - 推荐设置  

## 立即生效

配置已经自动生效！你现在可以：

1. **正常使用 VS Code 和 AI Agent**
   - 新建的文件自动使用 UTF-8 无 BOM
   - 移动文件不会破坏编码

2. **提交代码到 Git**
   ```bash
   git add .
   git commit -m "你的提交信息"  # 中文完全支持！
   ```

3. **定期检查编码（建议每周一次）**
   ```bash
   php tools/check-encoding.php
   ```

## VS Code 用户额外设置（可选）

虽然 `.editorconfig` 已经够用，但如果你想要 VS Code 特定的增强功能：

1. 复制示例配置：
   ```bash
   cp .vscode/settings.example.json .vscode/settings.json
   ```

2. 或在 VS Code 中：
   - `Ctrl+,` 打开设置
   - 点击右上角 "打开设置 (JSON)"
   - 复制 `.vscode/settings.example.json` 的内容

## 遇到乱码怎么办？

### 方法 1：自动修复（推荐）
```bash
php tools/check-encoding.php --fix
```

### 方法 2：VS Code 手动修复
1. 打开乱码文件
2. 右下角点击编码显示（如 "GBK"）
3. 选择 "Reopen with Encoding" → "GBK"
4. 确认内容正确后，点击编码 → "Save with Encoding" → "UTF-8"

## 验证配置

```bash
# 检查 Git 配置
git config --local --list | Select-String "i18n|encoding"

# 检查项目编码
php tools/check-encoding.php

# 检查单个文件（应该看到 3C 3F 70，即 <?p）
Get-Content -Encoding Byte app/bootstrap.php | Select-Object -First 3 | Format-Hex
```

## 完整文档

详细说明请查看：[docs/ENCODING-SOLUTION.md](./ENCODING-SOLUTION.md)

---

**🎉 现在你的项目已经完全配置好 UTF-8 支持！从此告别中文乱码！**
