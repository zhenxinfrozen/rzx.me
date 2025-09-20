<?php
// Lightweight view renderer
// Usage: echo render_template(__DIR__ . '/views/header.php', ['title' => '...']);
function render_template(string $file, array $vars = []) {
    if (!file_exists($file)) {
        throw new RuntimeException("Template not found: $file");
    }
    // extract variables into local scope but don't overwrite existing variables
    extract($vars, EXTR_SKIP);
    // capture output to allow post-processing or caching
    ob_start();
    include $file;
    return ob_get_clean();
}

function render_partial(string $file, array $vars = []) {
    echo render_template($file, $vars);
}
