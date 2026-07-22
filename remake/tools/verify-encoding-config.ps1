#!/usr/bin/env pwsh
# VS Code 全局配置验证脚本

Write-Host "`n=== VS Code 全局编码配置验证 ===`n" -ForegroundColor Cyan

# 1. 检查用户设置文件
$settingsPath = "$env:APPDATA\Code\User\settings.json"
if (Test-Path $settingsPath) {
    Write-Host "✅ 找到用户设置文件" -ForegroundColor Green

    $content = Get-Content $settingsPath -Raw -Encoding UTF8

    # 检查关键配置
    $checks = @{
        "files.encoding" = $content -match '"files\.encoding"\s*:\s*"utf8"'
        "files.autoGuessEncoding" = $content -match '"files\.autoGuessEncoding"\s*:\s*true'
        "files.eol" = $content -match '"files\.eol"\s*:\s*"\\n"'
        "trimTrailingWhitespace" = $content -match '"files\.trimTrailingWhitespace"\s*:\s*true'
    }

    Write-Host "`n关键配置检查:" -ForegroundColor Yellow
    foreach ($key in $checks.Keys) {
        if ($checks[$key]) {
            Write-Host "  ✅ $key" -ForegroundColor Green
        } else {
            Write-Host "  ❌ $key (未配置或配置错误)" -ForegroundColor Red
        }
    }
} else {
    Write-Host "❌ 未找到用户设置文件" -ForegroundColor Red
}

# 2. 检查 Git 全局配置
Write-Host "`n`nGit 全局配置:" -ForegroundColor Yellow
$gitConfigs = git config --global --list | Select-String "utf-8|quotepath"
if ($gitConfigs) {
    $gitConfigs | ForEach-Object { Write-Host "  ✅ $_" -ForegroundColor Green }
} else {
    Write-Host "  ❌ Git 全局配置未完成" -ForegroundColor Red
}

# 3. 检查当前项目的 .editorconfig
Write-Host "`n`n项目 EditorConfig:" -ForegroundColor Yellow
if (Test-Path ".editorconfig") {
    Write-Host "  ✅ .editorconfig 存在" -ForegroundColor Green
} else {
    Write-Host "  ⚠️  .editorconfig 不存在（可选）" -ForegroundColor Yellow
}

Write-Host "`n=== 验证完成 ===`n" -ForegroundColor Cyan
Write-Host "📝 如果有 ❌ 项，请按照 docs/VSCODE-GLOBAL-ENCODING.md 完成配置`n"
