<?php
/**
 * 管理工具页面的视图
 *
 * @var string $dev_query - 开发模式下的查询字符串
 */
?>

<div class="admin-page-content">
    <!-- 工具卡片网格 -->
    <div class="row g-3">
        <!-- 缩略图中心 -->
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-0 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                            <i data-feather="image"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">缩略图中心</h5>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-4 flex-grow-1">
                        统一管理缩略图的批量生成、清理、配置预设和功能测试。
                    </p>
                    <a href="/admin?page=thumbnail-center" class="btn btn-primary btn-lg">
                        <i data-feather="arrow-right-circle" class="me-2"></i>进入中心
                    </a>
                </div>
            </div>
        </div>

        <!-- 缓存清理 -->
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-info text-white border-0 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                            <i data-feather="trash-2"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">缓存管理器</h5>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-4 flex-grow-1">
                        查看缓存统计，清理各类临时文件和缩略图缓存，优化站点性能。
                    </p>
                    <a href="/admin?page=cache-manager" class="btn btn-info btn-lg">
                        <i data-feather="arrow-right-circle" class="me-2"></i>开始管理
                    </a>
                </div>
            </div>
        </div>

        <!-- 系统信息 -->
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-warning text-dark border-0 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-dark bg-opacity-10 rounded-circle p-2 me-3">
                            <i data-feather="hard-drive"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">系统信息</h5>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-4 flex-grow-1">
                        查看服务器环境、PHP配置、磁盘空间和内存使用情况的详细报告。
                    </p>
                    <a href="/admin?page=system-info" class="btn btn-warning btn-lg">
                        <i data-feather="arrow-right-circle" class="me-2"></i>查看报告
                    </a>
                </div>
            </div>
        </div>

        <!-- 网站配置 -->
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary text-white border-0 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                            <i data-feather="sliders"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">网站配置</h5>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-4 flex-grow-1">
                        管理站点的核心配置文件。 (功能开发中)
                    </p>
                    <a href="/admin?page=site-config" class="btn btn-secondary btn-lg disabled">
                        <i data-feather="arrow-right-circle" class="me-2"></i>进行配置
                    </a>
                </div>
            </div>
        </div>

        <!-- 回收站 -->
        <div class="col-lg-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-danger text-white border-0 position-relative">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-10 rounded-circle p-2 me-3">
                            <i data-feather="archive"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">回收站</h5>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted mb-4 flex-grow-1">
                        管理和恢复从 `single-works` 删除的分类。
                    </p>
                    <a href="/admin?page=trash" class="btn btn-danger btn-lg">
                        <i data-feather="arrow-right-circle" class="me-2"></i>查看回收站
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
