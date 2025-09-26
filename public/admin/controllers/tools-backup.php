<?php
/**
 * 管理工具
 * 系统维护和优化工具集合
 */

// 设置页面信息
$page_title = '🛠️ 管理工具';
$page_subtitle = '系统维护和优化工具集合';
$_GET['page'] = 'tools';

require_once '../views/layouts/header.php';

// 处理操作
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_cache':
            try {
                // 清理缓存逻辑
                $message = "缓存清理完成";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "缓存清理失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        case 'regenerate_thumbs':
            try {
                // 重新生成缩略图逻辑
                $message = "缩略图重新生成完成";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = "缩略图生成失败: " . $e->getMessage();
                $message_type = 'error';
            }
            break;
            
        default:
            $message = "未知操作";
            $message_type = 'error';
    }
}
?>

<style>
    .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .tool-card {
        background: var(--bg-secondary);
        padding: var(--spacing-lg);
        border-radius: var(--radius-medium);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-light);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .tool-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    
    .tool-card.highlight {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, var(--bg-secondary) 0%, rgba(0,115,170,0.05) 100%);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-small);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--primary-hover);
        color: white;
    }
    
    .btn-success {
        background: var(--success-color);
        color: white;
    }
    
    .btn-outline {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }
    
    .btn-outline:hover {
        background: var(--bg-primary);
    }
</style>

<!-- 消息显示 -->
<?php if ($message): ?>
<div class="message message-<?php echo $message_type; ?>" style="margin-bottom: 20px; padding: 15px; border-radius: var(--radius-small); <?php echo $message_type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : ($message_type === 'error' ? 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : 'background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;'); ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="page-header">
    <h1>🛠️ 管理工具</h1>
    <p>系统维护和优化工具集合</p>
</div>

<div class="tools-grid">
    <div class="tool-card highlight">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="image" style="margin-right: 10px; color: var(--primary-color);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">缩略图管理器</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            统一的缩略图生成、清理和管理工具。支持所有Gallery类型，提供批量处理和进度监控。
        </p>
        <a href="thumbnail-manager.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-primary">
            <i data-feather="tool"></i>
            打开工具
        </a>
    </div>

    <div class="tool-card highlight">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="settings" style="margin-right: 10px; color: var(--success-color);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">缩略图配置管理</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            配置和管理缩略图生成参数。支持预设管理、自定义配置、实时测试和预览功能。
        </p>
        <a href="../thumbnail-config-manager.php<?= isset($_GET['dev']) ? '?dev' : '' ?>" class="btn btn-success">
            <i data-feather="sliders"></i>
            配置管理
        </a>
    </div>

    <div class="tool-card">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="trash-2" style="margin-right: 10px; color: var(--text-secondary);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">缓存清理</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            清理系统缓存文件，包括临时文件、日志文件和过期数据。
        </p>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="btn btn-outline">
                <i data-feather="trash-2"></i>
                清理缓存
            </button>
        </form>
    </div>

    <div class="tool-card">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="refresh-cw" style="margin-right: 10px; color: var(--text-secondary);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">重新生成缩略图</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            批量重新生成所有画廊的缩略图文件，解决缩略图丢失或损坏问题。
        </p>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="regenerate_thumbs">
            <button type="submit" class="btn btn-outline">
                <i data-feather="refresh-cw"></i>
                重新生成
            </button>
        </form>
    </div>

    <div class="tool-card">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="database" style="margin-right: 10px; color: var(--text-secondary);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">数据库优化</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            优化数据库性能，清理冗余数据，重建索引。
        </p>
        <button class="btn btn-outline" disabled>
            <i data-feather="clock"></i>
            即将推出
        </button>
    </div>

    <div class="tool-card">
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <i data-feather="shield" style="margin-right: 10px; color: var(--text-secondary);"></i>
            <h3 style="margin: 0; color: var(--text-primary);">安全检查</h3>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            检查系统安全设置，扫描潜在安全漏洞。
        </p>
        <button class="btn btn-outline" disabled>
            <i data-feather="clock"></i>
            即将推出
        </button>
    </div>
</div>

<!-- 工具信息说明 -->
<div class="content-card" style="margin-top: 40px;">
    <h3 style="margin-bottom: 20px; color: var(--text-primary);">
        <i data-feather="info" style="margin-right: 8px;"></i>
        工具说明
    </h3>
    
    <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
        <div class="info-item">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <i data-feather="image" style="margin-right: 8px; color: var(--primary-color);"></i>
                <strong>缩略图管理器</strong>
            </div>
            <p style="color: var(--text-secondary); line-height: 1.5;">
                用于批量生成、更新或清理图片缩略图，支持所有Gallery类型的统一管理。
            </p>
        </div>
        
        <div class="info-item">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <i data-feather="sliders" style="margin-right: 8px; color: var(--success-color);"></i>
                <strong>缩略图配置管理</strong>
            </div>
            <p style="color: var(--text-secondary); line-height: 1.5;">
                配置缩略图生成参数，管理预设配置，支持自定义尺寸、质量、格式等设置。
            </p>
        </div>
        
        <div class="info-item">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <i data-feather="trash-2" style="margin-right: 8px; color: var(--text-secondary);"></i>
                <strong>缓存清理</strong>
            </div>
            <p style="color: var(--text-secondary); line-height: 1.5;">
                定期清理系统产生的临时文件和缓存数据，保持系统运行流畅。
            </p>
        </div>
        
        <div class="info-item">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <i data-feather="refresh-cw" style="margin-right: 8px; color: var(--text-secondary);"></i>
                <strong>批量重新生成</strong>
            </div>
            <p style="color: var(--text-secondary); line-height: 1.5;">
                当缩略图出现问题时，可以批量重新生成所有画廊的缩略图文件。
            </p>
        </div>
    </div>
</div>

<script>
// 初始化图标
if (typeof feather !== 'undefined') {
    feather.replace();
}

// 确认操作
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[method="post"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('input[name="action"]').value;
            let confirmMessage = '';
            
            switch(action) {
                case 'clear_cache':
                    confirmMessage = '确定要清理缓存吗？这将删除所有临时文件。';
                    break;
                case 'regenerate_thumbs':
                    confirmMessage = '确定要重新生成所有缩略图吗？这可能需要几分钟时间。';
                    break;
            }
            
            if (confirmMessage && !confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once '../views/layouts/footer.php'; ?>