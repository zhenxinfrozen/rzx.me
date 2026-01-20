<?php
/**
 * 缩略图中心 - 配置管理部分 (partial)
 *
 * @var array $all_configs
 * @var array|null $test_result
 * @var string|null $edit_id
 * @var array|null $edit_config
 */
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i data-feather="list" class="me-2"></i>所有配置 (<?= count($all_configs) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>配置ID</th>
                            <th>名称</th>
                            <th>尺寸</th>
                            <th>质量</th>
                            <th>格式</th>
                            <th>剪裁</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_configs as $id => $config): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($id) ?></code></td>
                            <td>
                                <?= htmlspecialchars($config['name']) ?>
                                <?php if (!empty($config['builtin'])): ?>
                                    <span class="badge bg-info ms-1">内置</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-1">自定义</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= (int)$config['width'] ?>×<?= (int)$config['height'] ?></strong>px</td>
                            <td><?= (int)$config['quality'] ?>%</td>
                            <td><span class="badge bg-secondary"><?= strtoupper(htmlspecialchars($config['format'])) ?></span></td>
                            <td>
                                <?php if (!empty($config['crop'])): ?>
                                    <span class="badge bg-success"><i data-feather="check" class="feather-sm"></i></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark"><i data-feather="x" class="feather-sm"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="post" action="/admin?page=thumbnail-center#config-manager-tab" class="d-inline">
                                    <?php if (empty($config['builtin'])): ?>
                                    <input type="hidden" name="action" value="load_edit">
                                    <input type="hidden" name="config_id" value="<?= htmlspecialchars($id) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="编辑">
                                        <i data-feather="edit-2" class="feather-sm"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="删除" onclick="deleteConfig('<?= htmlspecialchars($id) ?>')">
                                        <i data-feather="trash-2" class="feather-sm"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary test-config" data-id="<?= htmlspecialchars($id) ?>" title="测试">
                                        <i data-feather="play" class="feather-sm"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i data-feather="play-circle" class="me-2"></i>配置测试</h5>
            </div>
            <div class="card-body">
                <form method="post" id="test-form" action="/admin?page=thumbnail-center#config-manager-tab">
                    <input type="hidden" name="action" value="test_generate">
                    <div class="mb-3">
                        <label for="config_id_test" class="form-label">选择配置</label>
                        <select class="form-select" id="config_id_test" name="config_id" required>
                            <option value="">请选择...</option>
                            <?php foreach ($all_configs as $id => $config): ?>
                            <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($config['name']) ?> (<?= htmlspecialchars($id) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="test_image" class="form-label">测试图片路径</label>
                        <input type="text" class="form-control" id="test_image" name="test_image" placeholder="留空则使用默认图片">
                        <div class="form-text">相对于 `public/assets/images/` 的路径</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">测试生成</button>
                </form>

                <?php if ($test_result): ?>
                <hr>
                <div class="alert alert-<?= $test_result['success'] ? 'success' : 'danger' ?>">
                    <h6 class="alert-heading">测试结果</h6>
                    <?php if ($test_result['success']): ?>
                        <p>原图: <?= htmlspecialchars($test_result['original_path']) ?></p>
                        <p>输出: <?= htmlspecialchars($test_result['output_path']) ?></p>
                        <p>大小: <?= number_format($test_result['file_size']/1024, 2) ?> KB</p>
                        <?php
                        $webPath = str_replace(realpath(__DIR__ . '/../../../..'), '', $test_result['output_path']);
                        $webPath = str_replace('\\', '/', $webPath);
                        ?>
                        <div class="text-center mt-2"><img src="<?= $webPath ?>?t=<?= time() ?>" class="img-fluid border rounded" style="max-width:200px;"></div>
                    <?php else: ?>
                        <p>错误: <?= htmlspecialchars($test_result['error'] ?? '未知错误') ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i data-feather="sliders" class="me-2"></i><?= $edit_config ? '编辑' : '添加' ?>自定义预设</h5>
            </div>
            <div class="card-body">
                <form method="post" action="/admin?page=thumbnail-center#config-manager-tab">
                    <input type="hidden" name="action" value="<?= $edit_config ? 'update_config' : 'add_config' ?>">
                    <div class="mb-3">
                        <label class="form-label">配置ID *</label>
                        <input type="text" class="form-control" name="config_id" value="<?= htmlspecialchars($edit_id ?? '') ?>" <?= $edit_config ? 'readonly' : '' ?> required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">配置名称 *</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($edit_config['name'] ?? '') ?>" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">宽度</label><input type="number" class="form-control" name="width" value="<?= (int)($edit_config['width'] ?? 200) ?>" required></div>
                        <div class="col-6"><label class="form-label">高度</label><input type="number" class="form-control" name="height" value="<?= (int)($edit_config['height'] ?? 200) ?>" required></div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6"><label class="form-label">质量</label><input type="number" class="form-control" name="quality" value="<?= (int)($edit_config['quality'] ?? 80) ?>" min="1" max="100" required></div>
                        <div class="col-6"><label class="form-label">格式</label><select class="form-select" name="format"><?php $fmt = strtolower($edit_config['format'] ?? 'jpg'); ?><option value="jpg" <?= $fmt==='jpg'?'selected':'' ?>>JPG</option><option value="png" <?= $fmt==='png'?'selected':'' ?>>PNG</option><option value="webp" <?= $fmt==='webp'?'selected':'' ?>>WEBP</option></select></div>
                    </div>
                    <div class="mt-2"><label class="form-label">存放目录</label><input type="text" class="form-control" name="directory" value="<?= htmlspecialchars($edit_config['directory'] ?? 'thumbs') ?>"></div>
                    <div class="mt-2"><label class="form-label">文件后缀</label><input type="text" class="form-control" name="suffix" value="<?= htmlspecialchars($edit_config['suffix'] ?? '_thumb') ?>"></div>
                    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" value="1" id="cropCheck" name="crop" <?= !empty($edit_config['crop']) ? 'checked' : '' ?>><label class="form-check-label" for="cropCheck">裁剪模式</label></div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-<?= $edit_config ? 'warning' : 'success' ?>"><?= $edit_config ? '保存更新' : '添加配置' ?></button>
                        <?php if ($edit_config): ?>
                            <a href="/admin?page=thumbnail-center#config-manager-tab" class="btn btn-secondary mt-2">取消编辑</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form method="post" id="delete-config-form" action="/admin?page=thumbnail-center#config-manager-tab" class="d-none">
    <input type="hidden" name="action" value="delete_config">
    <input type="hidden" name="config_id" id="delete-config-id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testButtons = document.querySelectorAll('.test-config');
    const configSelect = document.getElementById('config_id_test');

    testButtons.forEach(button => {
        button.addEventListener('click', function() {
            const configId = this.getAttribute('data-id');
            configSelect.value = configId;
            document.getElementById('test-form').scrollIntoView({ behavior: 'smooth' });
        });
    });
});

function deleteConfig(configId) {
    if (confirm(`确定要删除配置 "${configId}" 吗？`)) {
        document.getElementById('delete-config-id').value = configId;
        document.getElementById('delete-config-form').submit();
    }
}
</script>
