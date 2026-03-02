# 宝塔 + Git 自动部署清单

适用场景：用宝塔面板通过 Git/Webhook 自动拉取并部署站点代码。

## 1. 账号与目录权限
- 确认部署进程的用户（常见为 `www` 或 `root`）。
- 确保站点目录归属与部署用户一致：
  - 示例：`chown -R www:www /www/wwwroot/your-site`
- 避免部署用户对目录仅有只读权限。

## 2. Git 安全目录（必读）
- Git 2.35+ 默认启用安全目录校验。
- 如果报错 `detected dubious ownership`，执行：
  - `git config --global --add safe.directory /www/wwwroot/your-site`
- 这个配置每个站点目录只需要执行一次。

## 3. 远端仓库与分支
- 确认仓库 URL（HTTPS/SSH）：
  - `git remote -v`
- 确认部署分支：
  - `git branch -vv`
- 宝塔面板中选择正确分支（如 `v1.6.0`）。

## 4. Webhook 与自动部署
- 确认 Webhook 配置正确（仓库地址、密钥、触发事件）。
- 触发后查看宝塔日志是否拉取成功。
- 常见失败原因：
  - 安全目录未配置
  - 权限不足
  - 远端仓库地址变更

## 5. 缓存与服务重载
- 代码更新后如无变化，尝试重启：
  - PHP-FPM
  - Nginx
- 如果启用 OPcache，确认已清缓存或重启服务。

## 6. 快速验证
- `git log -1 --oneline` 确认最新提交。
- `grep -R "/admin/login.php" -n /www/wwwroot/your-site` 验证旧跳转是否仍存在。

## 7. 建议的标准流程
1) 拉取代码
2) 检查分支与提交
3) 重启 PHP-FPM / Nginx
4) 访问关键页面验证

---
如需扩展为“自动部署最佳实践”或“宝塔安全加固清单”，告诉我即可。
