<?php
/**
 * 全面修复EnhancedAgentCoordinator.php文件
 */

echo "🔧 全面修复EnhancedAgentCoordinator.php...\n";

$coordinatorPath = 'e:\Code\AlingAi\AlingAi_pro\src\AI\EnhancedAgentCoordinator.php';
$backupPath = $coordinatorPath . '.backup.' . date('YmdHis');

// 备份原文件
copy($coordinatorPath, $backupPath);
echo "✅ 原文件已备份到: $backupPath\n";

// 读取原文件内容
$content = file_get_contents($coordinatorPath);

// 修复第124行的括号问题
$content = str_replace(
    '        } catch (\Exception $e) {
            $this->logger->error(\'任务分配失败\', [\'error\' => $e->getMessage()]);
            return [\'success\' => false, \'error\' => $e->getMessage()];
        }

    /**',
    '        } catch (\Exception $e) {
            $this->logger->error(\'任务分配失败\', [\'error\' => $e->getMessage()]);
            return [\'success\' => false, \'error\' => $e->getMessage()];
        }
    }

    /**',
    $content
);

// 写回文件
file_put_contents($coordinatorPath, $content);

echo "✅ 文件结构已修复\n";

// 验证语法
echo "🔍 验证PHP语法...\n";
$syntaxCheck = shell_exec("php -l \"$coordinatorPath\" 2>&1");
if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ PHP语法验证通过\n";
    
    // 如果语法正确，删除备份文件
    unlink($backupPath);
    echo "✅ 备份文件已删除\n";
} else {
    echo "❌ PHP语法错误:\n$syntaxCheck\n";
    echo "📋 正在恢复备份文件...\n";
    copy($backupPath, $coordinatorPath);
    echo "✅ 已恢复原文件\n";
}
