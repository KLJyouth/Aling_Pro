# 移除PHP文件中的UTF-8 BOM标记
# UTF-8 BOM标记是一个字节序列：0xEF, 0xBB, 0xBF
# 这些标记可能导致PHP解析错误，特别是在文件开头的<?php标记之前

# 要处理的目录
$directories = @(
    "apps/ai-platform/Services/NLP",
    "ai-engines/cv",
    "src/Utils",
    "src/Performance"
)

$totalFixed = 0
$totalFiles = 0

foreach ($directory in $directories) {
    if (-not (Test-Path $directory)) {
        Write-Output "目录不存在: $directory"
        continue
    }

    Write-Output "处理目录: $directory"
    
    $files = Get-ChildItem -Path $directory -Filter "*.php" -Recurse -File
    
    foreach ($file in $files) {
        $totalFiles++
        $filePath = $file.FullName
        
        # 读取文件前几个字节来检查BOM
        $bytes = [System.IO.File]::ReadAllBytes($filePath)
        
        # 检查是否有BOM标记
        if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
            Write-Output "发现BOM标记: $filePath"
            
            # 读取文件内容（跳过BOM标记）
            $content = [System.Text.Encoding]::UTF8.GetString($bytes, 3, $bytes.Length - 3)
            
            # 检查PHP开始标记是否正确
            if (-not $content.StartsWith("<?php")) {
                # 如果不是以<?php开头，添加正确的PHP开始标记
                if ($content.StartsWith("<?")) {
                    # 替换短标记
                    $content = "<?php" + $content.Substring(2)
                } elseif ($content.StartsWith("hp")) {
                    # 修复错误的hp标记
                    $content = "<?php" + $content.Substring(2)
                } else {
                    # 添加完整标记
                    $content = "<?php`n`n" + $content
                }
            }
            
            # 保存修复后的文件
            [System.IO.File]::WriteAllText($filePath, $content, [System.Text.Encoding]::UTF8)
            
            Write-Output "已修复: $filePath"
            $totalFixed++
        }
    }
}

Write-Output "`n总结:"
Write-Output "检查的文件总数: $totalFiles"
Write-Output "修复的文件总数: $totalFixed"