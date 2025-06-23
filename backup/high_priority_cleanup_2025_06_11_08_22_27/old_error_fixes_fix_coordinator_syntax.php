<?php
/**
 * 修复EnhancedAgentCoordinator.php的语法错误
 */

echo "🔧 修复EnhancedAgentCoordinator语法错误...\n";

$coordinatorPath = 'e:\Code\AlingAi\AlingAi_pro\src\AI\EnhancedAgentCoordinator.php';

if (!file_exists($coordinatorPath)) {
    echo "❌ 文件不存在: $coordinatorPath\n";
    exit(1);
}

$content = file_get_contents($coordinatorPath);

// 查找和修复getStatus方法
$statusMethodPattern = '/\/\*\*\s*\*\s*获取系统状态.*?\*\/\s*public function getStatus\(\): array\s*\{.*?\}/s';

$newStatusMethod = '    /**
     * 获取系统状态
     */
    public function getStatus(): array
    {
        return [
            "status" => "active",
            "coordinator_id" => "enhanced-coordinator",
            "coordinator_status" => "running",
            "active_agents" => count($this->activeAgents),
            "active_agents_count" => count($this->activeAgents),
            "available_agents_count" => count(array_filter($this->agentCapabilities, fn($agent) => $agent[\'availability\'] === \'available\')),
            "total_tasks" => count($this->taskQueue) + count($this->completedTasks),
            "pending_tasks" => count($this->taskQueue),
            "queued_tasks" => count($this->taskQueue),
            "completed_tasks" => count($this->completedTasks),
            "system_health" => "good",
            "ai_service_connected" => $this->aiService !== null,
            "database_connected" => $this->database !== null,
            "configuration" => $this->config,
            "last_update" => date(\'Y-m-d H:i:s\'),
            "timestamp" => date("Y-m-d H:i:s")
        ];
    }';

// 替换第一个getStatus方法
$content = preg_replace($statusMethodPattern, $newStatusMethod, $content, 1);

// 清理可能的重复代码片段
$content = preg_replace('/\s*return \[\s*"status" => "active",.*?\];\s*\}/s', '', $content);

// 清理多余的}符号
$content = preg_replace('/}\s*}/', '}', $content);

// 确保文件以单个}结尾
if (substr(trim($content), -1) !== '}') {
    $content = rtrim($content) . "\n}";
}

// 写回文件
file_put_contents($coordinatorPath, $content);

echo "✅ 语法错误已修复\n";

// 验证语法
echo "🔍 验证PHP语法...\n";
$syntaxCheck = shell_exec("php -l \"$coordinatorPath\" 2>&1");
if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✅ PHP语法验证通过\n";
} else {
    echo "❌ PHP语法错误:\n$syntaxCheck\n";
}
