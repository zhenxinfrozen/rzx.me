<?php
/**
 * 缩略图中心 视图
 *
 * @var string $dev_query
 * @var array $galleries
 * @var array $builtin_configs
 * @var array $custom_configs
 * @var array $all_configs
 * @var array|null $test_result
 * @var string|null $edit_id
 * @var array|null $edit_config
 * @var string $message
 * @var string $message_type
*/
?>

<div class="ray-body-box-useless">
<!-- 页面头部 -->
    <div class="page-header">
        <h1><i data-feather="image" class="me-2"></i>缩略图中心</h1>
        <p class="text-muted">统一管理缩略图的批量操作、配置预设和功能测试。</p>
    </div>

    <!-- 消息提示 -->
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="alert alert-info" id="statusPanel" style="display: none;">
        <div id="statusMessage"></div>
    </div>


    <!-- 标签页导航 -->
    <ul class="nav nav-tabs" id="thumbnailTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="batch-manager-tab" data-bs-toggle="tab" data-bs-target="#batch-manager" type="button" role="tab" aria-controls="batch-manager" aria-selected="true">
                <i data-feather="grid" class="me-1"></i> 批量管理
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="config-manager-tab" data-bs-toggle="tab" data-bs-target="#config-manager" type="button" role="tab" aria-controls="config-manager" aria-selected="false">
                <i data-feather="settings" class="me-1"></i> 配置管理
            </button>
        </li>
    </ul>

    <!-- 标签页内容 -->
    <div class="tab-content" id="thumbnailTabContent">
        <!-- 批量管理面板 -->
        <div class="tab-pane fade show active" id="batch-manager" role="tabpanel" aria-labelledby="batch-manager-tab">
            <div class="p-3">
                <?php require_once __DIR__ . '/admin-thumbnail-batch.php'; ?>
            </div>
        </div>

        <!-- 配置管理面板 -->
        <div class="tab-pane fade" id="config-manager" role="tabpanel" aria-labelledby="config-manager-tab">
            <div class="p-3">
                <?php require_once __DIR__ . '/admin-thumbnail-config.php'; ?>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 激活 Feather 图标
    feather.replace();

    // 标签页切换时，重新激活图标
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            feather.replace();
        });
    });
});
</script>
</div>
