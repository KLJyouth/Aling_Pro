<?php
/**
 * 移除PHP文件中的UTF-8 BOM标记
 * 
 * UTF-8 BOM标记是一个字节序列：0xEF, 0xBB, 0xBF
 * 这些标记可能导致PHP解析错误，特别是在文件开头的<?php标记之前
 */

// 要处理的目录
$directories = [
    "apps/ai-platform/Services/NLP",
    "ai-engines/cv",
    "src/Utils",
    "src/Performance"
];

$totalFixed = 0;
$totalFiles = 0;

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        echo "目录不存在: $directory\n";
        continue;
    }

    echo "处理目录: $directory\n";
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory],
        RecursiveIteratorIterator::LEAVES_ONLY
    ];

    foreach ($files as $file) {
        if (!$file->isFile() || $file->getExtension() !== "php") {
            continue;
        }

        $totalFiles++;
        $filePath = $file->getRealPath(];
        $content = file_get_contents($filePath];
        
        // 检查是否有BOM标记
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            // 移除BOM标记
            $content = substr($content, 3];
            
            // 检查PHP开始标记是否正确
            if (substr($content, 0, 5) !== "<?php") {
                // 如果不是以<?php开头，添加正确的PHP开始标记
                if (substr($content, 0, 2) === "<?") {
                    // 替换短标记
                    $content = "<?php" . substr($content, 2];
                } else {
                    // 添加完整标记
                    $content = "<?php\n\n" . $content;
                }
            }
            
            // 保存修复后的文件
            file_put_contents($filePath, $content];
            
            echo "已修复: $filePath\n";
            $totalFixed++;
        }
    }
}

echo "\n总结:\n";
echo "检查的文件总数: $totalFiles\n";
echo "修复的文件总数: $totalFixed\n";
