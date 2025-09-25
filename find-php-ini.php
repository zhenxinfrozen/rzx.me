<?php
echo "=== PHP配置信息 ===\n\n";

// 获取PHP配置文件路径
$phpIniPath = php_ini_loaded_file();
$phpIniScanDir = php_ini_scanned_files();

echo "📁 主配置文件路径:\n";
if ($phpIniPath) {
    echo "   " . $phpIniPath . "\n";
} else {
    echo "   未找到主配置文件\n";
}

echo "\n📂 扫描的配置目录:\n";
if ($phpIniScanDir) {
    $scanDirs = explode(',', $phpIniScanDir);
    foreach ($scanDirs as $dir) {
        echo "   " . trim($dir) . "\n";
    }
} else {
    echo "   无额外配置目录\n";
}

echo "\n🔧 当前相关配置:\n";
echo "   upload_max_filesize = " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size = " . ini_get('post_max_size') . "\n";
echo "   max_file_uploads = " . ini_get('max_file_uploads') . "\n";
echo "   max_execution_time = " . ini_get('max_execution_time') . "\n";

echo "\n📝 修改步骤:\n";
echo "1. 打开上述配置文件\n";
echo "2. 找到并修改以下配置项:\n";
echo "   upload_max_filesize = 50M\n";
echo "   post_max_size = 60M\n";
echo "   max_execution_time = 300\n";
echo "3. 保存文件并重启Web服务器\n";

echo "\n💡 提示:\n";
echo "- 如果配置项不存在，请手动添加\n";
echo "- post_max_size 应该大于 upload_max_filesize\n";
echo "- 修改后需要重启Apache/Nginx等Web服务器\n";

// 检测Web服务器类型
if (isset($_SERVER['SERVER_SOFTWARE'])) {
    echo "- 当前Web服务器: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
}
?>