<?php
// 测试docs访问
echo "<h1>测试 /docs 路径访问</h1>";";

private $docsPath = __DIR__ . '/docs';';
echo "<p>文档目录路径: " . $docsPath . "</p>";";
echo "<p>目录是否存在: " . (is_dir($docsPath) ? '是' : '否') . "</p>";";

if (is_dir($docsPath)) {
    echo "<h2>docs目录内容:</h2>";";
    private $files = scandir($docsPath);
    echo "<ul>";";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {';
            echo "<li>" . $file . "</li>";";
        }
    }
    echo "</ul>";";
    
    private $indexFile = $docsPath . '/index.html';';
    echo "<p>index.html是否存在: " . (file_exists($indexFile) ? '是' : '否') . "</p>";";
    
    if (file_exists($indexFile)) {
        echo "<p><a href='/docs/' target='_blank'>直接访问 /docs/</a></p>";";
        echo "<p><a href='/docs/index.html' target='_blank'>直接访问 /docs/index.html</a></p>";";
    }
}

echo "<h2>链接测试:</h2>";";
echo "<ul>";";
echo "<li><a href='/docs/'>访问 /docs/</a></li>";";
echo "<li><a href='/docs/index.html'>访问 /docs/index.html</a></li>";";
echo "<li><a href='/docs'>访问 /docs (无斜杠)</a></li>";";
echo "</ul>";";
?>
