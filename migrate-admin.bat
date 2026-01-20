@echo off
chcp 65001 >nul
echo.
echo ====================================
echo RZX.ME Admin 安全迁移工具
echo ====================================
echo.
echo 选择操作:
echo.
echo 1. 验证当前文件编码
echo 2. 模拟运行迁移 (不实际复制)
echo 3. 执行实际迁移
echo 4. 回滚迁移
echo 0. 退出
echo.
set /p choice="请输入选项 (0-4): "

if "%choice%"=="1" goto verify
if "%choice%"=="2" goto dryrun
if "%choice%"=="3" goto migrate
if "%choice%"=="4" goto rollback
if "%choice%"=="0" goto end
goto menu

:verify
echo.
echo 正在验证文件编码...
powershell -ExecutionPolicy Bypass -File ".\tools\migrate-admin-safe.ps1" -Verify
pause
goto end

:dryrun
echo.
echo 正在模拟运行迁移...
powershell -ExecutionPolicy Bypass -File ".\tools\migrate-admin-safe.ps1" -DryRun
pause
goto end

:migrate
echo.
echo ⚠️  警告: 即将执行实际迁移操作！
echo.
set /p confirm="确认执行? (yes/no): "
if not "%confirm%"=="yes" (
    echo 已取消
    pause
    goto end
)
echo.
echo 正在执行迁移...
powershell -ExecutionPolicy Bypass -File ".\tools\migrate-admin-safe.ps1"
pause
goto end

:rollback
echo.
echo ⚠️  警告: 即将回滚迁移！
echo.
set /p confirm="确认回滚? (yes/no): "
if not "%confirm%"=="yes" (
    echo 已取消
    pause
    goto end
)
echo.
echo 正在回滚...
powershell -ExecutionPolicy Bypass -File ".\tools\migrate-admin-safe.ps1" -Rollback
pause
goto end

:end
echo.
echo 操作完成
