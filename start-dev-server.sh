#!/bin/bash
# 启动开发服务器 - Linux/Mac 脚本
# 在项目根目录运行此脚本

echo "启动 RZX.ME 开发服务器..."
echo "服务器地址: http://localhost:8000"
echo "按 Ctrl+C 停止服务器"
echo ""

cd "$(dirname "$0")"
php -S localhost:8000 -t public dev-server.php