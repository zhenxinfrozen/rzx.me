# UTF-8 编码检查脚本
# 用于验证所有编码配置是否正确

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "UTF-8 编码配置检查" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# 1. 检查 PowerShell 编码
Write-Host "1. PowerShell 编码设置:" -ForegroundColor Yellow
Write-Host "   输出编码: $([Console]::OutputEncoding.EncodingName)" -ForegroundColor $(if ([Console]::OutputEncoding.CodePage -eq 65001) { "Green" } else { "Red" })
Write-Host "   输入编码: $([Console]::InputEncoding.EncodingName)" -ForegroundColor $(if ([Console]::InputEncoding.CodePage -eq 65001) { "Green" } else { "Red" })
Write-Host "   变量编码: $($OutputEncoding.EncodingName)" -ForegroundColor $(if ($OutputEncoding.CodePage -eq 65001) { "Green" } else { "Red" })

# 2. 检查代码页
Write-Host "`n2. 活动代码页:" -ForegroundColor Yellow
$codepage = chcp
Write-Host "   $codepage" -ForegroundColor $(if ($codepage -match "65001") { "Green" } else { "Red" })

# 3. 检查 PowerShell 配置文件
Write-Host "`n3. PowerShell 配置文件:" -ForegroundColor Yellow
$profilePath = $PROFILE.CurrentUserAllHosts
if (Test-Path $profilePath) {
    $hasUtf8Config = (Get-Content $profilePath -Raw) -match "UTF8"
    Write-Host "   路径: $profilePath" -ForegroundColor Green
    Write-Host "   UTF-8配置: $(if ($hasUtf8Config) { '已配置 ✓' } else { '未配置 ✗' })" -ForegroundColor $(if ($hasUtf8Config) { "Green" } else { "Red" })
} else {
    Write-Host "   配置文件不存在 ✗" -ForegroundColor Red
}

# 4. 检查 VSCode 项目配置
Write-Host "`n4. VSCode 项目配置:" -ForegroundColor Yellow
$vscodeSettingsPath = ".\.vscode\settings.json"
if (Test-Path $vscodeSettingsPath) {
    $settings = Get-Content $vscodeSettingsPath -Raw
    $hasEncodingConfig = $settings -match '"files.encoding".*utf8'
    $hasTerminalConfig = $settings -match 'terminal.integrated.profiles.windows'
    Write-Host "   settings.json: 存在 ✓" -ForegroundColor Green
    Write-Host "   编码配置: $(if ($hasEncodingConfig) { '已配置 ✓' } else { '未配置 ✗' })" -ForegroundColor $(if ($hasEncodingConfig) { "Green" } else { "Red" })
    Write-Host "   终端配置: $(if ($hasTerminalConfig) { '已配置 ✓' } else { '未配置 ✗' })" -ForegroundColor $(if ($hasTerminalConfig) { "Green" } else { "Red" })
} else {
    Write-Host "   .vscode/settings.json 不存在 ✗" -ForegroundColor Red
}

# 5. 测试中文输出
Write-Host "`n5. 中文输出测试:" -ForegroundColor Yellow
$testString = "测试中文: 你好世界 Hello World 123"
Write-Host "   $testString" -ForegroundColor Green

# 6. 检查文件编码函数
Write-Host "`n6. 文件操作测试:" -ForegroundColor Yellow
$testFile = ".\test-encoding-temp.txt"
$testContent = "测试UTF-8编码: 中文正常"
try {
    [System.IO.File]::WriteAllText($testFile, $testContent, [System.Text.Encoding]::UTF8)
    $readContent = [System.IO.File]::ReadAllText($testFile, [System.Text.Encoding]::UTF8)
    if ($readContent -eq $testContent) {
        Write-Host "   文件读写测试: 通过 ✓" -ForegroundColor Green
    } else {
        Write-Host "   文件读写测试: 失败 ✗" -ForegroundColor Red
    }
    Remove-Item $testFile -Force
} catch {
    Write-Host "   文件操作测试失败: $($_.Exception.Message)" -ForegroundColor Red
}

# 7. 总结
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "配置建议:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ([Console]::OutputEncoding.CodePage -ne 65001) {
    Write-Host "⚠ 需要重启 VSCode 终端以应用配置" -ForegroundColor Yellow
}

if (-not (Test-Path $profilePath) -or -not ((Get-Content $profilePath -Raw) -match "UTF8")) {
    Write-Host "⚠ 运行以下命令配置 PowerShell:" -ForegroundColor Yellow
    Write-Host '   Add-Content -Path $PROFILE.CurrentUserAllHosts -Value "`n[Console]::OutputEncoding=[System.Text.Encoding]::UTF8" -Encoding UTF8' -ForegroundColor Gray
}

Write-Host "`n✓ 检查完成！" -ForegroundColor Green
Write-Host "`n提示: 如果编码不是 UTF-8，请关闭终端并打开新终端" -ForegroundColor Cyan
