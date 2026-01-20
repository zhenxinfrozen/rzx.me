<?php
/**
 * 项目文档查看器 - Wiki风格 AJAX版
 */

if (!isset($files)) {
    require_once __DIR__ . '/../../controllers/docs-handler.php';
}

$page_title = '项目文档';
$page_subtitle = '查看和搜索项目的各类技术文档与记录';
?>

<div class="ray-body-box-useless">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css">
<style>
    /* 全局容器优化 */
    .docs-wrapper {
        display: flex;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        min-height: 800px;
        border: 1px solid #e1e4e8;
    }

    /* 侧边栏样式 - 参考 Billfish */
    .docs-sidebar {
        width: 300px;
        background: #f6f8fa;
        border-right: 1px solid #d0d7de;
        display: flex;
        flex-direction: column;
        user-select: none;
    }

    .search-box-container {
        padding: 24px 20px 16px;
        border-bottom: 1px solid #e1e4e8;
        background: #f6f8fa;
    }

    .search-box-container .input-group {
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border-radius: 6px;
        overflow: hidden;
    }

    .docs-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }

    /* 滚动条美化 */
    .docs-list::-webkit-scrollbar { width: 5px; }
    .docs-list::-webkit-scrollbar-track { background: transparent; }
    .docs-list::-webkit-scrollbar-thumb { background: #d1d5da; border-radius: 10px; }

    .section-header {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        cursor: pointer;
        color: #0d1117;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
        background: #f6f8fa;
        border-top: 1px solid #d0d7de;
        border-bottom: 1px solid #d0d7de;
        /* margin-top: 8px; */
    }
    .section-header:first-child { margin-top: 0; border-top: none; }
    .section-header:hover { background: #4c86bdff; color: #ffffffff; }

    .section-header .toggle-icon {
        margin-left: auto;
        font-size: 12px;
        transition: transform 0.2s;
        opacity: 0.5;
    }
    .section-header.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }

    .section-content {
        background: #fff;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    .section-content.collapsed {
        display: none;
    }

    .doc-item {
        display: flex;
        align-items: center;
        padding: 8px 20px 8px 35px;
        color: #57606a;
        text-decoration: none !important;
        font-size: 13.5px;
        transition: all 0.2s;
        position: relative;
        border-left: 3px solid transparent;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .doc-item:hover {
        background: #f6f8fa;
        color: #0d1117;
        border-left-color: #d0d7de;
    }
    .doc-item.active {
        background: #ddf4ff;
        color: #0969da;
        font-weight: 500;
        border-left-color: #0969da;
    }
    .doc-item.active::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: #0969da;
        border-radius: 50%;
    }

    /* 主内容区样式 */
    .docs-main {
        flex: 1;
        background: #fff;
        position: relative;
        overflow-y: auto;
    }

    .content-canvas {
        max-width: 960px;
        margin: 0 auto;
        padding: 50px 60px;
        transition: opacity 0.3s;
    }

    /* 面包屑 */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 30px;
        font-size: 14px;
        color: #57606a;
    }
    .breadcrumb-nav a { color: #0969da; text-decoration: none; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .separator { color: #d1d5da; font-size: 12px; }

    .doc-meta {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eaecef;
    }

    /* Markdown 样式增强 */
    .markdown-body {
        font-size: 16.5px;
        line-height: 1.8;
        color: #24292f;
    }
    .markdown-body h1 {
        font-size: 2.25em;
        font-weight: 600;
        padding-bottom: 0.3em;
        border-bottom: 1px solid #eaecef;
        margin-bottom: 25px;
    }
    .markdown-body h2 { margin-top: 35px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .markdown-body pre {
        background-color: #f6f8fa;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #e1e4e8;
        position: relative;
    }

    /* 复制按钮优化 */
    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        background: #fff;
        border: 1px solid #d0d7de;
        border-radius: 6px;
        font-size: 12px;
        color: #24292f;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .copy-btn:hover { background: #f3f4f6; border-color: #1b1f24; }
    pre:hover .copy-btn { opacity: 1; }

    /* 全局搜索遮罩/加载效果 */
    #docs-loader {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: #0969da;
        z-index: 100;
        display: none;
    }
    .loading-active #docs-loader {
        display: block;
        animation: loading-bar 1.5s infinite;
    }
    @keyframes loading-bar {
        0% { left: 0; width: 0; }
        50% { left: 0; width: 100%; }
        100% { left: 100%; width: 0; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="docs-wrapper" id="docs-app-container">
            <!-- 加载条 -->
            <div id="docs-loader"></div>

            <!-- 侧边栏 -->
            <div class="docs-sidebar">
                <div class="search-box-container">
                    <form action="index.php" method="GET" id="docs-search-form">
                        <input type="hidden" name="page" value="docs">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-1" placeholder="搜索文档 (Ctrl+K)" value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                        </div>
                    </form>
                </div>
                <div class="docs-list">
                    <?php if (empty($files)): ?>
                        <div class="text-center text-muted p-4">没有发现文档</div>
                    <?php else: ?>
                        <?php foreach ($files as $section):
                            $isSectionActive = false;
                            if ($selectedFile) {
                                foreach ($section['files'] as $f) {
                                    if ($selectedFile === $f['file']) {
                                        $isSectionActive = true;
                                        break;
                                    }
                                }
                            }
                        ?>
                            <div class="section-header <?= $isSectionActive ? '' : 'collapsed' ?>" onclick="toggleSection(this)">
                                <i class="bi bi-folder2-open me-2"></i>
                                <span><?= htmlspecialchars($section['name']) ?></span>
                                <i class="bi bi-chevron-down toggle-icon"></i>
                            </div>
                            <div class="section-content <?= $isSectionActive ? '' : 'collapsed' ?>">
                                <?php foreach ($section['files'] as $fileMeta): ?>
                                    <a href="/admin?page=docs&file=<?= urlencode($fileMeta['file']) ?>"
                                       class="doc-item ajax-load <?= $selectedFile === $fileMeta['file'] ? 'active' : '' ?>"
                                       data-file="<?= $fileMeta['file'] ?>"
                                       title="<?= htmlspecialchars($fileMeta['title']) ?>">
                                        <?= htmlspecialchars($fileMeta['title']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 主内容区 -->
            <div class="docs-main" id="docs-content-area">
                <div class="content-canvas">
                    <?php if ($searchQuery): ?>
                         <div id="document-view">
                            <h3><i class="bi bi-search me-2"></i>搜索结果: "<?= htmlspecialchars($searchQuery) ?>"</h3>
                            <hr>
                            <?php if (empty($searchResults)): ?>
                                <p class="text-muted">未找到匹配的结果。</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($searchResults as $result): ?>
                                        <a href="/admin?page=docs&file=<?= urlencode($result['file']) ?>" class="list-group-item list-group-item-action py-3 ajax-load" data-file="<?= $result['file'] ?>">
                                            <h5 class="mb-1 text-primary"><?= htmlspecialchars($result['title']) ?></h5>
                                            <p class="mb-1 text-muted small"><?= $result['preview'] ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                         </div>

                    <?php elseif ($document): ?>
                        <div id="document-view">
                            <nav class="breadcrumb-nav" aria-label="breadcrumb">
                                <a href="/admin?page=docs" class="ajax-load" data-file="">文档中心</a>
                                <span class="separator">/</span>
                                <span><?= htmlspecialchars($document['title']) ?></span>
                            </nav>

                            <div class="doc-meta">
                                <h1><?= htmlspecialchars($document['title']) ?></h1>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i> 最后修改: <?= date('Y-m-d H:i', $document['mtime']) ?></small>
                            </div>

                            <article class="markdown-body">
                                <?= $document['html'] ?>
                            </article>
                        </div>
                    <?php else: ?>
                        <div id="document-view">
                            <div class="text-center py-5">
                                <h2 class="mt-4 text-muted">请从左侧选择一个文档</h2>
                                <p class="text-muted">所有项目技术规范、重构日志和开发文档均在此管理。</p>
                                <div class="mt-4 opacity-50"><i class="bi bi-journal-richtext" style="font-size: 80px;"></i></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
<script>
    // AJAX 加载逻辑
    function loadDocument(file, updateState = true) {
        const container = document.getElementById('docs-app-container');
        const contentCanvas = document.querySelector('.content-canvas');

        container.classList.add('loading-active');
        contentCanvas.style.opacity = '0.5';

        // file 为空表示请求主页内容
        const url = `/admin?page=docs${file ? '&file=' + encodeURIComponent(file) : ''}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // 更新内容
                if (data.type === 'document') {
                    renderDocument(data);
                } else if (data.type === 'index') {
                    renderIndex();
                }

                // 更新侧边栏激活状态
                document.querySelectorAll('.doc-item').forEach(item => {
                    item.classList.toggle('active', item.getAttribute('data-file') === file);
                });

                // 历史记录处理
                if (updateState) {
                    window.history.pushState({file: file}, data.title || '文档中心', url);
                }

                document.title = (data.title || '文档中心') + ' - 项目文档';
            }
        })
        .catch(err => {
            console.error('加载失败:', err);
            // 如果 AJAX 失败，可以给个提示
            alert('加载文档失败，请检查网络或刷新页面。');
        })
        .finally(() => {
            container.classList.remove('loading-active');
            contentCanvas.style.opacity = '1';
            document.querySelector('.docs-main').scrollTo(0, 0);
        });
    }

    function renderIndex() {
        const contentCanvas = document.querySelector('.content-canvas');
        contentCanvas.innerHTML = `
            <div id="document-view">
                <div class="text-center py-5">
                    <h2 class="mt-4 text-muted">请从左侧选择一个文档</h2>
                    <p class="text-muted">所有项目技术规范、重构日志和开发文档均在此管理。</p>
                    <div class="mt-4 opacity-50"><i class="bi bi-journal-richtext" style="font-size: 80px;"></i></div>
                </div>
            </div>
        `;
    }

    function renderDocument(data) {
        const contentCanvas = document.querySelector('.content-canvas');
        contentCanvas.innerHTML = `
            <div id="document-view">
                <nav class="breadcrumb-nav" aria-label="breadcrumb">
                    <a href="/admin?page=docs" class="ajax-load" data-file="">文档中心</a>
                    <span class="separator">/</span>
                    <span>${data.title}</span>
                </nav>

                <div class="doc-meta">
                    <h1>${data.title}</h1>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i> 最后修改: ${data.mtime}</small>
                </div>

                <article class="markdown-body">
                    ${data.html}
                </article>
            </div>
        `;

        // 重新初始化代码高亮和复制按钮
        initMarkdownFeatures();
    }

    function initMarkdownFeatures() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });

        document.querySelectorAll('pre').forEach((pre) => {
            if (pre.querySelector('.copy-btn')) return;
            const btn = document.createElement('button');
            btn.className = 'copy-btn';
            btn.innerHTML = '<i class="bi bi-clipboard"></i> 复制';

            btn.addEventListener('click', () => {
                const code = pre.querySelector('code').innerText;
                navigator.clipboard.writeText(code).then(() => {
                    btn.innerHTML = '<i class="bi bi-check2"></i> 已复制';
                    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i> 复制', 2000);
                });
            });
            pre.appendChild(btn);
        });
    }

    function toggleSection(header) {
        const section = header.nextElementSibling;
        const isCollapsed = section.classList.contains('collapsed');

        if (isCollapsed) {
            section.classList.remove('collapsed');
            header.classList.remove('collapsed');
            section.style.maxHeight = section.scrollHeight + 'px';
        } else {
            section.classList.add('collapsed');
            header.classList.add('collapsed');
            section.style.maxHeight = '0';
        }
    }

    // 事件委派处理 AJAX 点击
    document.addEventListener('click', function(e) {
        const ajaxLink = e.target.closest('.ajax-load');
        if (ajaxLink) {
            const file = ajaxLink.getAttribute('data-file');
            // 只要有 data-file 属性（即使是空字符串表示首页），就进行 AJAX 加载
            if (file !== null) {
                e.preventDefault();
                loadDocument(file);
            }
        }
    });

    // 监听后退/前进
    window.onpopstate = function(event) {
        if (event.state && event.state.file) {
            loadDocument(event.state.file, false);
        } else {
            // 如果回到初始状态
            const urlParams = new URLSearchParams(window.location.search);
            const file = urlParams.get('file');
            if (file) {
                loadDocument(file, false);
            } else {
                location.reload();
            }
        }
    };

    // 快捷键 Ctrl+K 聚焦搜索
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput) searchInput.focus();
        }
    });

    // 搜索表单 AJAX 化
    document.getElementById('docs-search-form').addEventListener('submit', function(e) {
        const query = this.querySelector('input[name="q"]').value;
        if (query.trim()) {
            e.preventDefault();
            const url = `/admin?page=docs&q=${encodeURIComponent(query)}`;

            const container = document.getElementById('docs-app-container');
            container.classList.add('loading-active');

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // 渲染搜索结果
                    const contentCanvas = document.querySelector('.content-canvas');
                    let resultsHtml = `
                        <div id="document-view">
                            <h3><i class="bi bi-search me-2"></i>搜索结果: "${query}"</h3>
                            <hr>
                    `;

                    if (!data.results || data.results.length === 0) {
                        resultsHtml += `<p class="text-muted">未找到匹配的结果。</p>`;
                    } else {
                        resultsHtml += `<div class="list-group list-group-flush">`;
                        data.results.forEach(res => {
                            resultsHtml += `
                                <a href="/admin?page=docs&file=${encodeURIComponent(res.file)}" class="list-group-item list-group-item-action py-3 ajax-load" data-file="${res.file}">
                                    <h5 class="mb-1 text-primary">${res.title}</h5>
                                    <p class="mb-1 text-muted small">${res.preview}</p>
                                </a>
                            `;
                        });
                        resultsHtml += `</div>`;
                    }
                    resultsHtml += `</div>`;
                    contentCanvas.innerHTML = resultsHtml;

                    // 只要更新历史，不刷新页面
                    window.history.pushState({query: query}, `搜索: ${query}`, url);
                }
            })
            .finally(() => {
                container.classList.remove('loading-active');
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        initMarkdownFeatures();

        // 初始化侧边栏高度
        document.querySelectorAll('.section-content:not(.collapsed)').forEach(s => {
            s.style.maxHeight = s.scrollHeight + 'px';
        });
    });
</script>
</div>
