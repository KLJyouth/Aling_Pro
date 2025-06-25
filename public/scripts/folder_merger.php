<?php
/**
 * AlingAi Pro 5.0 - 文件夹合并工�?
 * 将根目录下与public文件夹内重名的文件夹合并到public文件夹内
 */

echo "🔄 AlingAi Pro 5.0 - 文件夹合并工具\n";
echo "======================================================================\n";

class FolderMerger 
{
    private $rootPath;
    private $publicPath;
    private $duplicateFolders = [];
    private $mergedFolders = [];
    private $skippedFiles = [];
    private $errors = [];

    public function __construct($rootPath) {
        $this->rootPath = rtrim($rootPath, '/\\'];
        $this->publicPath = $this->rootPath . DIRECTORY_SEPARATOR . 'public';
        
        if (!is_dir($this->publicPath)) {
            throw new Exception("Public文件夹不存在: {$this->publicPath}"];
        }
    }

    public function findDuplicateFolders() {
        echo "🔍 查找重名文件�?..\n";
        echo "----------------------------------------\n";
        
        $rootFolders = $this->getFolders($this->rootPath];
        $publicFolders = $this->getFolders($this->publicPath];
        
        $this->duplicateFolders = array_intersect($rootFolders, $publicFolders];
        
        echo "根目录文件夹: " . count($rootFolders) . " 个\n";
        echo "Public文件�? " . count($publicFolders) . " 个\n";
        echo "重名文件�? " . count($this->duplicateFolders) . " 个\n\n";
        
        if (empty($this->duplicateFolders)) {
            echo "�?未发现重名文件夹\n";
            return false;
        }
        
        echo "📋 发现以下重名文件�?\n";
        foreach ($this->duplicateFolders as $folder) {
            echo "   �?$folder\n";
        }
        echo "\n";
        
        return true;
    }

