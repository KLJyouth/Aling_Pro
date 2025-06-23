<?php
/**
 * 智能体编译修复验证测试
 * 专门测试IntelligentAgentController及相关类的编译问题修复
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "🧪 智能体编译修复验证测试\n";
echo "============================================================\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";
echo "============================================================\n";

// 测试核心类是否可以加载
$testClasses = [
    'AlingAi\\Controllers\\Api\\IntelligentAgentController' => '智能体API控制器',
    'AlingAi\\AI\\IntelligentAgentSystem' => '智能体系统',
    'AlingAi\\AI\\EnhancedAgentCoordinator' => '增强智能体协调器',
    'AlingAi\\Core\\SelfEvolutionSystem' => '自我进化系统',
    'AlingAi\\AI\\SelfLearningFramework' => '自学习框架'
];

$results = [
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

echo "\n🏗️ 测试核心类加载...\n";

foreach ($testClasses as $className => $displayName) {
    try {
        if (class_exists($className)) {
            echo "[✅ PASS] 类加载: $displayName\n";
            
            // 检查类的基本信息
            $reflection = new ReflectionClass($className);
            $methodCount = count($reflection->getMethods(ReflectionMethod::IS_PUBLIC));
            echo "         公共方法数: $methodCount\n";
            
            $results['passed']++;
            $results['details'][$className] = [
                'status' => 'success',
                'methods' => $methodCount
            ];
        } else {
            echo "[❌ FAIL] 类加载: $displayName - 类不存在\n";
            $results['failed']++;
            $results['details'][$className] = [
                'status' => 'failed',
                'error' => 'Class not found'
            ];
        }
    } catch (Exception $e) {
        echo "[❌ FAIL] 类加载: $displayName - {$e->getMessage()}\n";
        $results['failed']++;
        $results['details'][$className] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

echo "\n🔍 测试方法可用性...\n";

// 测试IntelligentAgentSystem的关键方法
try {
    if (class_exists('AlingAi\\AI\\IntelligentAgentSystem')) {
        $reflection = new ReflectionClass('AlingAi\\AI\\IntelligentAgentSystem');
        
        $requiredMethods = [
            'getAllAgents',
            'updateAgent', 
            'startAgent',
            'stopAgent',
            'restartAgent',
            'getAgentLogs',
            'healthCheck',
            'getPerformanceMetrics',
            'getLearningStatistics'
        ];
        
        $availableMethods = 0;
        foreach ($requiredMethods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "[✅ PASS] 方法: IntelligentAgentSystem::$method()\n";
                $availableMethods++;
            } else {
                echo "[❌ FAIL] 方法: IntelligentAgentSystem::$method() - 不存在\n";
            }
        }
        
        echo "         可用方法: $availableMethods/" . count($requiredMethods) . "\n";
    }
} catch (Exception $e) {
    echo "[❌ FAIL] IntelligentAgentSystem方法测试失败: {$e->getMessage()}\n";
}

// 测试EnhancedAgentCoordinator的关键方法
try {
    if (class_exists('AlingAi\\AI\\EnhancedAgentCoordinator')) {
        $reflection = new ReflectionClass('AlingAi\\AI\\EnhancedAgentCoordinator');
        
        $requiredMethods = [
            'getTaskStatus',
            'getHealthStatus',
            'getStatus'
        ];
        
        $availableMethods = 0;
        foreach ($requiredMethods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "[✅ PASS] 方法: EnhancedAgentCoordinator::$method()\n";
                $availableMethods++;
            } else {
                echo "[❌ FAIL] 方法: EnhancedAgentCoordinator::$method() - 不存在\n";
            }
        }
        
        echo "         可用方法: $availableMethods/" . count($requiredMethods) . "\n";
    }
} catch (Exception $e) {
    echo "[❌ FAIL] EnhancedAgentCoordinator方法测试失败: {$e->getMessage()}\n";
}

// 测试SelfEvolutionSystem的关键方法
try {
    if (class_exists('AlingAi\\Core\\SelfEvolutionSystem')) {
        $reflection = new ReflectionClass('AlingAi\\Core\\SelfEvolutionSystem');
        
        $requiredMethods = [
            'generateReport',
            'healthCheck',
            'getSystemStatus'
        ];
        
        $availableMethods = 0;
        foreach ($requiredMethods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "[✅ PASS] 方法: SelfEvolutionSystem::$method()\n";
                $availableMethods++;
            } else {
                echo "[❌ FAIL] 方法: SelfEvolutionSystem::$method() - 不存在\n";
            }
        }
        
        echo "         可用方法: $availableMethods/" . count($requiredMethods) . "\n";
    }
} catch (Exception $e) {
    echo "[❌ FAIL] SelfEvolutionSystem方法测试失败: {$e->getMessage()}\n";
}

// 测试SelfLearningFramework的关键方法
try {
    if (class_exists('AlingAi\\AI\\SelfLearningFramework')) {
        $reflection = new ReflectionClass('AlingAi\\AI\\SelfLearningFramework');
        
        $requiredMethods = [
            'executeSpecificLearning',
            'getLearningProgress',
            'getStatus'
        ];
        
        $availableMethods = 0;
        foreach ($requiredMethods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "[✅ PASS] 方法: SelfLearningFramework::$method()\n";
                $availableMethods++;
            } else {
                echo "[❌ FAIL] 方法: SelfLearningFramework::$method() - 不存在\n";
            }
        }
        
        echo "         可用方法: $availableMethods/" . count($requiredMethods) . "\n";
    }
} catch (Exception $e) {
    echo "[❌ FAIL] SelfLearningFramework方法测试失败: {$e->getMessage()}\n";
}

echo "\n🎯 测试结果汇总\n";
echo "============================================================\n";
echo "通过的类: {$results['passed']}\n";
echo "失败的类: {$results['failed']}\n";
echo "总体状态: " . ($results['failed'] === 0 ? "✅ 全部通过" : "❌ 有失败项") . "\n";
echo "完成时间: " . date('Y-m-d H:i:s') . "\n";

if ($results['failed'] === 0) {
    echo "\n🎉 编译错误修复验证成功！\n";
    echo "所有智能体相关类都可以正常加载，编译错误已全部解决。\n";
} else {
    echo "\n⚠️  仍有部分问题需要解决\n";
}

echo "\n============================================================\n";
