<?php
// 动态扫描仓库中使用到的 Font Awesome 类（只扫描常见文本文件：php, html, js, css, tpl）
function scan_for_fa_classes($root)
{
    $files = [];
    $exts = ['php','html','htm','js','css','tpl','twig'];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $exts)) continue;
        $files[] = $file->getPathname();
    }

    $classes = [];
    foreach ($files as $f) {
        $content = @file_get_contents($f);
        if ($content === false) continue;
        // 捕获 class="..." 和 class='...'
        if (preg_match_all('/class\s*=\s*"([^"]+)"/is', $content, $m1)) {
            foreach ($m1[1] as $cls) {
                foreach (preg_split('/\s+/', $cls) as $token) {
                    if (strpos($token, 'fa-') === 0) {
                        // 尝试找出同一 class 属性里的样式前缀（fas/far/fab/fal/fad）
                        if (preg_match('/\b(fas|far|fal|fab|fad)\b/i', $cls, $p)) {
                            $classes[] = strtolower($p[1] . ' ' . $token);
                        } else {
                            $classes[] = strtolower($token);
                        }
                    }
                }
            }
        }
        if (preg_match_all("/class\\s*=\\s*'([^']+)'/is", $content, $m2)) {
            foreach ($m2[1] as $cls) {
                foreach (preg_split('/\s+/', $cls) as $token) {
                    if (strpos($token, 'fa-') === 0) {
                        if (preg_match('/\b(fas|far|fal|fab|fad)\b/i', $cls, $p)) {
                            $classes[] = strtolower($p[1] . ' ' . $token);
                        } else {
                            $classes[] = strtolower($token);
                        }
                    }
                }
            }
        }
        // 也支持直接出现的 fa-XXX（例如在 JS 中）
        if (preg_match_all('/\bfa-[a-z0-9-]+\b/i', $content, $m3)) {
            foreach ($m3[0] as $tk) $classes[] = strtolower($tk);
        }
    }

    $classes = array_unique($classes);
    sort($classes);
    return $classes;
}

$root = realpath(__DIR__ . '/../');
$fa_classes = scan_for_fa_classes($root);
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>项目中使用的 Font Awesome 图标（检测 Free 可用性）</title>
    <!-- 使用与 header.php 相同的 CDN 引用（Free 版本示例） -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--bg:#0f172a;--card:#fff;--muted:#6b7280}
        body{font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; padding:24px; background:#f1f5f9;color:#0f172a}
        header{display:flex;align-items:center;gap:16px;margin-bottom:18px}
        h1{font-size:20px;margin:0}
        .summary{color:#475569}
        .controls{margin-left:auto}
        .search{padding:8px 10px;border-radius:8px;border:1px solid #e2e8f0}
        .cols{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px}
        .panel{background:#ffffff;border-radius:10px;padding:14px;box-shadow:0 4px 12px rgba(2,6,23,0.04)}
        .panel h3{margin:0 0 10px 0;font-size:15px}
        .grid{display:flex;flex-wrap:wrap;gap:12px}
        .icon-card{width:150px;padding:12px;border-radius:8px;background:#f8fafc;border:1px solid #eef2ff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px}
        .icon-card.missing{opacity:0.45;background:#fff7f0;border-color:#fde68a}
        .icon-card i{font-size:28px;color:#0f172a}
        .icon-name{font-family:Menlo,monospace;font-size:13px;text-align:center;word-break:break-all;color:#0f172a}
        .muted{color:var(--muted);font-size:13px}
        .legend{display:flex;gap:12px;align-items:center}
        .badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#eef2ff;color:#0369a1;font-size:12px}
        .small{font-size:12px;color:#64748b}
    </style>
</head>
<body>
    <header>
        <div>
            <h1>项目中使用的 Font Awesome 图标</h1>
            <div class="summary small">自动扫描仓库文件（.php .html .js .css 等），并在浏览器端检测当前加载的 Free CSS 是否包含图标。</div>
        </div>
        <div class="controls">
            <input id="filter" class="search" placeholder="过滤类名（例如 fa-play）">
        </div>
    </header>

    <div class="cols">
        <div class="panel">
            <h3>可用（在当前加载的 Font Awesome CSS 中检测到） <span id="count-yes" class="badge">0</span></h3>
            <div id="yesGrid" class="grid"></div>
        </div>

        <div class="panel">
            <h3>不可用 / 可能为 Pro（未在当前 CSS 中检测到） <span id="count-no" class="badge">0</span></h3>
            <div id="noGrid" class="grid"></div>
        </div>
    </div>

    <section style="margin-top:18px">
        <div class="small muted">说明：检测通过读取元素 <code>::before</code> 伪元素的 content 值来判断图标是否由当前 CSS 提供；若返回空或 none 则认为不可用（可能为 Pro 图标或未被包含在所加载的版本）。</div>
        <div style="margin-top:8px" class="small">参考：<a href="https://fontawesome.com/" target="_blank" rel="noopener">fontawesome.com</a>、<a href="https://cdnjs.com/libraries/font-awesome" target="_blank" rel="noopener">cdnjs</a></div>
    </section>

    <script>
        // 从服务器传来的类名列表
        const faClasses = <?php echo json_encode(array_values($fa_classes), JSON_UNESCAPED_UNICODE); ?>;

        const yesGrid = document.getElementById('yesGrid');
        const noGrid = document.getElementById('noGrid');
        const countYes = document.getElementById('count-yes');
        const countNo = document.getElementById('count-no');

        function makeCard(cls, available) {
            const div = document.createElement('div');
            div.className = 'icon-card' + (available ? '' : ' missing');
            const i = document.createElement('i');
            i.className = cls;
            i.setAttribute('aria-hidden','true');
            const name = document.createElement('div');
            name.className = 'icon-name';
            name.textContent = cls;
            div.appendChild(i);
            div.appendChild(name);
            return div;
        }

        function testIconClass(cls) {
            // 创建一个临时元素并检测 ::before content
            const el = document.createElement('i');
            el.className = cls;
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            const content = window.getComputedStyle(el, '::before').content || '';
            document.body.removeChild(el);
            // content 常常是 quoted string like "\f0c7"，或者 "none"
            if (!content) return false;
            const cleaned = content.trim();
            if (cleaned === 'none' || cleaned === 'normal' || cleaned === '""' || cleaned === "''") return false;
            return true;
        }

        function render(filter) {
            yesGrid.innerHTML = '';
            noGrid.innerHTML = '';
            let y=0,n=0;
            faClasses.forEach(c => {
                if (filter && c.indexOf(filter) === -1) return;
                const ok = testIconClass(c);
                const card = makeCard(c, ok);
                if (ok) { yesGrid.appendChild(card); y++; } else { noGrid.appendChild(card); n++; }
            });
            countYes.textContent = y;
            countNo.textContent = n;
        }

        // 首次渲染
        window.addEventListener('DOMContentLoaded', () => {
            render(document.getElementById('filter').value.trim());
            document.getElementById('filter').addEventListener('input', (e)=>{
                render(e.target.value.trim());
            });
        });
    </script>
</body>
</html>