    private function getFolders($path) {
        $folders = [];
        
        if (!is_dir($path)) {
            return $folders;
        }
        
        $items = scandir($path];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $folders[] = $item;
            }
        }
        
        return $folders;
    }

    public function mergeFolders($dryRun = false) {
        if (empty($this->duplicateFolders)) {
            echo "⚠️ 没有重名文件夹需要合并\n";
            return false;
        }
        
        echo ($dryRun ? "🔍 预览模式" : "🔄 执行合并") . " - 合并重名文件�?..\n";
        echo "----------------------------------------\n";
        
        foreach ($this->duplicateFolders as $folderName) {
            $this->mergeFolder($folderName, $dryRun];
        }
        
        return true;
    }

    private function mergeFolder($folderName, $dryRun = false) {
        $sourcePath = $this->rootPath . DIRECTORY_SEPARATOR . $folderName;
        $targetPath = $this->publicPath . DIRECTORY_SEPARATOR . $folderName;
        
        echo "📁 处理文件�? $folderName\n";
        echo "   源路�? $sourcePath\n";
        echo "   目标路径: $targetPath\n";
        
        if (!is_dir($sourcePath)) {
            echo "   ⚠️ 源文件夹不存在，跳过\n\n";
            return;
        }
        
        if (!is_dir($targetPath)) {
            echo "   📂 目标文件夹不存在，将整个移动\n";
            if (!$dryRun) {
                if ($this->moveDirectory($sourcePath, $targetPath)) {
                    echo "   �?移动成功\n";
                    $this->mergedFolders[] = $folderName;
                } else {
                    echo "   �?移动失败\n";
                    $this->errors[] = "移动文件夹失�? $folderName";
                }
            } else {
                echo "   🔍 [预览] 将移动整个文件夹\n";
            }
        } else {
            echo "   🔀 目标文件夹已存在，合并内容\n";
            $this->mergeDirectoryContents($sourcePath, $targetPath, $dryRun];
        }
        
        echo "\n";
    }

    private function mergeDirectoryContents($sourcePath, $targetPath, $dryRun = false) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::SELF_FIRST
        ];
        
        $movedCount = 0;
        $skippedCount = 0;
        
        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($sourcePath) + 1];
            $targetItemPath = $targetPath . DIRECTORY_SEPARATOR . $relativePath;
            
            if ($item->isDir()) {
                if (!is_dir($targetItemPath)) {
                    if (!$dryRun) {
                        if (mkdir($targetItemPath, 0755, true)) {
                            echo "     📂 创建目录: $relativePath\n";
                        } else {
                            echo "     �?创建目录失败: $relativePath\n";
                            $this->errors[] = "创建目录失败: $relativePath";
                        }
                    } else {
                        echo "     🔍 [预览] 将创建目�? $relativePath\n";
                    }
                }
            } else {
                if (!file_exists($targetItemPath)) {
                    if (!$dryRun) {
                        // 确保目标目录存在
                        $targetDir = dirname($targetItemPath];
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true];
                        }
                        
                        if (copy($item->getPathname(), $targetItemPath)) {
                            echo "     📄 复制文件: $relativePath\n";
                            $movedCount++;
                        } else {
                            echo "     �?复制文件失败: $relativePath\n";
                            $this->errors[] = "复制文件失败: $relativePath";
                        }
                    } else {
                        echo "     🔍 [预览] 将复制文�? $relativePath\n";
                        $movedCount++;
                    }
                } else {
                    echo "     ⚠️ 文件已存在，跳过: $relativePath\n";
                    $this->skippedFiles[] = $relativePath;
                    $skippedCount++;
                }
            }
        }
        
        echo "     📊 统计: 移动 $movedCount 个文件，跳过 $skippedCount 个文件\n";
    }

    private function moveDirectory($source, $target) {
        return rename($source, $target];
    }

    public function deleteOriginalFolders($dryRun = false) {
        if (empty($this->duplicateFolders)) {
            echo "⚠️ 没有原始文件夹需要删除\n";
            return false;
        }
        
        echo ($dryRun ? "🔍 预览模式" : "🗑�?执行删除") . " - 删除原始重名文件�?..\n";
        echo "----------------------------------------\n";
        
        foreach ($this->duplicateFolders as $folderName) {
            $folderPath = $this->rootPath . DIRECTORY_SEPARATOR . $folderName;
            
            if (!is_dir($folderPath)) {
                echo "📁 $folderName - 已不存在，跳过\n";
                continue;
            }
            
            if (!$dryRun) {
                if ($this->deleteDirectory($folderPath)) {
                    echo "�?删除成功: $folderName\n";
                } else {
                    echo "�?删除失败: $folderName\n";
                    $this->errors[] = "删除文件夹失�? $folderName";
                }
            } else {
                echo "🔍 [预览] 将删�? $folderName\n";
            }
        }
        
        echo "\n";
        return true;
    }

    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS],
            RecursiveIteratorIterator::CHILD_FIRST
        ];
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (!rmdir($item->getPathname())) {
                    return false;
                }
            } else {
                if (!unlink($item->getPathname())) {
                    return false;
                }
            }
        }
        
        return rmdir($dir];
    }

    public function generateReport() {
        echo "📋 合并操作报告\n";
        echo "======================================================================\n";
        
        echo "📊 统计信息:\n";
        echo "   🔍 发现重名文件�? " . count($this->duplicateFolders) . " 个\n";
        echo "   �?成功合并文件�? " . count($this->mergedFolders) . " 个\n";
        echo "   ⚠️ 跳过的文�? " . count($this->skippedFiles) . " 个\n";
        echo "   �?错误数量: " . count($this->errors) . " 个\n\n";
        
        if (!empty($this->duplicateFolders)) {
            echo "📁 处理的重名文件夹:\n";
            foreach ($this->duplicateFolders as $folder) {
                echo "   �?$folder\n";
            }
            echo "\n";
        }
        
        if (!empty($this->skippedFiles)) {
            echo "⚠️ 跳过的文�?(�?0�?:\n";
            $showFiles = array_slice($this->skippedFiles, 0, 10];
            foreach ($showFiles as $file) {
                echo "   �?$file\n";
            }
            if (count($this->skippedFiles) > 10) {
                echo "   ... 还有 " . (count($this->skippedFiles) - 10) . " 个文件\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "�?错误列表:\n";
            foreach ($this->errors as $error) {
                echo "   �?$error\n";
            }
            echo "\n";
        }
        
        $success = count($this->errors) === 0;
        $score = empty($this->duplicateFolders) ? 100 : round((count($this->duplicateFolders) - count($this->errors)) / count($this->duplicateFolders) * 100];
        
        echo "🎯 合并结果:\n";
        if ($success) {
            echo "   🎉 全部成功！所有重名文件夹已合并\n";
        } else {
            echo "   ⚠️ 部分成功，成功率: {$score}%\n";
        }
        
        echo "\n💡 建议:\n";
        echo "   📋 检查合并后的文件夹结构\n";
        echo "   🧪 运行系统测试确保功能正常\n";
        echo "   📊 如有需要，可手动处理跳过的文件\n";
        
        echo "\n======================================================================\n";
        echo "🎯 文件夹合并完成！\n";
        echo "�?完成时间: " . date('Y-m-d H:i:s') . "\n";
        
        return $success;
    }
}

// 使用说明
function showUsage() {
    echo "使用方法:\n";
    echo "  php folder_merger.php [选项]\n\n";
    echo "选项:\n";
    echo "  --preview    预览模式，不执行实际操作\n";
    echo "  --execute    执行模式，实际合并和删除文件夹\n";
    echo "  --help       显示此帮助信息\n\n";
    echo "示例:\n";
    echo "  php folder_merger.php --preview   # 预览将要执行的操作\n";
    echo "  php folder_merger.php --execute   # 执行实际的合并操作\n";
}

// 主程�?
try {
    $mode = isset($argv[1]) ? $argv[1] : '--help';
    
    if ($mode === '--help') {
        showUsage(];
        exit(0];
    }
    
    $dryRun = ($mode === '--preview'];
    $execute = ($mode === '--execute'];
    
    if (!$dryRun && !$execute) {
        echo "�?无效的参�? $mode\n\n";
        showUsage(];
        exit(1];
    }
    
    echo "启动文件夹合并工�?..\n";
    echo "模式: " . ($dryRun ? "预览模式" : "执行模式") . "\n\n";
    
    $rootPath = __DIR__ . '/..';
    $merger = new FolderMerger($rootPath];
    
    // 查找重名文件�?
    if (!$merger->findDuplicateFolders()) {
        exit(0];
    }
    
    // 合并文件�?
    $merger->mergeFolders($dryRun];
    
    // 删除原始文件�?
    if ($execute) {
        $merger->deleteOriginalFolders($dryRun];
    }
    
    // 生成报告
    $success = $merger->generateReport(];
    
    exit($success ? 0 : 1];
    
} catch (Exception $e) {
    echo "�?错误: " . $e->getMessage() . "\n";
    exit(1];
}

